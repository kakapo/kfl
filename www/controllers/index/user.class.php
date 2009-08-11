<?php

/**
 * @abstract 用户管理类
 * @author zgma at
 *
 **/
class user
{
	var $user_id;
	var $user_name;
	var $user_nickname;
	var $user_gender;
	var $user_db_key;
	var $user_email;
    var $user_vip;
	function __construct()
	{
		global $tpl;
		$user = authenticate ();

		if ( $user != false )
		{
			$this->user_id			 =	 $user[1];
			$this->user_name		 =	 $user[0];
			$this->user_nickname	 =	 $user[2];
			$this->user_gender		 =	 $user[6];
			$this->user_db_key		 =	 $user[12];
			$this->user_email		 =	 $user[13];
			$this->user_vip		 	 =	 $user[14];
			$this->user_cash 		 = 	 isset($_COOKIE['IDOL_CASH_'.$user [0]])?$_COOKIE['IDOL_CASH_'.$user [0]]:0;
			$this->user_coin 		 =	 isset($_COOKIE['IDOL_COIN_'.$user [0]])?$_COOKIE['IDOL_COIN_'.$user [0]]:0;
			$this->user_msgnum		 =	 isset($_COOKIE['IDOL_MSGNUM_'.$user [0]])?$_COOKIE['IDOL_MSGNUM_'.$user [0]]:0;
			
			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_vote_num', $user [10] );
			$tpl->assign ( 'user_gender', $user[6] );
			$tpl->assign ( 'average', $user [11] );
			$tpl->assign ( 'user_name', $user [0] );
			$tpl->assign ( 'user_id', $user [1] );
			$tpl->assign ( 'user_vip', $user [14] );
			$tpl->assign ( 'user_cash', $this->user_cash );
			$tpl->assign ( 'user_coin', $this->user_coin);
			$tpl->assign ( 'user_msgnum', $this->user_msgnum);
		}
		else
		{
			$this->user_name = '';
			$this->user_id = '';
			$tpl->assign ( 'user_vip', 0 );
			$tpl->assign ( 'is_login', 0 );
			$tpl->assign ( 'user_nickname', '默认' );
			$tpl->assign ( 'user_rank', '' );
			$tpl->assign ( 'user_vote_num', 0 );
			$tpl->assign ( 'average', 0 );
			$tpl->assign ( 'user_name', '' );
		}
	}

	//--------------------------------------用户个人资料管理---------------------------------------
	//个人主页
	function view_defaults()
	{
		global $tpl;
		$user = isset( $_GET [ 'defaults' ] ) ? $_GET [ 'defaults' ] : '';		
		if ( empty( $user ) )
		{
			show_message_goback ( '此用户不存在!' );
		}

		//取得用户信息
		include_once( "ApiUser.class.php" );
		$userInfo = ApiUser::getUserByName( $user );

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel ( $userInfo[ 'user_db_key' ] );

		if ( !$userInfo )
		{
			show_message_goback ( '此用户不存在!' );
		}

		//用户好友
		$userFriend = $_userObj->getUserFriends ( $userInfo['user_id'] , 8 );
		
		//用户博客
		//验证51正则
		$rss51 = "/^http:\/\/home\.51\.com\/(.+)$/i";

		//用户详细信息
		$userInfo2 = $_userObj->getUserBlog( $userInfo['user_id'] );
		//绑定空间
		$userRssUrl = $userInfo2[ 'user_rss_url' ];
		//VIP参数
		$userVip = $userInfo2[ 'user_vip' ];
		
		// 博客空间目录
		$rssDir = getcwd() . "/tmp/rss";

		$rssItem = array ();

		//是51的blog
		if ( preg_match ( $rss51 , $userRssUrl , $m ) )
		{
			include_once ("html51.class.php");
			$html51 = new html51();
			$rss_name = empty( $_SESSION[ 'rss_name' ] ) ? '' : $_SESSION[ 'rss_name' ];
			$rssItem = $html51->outhtml ( $user , $userRssUrl , $rssDir , $rss_name );
		}
		else
		{
			include_once ("rss.class.php");
			$rss = new rss();
			$rssItem = $rss->outrss ( $user , $userRssUrl , $rssDir );
		}

		$datereg = "/([1|2]\d{3}).*?([0|1]{0,1}\d).*?([0-3]{0,1}\d)/i";
		if ( !empty( $rssItem ) )
		{
			foreach ( $rssItem as $k => $v )
			{
				if ( strstr( $v['pubDate'] , '+' ) )
				{
					date_default_timezone_set( 'Asia/Shanghai' );
				}
				$strtime = strtotime ( $v[ 'pubDate' ] );
				if ( !empty( $strtime ) )
				{
					$rssItem[ $k ][ 'pubDate' ] = date ( 'Y.m.d' , $strtime );
				}
				elseif ( preg_match( $datereg , $v[ 'pubDate' ] , $ar ) )
				{
					$rssItem[ $k ][ 'pubDate' ] = $ar[1] . "." . $ar [2] . "." . $ar[3];
				}
				else
				{
					$rssItem[ $k ][ 'pubDate' ] = "";
				}
			}
		}

		if ( count( $rssItem ) > 8 )
		{
			$rssItem = array_splice( $rssItem , 0 , 8-count( $rssItem ) );
		}
		
		$tpl->assign ( 'title', $userInfo[ 'user_nickname' ] . '的个人主页' );
		$tpl->assign ( "view", "defaults" );
		$tpl->assign ( 'userRssUrl', $userRssUrl );
		$tpl->assign ( 'rssItem', $rssItem );
		$tpl->assign ( 'userFriend', $userFriend );
		$tpl->assign ( 'userVip', $userVip );
		$tpl->assign ( 'user', $user );
		$tpl->assign ( 'userNickname', $userInfo[ 'user_nickname' ] );
	}

