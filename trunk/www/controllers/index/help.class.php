<?php
include_once ("HelpModel.class.php");
include_once ("Cache.class.php");
/**
 * @abstract 公共信息类
 * @author zswu at
 *
 **/
class help {

    var $help;
	var $userInfo;

	function __construct()
	{
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
		$this->help=new HelpModel();
	}


	function view_index() {
		global $tpl;
		$tpl->assign ( "title", '常见问题-果动网-果然会动-网页3D娱乐' );
	}

	function view_kfzx() {
		global $tpl;
        $msg = "";
		$gbook=$this->help->getGuestBook(10,true);
		$tpl->assign ( "list_array", $gbook);
		$tpl->assign ( "user_name", $this->userInfo ['user_name']);
		$tpl->assign ( "title", '客服中心-果动网-果然会动-网页3D娱乐' );
	}

	function view_hdgg() {
		global $tpl;

		$list_array = array();
		$list_array = $this->help->getbulletin(15,true);
		$img_array = $this->help->getImage(3);

		$flag_id = isset($_GET['id'])?$_GET['id']:'0';
		$flag = isset($_GET['flag'])?$_GET['flag']:'0';
		$tpl->assign ( "list_array", $list_array);
		$tpl->assign ( "img_array", $img_array);
		$tpl->assign ( "flag_id", $flag_id);
		$tpl->assign ( "flag", $flag);
		$tpl->assign ( "title", '活动公告-果动网-果然会动-网页3D娱乐' );
	}

	function view_hdggContent() {
		global $tpl;

		$id = isset($_GET['id'])?$_GET['id']:'';
		if($id)
		{
			$rs = $this->help->getbulletinContent($id);
		}
		else
		{
		   redirect('/index.php?action=help&view=hdgg');
		}
		$img_array = $this->help->getImage(3);
		$tpl->assign ( "img_array", $img_array);
		$tpl->assign ( "rs", $rs);
		$tpl->assign ( "title", '活动公告-果动网-果然会动-网页3D娱乐' );
	}

	function view_doing() {
		global $tpl,$gSiteInfo;
		$list_array = array();
		$list_array = $this->help->getbulletin(10,true);
		$str='';
		foreach ($list_array['record'] as $key => $vs) {
		   $url=$gSiteInfo['www_site_url'].'/index.php?action=help&view=hdgg&id='.$vs['id'].'&flag=1#'.$vs['id'];
		   $str.= 'document.writeln(\'<dt class="green_12_blod">'.date('Y-m-d',$vs['time']).'</dt>\');';
		   $str.='document.writeln(\'<dd class="gray_12"><a class="gray_12" href="'.$url.'" target="_blank">'.$vs['title'].'</a></dd>\');';
		}
		echo $str;
		die;
	}

	function my_mb_substr($str,$start,$end) {
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $ar);
    if(func_num_args() >= 3) {
        $end = func_get_arg(2);
        return join("",array_slice($ar[0],$start,$end))."...";
    } else {
        return join("",array_slice($ar[0],$start))."...";
    }
}

	function view_gonggao()
	{
		 global $gSiteInfo;
		 header('Content-Type: text/javascript');
	 	 header("Cache-Control: max-age=604800, public");
		$file ='gonggao.html';
		$str = '';

		$cache = new Cache(3600*24);
		$cache->setCacheDir(APP_TEMP_DIR.'/');
		$cache->setCacheFile($file);
		if($cache->isCached()){
			$str = unserialize($cache->fetch());
		}else{
		    $list_array = array();
		    $list_array = $this->help->getbulletin(3,true);
			$str='document.writeln(\'<ul class="Annount" >\');';
		    foreach ($list_array['record'] as $key => $vs) {
		     $url= $gSiteInfo['www_site_url'].'/index.php/help/hdggContent/id/'.$vs['id'];
		     $date=substr(date('Y',$vs['time']),-2).'/'.date('m',$vs['time']).'/'.date('d',$vs['time']);
		     if(strlen($vs['title'])>31){
		       $title=$this->my_mb_substr($vs['title'],0,14);
		     }else{
		       $title=$vs['title'];
		     }
	         $str.='document.writeln(\'<li style="padding-left:3px;"><p class="date gray6_12">'.$date.'</p><p class="msg"><a href='.$url.' class="blue_by12 noneline">'.$title.'</a></p><p class="clr"></p></li>\');';
		    }
	        $str.='document.writeln(\'</ul>\');';
			$cache->save(serialize($str));
		}

	  echo $str;
	  die;

	}

	function view_newuser() {
		global $tpl;
		$tpl->assign ( "title", '新手指南-果动网-果然会动-网页3D娱乐' );
	}
	//友情链接
	function view_hzhb() {
		global $tpl;
		$imglink=$this->help->getAllLink(0);
		$txtlink=$this->help->getAllLink(1);
		$tpl->assign ( "img", $imglink);
		$tpl->assign ( "txt", $txtlink);
		$tpl->assign ( "title", '合作伙伴-果动网-果然会动-网页3D娱乐' );
	}

	function view_aboutme() {
		global $tpl;
		if (file_exists ( APP_TEMP_DIR . "/aboutmeblock.html" )) {
			$exist = 1;
		} else {
			$exist = 0;
		}
		$tpl->assign ( "exist", $exist );
		$tpl->assign ( "title", '关于我们-果动网-果然会动-网页3D娱乐' );
	}

	function view_swhz() {
		global $tpl;
		$tpl->assign ( "title", '商务合作-果动网-果然会动-网页3D娱乐' );
	}

	function view_gongneng() {
		global $tpl;
		$tpl->assign ( "title", '功能介绍-果动网-果然会动-网页3D娱乐' );
	}

	function view_map() {
		global $tpl;
		$tpl->assign ( "username", $this->userInfo ['user_name']);
		$tpl->assign ( "title", '网站地图-果动网-果然会动-网页3D娱乐' );
	}

	function view_clause() {
		global $tpl;
		$tpl->assign ( "title", '使用条款-果动网-果然会动-网页3D娱乐' );
	}

	function op_feedback() {
		global $gSiteInfo;
		$fd_c = strip_tags($_POST['fd_c']);
		$anonym = empty( $_POST['anonym'] )?0:$_POST['anonym'];
		$user_name = empty($_POST ['passport_user'])?'':$_POST ['passport_user'];
		$user_passwd = empty($_POST ['passport_passwd'])?'':$_POST ['passport_passwd'];
		$users = authenticate ();
		$username="";
		if(empty ( $fd_c )||(!$user_name&&!$anonym&&!$users))
		{
			echo '0';
			exit;
		}
		if(!$anonym){
		if(!$anonym&&!$users){
		$user = $this->UserModel->getUserByName ( $user_name );
		if ($user!== false) {
	      if ($user ['user_passwd'] == md5 ( $user_passwd )){$username=$user_name;}
		  else {echo '-1';exit;}
		  }else {echo '-1';exit;}}
		  if(!$anonym&&$users){$username=$users[0];}}
			$help = new HelpModel ( );
			$help->doFeedback ($fd_c,$username);echo 1;
	}
	
	function view_qianru(){
		global $tpl;
		$tpl->assign ( "title", '嵌入空间效果展示-果动网-果然会动-网页3D娱乐' );
	}
}
?>