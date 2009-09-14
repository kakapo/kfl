<?php
//****************KFL全局变量|静态变量设置*************************/
if($_SERVER["SERVER_PORT"] == 443)
{
	$preht = "https://";
}
else
{
	$preht = "http://";
}

$dir_name = dirname($_SERVER["SCRIPT_NAME"]);
if($dir_name=="\\") $dir_name ='';
define("BASE_URL", $preht . $_SERVER["HTTP_HOST"] . $dir_name);

define("HOST_URL", $preht . $_SERVER["HTTP_HOST"]);
$GLOBALS ["gSiteInfo"] ["www_site_url"] = BASE_URL;

//支持path_info
if(!isset($_SERVER["PATH_INFO"]))
{
	$GLOBALS['KFL_PATH_INFO'] = "";
}
elseif (empty($_SERVER["PATH_INFO"]))
{
	//iis 不支持 $_SERVER["PATH_INFO"]
	$GLOBALS['KFL_PATH_INFO'] = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
}
else
{
	//	add a slash to the front if it's not already there
	if(substr($_SERVER["PATH_INFO"],0,1) == "/")
		$GLOBALS['KFL_PATH_INFO'] = $_SERVER["PATH_INFO"];
	else
		$GLOBALS['KFL_PATH_INFO'] = "/" . $_SERVER["PATH_INFO"];
}
$GLOBALS['KFL_PATHINFO_ARRAY'] = explode("/", $GLOBALS['KFL_PATH_INFO']);
if(count($GLOBALS['KFL_PATHINFO_ARRAY'])>1){
	$_GET['action'] = $GLOBALS['KFL_PATHINFO_ARRAY'][1];
	$_GET['view'] = $GLOBALS['KFL_PATHINFO_ARRAY'][2];
	foreach ($GLOBALS['KFL_PATHINFO_ARRAY'] as $key=>$value)
	{
		if($key>1){
			$_GET[$value]=$GLOBALS['KFL_PATHINFO_ARRAY'][$key+1];
		}
	}
}
define("WEB_URL", $preht . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"] . $GLOBALS['KFL_PATH_INFO']);

//过滤post和get变量
foreach ($_POST as $k=>$v){
	if(is_array($v)){
		foreach($v as $k1=>$v1){
			if (!get_magic_quotes_gpc()) $v[$k1] = addslashes($v1);
		}
		$_POST[$k] = $v;
	}else{
		if (!get_magic_quotes_gpc()) $_POST[$k] = addslashes($v);
	}
}
foreach ($_GET as $k=>$v){
	$_GET[$k] =  isset($v)?strtok($v, " \t\r\n\0\x0B"):'';
}


// assign dispatcher
if($_SERVER['REQUEST_METHOD']=='GET'){
	if(isset($_GET['action'])) $GLOBALS['gDispatcher'] = $_GET['action'];
}elseif ($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['action'])) $GLOBALS['gDispatcher'] = $_POST['action'];
}


//***********************KFL错误异常处理******************************/

if(php_sapi_name() != "cli")
{
	if (APP_STATUS == "dev"){
		set_error_handler('error_debug_handler');

	}else if(APP_STATUS == "online"){
		set_error_handler('error_live_handler');
	}

}else{
	set_error_handler('error_debug_handler');
}
function error_debug_handler($errno, $errstr, $errfile, $errline, $context, $backtrace = null)
{
	$type = get_error_type($errno);
	if($type == 'Unknown')
	return;

	$basedir = dirname(dirname(__file__));
	$errfile = str_replace($basedir, "", $errfile);

	if(php_sapi_name() != "cli")
	{
		echo "<table style='font-size: 12px;color:#333399' cellpadding=\"0\" cellspacing='0' border='0'>";
		echo "<caption><span style='color: #FF1111;'>$type:</span>&nbsp; \"" . nl2br(htmlspecialchars($errstr)) . "\" in file $errfile (on line $errline)</caption>";
		echo "<tr><td>";
	}
	else
	{
		echo("$errstr\r\n");
	}

	if ($backtrace === null)
	{
		echo fetch_backtrace();
	}
	else
	{
		echo $backtrace;
	}
	echo "<br />";
	echo get_misc_error_info();
	if(php_sapi_name() != "cli")
	{
		echo "</td></tr></table>";
	}
}

function get_error_type($errno)
{
	switch($errno)
	{
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$errortype = "User Fatal Error";
			break;

		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			$errortype = "User Warning";
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$errortype = "User Notice";
			break;

		default:
			$errortype = "Unknown";
	}

	return $errortype;
}

