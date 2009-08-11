<?php
 

date_default_timezone_set('PRC');
define ( "GM_USER_AGENT", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
define('APP_TEMP_DIR',dirname(__file__)."/logs");
define('LOG_TEST_DIR',dirname(__file__)."/../../tmp");


if(ini_get("magic_quotes_gpc")=="1")
{
	$_GET['post_data']	=stripslashes($_GET['post_data']);
}
$_POST=json_decode($_GET['post_data'],1);
$session_id	=$_POST['session_id'];
$user_ip	=$_POST['user_ip'];  
$ip=$_SERVER['REMOTE_ADDR'];

if(isset($_GET['action']) && $_GET['action']=="log" ){
	$session_id=session_id();
	$result=$_GET['result'];
	echo $_GET['action'];
	log2($_SERVER['SERVER_ADDR'].' '.$ip.' '.date("Y-m-d H:i:s").' '.$session_id.' '.$_GET ['u']." $result ,finish\n<br>",LOG_TEST_DIR.'/'."qqloginlog2.html");
	die ();
	
}

$cookie = "";
$cookie = "zzpanelkey=; zzpaneluin=; jump=4; pvid=587889849; " .$_POST['cookie'];
$post_data = "u1=" . urlencode ( $_POST ['u1'] ) . "&fp=loginerroralert&h=1&u=" . $_POST ['u'] . "&p=" . $_POST ['p'] . "&verifycode=" . $_POST ['verifycode'] . "&aid=8000108&dummy=1";
$content = execute_curl ( "http://ptlogin2.qq.com/login", 'http://imgcache.qq.com/qzone/toolbar/loginbox.html', 'post', $post_data, 'cookie', $cookie );
if(strpos($content,'验证码有误')){
	echo "2";
	log2($user_ip.' '.$ip.' '.date("Y-m-d H:i:s").' '.$session_id.' '.$_POST ['u']." failed ,veriftyCode\n<br>",APP_TEMP_DIR.'/'."qqloginlog.html");
	die ();
}
if (preg_match ( "/history/i", $content )) {
	echo "0";
	log2($user_ip.' '.$ip.' '.date("Y-m-d H:i:s").' '.$session_id.' '.$_POST ['u']." failed ,error pass\n<br>",APP_TEMP_DIR.'/'."qqloginlog.html");
	die ();
}


if (preg_match ( "/Set-Cookie/i", $content )) {
	log2($user_ip.' '.$ip.' '.date("Y-m-d H:i:s").' '.$session_id.' '.$_POST ['u']." sucessed \n<br>",APP_TEMP_DIR."/qqloginlog.html");
	$cookie = get_cookies ( $content );
	//log2($cookie,"c:cookie.html");
	$flash 		= $_POST ['flash'];
	$width 		= $_POST ['width'];
	$height 	= $_POST ['height'];
	$wrap_width	=$_POST ['wrap_width'];
	$wrap_height=$_POST ['wrap_height'];
	$flash = '<qz:title type="flash" moduleborder="true">' . iconv ( "utf-8", "gb2312", '我的3D形象' ) . '</qz:title><div><qz:swf swfsrc="' . $flash . '" width="' . $width . '" height="' . $height . '" loop="true" waitforclick="false" wmode="transparent" /></div>';
	//<qz:title type="flash" moduleborder="true">yyy</qz:title>														  <div><qz:swf swfsrc="http://yy.swf" width="0" height="0" loop="false" waitforclick="false" wmode="transparent" /></div>
	$data = 'uin=' . $_POST ['u'] . '&qzml=' . urlencode ( $flash );
	//$data='uin='.$_POST['u'].'&qzml=%3Cqz%3Atitle+type%3D%22flash%22+moduleborder%3D%22true%22%3E%CE%D2%B5%C43D%D0%CE%CF%F3%D0%E3%3C%2Fqz%3Atitle%3E%3Cdiv%3E%3Cqz%3Aswf+swfsrc%3D%22http%3A%2F%2Fimage.guodong.com%2Fpublic%2Fimages%2Fwidget.swf%3Fuser%3Dweichaoduo%22+width%3D%220%22+height%3D%220%22+loop%3D%22false%22+waitforclick%3D%22false%22+wmode%3D%22opaque%22+%2F%3E%3C%2Fdiv%3E';
	//log2($data,"c:add_data.html");
	$content = execute_curl ( "http://users.qzone.qq.com/cgi-bin/custom/add_custom_window.cgi", 'http://imgcache.qq.com/qzone/v5/toolpages/fp_gbk.html', 'post', $data, 'cookie', $cookie );
	if (preg_match ( "/success/i", $content )) {
		//log2($content,"c:key_conten.html");
		$mod_id=1;
		preg_match_all('/"id":\s*(\d){0,4}/i',$content,$temp,PREG_SET_ORDER);
		if(isset($temp[0][1])) $mod_id=$temp[0][1];
			
		$index = execute_curl ( "http://user.qzone.qq.com/" . $_POST ['u'] . "", 'http://qzone.qq.com', 'get', '', 'cookie', $cookie);
		//$item_str2 = execute_curl ( "http://www.guodong.dev2/plugins/keyadd/qzone_parse.php", 'http://www.guodong.dev2/plugins/keyadd/qzone_parse.php', 'post', 'action=g_Dressup&html='.$index, 'cookie', '');
		//log2($item_str2,"c:item_str2.txt");
		//取得json字符串，并转换为合法JSON字符串
		preg_match_all('/g_Dressup\s*=\s*(.*?),g_StaticFlag/ism',$index,$arr,PREG_SET_ORDER);
		$str2	="";
		$new_str="";
		if(!empty($arr[0][1])) {
			$str2= $arr[0][1];
			$pattern = "/([a-z0-9\-_]+)/ism";
			$new_str = preg_replace($pattern,'"\\1"',$str2);
		}else{
			echo "2@@@@" . $_POST ['u']; //成功
			die();//嵌入已经成功，但因为您的QQ未开通对外权限，因此不能显示在首页中，您需要在自定义、模块中勾选出来，详看教程
		}

		$g_Dressup_arr=json_decode($new_str,1);
		//log2($new_str,"c:json.txt");
			
		//取得下面的值
		//$tt="g_hasCustomStyle = 0,g_diySkin = 0,g_StyleID = 16,g_fullMode = 4,g_frameStyle = 14,g_version = 5";
		$pattern = "/g_hasCustomStyle\s*=\s*(\d+),g_diySkin\s*=\s*(\d+),g_StyleID\s*=\s*(\d+),g_fullMode\s*=\s*(\d+),g_frameStyle\s*=\s*(\d+),g_version\s*=\s*(\d+)/ism";
		preg_match_all($pattern,$index,$arr,PREG_SET_ORDER);
		list(,$hasCustomStyle,$diySkin,$StyleID,$fullMode,$frameStyle,$version)=$arr[0];

		//将数组转换为POST数据
		//windlist:99_4_0_1_0_0_292_4|99_1_0_2_0_0_446_1|1_2_0_3_0_0_409_2|311_0_0_4_0_0_151_0|310_0_0_5_0_0_125_0|4_1_1_1_0_0_244_1|15_2_1_2_0_0_402_2|7_0_2_1_0_0_146_0|3_0_2_2_0_0_292_0
		$items_str="";
		$windows_str="";
			
		if(is_array($g_Dressup_arr['items'])){
			$configs=$g_Dressup_arr['items'];
			foreach ($configs as $key => $v) {
				if(is_int($key)){
					extract($v);
					//{type:19,itemno:1,posx:0,posy:0,posz:0,height:0,width:0,flag:0}
					$items_str.=$type."_".$itemno."_".$posx."_".$posy."_".$posz."_".$width."_".$height."_".$flag."|";//   implode("_",$v)."|";
				}
			}//end foreach ($configs as

		}

		//抓取QZONE原来的布局配置
		//http://u.qzone.qq.com/cgi-bin/qzone_static_widget?uin=79720699&timestamp=1236311206
		$index2 = execute_curl ( 'http://u.qzone.qq.com/cgi-bin/qzone_static_widget?uin='.$_POST ['u'].'&timestamp='.time(), 'http://qzone.qq.com', 'get', '', 'cookie', $cookie);
		$windows_str2 = execute_curl ( "http://www.guodong.dev2/plugins/keyadd/qzone_parse.php", 'http://www.guodong.dev2/plugins/keyadd/qzone_parse.php', 'post', 'action=windows_str&html='.$index2, 'noheader', '');
		//log2($windows_str2,"c:windows_str2.txt");
		
		preg_match_all('/g_Dressup:\s*(.*?),\s*g_StaticFlag/ism',$index2,$arr2,PREG_SET_ORDER);
		$str2	="";
		$new_str="";
		if(!empty($arr2[0][1])) {
			$str2= $arr2[0][1];
			$pattern = "/([a-z0-9\-_]+)/ism";
			$new_str2 = preg_replace($pattern,'"\\1"',$str2);
		}
		$g_Dressup_arr2=json_decode($new_str2,1);
		$windows_str="";
		if(empty($g_Dressup_arr2['windows'])){
			$windows_str="99_1_0_1_0_".$wrap_width."_".$wrap_height."_" . $mod_id ."|";
		}
		if(isset($g_Dressup_arr2['windows'])){
			if(is_array($g_Dressup_arr2['windows'])){
				$configs	=$g_Dressup_arr2['windows'];
				$y			=0;
				$max_arr	=array();
				$cur_mode	=0;
				if(empty($wrap_width)) $wrap_width=0;
				if(empty($wrap_height)) $wrap_height=456;
				//取出mode中的最大值
				foreach ($configs as $key2 => $v2) {
					if($v2['appid']=="99")  array_push($max_arr,$v2['mode']);
				}
				if(!empty($max_arr)){
					$cur_mode=max($max_arr)+1;
				}else{
					$cur_mode=1;
				}

				foreach ($configs as $key => $v) {
					if(is_array($v)){
						extract($v);
						if($y==0){
							$windows_str.="99_".$cur_mode."_0_1_0_".$wrap_width."_".$wrap_height."_" . $mod_id ."|";
						}
						$windows_str.=$appid."_".$mode."_".$posx."_".$posy."_".$posz."_".$width."_".$height."_".$wndid."|";
						$y++;
					}
				}//end foreach ($configs as
			}
		}
		$items_str = substr($items_str,0,-1);
		$windows_str=substr($windows_str,0,-1);
		//log2($windows_str,"c:windows_str.txt");
		//$last_post="uin=".$_POST['u']."&bstyle=".trim($hasCustomStyle)."&styleid=".trim($diySkin)."&framestyle=".trim($frameStyle)."&mode=".trim($fullMode)."&xpos=0&ypos=0&diystyle=".trim($diySkin)."&inshop=0&transparence=0&itemlist=".urlencode($items_str)."&windlist=".urlencode($windows_str)."&diyitemlist=";
		$last_post = "uin=" . $_POST ['u'] . "&bstyle=" . trim ( $hasCustomStyle ) . "&styleid=" . trim ( $StyleID ) . "&framestyle=" . trim ( $frameStyle ) . "&mode=" . trim ( $fullMode ) . "&xpos=0&ypos=0&diystyle=0&inshop=0&transparence=0&isBGScroll=0&itemlist=" . urlencode ( $items_str ) . "&windlist=" . urlencode ( $windows_str ) . "&diyitemlist=";
		// log2($last_post,"c:last.txt");
		$content = execute_curl ( "http://users.qzone.qq.com/cgi-bin/user/save_scenario.cgi", 'http://imgcache.qq.com/qzone/v5/toolpages/fp_gbk.html', 'post', $last_post, 'cookie', $cookie );
		//log2($content,"c:aaa.txt");
		echo "1@@@@" . $_POST ['u']; //成功

	} else {
		echo "4";

	}

	die ();


}


function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "") {
	$message = '';

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
		if ($extra_type != "noheader") {
			curl_setopt ( $c, CURLOPT_HEADER, 1 );
		}else{
			curl_setopt ( $c, CURLOPT_HEADER, 0 );
		}
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
	$fd = fopen ( $filename, 'a' );
	$log =  $event;
	fwrite ( $fd, $log );
	fclose ( $fd );

}



?>