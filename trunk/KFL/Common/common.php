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
define("SCRIPT_URL", $preht . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]);
define("HOME_URL", dirname(SCRIPT_URL));


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
define("VIRTUAL_URL", SCRIPT_URL . $GLOBALS['KFL_PATH_INFO']);

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
		echo "<table style='font-size: 15px;' cellpadding=\"0\" cellspacing='0' border='0'>";
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

	if(php_sapi_name() != "cli")
	{
		echo "</td></tr></table>";
	}
}

function log_error($msg,$filename,$linenum)
{
	if(!is_dir(LOG_FILE)){
		@create_dir(LOG_FILE);
	}
	$date=date('y-m-d');
	$logFile 	  = LOG_FILE."/".$date."_errors.log.html";

	$fp = @fopen($logFile, "a+");
	@fwrite($fp, $msg);
	@fclose($fp);
	return $linenum;
}

function log_index_word($filename,$linenum)
{
	if(!is_dir(LOG_FILE)){
		@create_dir(LOG_FILE);
	}
	$today = date("Y-m-d");
	$index=$filename."@@@".$linenum."\r\n";

	$logFileIndex = LOG_FILE."/".$today."_errors.log.txt";

	$fp2 = @fopen($logFileIndex, "a+");
	@fwrite($fp2, $index);
	@fclose($fp2);

}

function get_error_type($errno)
{
	switch($errno)
	{
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$errortype = "Fatal Error";
			break;

		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			$errortype = "Warning";
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$errortype = "Notice";
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

	$ret = "<table cellpadding='2' cellspacing='0' border='0' style='background-color: #DDDDDD;'>";
	$ret .= "<tr><th>Variable</th><th>Value</th></tr>\n";

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

					$ret .= "<tr><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . (isset($src[$keyname]) ? $src[$keyname] : "unset") . "</td></tr>\n";
				}
			}
			else
			{
				foreach($src as $keyname => $val)
				{
					$ret .= "<tr><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . $src[$keyname] . "</td></tr>\n";
				}
			}
		}
		elseif (is_object($$var))
		{
			$src =& $$var;
			$values = get_object_vars($$var);
			foreach ($values as $name => $value)
			{
				$ret .= "<tr><td>\n\t{$var}->{$name}</td><td>\n\t{$value}</td></tr>\n";
			}
		}
	}
	$ret .= "<tr><td>\n\tTime</td><td>\n\t" . date("r") . "</td></tr>\n";
	$ret .= "<tr><td>\n\tIP</td><td>\n\t" . GetIP() . "</td></tr>\n";
	$ret .= "</table>";
	return $ret;
}

