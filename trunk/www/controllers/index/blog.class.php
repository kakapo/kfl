<?php
/**
 * @abstract 博客管理类
 * @author zswu at
 *
 **/
class blog {
	/**
	 * _constructor
	 * @access private
	 * @return void
	 */
	var $user_id;
	var $user_name;
	var $user_db_key;

	function __construct() {
		global $tpl;
		$user = authenticate ();
		if ($user != false) {
			$this->user_id = $user [1];
			$this->user_name = $user[0];
			$this->user_db_key = $user[12];
			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_name', $user [0] );
			$tpl->assign ( 'user_id', $user [1] );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_icon', $user [16] );
		} else {
			$tpl->assign ( 'is_login', 0 );
			$tpl->assign ( 'user_name', '' );
			$tpl->assign ( 'user_id', '' );
			$tpl->assign ( 'user_nickname', '' );
			$tpl->assign ( 'user_rank', '' );
			$tpl->assign ( 'user_icon', '' );

			//die;
		}
	}

	//保存用户博客信息
	function op_updatepersoninfo6()
	{
		$userotherspace = isset( $_POST [ 'f_User_Otherspace' ] ) ? $_POST [ 'f_User_Otherspace' ] : '';

		$rss = '';
		$filename = '';

		$url51_1 = "/^http:\/\/(.+)\.51\.com\/?$/i";
		$url51_2 = "/^http:\/\/home\.51\.com\/(.+)$/i";

		//51的blog
		if ( ( preg_match( $url51_1 , $userotherspace , $m ) ) || ( preg_match( $url51_2 , $userotherspace , $m ) ) )
		{
			$userotherspace = addslashes ( $m [0] );
			$userurl = "http://home.51.com/" . $m [1];
			$_SESSION['rss_name'] = $m [1];
			$isvalidate = $this->validate51 ( "http://home.51.com/" . $m [1] . '/diary' ); //验证有效性

			if ( $isvalidate )
			{
				$rss = addslashes ( "http://home.51.com/" . $m [1] . '/diary' );
			}
			else
			{
				die ( 'no' );
			}
			$filename = $this->user_name . '.html';
		}
		//非51blog
		else
		{
			if ( !empty( $userotherspace ) )
			{
				//debug;
				$url = $this->get_rss_xml( $userotherspace );
				if ($url)
					$rss = $url;
				else
					die ( 'no' );
			}
			$filename = $this->user_name . '.xml';
		}

		include_once ("UserModel.class.php");
		$UserModel = new UserModel ( $this->user_db_key );
		$userblog = array ('0' => $userotherspace, '1' => $rss, '2' => $this->user_id );
		$res = $UserModel->saveUserblog ( $userblog );

		//删除以前的缓存文件
		$rssDir = getcwd() . "/tmp/rss";
		$file = $rssDir . "/" . $filename;
		if ( file_exists( $file ) )
		{
			@unlink ( $file );
		}

		if ( $res && $rss )
		{
			die ( 'yes' );
		}
		else
		{
			die ( 'no' );
		}
	}

	//验证rss是否有效
	function get_rss_xml($url) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
		curl_setopt ( $ch, CURLOPT_ENCODING, "" );

		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.8.1) Gecko/20061010 Firefox/2.0" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$content = curl_exec ( $ch ); //远程抓取内容
		curl_close ( $ch );
		$pattern0 = "/.*?rssfeed:\/\/(.+?)[\'|\"]/i";
		$pattern1 = "/.*?type\=[\'|\"|\s]application\/rss\+xml[\'|\"|\s](.*?)href\=[\'|\"|\s](http:\/\/.+?)[\'|\"|\s].*?/i";
		$pattern2 = "/.+?href\=[\'|\"](http:\/\/.+?)[\'|\"](.*?)type\=[\'|\"|\s]application\/rss\+xml[\'|\"|\s]/i";
		$pattern3 = "/.*?[\'](http:\/\/feeds\.qzone\.qq\.com\/cgi-bin\/cgi_rss_out\?uin=\d+)[\'].*/i";

		if (preg_match_all ( $pattern0, $content, $arr, PREG_SET_ORDER )) {
			if ($arr) {
				return "http://" . $arr [0] [1];
			}
		} elseif (preg_match_all ( $pattern1, $content, $arr, PREG_SET_ORDER )) {
			if ($arr) {
				return $arr [0] [2];
			}
		} elseif (preg_match_all ( $pattern2, $content, $arr, PREG_SET_ORDER )) {
			if ($arr) {
				return $arr [0] [1];
			}
		} elseif (preg_match_all ( $pattern3, $content, $arr, PREG_SET_ORDER )) {
			if ($arr) {
				return $arr [0] [1];
			}
		}
		return false;
	}
	//验证51blogurl有效性
	function validate51($userurl) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $userurl );
		curl_setopt ( $ch, CURLOPT_REFERER, "http://home.51.com" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_ENCODING, "" );
		$content = curl_exec ( $ch );
		$content = strip_tags ( $content, "<html><body><div><a>" );
		curl_close ( $ch );

		$is51user = "/diary\.php\?user=/i";
		if (preg_match ( $is51user, $content, $m )) {
			return true;
		} else {
			return false;
		}
	}
}
?>