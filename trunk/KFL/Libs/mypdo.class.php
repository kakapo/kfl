<?php
/**
 * mypdo.class.php :: 数据库操作类
 *2009-01-06做了以下更新,by zswu:
 *1、去掉getRowByMM,getAllByMM,getOneByMM,closeMM,disposal();
 *2、将memcache功能整合到getRow,getOne,getAll
 *3、新增public方法useMemCache();setMemCacheLife;private方法：_generateKey()；_microTime();_execSql();_throwError,去掉getEorror;
 *4、归类了所有方法：分内部使用方法,基本SQL操作方法,快捷查询方法,本地缓存操作方法；
 *5、重构了query(),selectLimit();insert();update();delete();insertId();beginTrans();commitTrans();rollbackTrans();cacheQuery();
 */
if(version_compare(PHP_VERSION, "5.1.0", "<") && !class_exists("PDO"))
{
	trigger_error('Current PHP version: ' . PHP_VERSION . ' is too low for PDO.',E_USER_ERROR);
	die();
}
class mypdo extends PDO
{
	public $debug = 1;	   //是否开启DEBUG信息 true|false
	private $errorMsg;            //SQL语句错误记录
	private $lastSql;			//最后运行的SQL语句
	private $lastData =''; //最后绑定数据
	private $startTime;			//查询开始时间

	public $cacheTime = 3600;		//设置全局缓存时间
	public $cacheDir = "/tmp";			//缓存存储路径
	public $cacheDirLevel = 3;	//缓存 HASH 目录级别

	private $memcache ;				//memcache 对象
	private $memcache_life=3600;  	//memcache缓存时间;


	function __construct($dsn,$username='',$password='',$driver_options=array())
	{
		$this->startTime = $this->_microTime();
		try{
			parent::__construct($dsn,$username,$password,$driver_options);
		}catch (PDOException $e) {
			$this->errorMsg = "Error:".$e->getMessage();
		    trigger_error($this->errorMsg, E_USER_ERROR);
			$this->showDebug();
		}
		//$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('mypdostatement', array($this)));
	}

	//============================= 内部方法=======================
	/**
	 * 获得 Cache hash 路径
	 * @access private
	 * @param string $cacheId
	 * @return string
	 */
	private function _hashCacheId($cacheId)
	{
		$cacheId = $cacheId ? md5($cacheId) : md5($this->lastSql);

		$hashLevel = '';

		//构造缓存目录结构
		for($i = 1; $i <= $this->cacheDirLevel; $i++)
		{
			$hashLevel .= '/'.substr($cacheId, 0, $i);
		}
		return trim($hashLevel,'/').'/'.$cacheId.'.cache';
	}
	/**
	 * 生成唯一key
	 * @access private
	 * @param string $str
	 * @return string
	 */
	private function _generateKey($str){
		return md5($str);
	}
	/**
	 * 返回微秒时间
	 * @access private
	 * @return float
	 */
	private function _microTime()
	{
		$mTime = explode(' ', microtime());  //microtime();返回当前 Unix 时间戳和微秒数
		return $mTime[1] + $mTime[0];
	}
	/**
	 * 执行数据库查询
	 * @access private
	 * @param string $type
	 * @return mix
	 */
	private function _execSql($type='fetch'){
		$start = $this->_microTime();
		if($this->lastData){
			$sth = $this->prepare($this->lastSql);
			$rs = $sth->execute($this->lastData);
		}else{
			$rs = $sth = parent::query($this->lastSql);
		}
		if($rs){
			switch ($type){
				case "fetch":
					$res = $sth->fetch(PDO::FETCH_ASSOC);
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return $res;
				break;
				case "fetchAll":
					$res = $sth->fetchAll(PDO::FETCH_ASSOC);
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return $res;
				break;
				case "fetchColumn":
					$res = $sth->fetchColumn();
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return $res;
				case "rowCount":
					$res = $sth->rowCount();
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return $res;
				break;
				case "boolen":
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return true;
				break;
				default:
					$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
					return true;
			}
		}else{
			$GLOBALS['gSqlQuery'][] = array($this->lastSql,$this->_microTime()-$start);
			$this->_throwError($sth);
			switch ($type){
				case "fetch":
					return array();
				break;
				case "fetchAll":
					return array();
				break;
				case "fetchColumn":
					return '';
				case "rowCount":
					return 0;
				break;
				case "boolen":
					return false;
				break;
				default:
					return false;
			}

		}
	}
	/**
	 * 抛出错误异常信息
	 * @access private
	 * @param object $sth
	 * @return void
	 */
	private function _throwError($sth='')
	{
		$err_arr = array();
		if($sth!='')
		{
			$err_arr = $sth->errorInfo();
		}
		else
		{
			$err_arr = $this->errorInfo();
		}
		if(isset($err_arr[2])) trigger_error("Error:".$err_arr[2], E_USER_ERROR);
		//写入查询日志
		$GLOBALS['gSqlArr'][] = $this->lastSql.' Second:'.($this->_microTime()-$this->startTime);

		$this->errorMsg = $err_arr[2];
		$this->showDebug();
	}

