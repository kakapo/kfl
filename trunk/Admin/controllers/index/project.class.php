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
		if(!is_array($folders)){
			return false;
		}
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
			$v['value'] = htmlspecialchars_decode($v['value']);
			$content .= 'define("'.$v['name'].'", '.$v['value'].');'.$nt; 
		}
		
		
		$content .='
//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
		'.$nt;
		$array = $this->mSettingObj->getSettings('website');
		foreach ($array as $v){
			//$v['value'] = htmlspecialchars_decode($v['value'],ENT_QUOTES);
			$content .= '$GLOBALS ["gSiteInfo"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
		}
		
		$content .='
//////////////////////////////////////////////////////
//				Email   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('email');
		foreach ($array as $v){
			$v['value'] = htmlspecialchars_decode($v['value']);
			$content .= '$GLOBALS ["gEmail"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
		}
		
		$content .='
//////////////////////////////////////////////////////
//				TimeZone   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('timezone');
		foreach ($array as $v){
			$v['value'] = htmlspecialchars_decode($v['value']);
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
				$v['value'] = htmlspecialchars_decode($v['value']);
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
				$v['value'] = htmlspecialchars_decode($v['value']);
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
			$v['value'] = htmlspecialchars_decode($v['value']);
			if($v['name']=='cacheServer'){
				$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).')';
			}
			$content .= '$GLOBALS ["gPacket"] ["'.$v['name'].'"] =  '.$v['value'].';'.$nt; 
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
				$v['value'] = htmlspecialchars_decode($v['value']);
				if($v['name']=='cacheserver'){
					$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).');';
				}
				$content .= '$GLOBALS ["gPageCache"] ["'.$rule['rule_name'].'"] ["'.$v['name'].'"]=  '.$v['value'].';'.$nt; 
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
			$v['value'] = htmlspecialchars_decode($v['value']);
			if($v['name']=='memcached'){
				$v['value'] = "array(".preg_replace("/(\"[0-9\.:]+\")/",'$GLOBALS ["gMemcacheServer"][\\1]',$v['value']).');';
			}			
			if($v['name']=='database'){
				$v['value'] = preg_replace("/(\"[a-z0-9_\.]+\")/ism",'$GLOBALS ["gDataBase"][\\1]',$v['value']);
			}
			
			$content .= '$GLOBALS ["gSession"] ["'.$v['name'].'"] =  '.$v['value'].';'.$nt; 
		}		
		
		$content .='
