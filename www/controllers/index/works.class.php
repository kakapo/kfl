<?php
include_once("Original.class.php");
/**
 * @abstract 原创作品上传类
 * @author sbjiang at
 *
 **/
class works {

    var $userInfo;
	var $works_obj;
	var $is_login;
	var $user_name;
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
			$path = substr ( $user [0], 0, 1 ) . '/' . substr ( $user [0], 0, 2 ) . '/' . substr ( $user [0], 0, 3 ) . '/' . $user [0];

			$this->user_id = $user [1];
			$this->user_name = $user [0];
			$this->is_login=1;
			$tpl->assign ( 'is_login', 1 );
			$tpl->assign ( 'user_nickname', $user [2] );
			$tpl->assign ( 'user_rank', $user [4] );
			$tpl->assign ( 'user_vote_num', $user [10] );
			$tpl->assign ( 'average', $user [11] );
			$tpl->assign ( 'user_name', $user [0] );
			$tpl->assign ( 'user_vip', $user [14] );
			$tpl->assign ( 'user_cash', $this->userInfo ['user_cash'] );
			$tpl->assign ( 'user_coin', $this->userInfo ['user_coin']);
			$tpl->assign ( 'user_pic', '' );
		} else {
			$this->user_name = '';
			$this->is_login=0;
			$tpl->assign ( 'is_login', 0 );
			$tpl->assign ( 'user_vip', 0 );
			$tpl->assign ( 'user_nickname', '默认' );
			$tpl->assign ( 'user_rank', '' );
			$tpl->assign ( 'user_vote_num', 0 );
			$tpl->assign ( 'average', 0 );
			$tpl->assign ( 'user_name', '' );
			$tpl->assign ( 'user_jsurl', '' );
		}	
		$this->works_obj=new Original();
	}
	
	function view_defaults()
	{
	  header ( 'Location: /index.php/works/topic' );
	  die ();
	}

	//美术资源
	function view_art() {
		global $tpl;
		$list_array = $this->works_obj->getdatalist (0,15,true);
		$tpl->assign ( 'list_array', $list_array );
		$tpl->assign ( 'title', '美术资源' );
	}

	//主题背景
	function view_topic()
	{
	  global $tpl;
      $list_array = $this->works_obj->getdatalist (1,15,true);
	  $tpl->assign ( 'list_array', $list_array );
	  $tpl->assign ( 'title', '主题背景' );
	}
	//协议
	function view_agree()
	{
	  global $tpl;
	  $tpl->assign ( 'title', '协议' );
	}

	function view_uploadws()
	{
	  global $tpl , $gSiteInfo;
	  $type=isset ( $_GET ['wstype'] ) ? $_GET ['wstype'] : 0;
	  $isread=isset ( $_GET ['isread'] ) ? $_GET ['isread'] : '';
      if(!$isread){
      	show_message ( '您无权访问此页面，请阅读协议后在访问！' );
      	redirect ( $gSiteInfo['www_site_url'] . "/index.php/works/agree");
      }
	  if($this->is_login)
	  {
	    $tpl->assign ( 'type', $type);
	    $tpl->assign ( 'author', $this->user_name);
	    $tpl->assign ( 'title', '作品上传' );
	  }else{
	  	show_message ( '您还没登录,请登录后在上传！' );
	    redirect ( $gSiteInfo['www_site_url'] . "/index.php?action=passport&view=login&from=".urlencode('/index.php/works/uploadws/isread/1'));
	  }

	}

	function op_uploadws()
	{
		$search=array();
        global $gSiteInfo;
        if(!$this->is_login){redirect ( $gSiteInfo['www_site_url'] . "/index.php?action=passport&view=login&from=".urlencode('/index.php/works/uploadws/isread/1'));}
		$search['ws_author']=$this->user_name;
		$search['ws_uploadtime']=date('Y-m-d H:i:s');
		$search['ws_type']= isset ( $_POST ['wstype'] ) ? $_POST ['wstype'] : 0;
		$search['ws_name']= isset ( $_POST ['worksname'] ) ? $_POST ['worksname'] : '';
		$search['ws_remark']= isset ( $_POST ['remark'] ) ? $_POST ['remark'] : '';

		$tmpFile['file1']= isset($_FILES['file1'])?$_FILES['file1']:'';
		$tmpFile['file2']= isset($_FILES['file2'])?$_FILES['file2']:'';

		$igearr2=getimagesize($tmpFile['file2']['tmp_name']);

		$filename1=$tmpFile['file1']['name'];
		$filename2=$tmpFile['file2']['name'];

		if(empty($search['ws_name'])){
		   show_message ( '作品名称不能为空！' );
		   goback(3000);
		   //redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		   die;
		}
		if(empty($filename1)){
			show_message ( '您还没有选择上传的作品！' );
			goback(3000);
		   // redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		   die;
		}else{
		 if(!preg_match("/^[0-9a-zA-Z._]+$/", $filename1)){
		    show_message ( '您上传的文件名格式不正确！' );
		    goback(3000);
		 //   redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		   die;
		 }
		}
		if(empty($filename2)){
		  show_message ( '您还没有选择上传的缩略图作品！' );
		   goback(3000);
		//  redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		  die;
		}else{
		 if(!preg_match("/^[0-9a-zA-Z._]+$/", $filename2)){
           show_message ( '您上传的缩略图文件名格式不正确！' );
           goback(3000);
		//   redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		   die;
		 }
		}
		$setmat=array('RAR','ZIP');
		$filemat=strtoupper(substr($filename1,-3));
		//文件大小，长宽，类型，验证
		 if(!in_array($filemat,$setmat) || $tmpFile['file1']['size']>3145628)
		 {
		   	  show_message ( '原创作品格式不正确,必须为zip或rar压缩格式，大小不能超过3M！' );
		   	  goback(3000);
		  //    redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		      die;
		 }
		if($igearr2[0]!=190 || $igearr2[1]!=133 || $igearr2[2]!=2 || $tmpFile['file2']['size']>1048576){
		  	  show_message ( '缩略图格式不正确，请按照正确的格式上传！' );
		  	  goback(3000);
		   //   redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		     die;
		 }

		$uploadresult1=$this->works_obj->origUpload($tmpFile['file1']);
        $uploadresult2=$this->works_obj->abbrUpload($tmpFile['file2']);

		if($uploadresult1===false){
		  show_message ( '原创作品上传失败！' );
		  goback(3000);
		 // redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		  die;
		}
		if($uploadresult2===false){
		  show_message ( '缩略图上传失败！' );
		  goback(3000);
		 // redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
		  die;
		}
        $search['ws_Orig']=$gSiteInfo['image_site_url']."/images/upload/user_works/".$uploadresult1['save_filename'];
        $search['ws_Abbr']=$gSiteInfo['image_site_url']."/images/upload/user_works/".$uploadresult2['save_filename'];
        $rs=$this->works_obj->createWorks($search);
        if($rs){
          show_message ( '恭喜您，上传成功，请继续上传！' );
          goback(3000);
		 // redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
        }else{
          show_message ( '上传失败！再来一次' );
          goback(3000);
		 // redirect ($gSiteInfo['www_site_url'] . "/index.php?action=works&view=uploadws&isread=1");
        }
	}

}

?>