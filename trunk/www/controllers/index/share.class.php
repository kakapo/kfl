<?
//include_once ("UserModel.class.php");
//include_once ("ShareModel.class.php");
/**
 * @abstract 嵌入分享类
 * @author zgma at
 *
 **/
class share 
{
	var $user_id;
	var $user_name;
	var $user_gender;
	var $user_db_key;
	var $is_login;

	function __construct() 
	{
		global $tpl;
		$user = authenticate ();
		
		if ($user !== false) 
		{
			$this->user_id 		=	 $user [1];
			$this->user_name 	=	 $user [0];
			$this->user_gender 	=	 $user [6];
			$this->user_db_key 	=	 $user[12];
			$this->is_login = 1;
			$this->user_cash = isset($_COOKIE['IDOL_CASH_'.$user [0]])?$_COOKIE['IDOL_CASH_'.$user [0]]:0;
			$this->user_coin = isset($_COOKIE['IDOL_COIN_'.$user [0]])?$_COOKIE['IDOL_COIN_'.$user [0]]:0;
			
			$tpl->assign ( 'is_login', $this->is_login );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_vote_num', $user [10] );
			$tpl->assign ( 'average', $user [11] );
			$tpl->assign ( 'user_name', $user [0] );
			$tpl->assign ( 'user_id', $user [1] );
			$tpl->assign ( 'user_vip', $user [14] );
			$tpl->assign ( 'user_cash', $this->user_cash );
			$tpl->assign ( 'user_coin', $this->user_coin);
			$tpl->assign ( "title", "空间3D装饰-果动网-果然会动-网页3D娱乐" );
		} 
		else 
		{
			show_message ( '请先登录!' );
	    	redirect ( $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=share'));
		}
	}
	
	function view_defaults()
	{
		global $tpl;
	}

	//----------------------------------------------博客类型------------------------------------------
	//一键嵌入首页,空间选择
	function view_index()
	{
		global $tpl;
		$this->checklogin ();
		
		$size = array( 'width' => '520' , 'height' => '390' );
		
		$tpl->assign( 'size' , $size );
	}
	
	//尺寸选择
	function view_size()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//嵌入类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'qq';
			
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $sizeArray[ $type ] );
	}
	
	//qq
	function view_qzone()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'qzone';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}";
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//新浪
	function view_sina()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'sina';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//网易
	function view_w163()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'w163';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		//$flash = "<embed allowScriptAccess='never' allowNetworking='internal' src='{$GLOBALS ['gSiteInfo'] ['image_site_url']}/public/flash/widget.swf?userId={$this->user_name}' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' wmode='transparent' quality='high' width='100%' autostart='0'></embed>";
		$flash = addslashes('<embed allowScriptAccess="never" allowNetworking="internal" src="'.$GLOBALS ['gSiteInfo'] ['image_site_url'].'/flash/guodongWidgest.swf?userId='.$this->user_name.'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent" quality="high" width="'.$size[ 'width' ].'" height="'.$size[ 'height' ].'" autostart="0">');
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//51com
	function view_51com()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : '51com';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}";
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//博客大巴
	function view_blogbus()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'blogbus';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//MSN
	function view_msn()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'msn';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//聚友网
	function view_myspace()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'myspace';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//19楼
	function view_19lou()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : '19lou';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//POCO
	function view_poco()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'poco';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash
		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
	}
	
	//论坛发帖
	function view_bbs()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'bbs';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash_code
		$html_flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		$ubb_flash  = "[flash={$size[ 'width' ]},{$size[ 'height' ]}]" . $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}[/flash]";
		$url_flash  = $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}";
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'html_flash' , $html_flash );
		$tpl->assign( 'ubb_flash' , $ubb_flash );
		$tpl->assign( 'url_flash' , $url_flash );
	}
	
	//通用代码
	function view_currency()
	{
		global $tpl , $sizeArray;
		$this->checklogin ();
		//类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : 'currency';
		//尺寸
		$size = $sizeArray[ $type ];
		//flash_code
		$html_flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
		$ubb_flash  = "[flash={$size[ 'width' ]},{$size[ 'height' ]}]" . $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}[/flash]";
		$url_flash  = $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}";
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'html_flash' , $html_flash );
		$tpl->assign( 'ubb_flash' , $ubb_flash );
		$tpl->assign( 'url_flash' , $url_flash );
	}
	
	//-----------------------------------------------------------------------------------------------
	
	//复制代码
	function view_copy()
	{
		global $tpl , $sizeArray;
		
		//BLOG类型
		$type = isset( $_GET[ 'type' ] ) ? $_GET[ 'type' ] : '';
		
		//尺寸
		$size = $sizeArray[ $type ];
		
		//flash字符串
		$flash_url = $GLOBALS[ 'gSiteInfo' ][ 'image_site_url' ] . "/public/flash/widget.swf?userId={$this->user_name}";
		
		$flash = '';
		$blogUrl = '';
		switch ( $type )
		{
			case 'qzone': 		$flash = $flash_url;
								$blogUrl = 'http://qzone.qq.com';
								break;
								
			case 'sina': 		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://blog.sina.com.cn';
								break;
								
			case 'w163': 		$flash = $flash_url;
								$blogUrl = 'http://blog.163.com';
								break;
								
			case '51com': 		$flash = $flash_url;
								$blogUrl = 'http://www.51.com';
								break;
								
			case 'blogbus': 	$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://www.blogbus.com';
								break;
								
			case 'msn': 		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://home.services.spaces.live.com';
								break;
								
			case 'myspace': 	$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://www.myspace.cn';
								break;
								
			case '19lou': 		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://www.19lou.com';
								break;
								
			case 'poco': 		$flash = $this->getFlash( $size[ 'width' ] , $size[ 'height' ] );
								$blogUrl = 'http://www.poco.cn';
								break;
								
			case 'bbs': 		$flash = $flash_url; break;
			case 'currency': 	$flash = $flash_url; break;
		}
		
		$str = array( 'qzone' => 'Q-Zone' , 'sina' => '新浪博客' , 'w163' => '网易博客' , '51com' => '51COM');
		
		$tpl->assign( 'type' , $type );
		$tpl->assign( 'size' , $size );
		$tpl->assign( 'flash' , $flash );
		$tpl->assign( 'blogUrl' , $blogUrl );
		$tpl->assign( 'str' , $str );
	}
	
	//嵌入博客
	function op_blog() 
	{
		$path = isset( $_POST['path'] ) ? $_POST['path'] : '';
		
		if ( empty( $path ) )
		{
			echo 'no';
		}
		
		switch ( $path )
		{
			case 'qzone':
				include_once (APP_DIR . "/plugins/keyadd/qzone.php");break;
			case 'baidu':
				include_once (APP_DIR . "/plugins/keyadd/baidu.php");break;
			case 'sina':
				include_once (APP_DIR . "/plugins/keyadd/sina.php");break;
			case 'w163':
				include_once (APP_DIR . "/plugins/keyadd/w163.php");break;
			case '51com':
				include_once (APP_DIR . "/plugins/keyadd/51com.php");break;
			case 'sohu':
				include_once (APP_DIR . "/plugins/keyadd/sohu.php");break;
		}
	}

	//QZONE
	function op_qzoneflash() 
	{
		include_once (APP_DIR . "/plugins/keyadd/mod.php");
	}
	
	//----------------------------------------------内部方法-----------------------------------------
	//验证是否登录
	function checklogin() 
	{
		if ( !$this->is_login ) 
		{
			header ( 'Location: /index.php/passport/login' );
		}
	}
	
	//取得FLASH字符串
	function getFlash( $width , $height ) 
	{
		$str = "<embed src='{$GLOBALS ['gSiteInfo'] ['image_site_url']}/flash/widget.swf?userId={$this->user_name}' width='{$width}' height='{$height}' align='middle' quality='high' name='widget' allowScriptAccess='always' allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' flashvars='scale=1' wmode='transparent' />";
		return $str;
	}
}
?>