function add_misc_error_info()
{
	$info = array();
	if(php_sapi_name() != "cli")
	{
		$info["_SERVER"][] = "REMOTE_ADDR";
		$info["_SERVER"][] = "HTTP_USER_AGENT";
		$info["_SERVER"][] = "HTTP_REFERER";
		$info["_SERVER"][] = "HTTP_COOKIE";
		$info["_SERVER"][] = "REQUEST_URI";
	}
	$info['sGlobals'] = 1;
	$info['sUrls'] = 1;

	return $info;
}

function get_misc_error_info()
{
	$info = add_misc_error_info();

	$ret = "<table cellpadding='2' cellspacing='1' border='0' style='background-color: #ccc;'>";
	$ret .= "<tr style='background-color:#fff'><th>Variable</th><th>Value</th></tr>\n";

	foreach ($info as $var => $vals)
	{
		if (!isset($$var))
		{
			global $$var;
		}
		if(!isset($$var))
		{
			//skip it if it isn't yet defined...
			continue;
		}
		if (is_array($$var))
		{
			$src =& $$var;
			if(is_array($vals))
			{
				foreach ($vals as $keyname)
				{

					$ret .= "<tr style='background-color:#fff'><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . (isset($src[$keyname]) ? $src[$keyname] : "unset") . "</td></tr>\n";
				}
			}
			else
			{
				foreach($src as $keyname => $val)
				{
					$ret .= "<tr style='background-color:#fff'><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . $src[$keyname] . "</td></tr>\n";
				}
			}
		}
		elseif (is_object($$var))
		{
			$src =& $$var;
			$values = get_object_vars($$var);
			foreach ($values as $name => $value)
			{
				$ret .= "<tr style='background-color:#fff'><td>\n\t{$var}->{$name}</td><td>\n\t{$value}</td></tr>\n";
			}
		}
	}
	list ( $usec, $sec ) = explode ( " ", microtime () );
	$exec_time =  (( float ) $usec + ( float ) $sec);
	$time_usage = $exec_time - $GLOBALS['gAppStartTime'] ;
	$ret .= "<tr style='background-color:#fff'><td>\n\tMemory Usage</td><td>\n\t" . round(memory_get_usage()/1024,2). " k bytes</td></tr>\n";
	$ret .= "<tr style='background-color:#fff'><td>\n\tTime Usage</td><td>\n\t" . $time_usage. " second</td></tr>\n";
	$ret .= "<tr style='background-color:#fff'><td>\n\tNow</td><td>\n\t" . date("r") . "</td></tr>\n";
	$ret .= "<tr style='background-color:#fff'><td>\n\tIP</td><td>\n\t" . getip() . "</td></tr>\n";
	$ret .= "<tr style='background-color:#fff'><td>\n\tURL</td><td>\n\t" . WEB_URL. "</td></tr>\n";
	$ret .= "</table>";
	return $ret;
}

