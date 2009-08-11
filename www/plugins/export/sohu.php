<?php

//error_reporting(8);
require_once ("export_function.php");
$location = "";
$cookiearr = array ();

//print_r(get_contacts("weichaoduo","79720699"));
function get_contacts($login, $password) {
	
	#the globals will be updated/used in the read_header function
	global $location;
	global $cookiearr;
	global $ch;
	$password = md5 ( $password );
	
	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	
	$action = "http://passport.sohu.com/sso/login.jsp?userid=" . $login . "%40sohu.com&password=" . $password . "&appid=10000&persistentcookie=1&s=" . time () . "&b=1&w=1440&pwdtype=1";
	
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_HEADERFUNCTION, 'read_header' );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	
	if (preg_match ( "/error/", $html )) {
		return 0; //username or password error
	}
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return 2; //server error
	}
	
	$cookie = "";
	foreach ( $cookiearr as $key => $value ) {
		$cookie .= $key . "=" . $value . "; ";
	}
	
	//echo $cookie;
	

	$url3 = "http://login.mail.sohu.com/servlet/LoginServlet";
	
	curl_setopt ( $ch, CURLOPT_URL, $url3 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	
	list ( $header, $body ) = explode ( "\n\r", $html );
	$cookie2 = get_cookies ( $header );
	
	$headers = explode ( "\n", $header );
	$host = "";
	foreach ( $headers as $key => $v ) {
		if (preg_match ( "/Location/i", $v )) {
			$host = trim ( str_replace ( "Location:", "", $v ) );
			$host = trim ( str_replace ( "main", "", $host ) );
			break;
		}
	}
	if (empty ( $host )) {
		$host = "http://mail.sohu.com/bapp/40/";
	}
	
	$url3 = $host . "contact?action=export&type=xml";
	
	curl_setopt ( $ch, CURLOPT_URL, $url3 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIE, $cookie );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$res_xml = curl_exec ( $ch );
	
	// log2($res_xml,"test22.html");
	

	$xml = xml_parser_create ();
	xml_parser_set_option ( $xml, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option ( $xml, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct ( $xml, $res_xml, $vals, $index );
	xml_parser_free ( $xml );
	
	$finally = array ();
	if (! empty ( $index ['card'] )) {
		$tmp = array ();
		$tmp = $index ['card'];
		foreach ( $tmp as $key => $v ) {
			if (empty ( $vals [$v] ['attributes'] ['nickname'] ))
				$vals [$v] ['attributes'] ['nickname'] = "";
			if (empty ( $vals [$v] ['attributes'] ['personalemail'] ))
				$vals [$v] ['attributes'] ['personalemail'] = "";
			$finally [0] [] = $vals [$v] ['attributes'] ['nickname'];
			$finally [1] [] = $vals [$v] ['attributes'] ['personalemail'];
		
		}
	
	}
	
	return $finally;

}

?>