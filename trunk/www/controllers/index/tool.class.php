<?php
include("ToolManage.class.php");
class tool {
	function view_createtbls(){
		$toolManage = new ToolManage();
		$toolManage->createTbl(0);
		
	}
}
?>