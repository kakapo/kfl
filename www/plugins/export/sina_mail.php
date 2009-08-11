<?php
//error_reporting(8);
//get_contacts("weichaoduo","");
include ("export_function.php");
function get_contacts($login, $password) {
	$cookie_file = "";
	$url = "http://mail.sina.com.cn/cgi-bin/login.cgi";
	$fields = "logintype=uid&u=" . $login . "&psw=" . $password . "&btnloginfree=登 录";
	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	
	//第1步 登录
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	//写cookie
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	
	if (preg_match ( "/loginerror/", $html )) {
		return 0; //username or password error
	}
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return 2; //server error  
	}
	
	$tmpp = get_headerArrByString ( $html );
	$location = $tmpp ['location'];
	
	curl_close ( $ch );
	
	//登录成功操作 
	$ch2 = curl_init ();
	curl_setopt ( $ch2, CURLOPT_URL, $location );
	curl_setopt ( $ch2, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	//cookie
	

	curl_setopt ( $ch2, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch2, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch2, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt ( $ch2, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch2, CURLOPT_NOBODY, 1 );
	curl_setopt ( $ch2, CURLOPT_RETURNTRANSFER, 1 );
	$result2 = curl_exec ( $ch2 );
	$content = curl_multi_getcontent ( $ch2 );
	
	curl_close ( $ch2 );
	
	//第2步 到处cvs
	$fields2 = "extype=csv";
	list ( , , $tmp ) = explode ( "/", $location );
	$url4 = "http://" . $tmp . "/classic/addr_export.php";
	
	$ch3 = curl_init ();
	curl_setopt ( $ch3, CURLOPT_URL, $url4 );
	curl_setopt ( $ch3, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch3, CURLOPT_POST, 1 );
	curl_setopt ( $ch3, CURLOPT_POSTFIELDS, $fields2 );
	//发送cookie
	curl_setopt ( $ch3, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch3, CURLOPT_FOLLOWLOCATION, true );
	
	curl_setopt ( $ch3, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch3 );
	
	curl_close ( $ch3 );
	
	$csvrows = array ();
	$values = array ("", "", "", "" );
	$csvrows = explode ( "\n", $html );
	array_shift ( $csvrows );
	
	$names = array ();
	$emails = array ();
	
	foreach ( $csvrows as $row ) {
		$values = explode ( ",", $row );
		if (empty ( $row ))
			break;
			//  var_export($values);echo "<br>";
		if (empty ( $values [3] ))
			$values [3] = "";
		if (eregi ( "@", $values [3] )) {
			$name = iconv ( "gb2312", "UTF-8", strip_tags ( $values [0] ) );
			$names [] = (trim ( $name ) == "") ? $values [3] : $name;
			$emails [] = $values [3];
		
		}
	}
	
	return array ($names, $emails );
}

?>