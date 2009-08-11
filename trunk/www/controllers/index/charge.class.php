<?php
/**
 * @abstract 充值管理类
 * @author sbjiang at
 *
 **/

class charge {

	var $userInfo;
	function __construct(){

	  global $tpl;
	  $user = authenticate ();

	  if ($user != false) {
			$this->userInfo ['is_login'] = 1;
			$this->userInfo ['user_nickname'] = $user [2];
			$this->userInfo ['user_id'] = $user [1];
			$this->userInfo ['user_rank'] = $user [4];
			$this->userInfo ['user_name'] = $user [0];
			$this->userInfo ['user_gender'] = $user [6];
			$this->userInfo ['user_db_key'] = $user [12];

			$this->userInfo ['user_cash']	 = 	 isset($_COOKIE['IDOL_CASH_'.$user [0]])?$_COOKIE['IDOL_CASH_'.$user [0]]:0;
			$this->userInfo ['user_coin'] = isset($_COOKIE['IDOL_COIN_'.$user [0]])?$_COOKIE['IDOL_COIN_'.$user [0]]:0;    
			$this->user_id = $user [1];
			$this->user_name = $user [0];
			$tpl->assign ( 'user_vip', $user [14] );
			$tpl->assign ( 'user_cash', $this->userInfo ['user_cash']);
			$tpl->assign ( 'user_coin', $this->userInfo ['user_coin']);
			$tpl->assign ( 'user_gender', $user[6] );
			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_vote_num', $user [10] );
			$tpl->assign ( 'average', $user [11] );
			$tpl->assign ( 'user_name', $user [0] );
	  }else{
	  	show_message ( '请先登录!' );
	    redirect ( $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php?action=passport&view=login&from=/".urlencode('index.php?action=charge'));
	  }
	}
	//冲值
	function view_defaults()
	{
	   global $tpl;
	  
	   if(isset($_GET ['czmethod'])){
	   	 $czmethod=$_GET ['czmethod'];
	   }else{
	   	 $czmethod='zfb';
	   }
	   switch ($czmethod){
	   	
	   	case 'history':
	   		$location="/index.php/charge/history";
	   		break;
	   	case 'cosume':
	   		$location="/index.php/charge/consume";
	   		break;
	   	case 'cft':    //财付通
	    	$location="/index.php/charge/tenpay";
	   		break;
	   	case 'kq':     //快钱
	      	$location="/index.php/charge/99bill";
	   		break;
	   	default:       //支付宝
	       $location="/index.php/charge/alipay";
	   	    break;
	   }
	   
	   	include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->userInfo ['user_db_key'] );	
		$userInfo = $_userObj->getUserBlog( $this->userInfo ['user_id'] );	
	   $tpl->assign ( 'location', $location );
	   $tpl->assign ( "title", "快速充值-果动网-果然会动-网页3D娱乐" );
	   $tpl->assign ( 'userInfo' , $userInfo );
	   $tpl->assign ( 'cur' , 'charge' );
	}
	//支付宝
	function view_alipay(){
	  global $tpl;
	  $search['return_url']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/alipay/return_url.php?u=".$this->userInfo ['user_name'];
	  $search['notify_url']='';
	  $search['send_url']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/alipay/index.php";
	  $search['user_id']=$this->userInfo ['user_id'];
	  $tpl->assign ( 'search', $search );
	  $tpl->assign ( 'title', '支付宝充值' );
	}
	//财付通
	function view_tenpay(){
	  global $tpl;
	  $search['sendurl']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/tenpay/md5_request.php";
	  $search['reurl']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/tenpay/notify_handler.php";
	  $search['user_id']=$this->userInfo ['user_id'];
	  $search['user_name']=$this->userInfo ['user_name'];
	  $tpl->assign ( 'search', $search );
	  $tpl->assign ( 'title', '财付通充值' );
	}

   //快钱
	function view_99bill(){
	  global $tpl;
	  $search['sendurl']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/99bill/send.php";
	  $search['reurl']=$GLOBALS ['gSiteInfo'] ['www_site_url']."/plugins/99bill/receive.php";
	  $search['user_id']=$this->userInfo ['user_id'];
	  $search['user_name']=$this->userInfo ['user_name'];
	  $tpl->assign ( 'search', $search );
	  $tpl->assign ( 'title', '快钱充值' );
	}

	//冲值历史
	function view_history()
	{
	  global $tpl;
	  $search['user_name']=$this->userInfo ['user_name'];
	  $search['user_id']=$this->userInfo ['user_id'];
	  if(isset($_GET['year'])&&!empty($_GET['year'])){
	  	 $search['year']=$_GET['year'];
	  }else{
	  	 $search['year']=date('Y');
	  }
	  if(isset($_GET['month'])&&!empty($_GET['month'])){
	  	 $search['month']=$_GET['month'];
	  }else{
	  	 $search['month']="";
	  }
	  include_once( "ApiUser.class.php" );
	  include_once( "ChargeModel.php" );
	  $mymain=new ChargeModel();
      $list_array = $mymain->getMyHistoryList(20,true,$search ); //查询结果
      $tpl->assign ('search', $search);
      $tpl->assign ('list_array',$list_array );
	  $tpl->assign ( 'title', '充值历史' );
	}
	//消费记录
	function view_consume()
	{
      global $tpl;
	  $search['user_name']=$this->userInfo ['user_name'];
	  $search['user_id']=$this->userInfo ['user_id'];
	  if(isset($_GET['year'])&&!empty($_GET['year'])){
	  	 $search['year']=$_GET['year'];
	  }else{
	  	 $search['year']=date('Y');
	  }
	  if(isset($_GET['month'])&&!empty($_GET['month'])){
	  	 $search['month']=$_GET['month'];
	  }else{
	  	 $search['month']="";
	  }
	  if(isset($_GET['moneytype'])&&!empty($_GET['moneytype'])){
	  	 $search['moneytype']=$_GET['moneytype'];
	  }else{
	  	 $search['moneytype']="";
	  }
	  include_once( "ApiUser.class.php" );
	  include_once( "ChargeModel.php" );
	  $mymain=new ChargeModel();
      $list_array = $mymain->getMyConsumeList(14,true,$search ); //查询结果
      $tpl->assign ('search', $search);
      $tpl->assign ('list_array',$list_array );
	  $tpl->assign ('title', '消费记录' );
	}
}

?>