	//个人资料页面
	function view_info()
	{
		global $tpl , $gSiteInfo;
		
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=info'));
		}
		
		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );
        
		$userInfo = $_userObj->getUserBlog( $this->user_id );
		
		$tpl->assign ( 'title', '个人资料-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'userName' , $this->user_name );
		$tpl->assign ( 'nickName' , $this->user_nickname );
		$tpl->assign ( 'userEmail' , $this->user_email );
		$tpl->assign ( 'userInfo' , $userInfo );
		$tpl->assign ( 'cur' , 'info' );
	}

	//更改用户密码页面
	function view_pwd()
	{
		global $tpl , $gSiteInfo;

		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=pwd'));
		}
		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );
		
		$userInfo = $_userObj->getUserBlog( $this->user_id );

		$tpl->assign ( 'title', '密码修改-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'userInfo' , $userInfo );
		$tpl->assign ( 'cur' , 'pwd' );
	}

	//更改用户密码
	function op_changepwd()
	{
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=pwd'));
		}
		
		if ( empty ( $_POST [ 'org' ] ) or empty ( $_POST [ 'new1' ] ) or empty ( $_POST [ 'new2' ] ) )
		{
			show_message ( '密码不能为空' );
			goback ( $timeout = 500 );
		}

		$org	 =	 $_POST [ 'org' ];
		$new1	 =	 $_POST [ 'new1' ];
		$new2	 =	 $_POST [ 'new2' ];

		if ( strlen ( $new1 ) < 6 or strlen ( $new2 ) < 6)
		{
			show_message ( '密码不能少于6位' );
			goback ( $timeout = 500 );
		}
		if ( trim ( $new1 ) != trim ( $new2 ) )
		{
			show_message ( '两次密码不一致' );
			goback ( $timeout = 500 );
		}

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );

		$orgpwd = $_userObj->getUserById( $this->user_id );

		if ( $orgpwd[ 'user_passwd' ] != md5( $org ) )
		{
			echo '原始密码输入不正确';
		}
		else
		{
			if ( $_userObj->updatePassByUsername ( $this->user_name , $new1 ) )
			{
				echo '保存成功';
			}
			else
			{
				echo '保存失败';
			}
		}
	}

	//收藏好友页面
	function view_friends()
	{
		global $tpl , $gSiteInfo;

		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=friends'));
		}

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );
		
		$userInfo = $_userObj->getUserBlog( $this->user_id );

		//好友信息
		$list_array = $_userObj->getUserFriends ( $this->user_id , 18 );
		
		$tpl->assign ( 'title', '好友收藏-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'list_array', $list_array );
		$tpl->assign ( 'userInfo' , $userInfo );
		$tpl->assign ( 'cur' , 'friends' );
	}

	//收藏好友
	function op_addfriend()
	{
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			die('请先登录!');
			//redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=friends'));
		}
		
		$userName = empty ( $_POST [ 'username' ] ) ? '' : $_POST [ 'username' ];
		
		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );

		if ( $this->user_name == $userName )
		{
			die('你不能收藏自己!');
		}

		//收藏用户
		$rs = $_userObj->addFrinend( $this->user_id, $userName );

		switch ( $rs )
		{
			case '1':echo '你已经收藏了该好友!';break;
			case '2':echo '收藏好友成功!';break;
			case '3':echo '收藏好友失败!';break;
		}
	}

	//删除好友
	function op_delfriend()
	{
		global $gSiteInfo;
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			die('请先登录!');
			//redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=friends'));
		}
		
		$friendId = empty( $_POST[ 'f_id' ] ) ? '' : $_POST[ 'f_id' ];

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );

		if ( $_userObj->delFriend( $this->user_id , $friendId ) )
		{
			echo '删除成功';
		}
		else
		{
			echo '删除失败';
		}
	}

	//消息中心
	function view_message()
	{
		global $tpl , $gSiteInfo;

		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=message'));
		}

		//查看类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'all';
		
		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );
		
		$userInfo = $_userObj->getUserBlog( $this->user_id );
		
		//添加消息（测试）
		//$_userObj->add();
		
		//消息列表
		$list_array = $_userObj->getUserMessage ( $type , $this->user_id );
		
		$tpl->assign ( 'title', '消息中心-果动网-果然会动-网页3D娱乐' );
		$tpl->assign ( 'list_array', $list_array );
		$tpl->assign ( 'type', $type );
		$tpl->assign ( 'userInfo' , $userInfo );
		$tpl->assign ( 'cur' , 'message' );
	}

	//更改消息状态
	function op_updatemessage()
	{
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=message'));
		}
		
		//消息ID
		$messageId = isset( $_POST[ 'id' ] ) ? $_POST[ 'id' ] : '';

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );

		if ( $_userObj->updateMessage( $messageId ) )
		{
			//设置cookie数据，消息数
			setcookie ( 'IDOL_MSGNUM_' . $this->user_name, $_COOKIE['IDOL_MSGNUM_' . $this->user_name] - 1, 0, '/', COOKIE_DOMAIN );
		}
	}

	//删除消息
	function op_delmessage()
	{
		//判断用户是否登录
		if ( empty ( $this->user_id ) )
		{
			show_message ( '请先登录!' );
			redirect ( $gSiteInfo ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=user&view=message'));
		}
		
		//消息ID
		$messageId = isset( $_POST[ 'id' ] ) ? $_POST[ 'id' ] : '';

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->user_db_key );

		if ( $_userObj->delMessage( $messageId ) )
		{
			$list_array = $_userObj->getUserMessage ( 'noread' , $this->user_id );
			$num = count($list_array['record']);
			setcookie ( 'IDOL_MSGNUM_' . $this->user_name, $num, 0, '/', COOKIE_DOMAIN );
			echo '删除成功';
		}
		else
		{
			echo '删除失败';
		}
	}
	
	//充值
	function op_charge(){
		$user_name = isset( $_POST[ 'user_name' ] ) ? $_POST[ 'user_name' ] : '';
		include_once( "ApiUser.class.php" );
		if($this->user_id && $this->user_name && $this->user_db_key){   		
		}else{		 
	     echo "您还没有登录！";
	     die;
		}
		 include_once( "ChargeModel.php" );
		 $_userObj = new ChargeModel();
	     $_userObj->setVarDb($this->user_db_key);
		 $rs=$_userObj->getChargeMoney($this->user_name,$this->user_id);	
	     if($rs){	
	      $content="<table width='300' border='0' cellspacing='3' cellpadding='3'>";	      
          $content.="<tr><td colspan=2>恭喜，您已成功充值<span style='color:#ff0000;font-weight:bold;'>".$rs['chg_rmb']."</span>RMB</td></tr>";	   		  
		  $content.="<tr><td align='left'>您现在可以：</td><td>&nbsp;</td></tr>";
		  if(!$this->user_vip){
		  	 $content.="<tr><td colspan=2><a href='/index.php/show/frameset/vip' onclick='AlertHidden();' target='_parent'>立即成为VIP</a></td></tr>";
		  }
		  $content.="<tr><td colspan=2><a href='/index.php/show/frameset/mainfrm' onclick='AlertHidden();' target='_parent'>去商城看看有什么好东西</a>&gt;&gt;</td></tr>";
		  $content.="<tr><td colspan=2><a href='/index.php?action=charge&czmethod=history' onclick='AlertHidden();' target='_parent'>查询帐户余额</a></td></tr></table>";
		  echo $content;
		  die;
		 }else{
		  echo "&nbsp;&nbsp;您当前没有充值记录！";
		  die;
	     }		 
	}
}
?>