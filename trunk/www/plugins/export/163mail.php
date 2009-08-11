<?php 

include("export_function.php");
require ("simplehtmldom/simple_html_dom.php"); 
function get_contacts($login, $password) {

	$index=execute_curl ("http://mail.163.com", 'http://mail.163.com', 'get', '', 'cookie', "" );
	$cookie=get_cookies($index);
	$url = "http://reg.163.com/login.jsp?type=1&url=http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D35";
	$fields = "verifycookie=1&style=-1&product=mail163&savelogin=&username=" . $login . "&password=" . $password . "&selType=-1&%B5%C7%C2%BC%D3%CA%CF%E4=";
	$cookie_file = "" . $login . "_cookie.txt";
	$sid = "";
	$result=execute_curl ($url, 'http://mail.163.com/', 'post', $fields, 'cookie', $cookie );
	if (preg_match ( "/password/", $result )) {
		return 0; //username or password error
	}
	$cookie=get_cookies($result);
	$html=execute_curl ("http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=35&username=".$login, 'http://mail.163.com/', 'get', $fields, 'cookie', $cookie );
	preg_match_all ( "/sid=(.*?)\n/m", $html, $sid_arr );
	if(isset($sid_arr[1][0])) $sid=$sid_arr[1][0];
	$cookie=get_cookies($html);
	$next_url="http://g1a118";
	preg_match_all ( "/Location:(.*?).mail.163.com/m", $html, $next_arr );
	if(isset($next_arr[1][0])) $next_url=$next_arr[1][0];
	$next_url=trim($next_url);
	$json=execute_curl ($next_url.".mail.163.com/jy3/address/addrlist.jsp?sid=".$sid."&gid=all", 'http://mail.163.com/', 'get', '', 'cookie', $cookie );
	//log2($json,"c:json.txt"); 
	
	//将内容存放如数组 
	$html_dom = str_get_html ( $json );
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