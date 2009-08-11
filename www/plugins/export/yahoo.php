<?php

//error_reporting(8);


//print_r( get_contacts("",""));
#function get_contacts, accepts as arguments $login (the username) and $password
require ("simplehtmldom/simple_html_dom.php");
function get_contacts($login, $password) {

	include('open_invite/openinviter.php');
	$inviter=new OpenInviter();
	$oi_services=$inviter->getPlugins();
	$plugType='email';
	$plugType='social';
	$ers=array();$oks=array();$import_ok=false;$done=false;

	$inviter->startPlugin("yahoo");
	$inviter->login($login,$password);
	$contacts=$inviter->getMyContacts();
	$finally=array();

	if(is_array($contacts)){
		foreach ($contacts as $key => $v) {
		$finally[0][]=$v;
		$finally[1][]=$key;
		}
	}

	return $finally;

	print_r($finally);die;



	$cookie_file = APP_TEMP_DIR . '/' . $login . "_cookie.txt";
	#initialize the curl session
	$ch = curl_init ();

	$action = "https://login.yahoo.com/config/login_verify2?.src=ab&.done=http%3A%2F%2Faddress.mail.yahoo.com%2F";
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_REFERER, 'https://login.yahoo.com/config/login_verify2?.src=ab&.intl=cn&.done=http%3A%2F%2Fcn.address.yahoo.com%2F' );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );

	//取得隐藏域并序列化
	$params="";
	$matches = array ();
	preg_match_all ( '/<input type\="hidden" name\="([^"]+)".*?value\="([^"]*)"[^>]*>/si', $html, $matches );
	if(!empty($matches [2])){
		$values = $matches [2];
		$i = 0;
		if(is_array($matches [1])){
			foreach ( $matches [1] as $name ) {
				$params .= "$name=" . urlencode ( $values [$i] ) . "&";
				++ $i;
			}
		}
	}
//	if(strpos("yahoo.cn",$login)){
//		list($login)=explode("@",$login);
//	}
 	$params .= "&login=" .  $login  . "&passwd=" . $password;
	 echo $params;

	$action = "https://login.yahoo.com/config/login?";
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_REFERER, 'https://login.yahoo.com/config/login_verify2?.src=ab&.intl=cn&.done=http%3A%2F%2Fcn.address.yahoo.com%2F' );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
 echo $html;
	if (preg_match ( "/yregertxt/", $html )) {
		return 0; //username or password error
	}
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return 2; //server error
	}

	#this is the new address's url:


	curl_setopt ( $ch, CURLOPT_URL, "http://cn.address.yahoo.com/" );

	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

	$html = curl_exec ( $ch );

	//echo $html;die;


	$html_dom = new simple_html_dom ( );
	$html_dom = str_get_html ( $html );
	$nicks = $html_dom->find ( 'span[class=contactname]' );
	$emails = $html_dom->find ( 'td[class=contactnumbers]' );
	$nick = array ();
	$email = array ();
	$i = 0;
	foreach ( $nicks as $v ) {
		$nick [$i] = trim ( $v->plaintext ); //iconv("gb2312","UTF-8",trim($v->plaintext));

		$i ++;
	}
	$i = 0;
	foreach ( $emails as $v ) {
		$email [$i] = trim ( $v->plaintext );
		$i ++;
	}
	// var_dump($email);

	$finally = array (0 => $nick, 1 => $email );

	return $finally;
	//var_dump($finally);die;


}

?>
