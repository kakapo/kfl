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
		include_once 'CacheManage.class.php';
		$this->mSettingObj = new SettingManage();	
		$this->mCacheObj = new CacheManage();	
			
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
			$content .= '$GLOBALS ["gDataBase"] ["'.$db['dbname'].'"] = ';
			$sets = $this->mSettingObj->getSettings($db['dbname']);
			$db_info = array();
			foreach($sets as $v){
				$db_info[$v['name']] = $v['value'];
			}		
			$tmp = var_export($db_info,true);
			$tmp = preg_replace("/(.*?)'(APP_[a-zA-Z\.\/\"]+)'(.*?)/ism",'\\1\\2\\3',$tmp);
			$content .= $tmp.';'.$nt;
		}
		
		$content .='
//////////////////////////////////////////////////////
//				Memcached  Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;	
		$memcached = $this->mCacheObj->getMemcached();
		foreach($memcached as $memdb){	
			$content .= '$GLOBALS ["gMemcacheServer"] ["'.$memdb['host'].'"] = ';
			$sets = $this->mSettingObj->getSettings($memdb['host']);
			$info = array();
			foreach($sets as $v){
				$info[$v['name']] = $v['value'];
			}
			
			$content .= var_export($info,true).';'.$nt;
		}
		
		$content .='
//////////////////////////////////////////////////////
//				Packet   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;	
		$array = $this->mSettingObj->getSettings('packet');
		
		$packet = array();
		foreach ($array as $v){
			if($v['name']=='cacheServer'){
				$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).')';
			}
			$content .= '$GLOBALS ["packet"] ["'.$v['name'].'"] =  '.$v['value'].';'.$nt; 
		}
		
		
		
		$content .='
//////////////////////////////////////////////////////
//				PageCache  Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;		
		
		$pagerules = $this->mCacheObj->getPageRule();
		foreach($pagerules as $rule){	
			$content .= '$GLOBALS ["pagecache"] ["'.$rule['rule_name'].'"] = ';
			$sets = $this->mSettingObj->getSettings($rule['rule_name']);
			$info = array();
			foreach($sets as $v){
				if($v['name']=='cacheserver'){
					$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).');';
				}
				$content .= '$GLOBALS ["pagecache"] ["'.$rule['rule_name'].'"] ["'.$v['name'].'"]=  '.$v['value'].';'.$nt; 
			}
		}		
		
		$content .='
//////////////////////////////////////////////////////
//				Session   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;	
		$array = $this->mSettingObj->getSettings('basicset');
		
		$packet = array();
		foreach ($array as $v){
			if($v['name']=='memcached'){
				$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).');';
			}			
			if($v['name']=='database'){
				$v['value'] = preg_replace("/(\"[a-z_0-9]+\")/ism",'$GLOBALS ["gDataBase"][\\1]',$v['value']);
			}
			
			$content .= '$GLOBALS ["session"] ["'.$v['name'].'"] =  '.$v['value'].';'.$nt; 
		}		
		
		$content .='
//////////////////////////////////////////////////////
//				Log   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('noticeset');
		foreach ($array as $v){
			$content .= '$GLOBALS ["log"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
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
	
	function op_copyconfig(){
		$res = 0;
		$rs1 =0;
		if(file_exists(APP_DIR."/config/config.ini.php.bak")){
			$rs= unlink(APP_DIR."/config/config.ini.php.bak");
		}else{
			$rs= 1;
		}
		if($rs) {
			$rs1= rename(APP_DIR."/config/config.ini.php",APP_DIR."/config/config.ini.php.bak");
		}
		if($rs1) {
			$res =rename(APP_DIR."/config/newconfig.ini.php",APP_DIR."/config/config.ini.php");
		}
		
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