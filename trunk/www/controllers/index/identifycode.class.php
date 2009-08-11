<?php
/**
 * @abstract 验证码生成类
 * @author zswu at
 *
 **/
class identifycode{

	function view_getimage(){
		//http://ptlogin2.qq.com/getimage?aid=23000101&0.5626820830966479
		$res = $this->execute_curl ( "http://ptlogin2.qq.com/getimage?aid=23000101", '', 'get', '', 'cookie' );
		list ( $header, $body ) = explode ( "\n\r", $res );

		$cookie = $this->get_cookies ( $header );

		$_SESSION ['cookie'] = $cookie;
		$this->log2(date("Y-m-d H:i:s").' session Id:'.session_id().' cookie:'.$cookie,APP_TEMP_DIR."/qqlog.html");
		echo trim ( $body );
		die;
	}
	function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "") {
		$message = '';

		if ($method != "get" and $method != "post") {
			$message = 'The cURL method is invalid.';
		}
		if ($url == "") {
			$message = 'The cURL url is blank.';
		}
		/* 		if ($referrer == "") { */
		/* 			$message = 'The cURL referrer is blank.'; */
		/* 		} */
		/* 		if ($method == "post" and (!is_array($data) or count($data) == 0)) { */
		/* 			$message = 'The cURL post data  for POST is empty or invalid.'; */
		/* 		} */

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
			curl_setopt ( $c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
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
			curl_setopt ( $c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6" );
			curl_setopt ( $c, CURLOPT_HEADER, 1 );
			if ($extra_type != "nocookie") {
				curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
			}
		}
		curl_setopt ( $c, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt ( $c, CURLOPT_SSL_VERIFYPEER, FALSE );

		/* 		// debugging cURL */
		/* 		$fd = fopen("debug_curl.txt", "a+"); */
		/* 		curl_setopt($c, CURLOPT_VERBOSE, 1); */
		/* 		curl_setopt($c, CURLOPT_STDERR, $open_file_handle); */

		$gmail_response = curl_exec ( $c );
		curl_close ( $c );

		/* 		// close debugging file */
		/* 		fclose($fd); */

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
			$cookie .= $val . ";";
		}
		return substr ( $cookie, 0, - 1 );
	}
		//将日志输出到一个文件中
	function log2($event = null, $filename = "") {

		$now = date ( "Y-M-d-H-i-s" );
		if (empty ( $filename ))
		$filename = $now . "log4.html";
		$fd = @fopen ( $filename, 'a' );
		$log =  $event;
		@fwrite ( $fd, $log.'\n\r<br>');
		@fclose ( $fd );

	}

	function view_generate(){
		$_SESSION ['validatecode'] = "";
		//定义
		$width = "90";
		$height = "30";
		$len = "4";
		$bgcolor = "#eeeeee";
		$noise = 1;
		$noisenum = 20;
		$border = false;
		$bordercolor = "#cccccc";

		//创建画布
		$image = imagecreatetruecolor ( $width, $height );
		$back = $this->getcolor ($image, $bgcolor );
		imageFilledRectangle ( $image, 0, 0, $width, $height, $back );
		$size = ($width-6) / $len;
		if ($size > $height)
			$size = $height;


		//为画布添加杂点
		if ($noise == true){
			for($i = 0; $i < $noisenum; $i ++) {
				$randColor = imageColorAllocate ( $image, rand ( 150, 255 ), rand ( 150, 255 ), rand ( 150, 255 ) );
				$x1 = rand(0,$width);
				$y1 = rand(0,$height);
				$x2 = $x1+rand(-20,20);
				$y2 = $y1+rand(-20,20);
				imageline ( $image, $x1, $y1,  $x2,  $y2,  $randColor );
			}
		}
		//生成随机数字

		$font = APP_DIR. "/plugins/ENGR.TTF";
		$textall = "123456789ABCDEFGHHJKLMNPQRSTWXY";
		$code = '';
		$colorArr = array("#006633","#990000","#006699","#663333");
		$angleArr = array(10,-10,-20,20);
		$textColor = $this->getcolor($image,$colorArr[array_rand($colorArr)]);

		//echo $textColor;
		for($i = 0; $i < $len; $i ++) {
			$tmptext = rand ( 0, 30 );

			$randtext = $textall [$tmptext];
			imagettftext ( $image,18, $angleArr[array_rand($angleArr)], 6+$size*$i, 24, $textColor, $font, $randtext );
			$code .= $randtext;
		}
		$_SESSION ['validatecode'] = $code;

		//加上边框
		if ($border == true){
			$bordercolor = $this->getcolor ( $image, $bordercolor );
			imageRectangle ( $image, 0, 0, $width - 1, $height - 1, $bordercolor );
		}


		//生成
		header ( "Content-type: image/png" );
		imagePng ( $image );
		imagedestroy ( $image );
		die;
	}
	function getcolor(&$image,$color) {
		$color = eregi_replace ( "^#", "", $color );
		$r = $color [0] . $color [1];
		$r = hexdec ( $r );
		$b = $color [2] . $color [3];
		$b = hexdec ( $b );
		$g = $color [4] . $color [5];
		$g = hexdec ( $g );
		$color = imagecolorallocate ( $image, $r, $b, $g );
		return $color;
	}

}


?>