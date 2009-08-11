<?php
if(!defined('GM_USER_AGENT')) define ( "GM_USER_AGENT", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)" );
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
			//echo $extra_data;
			curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
		}
	}

	$gmail_response = curl_exec ( $c );
	curl_close ( $c );

	return $gmail_response;
}

function getCookieByString($string) {

	$temp = explode ( "\r\n", $string );
	foreach ( $temp as $key => $header ) {
		if (preg_match ( "/Set-Cookie:/i", $header )) {

			$cookiestr = trim ( substr ( $header . "1", 11, - 1 ) );
			return $cookiestr;
		}

	}

	return "";
}

/**
 * 通过获得的HTTP头文字符串以解析成头的数组
 *
 * @param string $string
 * @return array
 */
function get_headerArrByString($string) {

	$cookiearr = array ();
	$location = "";
	$Server = "";
	$Date = "";
	$Content_Type = "";
	$Transfer_Encoding = "";
	$Connection = "";
	$Cache_Control = "";
	$final = array ('location' => '', 'cookie' => $cookiearr, 'Server' => '', 'Date' => '', 'Content-Type' => '', 'Transfer-Encoding' => '', 'Connection' => '', 'Cache-Control' => '' );
	$temp = explode ( "\r\n", $string );
	foreach ( $temp as $key => $header ) {

		if (preg_match ( "/Location:/i", $header )) {
			$location = trim ( substr ( $header . "1", 9, - 1 ) );
		}
		if (preg_match ( "/Server:/i", $header )) {
			$Server = trim ( substr ( $header . "1", 6, - 1 ) );
		}
		if (preg_match ( "/Date:/i", $header )) {
			$Date = trim ( substr ( $header . "1", 5, - 1 ) );
		}
		if (preg_match ( "/Content-Type:/i", $header )) {
			$Content_Type = trim ( substr ( $header . "1", 13, - 1 ) );
		}
		if (preg_match ( "/Transfer-Encoding:/i", $header )) {
			$Transfer_Encoding = trim ( substr ( $header . "1", 18, - 1 ) );
		}
		if (preg_match ( "/Connection:/i", $header )) {
			$Connection = trim ( substr ( $header . "1", 1, - 1 ) );
		}

		if (preg_match ( "/Cache-Control:/i", $header )) {
			$Cache_Control = trim ( substr ( $header . "1", 9, - 1 ) );
		}

		if (preg_match ( "/Set-Cookie:/i", $header )) {

			$cookiestr = trim ( substr ( $header . "1", 11, - 1 ) );
			$cookie = explode ( ';', $cookiestr );
			$cookie = explode ( '=', $cookie [0] );
			$cookiename = trim ( array_shift ( $cookie ) );
			$cookiearr [$cookiename] = trim ( implode ( '=', $cookie ) );
		}

	}
	$final = array ('location' => $location, 'cookie' => $cookiearr, 'Server' => $Server, 'Date' => $Date, 'Content-Type' => $Content_Type, 'Transfer-Encoding' => $Transfer_Encoding, 'Connection' => $Connection, 'Cache-Control' => $Cache_Control );
	return $final;
}

function findinside($start, $end, $string) {
	preg_match_all ( '/' . $start . '([^\.)]+)' . preg_quote ( $end, '/' ) . '/i', $string, $m );
	return $m [1];
}

function getExportid($string) {
	preg_match_all ( '/(.*?)href=[\"](.*?[\?subsection=26].*?)[\"](.*?)/i', $string, $m );

	return $m;
}

function getMiddleStr($start, $end, $string) {

	preg_match_all ( "|" . $start . "(.*)" . $end . "|U", $string, $out );
	return $out;

}

function get_cookies($header) {
	$match = "";
	preg_match_all ( '!Set-Cookie:\s*([^;\s]+)($|;)!', $header, $match );

	$cookie = "";
	foreach ( $match [1] as $val ) {
		if ($val {0} == '=')
			continue;
			// Skip over "expired cookies which were causing problems; by Neerav; 4 Apr 2006
		if (stripos ( $val, "Expires" ) !== false)
			continue;
		list ( $key, $value ) = explode ( "=", $val );
		if ($value != '')
			$cookie .= $val . "; ";
	}
	return substr ( $cookie, 0, - 2 );
}
#read_header is essential as it processes all cookies and keeps track of the current location url
#leave unchanged, include it with get_contacts
function read_header($ch, $string) {
	global $location;
	global $cookiearr;
	global $ch;

	$length = strlen ( $string );
	if (! strncmp ( $string, "Location:", 9 )) {
		$location = trim ( substr ( $string, 9, - 1 ) );
	}
	if (! strncmp ( $string, "Set-Cookie:", 11 )) {
		$cookiestr = trim ( substr ( $string, 11, - 1 ) );
		$cookie = explode ( ';', $cookiestr );
		$cookie = explode ( '=', $cookie [0] );
		$cookiename = trim ( array_shift ( $cookie ) );
		$cookiearr [$cookiename] = trim ( implode ( '=', $cookie ) );
	}
	$cookie = "";
	if (trim ( $string ) == "") {
		foreach ( $cookiearr as $key => $value ) {
			$cookie .= "$key=$value; ";
		}
		curl_setopt ( $ch, CURLOPT_COOKIE, $cookie );
	}

	return $length;
}

#function to trim the whitespace around names and email addresses
#used by get_contacts when parsing the csv file
function trimvals($val) {
	return trim ( $val, "\" \n" );
}

//将日志输出到一个文件中
function log2($event = null, $filename = "") {

	$now = date ( "Y-M-d-H-i-s" );
	if (empty ( $filename ))
		$filename = $now . "log4.html";
	$fd = @fopen ( $filename, 'w' );
	$log = $now . " " . $_SERVER ["REMOTE_ADDR"] . " - $event <br>";
	@fwrite ( $fd, $log );
	@fclose ( $fd );

}

?>