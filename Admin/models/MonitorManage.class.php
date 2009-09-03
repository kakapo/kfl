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
	}
	function deleteErrorLog($error_no){
		return $this->db->execute("delete from errorlog where error_no='$error_no'");
	}
	function getErrorLogById($error_no){
		return $this->db->getOne("select backtrace_msg from errorlog where error_no='$error_no'");
	}
	function setListStyle($total,$pageCount)
	{
		include_once("Pager.class.php");
	    $list ['page'] = new Pager ( $total, $pageCount ); //创建分页对象
		$offset = $list ['page']->offset ();                    //获得记录偏移量
		//$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'yellowf3_12b none', 'numBarMain' => 'gray4_12 none' );                      //翻页条的样式
		//$list ['page']->setLinkStyle ( $pagerStyle );
		$list ['page']->setLinkScript("dijit.byId(\"_monitor_errorlog\").attr(\"href\",\"/index.php/monitor/errorlog/pagecount/@PAGE@\")");
		$list ['page_array'] ['pagebar'] = $list ['page']->wholeNumBar(10, '#000000', '#cccccc');

		return (array)$list;
	}
}