<?php
include("ToolManage.class.php");
class tool {
	function view_createtbls(){
		
		$toolManage = new ToolManage();
		$toolManage->createTbl(0);
		
	}
	
	function view_client(){
		
		$arr['domain'] = $_GET['domain'];
		
		$toolManage = new ToolManage();
		
		$arr['key'] = $toolManage->generateKey();
		
		$toolManage->addNewClient($arr);
	}
	
	
}
?>