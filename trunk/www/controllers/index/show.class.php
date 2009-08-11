<?php
class show {
	public $tpl;
	public $gender;
	function __construct() {
		global $showConfig;
		$this->tpl = $GLOBALS ['tpl'];
		$user = authenticate ();

		if ($user == false) {
			$this->userInfo ['is_login'] = 0;
			$this->tpl->assign ( 'is_login', 0 );
			$this->tpl->assign ( 'user_vip', 0 );
			$this->tpl->assign ( 'user_name', '' );
			$this->tpl->assign ( 'user_cash', 0 );
			$this->tpl->assign ( 'user_coin', 0 );
		} else {
			$this->userInfo ['is_login'] = 1;
			$this->userInfo ['user_nickname'] = $user [2];
			$this->userInfo ['user_id'] = $user [1];
			$this->userInfo ['user_name'] = $user [0];
			$this->userInfo ['user_gender'] = $user [6];
			$this->userInfo ['user_db_key'] = $user [12];
			$this->userInfo ['user_vip'] = $user [14];
			$this->userInfo ['user_cash'] = isset($_COOKIE['IDOL_CASH_'.$user [0]])?$_COOKIE['IDOL_CASH_'.$user [0]]:0;
			$this->userInfo ['user_coin'] = isset($_COOKIE['IDOL_COIN_'.$user [0]])?$_COOKIE['IDOL_COIN_'.$user [0]]:0;

			$this->tpl->assign ( 'is_login', $this->userInfo ['is_login'] );
			$this->tpl->assign ( 'user_nickname', $this->userInfo ['user_nickname'] );
			$this->tpl->assign ( 'user_name', $this->userInfo ['user_name'] );
			$this->tpl->assign ( 'user_vip', $this->userInfo ['user_vip'] );
			$this->tpl->assign ( 'user_cash', $this->userInfo ['user_cash'] );
			$this->tpl->assign ( 'user_coin', $this->userInfo ['user_coin'] );
			$this->tpl->assign ( 'showConfig', $showConfig);
		}

		//原始的
		 if(isset($_COOKIE['VIEW_GENDER'])){
			if($_COOKIE['VIEW_GENDER']=='boy') $this->gender =1;
			if($_COOKIE['VIEW_GENDER']=='girl') $this->gender =2;
		}else{
			$this->gender =1;
		}
			
		$this->tpl->assign ( "title", "商城-果动网-果然会动-网页3D娱乐" );
	}
	function view_frameset(){
		$main_frame = !empty($_GET['frameset'])?$_GET['frameset']:'mainfrm';
		$this->tpl->assign("content",$main_frame);
	}
	function view_topfrm(){
		$cur = !empty($_GET['topfrm'])?$_GET['topfrm']:'mainfrm';
		$this->tpl->assign("cur",$cur);
	}
	function view_playfrm(){

	}
	function view_mainfrm(){

		if(isset($_GET['userid'])){
			$username=$_GET['userid'];
			setcookie ('ineedtoo', $username, time () + 3600 * 24, '/', COOKIE_DOMAIN );
			include_once("UserModel.class.php");
			include_once("ApiUser.class.php");
			$user_arr=ApiUser::getUserByName($username);
			$_userObj = new UserModel($user_arr['user_db_key']);
			$user_sex = $_userObj->getUserWidgetGender($user_arr['user_id']); 
			if(empty($user_sex)){
				$user=$_userObj->getUserExt($user_arr['user_id']);
				$user_sex=$user['user_gender']; 
			}
			
			if($user_sex=='1'){
				setcookie ('VIEW_GENDER','boy',0,'/',COOKIE_DOMAIN);
			}else{
				setcookie ('VIEW_GENDER','girl',0,'/',COOKIE_DOMAIN);
			}
			//print_r($_GET);die;
		}

		include_once("ShowItem.class.php");
		$item = new ShowItem();
		$con['gender'] = $this->gender;
		$con['vip'] = 0;
		$data = $item->getRecomendIndex($con,12);

		$con2['order'] = 'item_sort';
		$con2['gender'] = $this->gender;
		$con2['vip'] = 0;
		$con2['item_recommend'] = 1;
		$items = $item->getItems($con2,16);

		$this->tpl->assign('recommenditems',$data);
		$this->tpl->assign('items',$items);
		$this->tpl->assign('con',$con);
	}
	//推荐搭配
	function view_recommend(){
		$cur_sort = !empty($_GET['sort'])?$_GET['sort']:'id';
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		$con['order'] = $cur_sort;
		$con['gender'] = $this->gender;
		$con['vip'] = 0;
		$data = $item->getRecomendDiy($con,12);
		//print_sql();
		$this->tpl->assign('cur_sort',$cur_sort);
		$this->tpl->assign('items',$data);
		$this->tpl->assign('con',$con);
	}


