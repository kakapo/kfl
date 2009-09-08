<?php
class index {
	function view_login(){
		
	}
	function op_dologin(){
		if($_POST['name']==$GLOBALS['config']['admin'] && $_POST['password']==$GLOBALS['config']['password']){
			$_SESSION['login']=1;
			redirect("/index.php/index/defaults");
		}else{
			//show_message_goback("用户名或者密码不正确！");
		}
		
	}
	function view_logout(){
		unset($_SESSION['login']);
		redirect("/index.php/index/login");
		
	}
	function view_defaults(){
		global $tpl;
		$this->is_login();
		include_once 'ProjectManage.class.php';
		$projectObj = new ProjectManage();
		$app_arr = $projectObj->getAppList();
		foreach($app_arr as $k=>$v){
			$app_arr[$k]['app_dir'] = urlencode($v['app_dir']);
		}
		$app = $projectObj->getLastApp();
	
		$app_name = isset($app['app_name'])?$app['app_name']:'';
		$app_dir = isset($app['app_dir'])?urlencode($app['app_dir']):'';
		$tpl->assign("app_name",$app_name);
		$tpl->assign("app_dir",$app_dir);
		$tpl->assign("project_json",json_encode($app_arr));

	
	}
	function is_login(){
		if(!(isset($_SESSION['login'])&&$_SESSION['login']==1)){
			redirect("/index.php/index/login");
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