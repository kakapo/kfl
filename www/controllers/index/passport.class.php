<?php
/**
 * @abstract 用户通行证类
 * @author zswu at
 *
 **/
class passport {

	/****登录相关方法 开始*****/
	function view_login() {
		global $tpl;
		$redirect_url = isset ( $_GET ['from'] ) ? $_GET ['from'] : '';
		if(isset($_GET ['forward'])) {
			$redirect_url = isset ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
		}
		$tpl->assign ( 'redirect_url', urlencode ( $redirect_url ) );
		$tpl->assign ( 'title', '登录-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'tag', time () );
	}
	function view_loginwin(){
		global $tpl;
		$redirect_url = isset ( $_GET ['from'] ) ? $_GET ['from'] : '';
		if(isset($_GET ['forward'])) {
			$redirect_url = isset ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
		}
		$tpl->assign ( 'title', '登录-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'redirect_url', urlencode ( $redirect_url ) );
	}
	function op_dologin() {
		$redirect_url = ! empty ( $_POST ['redirect_url'] ) ? $_POST ['redirect_url'] : '';
		if (empty ( $_POST ['passport_user'] )) {
			$msg = "";
			die ();
		}
		$user_name = $_POST ['passport_user'];
		$user_passwd = $_POST ['passport_passwd'];
		if(isset($_SESSION['pwd_error']) && isset($_POST ['User_Code'])){
			$vcode		 =strtolower($_POST ['User_Code']);
			if($vcode!=strtolower($_SESSION['validatecode'])){
				echo "-5";die;
			}
		}
		$cookie_remember = ! empty ( $_POST ['remember'] ) ? $_POST ['remember'] : '0';
		include_once("ApiUser.class.php");
		$user = ApiUser::getUserByName( $user_name );
		if ($user) {

			include_once("UserModel.class.php");
			$usermod = new UserModel($user['user_db_key']);
			$user_info = $usermod->getUserById($user['user_id']);
				
			if ($user_info ['user_passwd'] == md5 ( $user_passwd )) {
				if(isset($_SESSION['pwd_error'])) unset($_SESSION['pwd_error']);
				if ($user_info ['user_status'] == 1) {

					// 送果果
					include_once("Rule/Rule.class.php");
					Rule::login($user['user_id'],$user ['user_name']);

					$user_ext = $usermod->getUserExt ( $user ['user_id'] );
					$msg_noread_no = $usermod->getUserMsgNum( $user ['user_id']);
					$user ['user_passwd'] = $user_info ['user_passwd'];
					$user ['user_score'] = $user_ext ['user_score'];
					$user ['user_host'] = $user_ext ['user_host'];
					$user ['user_gender'] = $user_ext ['user_gender'];
					$user ['user_coin'] = $user_ext ['user_coin'];
					$user ['user_cash'] = $user_ext ['user_cash'];
					$user ['city_code'] = $user_ext ['city_code'];
					$user ['town_code'] = $user_ext ['town_code'];
					$user ['province_code'] = $user_ext ['province_code'];
					$user ['user_rank'] = 1;
					$user ['user_vip'] =  $user_ext ['user_vip'];
					$user ['user_vip_time'] =  $user_ext ['user_vip_time'];
					$user ['user_icon'] =  $user_ext ['user_icon'];
					$user ['autologin'] = $cookie_remember;
					$user ['user_vote_total'] = $user_ext ['user_vote_total'];
					$user ['user_vote_num'] = $user_ext ['user_vote_num'];
					$user ['msg_noread_no'] = $msg_noread_no;
					$user['user_last_logtime'] = (empty($user_ext['user_last_logtime']))?'':$user_ext['user_last_logtime'];
					$this->set_cookie ( $user );
					$usermod->updateUserExtInfo ( $user );

					//如果不是在商城登陆的,要种cookie['VIEW_GENDER']
					if(strpos($redirect_url,'show')==false){
						$widget_gender = '';
						$widget_gender = $usermod->getUserWidgetGender( $user ['user_id'] );
						if($widget_gender==1) $widget_gender='boy';
						if($widget_gender==2) $widget_gender='girl';
						setcookie("VIEW_GENDER",$widget_gender,0,'/',COOKIE_DOMAIN);
					}

					//记录登陆日志
					curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user_name."&userid=".$user ['user_id']);


					if (empty ( $redirect_url )){
						echo $GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/forwardbbs";
						die;
					} else{
						if(strpos($redirect_url,'http')===false) $redirect_url = $GLOBALS ['gSiteInfo'] ['www_site_url'].$redirect_url;
						echo $GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php?action=passport&view=forwardbbs&gourl=".$redirect_url;
						die ();
					}
				} else {
					$msg = "-1";
				}
			} else {
				if(isset($_SESSION['pwd_error'])){
					$_SESSION['pwd_error']=$_SESSION['pwd_error']+1;
				}else{
					$_SESSION['pwd_error']=1;
				}
				if($_SESSION['pwd_error']>3){
					echo "-4";die;
				}
				$msg = "-2";
			}
		} else {
		if(isset($_SESSION['pwd_error'])){
					$_SESSION['pwd_error']=$_SESSION['pwd_error']+1;
				}else{
					$_SESSION['pwd_error']=1;
				}
				if($_SESSION['pwd_error']>3){
					echo "-4";die;
				}
			$msg = "-3";
		}
		echo $msg;
		die ();
	}
	function view_forwardbbs() {

		$user = authenticate ();
		if ($user != false) {

			$passport_key=$GLOBALS ['bbs']['key'];  //通行证私有密匙
			$member = array
			(
				        'cookietime'    => 0,
				        'time'      => time(),
				        'username'  => $user ['0'],  //用户名
				        'password'  => $_COOKIE['IDOL_AUTH'] ,        //密码
				        'email'     => $user['13'],         //emial
					    'nickname'=>$user [2]
			);

			$action     = 'login';  //状态为登录
			$forward = $GLOBALS ['gSiteInfo'] ['www_site_url'];
			if(isset($_GET['gourl']) && !empty($_GET['gourl'])){
				$forward=$_GET['gourl'];
			}
			include_once ("PassportModel.class.php");
			$auth		= PassportModel::passport_encrypt(PassportModel::passport_encode($member), $passport_key);
			$verify		= md5($action.$auth.$forward.$passport_key);


			header("Location: ".$GLOBALS ['gSiteInfo'] ['bbs_site_url']."/api/passport.php".
							"?action=$action".
							"&auth=".rawurlencode($auth).
							"&forward=".rawurlencode($forward).
							"&verify=$verify");
			die;
		}

		header("Location: ".$GLOBALS ['gSiteInfo'] ['www_site_url']);
		die;
	}
	function view_autologin() {

		//初始化道具
		$encrypted_data = '';
		if (! empty ( $_GET ['ticket'] ) && (!preg_match("/[^0123456789abcdef]/i",$_GET ['ticket']))) {
			$encrypted_data = pack ( "H*", $_GET ['ticket'] );
			$from = 'client';
		} else if (isset ( $_COOKIE ['IDOL_TICKET'] ) && ! empty ( $_COOKIE ['IDOL_TICKET'] ) && (!preg_match("/[^0123456789abcdef]/i",$_COOKIE ['IDOL_TICKET']))) {
			$encrypted_data = pack ( "H*", $_COOKIE ['IDOL_TICKET'] );
			$from = 'user';
		}
		if(!empty($encrypted_data)){
			$key = TICKET_KEY;
			$td = mcrypt_module_open ( 'des', '', 'ecb', '' );
			$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
			$key = substr ( $key, 0, mcrypt_enc_get_key_size ( $td ) );

			/* Initialize encryption module for decryption */
			mcrypt_generic_init ( $td, $key, $iv );
			$decrypted_data = mdecrypt_generic ( $td, $encrypted_data );
			mcrypt_generic_deinit ( $td );
			mcrypt_module_close ( $td );

			//echo "text: ".trim($decrypted_data);
			if(!empty($decrypted_data) && preg_match("/.*?\|.{32}\|.*?\|\d*?/ism",$decrypted_data)){
				list ( $user_name, $pwd_md5, $nickname, $time ) = explode ( "|", $decrypted_data );

				if (($time >= time () - 5 && $from == 'client') || ($from == 'user')) {
					include_once("ApiUser.class.php");
					$user = ApiUser::getUserByName( $user_name );
					if ($user !== false) {

						include_once("UserModel.class.php");
						$usermod = new UserModel($user['user_db_key']);
						$user_info = $usermod->getUserById($user['user_id']);

						if ($user_info ['user_passwd'] == $pwd_md5) {
							if ($user ['user_status'] == 1) {

								// 送果果
								include_once("Rule/Rule.class.php");
								Rule::login($user['user_id'],$user ['user_name']);

								$user_ext = $usermod->getUserExt ( $user ['user_id'] );
								$msg_noread_no = $usermod->getUserMsgNum($user['user_id']);
								$user ['user_passwd'] = $user_info ['user_passwd'];
								$user ['user_score'] = $user_ext ['user_score'];
								$user ['user_coin'] = $user_ext ['user_coin'];
								$user ['user_cash'] = $user_ext ['user_cash'];
								$user ['user_host'] = $user_ext ['user_host'];
								$user ['user_gender'] = $user_ext ['user_gender'];
								$user ['city_code'] = $user_ext ['city_code'];
								$user ['town_code'] = $user_ext ['town_code'];
								$user ['province_code'] = $user_ext ['province_code'];
								$user ['user_rank'] = 1;
								$user ['user_vip'] = $user_ext ['user_vip'];
								$user ['user_vip_time'] = $user_ext ['user_vip_time'];
								$user ['user_icon'] = $user_ext ['user_icon'];
								$user ['autologin'] = 0;
								$user ['user_vote_total'] = $user_ext ['user_vote_total'];
								$user ['user_vote_num'] = $user_ext ['user_vote_num'];
								$user ['msg_noread_no'] = $msg_noread_no;
								$user['user_last_logtime'] = (empty($user_ext['user_last_logtime']))?'':$user_ext['user_last_logtime'];
								$usermod->updateUserExtInfo ( $user );
								$this->set_cookie ( $user );

								$widget_gender = '';
								$widget_gender = $usermod->getUserWidgetGender( $user ['user_id'] );
								if($widget_gender==1) $widget_gender='boy';
								if($widget_gender==2) $widget_gender='girl';
								setcookie("VIEW_GENDER",$widget_gender,0,'/',COOKIE_DOMAIN);
								
								//show_message ( $msg );
								//记录登陆日志
								curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user_name."&userid=".$user ['user_id']);

								$backurl = ! empty ( $_GET ['backurl'] )?urlencode($_GET ['backurl']):$GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/show/frameset";

								redirect ( $GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php?action=passport&view=forwardbbs&gourl=".urlencode($backurl) );
								die;

							} else {
								$msg = "此用户名被停用!";
							}
						} else {
							$msg = "密码错误!";
						}
					} else {
						$msg = "用户名不存在!";
					}
				} else {
					$msg = "此链接无效!";
				}
			}else{
				$msg = "非法登陆!";
			}
		}else{
			$msg = "非法登陆!";
		}
		show_message ( $msg );
		//redirect ( $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/.php/index/index" );
		goback();
	}
	function view_logout() {
		$user = authenticate ();
		if ($user != false) {
			setcookie ( 'buy_car_data' . $user [1], '', time () - 3600, '/', COOKIE_DOMAIN ); //"buy_car_data"+this.UserId
			setcookie ( 'try_data' . $user [1] . $user [6], '', time () - 3600, '/', COOKIE_DOMAIN );
		}
		setcookie ( 'IDOL_INFO', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_TOKEN', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_STATE', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_TICKET', '', time () - 3600, '/', COOKIE_DOMAIN );
		unset($_SESSION['cart']);

		//由于数据缓存不能使用JS后退的函数，所以改变成以下函数（注意）
		//与论坛一起注销
		$gourl="";
		if(isset($_GET['forward'])){
			$gourl = $_GET['forward'];
		}else if(isset($_GET ['url'])){
			$gourl= $GLOBALS ['gSiteInfo'] ['www_site_url'] . $_GET ['url'];
		}

		redirect("/index.php?action=passport&view=logoutbbs&gourl=".urlencode ($gourl),1);

		die ();

	}
	function view_logoutbbs() {

		$passport_key=	$GLOBALS ['bbs']['key'];
		$action     = 'logout';  //状态为登录
		$forward = $GLOBALS ['gSiteInfo'] ['www_site_url'];
		if(isset($_GET['gourl']) && !empty($_GET['gourl'])){
			$forward=$_GET['gourl'];
		}
		$auth		= '';//PassportModel::passport_encrypt(PassportModel::passport_encode($member), $passport_key);
		$verify		= md5($action.$auth.$forward.$passport_key);
		unset($_SESSION['user_info']);
		header("Location: ".$GLOBALS ['gSiteInfo'] ['bbs_site_url']."/api/passport.php".
							"?action=$action".
							"&auth=".rawurlencode($auth).
							"&forward=".rawurlencode($forward).
							"&verify=$verify");

		die;
	}
	function set_cookie($user) {
		$str = 'abcedfghijklmnopqrstuvwxyz';
		$rand_str = $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )];
		$time = time ();
		$token = md5 ( microtime () );
		$key = md5 ( $user ['user_name'] . $token . $time . $rand_str );

		if ($user ['user_vote_num'] != 0) {
			$average = round ( $user ['user_vote_total'] / $user ['user_vote_num'], 0 ); //平均分数=评分总数/评分人数
		} else {
			$average = 0;
		}

		$user_arr[0] = $user ['user_name'];
		$user_arr[1] = $user ['user_id'];
		$user_arr[2] = $user ['user_nickname'];
		$user_arr[3] = $user ['user_cash'];
		$user_arr[4] = $user ['user_rank'];
		$user_arr[5] = "http://" . $user ['user_host'] ;
		$user_arr[6] = $user ['user_gender'];
		$user_arr[7] = $user ['city_code'];
		$user_arr[8] = $user ['town_code'];
		$user_arr[9] = $user ['province_code'];
		$user_arr[10] = $user ['user_vote_num'];
		$user_arr[11] = $average;
		$user_arr[12] = $user ['user_db_key'];
		$user_arr[13] = $user ['user_email'];
		$user_arr[14] = $user ['user_vip'];
		$user_arr[15] = $user ['user_coin'];
		$user_arr[16] = !empty($user ['user_icon'])?$user ['user_icon']:$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/noavatar.gif";
		$enc_info = encrypt ( json_encode($user_arr), $key );


		setcookie ( 'IDOL_TOKEN', $token, 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_STATE', urlencode ( $time . '|' . $user ['user_name'] . '|' . $key . '|**|' . $rand_str ), 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_INFO', $enc_info, 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_AUTH', $user ['user_passwd'], 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_COIN_' . $user ['user_name'], $user ['user_coin'], 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_CASH_' . $user ['user_name'], $user ['user_cash'], 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_VIP_' . $user ['user_name'], $user ['user_vip_time'], 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_MSGNUM_' . $user ['user_name'], $user ['msg_noread_no'], 0, '/', COOKIE_DOMAIN );
		setcookie ( 'IDOL_USERNAME' ,$user ['user_name'], time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN);
		setcookie ( 'IDOL_NICKNAME' ,urlencode($user ['user_nickname']), time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN);
		if ($user ['autologin'] == 1) {
			//setcookie ( 'IDOL_USERNAME' ,$user ['user_name'], time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN);
			$key = TICKET_KEY;
			$td = mcrypt_module_open ( 'des', '', 'ecb', '' );
			$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
			$key = substr ( $key, 0, mcrypt_enc_get_key_size ( $td ) );
			mcrypt_generic_init ( $td, $key, $iv );
			$input = $user ['user_name'] . '|' . $user ['user_passwd'] . "|" . $user ['user_nickname'] . "|" . time ();
			$encrypted_data = mcrypt_generic ( $td, $input );
			$encrypted_data = bin2hex ( $encrypted_data );
			setcookie ( 'IDOL_TICKET', $encrypted_data, time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN );
		}
	}
	/****登录相关方法 结束*****/

	/****注册相关方法 开始*****/
	function view_checkusername(){
		$user = !empty($_GET['user'])?$_GET['user']:'';
		//拒绝夸域名访问保护
		if (! empty ( $_SERVER ['HTTP_REFERER'] )) {
			$arr = parse_url ( $_SERVER ['HTTP_REFERER'] );
			if(strpos($arr['host'],COOKIE_DOMAIN)=== false) die('deny!');
		} else {
			die ( 'deny!' );
		}
		include_once("PassportModel.class.php");
		$pasmod = new PassportModel();
		$row = $pasmod->checkUserName($user);
		if ($row > 0) {
			$recommend_user = array ();
			for($i = 0; $i < 3; $i ++) {
				$recommend_user [] = $pasmod->getRecommendUserName ( $user, $i );
			}
			echo 'true|' . join ( ',', $recommend_user );
		} else if ($pasmod->isBlockword ( $user )) {
			$recommend_user = array ();
			$user = substr ( $user, 0, - 1 ) . '_';
			for($i = 0; $i < 3; $i ++) {
				$recommend_user [] = $pasmod->getRecommendUserName ( $user, $i );
			}
			echo 'true|' . join ( ',', $recommend_user );
		} else {

			echo 'false|';
		}
		exit();
	}
	function view_checknickname(){
		$nick = !empty($_GET['nick'])?$_GET['nick']:'';
		//拒绝夸域名访问保护
		if (! empty ( $_SERVER ['HTTP_REFERER'] )) {
			$arr = parse_url ( $_SERVER ['HTTP_REFERER'] );
			if(strpos($arr['host'],COOKIE_DOMAIN)=== false) die('deny!');
		} else {
			die ( 'deny!' );
		}
		include_once("PassportModel.class.php");
		$pasmod = new PassportModel();
		$row = $pasmod->checkNickName($nick);
		if ($row > 0 || $pasmod->isBlockword ( $nick )) {
			echo 'true';
		} else {
			echo 'false';
		}
		exit();
	}
	function view_checkemail(){
		$email= !empty($_GET['email'])?$_GET['email']:'';
		//拒绝夸域名访问保护
		if (! empty ( $_SERVER ['HTTP_REFERER'] )) {
			$arr = parse_url ( $_SERVER ['HTTP_REFERER'] );
			if(strpos($arr['host'],COOKIE_DOMAIN)=== false) die('deny!');
		} else {
			die ( 'deny!' );
		}
		include_once("PassportModel.class.php");
		$pasmod = new PassportModel();
		$row = $pasmod->checkEmail($email);
		if ($row > 0 || $pasmod->isBlockword ( $email )) {
			echo 'true';
		} else {
			echo 'false';
		}
		exit();
	}
	function view_reg() {
		global $tpl;
		$reg_html = APP_DIR . '/reg.html';
		$sponsor = isset ( $_GET ['sponsor'] ) ? $_GET ['sponsor'] : '';

		$_SESSION ['User_Sex'] = ! isset ( $_SESSION ['User_Sex'] ) ? '' : $_SESSION ['User_Sex'];
		$_SESSION ['User_Account'] = ! isset ( $_SESSION ['User_Account'] ) ? '' : $_SESSION ['User_Account'];
		$_SESSION ['User_Email'] = ! isset ( $_SESSION ['User_Email'] ) ? '' : $_SESSION ['User_Email'];
		$_SESSION ['User_Nickname'] = ! isset ( $_SESSION ['User_Nickname'] ) ? '' : $_SESSION ['User_Nickname'];

		$forward=$GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/regok";
		if(isset($_GET ['forward'])) {
			$redirect_url = isset ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
		}

		$tpl->assign ( 'User_Account', $_SESSION ['User_Account'] );
		$tpl->assign ( 'User_Email', $_SESSION ['User_Email'] );
		$tpl->assign ( 'User_Nickname', $_SESSION ['User_Nickname'] );
		$tpl->assign ( 'select_sex', $_SESSION ['User_Sex'] );

		$tpl->assign ( 'sponsor', $sponsor );
		$tpl->assign ( 'forward', $forward );
		$tpl->assign ( 'title', "注册-果动网-果然会动-网页3D娱乐" );




		if(!file_exists($reg_html)) Controller::createHtml($reg_html);
	}
	function view_regok() {
		global $tpl;

		$emailurl = empty ( $_GET ['email'] ) ? '' : $_GET ['email'];
		$mail_arr = explode ( "@", $emailurl );
		if (isset ( $mail_arr [1] ))
		$emailurl = "http://www." . $mail_arr [1];
		$tpl->assign ( 'emailurl', $emailurl );
		$tpl->assign ( 'title', "注册-果动网-果然会动-网页3D娱乐" );
	}
	function op_saveuser() {
		$msg = '';
		$_POST ['User_Sex'] = ($_POST ['User_Sex'] ==1)?$_POST ['User_Sex']:2;
		$_SESSION ['User_Account'] = $_POST ['User_Account'];
		$_SESSION ['User_Email'] = $_POST ['User_Email'];
		$_SESSION ['User_Nickname'] = $_POST ['User_Nickname'];
		$_SESSION ['User_Sex'] = $_POST ['User_Sex'];
		//在php端对变量的规则进行判断.
		$pattern = "/^[a-zA-Z][a-zA-Z0-9_]{1,13}[a-zA-Z0-9]$/i";
		$pattern2 = "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/";
		if (!isset($_SESSION['validatecode']) || ($_POST['User_Code']!='back' && strtolower($_POST['User_Code'])!=strtolower($_SESSION['validatecode']))) {
			show_message_goback ( "验证码输入错误!" );
		}
		$_POST ['User_Account'] = trim ( $_POST ['User_Account'] );
		if (empty ( $_POST ['User_Account'] ) || ! preg_match ( $pattern, $_POST ['User_Account'] )) {
			show_message_goback ( "用户名只能由3-15位字母(a-z)、数字(0-9)或下划线(_)构成，并且必须以字母开头!" );
		}
		if (empty ( $_POST ['User_Email'] ) || ! preg_match ( $pattern2, $_POST ['User_Email'] )) {
			show_message_goback ( "您的电子邮箱格式有误，请修改!" );
		}
		if ($_POST ['User_Password'] != $_POST ['User_RePassword']) {
			show_message_goback ( "两个密码不一致，请修改!" );
		}
		$_POST ['User_Nickname'] = trim ( $_POST ['User_Nickname'] );
		$nickname_len = mb_strlen ( $_POST ['User_Nickname'], "UTF-8");
		if (empty ( $_POST ['User_Nickname'] ) || $nickname_len < 2 || $nickname_len > 8) {
			show_message_goback ( "昵称不能为空,不能少于2位,大于8位!" );
		}
		$_POST ['User_Sex'] = intval($_POST ['User_Sex']);
		if (empty($_POST ['User_Sex'])) {
			show_message_goback ( "请选择性别！" );
		}


		include_once("PassportModel.class.php");
		$passmod = new PassportModel();

		$count = $passmod->checkUserName ( $_POST ['User_Account'] );
		$count1 = $passmod->checkEmail ( $_POST ['User_Email'] );
		$count2 = $passmod->checkNickName ( htmlspecialchars ( $_POST ['User_Nickname'] ) );
		if ($count > 0 || $count1 > 0 || $count2 > 0) {
			$msg = "用户名 或者 邮箱 或者 昵称 已存在!";
			show_message_goback ( $msg );

		} else {
			$city_code = 0;
			$town_code = 0;
			$province_code = 0;

			$sponsor = ! empty ( $_POST ['sponsor'] ) ? $_POST ['sponsor'] : '';
			$user = array ('user_name' => $_POST ['User_Account'], 'user_passwd' => md5 ( $_POST ['User_Password'] ), 'user_email' => $_POST ['User_Email'], 'user_nickname' => htmlspecialchars ( $_POST ['User_Nickname'] ), 'user_gender' => $_POST ['User_Sex'], 'user_province' => $province_code, 'user_city' => $city_code, 'user_town' => $town_code, 'city_code' => $city_code, 'town_code' => $town_code, 'province_code' => $province_code );
			$user ['user_rank'] = 0;
			$user ['autologin'] = 0;
			$user ['user_coin'] = 0;

			// 1. create db user
			$row = $passmod->createNewUser ( $user );
			if ($row !== false) {
				// update the ad_id in order to  dealing register count
				if (! empty ( $_COOKIE ['advertise_uid'] )) {
					$medium_id = $_COOKIE ['advertise_uid'];
					$passmod->updateMediumIDByUser ( $row['db_key'],$row['user_id'], $medium_id );
					setcookie ( 'advertise_uid', '', time()-3600, '/', COOKIE_DOMAIN );
				}

				// 送果果
				include_once("Rule/Rule.class.php");
				$reg_coin = Rule::reg($row['user_id'],$user ['user_name']);

				//  add user to user_sponsor,user_friend table
				if ($sponsor != '') {
					$sponsor_userid = $passmod->addUserSponsor (  $row['db_key'],$row['user_id'], $sponsor, $user ['user_name'], $user ['user_email'], $user ['user_nickname'] );
				}


				// 6.自动登录
				$user ['user_id'] = $row['user_id'];
				$user ['user_score'] = 0;
				$user ['user_coin'] = $reg_coin;
				$user ['user_cash'] = 0;
				$user ['user_host'] = '';
				$user ['user_vote_total'] = 0;
				$user ['user_vote_num'] = 0;
				$user ['user_db_key'] = $row['db_key'];
				$user ['user_vip'] = 0;
				$user ['user_vip_time'] = 0;
				$user ['user_icon'] = '';
				$user ['msg_noread_no'] = 1;
				//
				$_SESSION ['User_Sex'] = '';
				$_SESSION ['User_Account'] = '';
				$_SESSION ['User_Email'] = '';
				$_SESSION ['User_Nickname'] = '';

				$this->set_cookie ( $user );

				// 默认商城显示
				$view_gender ='';
				if($user['user_gender']==1) $view_gender = 'boy';
				if($user['user_gender']==2) $view_gender = 'girl';
				setcookie("VIEW_GENDER",$view_gender,0,'/',COOKIE_DOMAIN);

				//记录登陆日志
				curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user ['user_name']."&userid=".$row['user_id']);

				//如果需要同步到论坛系统里
				//if(isset($_POST['sysbbs']) && $_POST['sysbbs']=="1"){
				$passport_key=$GLOBALS ['bbs']['key'];  //通行证私有密匙
				$member = array
				(
			        'cookietime'    => 0,
			        'time'      => time(),
			        'username'  => $_POST ['User_Account'],  //用户名
			        'password'  => md5 ( $_POST ['User_Password'] ),        //密码
			        'email'     => $_POST ['User_Email'],         //emial
					'nickname'=>$_POST ['User_Nickname']
				);
				$action     = 'login';  //状态为登录
				$forward    = $GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/regok";
				if(isset($_GET ['forward'])) {
					$forward = $_GET ['forward'];
				}
				$auth		= PassportModel::passport_encrypt(PassportModel::passport_encode($member), $passport_key);
				$verify		= md5($action.$auth.$forward.$passport_key);
				header("Location: ".$GLOBALS ['gSiteInfo'] ['bbs_site_url']."/api/passport.php".
						"?action=$action".
						"&auth=".rawurlencode($auth).
						"&forward=".rawurlencode($forward).
						"&verify=$verify");

				//}

				//redirect("index.php?action=user&view=personal1");
				//header ( "location: /index.php/passport/regok/" );

			}

		}
	}
	function op_saveuserapi(){
		$msg = '';
		$_POST['User_Account'] = !isset($_POST['User_Account'])?'':$_POST['User_Account'];
		$_POST['User_Email'] = !isset($_POST['User_Email'])?'':$_POST['User_Email'];
		$_POST['User_Sex'] = !isset($_POST['User_Sex'])?'':$_POST['User_Sex'];
		$_POST['User_Password'] = !isset($_POST['User_Password'])?'':$_POST['User_Password'];
		$_POST['User_RePassword'] = !isset($_POST['User_RePassword'])?'':$_POST['User_RePassword'];
		$_POST['User_Nickname'] = !isset($_POST['User_Nickname'])?'':$_POST['User_Nickname'];
		//在php端对变量的规则进行判断.
		$pattern = "/^[a-zA-Z][a-zA-Z0-9_]{1,13}[a-zA-Z0-9]$/i";
		$pattern2= "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/";
		$_POST['User_Account'] = trim($_POST['User_Account']);
		if(empty($_POST['User_Account']) || !preg_match($pattern,$_POST['User_Account'])){
			$msg ="用户名只能由3-15位字母(a-z)、数字(0-9)或下划线(_)构成，并且必须以字母开头!";
		}
		if(empty($_POST['User_Email']) || !preg_match($pattern2,$_POST['User_Email'])){
			$msg ="您的电子邮箱格式有误，请修改!";
		}
		if($_POST['User_Password']!=$_POST['User_RePassword']){
			$msg = "两个密码不一致，请修改!";
		}
		$_POST['User_Nickname'] = trim($_POST['User_Nickname']);
		$nickname_len = mb_strlen($_POST['User_Nickname']);
		if(empty($_POST['User_Nickname'])|| $nickname_len<2 || $nickname_len>8){
			$msg = "昵称不能为空,不能少于2位,大于8位!";
		}
		if($_POST['User_Sex']==''){
			$msg ="请选择性别！";
		}
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$count = $passmod->checkUserName(trim($_POST['User_Account']));
		$count1 = $passmod->checkEmail(trim($_POST['User_Email']));
		$count2 = $passmod->checkNickName(htmlspecialchars(trim($_POST['User_Nickname'])));
		if ($count > 0 || $count1 > 0 || $count1 > 0)
		{
			$msg = "用户名 或者 邮箱 或者 昵称 已存在!";
		}
		else
		{
			$city_code = 0;
			$town_code = 0;
			$province_code = 0;
			$gender = empty($_POST['select_sex'])?1:$_POST['select_sex'];
			$select_idol = empty($_POST['select_idol'])?1:$_POST['select_idol'];

			$user = array('user_name'=>trim($_POST['User_Account']),
						  'user_passwd'=>md5($_POST['User_Password']),
						  'user_email'=>trim($_POST['User_Email']),
						  'user_nickname'=>htmlspecialchars($_POST['User_Nickname']),
						  'user_gender'=>$gender,
						  'user_province'=>$province_code,
						  'user_city'=>$city_code,
						  'user_town'=>$town_code,
						  'city_code'=>$city_code,
						  'town_code'=>$town_code,
						  'province_code'=>$province_code);
			$user['user_rank'] = 0;
			$user['autologin'] = 0;
			$user['comment'] = '';
			$user ['user_coin'] = 0;

			// 1. create db user
			$row = $passmod->createNewUser($user);

			if ($row !== false) {
				echo  "<username>{$user['user_name']}</username>";
				die;
			}
		}
		echo "<msg>".$msg."</msg>";
		die;
	}
	function view_regframe2() {
		global $tpl;
		$v_user = array ();
		$src = "";
		$src2 = "";
		$res = "";
		if (! empty ( $_GET ['sponsor'] )) {
			include_once("ApiUser.class.php");
			include_once("UserModel.class.php");
			$user = ApiUser::getUserByName( $_GET ['sponsor']);
			$userModel = new UserModel( $user['user_db_key']);
			$v_user = $userModel->getUserExt( $user['user_id'] );
			$src = $v_user ['user_icon'] ;
			$src2 = $v_user ['user_pic'] ;
		} else {
			$v_user ['user_nickname'] = '';
			$v_user ['user_name'] = '';
		}
		$sponsor = isset($_GET ['sponsor'])?$_GET ['sponsor']:'';
		$tpl->assign ( "title", $v_user['user_name']."邀请你加入果动网" );
		$tpl->assign ( 'res', $sponsor );
		$tpl->assign ( 'vuser', $v_user );
		$tpl->assign ( 'src', $src );
		$tpl->assign ( 'src2', $src2 );
	}
	/****注册相关方法 结束*****/

