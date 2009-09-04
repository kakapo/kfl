<?php
include_once 'MonitorManage.class.php';
include_once 'SettingManage.class.php';
class monitor{
	public $mMonitorObj;
	public $mSettingObj;
	
	function __construct(){
		$this->mMonitorObj = new MonitorManage();
		$this->mSettingObj = new SettingManage();
	}
	function view_noticeset(){
		global $tpl;	
		$sets = array("subject"=>'From KFL',"receiver"=>'');
		$items = $this->mSettingObj->getSettings('noticeset');
		$tpl->assign('set_type','noticeset');
		
		if(is_array($items)){
			foreach($items as $item){
				$sets[$item['name']] = $item['value'];
			}
		}
		
		$tpl->assign('sets',$sets);
		//$tpl->assign('items',$items);
		
	}
	function view_errorlog(){
		global $tpl;
		$error_no = '';
		if(!empty($_GET['error_no'])) {
			$con['error_no'] = $_GET['error_no'];
			$error_no = $_GET['error_no'];
		}
		$con['order'] = "id";

		$items = $this->mMonitorObj->getErrorLog($con,4);
		
		$tpl->assign('items',$items);
		$tpl->assign('error_no',$error_no);
	}
	function op_delerrorlog(){
		$error_no = $_POST['error_no'];
		
		$res =$this->mMonitorObj->deleteErrorLog($error_no);
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "删除成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "删除失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	function view_viewerrorlog(){
		$msg = $this->mMonitorObj->getErrorLogById($_GET['error_no']);
		echo htmlspecialchars_decode($msg,ENT_QUOTES);
		//echo 123;
		die;
	}
	function view_eventlog(){
		try{
		$handle = fopen("c:\\data\\info.txt",'r');
		}catch(Exception  $e){
			var_dump($e);
		}
		var_dump($handle);
		die;
	}
}
?>