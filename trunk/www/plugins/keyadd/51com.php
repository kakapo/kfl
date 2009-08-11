<?php
include_once ("export_function.php");

$username = $_POST ["username"];
$password = $_POST ["password"];

echo insert51Blog ( $username, $password,$this->user_name);

/**
 * ͨ��BLOG�� �û��������将flash插入51的日记中
 * @param String $login
 * @param String $password
 * @param String $cur_username
 * @return String 是否成功
 */
function insert51Blog($login, $password, $cur_username) {
	$cookie_file = "";
	$url = "http://passport.51.com/login.5p";
	$fields = "passport_51_user=$login&passport_51_password=$password&gourl=http%3A%2F%2Fmy.51.com%2Fwebim%2Findex.php%3Frefer%3D%2F&submit.x=47&submit.y=21";
	
	$cookie_file =  APP_TEMP_DIR . '/' . $login . "_cookie.txt";
	$modul = $_POST ['flash'];
	$title=iconv("utf-8","gbk","我的3D形象");
	
	//��1�� ��¼
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	;
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	 
	//验证
	if (preg_match ( "/alert/", $html )) {
		return "no"; //username or password error
	}
	
	
	$action = "http://diary.51.com/diary/diary_add.php";
	#submit the login form:
	curl_setopt ( $ch, CURLOPT_URL, $action );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
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
	$f_Diary_Group="";
	$tmp=explode("selected value='",$html);
	$tmp3=array();
	if(!empty($tmp[1])){
		$tmp3=explode("'>",$tmp[1]);
	    if(!empty($tmp3[0])){
	    	$f_Diary_Group=$tmp3[0];
	    }
	}
	
	//http://diary.51.com/diary/diary_add.php?action=add
	//插入
	$submit_url = "http://diary.51.com/diary/diary_add.php?action=add";
	$posts = $params."&active=ûв&content=&f_Diary_Group=$f_Diary_Group&f_Diary_Heart=4&f_Diary_Memo=".urlencode("[movie=$modul]")."&f_Diary_ShowTime=".date("y-m-d")."&f_Diary_Title=".urlencode($title)."&id=".time()."&f_Diary_WeekDay=1&home_hidden=0&old_share_flag=&send_feed=1&share_flag=1&share_users=&status=c&action=save";
	curl_setopt ( $ch, CURLOPT_URL, $submit_url );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $posts );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html2 = curl_exec ( $ch );
  
    // echo $html2;
	return "ok";

}

?>