<?php
include_once 'ProjectManage.class.php';
class project {
	function __construct(){
		$this->mProjectObj =  new ProjectManage();
	}
	function view_appdir(){
		$app_name = $_GET['app_name'];
		
		$app_info = $this->mProjectObj->getAppByName($app_name);
		
		$arr['identifier'] = 'id';
		$arr['label'] = 'name';
		
		list_all_dir($app_info['app_dir'],$tree);
		$this->readfolder($tree,$return);
		$arr['items'] = $return;
		
		json_output($arr);
	}
	
	function readfolder($folders,&$return){
		$tmp = array();
		foreach($folders as $f){
			if(isset($f['folders'])&&count($f['folders'])>0) {
				$r = $this->readfolder($f['folders'],$return);
				if(count($r)>0) $f['folders'] = $r ;
				else unset($f['folders']);;
			}else{
				unset($f['folders']);
			}
			$tmp[]['_reference'] = $f['id'];
	
			$return[] = $f;
		}
		return $tmp;
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
		
		$app_info = $this->mProjectObj->getAppByName($_POST['app_name']);
		
		$content .="\n?>";
		$config_file = $app_info['app_dir'] ."/config/newconfig.ini.php";
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
		
		$app_info = $this->mProjectObj ->getAppByName($_POST['app_name']);
		$res = 0;
		$rs1 =0;
		$rs = file_exists($app_info['app_dir']."/config/newconfig.ini.php");
			
		if($rs) {
			if(file_exists($app_info['app_dir']."/config/config.ini.php.bak")){
				unlink($app_info['app_dir']."/config/config.ini.php.bak");
			}
			if(file_exists($app_info['app_dir']."/config/config.ini.php")){
				$rs1= rename($app_info['app_dir']."/config/config.ini.php",$app_info['app_dir']."/config/config.ini.php.bak");
			}else{
				$rs1= 1;
			}
		}
		if($rs1) {
			$res =rename($app_info['app_dir']."/config/newconfig.ini.php",$app_info['app_dir']."/config/config.ini.php");
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
	
	function op_createapp(){
		$app_name = $_POST['app_name'];
		
		$app_root = $_SERVER["DOCUMENT_ROOT"]."/../".$app_name;
		$res = create_dir($app_root);
		create_dir($app_root."/config");
		create_dir($app_root."/controllers/index");
		create_dir($app_root."/models");
		create_dir($app_root."/plugins");
		create_dir($app_root."/tmp");
		create_dir($app_root."/views/index");
		$index_content = file_get_contents(APP_DIR.'/public/install/index.txt');
		$index_class = file_get_contents(APP_DIR.'/public/install/index.class.txt');
		$index_defaults = file_get_contents(APP_DIR.'/public/install/index_defaults.txt');
		file_put_contents($app_root."/index.php",$index_content);
		file_put_contents($app_root."/controllers/index/index.class.php",$index_class);
		file_put_contents($app_root."/views/index/index_defaults.html",$index_defaults);
		
		
		$this->mProjectObj->createApp($app_name,$app_root);
		
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
	
	function op_deleteapp(){
		$app_name = $_POST['app_name'];
		$app_info = $this->mProjectObj->getAppByName($app_name);
		$rs = $this->mProjectObj->deleteApp($app_name);
		$res= 0;
		if($rs){
			$res = del($app_info['app_dir'],true);
		}
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
}
?>