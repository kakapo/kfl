<?php
include_once ("ApiUser.class.php");
include_once ("UserModel.class.php");
include_once ("Cache.class.php");
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
    function view_recomenddiy(){
	  $content = '';
	  $con['sort'] = isset ( $_GET ['sort'] ) ? $_GET ['sort'] : '1';
	  include_once ("ShowItem.class.php");
	  $show = new ShowItem();
	  $list_array = $show->getRecomendIndex ( $con,6 ); //查询结果
	  if(isset($list_array['records'])){
	  		foreach ($list_array['records'] as $key=>$v){
                $content .='	<div class="list">
                					<a href="/index.php/show/frameset/recommend" class="list_pic none">';
                if($con['sort']==2) $content .= "     <span class='new_hot' style='width:53px; height:59px;background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/hot_pico_in.png) 0 0 no-repeat; +background:none;'><img src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/blank.gif style='background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/hot_pico_in.png)!important;background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/hot_pico_in.png',sizingMethod='image'); /></span>
     ";
				if($con['sort']==1) $content .= "     <span class='new_hot' style='width:53px; height:59px;background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png) 0 0 no-repeat; +background:none;'><img src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/blank.gif style='background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png)!important;background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png',sizingMethod='image'); /></span>
     ";
		        $content .= '<span class="jiage_add_gg"><b class="guoguo">'.$v['coins'].'</b><b class="add_bg"></b><b class="g_bg">'.$v['cash'].'</b></span>';
		        if($v['pic'])  $content .='      <img src="'.$GLOBALS ['gSiteInfo'] ['image_site_url'].'/recomenddiy/images/'. $v['pic'].'" />';
                $content .='     </a>
                					<div class="list_tit"><a href="/index.php/show/frameset/recommend" class="yellow_13b none">'.$v['title'].'</a></div>
                				</div>';
			}
	  }
	  echo $content;
	  die;
	}
	function view_displayItems(){
	  include_once ("ShowItem.class.php");
	  $show = new ShowItem();
	  $list_array['records'] = $show->getDisplayItem (); //查询结果
	  $content='';
	  if(isset($list_array['records'])){
	  		foreach ($list_array['records'] as $key=>$v){
	  			if($v['item_price_type']=='GB'){
	  				 $gbclass="<B class='g_bg'>{$v['item_price']}</B>";
	  			}else{
	  				 $gbclass="<B class='guoguo'>{$v['item_price']}</B>";
	  			}		  
                $content.="<DIV class='list'>
                    <DIV class='pic'>
                        <A href='/index.php/show/frameset/clothes'>                         
			            <span class='new_hot' style='width:53px; height:59px;background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png) 0 0 no-repeat; +background:none;'><img src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/blank.gif style='background:url(".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png)!important;background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$GLOBALS ['gSiteInfo'] ['image_site_url']."/images/new_pico_in.png',sizingMethod='image'); /></span>
                            <SPAN class='jiage_add_gg2'>{$gbclass}</SPAN>
                            <IMG src='{$GLOBALS ['gSiteInfo'] ['image_site_url']}/images/itemimages/{$v['item_image']}.jpg' width='103' height='103'>                            
                        </A> 
                   </DIV>
                    <DIV class='name yellow_13b'>{$v['item_title']}</DIV>
                </DIV>";
			}
	  }
	  echo $content; 
	  die;
	}
	            

	function view_getuser(){
			//保险系数，由于多服务器mount同一个文件
			$include_flag = 0;
			$file = APP_TEMP_DIR.'/user_setup.php';
			$file_bak = APP_TEMP_DIR.'/user_setup_'.date('H').'.php';
			$file_hour_ago = APP_TEMP_DIR.'/user_setup_'.date('H',(time()-3600)).'.php';
			if(file_exists($file)){
				include($file);
				$include_flag = 1;
			}else if(file_exists($file_bak)){
				//先当前小时的备份文件
				include($file_bak);
				$include_flag = 1;
			}else{
				//恢复一个小时之前数据。
				if(file_exists($file_hour_ago)) {
					include($file_hour_ago);
					$include_flag = 1;
				}
			}

			$date = 'today_user_'.date("m_d");
			//$total_user = isset($total_user)?$total_user:'4875000';
			if(isset($total_user) && $total_user>4875000){
				//调整比例
				$today_user = isset($$date)?$$date:5;
				if(rand(1,100)<70){
					$add = rand(1,5);

					$total_user = $total_user+$add;
					$today_user = $today_user+$add;
					$content = '<?php $total_user='.$total_user.';$'.$date.'='.$today_user.';?>';
					if($include_flag && file_exists($file) && $today_user>0){
						write_file($content,$file);
					}
					//每10分钟备份一下数据
					if(date("i")%10==0){
						if($include_flag && file_exists($file_bak) && $today_user>0){
							write_file($content,$file_bak);
						}
					}

				}
				if($today_user>0){
					$total_user = number_format($total_user);
					$today_user = number_format($today_user);
					echo "document.getElementById('total_user').innerHTML = '".$total_user."';";
					echo "document.getElementById('today_user').innerHTML = '".$today_user."';";
					die;
				}
			}
			die;

	}
	function getNewRegUser(){
		$file ='newreguser.html';
		$content = '';

		$cache = new Cache(600);
		$cache->setCacheDir(APP_TEMP_DIR.'/');
		$cache->setCacheFile($file);
		if($cache->isCached()){
			$content = $cache->fetch();
		}else{
			$UserModel = new UserModel ( );
			$users = $UserModel->getNewRegUser(10);
			$content .=' <ul class="user_list">';
			foreach ($users as $k=>$v){
                $content .='	<li>';
                if($v['user_gender']==1) $content .='         <div class="boy"><span></span></div>';
                if($v['user_gender']==2) $content .='         <div class="girl"><span></span></div>';
                $content .= '     <div class="name"><a href="index.php?action=user&view=center&user='.$v['user_name'].'" class="blue_by12 underline">'.$v['user_nickname'].'</a></div>';
                $content .='    </li>';
               if(($k+1)%5==0 ) $content .=' </ul><ul class="user_list">';
			}
			 $content .=' </ul>';
			$cache->save($content);
		}
		return $content;
	}
	function getUserExample(){
	  $content = '';
	  $UserModel = new UserModel ("");
	  $boy_example = $UserModel->getExample(1);
	  $girl_example = $UserModel->getExample(2);
	  $data= array($boy_example,$girl_example);
	  return $data;
	}
	function view_clearindexhtml(){
		$index_html = APP_DIR.'/index.html';
		Controller::destoryHtml($index_html);
		die;
	}
	function view_clearreghtml(){
		$reg_html = APP_DIR.'/reg.html';
		Controller::destoryHtml($reg_html);
		die;
	}


	/***** 其他网页 开始****/
	function view_chatintro() {
		global $tpl, $gservices;
		$tpl->assign ( "gservices", $gservices );
		$tpl->assign ( "title", "3D聊天工具-果动网-果然会动-网页3D娱乐" );
	}
	function view_gameintro() {
		global $tpl, $gservices;
		$tpl->assign ( "gservices", $gservices );
		$tpl->assign ( "title", "3D社交游戏-果动网-果然会动-网页3D娱乐" );

	}
	function op_vote() {
		$userid = $_POST ['userID'];
		$value = $_POST ['value'] * 2;
		$cookuser = "IDOL_VOTE_" . $userid;
		//echo $userid;
		if ((isset ( $_COOKIE [$cookuser] )) && ($_COOKIE [$cookuser] == $userid)) { //已投过
			echo '0';

		} else { //未投过
			$userbase = ApiUser::getUserByName($this->userInfo ['user_name']);
			$this->UserModel = new UserModel ($userbase['user_db_key'] );
			$rs = $this->UserModel->getVote ( $userid );

			if (! empty ( $rs ['user_id'] )) { //投票
				$this->UserModel->updateVote ( $userid, $value );

				$avg_vote = round ( (($rs ['user_vote_total'] + $value) / ($rs ['user_vote_num'] + 1)), 0 ); //平均分数=评分总数/评分人数
				$back = $avg_vote . "|" . ($rs ['user_vote_num'] + 1);
				setcookie ( $cookuser, $userid, time () + 24 * 3600 );
				echo $back;
			} else { //被投用户id错误
				echo '-1';
			}

		}
		die ();
	}

	/***** 用户榜样 开始****/
	function view_example()
	{
		global $tpl;
		//读取榜样信息
		$UserModel = new UserModel("");
		$boy_example = $UserModel->getExample(1);
		$girl_example = $UserModel->getExample(2);
		$is_login = empty($this->userInfo ['is_login'])?'0':'1';

		$tpl->assign ( "title", "果动榜样-果动网-果然会动-网页3D娱乐" );
		$tpl->assign ( "is_login",$is_login );
		$tpl->assign ( "boy_example",$boy_example );
		$tpl->assign ( "girl_example",$girl_example );
	}

	function op_saveexample()
	{
		if(!$_POST['blog_path'])
		{
			show_message_goback ( "请填写博客地址!" );
		}
		$user_info = array(
							 'user_id' => $this->userInfo ['user_id'],
							 'user_name' => $this->userInfo ['user_name'],
							 'user_nickname' => $this->userInfo ['user_nickname'],
							 'user_gender' => $this->userInfo ['user_gender'],
							 'user_blog_path' => $_POST['blog_path']
							 );
		$userbase = ApiUser::getUserByName($this->userInfo ['user_name']);
		$this->UserModel = new UserModel ($userbase['user_db_key'] );
		$count = $this->UserModel->checkExample($user_info['user_id']);
		if(!$count)
		{
			if($this->UserModel->addExample($user_info))
			{
				echo '申请成功!';
			}
			else
			{
				echo '申请失败!';
			}
		}
		else
		{
			echo '你已经申请过上榜了!';
		}
	}
}

?>