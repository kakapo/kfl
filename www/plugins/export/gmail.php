<?php

require_once ("export_function.php");

//print_r(get_contacts("phpgrid2"," "));


function get_contacts($login, $password) {

	$cookie_file = APP_TEMP_DIR . "/" . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	$fileds = 'service=mail&Email=' . $login . '&Passwd=' . $password . '&rmShown=1&signIn=%E7%99%BB%E5%BD%95&asts=';
	$action = "https://www.google.com/accounts/ServiceLoginAuth?service=mail";
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );

	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );

	$html = curl_exec ( $ch );

	if (preg_match ( "/errormsg/", $html )) {
		return 0; //username or password error
	}
	if (empty ( $html )) {
		return 0; //
	}
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return 2; //server error
	}

	curl_setopt ( $ch, CURLOPT_URL, "http://mail.google.com/mail/contacts/data/export?exportType=ALL&groupToExport=&out=OUTLOOK_CSV" );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_POST, 0 );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	$html = curl_exec ( $ch );
	curl_close ( $ch );
	$csvrows = explode ( "\n", $html );
    array_shift ( $csvrows );
	$names = array ();
	$emails = array ();
    $temp=array();
	foreach ( $csvrows as $row ) {
		$values = explode ( ",", $row );
		$len=count($values);
		for($i=0;$i<$len;$i++)
		{
		  if(preg_match ( "/@/", $values [$i] )){
		  	$emails [] = $values [$i];
		  	$myar=explode('@',$values [$i]);
		  	$names []=$myar[0];
		  }
		}
	}
	return array ($names, $emails );
}

?>