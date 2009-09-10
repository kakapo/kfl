<?php
include_once 'CacheManage.class.php';
include_once 'SettingManage.class.php';
class cache{
	public $mCacheObj;
	public $mSettingObj;
	
	function __construct(){
		$this->mCacheObj = new CacheManage();
		$this->mSettingObj = new SettingManage();
	}
	function view_memcached(){
		global $tpl;	
		$memcached = $this->mCacheObj->getMemcached();
		$tpl->assign('set_type','memcached');
		$tpl->assign('memcached',$memcached);
	}
	
	function view_getmemcached(){
		//global $tpl;
		$items = $this->mSettingObj->getSettings($_GET['getmemcached']);
		json_output($items);	
	}
	
	function op_savememcached(){
		unset($_POST['action']);
		unset($_POST['op']);
		
		$set_type = $_POST['valu_mmhost'].":".$_POST['valu_mmport'];
		$exist = $this->mCacheObj->getMemcachedByName($set_type);
		if(!$exist){
			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
			$res = $this->mCacheObj->saveMemcached($set_type);
			
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
			$msg['m'] = "此Memcached已经存在!";
			$msg['d'] = 'null';	
		}

		json_output($msg);
	}
	
	function op_updatememcached(){
		
		unset($_POST['action']);
		unset($_POST['op']);
		
		$host = $_POST['edit_host'];
		unset($_POST['edit_host']);
		$set_type = $_POST['valu_mmhost'].":".$_POST['valu_mmport'];
		
		$exist = $this->mCacheObj->getMemcachedByName($set_type);
		
		if(!$exist || $exist==$host){
			$this->mCacheObj->deleteMemcached($host);

			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
	
			$res = $this->mCacheObj->saveMemcached($set_type);
			
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
	
	function op_deletememcached(){
		
		$host = $_POST['host'];
		$res =$this->mCacheObj->deleteMemcached($host);
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
	
	function view_packet(){
		global $tpl;	
		$items = $this->mSettingObj->getSettings('packet');
		$tpl->assign('set_type','packet');
		$sets= array();
		if(is_array($items)){
			foreach($items as $item){
				$sets[$item['name']] = $item['value'];
			}
		}
		
		$tpl->assign('sets',$sets);
	}
	
	function view_page(){
		global $tpl;	
		$rules = $this->mCacheObj->getPageRule();
		$tpl->assign('set_type','pagerule');
		$tpl->assign('rules',$rules);
	}
	
	function op_savepagerule(){
		unset($_POST['action']);
		unset($_POST['op']);
		
		$set_type = str_replace('"',"",stripslashes($_POST['valu_rulename']));
		$exist = $this->mCacheObj->getPageRuleByName($set_type);
		if(!$exist){
			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
			$res = $this->mCacheObj->savePageRule($set_type);
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
			$msg['m'] = "此规则已经存在!";
			$msg['d'] = 'null';	
		}

		json_output($msg);
	}
	
	function op_deletepagerule(){
		$pagerule = $_POST['pagerule'];
		$res =$this->mCacheObj->deletePageRule($pagerule);
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
	
	function view_getpagerule(){
		$items = $this->mSettingObj->getSettings($_GET['getpagerule']);
		foreach($items as $k=>$v){
			$v['value'] = htmlspecialchars_decode($v['value'],ENT_QUOTES);
			$items[$k] = $v;
		}
		json_output($items);
	}
	
	function op_updatepagerule(){
		
		unset($_POST['action']);
		unset($_POST['op']);
		
		$pagerule= $_POST['edit_pagerule'];
		unset($_POST['edit_pagerule']);
		$set_type = str_replace('"',"",stripslashes($_POST['valu_rulename']));
		
		$exist = $this->mCacheObj->getPageRuleByName($set_type);
		
		if(!$exist || $exist==$pagerule){
			$this->mCacheObj->deletePageRule($pagerule);

			$pair = array();
			foreach ($_POST as $key=>$value){
				$n = substr($key,5);
				$pre = substr($key,0,5);
				$pair[$n][$pre]= $value;
			}	
	
			$res = $this->mCacheObj->savePageRule($set_type);
			
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
	
}
?>