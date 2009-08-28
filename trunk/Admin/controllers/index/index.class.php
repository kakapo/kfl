<?php
class index {
	function view_login(){
		
	}
	function op_dologin(){
		if($_POST['name']==$GLOBALS['config']['admin'] && $_POST['password']==$GLOBALS['config']['password']){
			$_SESSION['login']=1;
			redirect("/index.php/index/defaults");
		}else{
			show_message_goback("用户名或者密码不正确！");
		}
		
	}
	function view_logout(){
		unset($_SESSION['login']);
		redirect("/index.php/index/login");
		
	}
	function view_defaults(){
		$this->is_login();
	}
	function is_login(){
		if(!(isset($_SESSION['login'])&&$_SESSION['login']==1)){
			redirect("/index.php/index/login");
		}
	}
	function view_phpinfo(){
		phpinfo(INFO_MODULES);
		die;
	}
	function view_dashboard(){
		print_r($_GET);
	}
}