	//装扮
	function view_clothes(){
		$keyword = $_GET['view'];
		$cur_cate_id = !empty($_GET['cate_id'])?$_GET['cate_id']:'0';
		$cur_sort = !empty($_GET['sort'])?$_GET['sort']:'item_sort';
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		//取分类
		$cate_id = $item->getCateByKeyword($keyword);
		$cates = $item->getSubCates($cate_id);
		foreach ($cates as $cate){
			$sub_cate_id_arr[] = $cate['cate_id'];
		}
		//取道具
		if(!$cur_cate_id) {
			$con['cate_id'] = $sub_cate_id_arr;
		}else{
			//取道具
			$con['cate_id'] = $cur_cate_id;
		}
		$con['order'] = $cur_sort;
		$con['gender'] = $this->gender;
		$con['vip'] = 0;
		$items = $item->getItems($con,16);



		//print_r($items);
		// 推荐
		$con2['order'] = 'id';
		$con2['gender'] = $this->gender;
		$con2['pos'] = 'FashionRight';
		$con2['vip'] = '0';
		$data = $item->getRecomendDiy($con2,3,false);
		$this->tpl->assign('recItems',$data);

		$this->tpl->assign('cur_cate_id',$cur_cate_id);
		$this->tpl->assign('cur_sort',$cur_sort);
		$this->tpl->assign('cates',$cates);
		$this->tpl->assign('items',$items);
		$this->tpl->assign('con',$con);
	}
	//动作
	function view_motion(){
		$keyword = $_GET['view'];
		$cur_sort = !empty($_GET['sort'])?$_GET['sort']:'item_sort';
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		//取分类
		$cate_id = $item->getCateByKeyword($keyword);
		//取道具
		$con['cate_id'] = $cate_id;
		$con['order'] = $cur_sort;
		$con['gender'] = $this->gender;
		$con['vip'] = 0;
		$items = $item->getItems($con,16);
		//print_sql();
		//print_r($items);
		// 推荐
		$con2['order'] = 'id';
		$con2['gender'] = $this->gender;
		$con2['pos'] = 'ActionRight';
		$con2['vip'] = '0';
		$data = $item->getRecomendDiy($con2,3,false);
		$this->tpl->assign('recItems',$data);

		$this->tpl->assign('cur_sort',$cur_sort);
		$this->tpl->assign('items',$items);
		$this->tpl->assign('con',$con);
	}
	//背景
	function view_background(){
		$keyword = $_GET['view'];

		$cur_cate_id = !empty($_GET['cate_id'])?intval($_GET['cate_id']):'';
		$cur_sort = !empty($_GET['sort'])?$_GET['sort']:'item_sort';
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		//取分类
		$cate_id = $item->getCateByKeyword($keyword);
		$cates = $item->getSubCates($cate_id);
		foreach ($cates as $cate){
			$sub_cate_id_arr[] = $cate['cate_id'];
		}
		//取道具
		if(!$cur_cate_id) {
			$con['cate_id'] = $sub_cate_id_arr;
		}else{
			//取道具
			$con['cate_id'] = $cur_cate_id;
		}
		$con['order'] = $cur_sort;
		$con['vip'] = 0;
		//$con['gender'] = $this->gender;
		$items = $item->getItems($con,16);

		$con2['order'] = 'id';
		$con2['gender'] = $this->gender;
		$con2['pos'] = 'SceneRight';
		$con2['vip'] = '0';
		$data = $item->getRecomendDiy($con2,3,false);
		$this->tpl->assign('recItems',$data);

		$this->tpl->assign('cur_cate_id',$cur_cate_id);
		$this->tpl->assign('cur_sort',$cur_sort);
		$this->tpl->assign('cates',$cates);
		$this->tpl->assign('items',$items);
		$this->tpl->assign('con',$con);
	}

	//音乐
	function view_music()
	{
		//判断用户是否登录
		//$this->checkIsLogin();
		if($this->userInfo ['is_login'])
		{
			include_once( "UserModel.class.php" );
			$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

			$userDiy = $_userLObj->getUserDiy( $this->userInfo ['user_id'] );

			$this->tpl->assign( 'userMusic' , $userDiy['user_music_url'] );
			$this->tpl->assign( 'userVip' , $this->userInfo ['user_vip']);
		}
		else
		{
			$this->tpl->assign( 'userMusic' , '' );
			$this->tpl->assign( 'userVip' , '');
		}
	}

