<?php

//***************************KFL常用方法库 *******************************/
function getmicrotime() {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return (( float ) $usec + ( float ) $sec);
}
function show_message($msg=''){
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo "<div style='width:300px; padding:3px; font-size:12px;color:#000; background:#FFF repeat-x left top'>".$msg."</div>";

}

function show_message_goback($msg=''){
	show_message($msg);
	goback();
}

function goback($delay='1000') {
	echo "<SCRIPT>";
	echo 'setTimeout("history.go(-1)",'.$delay.');';
	echo "</SCRIPT>";
	die;
}

function redirect( $URL, $redirectType = 3)
{
	switch($redirectType)
	{
		case 1:
			header("location: $URL");
			break;
		case 2:
			echo("<script language=\"JavaScript\" type=\"text/javascript\"> window.location.href = \"$URL\"; </script>");
			break;
		case 3:
			echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />".'<font style="font-size:12px"> 自动跳转中.....如果浏览器不支持，请点击<a href="'.$URL.'">此处。</a></font><SCRIPT>
       				 setTimeout("window.location.replace(\"'.$URL.'\")",1000);
        		   </SCRIPT>';
			exit();
			break;
		default:
			trigger_error("unknown redirect type");
			break;
	}
	exit();
}
function encrypt($s, $key='key')
{
	$r="";
	for($i=0;$i<strlen($s);$i++){
		$r .= substr(str_shuffle(md5($key)),($i % strlen(md5($key))),1).$s[$i];
	}
	for($i=1;$i<=strlen($r);$i++) {
		$s[$i-1] = chr(ord($r[$i-1])+ord(substr(md5($key),($i % strlen(md5($key)))-1,1)));
	}
	return urlencode(base64_encode($s));
}

function decrypt($s, $key='key')
{
	$r ='';
	$s=base64_decode(urldecode($s));
	for($i=1;$i<=strlen($s);$i++){
		$s[$i-1] = chr(ord($s[$i-1])-ord(substr(md5($key),($i % strlen(md5($key)))-1,1)));
	}
	for($i=1;$i<=strlen($s)-1;$i=$i+2){
		$r .= $s[$i];
	}
	return $r;
}

