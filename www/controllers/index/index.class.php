<?php
include_once ("ApiUser.class.php");
include_once ("UserModel.class.php");
//include_once ("Cache.class.php");
/**
 * @abstract 首页管理类
 * @author zswu at
 *
 **/
class index {
	var $userInfo;

	function __construct() {
		global $tpl;
		$user = authenticate ();
		
		if ($user != false) {

			$this->userInfo ['is_login'] = 1;
			$this->userInfo ['user_nickname'] = $user [2];
			$this->userInfo ['user_id'] = $user [1];
			$this->userInfo ['user_rank'] = $user [4];
			$this->userInfo ['user_name'] = $user [0];
			$this->userInfo ['user_gender'] = $user [6];
			$this->userInfo ['user_cash'] = isset($_COOKIE['IDOL_CASH_'.$user [0]])?$_COOKIE['IDOL_CASH_'.$user [0]]:0;
			$this->userInfo ['user_coin'] = isset($_COOKIE['IDOL_COIN_'.$user [0]])?$_COOKIE['IDOL_COIN_'.$user [0]]:0;

			$this->user_id = $user [1];
			$this->user_name = $user [0];
			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_vote_num', $user [10] );
			$tpl->assign ( 'average', $user [11] );
			$tpl->assign ( 'user_name', $user [0] );
			$tpl->assign ( 'user_vip', $user [14] );
			$tpl->assign ( 'user_cash', $this->userInfo ['user_cash'] );
			$tpl->assign ( 'user_coin', $this->userInfo ['user_coin']);
			$tpl->assign ( 'user_icon', $user [16] );
		} else {
			$this->user_name = '';
			$tpl->assign ( 'is_login', 0 );
			$tpl->assign ( 'user_vip', 0 );
			$tpl->assign ( 'user_nickname', '默认' );
			$tpl->assign ( 'user_rank', '' );
			$tpl->assign ( 'user_vote_num', 0 );
			$tpl->assign ( 'average', 0 );
			$tpl->assign ( 'user_name', '' );
			$tpl->assign ( 'user_jsurl', '' );
		}
	}
	
	function view_defaults() {
		header("Location: /index.php/passport/login");die;
		global $tpl;
		$tpl->assign ( "user_example", $this->getUserExample());
		//$tpl->assign ( "new_reg_user", $this->getNewRegUser());
		$tpl->assign ( "view", "defaults" );
		$tpl->assign ( "title", "果动网-果然会动-网页3D娱乐" );

//		$index_html = APP_DIR.'/index.html';
//		if(!file_exists($index_html)){
//			if($this->user_name=='') Controller::createHtml($index_html);
//		}

	}
}

?>