function error_live_handler($errno, $errmsg, $filename, $linenum, $vars)
{
	echo $errno;
	// 错误发生时间
	$dt = date("Y-m-d H:i:s (T)");

	//定义错误类型为一个数组
	$errortype = array (
	E_ERROR              => 'Error(代码严重错误)',
	E_WARNING            => 'Warning(代码警告)',
	E_PARSE              => 'Parsing Error(代码格式错误)',
	E_NOTICE             => 'Notice(代码建议提示)',
	E_CORE_ERROR         => 'Core Error(PHP内核错误)',
	E_CORE_WARNING       => 'Core Warning(PHP内核警告)',
	E_COMPILE_ERROR      => 'Compile Error(编译错误)',
	E_COMPILE_WARNING    => 'Compile Warning(编译警告)',
	E_USER_ERROR         => 'User Error(用户错误)',
	E_USER_WARNING       => 'User Warning(用户警告)',
	E_USER_NOTICE        => 'User Notice(用户提示)',
	E_STRICT             => 'Runtime Notice',
	E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
	);

	// 哪一类型的错误会被写入日志
	$log_errors = array(E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE,E_RECOVERABLE_ERROR,E_STRICT);
	// 哪一类型的错误会被发送邮件到开发人员
	$devloper_errors =array(E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR,E_COMPILE_WARNING,E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE,E_RECOVERABLE_ERROR,E_STRICT);
	// 哪一类型的错误会被呈现到用户面前
	$display_user_errors=array(E_NOTICE,E_ERROR,E_WARNING,E_PARSE,E_USER_ERROR);

	if(php_sapi_name() != "cli") {
		$host = $_SERVER["SERVER_ADDR"];
	} else {
		$host = '127.0.0.1';
		define('WEB_URL', $_SERVER["SCRIPT_NAME"]);
	}
	$num="";
	$tmp="";
	list($tmp, $tmp, $tmp, $num) = explode(".", $host);
	$crcno = sprintf("%u", crc32($linenum.$filename.$errmsg));
	$errNum = $num .'.'.$crcno;	// Get a unique error ID for this
	
	if(in_array($errno, $log_errors)) {
		$error_exit = is_error_in_log($crcno);
		
		if($error_exit<1){
				
			$brief_message = "<strong>ErrorNo: #$errNum:</strong>
						<p style='font-size: 15px;'>
					   <span style='color: #FF1111;'> 发生时间: </span> $dt<br />
						<span style='color: #FF1111;'> $errortype[$errno] </span>
						&nbsp;
						\"" . htmlspecialchars($errmsg) . "\" in file $filename (on line $linenum)<br />
						<span style='color: #FF1111;'> URL: </span> \"" . VIRTUAL_URL . "\"
						</p>";
			$backtrace_msg = "
						<span id=\"$errNum\"> " ."<br>". fetch_backtrace() . "<br />" . get_misc_error_info() . "
						</span>
						";
				
			//Do not log repeated messages
		 	error_log($crcno."\n", 3, LOG_FILE_DIR."/ignore_repeated_errors.txt");
		 	
		 	// log error backtrace in database
		 	if(isset($GLOBALS ['gDataBase'] ['db_setting.db3'])){
		 		if(!class_exists("Database")) require_once("Libs/Database.class.php");
			 	$db = Model::dbConnect($GLOBALS ['gDataBase'] ['db_setting.db3']);
			 	if(!$db->getOne("select error_no from errorlog where error_no='$errNum'")){
			 		$db->execute("insert into errorlog (error_no,linenum,filename,errmsg,backtrace_msg) values ('$errNum','$linenum','$filename','".htmlspecialchars($errmsg,ENT_QUOTES)."','".htmlspecialchars($backtrace_msg,ENT_QUOTES)."')");
			 	}
		 	}
		 	//send email to notice
		 	if(isset($GLOBALS['gLog']['sendemail']) && $GLOBALS['gLog']['sendemail']==1){
		 		send_email($GLOBALS['gEmail']['smtp_from'],$GLOBALS['gLog']['receiver'],$GLOBALS['gLog']['subject'],$brief_message.$backtrace_msg);
		 	}
	 	}
	}

	if (in_array($errno, $display_user_errors)) {
		
		//header("location: http://image.guodong.dev2/500.html");
		//echo "<script>window.location.replace('http://image.guodong.dev2/500.html');</script>";
		$message = "
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
		<meta http-equiv='pragma' content='no-cache' />
		<meta HTTP-EQUIV='cache-control' content='no-cache'>
		<p style='font-size: 12px;'>
		<strong>该应用发生错误。 错误代号： #$errNum. <a href='mailto:".$GLOBALS['gEmail']['smtp_from']."?subject=errorNum:".$errNum."&body=Thank You!'>通知管理员。</strong>
		</p>
		";
		echo $message;
		die;
		//echo $message;
	}

}