	//更新音乐地址
	function op_updatemusic()
	{
		//判断用户是否登录
		$this->checkIsLogin();

		//音乐链接地址
		$musicUrl = isset( $_POST[ 'music_url' ] ) ? $_POST[ 'music_url' ] : '';

		$pattern = '/^http:\/\/(.*?)$/';

		$type = strtolower( substr( $musicUrl , -4 ) );

		if ( !$this->userInfo ['user_vip'] )
		{
			die ( "你还不是VIP会员，<a href='javascript:join_vip({$this->userInfo ['user_coin']},{$this->userInfo ['user_cash']});' class='blue_by12'>现在就加入</a>" );
		}
		elseif ( empty( $musicUrl ) )
		{
			die ( "请填写音乐链接地址!" );
		}
		elseif ( !preg_match( $pattern , $musicUrl ) )
		{
			die ( "音乐链接地址格式错误!" );
		}
		elseif ( $type != '.mp3' )
		{
			die ( "音乐链接地址后缀错误!" );
		}

		include_once( "UserModel.class.php" );
		$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

		if ( $_userLObj->updateMusic( $this->userInfo ['user_id'] , $musicUrl ) )
		{
			die('1');
		}
		else
		{
			die('2');
		}
	}

	//图片DIY
	function view_diy()
	{
		//判断用户是否登录
		//$this->checkIsLogin();
		if ( !$this->userInfo ['is_login'] )
		{
			$this->tpl->assign( 'pic' , '' );
			$this->tpl->assign( 'picCount' , 0 );
			$this->tpl->assign( 'uploadImageTotal' , $GLOBALS['uploadImageTotal'] );
		}
		else
		{
			include_once( "UserModel.class.php" );
			$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

			$picCount = 0;
			$userDiy = $_userLObj->getUserDiy( $this->userInfo ['user_id'] );
			if ( !empty( $userDiy['user_image_json'] ) )
			{
				$userDiy['user_image_json'] = json_decode( $userDiy['user_image_json'] , true );
				$picCount = count( $userDiy['user_image_json'] );
			}
			$this->tpl->assign( 'pic' , $userDiy['user_image_json'] );
			$this->tpl->assign( 'picCount' , $picCount );
			$this->tpl->assign( 'uploadImageTotal' , $GLOBALS['uploadImageTotal'] );
		}
	}

