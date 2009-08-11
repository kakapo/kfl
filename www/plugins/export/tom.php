<?php
require_once ("export_function.php");

//get_contacts("weichaoduo","79720699");


function get_contacts($login, $password) {
	
	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	$fileds = "type=0&style=10&user=" . $login . "&pass=" . $password . "&enter=%B5%C7%A1%A1%C2%BC";
	$action = "http://login.mail.tom.com/cgi/login";
	
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	
	$html = curl_exec ( $ch );
	if (preg_match ( "/error/", $html )) {
		return 0; //username or password error
	}
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return 2; //server error  
	}
	
	list ( $header, $body ) = explode ( "\n\r", $html );
	
	$headers = explode ( "\n", $header );
	$s1 = 'Location:';
	foreach ( $headers as $head ) {
		if (false !== strpos ( $head, $s1 )) {
			$location = substr ( $head, strlen ( $s1 ) );
			break;
		}
	}
	if (empty ( $location )) {
		return 0;
	}
	parse_str ( $location );
	$sid = trim ( $sid );
	
	$url2 = "http://bjapp4.mail.tom.com/cgi/ldvcapp?funcid=address&sid=" . $sid . "&tempname=address%2Faddress.htm&showlist=all&ifirstv=all&listnum=0&x=25&y=5";
	curl_setopt ( $ch, CURLOPT_URL, $url2 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	$search = array ("'<script[^>]*?>.*?</script>'si", "'&#(\d+);'e" );
	$replace = array ("", "chr(\\1)" );
	$html = preg_replace ( $search, $replace, $html );
	$lines = array_unique ( explode ( "\n", $html ) );
	
	//print_r($lines);
	$names = array ();
	$emails = array ();
	$all = array ();
	foreach ( $lines as $line ) {
		$line = trim ( $line );
		if (trim ( $line ) != '') {
			
			if (preg_match ( "/ldvcapp\?funcid=loadiadd/i", $line )) {
				$temp = trim ( strip_tags ( $line ) );
				if (! empty ( $temp )) {
					$names [] = iconv ( "gb2312", "UTF-8", strip_tags ( $line ) );
				}
			
			}
			if (preg_match ( "/coremail\/fcg\/ldmmapp\?/i", $line )) {
				if (! empty ( $line )) {
					$emails [] = strip_tags ( $line );
				}
			}
		}
	}
	
	$all [0] = $names;
	$all [1] = $emails;
	if (empty ( $emails )) {
		return 3; //没有找到邮件列表
	}
	// print_r($all);
	return $all;
}

?>