	//============================= 最基本SQL语句操作方法=======================
	/**
	 * 获取一行数据
	 * @access private
	 * @param string $sql
	 * @param array $arr
	 * @return array
	 */
	public function getRow($sql,$arr=array())
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($this->memcache){
			$key = $this->_generateKey($sql);
			//如果生命时间小于0，直接删除缓存
			if($this->memcache_life < 0) $this->memcache->delete($key);
			$row = $this->memcache->get($key);
			if($row===false){
				$row = $this->_execSql('fetch');
				$this->memcache->set($key,$row,0,$this->memcache_life);
			}
			$this->memcache->close();
			return $row;
		}else{
			return $this->_execSql('fetch');
		}
	}
	/**
	 * 获取多行数据
	 * @access private
	 * @param string $sql
	 * @param array $arr
	 * @return array
	 */
	public function getAll($sql,$arr=array())
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($this->memcache){
			$key = $this->_generateKey($sql);
			//如果生命时间小于0，直接删除缓存
			if($this->memcache_life < 0) $this->memcache->delete($key);
			$row = $this->memcache->get($key);
			if($row===false){
				$row = $this->_execSql('fetchAll');
				$this->memcache->set($key,$row,0,$this->memcache_life);
			}
			$this->memcache->close();
			return $row;
		}else{
			return $this->_execSql('fetchAll');
		}
	}
	/**
	 * 获取单列数据
	 * @access private
	 * @param string $sql
	 * @param array $arr
	 * @return string
	 */
	public function getOne($sql,$arr=array())
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($this->memcache){
			$key = $this->_generateKey($sql);
			//如果生命时间小于0，直接删除缓存
			if($this->memcache_life < 0) $this->memcache->delete($key);
			$row = $this->memcache->get($key);
			if($row===false){
				$row = $this->_execSql('fetchColumn');
				$this->memcache->set($key,$row,0,$this->memcache_life);
			}
			$this->memcache->close();
			return $row;
		}else{
			return $this->_execSql('fetchColumn');
		}
	}
	/**
	 * 执行insert,update操作比较合适
	 * @access private
	 * @param string $sql
	 * @param array $arr
	 * @return boolen
	 */
	public function execute($sql,$arr=array())
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		return $this->_execSql('boolen');
	}
	/**
	 * 启动memcache
	 * @param array $server  memcached服务器配置
	 * @return void
	 */
	public function useMemCache($server){
		if (!class_exists('Memcache'))
        {
        	trigger_error("Fatal Error: Memcache extension not exists!", E_USER_ERROR);
            die();
        }
		 if(isset($server) && is_array($server)){
		 	$this->memcache  = new Memcache;
		 	foreach ($server as $key => $v) {
	 		  if(!empty($v['host']) && !empty($v['port'])){
	 			$this->memcache->addServer($v['host'], $v['port']);
	 		  }
		 	}
		}
	}
	/**
	 * 设置memcache生命周期
	 * @param int $life  秒数
	 * @return void
	 */
	public function setMemCacheLife($life){
		$this->memcache_life = $life;
	}
	/**
	 * 调试使用
	 * @return void
	 */
	function showDebug()
	{
		if($this->debug){
			if($this->errorMsg)
			{
				$errinfo = '<li>'.$this->lastSql.'<hr size=1 noshadow><span style=\"font-family:Tahoma; font-size: 12px;\">'.$this->errorMsg.'</span>"';

				echo "
				<table cellpadding=0 cellspacing=5 width=100% bgcolor=white>
				<tr>
					<td>".$errinfo."</td>
				</tr>

				</table>";
			}

		}
	}



	//====================== 快捷查询扩展方法 ===================================
	// 包括 selectLimit,insert,update,delete,insertId,beginTrans,commitTrans,rollbackTrans,query
	//limit 查询
	function selectLimit($sql,$start = 0,$length = -1)
	{
		$start = $start ? $start : 0 ;
		$length < 0 ? $length = '1' : $length ;
		return $this->getAll($sql." limit {$start},{$length}");
	}
	/**
	 * 执行 insert update delete 语
	 */
	function insert($field, $table)
	{
		$tempNames='';
		$tempValues='';
		foreach($field as $fieldName => $fieldValue)
		{
			if(isset($fieldValue))
			{
				$tempNames .= ",`".trim($fieldName).'`';

				if(substr_count($fieldValue,'MY_F:'))
				{
					$fieldValue = trim($fieldValue,'MY_F:');
					$tempValues .= ",{$fieldValue}";
				}
				else
				{
					$tempValues .= ",'{$fieldValue}'";
				}
			}
		}
		$sql = "insert into {$table} (".trim($tempNames,',').") values (".trim($tempValues,',').")";
		return $this->execute($sql);
	}
	//插入数据库 $condition 为条件也就是 where 以后的语
	function update($field, $table, $condition = false)
	{
		$tempData = '';
		foreach($field as $fieldName => $fieldValue)
		{
			if(isset($fieldValue))
			{
				if(substr_count($fieldValue,'MY_F:'))
				{
					$fieldValue = trim($fieldValue,'MY_F:');
					$tempData .= ',`'.trim($fieldName)."` = {$fieldValue}";
				}
				else
				{
					$tempData .= ',`'.trim($fieldName)."` = '{$fieldValue}'";
				}
			}
		}

		$sql = "update {$table} set ".trim($tempData,',').( $condition ? " where {$condition}" : '');
		return  $this->execute($sql);
	}
	//数据删除
	function delete($sql)
	{
		return $this->execute($sql);
	}
	//取得上一步 INSERT 操作产生的 ID
	function insertId()
	{
		return $this->lastInsertId();
	}
	//事务开始 start transaction
	function beginTrans()
	{
		$this->beginTransaction();
	}
	//事务提交 commit
	function commitTrans()
	{
		$this->commit();
	}
	//事务回滚 rollback
	function rollbackTrans()
	{
		$this->rollBack();
	}
	//sql语句执行
	function query($sql,$query_type = 1)   //$query_type = 1 返回影响记录数量；2，返回查询数组,3,返回单条数据
	{
		if(!$sql) return false;
		switch ($query_type)
		{
			case 1:
				$this->lastSql = $sql;
				return $this->_execSql('rowCount');	//返回影响的记录数量
			case 2:
				return $this->getAll($sql);
			case 3:
				return $this->getOne($sql);	//单条记录返回
		}
	}
	//创建查询对象
	function select()
	{
		$select = new DB_Core_Select($this);
		return $select;
	}

	//================================本地缓存扩展方法==========================/
	//设置缓存时间
	function setCacheTime($cacheTime)
	{
		$this->cacheTime = $cacheTime;
	}
	//设置缓存路径
	function setCacheDir($dir)
	{
		$this->cacheDir = $dir;
	}
	//缓存查询
	function cacheQuery($sql ,$cacheTime = 0, $cacheId = false)
	{
		$cacheTime = $cacheTime == -1 ? '999999999' : $cacheTime ;
		$cacheTime = $cacheTime ? $cacheTime : $this->cacheTime;
		$cacheId = $cacheId ? md5($cacheId) : md5($sql);
		      
		$cacheFile = ($this->cacheDir ? $this->cacheDir.'/' : '').$this->_hashCacheId($cacheId);
		
		if (!file_exists($cacheFile)){		
			$rs['recordArray'] =  $this->getAll($sql);
			$rs['cacheId'] = $cacheId;
			//写入缓存
			@write_file(serialize($rs),$cacheFile);
		}else{
			if((@filemtime($cacheFile) + $cacheTime) < time())
			{
				$rs['recordArray'] = $this->getAll($sql);
				$rs['cacheId'] = $cacheId;
				//写入缓存	
				@write_file(serialize($rs),$cacheFile);
			}
			else
			{
				//读取缓存
				if(!($rs = unserialize(@read_file($cacheFile))))
				{
					unlink($cacheFile);
				}
			}
		}
		return $rs;

	}
	//缓存返回第一条记录第一个字段
	function cacheGetOne($sql,$cacheTime = 0,$cacheId = false) //注意必须为单行数据
	{
		$record = $this->cacheGetRow($sql,$cacheTime,$cacheId);
		reset($record);
		return current($record);
	}
	//缓存取之返回单行数据 组成的数组
	function cacheGetRow($sql,$cacheTime = 0,$cacheId = false)
	{
		$rs = $this->cacheSelectLimit($sql,0,1,$cacheTime,$cacheId);
		return $rs['recordArray'][0];;
	}
	//limit 缓存查询
	function cacheSelectLimit($sql,$start = 0,$length = -1,$cacheTime = 0, $cacheId = false)
	{
		$start = $start ? $start : 0 ;
		$length < 0 ? $length = '1' : $length ;
		$rs = $this->cacheQuery($sql." limit {$start},{$length}",$cacheTime,$cacheId);
		return $rs['recordArray'];

	}
	//缓存返回所有数据
	function cacheGetAll($sql,$cacheTime = 0,$cacheId = false)
	{
		$rs = $this->cacheQuery($sql,$cacheTime,$cacheId);
		return $rs['recordArray'];
	}
	//清除指定缓存
	function cleanCache($cacheId)
	{
		$cacheId = md5($cacheId);
		$cacheFile = ($this->cacheDir ? $this->cacheDir.'/' : '').$this->_hashCacheId($cacheId);
		@unlink($cacheFile);
	}
	//清除全部缓存
	function cleanAllCache()
	{
		$array = list_dir_file($this->cacheDir);
		$array = $array ? $array : array();
		foreach($array as $cacheFile)
		{
			if($cacheFile['extension'] == 'cache')
			{
				@unlink($cacheFile['path']);
			}
		}
	}

}

