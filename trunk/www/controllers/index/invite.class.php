<?php
include_once ("ApiUser.class.php");
include_once ("InviteModel.class.php");

/**
 * @abstract 显示及处理邀请好友的控制类
 * @author cdwei
 * @version 0.3
 *
 */
class invite {
	
	private $user_id;
	private $user_name;
	private $user_nickname;
    private $inviteobj;
    
	function invite() {
		global $GLOBALS;
		//解决ie6 下iframe 因gzip 不显示页面问题
		header ( "Content-encoding: none" );
		$www = $GLOBALS ['gSiteInfo'] ['www_site_url'];
		$user = authenticate ();
		if ($user != false) {
			$this->user_id = $user [1];
			$this->user_name = $user [0];
			$this->user_nickname = $user [2];
            $userbase = ApiUser::getUserByName($user [0]);
			$this->inviteobj = new InviteModel ($userbase['user_db_key'] );
		} else {
			header('Content-Type: text/html; charset=utf-8');
			echo '
	 <script language="JavaScript" type="text/javascript">
	 			document.domain = "'.substr(COOKIE_DOMAIN,1).'";
			 	parent.AlertShow(300,"您还没有登录，不能执行此操作!");
			 </script>

		';
			die ();
		}
	}
	/**
	 * 显示邀请首页
	 *
	 */
	function view_index() {
		global $GLOBALS;
		$_SESSION ['qq'] = null;
		$_SESSION ['maillist'] = null;
	}
    function view_loading(){
    	global $tpl;
    	
    }
	/**
	 * 显示成功页面
	 *
	 */
	function view_succ() {
		global $tpl;
		$type = "";
		$num = 0;
		$msg = "";
		$newaction = "";

		if (empty ( $_GET ['type'] )) {
			$type = "";
		}
		if (empty ( $_GET ['num'] )) {
			$num = 0;
		}
		if (empty ( $_GET ['newaction'] )) {
			$newaction = "";
		}
		$type = $_GET ['type'];
		$num = isset($_GET ['num']) ? $_GET ['num'] : 0;
		$newaction = $_GET ['newaction'];

		switch ($type) {
			case "fav" :
				$msg = "成功收藏" . $num . "个人用户！";
				if ($num == 0) {
					$msg = "不能收藏选择的用户，可能用户已经收藏过！";
				}
				break;
			case "sendmail" :
				$msg = "发送成功！";
				break;
			case "msn" :
				$msg = "成功邀请" . $num . "个MSN朋友！";
				if ($num == 0) {
					$msg = "不能邀请选择的MSN朋友，可能已经邀请过了！";
				}
				break;
			case "qq" :
				$msg = "成功邀请" . $num . "个QQ朋友！";
				if ($num == 0) {
					$msg = "不能邀请选择的QQ朋友，可能已经邀请过了！";
				}
				break;
			case "invite" :
				$msg = "发送成功！";
				break;

			default :
				$msg = "邀请成功！";
				
				break;
		}
		$hover = '';
		if (! empty ( $_SESSION ['hover'] )) {
			$hover = $_SESSION ['hover'];
		}
		$tpl->assign ( "msg", $msg );
		$tpl->assign ( "newaction", $newaction );
	}
	/**
	 * 显示邀请MSN
	 *
	 */
	function view_msn() {
		global $user_server_table, $gSiteInfo, $tpl;
         
		$_SESSION ['qq'] = null;
		$_SESSION ['maillist'] = null; //clean up the SESSION value
		$_SESSION ['hover'] = "msn";
		$show="";
		$msg="";
		$tpl->assign ( 'msg', $msg );
	}
	/**
	 * 显示邀请qq
	 *
	 */
	function view_qq() {
		global  $user_server_table, $gSiteInfo, $tpl;
		$msg = "";
		$flag = "";
		$_SESSION ['qq'] = null;
		$_SESSION ['mailstr'] = null;
		$_SESSION ['maillist'] = null; //clean up the SESSION value
		$_SESSION ['hover'] = "qq";
		if (! isset ( $_GET ['flag'] )) {
			$msg = "";
		} else {
			$flag = $_GET ['flag'];
			if ($_GET ['flag'] == 0) {
				$msg = "<font color=red>用户名或密码错误!</font>";
			}
			if ($flag == 1) {
				$msg = "<font color=green>执行成功!</font>";
			}
			if ($flag == 2) {
				$msg = "<font color=red>对不起，服务器没有相应。</font>";
			}
			if ($flag == 3) {
				$msg = "<font color=red>没有找到联系人!</font>";
			}
			if ($flag == 4) {
				$msg = "<font color=red>请输入用户名，密码!</font>";
			}
		}
		$tpl->assign ( 'msg', $msg );
		$tpl->assign ( 'title', '' );
	}
	/**
	 * 显示邀请链接地址
	 *
	 */
	function view_copyurl() {
		global $tpl;
		$invite_str = "";
		$invite_str = trim ( $GLOBALS ['account'] ['inviteurl'] . $this->user_name );
		$tpl->assign ( "invite_str", $invite_str );
	}
	/**
	 * 显示发送邮件
	 *
	 */
	function view_sendmail() {
		global $tpl;
		$_SESSION ['hover'] = "email";
		$tpl->assign ( 'nickname', $this->user_nickname );

	}
	/**
	 * 显示导入通讯录界面
	 *
	 */
	function view_freemail() {
		global  $user_server_table, $gSiteInfo, $tpl;

		$_SESSION ['qq'] = null;
		$_SESSION ['mailstr'] = null;
		$_SESSION ['maillist'] = null; //clean up the SESSION value
		$_SESSION ['hover'] = "free";
		$msg = "";
		$flag = "";
		$_SESSION ['hover'] = "free";
		if (! isset ( $_GET ['flag'] )) {
			$msg = "";
		} else {
			$flag = $_GET ['flag'];
			if ($_GET ['flag'] == 0) {
				$msg = "<font color=red>用户名或密码错误!</font>";
			}
			if ($flag == 1) {
				$msg = "<font color=green>执行成功!</font>";
			}
			if ($flag == 2) {
				$msg = "<font color=red>对不起，服务器没有相应。</font>";
			}
			if ($flag == 3) {
				$msg = "<font color=red>没有找到联系人!</font>";
			}
			if ($flag == 4) {
				$msg = "<font color=red>请输入用户名，密码!</font>";
			}
		}

		$tpl->assign ( 'msg', $msg );

	}
	/**
	 * 显示找到的联系人
	 *
	 */
	function view_displayall() {
		global  $user_server_table, $gSiteInfo, $tpl;
		$maillist = array ();
		$user_id = $this->user_id;
		$count = 0;
		if(isset($_SESSION ['maillist'])&&!empty( $_SESSION ['maillist'] )){
			$maillist = $_SESSION ['maillist'];
		}
		$tmp = array ();
		if (isset( $_SESSION ['qq'] )) {
			$maillist1 = $_SESSION ['maillist'];
			if ( isset($maillist1 [0]) && is_array ( $maillist1 [0] )) {
				$ii = 0;
				foreach ( $maillist1 [0] as $v ) {
					$maillist [0] [$ii] = mb_convert_encoding ( $v, "UTF-8", "GBK" );
					$ii ++;
				}

			}
			if (isset( $maillist [1] )) {
		 		$maillist [1] = $maillist1 [1];
			}
		}
		if (isset( $maillist [1] )) {
			$count = count ( $maillist [1] );
		}
		$tpl->assign ( "userinfo", $maillist );
		$tpl->assign ( 'count', $count );
	}
	/**
	 * 显示果动网已经注册的联系人
	 *
	 */
	function view_favourite() {
		global $user_server_table, $GLOBALS, $tpl;
		$www = $GLOBALS ['gSiteInfo'] ['www_site_url'];
		$mailstr = "";
		$userinfo = array ();
		if(isset($_SESSION ['maillist'])&&!empty( $_SESSION ['maillist'] )){
			$mailstr = $_SESSION ['mailstr'];
			//获得 通讯录中已经再果动网注册的用户列表
			$userinfo = $this->inviteobj->getHavedMailUser ( $mailstr, $this->user_id );
			//获取已收藏却已注册的用户列表
			$userid_str = '';
			$userid_arr = array ();
			$friendinfo = array (); //已收藏却已注册用户id
			$nofrdinfo = array (); //未收藏却已注册用户
			if(!empty($userinfo)){
				foreach ( $userinfo as $val ) {
					$userid_arr [] = $val ['userid'];
				}
			}

			$userid_str = implode ( ",", $userid_arr );
			$friendinfo = $this->inviteobj->getRegHaveFrd ( $userid_str, $this->user_id );
			if ($friendinfo) { //获取未收藏却已注册用户
				$found = false;
				foreach ( $userinfo as $k => $v ) {
					foreach ( $friendinfo as $va ) {
						if ($v ['userid'] == $va ['friend_id']) {
							$found = true;
							break;
						}
					}
					if (! $found) {
						$nofrdinfo [] = $userinfo [$k];
					} else {
						$found = false;
					}
				}
			} else {
				$nofrdinfo = $userinfo;
			}

		}
		$count = 0;
		if (! empty ( $nofrdinfo )) {
			$count = count ( $nofrdinfo );
		} else {
			redirect ( $www . "/index.php?action=invite&view=displayall");
		}
		$tpl->assign ( 'userinfo', $nofrdinfo );
		$tpl->assign ( 'count', $count );
	}
	function view_uplaodcsv() {
		global $user_server_table, $gSiteInfo;
		$up = new OperationCsv ( );

	}
	/**
	 * 执行导入各大系列邮箱通讯录的入口操作函数
	 *return $flag  0为用户名密码错误，2为远程服务器未响应，3为通讯录为空，4为用户名或密码为空
	 */
	function op_freemail() {
		global  $user_server_table, $gSiteInfo;
		$msg = "";
		$flag = "";
		$invite_table = "user_invite_email";
		$fileds = "email_nick,email ";
       	if (!empty ( $_POST ['mailtype'] )) {
		  $mailtype = $_POST ['mailtype'];
		}
		if(empty($_POST ['login'])||empty($_POST ['password'])){
		  echo "4";
		  die;
		}else{
		  $login = $_POST ['login'];
		  $password = $_POST ['password'];
	    }
		switch (trim ( $mailtype )) {
			case "hotmail" :
				$flag = $this->selectMail ( "plugins/export/windowslive.php", $login."@hotmail.com", $password );
				break;
			case "sina" :
				$flag = $this->selectMail ( "plugins/export/sina_mail.php", $login, $password );
				break;
			case "yahoo" :
				$flag = $this->selectMail ( "plugins/export/yahoo.php", $login, $password );
				break;
			case "163" :
				$flag = $this->selectMail ( "plugins/export/163mail.php", $login, $password );
				break;
			case "126" :
				$flag = $this->selectMail ( "plugins/export/126.php", $login, $password );
				break;
			case "yeah" :
				$flag = $this->selectMail ( "plugins/export/yeah.php", $login, $password );
				break;
			case "gmail" :
				$flag = $this->selectMail ( "plugins/export/gmail.php", $login, $password );
				break;
			case "tom" :
				$flag = $this->selectMail ( "plugins/export/tom.php", $login, $password );
				break;
			case "sohu" :
				$flag = $this->selectMail ( "plugins/export/sohu.php", $login, $password );
				break;
			default :
				$flag = 0;
				break;
		}
		if (is_int ( $flag )) {
			echo $flag;
			die ();
		} else {
			$_SESSION ['mailstr'] = $flag;
			$this->inviteobj->importMailByArr ( $this->user_id, "user_invite_email ", "email_nick,email,user_id,im_type", $_SESSION ['maillist'], "'" . $this->user_id . "'" . ",'$mailtype'" );
			echo "1";	
			die ();
		}
	}
	function op_favourite() {
		global  $user_server_table, $GLOBALS;
		$www = $GLOBALS ['gSiteInfo'] ['www_site_url'];
		$flag = 0;
		$userstr=isset($_POST ['userid']) ? $_POST ['userid'] : "";
	    if (empty ( $userstr )) {
			redirect ( $www . "/index.php?action=invite&view=displayall" );
		}
		$friend_ids = explode(',',$userstr);
		$flag = $this->inviteobj->doFavourite ( $this->user_id, $friend_ids );
		if ($flag > 0) {
			redirect ( $www . "/index.php?action=invite&view=displayall&type=fav&newaction=displayall&num=$flag" );
		} else {
			redirect ( $www . "/index.php?action=invite&view=displayall&type=fav&newaction=displayall&num=0" );
		}
		die;
	}
	function op_displayall() {
		global $user_server_table, $GLOBALS;
		$www = $GLOBALS ['gSiteInfo'] ['www_site_url'];
		$flag = 0;
        $userstr=isset($_POST ['userid']) ? $_POST ['userid'] : "";
	    if(!empty($userstr)){
	    	$invite_ids=explode(',',$userstr);
			$flag = $this->inviteobj->doDisplayall ( $this->user_id, $invite_ids );
		}else{
			$flag = 0;
		}
		if ($flag > 0) {
			redirect ( $www . "/index.php?action=invite&view=succ&type=invite&newaction=index&num=$flag" );
		} else {
			redirect ( $www . "/index.php?action=invite&view=succ&type=invite&newaction=index&num=$flag" );
		}
	}
	/**
	 * 处理邀请MSN
	 *
	 */
	function op_msn() {
		global $user_server_table, $gSiteInfo;
		$msg = "";
		$flag = "";
		$invite_table = "user_invite_email";
		$login = $_POST ['login'];
		$password = $_POST ['password'];
		$mailtype = "msn";
		include_once ("plugins/export/MSN.class.php");
		$msn2 = new msn;
        $returned_emails = $msn2->qGrab($login, $password);
        $names = array ();
	    $emails = array ();
	    $mytemp=array();
	    if(is_array($returned_emails)){
	       foreach ( $returned_emails as $row ) {
			$emails [] = trim($row[0]);
			$myarr=explode('@',$row[0]);
			$names [] =$myarr[0];
	       }	
	    }
	    $mytemp=array ($names, $emails);
        $flag= $this->selectMsn($mytemp);
		if (is_int ( $flag )) {
			echo $flag;
			die ();
		} else {
			$_SESSION ['mailstr'] = $flag;
			$this->inviteobj->importMailByArr ( $this->user_id, "user_invite_email ", "email_nick,email,user_id,im_type", $_SESSION ['maillist'], "'" . $this->user_id . "'" . ",'$mailtype'" );
			echo "1";
			die ();
		}
	}

	
	
