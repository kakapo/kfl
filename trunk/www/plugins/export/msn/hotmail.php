<?php
if(!defined('GM_USER_AGENT')) define ( "GM_USER_AGENT", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)" );
//error_reporting(8);
$location = "";
$cookiearr = array ();
//include_once ("../export_function.php");


$location = ""; #keep track of location/redirects
$cookiearr = ""; #store cookies here
$ch = null;


function getHiddenFiledsByHtml($html) {
	$matches = array ();
	$actionarr = array ();

	preg_match_all ( '/<input type\="hidden" name\="([^"]+)".*?value\="([^"]*)"[^>]*>/si', $html, $matches );
	$values = $matches [2];
	$params = "";
	$i = 0;
	foreach ( $matches [1] as $name ) {
		$params .= "$name=" .  urlencode($values [$i])  . "&";
		++ $i;
	}
	return $params;
}

function loginAuth($login, $password,$consenturl) {
	#the globals will be updated/used in the read_header function
	global $location;
	global $cookiearr;
	global $ch;

	$html = '';
	$cookie_file =  APP_DIR."/tmp/" . $login . "_cookie.txt";
	#initialize the curl session
	//$consenturl="https://consent.live.com/pp650/Delegation.aspx?ps=Contacts.View&ru=http%3A%2F%2Fwww.guodong.dev2%2Fplugins%2Ftest_msn%2Fcontacts%2Fdelauth-handler.php&pl=http%3A%2F%2Fwww.guodong.dev2%2Fplugins%2Ftest_msn%2Fcontacts%2Fpolicy.html&app=appid%3D000000004000CBBC%26ts%3D1235980210%26sig%3D44a8r2iwhbuWJqok9j2fV%252FTK99Mn7ZwAdh%252FqRudV4eM%253D&rollrs=04&wa=wsignin1.0" ;

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL,$consenturl);
	curl_setopt ( $ch, CURLOPT_REFERER, "" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_HEADERFUNCTION, 'read_header' );

	#get the html from gmail.com
	$html = curl_exec ( $ch );
	//log2($html,"c:hotmail.html");
	$temp = findinside ( "lt=", "&co", $cookiearr ['MSPRequ'] );
	$bk_id = $temp [0];

	$params = getHiddenFiledsByHtml($html);

	//
	$action = "https://login.live.com/ppsecure/post.srf?wa=wsignin1.0&rpsnv=10&ct=".time()."&rver=5.5.4152.0&wp=SAPI&wreply=https:%2F%2Fconsent.live.com%2Fpp650%2FDelegation.aspx%3Fps%3DContacts.View%26ru%3Dhttp%253A%252F%252Fwww.guodong.dev2%252Fplugins%252Ftest_msn%252Fcontacts%252Fdelauth-handler.php%26pl%3Dhttp%253A%252F%252Fwww.guodong.dev2%252Fplugins%252Ftest_msn%252Fcontacts%252Fpolicy.html%26app%3Dappid%253D000000004000CBBC%2526ts%253D1235980210%2526sig%253D44a8r2iwhbuWJqok9j2fV%25252FTK99Mn7ZwAdh%25252FqRudV4eM%25253D%26rollrs%3D04%26wa%3Dwsignin1.0&lc=1033&id=252554&bk=" . $bk_id;
	$login = urlencode ( $login );
	$password = urlencode ( $password );
	$fileds = $params . "login=" . $login . "&passwd=" . $password;

	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_HEADER, 1);
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_HEADERFUNCTION, 'read_header');
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );

	$html2 = curl_exec ( $ch );
	//log2($html2,"c:hotmail2.html");
	$cookies=get_cookies($html2);
	//$params = getHiddenFiledsByHtml($html2);
	preg_match_all('/name="ANON"\s*id="ANON"\s*value="(.*?)"/ism',$html2,$temp,PREG_SET_ORDER);
	$ANON=$temp[0][1];
	preg_match_all('/name="NAP"\s*id="NAP"\s*value="(.*?)"/ism',$html2,$temp2,PREG_SET_ORDER);
	$NAP=$temp2[0][1];
	preg_match_all('/name="t"\s*id="t"\s*value="(.*?)"/ism',$html2,$temp3,PREG_SET_ORDER);
	$T=$temp3[0][1];
	$params	= "NAPExp=Fri%2C+26-Jun-2009+08%3A14%3A20+GMT&NAP=".$NAP."&ANON=".$ANON."&ANONExp=Sun%2C+04-Oct-2009+08%3A14%3A20+GMT&t=".$T;
	//log2($params,"c:hotmail2_post.html");
	curl_setopt ( $ch, CURLOPT_URL, $consenturl."&rollrs=04&wa=wsignin1.0"  );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_COOKIE, $cookies );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
	$html3 = curl_exec ( $ch );
	//log2($html3,"c:hotmail3.html");
	$responseform=strstr ($html3,"responseform");
	if(empty($responseform)){
		$cookies=get_cookies($html3);
		$params = getHiddenFiledsByHtml($html3)."&OfferRepeater%24ctl00%24ActionRepeater%24ctl00%24AcceptOfferCheck=Contacts.View&OfferRepeater%24ctl00%24ActionRepeater%24ctl00%24ExpiresDropDown%24ExpirationDropDown=30&SelectedDetails=&ConsentBtn=%E5%85%81%E8%AE%B8%E8%AE%BF%E9%97%AE";
		//log2($params,"c:hotmail_access_post.html");
		curl_setopt ( $ch, CURLOPT_URL, $consenturl."&rollrs=04&wa=wsignin1.0"  );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_COOKIE, $cookies );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
		$html3 = curl_exec ( $ch );
		//log2($html3,"c:hotmail_access.html");
	}


	$finally=array();
	//$params = getHiddenFiledsByHtml($html3);
	preg_match_all('/ConsentToken"\s*value="(.*?)"/ism',$html3,$temp,PREG_SET_ORDER);
	$finally['ConsentToken']=str_replace("&#37;","%",$temp[0][1]);

	$finally['ResponseCode']='RequestApproved';
	$finally['action']='delauth';
	$finally['appctx']='';
	curl_close($ch);
	return $finally;

}
