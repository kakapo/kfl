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
		if(!empty($_GET['error_no'])) $con['error_no'] = $_GET['error_no'];
		$con['order'] = "error_no";

		$items = $this->mMonitorObj->getErrorLog($con,4);
		
		$tpl->assign('items',$items);
	}
	function view_eventlog(){
		global $tpl;
	}
	
	
}
?>