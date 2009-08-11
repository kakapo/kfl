<?php
session_start ();
$res = execute_curl ( "http://ptlogin2.qq.com/getimage?aid=23000101", '', 'get', '', 'cookie' );
list ( $header, $body ) = explode ( "\n\r", $res );
$cookie = get_cookies ( $header );
$_COOKIE ['cookie'] = $cookie;
setcookie('cookie',$cookie, 0,'/');

echo trim ( $body );
function get_cookies($header) {
	$match = "";
	preg_match_all ( '!Set-Cookie: ([^;\s]+)($|;)!', $header, $match );
	/* 		Debugger::say("header: ".print_r($header,true)."\n\ncookies: ".print_r($match,true));  */
	$cookie = "";
	foreach ( $match [1] as $val ) {
		if ($val {0} == '=')
			continue;
			//    Skip over "expired cookies which were causing problems; by Neerav; 4 Apr 2006
		if (strpos ( $val, "EXPIRED" ) !== false)
			continue;
		$cookie .= $val . ";";
	}
	return substr ( $cookie, 0, - 1 );
}
function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "") {
	$message = '';
	
	if ($method != "get" and $method != "post") {
		$message = 'The cURL method is invalid.';
	}
	if ($url == "") {
		$message = 'The cURL url is blank.';
	}
	/* 		if ($referrer == "") { */
	/* 			$message = 'The cURL referrer is blank.'; */
	/* 		} */
	/* 		if ($method == "post" and (!is_array($data) or count($data) == 0)) { */
	/* 			$message = 'The cURL post data  for POST is empty or invalid.'; */
	/* 		} */
	
	// error
	if ($message != '') {
		array_unshift ( $return_status, array ("action" => "execute cURL", "status" => "failed", "message" => $message ) );
		return;
	}
	
	set_time_limit ( 150 );
	$c = curl_init ();
	if ($method == "get") {
		curl_setopt ( $c, CURLOPT_URL, $url );
		if ($referrer != "") {
			curl_setopt ( $c, CURLOPT_REFERER, $referrer );
		}
		//$this->CURL_PROXY($c);
		curl_setopt ( $c, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $c, CURLOPT_USERAGENT, GM_USER_AGENT );
		if ($extra_type != "noheader") {
			curl_setopt ( $c, CURLOPT_HEADER, 1 );
		}
		if ($extra_type != "nocookie") {
			curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
		}
		/* 			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				 */
	} elseif ($method == "post") {
		curl_setopt ( $c, CURLOPT_URL, $url );
		curl_setopt ( $c, CURLOPT_POST, 1 );
		curl_setopt ( $c, CURLOPT_POSTFIELDS, $post_data );
		if ($referrer != "") {
			curl_setopt ( $c, CURLOPT_REFERER, $referrer );
		}
		//$this->CURL_PROXY($c);
		curl_setopt ( $c, CURLOPT_RETURNTRANSFER, 1 );
		if ($extra_type == "nocookie") {
			curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 0 );
		} else {
			curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 1 );
		}
		curl_setopt ( $c, CURLOPT_USERAGENT, GM_USER_AGENT );
		curl_setopt ( $c, CURLOPT_HEADER, 1 );
		if ($extra_type != "nocookie") {
			curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
		}
	}
	curl_setopt ( $c, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt ( $c, CURLOPT_SSL_VERIFYPEER, FALSE );
	
	/* 		// debugging cURL */
	/* 		$fd = fopen("debug_curl.txt", "a+"); */
	/* 		curl_setopt($c, CURLOPT_VERBOSE, 1); */
	/* 		curl_setopt($c, CURLOPT_STDERR, $open_file_handle); */
	
	$gmail_response = curl_exec ( $c );
	curl_close ( $c );
	
	/* 		// close debugging file */
	/* 		fclose($fd); */
	
	return $gmail_response;
}

//将日志输出到一个文件中	
function log2($event = null, $filename = "") {
	
	$now = date ( "Y-M-d-H-i-s" );
	if (empty ( $filename ))
		$filename = $now . "log4.html";
	$fd = @fopen ( APP_TEMP_DIR . "/" . $filename, 'w' );
	$log = $now . " " . $_SERVER ["REMOTE_ADDR"] . " - $event <br>";
	@fwrite ( $fd, $log );
	@fclose ( $fd );

}

?>