function fetch_backtrace($full = false)
{
	if (function_exists("debug_backtrace"))
	{
		$trace = debug_backtrace();

		$basedir = dirname(dirname(__file__));
		$backtrace = sprintf("%30s%7s\t%-50s\r\n", "FILE:", "LINE:", "FUNCTION:");
		if(php_sapi_name() != "cli")
		{
			$backtrace = "	
			<table cellpadding='2' cellspacing='1' border='0' style='background-color:#ccc'>
			  <tr>
				<th colspan='3' style='text-align: center; color: #333399; padding-right: 5px;'>PHP Backtrace</th>
			  </tr>
			  <tr style='background-color:#fff'>
				<th style='text-align: left; padding-right: 5px;'>File:</th>
				<th style='text-align: left; padding-right: 5px;'>Line:</th>
				<th style='text-align: left; padding-right: 5px;'>Function:</th>
			  </tr>";
		}
		foreach ($trace as $line)
		{
			if (isset($line["file"]))
			{
				$file = str_replace($basedir, "", $line["file"]);
			}
			else
			{
				continue;
			}

			if (isset($line["class"]))
			{
				$func = $line["class"] . $line["type"] . $line["function"];
			}
			else
			{
				$func = $line["function"];
			}

			$arglist = array();
			if (!isset($line["args"]))
			{
				$line["args"] = array();
			}
			foreach ($line["args"] as $arg)
			{
				if (is_object($arg))
				{
					if ($full)
					{
						$arglist[] = "<pre>" . fetch_r($arg) . "</pre>";
						//$arglist[] = var_export($arg, true);
					}
					else
					{
						$arglist[] = "&lt;object&gt;";
					}
				}
				elseif (is_array($arg))
				{
					if ($full)
					{
						//$arglist[] = "<pre>" . fetch_r($arg) . "</pre>";
						$arglist[] = var_export($arg, true);
					}
					else
					{
						$arglist[] = "&lt;array&gt;";
					}
				}
				elseif (is_numeric($arg))
				{
					$arglist[] = $arg;
				}
				else
				{
					$arglist[] = "\"$arg\"";
				}
			}

			$funcargs = "(" . implode(", ", $arglist) . ")";;
			if(php_sapi_name() != "cli")
			{
				$backtrace .= "
				  <tr style='background-color:#fff'>
					<td>$file</td>
					<td>$line[line]</td>
					<td>$func $funcargs</td>
				  </tr>";
			}
			else
			{
				$backtrace .= sprintf("%30s%7d\t%-50s\r\n",$file,$line["line"],"$func $funcargs");
			}
		}
		if(php_sapi_name() != "cli")
		{
			$backtrace .= "</table>";
		}
	}
	else
	{
		$backtrace = "Backtrace not supported in this version of PHP.  You need 4.3.x or better";
	}

	return $backtrace;
}

function is_error_in_log($crcno)
{
	$rows = file(LOG_FILE_DIR."/ignore_repeated_errors.txt");
	
	$flag=0;
	foreach ($rows as $key =>$v) {
		if(trim($v)==$crcno){
			$flag++;
		}
		if($flag>0){
			break;
		}
	}
	return $flag ;
}

