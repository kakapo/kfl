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
		phpinfo();
		die;
	}
	function op_renewconfig(){
		include_once 'SettingManage.class.php';
		include_once 'SettingManage.class.php';
		$this->mSettingObj = new SettingManage();	
			
		$systems = $this->mSettingObj->getSettings('system');
		
		$nt = "\n\t";
		$content = '<?php'.$nt;
		
		$content .= '
//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////'.$nt;
		foreach ($systems as $v){
			$content .= 'define("'.$v['name'].'", '.$v['value'].');'.$nt; 
		}
		
		
		$content .='
//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
		'.$nt;
		$array = $this->mSettingObj->getSettings('website');
		foreach ($array as $v){
			$content .= '$GLOBALS ["gSiteInfo"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
		}
		
		$content .='
//////////////////////////////////////////////////////
//				Email   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('email');
		foreach ($array as $v){
			$content .= '$GLOBALS ["email"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
		}
		
		$content .='
//////////////////////////////////////////////////////
//				TimeZone   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('timezone');
		foreach ($array as $v){
			$content .= 'date_default_timezone_set("'.$v['value'].'");'.$nt; 
		}
		
		
		$content .='
//////////////////////////////////////////////////////
//				Database   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$databases = $this->mSettingObj->getDatabase();
	
		foreach($databases as $db){
			
			$content .= '$GLOBALS ["gDataBase"] ["'.$db['dbname'].'"] = '.$nt;
			$sets = $this->mSettingObj->getSettings($db['dbname']);
			$db_info = array();
			foreach($sets as $v){
				$db_info[$v['name']] = $v['value'];
			}
			
			$content .= var_export($db_info,true).';'.$nt;
		}
		
		$content .='
//////////////////////////////////////////////////////
//				Packet   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;	
		$array = $this->mSettingObj->getSettings('packet');
		foreach ($array as $v){
			$content .= '$GLOBALS ["packet"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
		}
		
		
		$content .="\n?>";
		$config_file = APP_DIR ."/config/newconfig.ini.php";
		$res = file_put_contents($config_file,$content,LOCK_EX);
		
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "生成成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "生成失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	function view_dashboard(){
		print_r($_GET);
	}
}