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
		$forward = isset ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
		$show_code = 0;
		if(isset($_SESSION['pwd_error'])) $show_code = $_SESSION['pwd_error'];
		if(!empty($_COOKIE['XPASS_IC_CARD'])) header("location: ".$GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/autologin");
		$tpl->assign ( 'forward', urlencode ( $forward ) );
		$tpl->assign ( 'show_code', $show_code );
	}
	function op_dologin() {
		$forward = ! empty ( $_POST ['forward'] ) ? $_POST ['forward'] : '';		
		$user = $_POST ['user'];
		$user_passwd = $_POST ['password'];
		
		if(isset($_SESSION['pwd_error']) && isset($_POST ['code'])){
			$vcode		 =strtolower($_POST ['code']);
			if($vcode!=strtolower($_SESSION['validatecode'])){
				show_message_goback("验证码输入错误");
			}
		}
		
		$cookie_remember = ! empty ( $_POST ['remember'] ) ? $_POST ['remember'] : '0';
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$user_arr = $passmod->getUser($user);
		if ($user_arr) {
			
			$user_info = $passmod->getUserById($user_arr['user_id'],$user);	
	
			
			if ($user_info ['user_password'] == md5 ( $user_passwd )) {
				if(isset($_SESSION['pwd_error'])) unset($_SESSION['pwd_error']);
				if ($user_info ['user_state'] == 1) {
								
					$updates['user_lastlogin_time'] = time();
					$updates['user_lastlogin_ip'] = getip();
					$passmod->updateUser( $updates, $user_arr['user_id'],$user);
				   
					$user_arr ['user_password'] = $user_info ['user_password'];							
					$user_arr ['user_email'] = $user_info ['user_email'];							
					$user_arr ['autologin'] = $cookie_remember;	
					$user_arr ['user_nickname'] = $user_info ['user_nickname'];		
					$user_arr ['user_sex'] = $user_info ['user_sex'];

					//自动登录
					$this->save_online_user ( $user_arr );
					
					//记录登录日志
					//curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user_email."&userid=".$user ['user_id']);
					
					if(!empty($forward)){
						header("location: ".$forward);
					}else{
						
						redirect("/index.php");
					}
									
				} else {
					$msg = "此用户被停用!";
				}
				
			} else {
				if(isset($_SESSION['pwd_error'])){
					$_SESSION['pwd_error']=$_SESSION['pwd_error']+1;
				}else{
					$_SESSION['pwd_error']=1;
				}
				if($_SESSION['pwd_error']>3){
					$msg = "-4";
				}
				$msg = "密码错误!";
			}
		} else {
				if(isset($_SESSION['pwd_error'])){
					$_SESSION['pwd_error'] = $_SESSION['pwd_error']+1;
				}else{
					$_SESSION['pwd_error']=1;
				}				
				if($_SESSION['pwd_error']>3){
					$msg = "-4";
				}
				$msg = "用户名不存在!";
		}
		show_message_goback($msg);
		
	}
	function view_autologin() {

		$encrypted_data = '';
		if (! empty ( $_GET ['ticket'] ) && (!preg_match("/[^0123456789abcdef]/i",$_GET ['ticket']))) {
			$encrypted_data = pack ( "H*", $_GET ['ticket'] );
			$from = 'client';
		} else if (isset ( $_COOKIE ['XPASS_IC_CARD'] ) && ! empty ( $_COOKIE ['XPASS_IC_CARD'] ) && (!preg_match("/[^0123456789abcdef]/i",$_COOKIE ['XPASS_IC_CARD']))) {
			$encrypted_data = pack ( "H*", $_COOKIE ['XPASS_IC_CARD'] );
			$from = 'user';
		}
		if(!empty($encrypted_data)){
			$key = 'Powered by Xpass!';
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
				list ( $user, $pwd_md5, $nickname, $time ) = explode ( "|", $decrypted_data );

				if (($time >= time () - 5 && $from == 'client') || ($from == 'user')) {
					
					include_once("PassportModel.class.php");
					$passmod = new PassportModel();
					$userindex = $passmod->getUser($user);
					if ($userindex !== false) {

						
						$user_info = $passmod->getUserById($userindex['user_id'],$user);

						if ($user_info ['user_password'] == $pwd_md5) {
							if ($user_info ['user_state'] == 1) {
			
								$updates['user_lastlogin_time'] = time();
								$updates['user_lastlogin_ip'] = getip();
								
								$passmod->updateUser( $updates, $userindex['user_id'],$user);
								
								$user ['user_password'] = $user_info ['user_password'];
								$user ['user_sex'] = $user_info ['user_sex'];
								$user ['user_nickname'] = $user_info ['user_nickname'];
								$user ['autologin'] = 0;
								

								$this->save_online_user ( $user );

								
								//记录登录日志
								//curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user_email."&userid=".$user ['user_id']);
								if(!empty($forward)){
									header("location: ".$forward);
								}else{
									$msg = "Login on. Welcome, ".$user_info['user_nickname']." go to <a href='/index.php/passport/logout'>log out</a>";
									show_message($msg);
									die;
								}

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
				$msg = "非法登录!";
			}
		}else{
			$msg = "非法登录!";
		}
		show_message ( $msg );
		
		goback();
	}
	function view_logout() {
		
		setcookie ( 'XPASS_INFO', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'XPASS_TOKEN', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'XPASS_STATE', '', time () - 3600, '/', COOKIE_DOMAIN );
		setcookie ( 'XPASS_IC_CARD', '', time () - 3600, '/', COOKIE_DOMAIN );
		session_unset();
		session_destroy();
		show_message('您已经安全退出！');
		redirect("/index.php/passport/login");
	}
	function save_online_user($user) {
		
		if(SSO_MODE=='ticket'){
			
		}elseif(SSO_MODE=='session'){
			
			$this->set_session($user);
			
		}else{
		
			$str = 'abcedfghijklmnopqrstuvwxyz';
			$rand_str = $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )] . $str [rand ( 0, 25 )];
			$time = time ();
			$token = md5 ( microtime () );
			$key = md5 ( $user ['user'] . $token . $time . $rand_str );
	
			$user_arr['user'] 	= $user ['user'];
			$user_arr['user_id'] 	= $user ['user_id'];
			$user_arr['user_nickname'] = $user ['user_nickname'];		
			$user_arr['user_sex'] 	= $user ['user_sex'];	
			$user_arr['user_email'] 	= $user ['user_email'];
			$user_arr['user_password'] = $user ['user_password'];
	
			$enc_info = encrypt ( json_encode($user_arr), $key );
	
			setcookie ( 'XPASS_TOKEN', $token, 0, '/', COOKIE_DOMAIN );
			setcookie ( 'XPASS_STATE', urlencode ( $time . '|' . $user ['user'] . '|' . $key . '|**|' . $rand_str ), 0, '/', COOKIE_DOMAIN );
			setcookie ( 'XPASS_INFO', $enc_info, 0, '/', COOKIE_DOMAIN );
			setcookie ( 'XPASS_USERNAME' ,$user ['user'], time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN);
			setcookie ( 'XPASS_NICKNAME' ,urlencode($user ['user_nickname']), time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN);
			if ($user ['autologin'] == 1) {			
				$this->set_iccard($user);
			}
		}
	}
	function set_session($user){
		
		$_SESSION['_XpassOnlineUser'] = $user;
		if ($user ['autologin'] == 1) {			
			$this->set_iccard($user);
		}
	}
	function  set_iccard($user){
		$key = 'Powered by Xpass!';
		$td = mcrypt_module_open ( 'des', '', 'ecb', '' );
		$iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
		$key = substr ( $key, 0, mcrypt_enc_get_key_size ( $td ) );
		mcrypt_generic_init ( $td, $key, $iv );
		$input = $user ['user'] . '|' . $user ['user_password'] . "|" . $user ['user_nickname'] . "|" . time ();
		$encrypted_data = mcrypt_generic ( $td, $input );
		$encrypted_data = bin2hex ( $encrypted_data );
		setcookie ( 'XPASS_IC_CARD', $encrypted_data, time () + 3600 * 24 * 365 * 10, '/', COOKIE_DOMAIN );
	}
	/****注册相关方法 开始*****/
	function view_checkuser(){
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
		$row = $pasmod->checkUser($user);
		if ($row > 0) {
			echo 2;
		} else if ($pasmod->isBlockword ( $user )) {
			echo 2;
		} else {

			echo 1;
		}
		exit();
	}	
	function view_reg() {
		global $tpl;
		$reg_type = 'email';
		if(isset($_GET['reg']) && $_GET['reg']=='username') $reg_type = 'username';
		
		$sponsor = isset ( $_GET ['sponsor'] ) ? $_GET ['sponsor'] : '';

		$_SESSION ['sex'] = ! isset ( $_SESSION ['sex'] ) ? '' : $_SESSION ['sex'];
		$_SESSION ['username'] = ! isset ( $_SESSION ['username'] ) ? '' : $_SESSION ['username'];
		$_SESSION ['email'] = ! isset ( $_SESSION ['email'] ) ? '' : $_SESSION ['email'];
		$_SESSION ['nickname'] = ! isset ( $_SESSION ['nickname'] ) ? '' : $_SESSION ['nickname'];
		$_SESSION ['realname'] = ! isset ( $_SESSION ['realname'] ) ? '' : $_SESSION ['realname'];

		$forward=$GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/regok";
		if(isset($_GET ['forward'])) {
			$forward = isset ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
		}

		$tpl->assign ( 'username', $_SESSION ['username'] );
		$tpl->assign ( 'email', $_SESSION ['email'] );
		$tpl->assign ( 'nickname', $_SESSION ['nickname'] );
		$tpl->assign ( 'select_sex', $_SESSION ['sex'] );
		$tpl->assign ( 'realname', $_SESSION ['realname'] );

		$tpl->assign ( 'sponsor', $sponsor );
		$tpl->assign ( 'forward', $forward );
		$tpl->assign ( 'reg_type', $reg_type );


	}
	function view_regok() {
		print_r(authenticate());
		show_message('注册成功！');
		
	}
	function op_saveuser() {
		$msg = '';
		$reg_type = !empty($_POST['reg_type'])?$_POST['reg_type']:'';
		$forward = ! empty ( $_POST ['forward'] ) ? $_POST ['forward'] : '';
		$_POST ['sex'] = (isset($_POST ['sex']))?$_POST ['sex']:0;		
		$_SESSION ['nickname'] = $_POST ['nickname'];
		$_SESSION ['realname'] = $_POST ['realname'];
		$_SESSION ['sex'] = $_POST ['sex'];
		//在php端对变量的规则进行判断.
		$pattern = "/^[a-zA-Z][a-zA-Z0-9_]{1,13}[a-zA-Z0-9]$/i";
		$pattern2 = "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/";
		if (!isset($_SESSION['validatecode']) || ($_POST['code']!='back' && strtolower($_POST['code'])!=strtolower($_SESSION['validatecode']))) {
			show_message_goback ( "验证码输入错误!" );
		}
		if($reg_type=='username'){
			$_POST ['username'] = trim ( $_POST ['username'] );
			if (empty ( $_POST ['username'] ) || ! preg_match ( $pattern, $_POST ['username'] )) {
				show_message_goback ( "用户名只能由3-15位字母(a-z)、数字(0-9)或下划线(_)构成，并且必须以字母开头!" );
			}
			$_SESSION ['username'] = $_POST ['username'];
		}
		if($reg_type=='email'){
			if (empty ( $_POST ['email'] ) || ! preg_match ( $pattern2, $_POST ['email'] )) {
				show_message_goback ( "您的电子邮箱格式有误，请修改!" );
			}
			$_SESSION ['email'] =    $_POST ['email'];
		}
		if ($_POST ['password'] != $_POST ['comfirm']) {
			show_message_goback ( "两个密码不一致，请修改!" );
		}
		$_POST ['nickname'] = trim ( $_POST ['nickname'] );
		$nickname_len = mb_strlen ( $_POST ['nickname'], "UTF-8");
		if (empty ( $_POST ['nickname'] ) || $nickname_len < 2 || $nickname_len > 12) {
			show_message_goback ( "昵称不能为空,不能少于2位,大于8位!" );
		}
		$_POST ['sex'] = intval($_POST ['sex']);
		if (empty($_POST ['sex'])) {
			show_message_goback ( "请选择性别！" );
		}


		include_once("PassportModel.class.php");
		$passmod = new PassportModel();

		if($reg_type=='username' ) {
			if($passmod->checkUser ( $_POST ['username'] )){
				$msg = "用户名已存在!";
				show_message_goback ( $msg );
			}
			
			$user['user'] = $_POST ['username'];
			$user['user_email'] = '';
			$user['user_question'] = isset($_POST ['question'])?$_POST ['question']:'';
			$user['user_answer'] = isset($_POST ['answer'])?$_POST ['answer']:'';
		}		
		if($reg_type=='email'){
			 if($passmod->checkUser ( $_POST ['email'] )) {
				$msg = "邮箱已存在!";
				show_message_goback ( $msg );
			}
			$user['user_email'] = $_POST ['email'];
			$user['user'] = $_POST ['email'];
			$user['user_question'] = '';
			$user['user_answer'] = '';
		}
		

		$user['user_password'] = md5 ( $_POST ['password'] );
		$user['user_nickname'] = htmlspecialchars ( $_POST ['nickname'] );
		$user['user_sex'] = $_POST ['sex'];
		$user['user_realname'] = $_POST['realname'];
		$user['user_reg_ip'] = getip();

		// 1. create db user
		$row = $passmod->createNewUser ( $user );
		if ($row !== false) {

			// 6.自动登录
			$user ['user_id'] = $row['user_id'];
			$user ['autologin'] = 0;
			$this->save_online_user ( $user );
			
			$_SESSION ['sex'] = '';
			$_SESSION ['username'] = '';
			$_SESSION ['email'] = '';
			$_SESSION ['realname'] = '';
			$_SESSION ['nickname'] = '';
			$_SESSION ['autologin'] = 0;			

			//记录登录日志
			//curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/loginlog.php?user=".$user ['user_name']."&userid=".$row['user_id']);
			if(!empty($forward)){
				header("location: ".$forward);
			}else{
				header ( "location: ".$GLOBALS ['gSiteInfo'] ['www_site_url']."/index.php/passport/regok" );
				
			}
			
		}	
	}	
	

	/****找回密码相关方法 开始****/
	function view_forget() {
		
	}
	function view_question(){
		global $tpl;
		$user = $_GET['user'];
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$userindex = $passmod->getUser ($user );
		$user_arr = $passmod->getUserById($userindex['user_id'],$user);
		
		$tpl->assign("arr",$user_arr);
	}
	function op_answer(){
		$user = $_POST['user'];
		$answer = $_POST['answer'];
		if (empty ( $_POST ['newpwd1'] ) or empty ( $_POST ['newpwd2'] )) {
			show_message_goback('请输入新密码！');
		}

		$new1 = $_POST ['newpwd1'];
		$new2 = $_POST ['newpwd2'];
		if (strlen ( $new1 ) < 6 or strlen ( $new2 ) < 6) {
			show_message_goback("新密码长度不能小于6位！");
		}

		if (trim ( $new1 ) != trim ( $new2 )) {
			show_message_goback("两个密码不一致！");;
		}
		
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$userindex = $passmod->getUser ($user );
		$user_arr = $passmod->getUserById($userindex['user_id'],$user);
		if($answer == $user_arr['user_answer']){
			if (false!=$passmod->updatePassByUser( $user_arr['user'], md5( $new1) )) {
				$passmod->updateForgetPwd($user_arr['user']);
				show_message_goback('重置密码成功！');;
			} else {
				show_message_goback('密码更新失败！请重新输入！');
			}
		}else{
			show_message_goback('您输入的答案错误！');
		}
	}
	function op_emailpwd() {
		$flag = 0;
		if (empty ( $_POST ['user'] )) {
			show_message_goback('请输入帐号！');
		}
		$user = trim ( $_POST ['user'] );
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$userindex = $passmod->getUser ($user );
		if (false==$userindex) {
			show_message_goback('此帐号不存在！');
		}
		
		if(false===strpos($user,'@')){
			
			header("location: ".$GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php/passport/question/user/".$user);
			
			die;
		}
		$code = $passmod->addForgetPwd ( $user );
		if ($code == 5) {
			show_message_goback("您已经执行过此操作，请访问您的邮箱！");
		}

		$link = "<a href='" . $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php/passport/resetpwd/code/" . $code . "'>" . $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php/passport/resetpwd/code/" . $code . "</a><br />";
		$content = ' 亲爱的 &lt;' . $user . '&gt; ：<br>
				  您申请了找回密码服务！请点下边的连接并重新设置您的新密码，此连接将在60分钟后失效。<br>' . $link . '<br>' . $GLOBALS ["gSiteInfo"] ["web_description"] ;

		$flag = send_email( $GLOBALS ["gEmail"] ["smtp_account"],$user, '找回密码', $content );
		if($flag==1) show_message_goback('邮件已经发送！');
		
	}
	function view_resetpwd() {
		global $tpl;
		if (empty ( $_GET ['code'] )) {
			redirect ($GLOBALS ['gSiteInfo'] ['www_site_url']. '/index.php/passport/login' );
		}

		$code = $_GET ['code'];
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		$flag = "0";
		$row = $passmod->checkForget ( $code );
		if (!$row) {			
			show_message("此链接已经失效!");
			echo "<script> alert('此链接已经失效!')</script>";	
			redirect ($GLOBALS ['gSiteInfo'] ['www_site_url']. '/index.php/passport/login' );		
		}
		$tpl->assign ( "code", $code );

	}
	function op_resetpwd() {
		if (empty ( $_POST ['code'] )) {
		 	show_message_goback("参数无效！");
		}

		if (empty ( $_POST ['newpwd1'] ) or empty ( $_POST ['newpwd2'] )) {
			show_message_goback('请输入新密码！');
		}

		$new1 = $_POST ['newpwd1'];
		$new2 = $_POST ['newpwd2'];
		if (strlen ( $new1 ) < 6 or strlen ( $new2 ) < 6) {
			show_message_goback("新密码长度不能小于6位！");
		}

		if (trim ( $new1 ) != trim ( $new2 )) {
			show_message_goback("两个密码不一致！");;
		}

		$code = $_POST ['code'];
		
		include_once("PassportModel.class.php");
		$passmod = new PassportModel();
		
		$row = $passmod->checkForget ( $code );
		if ($row) {		
			if (false!=$passmod->updatePassByUser ( $row['user'], md5( $new1) )) {
				$passmod->updateForgetPwd($row['user']);
				show_message_goback('重置密码成功！');;
			} else {
				show_message_goback('密码更新失败！请重新输入！');
			}
		} else { 
			show_message("此链接已经失效!");
			echo "<script> alert('此链接已经失效!')</script>";
			redirect ( $GLOBALS ['gSiteInfo'] ['www_site_url'].'/index.php/passport/login' );
		}
	}

}
?>