//////////////////////////////////////////////////////
//				Log   Settings	                //
//////////////////////////////////////////////////////		
		'.$nt;
		$array = $this->mSettingObj->getSettings('noticeset');
		foreach ($array as $v){
			$v['value'] = htmlspecialchars_decode($v['value']);
			$content .= '$GLOBALS ["gLog"] ["'.$v['name'].'"] =  "'.$v['value'].'";'.$nt; 
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
			$msg['m'] = "同步成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "同步失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	
	function op_createapp(){
		
		$app_name = $_POST['app_name'];
		$res = $this->mProjectObj->getAppByName($app_name);
		if(!$res){
			$parent_dir = realpath($_POST['app_dir']);
			
			if(is_dir($parent_dir) && is_writable($parent_dir)){
				
				$app_root = path_clean($parent_dir.'/'.$app_name);
				
				$app_url = $_POST['app_url'];
				if(!is_dir($app_root)){
					$rs1 = create_dir($app_root);
					create_dir($app_root."/config");
					create_dir($app_root."/controllers/index");
					create_dir($app_root."/models");
					create_dir($app_root."/public");
					create_dir($app_root."/plugins");
					create_dir($app_root."/langs/ch");
					create_dir($app_root."/tmp/logs");
					create_dir($app_root."/views/index");
					$index_content = file_get_contents(APP_DIR.'/public/install/index.txt');
					
					copy(APP_DIR.'/public/install/index.txt',$app_root."/index.php");
					copy(APP_DIR.'/public/install/demo.class.txt',$app_root."/controllers/index/demo.class.php");
					copy(APP_DIR.'/public/install/demo_defaults.txt',$app_root."/views/index/demo_defaults.html");
					copy(APP_DIR.'/public/install/DemoManage.class.txt',$app_root."/models/DemoManage.class.php");
					copy(APP_DIR.'/public/install/index.html',$app_root."/tmp/index.html");
					copy(APP_DIR.'/public/install/index.html',$app_root."/tmp/logs/ignore_repeated_errors.txt");
					copy(APP_DIR.'/public/install/index.html',$app_root."/plugins/index.html");
					copy(APP_DIR.'/public/install/index.html',$app_root."/config/index.html");
					copy(APP_DIR.'/public/install/index.html',$app_root."/public/index.html");
					copy(APP_DIR.'/public/install/index.html',$app_root."/langs/ch/index.html");
					copy(APP_DIR.'/public/install/globals.txt',$app_root."/langs/ch/globals.php");
				}else{
					$rs1=1;
				}
				
				$rs2 =$this->mProjectObj->createApp($app_name,$app_root,$app_url);
				if($rs1 && $rs2){
					$msg['s'] = 200;
					$msg['m'] = "生成成功!";
					$msg['d'] = 'null';	
				}else{
					$msg['s'] = 400;
					$msg['m'] = "生成失败!";
					$msg['d'] = 'null';	
				}
				
			}else{
				$msg['s'] = 400;
				$msg['m'] = "安装路径权限不可写!";
				$msg['d'] = 'null';	
			}
		}else{
			$msg['s'] = 400;
			$msg['m'] = "项目名称已存在!";
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
	
	function view_getallapp(){
		$app_arr = $this->mProjectObj->getAppList();
		foreach($app_arr as $k=>$v){
			$app_arr[$k]['app_dir'] = urlencode($v['app_dir']);
		}
		$arr['identifier'] = 'app_id';
		$arr['label'] = 'app_name';
		$arr['items'] = $app_arr;
		json_output($arr);
	}
	
	function view_dumpfile(){
		global $tpl;
		$file = decrypt($_GET['file']);
		//echo $_GET['file'];
		$types = array("php",'html','htm','js','css','txt','csv','json','bak','cache','xml','htaccess');
		$img_types = array('jpg','jpeg','gif','png');
		if(is_file($file)){
			$type = strtolower(substr(strrchr($file,"."),1));
			
			$filename = basename($file);
			if(in_array($type,$types)){
				highlight_file($file);
			}
			if(in_array($type,$img_types)){
				header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
				echo '<img src="/index.php/project/image?img='.urlencode($_GET['file']).'">';				
			}
			die;
		}elseif($file!='' && is_dir($file)){
			$arr = list_dir($file);
			foreach ($arr as $k=>$v){
				$v['path'] = urlencode(encrypt($v['path']));
				$arr[$k] = $v;
			}
			$tpl->assign("folders",$arr);
		}
		
	}
	
	function view_image(){
		//echo $_GET['img'];
	
		$file = decrypt($_GET['img']);
		//echo $file;die;
		if(is_file($file)){
			$size = getimagesize($file);		
			$fp = fopen($file, "rb");
			if ($size && $fp) {
			  header("Content-type: {$size['mime']}");
			  fpassthru($fp);
			 
			} 
		}
		die;
	}
	
	function view_download(){
		$file = decrypt($_GET['path']);
		//echo $file;die;
		if(is_file($file)){
			//header( "Pragma: public" );
			
			Header("Content-type: ".mime_content_type($file));
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file));
			
			// It will be called downloaded.pdf		
			header('Content-Disposition: attachment; filename="'.basename($file).'"');
			
			// The PDF source is in original.pdf
			readfile($file);
		}
		die();
	}
	
	function op_deletefile(){
		$file = decrypt($_POST['file']);
		if(is_file($file)) $res = unlink($file);
		else $res = del($file,true);
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
	
	function op_pastefile(){
		$file = decrypt($_POST['file']);
		if($_POST['id']=='root'){
			$todir = $_POST['todir'];
		}else{
			$todir = decrypt($_POST['todir']);
		}
	
		$res = 0;
		$tocpfile = '';
		if(is_file($file) && is_dir($todir)){
			$tocpfile  = $todir."/".basename($file);
			if(!file_exists($tocpfile)){
				$res = copy($file,$tocpfile);
			}else{
				$tocpfile = $todir."/copy_".basename($file);
				$res = copy($file,$tocpfile);
			}
		}
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "粘帖成功!$tocpfile";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "粘帖失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	
	function op_newfolder(){
		$newfolder = $_POST['newfolder'];
		if($_POST['id']=='root'){
			$todir = $_POST['todir'];
		}else{
			$todir = decrypt($_POST['todir']);
		}
		$res = 0;
		if(is_dir($todir)){
			$res = mkdir($todir."/".$newfolder,0777);
		}
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "创建文件夹成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "创建文件夹失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	
	function op_renamefile(){
		$newfilename = $_POST['newfilename'];

		$oldfile = decrypt($_POST['oldfile']);
		$res = 0;
		if(is_dir($oldfile) || is_file($oldfile)){
			$res=rename($oldfile,dirname($oldfile)."/".$newfilename);
		}
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "重命名成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "重命名失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	
	function op_uploadfile(){
		$fieldName = "flashUploadFiles";//Filedata";
		if($_POST['id']=='root'){
			$upload_path = urldecode($_POST['path']);
		}else{
			$upload_path = decrypt($_POST['path']);
		}
		
		if( isset($_FILES[$fieldName])){
		
			$returnFlashdata = true; //for dev
			$m = move_uploaded_file($_FILES[$fieldName]['tmp_name'],  $upload_path."/" . addslashes($_FILES[$fieldName]['name']));
			$name = urlencode($_FILES[$fieldName]['name']);
			$file = $upload_path ."/". $name;
			//try{
			  //list($width, $height) = getimagesize($file);
			//} catch(Exception $e){
			  $width=0;
			  $height=0;
			//}
			$type = strtolower(substr(strrchr($file,"."),1));
			//trace("file: " . $file ."  ".$type." ".$width);
			// 		Flash gets a string back:
			$data = '';
			$data .='file='.$file.',name='.$name.',width='.$width.',height='.$height.',type='.$type;
			if($returnFlashdata){	
				// echo sends data to Flash:
				echo($data);
				// return is just to stop the script:
				die;
			}	
		}
	}
	
	function view_stats(){
		$app_name = $_GET['stats'];
		
		$app_info = $this->mProjectObj->getAppByName($app_name);
		
		list_all_dir($app_info['app_dir'],$tree);
		$this->readfolder($tree,$return);
		
		$arr = array('sizes'=>0,"folders"=>0,'files'=>0);
		foreach ($return as $v){
			$arr['sizes'] += $v['size'];
			if($v['filetype']=='dir') $arr['folders']++;
			if($v['filetype']=='file') $arr['files']++;
		}
		$arr['sizes'] = size_unit_convert($arr['sizes']);
		json_output($arr);
		
	}
	
	function op_exportapp(){
		$app_name = $_POST['app_name'];
		$app_info = $this->mProjectObj->getAppByName($app_name);
		$zip = new ZipArchive();
		$filename = APP_TEMP_DIR."$app_name.zip";
		
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			trigger_error("cannot open <$filename>\n",E_USER_ERROR);
		}
		list_all_dir($app_info['app_dir'],$tree);
		
		$this->readfolder($tree,$return);
		$len = strlen($app_info['app_dir']);
		foreach ($return as $v){
		
			if($v['filetype'] =='file'){
				$file = urldecode($v['dir'])."/".$v['name'];
				$re_file = substr($file,$len+1);
				$zip->addFile($file,$re_file);
			}
			
		}
		
		$res = $zip->numFiles;
		$zip->close();
		
		if($res>0){
			$msg['s'] = 200;
			$msg['m'] = "成功!";
			$msg['d'] = "$app_name.zip";	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "失败!";
			$msg['d'] = 'null';	
		}
		json_output($msg);
				
	}
	
	function op_importapp(){
		$app_name = $_POST['app_name'];
		$app_info = $this->mProjectObj->getAppByName($app_name);
		$fieldName = "flashUploadFiles";//Filedata";
		if( isset($_FILES[$fieldName])){
		
			$returnFlashdata = true; //for dev
			$tmp_zip_file = $app_info['app_dir']."/tmp/" . addslashes($_FILES[$fieldName]['name']);
			$m = move_uploaded_file($_FILES[$fieldName]['tmp_name'], $tmp_zip_file );
			if($m){
				$zip = new ZipArchive();
				if ($zip->open($tmp_zip_file) === TRUE) {
				    $zip->extractTo($app_info['app_dir']);
				    $zip->close();
				} 
			}
			
			$name = urlencode($_FILES[$fieldName]['name']);
			$file = $app_info['app_dir']."/tmp/". $name;
			
			$width=0;
			$height=0;
			
			$type = strtolower(substr(strrchr($file,"."),1));
			
			$data = '';
			$data .='file='.$file.',name='.$name.',width='.$width.',height='.$height.',type='.$type;
			if($returnFlashdata){	
			
				echo($data);
				
				die;
			}	
		}
	
	}
	
	function view_getapp(){
		$app_name = $_GET['getapp'];	
		$app_info = $this->mProjectObj->getAppByName($app_name);
		if($app_info){
			$msg['s'] = 200;
			$msg['m'] = "重命名成功!";
			$msg['d'] = $app_info['app_url'];	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "此项目不存在";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
	
	function op_updateapp(){
		$app_name = $_POST['appname'];	
		$app_url = $_POST['new_app_url'];
		$res = $this->mProjectObj->updateApp($app_name,$app_url);
		if($res){
			$msg['s'] = 200;
			$msg['m'] = "修改成功!";
			$msg['d'] = 'null';	
		}else{
			$msg['s'] = 400;
			$msg['m'] = "修改失败";
			$msg['d'] = 'null';	
		}
		json_output($msg);
	}
}
?>