	/****找回密码相关方法 开始****/
	function view_forget() {
		global $tpl;
		$type = "";
		$msg = "";
		$user = "";
		$email = "";
		if (! empty ( $_GET ['username'] )) {
			$user = $_GET ['username'];
		}
		if (! empty ( $_GET ['email'] )) {
			$email = $_GET ['email'];
		}
		if (! empty ( $_GET ['flag'] )) {
			$type = $_GET ['flag'];
			switch ($type) {
				case "00" :
					$msg = "用户名或邮箱地址不能为空!";
					break;
				case "1" :
					$msg = "处理成功，请查阅您的邮箱！如果长时间仍然没有收到,请重试.";
					break;
				case "2" :
					$msg = "";
					break;
				case "3" :
					$msg = "用户名输入错误！";

					break;
				case "4" :
					$msg = "邮箱地址输入错误！请输入您注册时填写的地址";
					break;
				case "5" :
					$msg = "服务器邮件发送配置错误.";
					break;
				case "6" :
					$msg = "服务器邮件主机不能连接.";
					break;
				default :
					$msg = "";
					;
					break;
			}

		}
		$tpl->assign ( 'user', $user );
		$tpl->assign ( 'email', $email );
		$tpl->assign ( 'title', '找回密码-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'msg', $msg );
	}
	function view_confirmpass() {
		global $tpl;
		if (empty ( $_GET ['code'] ) or empty ( $_GET ['username'] )) {
			redirect ( '/index.php/passport/login' );
			die ();
		}

		$code = $_GET ['code'];
		$username = $_GET ['username'];
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$flag = "0";
		if ($passmod->checkForget ( $code, $username ) < 1) {
			$flag = "1";

			show_message("此链接已经失效!");
			echo "<script> alert('此链接已经失效!')</script>";
			//sleep(2) ;
			redirect ( '/index.php/passport/login' );
		}
		$tpl->assign ("title","找回密码-果动网-分享3D生活-美化我的博客空间");
		$tpl->assign ( "code", $code );
		$tpl->assign ( "username", $username );

	}
	function op_confirmpass() {
		global $_POST;

		if (empty ( $_POST ['code'] ) or empty ( $_POST ['username'] )) {
			echo "44"; //	 redirect('index.php?action=index&view=login');
			die ();
		}

		if (empty ( $_POST ['new1'] ) or empty ( $_POST ['new2'] )) {
			echo "22"; //echo "<script>history.go(-1);</script>";
			die ();
		}

		$new1 = $_POST ['new1'];
		$new2 = $_POST ['new2'];
		if (strlen ( $new1 ) < 6 or strlen ( $new2 ) < 6) {
			echo "66"; //两次密码不一致redirect('index.php?action=index&view=confirmpass&flag=2&new1='.$new1);
			die ();
		}

		if (trim ( $new1 ) != trim ( $new2 )) {
			echo "33"; //两次密码不一致redirect('index.php?action=index&view=confirmpass&flag=2&new1='.$new1);
			die ();
		}

		$code = $_POST ['code'];
		$username = $_POST ['username'];
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$flag = 0;
		$flag = $passmod->checkForget ( $code, $username );
		if ($flag != 0) {
			include_once("UserModel.class.php");
			include_once("ApiUser.class.php");
			$user = ApiUser::getUserByName($username);
			$usermod = new UserModel ( $user['user_db_key'] );
			if ($usermod->updatePassByUsername ( $username, $new1 ) > 0) {
				$passmod->updateForgetPwd($username);
				echo "11"; //redirect('index.php?action=index&view=confirmpass&flag=1');
				die ();
			} else {
				echo "55"; //
			}

		} else {
			echo "44"; //校验错误或时间过期redirect("index.php?action=index&view=confirmpass&flag=4&new1=$new1&code=$code&username=$username");
			die ();

		}

	}
	function op_forget() {
		global $_POST;

		$flag = 0;

		if (empty ( $_POST ['username'] ) or empty ( $_POST ['email'] )) {
			echo "00";
			exit ();
		}
		$username = trim ( $_POST ['username'] );
		$email = trim ( $_POST ['email'] );
		$username = htmlspecialchars ( $username );
		$username = htmlentities ( $username );

		$email = htmlspecialchars ( $email );
		$email = htmlentities ( $email );
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();

		if (! ($passmod->checkUserName ( $username ) > 0)) {
			echo "3";
			exit ();
		}
		if ($passmod->checkForgetEmail ( $username, $email )<1) {
			echo "4";
			exit ();
		}
		$code = $passmod->addForgetPwd ( $username );
		if ($code == 2 or $code == 5) {
			echo $code;
			die ();
		}

		$link = "<a href='" . $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php/passport/confirmpass/code/" . $code . "/username/" . $username . "'>" . $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php/passport/confirmpass/code/" . $code . "/username/" . $username . "</a><br />";
		$content = ' 亲爱的 &lt;' . $username . '&gt; ：<br>
				  果动网已经收到你的密码找寻申请！请点下边的连接设置你的新密码，此连接将在60分钟后失效。<br>' . $link . '<br>' . $GLOBALS ['account'] ['content'] ;

		$flag = send_email( "no-reply@guodong.com",$email, $GLOBALS ['account'] ['subject'], $content );
		print_r( $flag);
		die ();
	}
	/****找回密码相关方法 结束****/

	function view_widget(){
		// 送果果
		$userid = $_GET['userid'];

		include_once("ApiUser.class.php");
		$user = ApiUser::getUserByName($userid);
		if($user){
			include_once("Rule/Rule.class.php");
			//给自己送GG
			Rule::widget($user['user_id'],$user['user_name']);

			include_once("UserModel.class.php");
			$userModel  = new UserModel($user['user_db_key']);
			$userext = $userModel->getUserExt($user['user_id']);
			if(!empty($userext['user_sponsor'])){
				$sponsor_name  = $userext['user_sponsor'];
				$sponsor = ApiUser::getUserByName($sponsor_name);

				//给发起者送GG
				Rule::invite_widget($sponsor['user_id'],$sponsor['user_name'],$user['user_name']);
			}
		}

		die;

	}
}
?>