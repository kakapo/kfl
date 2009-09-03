<?php
class MonitorManage extends Model{
	private $db;
	function __construct(){
		$this->db = parent::dbConnect($GLOBALS ['gDataBase'] ['setting']);
	}
	
	function getErrorLog($con,$pageCount){
		$select =$this->db->select();
		$select->from ( " errorlog ");
		
		if(isset($con['error_no'])) $select->where ( "error_no = '".$con['error_no']."'" );

		
		if(isset($con['order'])) $select->order ( $con['order']." desc" );

		//return $this->getList ( $select, $pageCount, true );
		
		$list = array();
		//是否获得分页对象
		$offset = '';
		
		$total = $select->count (); //获得查询到的记录数
		$list  = $this->setListStyle($total,$pageCount);
		
		$select->limit ( $list['page']->offset(), $pageCount );
		$rs = $select->query();
	
		if ($rs) {
			
			foreach ( $rs as $key => $record ) {
				$list ['records'] [$key] = $record;
			}
		}
		return (array) $list;
		//return $this->db->getAll("select * from errorlog");
	}
	function setListStyle($total,$pageCount)
	{
		include_once("Pager.class.php");
	    $list ['page'] = new Pager ( $total, $pageCount ); //创建分页对象
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
}