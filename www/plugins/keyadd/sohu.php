<?php

//error_reporting(8);
require_once (APP_DIR . "/plugins/export/export_function.php");
$location = "";
$cookiearr = array ();
$username = $_POST ["username"];
$password = $_POST ["password"];

echo (insert_sohu_blog ( $username, $password ));

/**
 * 一键嵌入SOHu全过程，返回是否成功的状态字符串
 *
 * @param string $login
 * @param string $password
 * @return 返回是否成功的状态字符串
 */
function insert_sohu_blog($username, $password ) {

	#the globals will be updated/used in the read_header function
	global $location;
	global $cookiearr;
	global $ch;

	$password = md5 ( $password );
	//$htmlBoxTitle = iconv ( "utf-8", "gb2312", "我的".$_POST ['type'] )
	if(ini_get("magic_quotes_gpc")=="1")
	{
		$htmlBoxContent=stripslashes($_POST ["flash"]);
	}
	else
	{
		$htmlBoxContent = $_POST ["flash"];
	}

	$cookie_file = APP_TEMP_DIR . "/" . $username . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();

	//$action="http://passport.sohu.com/sso/login.jsp?userid=".$login."%40sohu.com&password=".$password."&appid=9998&persistentcookie=1&s=".time()."111&b=1&w=1024&pwdtype=1";
	$action = "http://passport.sohu.com/sso/login.jsp?userid=" . urlencode ( $username ) . "&password=" . $password . "&appid=9998&persistentcookie=0&s=" . time () . generateCode ( 3 ) . "&b=1&w=1024&pwdtype=1";

	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_HEADERFUNCTION, 'read_header' );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );

	if (preg_match ( "/error/", $html ))
	return "no"; //username or password error }


	$cookie = "";
	if(is_array($cookiearr)){
		foreach ( $cookiearr as $key => $value ) {
			$cookie .= $key . "=" . $value . "; ";
		}
	}

	$params = /*"name=" . urlencode ( $htmlBoxTitle ) . "&desc=" . urlencode ( $htmlBoxTitle ) . */"detailDesc=" . urlencode ( $htmlBoxContent ) . "&m=update&submit=%B1%A3%B4%E6";
	curl_setopt ( $ch, CURLOPT_URL, "http://blog.sohu.com/manage/setting.do" );
	curl_setopt ( $ch, CURLOPT_COOKIE, $cookie );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
	$html = curl_exec ( $ch );
	curl_close ( $ch );

	$url = "";
	$re = get_headerArrByString ( $html );
	$cookie = $re ['cookie'];
	$ppnewsinfo = $cookie ['ppnewsinfo'];
	$temp = explode ( "|", $ppnewsinfo );
	$user = explode('@',$username);
	if (! empty ( $temp )) {
		$url = "http://" . $temp [2] . ".blog.sohu.com/";
	} else {
		$url = "http://" . $user[0] . ".blog.sohu.com/";
	}
	$_SESSION ['sohu_blog'] = $url;

	if (empty ( $re ['location'] )) {
		return "error|服务器连接失败或超时!";
	} else {
		return "ok";
	}

}
function generateCode($length = 3) {
	$chars = "0123456789";
	$code = "";
	while ( strlen ( $code ) < $length ) {
		$code .= $chars [mt_rand ( 0, (strlen ( $chars ) - 1) )];
	}
	return $code;
}

?>