	//添加图片
	function op_addpic()
	{
		global $fdfs_storage_settings;
		//判断用户是否登录
		$this->checkIsLogin();

		//图片文件
		$file = isset( $_FILES[ 'pic_upload' ] ) ? $_FILES[ 'pic_upload' ] : '';

		if ( empty( $file[ 'name' ] ) )
		{
			show_message_goback( '请选择图片！' );
		}
		list($width, $height, $type, $attr) = getimagesize($file[ 'tmp_name']);

		if(!in_array($type,array(1,2,3))){
			show_message_goback(  '上传图片只支持jpg,gif,png！' );
		}

		if($file['size']>1024*1024){
			show_message_goback(  '上传图片大小不能超过1MB！' );
		}
		$ext = 'jpg';
		if($type==1)$ext = 'gif';
		if($type==2)$ext = 'jpg';
		if($type==3)$ext = 'png';
		
		//上传图片
		require("FastDFS.class.php");
		$tmpUrl = FastDFS::factory()->upByBuff( file_get_contents( $file[ 'tmp_name' ] ), $ext);

		//判断图片是否上传成功
		if ( !$tmpUrl )
		{
			show_message_goback ( '图片传输失败，请重新上传!' );
		}
		$arr = explode("/",$tmpUrl);
		$group = array_shift($arr);
		array_shift($arr);
		$imgUrl = join( "/", $arr);
		$host = $fdfs_storage_settings[$group];
		$showImageUrl = $host ."/". $imgUrl;

		//添加main下自定义图片数据
		include_once( "ShowItem.class.php" );
		$_showObj = new ShowItem();
		
		$imgId = $_showObj->addDiyImage( $this->userInfo ['user_id'] , $this->userInfo ['user_nickname'] , $showImageUrl );

		//判断数据是否添加成功
		if ( $imgId == false )
		{
			//删除图片
			FastDFS::factory()->setgroup();
			FastDFS::factory()->delFile( $tmpUrl );
			show_message_goback ( '图片保存失败，请重新上传!' );
		}

		//图片数组
		$img[$imgId][ 'picId' ] = $imgId;
		$img[$imgId][ 'picUrl' ] = $imgUrl;
		$img[$imgId][ 'tmpPicUrl' ] = $tmpUrl;

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->userInfo['user_db_key'] );
		if ( $_userObj->addDiyPic( $this->userInfo ['user_id'] , $img ) )
		{
			show_message_goback ( '上传图片成功!' );
		}
		else
		{
			$_showObj->delDiyImage( $imgId );
			show_message_goback ( '图片保存失败2，请重新上传!' );
		}
	}

	//删除自定义图片
	function op_delpic()
	{
		$key = isset( $_POST[ 'key' ] ) ? $_POST[ 'key' ] : '';
		$imgId = isset( $_POST[ 'img_id' ] ) ? $_POST[ 'img_id' ] : '';
		$imgUrl = isset( $_POST[ 'img_url' ] ) ? $_POST[ 'img_url' ] : '';

		include_once( "ShowItem.class.php" );
		$_showObj = new ShowItem();
		include_once( "UserModel.class.php" );
		$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

		//用户自定义信息
		$userDiy = $_userLObj->getUserDiy( $this->userInfo ['user_id'] );

		//删除json数据
		if ( !empty( $userDiy['user_image_json'] ) )
		{
			$userDiy['user_image_json'] = json_decode( $userDiy['user_image_json'] , true );
			unset( $userDiy['user_image_json'][ $key ] );
		}

		$pic = !empty( $userDiy['user_image_json'] ) ? json_encode( $userDiy['user_image_json'] ) : 'null';

		//更新用户自定义图片json
		if ( $_userLObj->updateDiyPic( $this->userInfo ['user_id'] , $pic ) )
		{
			//删除main中自定义图片记录
			if ( $_showObj->delDiyImage( $imgId ) )
			{
				//删除图片
				require("FastDFS.class.php");
				FastDFS::factory()->delFile( $imgUrl );
				die ( '删除自定义图片成功!' );
			}
			else
			{
				die ( '删除自定义图片失败!' );
			}
		}
		else
		{
			die ( '删除自定义图片失败!' );
		}
	}

	//文字DIY
	function view_txtdiy()
	{
		//判断用户是否登录
		//$this->checkIsLogin();
		if ( !$this->userInfo ['is_login'] )
		{
			$this->tpl->assign( 'txt' , '' );
			$this->tpl->assign( 'txtCount' , 0 );
		}
		else
		{
			include_once( "UserModel.class.php" );
			$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

			$userDiy = $_userLObj->getUserDiy( $this->userInfo ['user_id'] );
			if ( !empty( $userDiy['user_word_json'] ) )
			{
				$userDiy['user_word_json'] = json_decode( $userDiy['user_word_json'] , true );
			}

			$this->tpl->assign( 'txt' , $userDiy['user_word_json'] );
			$this->tpl->assign( 'txtCount' , count( $userDiy['user_word_json'] ) );
		}
	}

	//添加文字
	function op_addtxt()
	{
		//判断用户是否登录
		$this->checkIsLogin();
		
		//文字内容
		//替换特殊符号
		$tmpTxt = isset( $_POST[ 'textarea' ] ) ? $_POST[ 'textarea' ] : '';
		$tmpTxt = strip_tags($tmpTxt,"<b><u><i>");
		$tmpTxt = str_replace("\\","",$tmpTxt);
		$tmpTxt = str_replace("\n","",$tmpTxt);
		$tmpTxt = str_replace("'","’",$tmpTxt);
		$tmpTxt = str_replace("\"","”",$tmpTxt);
		
		//禁词表替换
		foreach ( $GLOBALS['blockword'] as $key => $value )
		{
			$tmpTxt = str_replace( $value , '*' , $tmpTxt );
		}
		
		$txt[] = $tmpTxt;

		if ( empty( $txt ) )
		{
			die ( "0|请输入文字!" );
		}

		include_once( "UserModel.class.php" );
		$_userObj = new UserModel( $this->userInfo['user_db_key'] );

		//取得用户DIY数据
		$userDiy = $_userObj->getUserDiy( $this->userInfo ['user_id'] );
		$userTxt = json_decode( $userDiy[ 'user_word_json' ] , true);

		//VIP可添加5条
		if ( $this->userInfo ['user_vip'] == '1' )
		{
			if ( count( $userTxt ) >= 5 )
			{
				die ( "0|你最多只能添加5条!" );
			}
		}
		//普通用户2条
		else
		{
			if ( count( $userTxt ) >= 2 )
			{
				die ( "0|你最多只能添加2条!" );
			}
		}


		$r = $_userObj->addDiyTxt( $this->userInfo ['user_id'] , $txt );
		if ( $r!=false )
		{
			echo $tmpTxt.'|添加自定义文字成功';die;
		}
		else
		{
			echo '0|添加自定义文字失败';die;
		}
	}

	//删除文字
	function op_deltxt()
	{
		//判断用户是否登录
		$this->checkIsLogin();

		$key = isset( $_POST[ 'id' ] ) ? $_POST[ 'id' ] : '';

		include_once( "UserModel.class.php" );
		$_userLObj = new UserModel( $this->userInfo['user_db_key'] );

		$userDiy = $_userLObj->getUserDiy( $this->userInfo ['user_id'] );

		if ( !empty( $userDiy['user_word_json'] ) )
		{
			$userDiy['user_word_json'] = json_decode( $userDiy['user_word_json'] , true );
			unset( $userDiy['user_word_json'][ $key ] );

			$txt = !empty( $userDiy['user_word_json'] ) ? json_encode( $userDiy['user_word_json'] ) : 'null';

			if ( $_userLObj->updateDiyTxt( $this->userInfo ['user_id'] , $txt ) )
			{
				echo $key.'|删除自定义文字成功';
			}
			else
			{
				echo $key.'|删除自定义文字失败';
			}
		}
		else
		{
			echo $key.'|删除自定义文字失败';
		}
	}

	//专题
	function view_subject(){
		include_once( "UserModel.class.php" );
		$mainobj = new UserModel('');
		$list = $mainobj->getSubjectList(15,true);
		$this->tpl->assign( 'list_array' ,$list );
	}

	//VIP
	function view_vip()
	{
		include_once("ShowItem.class.php");
		$item = new ShowItem();

		//推荐道具
		$con['gender'] = $this->gender;
		$con['vip'] = 1;
		$commendItems = $item->getRecomendDiy($con,4,false);

		//vip道具
		$con['item_type'] = '3D';
		$items = $item->getItems($con,5);

		$userVip = 0;
		if ( $this->userInfo ['is_login'] == 1 )
		{
			include_once( "UserModel.class.php" );
			$_userObj = new UserModel( $this->userInfo ['user_db_key'] );
			$userVip = $_userObj->getUserVip( $this->userInfo ['user_id'] );
		}
		//echo $userVip;
		$this->tpl->assign( 'commendItems' ,$commendItems );
		$this->tpl->assign( 'items' ,$items );
		$this->tpl->assign( 'user_vip', $userVip );
	}

	//加入VIP
	function op_joinvip()
	{
		//检查是否登录
		if ( !$this->userInfo['is_login'] )
		{
			die('1');
		}

		include_once("UserModel.class.php");
		$userModel = new UserModel($this->userInfo ['user_db_key']);
		$userInfo = $userModel->getUserExt( $this->userInfo ['user_id'] );

		//检查是否是VIP
		if ( $userInfo ['user_vip'] )
		{
			die('2');
		}

		//检查用户G币
		if ( $this->userInfo ['user_cash'] < $GLOBALS['vip_cash'] )
		{
			die('3');
		}

		if ( $userModel->joinVip( $this->userInfo ['user_id'] , $GLOBALS['vip_cash'] ) )
		{
			//设置cookie数据，G币
			setcookie ( 'IDOL_CASH_' . $this->userInfo ['user_name'], $this->userInfo ['user_cash']-$GLOBALS['vip_cash'], 0, '/', COOKIE_DOMAIN );
			//VIP
			setcookie ( 'IDOL_VIP_' . $this->userInfo ['user_name'], '1', 0, '/', COOKIE_DOMAIN );
			die('4');
		}
		else
		{
			die('5');
		}
	}

	function view_defaults() {



	}


	//我的物品
	function view_closet(){
		$cur_sort = !empty($_GET['sort'])?$_GET['sort']:'wealth_id';
		include_once("UserModel.class.php");
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		$userModel = new UserModel($this->userInfo ['user_db_key']);
		$con['user_id'] = $this->userInfo ['user_id'];
		$con['order'] = $cur_sort;
		$con['user_gender'] = $this->gender;
		$wealths = $userModel->getUserWealth($con,6);
		if(isset($wealths['records']) && is_array($wealths['records'])){
			foreach ($wealths['records'] as $k=>$v){
				$iteminfo =$item->getItemById($v['wealth_item_id']);
				$v['item_id'] = $v['wealth_item_id'];
				$v['item_2dfile'] = $iteminfo['item_2dfile'];
				$v['item_type'] = $iteminfo['item_type'];
				$wealths['records'][$k] = $v;
			}
		}
		$this->tpl->assign('con',$con);
		$this->tpl->assign('wealths',$wealths);
	}

	//判断用户是否登录
	function checkIsLogin()
	{
		if ( !$this->userInfo['is_login'] )
		{
			die("请您先登录!");
		}
	}

}
?>