/*--------------------------- 数据库查询构造器类 ---------------------------*/
class DB_Core_Select
{
	var $db;
	var $sql;
	var $sqlArray ;
	var $rs;
	var $multinest;
	var $limit;

	function DB_Core_Select(&$db)
	{
		$this->db = &$db;
		$this->sqlArray = array('where'=>'','order'=>'','group'=>'','having'=>'');
	}

	//查询表
	function from($table, $field = '*')
	{
		unset($this->sql,$this->rs);
		$this->sqlArray = array('where'=>'','order'=>'','group'=>'','having'=>'');
		$this->sql = "select {$field} from {$table}";
	}

	function leftJoin($table, $condition, $field = '*')
	{
		$this->sql .= " left join {$table} on {$condition} ";
		$this->sql = preg_replace('/select(.+?)from/ism', "select \\1,{$field} from", $this->sql, 1);
	}

	function rightJoin($table, $condition, $field = '*')
	{
		$this->sql .= " right join {$table} on {$condition} ";
		$this->sql = preg_replace('/select(.+?)from/ism', "select \\1,{$field} from", $this->sql, 1);
	}

	function multSelect($sql)
	{
		$this->sql = $sql;
		$this->multinest = true;
	}

	//与查询
	function where($where)
	{
		if($where)
		{
			$this->sqlArray['where'] .= !preg_match('/where/i',$this->sqlArray['where']) ? " where {$where} " : " and {$where} " ;
		}
	}
	//或查询
	function orWhere($where)
	{
		if($where)
		{
			$this->sqlArray['where'] .= !preg_match('/where/i',$this->sqlArray['where']) ? " where {$where} " : " or {$where} " ;
		}
	}
	//查询排序
	function order($order)
	{
		if($order)
		{
			$this->sqlArray['order'] .= !preg_match('/order/i',$this->sqlArray['order']) ? " order by {$order} " : ",{$order}" ;
		}
	}
	//分组查询
	function group($group)
	{
		if($group)
		{
			$this->sqlArray['group'] .= !preg_match('/group/i',$this->sqlArray['group']) ? " group by {$group} " : ",{$group}" ;
		}
	}
	/*
	 HAVING用户在使用SQL语言的过程中可能希望解决的一个问题就是对由sum或其它集合函数运算结果的输出进行限制。
	 例如，我们可能只希望看到Store_Information数据表中销售总额超过1500美圆的商店的信息，这时我们就需要使用HAVING从句。语法格式为：
	 SELECT store_name, SUM(sales)
	 FROM Store_Information
	 GROUP BY store_name
	 HAVING SUM(sales) > 1500
	 查询结果显示为：
	 store_name SUM(Sales)
	 Los Angeles $1800
	 小注：
	 SQL语言中设定集合函数的查询条件时使用HAVING从句而不是WHERE从句。通常情况下，HAVING从句被放置在SQL命令的结尾处

	 */
	function having($having)
	{
		if($having)
		{
			$this->sqlArray['having'] .= !preg_match('/having/i',$this->sqlArray['having']) ? " having {$having} " : " and {$having} " ;
		}
	}

