<?php
class index {
	function view_login(){
		
	}
	function op_dologin(){
		if($_POST['name']==$GLOBALS['config']['admin'] && $_POST['password']==$GLOBALS['config']['password']){
			$_SESSION['login']=1;
			redirect($GLOBALS ["gSiteInfo"] ["www_site_url"]."/index.php/index/defaults");
		}else{
			show_message_goback("用户名或者密码不正确！");
		}
		
	}
	function view_logout(){
		unset($_SESSION['login']);
		redirect($GLOBALS ["gSiteInfo"] ["www_site_url"]."/index.php/index/login");
		
	}
	function view_defaults(){
		global $tpl;
		$this->is_login();
		$path = realpath(APP_DIR."/../");
		
		$dirs = list_dir($path,'dir');
		$new_dirs[] = array('path'=>$path,'path_encode'=>urlencode($path),'is_writable'=>is_writable($path));
		if(is_array($dirs)){
			foreach($dirs as $d){
				$t = array();
				if(strtolower(substr($d['path'],-3))=='kfl') continue;		
				$t['path']=$d['path'];
				$t['path_encode']= urlencode($d['path']);
				$new_dirs[] =$t;
				
			}
		}
		
		$tpl->assign('dirs',$new_dirs);
	}
	function is_login(){
		if(!(isset($_SESSION['login'])&&$_SESSION['login']==1)){
			redirect($GLOBALS ["gSiteInfo"] ["www_site_url"]."/index.php/index/login");
		}
	}
	function view_phpinfo(){
		phpinfo();
		die;
	}
	function view_dashboard(){
		print_r($_GET);
	}
}