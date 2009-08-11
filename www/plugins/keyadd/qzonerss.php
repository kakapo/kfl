<?php
//error_reporting(E_ALL);
require_once ("simple_html_dom.php");
print_r ( qzone_rss ( "http://feeds.qzone.qq.com/cgi-bin/cgi_rss_out?uin=121642038" ) );

/**
 * 通过RSS地址获得QZONE用户日志
 * @author cdwei
 * @param string  $url QZONE用户的RSS地址
 * @return 包含标题，链接，时间的数组
 */
function qzone_rss($url) {
	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_REFERER, "http://qzone.qq.com" );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	
	$html2 = curl_exec ( $ch );
	
	curl_close ( $ch );
	
	if (preg_match ( "/error/", $html2 )) {
		return 0; //没有权限访问该RSS,需要开通QQ空间的权限
	}
	if (empty ( $html2 )) {
		return 2; //输入的URL错误
	}
	
	// create HTML DOM
	$html = new simple_html_dom ( );
	$html = str_get_html ( $html2 );
	
	$finally = array ();
	$re = $html->find ( 'item' );
	$i = 0;
	foreach ( $re as $key => $item ) {
		
		//取标题
		$title_arr = $item->find ( "title", 0 );
		
		$title = trim ( $title_arr->innertext );
		$title = str_ireplace ( "<![CDATA[", "", $title );
		$title = str_ireplace ( "]]>", "", $title );
		$finally [$i] ['title'] = $title;
		
		//取链接地址
		

		$all = $item->innertext;
		$test = explode ( "<link>", $all );
		$test2 = explode ( "</link>", $test [1] );
		$finally [$i] ['link'] = $test2 [0];
		
		//取时间	
		$date_arr = $item->find ( 'pubDate', 0 );
		$finally [$i] ['pubDate'] = $date_arr->plaintext;
		$i ++;
	}
	
	unset ( $html );
	return $finally;
}

?>
 