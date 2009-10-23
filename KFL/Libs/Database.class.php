<?php
/**
 * Database.class.php :: 数据库操作类
 
 */
if(version_compare(PHP_VERSION, "5.1.0", "<") && !class_exists("PDO"))
{
	trigger_error('Current PHP version: ' . PHP_VERSION . ' is too low for PDO.',E_USER_ERROR);
	die();
}
class Database extends PDO
{
	
	private $lastSql;						//最后运行的SQL语句
	private $lastData 		=''; 			//最后绑定数据
	private $startTime;						//查询开始时间

	public $cacheTime 		= 3600;			//设置全局缓存时间
	public $cacheDir 		= "/tmp";		//缓存存储路径
	public $cacheDirLevel 	= 3;			//缓存 HASH 目录级别
	public $cacheOpen		= 0 ;			//缓存开关,默认关闭
	public $cacheStore		='file';     	//缓存存储方式
	public $cacheServer	;					//memcached 服务器配置
	


	function __construct($dsn,$username='',$password='',$driver_options=array())
	{
		$this->startTime = $this->_microTime();
		try{
			parent::__construct($dsn,$username,$password,$driver_options);
		}catch (PDOException $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
			
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
		//debug_print_backtrace();
		if(isset($err_arr[2])) trigger_error($err_arr[2], E_USER_ERROR);
		//写入查询日志
		$GLOBALS['gSqlArr'][] = $this->lastSql.' Second:'.($this->_microTime()-$this->startTime);
		
	}
	
	/*
	 * 启动memcached
	 * @access private
	 * @param array $server
	 * @return void 
	 * */
	private function _setupMemcached($server){
		if (!class_exists('Memcache'))
        {
        	trigger_error("Memcache extension not exists!", E_USER_ERROR);
            die();
        }
        if(!(isset($GLOBALS['dbMemcacheObj']) && is_object($GLOBALS['dbMemcacheObj']))){
			if(isset($server) && is_array($server)){
			 	$memcache  = new Memcache;
			 	foreach ($server as $key => $v) {
		 		  if(!empty($v['host']) && !empty($v['port'])){
		 			$memcache->addServer($v['host'], $v['port']);
		 		  }
			 	}
			}
			$GLOBALS['dbMemcacheObj'] = $memcache;
        }
		
		return $GLOBALS['dbMemcacheObj'];
		
	}
	
	
	//================================缓存扩展方法==========================/
	//设置缓存时间
	public function setCache($setting)
	{
		
		$this->cacheOpen 	= isset($setting['cacheOpen'])?$setting['cacheOpen']:1;//开启
		$this->cacheTime 	= isset($setting['cacheTime'])?$setting['cacheTime']:3600;
		$this->cacheDir 	= isset($setting['cacheDir'])?$setting['cacheDir']:'';
		$this->cacheStore 	= isset($setting['cacheStore'])?$setting['cacheStore']:"file";
		$this->cacheServer 	= isset($setting['cacheServer'])?$setting['cacheServer']:'';

	}
	/**
	 * 缓存查询
	 * @access private
	 * @param string $sql
	 * @param string $type [fetch|fetchAll|fetchColumn]
	 * @param int $cacheTime -1=not cache
	 * @param string $cacheId 
	 * @return array
	 */

	//
	private function _cacheQuery($sql, $type, $cacheTime = -1, $cacheId = false)
	{
		
		$cacheTime = $cacheTime == 0 ? '999999999' : $cacheTime ;
		
		$cacheTime = $cacheTime>=0 ? $cacheTime : $this->cacheTime;
			
		
		$cacheId = $cacheId ? $this->_generateKey($cacheId) : $this->_generateKey($sql);
		
		if($this->cacheStore=='memcache'){
			
			$memcache = $this->_setupMemcached($this->cacheServer);
			
			//如果生命时间小于0，直接删除缓存
			if($this->cacheTime < 0) $memcache->delete($cacheId);
			$rs = $memcache->get($cacheId);
			if($rs===false){
				$rs = $this->_execSql($type);
				$memcache->set($cacheId,$rs,0,$cacheTime);
			}
			//$memcache->close();
		
		}
		
		if($this->cacheStore=='file'){      
			$cacheFile = ($this->cacheDir ? $this->cacheDir.'/' : '').$this->_hashCacheId($cacheId);
			
			if (!file_exists($cacheFile) || (filemtime($cacheFile) + $cacheTime) < time()){		
				$rs =  $this->_execSql($type);
				
				//写入缓存
				@write_file(serialize($rs),$cacheFile);
			}else{
				
				//读取缓存
				if(!($rs = unserialize(@read_file($cacheFile))))
				{
					unlink($cacheFile);
				}
				
			}
		}
		return $rs;

	}

	//清除指定缓存
	public function cleanCache($cacheId)
	{
		$cacheId = md5($cacheId);
		$cacheFile = ($this->cacheDir ? $this->cacheDir.'/' : '').$this->_hashCacheId($cacheId);
		if(file_exists($cacheFile)) @unlink($cacheFile);
	}
	//清除全部缓存
	public function cleanAllCache()
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
	

	//============================= 最基本SQL语句操作方法=======================
	/**
	 * 获取一行数据
	 * @access private
	 * @param string $sql
	 * @param array $arr
	 * @return array
	 */
	public function getRow($sql,$arr=array(),$cacheTime = -1, $cacheId = false)
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($cacheTime>=0 && $this->cacheOpen){
			return $this->_cacheQuery($sql,"fetch",$cacheTime, $cacheId);
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
	public function getAll($sql,$arr=array(),$cacheTime = -1, $cacheId = false)
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($cacheTime>=0 && $this->cacheOpen){
			return $this->_cacheQuery($sql,"fetchAll",$cacheTime, $cacheId);
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
	public function getOne($sql,$arr=array(),$cacheTime = -1, $cacheId = false)
	{
		$this->lastSql = $sql;
		$this->lastData = $arr;
		if($cacheTime>=0 && $this->cacheOpen){
			return $this->_cacheQuery($sql,"fetchColumn",$cacheTime, $cacheId);
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

	//====================== 快捷查询扩展方法 ===================================
	// 包括 insert,update
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

	//创建查询对象
	function select()
	{
		$select = new DB_Core_Select($this);
		return $select;
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
			return $this->db->getAll($this->getSql()." limit ". $this->limit['start'].", " .$this->limit['length']);
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

}
?>