//***************************KFL常用方法库 *******************************/
function getmicrotime() {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return (( float ) $usec + ( float ) $sec);
}
function show_message($msg=''){
	echo '<meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS ['gSiteInfo'] ['webcharset'].'" />';
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
/**
 * $encode = authcode('我要加密','ENCODE');
 * $decode = authcode($encode,'DECODE');
 * 
 **/
function authcode($string, $operation, $key = '8106') {
     $key = md5($key ? $key : $GLOBALS['auth_key']);
     $key_length = strlen($key);

     $string = $operation == 'DECODE' ? base64_decode(urldecode($string)) : substr(md5($string.$key), 0, 8).$string;
     $string_length = strlen($string);
     $rndkey = $box = array();
     $result = '';
     for($i = 0; $i <= 255; $i++) {
             $rndkey[$i] = ord($key[$i % $key_length]);
             $box[$i] = $i;
     }

     for($j = $i = 0; $i < 256; $i++) {
             $j = ($j + $box[$i] + $rndkey[$i]) % 256;
             $tmp = $box[$i];
             $box[$i] = $box[$j];
             $box[$j] = $tmp;
     }

     for($a = $j = $i = 0; $i < $string_length; $i++) {
             $a = ($a + 1) % 256;
             $j = ($j + $box[$a]) % 256;
             $tmp = $box[$a];
             $box[$a] = $box[$j];
             $box[$j] = $tmp;
             $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
     }

     if($operation == 'DECODE') {
             if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
                 return substr($result, 8);
             } else {
                 return '';
             }
     } else {
             return urlencode(str_replace('=', '', base64_encode($result)));
     }
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
	if(!empty($_COOKIE['IDOL_TOKEN']) && !empty($_COOKIE['IDOL_STATE']) && !empty($_COOKIE['IDOL_INFO'])){
		$token = $_COOKIE['IDOL_TOKEN'];
		$state_txt = urldecode($_COOKIE['IDOL_STATE']);
		$enc_info = $_COOKIE['IDOL_INFO'];
		list($login_time,$user_name,$key,,$rand_str) = explode('|',$state_txt);
		if($key==md5($user_name.$token.$login_time.$rand_str)){

			$userinfo = decrypt($enc_info,$key);
			//$user_name,$user_id,$user_nickname,$user_unique_id,$user_rank,$user_host
			$user = json_decode($userinfo);//explode('|',$userinfo);
			return $user;
		}else{
			return false;
		}
	}else{
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

function send_email($from="no-reply@guodong.com",  $to, $subject, $message)
{
	/*  your configuration here  */
	$subject= mb_convert_encoding($subject,"gb2312","utf-8");
	$message= mb_convert_encoding($message,"gb2312","utf-8");

	$smtpServer = $GLOBALS['gEmail']['smtp_host']; //ip accepted as well
	$port = "25"; // should be 25 by default
	$timeout = "30"; //typical timeout. try 45 for slow servers
	$username = $GLOBALS['gEmail']['smtp_account'];//"no-reply@guodong.com"; //the login for your smtp
	$password = $GLOBALS['gEmail']['smtp_pass'];//"tsong-0810"; //the pass for your smtp
	$localhost = "127.0.0.1"; //this seems to work always
	$newLine = "\r\n"; //var just for nelines in MS
	$secure = 0; //change to 1 if you need a secure connect

	//connect to the host and port
	$smtpConnect = @fsockopen($smtpServer, $port, $errno, $errstr, $timeout);
	if(!$smtpConnect)
	{
		$output = "Failed to connect: $smtpConnect";
		return 2;
	}
	else
	{
		$logArray['connection'] = "Connected to: $smtpConnect";
	}
	$smtpResponse = @fgets($smtpConnect, 4096);
	//say HELO to our little friend
	@fputs($smtpConnect, "HELO $smtpServer". $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['heloresponse'] = "$smtpResponse";

	//start a tls session if needed
	if($secure)
	{
		@fputs($smtpConnect, "STARTTLS". $newLine);
		$smtpResponse = @fgets($smtpConnect, 4096);
		$logArray['tlsresponse'] = "$smtpResponse";

		//you have to say HELO again after TLS is started
		@fputs($smtpConnect, "HELO $smtpServer". $newLine);
		$smtpResponse = @fgets($smtpConnect, 4096);
		$logArray['heloresponse2'] = "$smtpResponse";
	}

	//request for auth login
	@fputs($smtpConnect,"AUTH LOGIN" . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['authrequest'] = "$smtpResponse";

	//send the username
	@fputs($smtpConnect, base64_encode($username) . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['authusername'] = "$smtpResponse";

	//send the password
	@fputs($smtpConnect, base64_encode($password) . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['authpassword'] = "$smtpResponse";

	//email from
	
	@fputs($smtpConnect, "MAIL FROM: <$from>" . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['mailfromresponse'] = "$smtpResponse";

	//email to
	if(is_array($to)){
		foreach ($to as $key => $v) {
			@fputs($smtpConnect, "RCPT TO: <$v>" . $newLine);
			$smtpResponse = @fgets($smtpConnect, 4096);
			$logArray['mailtoresponse'][] = "$smtpResponse";
		}
	}else{
	 @fputs($smtpConnect, "RCPT TO: <$to>" . $newLine);
	 $smtpResponse = @fgets($smtpConnect, 4096);
	 $logArray['mailtoresponse'] = "$smtpResponse";
	}
	//the email
	@fputs($smtpConnect, "DATA" . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['data1response'] = "$smtpResponse";

	//construct headers
	$headers = "MIME-Version: 1.0" . $newLine;
	$headers .= "Content-type: text/html; charset=gb2312" . $newLine;
	if(is_array($to)){
		for($i=1;$i<count($to);$i++){
			$headers .= "To:  <".$to[$i].">" . $newLine;
		}

	}else{
		// $headers .= "To:s <".$to.">" . $newLine;
	}
	// $headers .= "From:  <$from>" . $newLine;
	if(is_array($to)){
	 	//observe the . after the newline, it signals the end of message
		@fputs($smtpConnect, "To: ".$to[0]."\r\nFrom: $from\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");
		$smtpResponse = @fgets($smtpConnect, 4096);
		$logArray['data2response'][] = "$smtpResponse";
	}else{
		@fputs($smtpConnect, "To: ".$to."\r\nFrom: $from\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");
		$smtpResponse = @fgets($smtpConnect, 4096);
		$logArray['data2response'][] = "$smtpResponse";
	}

	// say goodbye
	@fputs($smtpConnect,"QUIT" . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['quitresponse'] = "$smtpResponse";
	$logArray['quitcode'] = substr($smtpResponse,0,3);
	@fclose($smtpConnect);
	//print_r($logArray);
	//a return value of 221 in $retVal["quitcode"] is a success
	return 1;
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
	die;
}
?>