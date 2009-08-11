<?php
//session_start();
//error_reporting(0);
define ( "GM_USER_AGENT", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
 
require_once ("export_function.php");
require ("simplehtmldom/simple_html_dom.php");

//print_r(get_contacts($_POST['uin'],$_POST['p']));


function get_contacts($login, $password) {
	
	$main_url =$GLOBALS ['account'] ['invite']['qq_main_url']."/";
	
	foreach ( $_COOKIE as $k => $v ) {
		if ($v == '')
			$rand = trim ( str_replace ( '_', '.', $k ) );
	}
	
	//用正则取得要提交的服务器 \baction=(.*)cgi-bin\b
	$login_html=execute_curl ("http://mail.qq.com/cgi-bin/loginpage", 'http://mail.qq.com/', 'get', "", 'cookie', "" );
	preg_match_all('/action="(.*)cgi-bin/i',$login_html,$login_arr,PREG_SET_ORDER);
	if(isset($login_arr[0][1])) { $main_url=$login_arr[0][1];  }
	//准备数据
	$cookie = $_SESSION ['cookie']; //$rand."; ".
	$post_data = "sid2=0%2Czh_CN&firstlogin=false&starttime=" . $_POST ['starttime'] . "&redirecturl=&f=html&p=" . urlencode ( $_POST ['p'] ) . "&delegate_url=&s=&ts=" . $_POST ['ts'] . "&from=&uin=" . $_POST ['uin'] . "&aliastype=&pp=" . $_POST ['pp'] . "&verifycode=" . $_POST ['verifycode'];
	
	//第一步 登录 
	$content = execute_curl ( $main_url . "cgi-bin/login?sid=0,zh_CN", 'http://mail.qq.com/', 'post', $post_data, 'cookie', $cookie );
	if (preg_match ( "/loginpage/", $content )) {
		return 0; //username or password error
	}
	list ( $header, $body ) = explode ( "\n\r", $content );
	$cookie = $_SESSION ['cookie'] . '; ' . get_cookies ( $header );
	
	preg_match_all('/\"frame_html\?sid=(.*?)\";/ism',$body,$arr);
	$sid = $arr[1][0];
//	$tmp = explode ( "sid=", $body );
//	if(isset($tmp [1])){
//		list ( $sid ) = explode ( ",", $tmp [1] );
//	}else{
//		$sid="Mjg5NDk5ODc3OTUwNTYxNTIw84979065";
//	}

	//第二步 同步QQ 好友
	//http://m11.mail.qq.com/cgi-bin/addr_importqq?sid=Mjg5NDk5ODc3OTUwNTYxNTIw84979065,zh_CN
	$importqq_url = $main_url . "cgi-bin/addr_importqq?sid=" . $sid;
	$referer = "http://m68.mail.qq.com/cgi-bin/readtemplate?sid=" . $sid . "&t=addr_import_qq.html";
	//sid=vMXlL4LCkoIgYGUs&ImportType=&t=addr_importqq_new
	$post_data = "sid=" . $sid . "&ImportType=&t=addr_importqq_new";
	$res = execute_curl ( $importqq_url, $referer, 'post', $post_data, 'cookie', $cookie );
	
	//第三步 导出所有好友
	$list_all_url = $main_url . "cgi-bin/addr_listall?sid=" . $sid . "&category=qq";
	//echo $list_all_url;
	//http://m128.mail.qq.com/cgi-bin/addr_listall?sid=vMXlL4LCkoIgYGUs&category=qq
	$frame = execute_curl ( $list_all_url, '', 'get', '', 'cookie', $cookie );
	//file_set_contents('t.html',$frame,'w');
	// create HTML DOM
	$html = new simple_html_dom ( );
	$html = str_get_html ( $frame );
	
	$nick = $html->find ( 'p[class=L_n]' );
	$address = $html->find ( 'p[class=L_e]' );
	$finally = array ();
	
	$ii = 0;
	foreach ( $nick as $valuee ) {
		$value = $valuee->plaintext;
		$value = str_replace ( "&nbsp;", "", $value );
		$finally [0] [$ii] = $value;
		$ii ++;
	}
	$ii = 0;
	foreach ( $address as $valuee ) {
		$value = $valuee->plaintext;
		//$value = str_replace ( "@qq.com", "", $value );
		$value = str_replace ( "&nbsp;", "", $value );
		$finally [1] [$ii] = $value;
		$ii ++;
	}
	//  print_r($finally);
	return $finally;

}

?>