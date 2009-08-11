<?
include_once ("UserModel.class.php");
/**
 * @abstract 图片上传类
 * @author zswu at
 *
 **/
class image 
{
	var $user_id;
	var $user_name;

	function __construct() 
	{
		global $tpl;
		$user = authenticate ();

		if ( $user !== false ) 
		{
			$this->user_id = $user [1];
			$this->user_name = $user [0];

			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_name', $user [0] );
			$path = substr ( $user [0], 0, 1 ) . '/' . substr ( $user [0], 0, 2 ) . '/' . substr ( $user [0], 0, 3 ) . '/' . $user [0];
			$tpl->assign ( 'user_jsurl', $user [5] . '/' . $path . '/' . $user [0] . '.js' );
			$this->user_pic = $user [5] . '/' . $path . '/' . $user [0] . '/230x420.jpg';
		} 
		else 
		{
			$tpl->assign ( 'is_login', 0 );
			$tpl->assign ( 'user_nickname', '' );
			$tpl->assign ( 'user_rank', '' );
			$tpl->assign ( 'user_name', '' );
			$tpl->assign ( 'user_jsurl', '' );
		}
	}

	function view_upload() 
	{
		global $tpl;
		
		//解决ie6 下iframe 因gzip 不显示页面问题
		header ( "Content-encoding: none" );
		
		$login = 1;
		if ( !$this->user_name ) 
		{
			$login = 2;
		}

		$tpl->assign ( 'login', $login );
		$tpl->assign ( 'user_name', $this->user_name );
	}

	function op_onuploadavatar() {
		$user_path = APP_TEMP_DIR . '/';
		@header ( "Expires: 0" );
		@header ( "Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE );
		@header ( "Pragma: no-cache" );

		if (empty ( $_FILES ['Filedata'] )) {
			echo "no pic";
			exit ();
		}

		$file = @$_FILES ['Filedata'] ['tmp_name'];
		$filename = md5 ( time () );
		$filetype = '.jpg'; //note FLASH 通过 MIME 头判断。
		/*if(file_exists(APP_TEMP_DIR.'/'.$filename.$filetype))
		{
			@unlink(APP_TEMP_DIR.'/'.$filename.$filetype);
		}*/
		@copy ( $_FILES ['Filedata'] ['tmp_name'], $user_path . $filename . $filetype ) || @move_uploaded_file ( $_FILES ['Filedata'] ['tmp_name'], $user_path . $filename . $filetype );
		$avatarur = $GLOBALS ['gSiteInfo'] ['www_site_url'] . '/tmp/' . $filename . $filetype . '?name=' . rand ();

		echo $avatarur;
		exit ();
	}

	//note public 外部接口 flash 方式裁剪头像 COOKIE 判断身份
	function op_onrectavatar() {
		$userinfo = $this->UserModel->getUserExt ( $this->user_id );
		$user_path = $userinfo['user_store'] . "/" . $userinfo ['user_path'] . "/";
		@header ( "Expires: 0" );
		@header ( "Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE );
		@header ( "Pragma: no-cache" );
		header ( "Content-type: application/xml; charset=utf-8" );

		$img_name = md5 ( time () );
		$bigavatarfile = $user_path . $img_name . '_b.jpg';
		$middleavatarfile = $user_path . $img_name . '_m.jpg';
		$smallavatarfile = $user_path . $img_name . '_s.jpg';
		$bigavatar = base64_decode ( $this->getgpc ( 'avatar1', 'P' ) );
		$middleavatar = base64_decode ( $this->getgpc ( 'avatar2', 'P' ) );
		$smallavatar = base64_decode ( $this->getgpc ( 'avatar3', 'P' ) );
		if (! $bigavatar || ! $middleavatar || ! $smallavatar) {
			echo '2##';
			/*exit('<root><message type="error" value="-2" /></root>');*/
		}
		if ($fp = @fopen ( $bigavatarfile, 'w' )) {
			@fwrite ( $fp, $bigavatar );
			@fclose ( $fp );

			$fp = @fopen ( $middleavatarfile, 'w' );
			@fwrite ( $fp, $middleavatar );
			@fclose ( $fp );

			$fp = @fopen ( $smallavatarfile, 'w' );
			@fwrite ( $fp, $smallavatar );
			@fclose ( $fp );
			$filetype = '.jpg'; //note FLASH 通过 MIME 头判断。
			$user_img = $this->UserModel->getUserPicPath ( $this->user_id );
			if (file_exists ( $user_img ['user_store'] . '/' . $user_img ['user_path'] . '/' . $user_img ['user_pic'] . '_b' . $user_img ['user_pic_ext'] )) {
				@unlink ( $user_img ['user_store'] . '/' . $user_img ['user_path'] . '/' . $user_img ['user_pic'] . '_b' . $user_img ['user_pic_ext'] );
				@unlink ( $user_img ['user_store'] . '/' . $user_img ['user_path'] . '/' . $user_img ['user_pic'] . '_m' . $user_img ['user_pic_ext'] );
				@unlink ( $user_img ['user_store'] . '/' . $user_img ['user_path'] . '/' . $user_img ['user_pic'] . '_s' . $user_img ['user_pic_ext'] );
			}

			$user_avt = array ("0" => $img_name, "1" => $filetype, "2" => $this->user_id );
			$this->UserModel->saveUserPic ( $user_avt );
			$this->clearMemcache();
			echo $this->user_name . "|http://" . $user_img ['user_host'] . '/' . $user_img ['user_path'] . '/' . $img_name . '_m.jpg|http://' . $user_img ['user_host'] . '/' . $user_img ['user_path'] . '/' . $img_name . '_b.jpg##';
			/*exit('<?xml version="1.0" ?><root><face success="1"/></root>');*/
		} else {
			echo '0##';
			/*exit('<?xml version="1.0" ?><root><face success="0"/></root>');*/
		}
	}

	//note 此参数仅为FLASH，或者第三方应用请求用户中心时使用。
	function getgpc($k, $var = 'R') {
		switch ($var) {
			case 'G' :
				$var = &$_GET;
				break;
			case 'P' :
				$var = &$_POST;
				break;
			case 'C' :
				$var = &$_COOKIE;
				break;
			case 'R' :
				$var = &$_REQUEST;
				break;
		}
		return isset ( $var [$k] ) ? $var [$k] : NULL;
	}

	function clearMemcache()
	{
		$memConfig = $GLOBALS['gMemcacheServer'];

		$memcache = new Memcache;
		$memcache->connect( $memConfig[0]['host'] , $memConfig[0]['port'] , MEMCACHE_APP_DATA_EXPIRED ) or die ("Could not connect");
		$memcache->delete('userPicInfo',10);
	}
}
?>