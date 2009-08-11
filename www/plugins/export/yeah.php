<?php

//error_reporting(8);
require ("export_function.php");
require ("simplehtmldom/simple_html_dom.php");

//print_r (get_contacts("weichaoduo",""));


function get_contacts($login, $password) {

	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	//$fileds="bCookie=&user=".$login."&pass=".$password."&style=0&RmbUser=on";
	//bCookie=&username=kakapowu@yeah.net&savelogin=&style=-1&user=kakapowu&password=cuck8106
	//$fileds = "bCookie=" . $login . "&username=" . $login . "@yeah.net&user=" . $login . "&password=" . $password . "&style=9&RmbUser=on";
	$fileds = "bCookie=&username=" . $login . "@yeah.net&savelogin=&style=-1&user=" . $login . "&password=" . $password ;
	$action = "http://reg.163.com/login.jsp?type=1&product=mailyeah&url=http://entry.mail.yeah.net/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26style%3D-1";


	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );

	$html = curl_exec ( $ch );
	if (preg_match ( "/A0070F/", $html )) {
		return 0; //username or password error
	}
	$header_arr = get_headerArrByString ( $html );
	$cookie_NTES_SESS = $header_arr ['cookie'] ['NTES_SESS'];

	preg_match_all('/CONTENT="0;URL=(.*?)\"/ism',$html,$arr);
	$url2 = $arr[1][0];
	//$url2 = "http://passport.yeah.net/setcookie.jsp?username=" . $login . "@yeah.net&loginCookie=" . $cookie_NTES_SESS . "&domain=yeah.net";

	curl_setopt ( $ch, CURLOPT_URL, $url2 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );

	//$url3 = "http://entry.yeah.net/cgi/ntesdoor?lightweight=1&verifycookie=1&style=9&username=" . $login . "@yeah.net";
	preg_match_all('/CONTENT="0;URL=(.*?)\"/ism',$html,$arr);
	$url3 = $arr[1][0];
	curl_setopt ( $ch, CURLOPT_URL, $url3 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );
	//echo $html;die;
	$html_dom = new simple_html_dom ( );
	$html_dom = str_get_html ( $html );
	$href = $html_dom->find ( "a", 0 )->href;
	list ( , $sid ) = explode ( "sid=", $href );
	$url2 = "http://g1a5.mail.yeah.net/jy3/address/addrlist.jsp?sid=$sid&gid=all"; //.$postid."&sid=".$sid."&ifirstv=&group=&outformat=8&outport.x=%BF%AA%CA%BC%B5%BC%B3%F6";
	//http://g1a3.mail.yeah.net/jy3/address/addrlist.jsp?sid=DATizcddmTxkBXfTnsddhbMcfXUnjDQF&gid=all
	//$url2="http://g1a4.mail.yeah.net/jy3/address/addrlist.jsp?sid=".$sid;


	curl_setopt ( $ch, CURLOPT_URL, $url2 );
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