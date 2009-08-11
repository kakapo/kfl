<?php
include_once ("export_function.php");
include_once ("simple_html_dom.php");

$username = $_POST ["username"];
$password = $_POST ["password"];

echo insertBaiduBlog ( $username, $password, $this->user_name );

/**
 * ͨ��BLOG�� �û��������Լ���ǰ�û���3D������FLASH���뵽SINA BLOG��
 * @param String $login
 * @param String $password
 * @param String $cur_username
 * @return String "����״̬"
 */
function insertBaiduBlog($login, $password, $cur_username) {
	$cookie_file = "";
	$url = "https://passport.baidu.com/?login";
	$fields = "tpl_ok=&next_target=&tpl=&skip_ok=&aid=&need_pay=&need_coin=&pay_method=&u=.%2F&return_method=get&more_param=&return_type=&psp_tt=0&password=$password&safeflg=0&username=$login";
	
	$cookie_file = $login . "_cookie.txt";
	$htmlBoxTitle = $_POST['type'];
	$img_url = $GLOBALS ['gSiteInfo'] ['user_site_url'] . "/" . substr ( $cur_username, 0, 1 ) . "/" . substr ( $cur_username, 0, 2 ) . "/" . substr ( $cur_username, 0, 3 ) . "/" . $cur_username . "/230x330.jpg";
	$modul = '<p>&nbsp;</p>
			<div forimg="1" align="center">
			<div forimg="1" align="center"><a target="_blank" href="' . $img_url . '"><img class="blogimg" border="0" small="1" alt="" src="' . $img_url . '" /></a>
			</div>
			</div>
		
			<p>&nbsp;</p>
			
			';
	
	//��1�� ��¼
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	// echo $html;
	//�ж��Ƿ��¼�ɹ�
	//$headerArr=get_headerArrByString($html);
	
	
	if (strstr ($html,"switch(256)" ) or strstr ($html,"switch(257)" )) {
		return "no2"; //username or password error
	}
	 
	if (!strstr ($html,"url=url.replace" )) {
		return "no"; //username or password error
	}
	
	//�ڶ��� ȡ�û������� 
	$url2 = "http://hi.baidu.com/";
	
	curl_setopt ( $ch, CURLOPT_URL, $url2 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html3 = curl_exec ( $ch );
	
	$html_dom = new simple_html_dom ( );
	$html_dom = str_get_html ( $html3 );
	@$domain = $html_dom->find ( 'div[id=nav_extra]', 0 )->find ( 'a', 0 )->href;
	$domain = substr ( $domain, 1 );
	if (empty ( $domain )) {
		$domain = $login;
	}
	
	//log2($html3,"baidu_en.htm");
	

	//��3�� �ύ���
	$submit_url = "http://hi.baidu.com/" . $domain . "/commit";
	$posts = "ct=30&cm=2&spRefURL=http%3A%2F%2Fhi.baidu.com%2F" . $domain . "%2Fprofile&spRefURL=http%3A%2F%2Fhi.baidu.com%2Fdiykate&spMyIntroduction=" . urlencode ( $modul ) . "&tj=+%C8%B7%B6%A8+";
	// $posts="spMyIntroduction=".urlencode($modul)."&tj=+%C8%B7%B6%A8+";
	curl_setopt ( $ch, CURLOPT_URL, $submit_url );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $posts );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html2 = curl_exec ( $ch );
	//log2($html2,"baidu2.html");
	return "ok";

}

?>