function error_live_handler($errno, $errmsg, $filename, $linenum, $vars)
{
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
	E_USER_WARNING       => 'User Warning',
	E_USER_NOTICE        => 'User Notice',
	E_STRICT             => 'Runtime Notice',
	E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
	);

	// 哪一类型的错误会被写入日志
	$log_errors = array(E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE,E_RECOVERABLE_ERROR,E_STRICT);
	// 哪一类型的错误会被发送邮件到开发人员
	$devloper_errors =array(E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE,E_RECOVERABLE_ERROR,E_STRICT);
	// 哪一类型的错误会被发送邮件到系统维护人员
	$systemer_errors = array(E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR,E_COMPILE_WARNING);
	// 哪一类型的错误会被呈现到用户面前
	$display_user_errors=array(E_ERROR,E_PARSE,E_USER_ERROR);

	$num2="";
	$tmp="";
	$error_exit = check_word($filename, $linenum);
	if (in_array($errno, $log_errors)) {

		if(php_sapi_name() != "cli") {
			$host = $_SERVER["SERVER_ADDR"];
		} else {
			$host = '127.0.0.1';
			define('VIRTUAL_URL', $_SERVER["SCRIPT_NAME"]);
		}
		list($tmp, $tmp, $tmp, $num2) = explode(".", $host);
		$errNum = uniqid($num2 . ".");	// Get a unique error ID for this
		$message = "<strong>Error #$errNum:</strong>
					<p style='font-size: 15px;'>
				   <span style='color: #FF1111;'> 发生时间: </span> $dt<br />
					<span style='color: #FF1111;'> $errortype[$errno] </span>
					&nbsp;
					\"" . htmlspecialchars($errmsg) . "\" in file $filename (on line $linenum)<br />
					<span style='color: #FF1111;'> URL: </span> &nbsp; \"" . VIRTUAL_URL . "\"
					</p>

					<span id=\"$errNum\"> " ."<br>". fetch_backtrace() . "<br />" . get_misc_error_info() . "
					</span>
					<hr width='75%' />";

		//如果没有发送过邮件，则发送并写入日志

	 if($error_exit<1){

	 	$dvp	 =$GLOBALS['log']['devlopers'];
	 	$subject =$GLOBALS['log']['subject'];
	 	//send_multymail($dvp,$subject,$message,"no-reply@guodong.com");
	 	send_email("no-reply@guodong.com",$dvp,$subject,$message);
	 	if($error_exit<2){
	 		log_index_word($filename, $linenum);
	 	}
	 	if (in_array($errno, $systemer_errors)) {
	 		//send_multymail($GLOBALS['log']['systemers'],$subject ,$message,"no-reply@guodong.com");
	 		send_email("no-reply@guodong.com",$GLOBALS['log']['systemers'],$subject,$message);
	 	}
	 }
	}
	if($error_exit==1 ){
		log_error($message,$filename,$linenum);
	}
	if (in_array($errno, $display_user_errors)) {
		//header("location: http://image.guodong.dev2/500.html");
		echo "<script>window.location.replace('http://image.guodong.dev2/500.html');</script>";
		$message = "
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
		<meta http-equiv='pragma' content='no-cache' />
		<meta HTTP-EQUIV='cache-control' content='no-cache'>
		<p style='font-size: 12px;'>
		<strong>): 对不起！此页面发生了错误。 错误代码： #$errNum. <a href='http://www.guodong.com/index.php?action=help&view=kfzx'>联系客服MM。</strong>
		</p>
		";
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
			<style type='text/css'>
body, td, th {font-family: sans-serif;}
table {border-collapse: collapse;}
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
</style>
			<table cellpadding='2' cellspacing='0' border='0' style='background-color: #DDDDDD;'>
			  <tr>
				<th colspan='3' style='text-align: center; color: #333399; padding-right: 5px;'>PHP Backtrace</th>
			  </tr>
			  <tr>
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
				  <tr>
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

function check_word($filename,$linenum)
{
	$today = date("Y-m-d");
	$logFileIndex = LOG_FILE."/".$today."_errors.log.txt";
	$fp = @fopen($logFileIndex,"r");
	$contents = @fread($fp, filesize ($logFileIndex));
	@fclose($fp);
	$indexWord = explode("\r\n", $contents);
	$flag=0;
	foreach ($indexWord as $key =>$v) {
		if($v==$filename."@@@".$linenum){
			$flag++;
		}
		if($flag==3){
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
	echo "<div style='width:300px; margin:auto; padding:3px; font-size:12px;color:#000; background:#FFF repeat-x left top'>".$msg."</div>";

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
			echo ' <SCRIPT>
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

	$smtpServer = $GLOBALS['log']['smtp_host']; //ip accepted as well
	$port = "25"; // should be 25 by default
	$timeout = "30"; //typical timeout. try 45 for slow servers
	$username = $GLOBALS['log']['smtp_account'];//"no-reply@guodong.com"; //the login for your smtp
	$password = $GLOBALS['log']['smtp_pass'];//"tsong-0810"; //the pass for your smtp
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
	@fputs($smtpConnect, "MAIL FROM: $from" . $newLine);
	$smtpResponse = @fgets($smtpConnect, 4096);
	$logArray['mailfromresponse'] = "$smtpResponse";

	//email to
	if(is_array($to)){
		foreach ($to as $key => $v) {
			@fputs($smtpConnect, "RCPT TO: $v" . $newLine);
			$smtpResponse = @fgets($smtpConnect, 4096);
			$logArray['mailtoresponse'][] = "$smtpResponse";
		}
	}else{
	 @fputs($smtpConnect, "RCPT TO: $to" . $newLine);
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
?>