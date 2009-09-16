<?php
//include_once 'toolManage.class.php';
include_once 'SettingManage.class.php';
class tool{
	public $mToolObj;
	public $mSettingObj;
	
	function __construct(){
		$this->mtoolObj = new toolManage();
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
	function view_sqlite(){
		
	}
}
?>