	function orHaving($having)
	{
		if($having)
		{
			$this->sqlArray['having'] .= !preg_match('/having/i',$this->sqlArray['having']) ? " having {$having} " : " or {$having} " ;
		}
	}

	function limit($start, $length)
	{
		$this->limit['start'] = $start;
		$this->limit['length'] = $length;
	}

	function getSql()
	{
		return $this->sql.$this->sqlArray['where'].$this->sqlArray['group'].$this->sqlArray['order'].$this->sqlArray['having'];
	}

	function count()
	{
		if($this->multinest)
		{
			return false;
		}
		if($this->sqlArray['group'])
		{
			//得不尝失
			$this->rs = $this->db->getAll($this->getSql());
			return count($this->rs);
		}
		else
		{
			return $this->db->getOne(preg_replace('/select(.+?)from/ism', "select count(*) from", $this->getSql()));
		}
	}

	function query()
	{
		if($this->limit)
		{
			return $this->db->selectLimit($this->getSql(), $this->limit['start'], $this->limit['length']);
		}
		else
		{
			if($this->sqlArray['group'] && isset($this->rs))
			{
				return $this->rs;
			}
			else
			{
				return $this->db->getAll($this->getSql());
			}
		}
	}

	function cacheQuery($cacheTime = false, $cacheId = false)
	{
		if($this->limit)
		{
			return $this->db->cacheSelectLimit($this->getSql(), $this->limit['start'], $this->limit['length'],$cacheTime, $cacheId);
		}
		else
		{
			if($this->sqlArray['group'] && isset($this->rs))
			{
				return $this->rs;
			}
			else
			{
				return $this->db->cacheQuery($this->getSql(),$cacheTime,$cacheId);
			}
		}
	}

}
?>