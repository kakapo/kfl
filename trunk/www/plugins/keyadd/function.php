<?php
function GetWebContent($host, $method, $str, $port = '80', $sessid = '') {
	$ip = gethostbyname ( $host );
	$fp = fsockopen ( $ip, $port );
	if (! $fp)
		return;
	fputs ( $fp, "$method\r\n" );
	fputs ( $fp, "Host: $host\r\n" );
	if (! empty ( $sessid )) {
		fputs ( $fp, "Cookie: JSESSIONID=$sessid; path=/;\r\n" );
	}
	if (substr ( trim ( $method ), 0, 4 ) == "POST") {
		fputs ( $fp, "Content-Length: " . strlen ( $str ) . "\r\n" ); //  别忘了指定长度
	}
	fputs ( $fp, "Content-Type: application/x-www-form-urlencoded\r\n\r\n" );
	if (substr ( trim ( $method ), 0, 4 ) == "POST") {
		fputs ( $fp, $str . "\r\n" );
	}
	while ( ! feof ( $fp ) ) {
		$response .= fgets ( $fp, 1024 );
	}
	$hlen = strpos ( $response, "\r\n\r\n" ); // LINUX下是 "\n\n"
	$header = substr ( $response, 0, $hlen );
	$entity = substr ( $response, $hlen + 4 );
	if (preg_match ( '/JSESSIONID=([0-9a-z]+);/i', $header, $matches )) {
		$a ['sessid'] = $matches [1];
	}
	if (preg_match ( '/Location: ([0-9a-z\_\?\=\&\#\.]+)/i', $header, $matches )) {
		$a ['location'] = $matches [1];
	}
	$a ['content'] = $entity;
	fclose ( $fp );
	return $a;
}

?> 

