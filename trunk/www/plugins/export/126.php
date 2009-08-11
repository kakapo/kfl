<?php

require_once ("export_function.php");
require ("simplehtmldom/simple_html_dom.php");
$location = "";

//print_r(get_contacts("weichaoduo","79720699"));


function get_contacts($login, $password) {

	global $location;
	global $ch;

	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	//$fileds = 'domain=126.com&language=0&bCookie=&user=' . $login . '&pass=' . $password . '&style=-1&remUser=&enter.x=%B5%C7+%C2%BC';
	$fileds='domain=126.com&language=0&bCookie=&username=' . $login . '@126.com&user=' . $login . '&password=' . $password . '&style=-1&remUser=&enter.x=%B5%C7+%C2%BC';
	$action = "http://reg.163.com/login.jsp?type=1&product=mail126&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26language%3D0%26style%3D-1";

	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );

	$html = curl_exec ( $ch );
 

	if (preg_match ( "/color=#A0070F/", $html )) {
		return 0; //username or password error
	}
	$header_arr = get_headerArrByString ( $html );

	$cookie_NTES_SESS = $header_arr ['cookie'] ['NTES_SESS'];

	preg_match_all('/CONTENT="0;URL=(.*?)\"/ism',$html,$arr);
	$url2 = $arr[1][0];

	curl_setopt ( $ch, CURLOPT_URL, $url2 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );

	preg_match_all('/CONTENT="0;URL=(.*?)\"/ism',$html,$arr);
	$url3 = $arr[1][0];

	curl_setopt ( $ch, CURLOPT_URL, $url3 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );
	if(empty($html)){
		return 0;
	}
	$html_dom = new simple_html_dom ( );
	$html_dom = str_get_html ( $html );
	$href="";
	$sid="";
	if(!empty($html_dom->find ( "a", 0 )->href)){
		$href = $html_dom->find ( "a", 0 )->href; 
		list ( , $sid ) = explode ( "sid=", $href );
	}
	$url4 = "http://g1a62.mail.126.com/jy3/address/addrlist.jsp?sid=$sid&puid=&gid=&reloadGrp=true";


	curl_setopt ( $ch, CURLOPT_URL, $url4 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );

	curl_close ( $ch );

	//将内容存放如数组


	$html_dom = str_get_html ( $html );
	$nicks = $html_dom->find ( 'td[class=Ibx_Td_addrName]' );
	$emails = $html_dom->find ( 'td[class=Ibx_Td_addrEmail]' );
	$nick = array ();
	$email = array ();
	$i = 0;
	foreach ( $nicks as $v ) {
		$nick [$i] = iconv ( "gb2312", "UTF-8", trim ( $v->plaintext ) );
		$i ++;
	}
	$i = 0;
	foreach ( $emails as $v ) {
		$email [$i] = trim ( $v->plaintext );
		$i ++;
	}
	// var_dump($email);
	return array (0 => $nick, 1 => $email );
}

?>