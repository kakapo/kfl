<?php
class ShowItem extends Model {
	private $main_db;

	function __construct(  )
	{

		$this->main_db = parent::dbConnect( );

	}

	function getSubCates($cate_id){
		$sql = "select * from show_category where parent='{$cate_id}' and active=1 order by show_order desc";
		$key = md5($sql);
		$data = memcache_get_content($GLOBALS ['gMemcacheServer'] ['SqlDataCache'] ,$key);
		if(!$data){
			$data =  $this->main_db->getAll($sql);
			memcache_set_content($GLOBALS ['gMemcacheServer'] ['SqlDataCache'] ,$key,$data,3600);
		}
		return $data;
	}
	function getCateByKeyword($keyword){

		$data = memcache_get_content($GLOBALS ['gMemcacheServer'] ['SqlDataCache'],'all_cate_keyword');
		if(!$data){
			$all_cate_keyword = $this->main_db->getAll("select cate_id,keyword from show_category where active=1");
			foreach ($all_cate_keyword as $v){
				$data[$v['keyword']] = $v['cate_id'];
			}
			memcache_set_content($GLOBALS ['gMemcacheServer'] ['SqlDataCache'],'all_cate_keyword',$data,3600);
		}
		if(isset($data[$keyword]))
			return $data[$keyword];
		else
			return 0;
	}
	function getItemById($item_id){
		return $this->main_db->getRow("select * from show_items where item_id='{$item_id}'");
	}
	function getItems($con,$pageCount){
		$select = $this->main_db->select();
		$select->from ( " show_items as i,show_item_cate_rel as rel " ,"*,(UNIX_TIMESTAMP()-i.item_regtime) as leftime");
		$select->where ( " i.item_id=rel.item_id and i.item_status=1" );
		if(isset($con['gender'])) $select->where ( "i.item_gender = '".$con['gender']."'" );
		if(isset($con['vip'])) $select->where ( "item_vip = '".$con['vip']."'" );
		if(isset($con['item_type'])) $select->where ( "item_type = '".$con['item_type']."'" );
		if(isset($con['item_recommend'])) $select->where ( "item_recommend = '".$con['item_recommend']."'" );
		if(isset($con['cate_id'])) {
			if(is_array($con['cate_id'])){
				foreach ($con['cate_id'] as $id){
					$arr[] = " rel.cate_id = '".$id."'";
				}
				$select->where("(".join(' or ',$arr).")");
				//$select->where(" rel.cate_id in (".join(",",$con['cate_id']).")");
			}else{
				$select->where (" rel.cate_id = '".$con['cate_id']."'");
			}
		}
		
		if(isset($con['order'])) $select->order ( "i.".$con['order']." desc" );

		return $this->getList ( $select, $pageCount, true );
	}
	function getDisplayItem(){
	  	$sql = "select * from display_items where online=1 order by ctime desc limit 8";
		$this->main_db->setCacheDir(APP_TEMP_DIR.'/database');
		return $this->main_db->cacheGetAll($sql,-1,'display_items');
	}
	function getRecomendIndex($con,$num){
	  $select = $this->main_db->select();
	  $select->from ( "recomend_diy" ,"*");
	  $this->main_db->setCacheDir(APP_TEMP_DIR.'/database/recomend/');
	  if(isset($con['gender'])&&!empty($con['gender'])){
	   $select->where ( "user_gender = '".$con['gender']."'" );
	  }
	  $select->where ( "position ='Index'" );
	  if(isset($con['vip'])) $select->where ( " vip = '".$con['vip']."'" );
	  if(isset($con['sort'])){
	  	 if($con['sort']==2){
	  	 	$select->order ("hits desc");
	  	 }else{
	  	   $select->order ("rec_time desc");
	  	 }
	  }
	  $offset=0;
	  $select->limit ($offset,$num);
	  $list = array();
	  $rs = $select->cacheQuery ( - 1, $select->getSql () . $offset );
	  if ($rs) {
		foreach ( $rs as $key => $record ) {
		  $list ['records'] [$key] = $record;
		}
	   }
	   return ( array ) $list;
	}
	function getRecomendDiy($con,$pageCount,$pagination=''){
		$select = $this->main_db->select();
		$select->from ( "recomend_diy" ,"*,hits as item_hit");
		if(isset($con['gender'])) $select->where ( "user_gender = '".$con['gender']."'" );
		if(isset($con['order'])) $select->order ( "".$con['order']." desc" );
		if(isset($con['pos'])) $select->where ( " position = '".$con['pos']."'" );
		if(isset($con['vip'])) $select->where ( " vip = '".$con['vip']."'" );
        $this->main_db->setCacheDir(APP_TEMP_DIR.'/database/recomend/');
        $list = array();
		$offset = 0;
		if ($pagination !== false) {
			$total = $select->count (); //获得查询到的记录数
			$list=$this->setListStyle($total,$pageCount);
			$offset = $list['page']->offset();
		}
		$select->limit ( $offset, $pageCount );
		$rs = $select->cacheQuery ( - 1, $select->getSql () . $offset );
		if ($rs) {
			foreach ( $rs as $key => $record ) {
				$list ['records'] [$key] = $record;
			}
		}
		return ( array ) $list;
	}
	function getCateByItemId($item_id){
		return $this->main_db->getAll("select * from show_category c,show_item_cate_rel r where c.cate_id=r.cate_id and r.item_id=?",array($item_id));
	}
	function getRecomendById($aid){
		return $this->main_db->getRow("select * from recomend_diy where id='{$aid}'");
	}
	//获得道具列表
	function setListStyle($total,$pageCount)
	{
		include_once("HTML_Pager.class.php");
	    $list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
		$offset = $list ['page']->offset ();                    //获得记录偏移量
		$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'yellowf3_12b none', 'numBarMain' => 'gray4_12 none' );                      //翻页条的样式
		$list ['page']->setLinkStyle ( $pagerStyle );
		$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '' );
		$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '上一页' );
		$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '7',  '','', '', '' );
		$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '下一页' );
		$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '' );
		$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
		$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		return (array)$list;
	}
	function getList($select, $pageCount = -1, $pagination = false) {

		$list = array();
		//是否获得分页对象
		$offset = '';
		if ($pagination !== false) {
			$total = $select->count (); //获得查询到的记录数
			$list=$this->setListStyle($total,$pageCount);
		}
		$select->limit ( $list['page']->offset(), $pageCount );
		$rs = $select->query();
		if ($rs) {
			foreach ( $rs as $key => $record ) {
				$list ['records'] [$key] = $record;
			}
		}
		//$this->show_db->showDebug();  //显示运行的sql语句，调试使用
		return ( array ) $list;
	}

	//添加自定义图片
	public function addDiyImage( $userId , $userNickName , $imgU1rl )
	{
		$sql = "insert into user_diy_image(user_id,user_nickname,img_url,img_time)
				values ('{$userId}','{$userNickName}','{$imgU1rl}',FROM_UNIXTIME(UNIX_TIMESTAMP()))";
		if ( $this->main_db->query( $sql ) )
		{
			return $this->main_db->insertId();
		}
		else
		{
			return false;
		}
	}

	//删除自定义图片
	public function delDiyImage( $imgId )
	{
		return $this->main_db->query( "delete from user_diy_image where img_id = '{$imgId}'" );
	}
}



?>