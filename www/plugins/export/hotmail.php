<?php
if(defined(GM_USER_AGENT))define ( "GM_USER_AGENT", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)" );
//error_reporting(8);
$location = "";
$cookiearr = array ();
require ("export_function.php");
require ("simplehtmldom/simple_html_dom.php");
//print_r( get_contacts("weichaoduo",""));


$location = ""; #keep track of location/redirects
$cookiearr = ""; #store cookies here
$ch = null;

function get_contacts($login, $password) {
	#the globals will be updated/used in the read_header function
	global $location;
	global $cookiearr;
	global $ch;

	$html = '';
	$html_dom = new simple_html_dom ( );
	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, "http://login.live.com/login.srf?id=2" );
	curl_setopt ( $ch, CURLOPT_REFERER, "" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_HEADERFUNCTION, 'read_header' );

	#get the html from gmail.com
	$html = curl_exec ( $ch );

	//log2($html);
	$matches = array ();
	$actionarr = array ();

	$temp = findinside ( "lt=", "&co", $cookiearr ['MSPRequ'] );
	$bk_id = $temp [0];
	preg_match_all ( '/<input type\="hidden" name\="([^"]+)".*?value\="([^"]*)"[^>]*>/si', $html, $matches );
	$values = $matches [2];
	$params = "";

	$i = 0;
	foreach ( $matches [1] as $name ) {
		$params .= "$name=" . urlencode ( $values [$i] ) . "&";
		++ $i;
	}

	//
	$action = "https://login.live.com/ppsecure/post.srf?id=2&bk=" . $bk_id;
	$login = urlencode ( $login . "@hotmail.com" );
	$password = urlencode ( $password );
	$fileds = $params . "login=" . $login . "&passwd=" . $password;

	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	//curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );

	$html = curl_exec ( $ch );

	if (preg_match ( "/incorrect/", $html ))
		return 0; //username or password error
	if (preg_match ( "/format/", $html ))
		return 0; //username or password error
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400)
		return 2; //server error


	//取得下一个链接地址,并打开
	//<html><head><script type="text/javascript">function rd(){window.location.replace("http://www.hotmail.msn.com/cgi-bin/sbox?t=9o8WNbkEADp36XohEZKJiq8E6n0rnIB3s!4GKYtsWK2h9y6j1CwEaOlUpG!NyBaMjn5DBzfDUktscuqeUo*fQK45QrFcxaozoaAkZOvIr9pODHdJRXBBiwVy2shnF9T8!A&p=9yZyqiGUnEQY0IG6rhvIKTu9EOPSrMLggvLk8UPpeXqYaQYxEOcBJvqPVG9W7BTubejzay3rpRsvRcxDugSPMDoFnJxqS5h*HAl4GrWH8k1k!3TF5vxtfnsdgyYDk7lPZGYgFi8*Nrqo5wIkwHN40yVWyqJb7xTEXWWCEkbM1*D73563nI4Er4WaPmSfBH61P2&lc=1033&id=2");}function OnBack(){}</script></head><body onload="javascript:rd();"></body></html>
	//	$temp="";
	//	list($tt,$temp)=explode('replace("',$html);
	//	list($url2)=explode('");',$temp);
	//
	// 	curl_setopt($ch, CURLOPT_URL,$url2);
	//
	////	$cookie = '';
	////	foreach ($cookiearr as $kk=>$vv){
	////		if(in_array($kk,array('ANON','NAP','MC1','MUID','vjuids','vjlast','HMP1')))
	////		$cookie .= $kk.'='.$vv.'; ';
	////	}
	////
	////	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	////Cookie: ANON=A=AB2FA3A04E637212471EE52FFFFFFFFF&E=790&W=1; NAP=V=1.8&E=751&C=iQchNA9j4F091BKruweEJsvYEWp4_n0TUbsk2VaisF3QiLFBsaMrzw&W=2; MC1=V=3&GUID=567cfb168d314afa83ec5ac9bd12c8dc; MUID=868E683104A84E50B8F268F40B710A69; vjuids=d2a43aa4.11daea08cce.0.a2451358edcd4; vjlast=1226995437,1226995437,30; HMP1=1
	//
	////NAP=V=1.8&E=753&C=CHXbBAReSldZ7hC0fMIMK_2Fm3YR43GWy3V8ZYLQ-zDrqZCILvgiFA&W=1; ANON=A=AB2FA3A04E637212471EE52FFFFFFFFF&E=7ad&W=1;
	//
	//	curl_setopt($ch, CURLOPT_USERAGENT, GM_USER_AGENT);
	//	curl_setopt($ch, CURLOPT_HEADER,1);
	//	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	//	curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
	//	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	//	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	//
	//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	//	$html = curl_exec($ch);


	//取得HOTMAIL的URL参数n=1213131313
	//


	curl_setopt ( $ch, CURLOPT_URL, 'http://mail.live.com/default.aspx?&ip=10.12.144.8&d=d24&mf=0' );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	$html = curl_exec ( $ch );

	//


	preg_match_all ( "/TodayLight\.aspx\?n=(\d+)/ism", $html, $arr );
	//print_r ( $arr );
	$arr [1] [0] = isset ($arr [1][0]) ? $arr [1] [0] : 0;
	//获得全部联系人的列表
	$url4 = "http://by109w.bay109.mail.live.com/mail/ContactMainLight.aspx?n=" . $arr [1] [0];
	//echo $url4;
	curl_setopt ( $ch, CURLOPT_URL, $url4 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );

	curl_close ( $ch );

	//将内容存放如数组
	$html_dom = str_get_html ( $html );
	$nicks = $html_dom->find ( 'a[href^=ContactViewLight.aspx?ContactID=]' );
	$emails = $html_dom->find ( 'a[onclick^=submitToCompose]' );
	$nick = array ();
	$email = array ();
	$i = 0;
	foreach ( $nicks as $v ) {
		$nick [$i] = trim ( $v->plaintext );
		$i ++;
	}
	$i = 0;
	foreach ( $emails as $v ) {
		$email [$i] = trim ( $v->plaintext );
		$i ++;
	}
	//var_dump($email);
	return array (0 => $nick, 1 => $email );

}