function authenticate(){
	if(!defined('SSO_MODE')) define('SSO_MODE','session');
	if(SSO_MODE=='cookie'){
		if(!empty($_COOKIE['XPPASS_TOKEN']) && !empty($_COOKIE['XPPASS_STATE']) && !empty($_COOKIE['XPPASS_INFO'])){
			$token = $_COOKIE['XPPASS_TOKEN'];
			$state_txt = urldecode($_COOKIE['XPPASS_STATE']);
			$enc_info = $_COOKIE['XPPASS_INFO'];
			list($login_time,$user,$key,,$rand_str) = explode('|',$state_txt);
			
			if($key==md5($user.$token.$login_time.$rand_str)){	
				$userinfo = decrypt($enc_info,$key);
				return json_decode($userinfo,true);
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	if(SSO_MODE=='session' || SSO_MODE=='ticket'){
		if(isset($_SESSION['_XppassOnlineUser'])) 
			return $_SESSION['_XppassOnlineUser'];
		else 
			return false;
	}
}

function selfURL() {
	$s = empty($_SERVER["HTTPS"]) ? ''
	: ($_SERVER["HTTPS"] == "on") ? "s"
	: "";
	$protocol = substr($_SERVER["SERVER_PROTOCOL"],0,strpos($_SERVER["SERVER_PROTOCOL"],  "/")).$s;
	$protocol = strtolower($protocol);
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
	: (":".$_SERVER["SERVER_PORT"]);

	$arrayRequestURI = array();
	if(isset($_POST)){
		foreach($_POST as $key => $value) {
			$arrayRequestURI[] = "$key=" . $value;
		}
	}
	if(isset($_GET)){
		foreach($_GET as $key => $value) {
			$arrayRequestURI[] = "$key=" . $value;
		}
	}
	$requestURI = "";
	if($arrayRequestURI)
	$requestURI =  "?" . implode("&", $arrayRequestURI);

	return urlencode($protocol."://".$_SERVER['HTTP_HOST']. $port . $_SERVER['PHP_SELF'] . $requestURI);
}

function lang($lang_key, $force = true) {
	return isset($GLOBALS['gLang'][$lang_key]) ? $GLOBALS['gLang'][$lang_key] : ($force ? $lang_key : '');
}
function getip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return($ip);
}

function print_sql($show_include_files=0){
	if($show_include_files){
		foreach (get_included_files() as $key=>$value) {
			echo "<li>$key. $value</li>";
		}
	}
	if(isset($GLOBALS['gSqlQuery'])){
		$debugLog = '';
		$debugTime =0;
		foreach($GLOBALS['gSqlQuery'] as $line)
		{
			$debugLog .= sprintf('<li><b>%1.5fs</b> %s<hr size=1 noshadow>',$line[1], "<span style=\"font-family:Tahoma; font-size: 12px;\">{$line[0]}</span>");

			$debugTime += $line[1];
		}
		echo "
		<table cellpadding=0 cellspacing=5 width=100% bgcolor=white>
			<tr>
				<td>{$debugLog} TIMES: ".(float)$debugTime."s QUERY: ".count($GLOBALS['gSqlQuery'])."</td>
		    </tr>
		</table>";
	}else{
		echo 'no query!';
	}
}

function memcache_get_content($servers,$key){
	$memcache= new Memcache;
	if(is_array($servers)){
		foreach ($servers as $server){
			$memcache->addserver($server['host'],$server['port']);
		}
	}
	$host = $memcache->get($key);
	$memcache->close();
	return $host;

}

function memcache_set_content($servers,$key,$value,$lifetime=0){
	$memcache= new Memcache;
	if(is_array($servers)){
		foreach ($servers as $server){
			$memcache->addserver($server['host'],$server['port']);
		}
	}
	//永不过期
	$memcache->set($key,$value,0,$lifetime);
	$memcache->close();
}

function curl_get_content($url){
	if(function_exists('curl_init')){
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		//curl_setopt ( $ch, CURLOPT_TIMEOUT, 1 );
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		$result = curl_exec ( $ch );

		if(!curl_errno($ch) && $http_code=='200')
		{
			curl_close ( $ch );
			return $result;
		}else{
			return false;
		}

	}else{
		return false;
	}
}

function json_output($arr){
	echo json_encode($arr);
	//echo preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $code);
	
}

function hmac($key, $data, $hash="md5") {
    // RFC 2104 HMAC implementation for php. Hacked by Lance Rushing
    $b = 64;
    if (strlen($key) > $b)
        $key = pack("H*", call_user_func($hash, $key));
     $key = str_pad($key, $b, chr(0x00));
     $ipad = str_pad("", $b, chr(0x36));
     $opad = str_pad("", $b, chr(0x5c));
     $k_ipad = $key ^ $ipad ;
     $k_opad = $key ^ $opad;
    
     return call_user_func($hash, $k_opad . pack("H*", call_user_func($hash, $k_ipad . $data)));
}
/*-------------------------------文件扩展函数-----------------------------------*/

function path_clean($path)
{
	$path = str_replace('://','__SLASH_SLASH_COLON__',$path);
	$path = str_replace('\\','/',$path);
	$path = preg_replace('/\/\.$/','/',str_replace('/./','/',preg_replace('/\/{2,}/','/',$path)));
	$path = str_replace('__SLASH_SLASH_COLON__','://',$path);

	return $path;
}
//作用:模拟 parse_path 但其解决解析带中文文件名路径无法正常获得文件名的问题
//参数:$path 需要解析的路径
//返回:数组 下标分别为 dirname,filename,basename,extension;
function parse_path($path)
{
	$path = path_clean($path);
	$pathinfo = pathinfo($path);
	$pathinfo['filename'] = preg_replace('/^'.preg_quote($pathinfo['dirname'],'/').'\//' ,'', $path);
	$pathinfo['basename'] = preg_replace('/\.'.$pathinfo['extension'].'$/' ,'', $pathinfo['filename']);
	return $pathinfo;
}

//作用:容量单位转换
//参数:$size 容量及单位 b(默认)|kb|mb|gb|tb
//参数:$tounit 输出单位 b|kb|mb|gb|tb|auto 当值为 auto(默认) 的时候按照容量自适应生成
//返回:数值 + 单位
function size_unit_convert($size,$tounit='auto')
{
	preg_match("/([0-9]+)\s*([a-zA-Z]*)/i",$size,$size_array);
	$size = $size_array[1];
	$from_unit = strtolower($size_array[2]);
	$from_unit = $from_unit ? $from_unit : 'b' ;
	$tounit = strtolower($tounit);

	$unit['b'] = 1;
	$unit['kb'] = 1024;
	$unit['mb'] = 1048576;
	$unit['gb'] = 1073741824;
	$unit['tb'] = 1099511627776;
	$size_bit = $size * $unit[$from_unit];

	if($tounit=='auto')
	{
		if(($convert_size = $size_bit) < 1024)
		{
			return round($convert_size,2).' Bytes';
		}

		if(($convert_size = $size_bit/1024) < 1024)
		{
			return round($convert_size,2).' KB';
		}

		if(($convert_size = $size_bit/1048576) < 1024)
		{
			return round($convert_size,2).' MB';
		}

		if(($convert_size = $size_bit/1073741824) < 1024)
		{
			return round($convert_size,2).' GB';
		}

		if(($convert_size = $size_bit/1099511627776) < 1024)
		{
			return round($convert_size).' TB';
		}
	}
	else
	{
		return round($size_bit / $unit[$tounit],2).strtoupper($tounit);
	}
}

//作用:创建目录 可创建路径中包含的所有目录 并将其设置成指定模式
//参数:$path 创建目录路径
//参数:$mode 目录模式(权限)
//返回:true|false
function create_dir($path,$mode = 0777)
{
	preg_match_all('/([^\\\|\/]+)[\\\|\/]*/',$path,$array_dir);
	$os = explode(' ', php_uname());
	if(strtolower($os[0])!='windows')
	{
		$array_dir['1']['0'] = '/'.$array_dir['1']['0'];
	}
	$dir = '';
	foreach($array_dir['1'] as $temp_dir)
	{
		$dir.=$temp_dir.'/';
		if(!file_exists($dir))
		{
			@mkdir($dir,$mode);
		}
	}

	if(!file_exists($path))
	{
		return false;
	}

	return true;
}

//作用:删除目录 扩展函数 可删除目录中所有文件包括目录
//参数:$path 要删除的文件/目录
//参数:$self true(包含指定目录)|false(不包括指定目录只删除指定目录下的)
//参数:$private_level 私有的内部递归参数用于判断是否到顶层
//参数:$types 文件类型，例如.jpg|.gif
//返回:true|false
function del($path, $self = false, $private_level = 0,$types='')
{
	
	$list_dir = list_dir($path);//注此处使用了自定义扩展函数

	if(is_array($list_dir))
	{
		foreach($list_dir as $row)
		{
			if($row['type'] == 'dir')
			{
				if(!del($row['path'],'1',$private_level+1))
				{
					return false;
				}
			}
			else
			{
				if($types==''){
					@unlink($row['path']);
				}else{
					$items = explode("|",$types);
					foreach ($items as $type){
						if(stripos($row['filename'],$type)){
							if(is_file($row['path'])) @unlink($row['path']);
						}
					}
				}
			}
		}
	}
	else
	{
		@unlink($path);
	}

	if($private_level!=0 || $self)
	{
		if(is_dir($path) && !@rmdir($path))
		{
			return false;
		}
	}
	return true;
}


//作用:遍历文件夹
//参数:$path 要遍历的文件夹
//参数:$type 传回文件数组的类型 all|dir|file
//返回:返回文件夹内容数组 | false 文件夹不存在
function list_dir($path,$type = 'all')
{
	$list = array();
	if(!$dir = @dir($path))
	{
		return false;
	}
	$i = 0;
	while (false !== ($filename = $dir->read()))
	{
		if (preg_match("/^(\.{1,2}|\.svn)$/ism",$filename)) {	continue; }

		$fileinfo = stat($dir->path.'/'.$filename);
		$pathinfo = pathinfo($filename);
		$filetype = filetype($dir->path.'/'.$filename);
		//if($filetype == 'dir') list_dir($dir->path,$list[$i]);
		if(($type == 'file' && $filetype == 'dir') && $type != 'all'){	continue;	}
		if(($type == 'dir' && $filetype == 'file') && $type != 'all'){	continue;	}
		$list[$i]['id'] = $filename.uniqid("_");
		$list[$i]['type'] = $filetype;
		$list[$i]['name'] = mb_convert_encoding($filename, "UTF-8", "GBK");
		$list[$i]['basename'] = $pathinfo['basename'];
		$list[$i]['extension'] = isset($pathinfo['extension'])?$pathinfo['extension']:'';
		$list[$i]['time'] = date ("Y-m-d H:i:s", $fileinfo['mtime']);
		$list[$i]['size'] = size_unit_convert($fileinfo['size']);
		if($filetype=='dir') $list[$i]['folders'] = array(array("_reference"=>''));
		$list[$i]['dir'] = path_clean($dir->path);
		$list[$i]['path'] = path_clean($dir->path.'/'.$filename);

		$i++;
	}
	$dir->close();
	//@array_multisort($list,SORT_DESC,$list);//排序 如果搜索全部类型则先列数组
	return $list;
}

function list_all_dir($path,&$tree){
	if(!is_dir($path)) return false;
	if( !$dir = dir($path))
	{
		return false;
	}
	$i = 0;
	
	while (false !== ($filename = $dir->read()))
	{
		$t = array();
		if (preg_match("/^(\.{1,2}|\.svn)$/ism",$filename)) {	continue; }
		$filetype = filetype($dir->path.'/'.$filename);
		
		if($filetype == 'dir'){
			list_all_dir($dir->path.'/'.$filename,$t['folders']);
		}
		$fileinfo = stat($dir->path.'/'.$filename);
		$pathinfo = pathinfo($filename);
		
		$t['path'] = urlencode(encrypt(path_clean($dir->path.'/'.$filename)));
		
		$filename = mb_convert_encoding($filename, "UTF-8", "GBK");
		$t['id'] = $filename.uniqid("_");
		$t['filetype'] = $filetype;
		$t['name'] = $filename;
		
		$t['basename'] = $pathinfo['basename'];
		$t['extension'] = isset($pathinfo['extension'])?$pathinfo['extension']:'';
		$t['time'] = date ("Y-m-d H:i:s", $fileinfo['mtime']);
		$t['size'] = $fileinfo['size'];	
		$t['dir'] = urlencode(path_clean($dir->path));
		
		$tree[$i] = $t;
		$i++;
		@array_multisort($tree, SORT_REGULAR,SORT_DESC   );//排序 如果搜索全部类型则先列数组
	}
	
	$dir->close();
	
}

//作用:检测指定目录大小
//参数:$path 要检测的目录
//返回:返回文件夹所占用的空间的字节数
function dir_size($path)
{
	static $dir_size;
	$list_dir = list_dir($path);

	if(is_array($list_dir))
	{
		foreach($list_dir as $row)
		{
			if($row['type']=='dir')
			{
				dir_size($row['path']);
			}
			else
			{
				$dir_size += filesize($row['path']);
			}
		}
	}

	return $dir_size;
}


//作用:完整读取文件 替代 file_get_contents 的方案 如果 file_get_contents 函数存在则执行 file_get_contents
//参数:$path 要读取的文件路径
//返回:返回读取的文件内容
function read_file($path)
{
	if (!($file = @fopen($path,'rb')))
	{
		return false;
	}
	$content="";
	@flock($file, LOCK_SH);
	while($line=fread($file,2048))
	{
		$content .= $line;
	}

	@flock($file, LOCK_UN);
	fclose($file);

	return $content;
}

//作用:将指定内容写入文件
//参数:$content 要写入的内容
//参数:$path 文件存放路径
//参数:$mode 写入模式 默认 wb
//返回:true|false 是否写入成功
function write_file($content,$path,$mode='wb')
{
	if(!is_dir(dirname($path)))
	{
		@create_dir(dirname($path));
	}

	if(!($fp = fopen($path,$mode)))
	{
		return false;
	}
	if (flock($fp, LOCK_EX)) { // 进行排它型锁定
	    fwrite($fp, $content);
	    flock($fp, LOCK_UN); // 释放锁定
	} else {
		fclose($fp);
	    return false;
	}
	
	fclose($fp);
	//file_put_contents($path,$content,LOCK_EX);
	return true;
}


//作用:复制文件或目录下的所有文件到指定目录
function icopy($path, $dir)
{
	if(!file_exists($path))
	{
		return false;
	}

	$tmpPath = parse_path($path);


	if(!is_dir($path))
	{
		create_dir($dir);
		if(!copy($path, $dir.'/'.$tmpPath['filename']))
		{
			return false;
		}
	}
	else
	{
		create_dir($dir);
		foreach((array)list_dir($path) as $lineArray)
		{
			if($lineArray['type'] == 'dir')
			{
				icopy($lineArray['path'], $dir.'/'.$lineArray['filename']);
			}
			else
			{
				icopy($lineArray['path'], $dir);
			}
		}
	}

	return true;
}

function second_convert($s){
	if($s<0) return $s.'s';
	$hour = floor($s/3600);
	$minute = floor(($s-$hour*3600)/60);
	$second = $s-$hour*3600-$minute*60;
	return $hour.'h '.$minute.'m '.$second.'s ';
}
?>