<?php
include_once 'SettingManage.class.php';
class setting{
	public $mSetingObj;
	function __construct(){
		$this->mSettingObj = new SettingManage();
	}
	
	function view_system(){
		global $tpl;	
		$items = $this->mSettingObj->getSettings('system');
		$pre_defined = array('APP_STATUS','APP_LANG','APP_TEMP_DIR','KFL_DIR');
		$sets= array();
		$left_items = array();
		if(is_array($items)){
			foreach($items as $v){
				if(in_array($v['name'],$pre_defined)){
					$sets[$v['name']] = $v['value'];
				}else{
					
					$left_items[] = $v;
				}
			}
		}
		
		$tpl->assign('sets',$sets);
		$tpl->assign('set_type','system');
		$tpl->assign('items',$left_items);
	}
	
	function view_website(){
		global $tpl;	
		$items = $this->mSettingObj->getSettings('website');
	
		$tpl->assign('set_type','website');
		$tpl->assign('items',$items);
	}
	
	function view_email(){
		global $tpl;	
		$items = $this->mSettingObj->getSettings('email');
		$pre_defined = array('smtp_host','smtp_account','smtp_pass','smtp_from');
		$sets= array();
		$left_items = array();
		if(is_array($items)){
			foreach($items as $v){
				if(in_array($v['name'],$pre_defined)){
					$sets[$v['name']] = $v['value'];
				}else{
					
					$left_items[] = $v;
				}
			}
		}
		
		$tpl->assign('sets',$sets);
		$tpl->assign('set_type','email');
		$tpl->assign('items',$left_items);
	}
	
	function view_database(){
		global $tpl;	
		$database = $this->mSettingObj->getDatabase();
		$tpl->assign('set_type','database');
		$tpl->assign('database',$database);
	}
	
	function view_timezone(){
		global $tpl;	
		
		$item = $this->mSettingObj->getSettings('timezone');
		//print_r($item);
		if(empty($item)) $item = date_default_timezone_get();
		$tpl->assign('set_type','timezone');
		$tpl->assign('item',$item[0]['value']);
		$tpl->assign('timezone',timezone_identifiers_list());
	}
	
	function view_getdb(){
		//global $tpl;
		$items = $this->mSettingObj->getSettings($_GET['getdb']);
		foreach($items as $k=>$v){
			$v['value'] = htmlspecialchars_decode($v['value'],ENT_QUOTES);
			$items[$k] = $v;
		}
		json_output($items);	
	}
	
	function op_savedb(){
		unset($_POST['action']);
		unset($_POST['op']);
		$set_type = "db_".$_POST['valu_dbname'];
		$exist_db = $this->mSettingObj->getDatabaseByName($set_type);
		if(!$exist_db){
			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
			$res = $this->mSettingObj->saveDatabase($set_type);
			$res1 = 0;
			if($res){
				$res1 = $this->mSettingObj->saveSettings($pair,$set_type);
				if($res1){
					$msg['s'] = 200;
					$msg['m'] = "创建成功!";
					$msg['d'] = $set_type;	
				}
			}else{
				$msg['s'] = 400;
				$msg['m'] = "创建失败!";
				$msg['d'] = 'null';	
			}
		}else{
			$msg['s'] = 400;
			$msg['m'] = "此数据库名已经存在!";
			$msg['d'] = 'null';	
		}

		json_output($msg);
	}
	
	function op_updatedb(){
		
		unset($_POST['action']);
		unset($_POST['op']);
		
		$dbname = $_POST['edit_dbname'];
		unset($_POST['edit_dbname']);
		$set_type = "db_".$_POST['valu_dbname'];
		
		$exist_db = $this->mSettingObj->getDatabaseByName($set_type);
		
		if(!$exist_db || $exist_db==$dbname){
			$this->mSettingObj->deleteDatabase($dbname);

			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
	
			$res = $this->mSettingObj->saveDatabase($set_type);
			
			if($res){
				$res1 = $this->mSettingObj->saveSettings($pair,$set_type);
				if($res1){
					$msg['s'] = 200;
					$msg['m'] = "修改成功!";
					$msg['d'] = $set_type;				
				}
			}else{
				$msg['s'] = 400;
				$msg['m'] = "修改失败!";
				$msg['d'] = 'null';	
			} 
		}else{
			$msg['s'] = 400;
			$msg['m'] = "此数据库名已经存在!";
			$msg['d'] = 'null';	
		}
		
		json_output($msg);
		
	}
	
	function op_deletedb(){
		
		$dbname = $_POST['dbname'];
		$res =$this->mSettingObj->deleteDatabase($dbname);
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
	
	function op_save(){
		
		unset($_POST['action']);
		unset($_POST['op']);
		$set_type =$_POST['set_type'];
		unset($_POST['set_type']);
		$pair = array();
		foreach ($_POST as $key=>$value){
			$n = substr($key,5);
			$pre = substr($key,0,5);
			$pair[$n][$pre]= $value;
		}
		
		$res = $this->mSettingObj->saveSettings($pair,$set_type);
		if($res) {
			$msg['s'] = 200;
			$msg['m'] = "保存成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "保存失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
}