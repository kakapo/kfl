<?php
//session_start();
//
if (empty ( $_POST ['uin'] ) or empty ( $_POST ['vcode'] )) {
	echo "3";
	die ();

}

define ( "GM_USER_AGENT", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
$cookie = "zzpanelkey=; zzpaneluin=; jump=4; pvid=587889849; " . $_SESSION ['cookie2'] . "; " . $_SESSION ['cookie'];
$flash = $GLOBALS ['gSiteInfo'] ['image_site_url'] . "/public/images/widget.swf?user=" . $this->user_name;

$post_data = "wndtype=flash&uin=" . $_POST ['uin'] . "&wndtitle=" . iconv ( "utf-8", "gb2312", "我的3D形象秀" ) . "&img=" . urlencode ( $flash ) . "&url=" . urlencode ( $flash ) . "&content=&title=idol&style=-104&verifycode=" . $_POST ['vcode'];

$content1 = execute_curl ( "http://users.cnc.qzone.qq.com/cgi-bin/user/cgi_add_diywnd", 'http://users.cnc.qzone.qq.com/proxy.html', 'post', $post_data, 'cookie', $cookie );
//log2($content1 ,"last_succ.html");
if (preg_match ( "/error/", $content1 )) {
	echo "3"; //第二次验证码错误
	die (); //username or password error
}

$itemXml = execute_curl ( "http://users.qzone.qq.com/cgi-bin/user/cgi_diywnd_xml", 'http://user.qzone.qq.com/' . $_POST ['uin'], 'get', '', 'cookie', $cookie );
$itemXmls = explode ( "<?xml", $itemXml );
if (! empty ( $itemXmls [1] )) {
	$itemXml = "<?xml " . $itemXmls [1];
} else {
	
	$itemXml = "";
}
$itmid = 1;
//log2($itemXml,"itemXml.xml");
$xml2 = simplexml_load_string ( $itemXml );
$num = count ( $xml2->item ) - 1;
$aa = 1;
foreach ( $xml2->item [$num]->attributes () as $a => $b ) {
	$itmid = $b;
}

$json = execute_curl ( "http://u.qzone.qq.com/cgi-bin/entry_js.cgi?uin=" . $_POST ['uin'], 'http://user.qzone.qq.com/' . $_POST ['uin'], 'get', '', 'cookie', $cookie );
$test = explode ( "g_Configuration", $json );
if (! empty ( $test [1] )) {
	
	$json = $test [1];
}
//log2($json,"tes2t.xml");
$json = str_replace ( "={", "{", $json );
$json = str_replace ( ";", "", $json );
$g_Configuration = preg_replace ( "/([a-z]+)/i", '"\\1"', $json );

$arr = json_decode ( $g_Configuration, true );
$saveInfo = "http://mall.qzone.qq.com/fcg-bin/v3/fcg_diy_save_scenario?";

$saveInfo .= 'frameStyle=' . $arr ['frameStyle'] . '&fullMode=' . $arr ['fullMode'] . '&simpleMode=' . $arr ['simpleMode'] . '&scenario=ver' . $arr ['version'] . '@';
$tmp = array ();
if (is_array ( $arr ['items'] )) {
	$tmp = $arr ['items'];
	foreach ( $tmp as $key => $v ) {
		
		$saveInfo .= $v ['type'] . "_" . $v ['itemno'] . "_" . $v ['posx'] . "_" . $v ['posy'] . "_" . $v ['width'] . "_" . $v ['height'] . "_" . $v ['zindex'] . "|";
	
	}
}

if (is_array ( $arr ['windows'] )) {
	$tmp = $arr ['windows'];
	$i = 0;
	$count = count ( $arr ['windows'] );
	foreach ( $tmp as $key => $v ) {
		//$i++;
		//if($i==$count){
		//	$saveInfo.=$v['type'].'_'.$itmid."_".$v['posx']."_".$v['posy']."_".$v['width']."_".$v['height']."_".$v['zindex']."|";
		//	break;
		//}
		$saveInfo .= $v ['type'] . '_' . $v ['itemno'] . "_" . $v ['posx'] . "_" . $v ['posy'] . "_" . $v ['width'] . "_" . $v ['height'] . "_" . $v ['zindex'] . "|";
	
	}
}

$addMod = '99_' . $itmid . '_2_2_232_440_0';
$saveInfo .= $addMod;

$saveInfo .= "&scenari_no=0&styleid=" . $arr ['style'] . "&uin=" . $_POST ['uin'] . "&skinupd=0&sds=0.9143491266687662&vt=json";
//log2($saveInfo,"saveInfo.xml");


///$save = "http://mall.qzone.qq.com/fcg-bin/v3/fcg_diy_save_scenario?frameStyle=0&fullMode=0&simpleMode=0&scenario=ver4@1_1_0_0_0_0_0|19_1_0_0_0_0_0|13_1_799_63_100_500_1|95_2_182_2_355_285_0|94_3_2_437_175_285_0|95_4_182_292_355_285_0|94_6_362_582_175_140_0|94_7_182_582_175_140_0|94_8_542_437_175_285_0|94_10_542_147_175_285_0|94_11_542_2_175_140_0|95_19_2_2_175_430_0&scenari_no=0&styleid=1&uin=".$_POST['uin']."&skinupd=0&sds=0.9143491266687662&vt=json";


$content = execute_curl ( $saveInfo, 'http://user.qzone.qq.com/' . $_POST ['uin'], 'get', '', 'cookie', $cookie );

if (preg_match ( "/succ/", $content1 )) {
	echo "1@@@@" . $_POST ['uin']; //成功
	die (); //username or password error230*440
} else {
	echo "4"; //其他问题未能相应
	die ();
}

function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "") {
	$message = '';
	$cookie_str = "";
	
	if ($method != "get" and $method != "post") {
		$message = 'The cURL method is invalid.';
	}
	if ($url == "") {
		$message = 'The cURL url is blank.';
	}
	
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

function get_cookies($header) {
	$match = "";
	preg_match_all ( '!Set-Cookie: ([^;\s]+)($|;)!', $header, $match );
	/* 		Debugger::say("header: ".print_r($header,true)."\n\ncookies: ".print_r($match,true));  */
	$cookie = "";
	foreach ( $match [1] as $val ) {
		if ($val {0} == '=')
			continue;
			// Skip over "expired cookies which were causing problems; by Neerav; 4 Apr 2006
		if (strpos ( $val, "EXPIRED" ) !== false)
			continue;
		list ( $key, $value ) = explode ( "=", $val );
		if ($value != '')
			$cookie .= $val . "; ";
	}
	return substr ( $cookie, 0, - 2 );
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