	/**
	 * 处理邀请qq
	 *
	 */
	function op_qq() {
		global  $user_server_table, $gSiteInfo;
		$_SESSION ['qq'] = 1;
		$msg = "";
		$flag = "";
		$invite_table = "user_invite_email";
		if (empty ( $_POST ['p'] ) or empty ( $_POST ['uin'] )) {
			echo "4";
			die ();
		}
		$password = $_POST ['p'];
		$login = $_POST ['uin'];
		$mailtype = "qq";
		$flag = $this->selectMail ( "plugins/export/qq.php", $login, $password );
		if (is_int ( $flag )) {
			echo $flag;
			die ();
		} else {
			$_SESSION ['mailstr'] = $flag;
			$this->inviteobj->importMailByArr ( $this->user_id, "user_invite_email ", "email_nick,email,user_id,im_type", $_SESSION ['maillist'], $this->user_id . ",'$mailtype'" );
			echo "qq";
			die ();
		}

	}

	/**
	 * 处理发送邮件
	 *
	 */
	function op_sendmail() {
		global  $user_server_table, $gSiteInfo;
		$emailist = array ();
		$flag = 0;
		$emailist = $_POST ['email'];
		$feedback = '';
		$fromname = $this->user_nickname;
		$flag = $this->inviteobj->doSendmail ( $this->user_id, $emailist, $feedback, $fromname );
		if ($flag > 0) {
			echo "emailyes";
		} else {
	        echo "emailno";
		}
		die;
	}
    function selectMsn($myArray)
    {
       $temp = array ();
       $temp=$myArray;
       if (is_array ( $temp )) {
			$_SESSION ['maillist'] = $temp;
			if (! empty ( $temp [1] )) {
				$_SESSION ['qq_count'] = count ( $temp [1] );
			}
		}
		$mailstr = "";
		if (is_array ( $temp )) { //成功
			///$import->importMailByArr($invite_table,$fileds,$temp,$mailtype);
			$maillist = array ();
			if (! empty ( $temp [1] )) {
				$maillist = $temp [1];
			}
			$flag = 1;
			for($i = 0; $i < count ( $maillist ); $i ++) {
				if ($i == count ( $maillist ) - 1) {
					$mailstr .= "'" . $maillist [$i] . "'";
					break;
				}
				$mailstr .= "'" . $maillist [$i] . "',";
			}
			return $mailstr;
		} else {
			$flag = $temp;
			return $flag;
		}
    }
	/**
	 * 执行获得通讯录的公共函数
	 *
	 * @param string $mailfile 所要进行的引入各类型通讯录函数子文件
	 * @param string $login    用户名
	 * @param string $password 密码
	 * @return mix   	成功的话返回字符串否则返回数字
	 */
	function selectMail($mailfile = "plugins/export/163mail.php", $login = "", $password = "") {
		include_once ($mailfile);
		$temp = array ();
		$temp = get_contacts ( $login, $password );
		if (is_array ( $temp )) {
			$_SESSION ['maillist'] = $temp;
			if (! empty ( $temp [1] )) {
				$_SESSION ['qq_count'] = count ( $temp [1] );
			}
		}

		$mailstr = "";
		if (is_array ( $temp )) { //成功
			///$import->importMailByArr($invite_table,$fileds,$temp,$mailtype);
			$maillist = array ();
			if (! empty ( $temp [1] )) {
				$maillist = $temp [1];
			}
			$flag = 1;
			for($i = 0; $i < count ( $maillist ); $i ++) {
				if ($i == count ( $maillist ) - 1) {
					$mailstr .= "'" . $maillist [$i] . "'";
					break;
				}
				$mailstr .= "'" . $maillist [$i] . "',";
			}
			return $mailstr;
		} else {
			$flag = $temp;
			return $flag;
		}
	}

	function view_getimage(){
		$res = $this->execute_curl ( "http://ptlogin2.qq.com/getimage?aid=23000101", '', 'get', '', 'cookie' );
		list ( $header, $body ) = explode ( "\n\r", $res );
		$cookie = $this->get_cookies ( $header );
		$_SESSION ['cookie'] = $cookie;
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

}


?>