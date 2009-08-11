<?php
include_once ("export_function.php");

$username = $_POST ["username"];
$password = $_POST ["password"];
echo insertSinaBlog ( $username, $password, $this->user_name );

/**
 * 通过BLOG的 用户名密码以及当前用户名，将3D形象秀FLASH插入到SINA BLOG中
 * @param String $login
 * @param String $password
 * @param String $cur_username
 * @return String "返回状态"
 */
function insertSinaBlog($login, $password, $cur_username) {
	$cookie_file = "";
	$url = "http://my.blog.sina.com.cn/login.php?url=%2F";
	$fields = "loginname=" . $login . "&passwd=" . $password . "&checkwd=";
	$cookie_file = $login . "_cookie.txt";
	$htmlBoxTitle = "我的3D形象";
	$flash = $_POST ["flash"];
	if(ini_get("magic_quotes_gpc")=="1")
		$modul=stripslashes($flash);
	else
		$modul = $flash;
		
	//$modul = "<div>".$modul."</div>";
	
	//第1步 登
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	//写cookie
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec ( $ch );
	//log2($html,"sina.html");
	$headerArr = get_headerArrByString ( $html );
	if (preg_match ( "/loginerror/", $html )) {
		return "no"; //username or password error
	}
	
	if (preg_match ( "/error/", $html )) {
		return "no"; //username or password error
	}
	
	$info = curl_getinfo ( $ch );
	if ($info ['http_code'] >= 400) {
		return "no"; //server error
	}
	
	$suid = "";
	if (! empty ( $headerArr ['cookie'] ['SINABLOGNUINFO'] )) {
		$suid = substr ( $headerArr ['cookie'] ['SINABLOGNUINFO'], 0, 10 );
	}
	
	if (! preg_match ( "/\/u\//i", $html )) {
		$temp = explode ( "location.href", $html );
		if (! empty ( $temp [1] )) {
			
			$temp = explode ( ";", $temp [1] );
			if (! empty ( $temp [0] )) {
				$url = str_ireplace ( '=', '', $temp [0] );
				$url = str_ireplace ( '"', '', $url );
				$url = trim ( $url );
			}
		}
		$_SESSION ['sina_blog_url'] = $url;
		$secure_code="";
		if(!empty($url)){
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
			//cookie
			curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
			curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true ); 
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$index = curl_exec ( $ch );
			
			 
			
			 preg_match_all('/encrypt_code\s*=\s*"(.*?)";/m', $index,$arr) ;
			 if(isset($arr[1][0])){
			 	$secure_code=$arr[1][0];
			 	
			 }   	 
		}
		
		
		
		$c = "Title=" . urlencode ( $htmlBoxTitle ) . "&Data=" . urlencode ( $modul ) . "&uid=$suid&cid=0&secure_code=$secure_code";
		//log2($c,"sina2.txt");
		//$c ="Title=%E7%9A%84%E7%9A%84&Data=%E4%BC%9A%E8%AE%A1%E5%B8%88%E7%9A%84%E5%8F%91%3Cbr%3E%E6%98%AF%E7%9A%84%E5%8F%91%E6%98%AF%3Cbr%3E%3Cbr%3E&uid=1080622785&cid=0&secure_code=d2a52eaee8588c8c0da8ae354d527240";
		$url2 = "http://control.blog.sina.com.cn/admin/custom/custmod/new_custmod_html_post.php"; //,$c,$sinacookies,"application/x-www-form-urlencoded","");
		
	
		curl_setopt ( $ch, CURLOPT_URL, $url2 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
		//cookie
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $c );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html2 = curl_exec ( $ch );
		//log2($html2,"sina2.html");
		$getModulidUrl = "http://control.blog.sina.com.cn/admin/custom/get_custom_info.php?uid=$suid&requestId=scriptId_0.5713787461496247";
		curl_setopt ( $ch, CURLOPT_URL, $getModulidUrl );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html3 = curl_exec ( $ch );
		
		$model_id = "";
		if (preg_match ( "/response/", $html3 )) {
			$emp = explode ( ",'[{", $html3 );
			if (! empty ( $emp [1] )) {
				$json = "[{" . $emp [1];
				$json = str_ireplace ( "');", "", $json );
				$re = json_decode ( $json, 1 );
				foreach ( $re as $v ) {
					if (! empty ( $v ['cid'] )) { //取最后一个CID
						$model_id = $v ['cid'];
					}
				}
			
			} else {
				$re = explode ( ',"cid":', $html3 );
				foreach ( $re as $v ) {
					$model_id = substr ( $v, 0, 4 );
				}
			}
			
			$getConfigUrl = $url;
			curl_setopt ( $ch, CURLOPT_URL, $getConfigUrl );
			curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$html3 = curl_exec ( $ch );
			
			$test = explode ( '"cl":[', $html3 );
			$conf = explode ( '],', $test [1] );
			if (! empty ( $conf [0] )) {
				$conf_str = $conf [0];
			}
			
			$conf_str = $model_id . "," . $conf_str;
			$lastUrl = "http://control.blog.sina.com.cn/admin/custom/update_index.php?module=$conf_str&uid=$suid&requestId=scriptId_0.46373730741145724";
			curl_setopt ( $ch, CURLOPT_URL, $lastUrl );
			curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$html4 = curl_exec ( $ch );
			curl_close ( $ch );
		} else {
			return "ok";
		} //end if(preg_match("/response/"
		return "ok";
	} // end if(!preg_match("/\/u\//i",
	

	$temp = explode ( "location.href", $html );
	if (! empty ( $temp [1] )) {
		
		$temp = explode ( ";", $temp [1] );
		if (! empty ( $temp [0] )) {
			$url = str_ireplace ( '=', '', $temp [0] );
			$url = str_ireplace ( '"', '', $url );
			$url = trim ( $url );
		}
	}
	$_SESSION ['sina_blog_url'] = $url;
	
	//登录成功操作 
	//$htmlBoxTitle=iconv("gb2312","utf-8",$htmlBoxTitle);
	$params = "pptype=0b5b0f1663505a904dd4aa3b8d609169&title=%CE%D2%B5%C43D%D0%CE%CF%F3%D0%E3&desc=" . urlencode ( $modul ) . "&submit=%B1%A3%B4%E6";
	$url2 = "http://my.blog.sina.com.cn/section/label/label_add_post.php?f=static&win=0";
	
	curl_setopt ( $ch, CURLOPT_URL, $url2 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	//cookie
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html2 = curl_exec ( $ch );
	
	$getLableIDurl = 'http://my.blog.sina.com.cn/myblog/blog4/get_usermodule.php?suid=' . $suid . '&requestId=scriptId_0.37771564123664586';
	curl_setopt ( $ch, CURLOPT_URL, $getLableIDurl );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
	//cookie
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html3 = curl_exec ( $ch );
	
	$emp = explode ( "'[", $html3 );
	
	if (! empty ( $emp [1] )) {
		$json = str_replace ( "]');", "", $emp [1] );
		$test = explode ( "},", $json );
		foreach ( $test as $v ) {
			
			if (preg_match ( "/blog_diy_label/i", $v )) {
				$v = $v . "}";
				str_replace ( array ("{", "}", '"' ), array ("", "", "" ), $v );
				$ee = explode ( ",", $v );
				foreach ( $ee as $key => $value ) {
					$qq = explode ( ":", $value );
					if ($qq [0] == "dbid") {
						$lable_id = $qq [1];
					
					}
				}
			}
		
		}
		
		$lable_id = str_replace ( '"', '', $lable_id );
		$lable_id = trim ( $lable_id );
		$url3 = "http://conf.blog.sina.com.cn/cnf?" . $suid . "&1&0.7000271239312339.js";
		curl_setopt ( $ch, CURLOPT_URL, $url3 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
		//	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html4 = curl_exec ( $ch );
		//  echo $html4;
		

		$test = explode ( "layout:{column_1:[", $html4 );
		$test = explode ( "]", $test [1] );
		$post = $test [0];
		$postfiled = "col=" . urlencode ( '"blog_diy_label ' . $lable_id . '",' . $post );
		//echo $postfiled;
		$url3 = "http://conf.blog.sina.com.cn/cnfdich";
		curl_setopt ( $ch, CURLOPT_URL, $url3 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)" );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postfiled );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html4 = curl_exec ( $ch );
	
	}
	
	curl_close ( $ch );
	
	return "ok";
}

?>