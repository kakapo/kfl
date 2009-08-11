<?php
class UserModel extends Model
{
	private $account_db;

	function __construct( $user_db_key )
	{
		if ( isset( $GLOBALS [ 'gDataBase' ][ $user_db_key ] ) )
		{
			$this->account_db = parent::dbConnect( $GLOBALS[ 'gDataBase' ][ $user_db_key ] );
		}
		else
		{
			return "database {$user_db_key} not defined!";
		}
	}

	//获取列表
	private function _getList( $select , $pageCount = -1 , $pagination = false )
	{
		//是否获得分页对象
		if ($pagination !== false) {
			include_once('HTML_Pager.class.php');
			$total = $select->count (); //获得查询到的记录数
			$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
			$offset = $list ['page']->offset (); //获得记录偏移量
			$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'gray4_12 none', 'numBarMain' => 'gray4_12 none' );                      //翻页条的样式
			$list ['page']->setLinkStyle ( $pagerStyle );
			$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '' );
			$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '上一页' );
			$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '6', '', '', '', '' );
			$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '下一页' );
			$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '' );
			$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
			$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		}
		$select->limit ( $offset, $pageCount );
		$rs = $select->query ();
		$list [ 'record' ] = array();
		if ( $rs )
		{
			foreach ( $rs as $key => $record )
			{
				$list[ 'record' ][] = $record;
			}
		}
		//echo $select->getSql();//显示运行的sql语句，调试使用
		return ( array ) $list;
	}

	//获取用户信息
	public function getUserById( $user_id )
	{
		$sql = "select * from user where user_id = '{$user_id}' and user_status > 0 and user_status < UNIX_TIMESTAMP()";
		return $this->account_db->getRow ( $sql );
	}

	//获取用户扩展信息
	public function getUserExt( $user_id )
	{
		$sql = "select * from user_extinfo where user_id ='{$user_id}'";
		return $this->account_db->getRow ( $sql );
	}

	//获取用户交友信息
	public function getUserPersonInfo( $user_id )
	{
		$sql = "select * from user_personinfo where user_id='{$user_id}'";
		return $this->account_db->getRow ( $sql );
	}
	public function getUserWidgetGender( $user_id ){
		$sql = "select user_sex from user_widget where user_id='{$user_id}'";
		return $this->account_db->getOne( $sql );
	}
	//获取用户好友信息
	public function getUserFriends( $user_id , $num )
	{
		$select = $this->account_db->select ();
		$select->from ( "user_extinfo ue , user_friend fr" , "*" );
		$select->where ( "ue.user_id = fr.friend_id and fr.user_id = '{$user_id}'" );
		return $this->_getList ( $select, $num, true );
	}

	//更改用户密码
	public function updatePassByUsername( $userName , $newPass )
	{
		$sql = "update user set user_passwd = '" . md5 ( $newPass ) . "' where user_name = '{$userName}'";
		return $this->account_db->execute ( $sql );
	}

	//获得用户的投票信息
	public function getUserVote($user_id) {

		if (empty ( $user_id )) {
			return "";
		}
		$re = $this->account_db->getRow ( "select  user_vote_total,  user_vote_num  from  user_extinfo where  user_id=$user_id" );
		if (! empty ( $re ['user_vote_num'] )) {
			$average = round ( $re ['user_vote_total'] / $re ['user_vote_num'], 0 ); //平均分数=评分总数/评分人数
		} else {

			return "00";
		}
		return array ($average, $re ['user_vote_num'] );

	}

	//得到用户上传头像路径
	public function getUserPicPath($user_id) {
		$sql = "select user_pic,user_pic_ext,user_host,user_path,user_store from user_extinfo where user_id = '{$user_id}'";
		return $this->account_db->getRow ( $sql );
	}

	//保存用户上传头像
	public function saveUserPic($userpicinfo) {
		$sql = "update user_extinfo set user_pic=?,user_pic_ext=? where user_id=?";
		return $this->account_db->execute ( $sql, $userpicinfo );
	}

	//获取用户数据（VIP属性，果果，G币，头像，rss,绑定空间）
	public function getUserBlog( $user_id )
	{
		$sql = "select ue.user_vip,ue.user_coin,ue.user_cash,ue.user_pic,ue.user_icon,up.user_otherspace,up.user_rss_url from user_extinfo as ue,user_personinfo as up where ue.user_id = up.user_id and ue.user_id = '{$user_id}'";
		return $this->account_db->getRow ( $sql );
	}

	//保存用户绑定空间地址
	public function saveUserblog($data) {
		$sql = "update user_personinfo set user_otherspace=?,user_rss_url=? where user_id=?";
		return $this->account_db->execute ( $sql, $data );
	}

	//更新用户最后登录时间
	public function updateUserExtInfo($user) {
		$ip = getip();
		return $this->account_db->execute ( "update user_extinfo set user_rank=?,user_last_logtime=UNIX_TIMESTAMP(),user_last_logip=? where user_id=?", array ($user ['user_rank'], $ip, $user ['user_id'] ) );
	}

	//获取用户图片，路径
	public function getUserHostPath( $user_id )
	{
		return $this->account_db->getRow ( "select user_host,user_path,user_pic,user_pic_ext from user_extinfo where user_id = '{$user_id}'" );
	}

	//更新用户票数
	public function updateVote($user_id, $value) {
		$qry = "update user_extinfo set user_vote_total=user_vote_total+" . $value . ", user_vote_num=user_vote_num+1 where user_id='" . $user_id . "'";
		return $this->account_db->exec ( $qry );
	}

	//收藏用户
	public function addFrinend( $user_id , $friend_name )
	{
		$sql_get_user = "select us.user_id,us.user_name,us.user_nickname,ue.user_gender from user us,user_extinfo ue where us.user_id = ue.user_id and us.user_name = '{$friend_name}'";
		$user = $this->account_db->getRow ( $sql_get_user );

		$sql_get_count = "select count(*) from user_friend where user_id = '{$user_id}' and friend_id = '{$user['user_id']}'";
		$count = $this->account_db->getOne ( $sql_get_count );

		if ($count > 0)
		{
			return 1;
		}
		else
		{
			$sql_insert_friend = "insert into user_friend (user_id,friend_id,friend_name,friend_nickname,friend_gender) values ('{$user_id}','{$user['user_id']}','{$user['user_name']}','{$user['user_nickname']}','{$user['user_gender']}')";
			if ( $this->account_db->query ( $sql_insert_friend ) )
			{
				return 2;
			}
			else
			{
				return 3;
			}
		}
	}

	//删除好友
	public function delFriend( $user_id , $f_id )
	{
		return $this->account_db->query( "delete from user_friend where user_id = '{$user_id}' and friend_id = '{$f_id}'" );
	}

	public function getSubjectList($pageCount = -1 , $pagination = false)
	{
		$main= parent::dbConnect();
		$select = $main->select ();
		$select->from ("subject" , "*" );
		$select->order("times desc");
		return $this->_getList ( $select, $pageCount, true );
	}
	//读取榜样信息
	public function getExample($user_gender)
	{

		$main= parent::dbConnect();
		$sql = "select * from guodong_example where user_gender = '{$user_gender}' and user_state = '2' limit 8";
		//return $main->getAll($sql);
		$main->setCacheDir(getcwd().'/tmp/database');
		return $main->cacheGetAll($sql,-1,'user_example_'.$user_gender);
	}

	//检查用户是否添加过榜样
	public function checkExample($user_id)
	{
		$main= parent::dbConnect();
		return $main->getOne("select count(user_id) from guodong_example where user_id = '{$user_id}'");
	}

	//添加用户榜样
	public function addExample($user)
	{
		$main= parent::dbConnect();
		$sql = "insert into guodong_example(user_id,user_name,user_nickname,user_gender,user_blog_path,user_time) values (?,?,?,?,?,UNIX_TIMESTAMP())";
		return $main->execute($sql,array($user['user_id'],$user['user_name'],$user['user_nickname'],$user['user_gender'],$user['user_blog_path']));
	}

	//添加消息（测试）
	public function add()
	{
		for ( $i = 2 ; $i < 22 ; $i++)
		{
			$sql = "insert into user_msg(user_id,msg_time,msg_content,msg_title) values ('8',UNIX_TIMESTAMP(),'$i * 2 = ". $i*2 ."','$i * 2')";
			$this->account_db->query($sql);
		}
	}

	//获取用户消息信息
	public function getUserMessage( $type , $user_id , $num = 10 )
	{
		$select = $this->account_db->select ();
		$select->from ( "user_msg" , "*" );
		$select->where( "user_id = '{$user_id}'" );
		if ( $type == 'read' )
		{
			$select->where("msg_isread = 1");
		}
		if ( $type == 'noread' )
		{
			$select->where("msg_isread = 0");
		}
		return $this->_getList ( $select, $num, true );
	}

	//更新消息状态
	public function updateMessage( $messageId )
	{
		$sql = "update user_msg set msg_isread = '1' where msg_id = '{$messageId}'";
		return $this->account_db->query( $sql );
	}

	//删除消息
	public function delMessage( $messageId )
	{
		$sql = "delete from user_msg where msg_id in ($messageId)";
		return $this->account_db->query( $sql );
	}

	//取得用户自定义数据
	public function getUserDiy( $userId )
	{
		return $this->account_db->getRow( "select * from user_diy where user_id = '{$userId}'" );
	}

	//更新(添加)自定义音乐
	public function updateMusic( $userId , $musicUrl )
	{
		//查看用户自定义记录是否存在
		$count = $this->account_db->getOne("select count(*) from user_diy where user_id = '{$userId}'");
		if ( $count )
		{
			//更新音乐地址
			$sql = "update user_diy set user_music_url = '{$musicUrl}' where user_id = '{$userId}'";
			return $this->account_db->query( $sql );
		}
		else
		{
			//添加音乐地址
			$sql = "insert into user_diy(user_id,user_music_url) values ('{$userId}','{$musicUrl}')";
			return $this->account_db->query( $sql );
		}
	}

	//更新(添加)自定义图片
	public function addDiyPic( $userId , $pic )
	{
		//查看用户自定义记录是否存在
		$count = $this->account_db->getOne("select count(*) from user_diy where user_id = '{$userId}'");

		if ( $count )
		{
			//用户自定义图片
			$userPic = $this->account_db->getOne( "select user_image_json from user_diy where user_id = '{$userId}'" );
				
			$tmpPic = '';
			//拼装用户自定义图片，生成JSON
			if ( $userPic != 'null' && !empty($userPic) )
			{

				$tmpPic = json_encode( array_merge( json_decode( $userPic , true ) , $pic ) );
			}
			else
			{
				$tmpPic = json_encode( $pic );
			}

			return $this->account_db->query( "update user_diy set user_image_json = '{$tmpPic}' where user_id = '{$userId}'" );
		}
		else
		{
			$userPic = json_encode( $pic );
			return $this->account_db->query( "insert into user_diy(user_id,user_image_json) values ('{$userId}','{$userPic}')" );
		}
	}

	//更新自定义图片
	public function updateDiyPic( $userId , $pic )
	{
		return $this->account_db->query( "update user_diy set user_image_json = '{$pic}' where user_id = '{$userId}'" );
	}

	//更新(添加)自定义文字
	public function addDiyTxt( $userId , $txt )
	{
		//查看用户自定义记录是否存在
		$count = $this->account_db->getOne("select count(*) from user_diy where user_id = '{$userId}'");
		
		if ( $count )
		{
			//用户自定义文字
			$userTxt = $this->account_db->getOne( "select user_word_json from user_diy where user_id = '{$userId}'" );

			//拼装用户自定义文字，生成JSON
			if ( $userTxt != 'null' && !empty( $userTxt ) )
			{
				$tmpTxt = array_merge( json_decode( $userTxt , true ) , $txt );
			}
			else
			{
				$tmpTxt = $txt;
			}

			$c = count($tmpTxt);
			$tmpTxt = json_encode($tmpTxt);
			$res = $this->account_db->query( "update user_diy set user_word_json = '".addslashes($tmpTxt)."' where user_id = '{$userId}'" );
			if($res)
			return $c;
			else
			return false;
		}
		else
		{
			$c = count($txt);
			$userTxt = addslashes(json_encode( $txt ));
			$res = $this->account_db->query( "insert into user_diy(user_id,user_word_json) values ('{$userId}','{$userTxt}')" );
			if($res)
			return $c;
			else
			return false;
		}
	}

	//更新自定义文字
	public function updateDiyTxt( $userId , $txt )
	{
		return $this->account_db->query( "update user_diy set user_word_json = '".addslashes($txt)."' where user_id = '{$userId}'" );
	}


	/**
	 * ***商城 保存用户财产，扣钱等操作API 开始 ****
	 */
	public function buyCart($cart,$userinfo,$total_gg,$total_gb){
		$this->account_db->beginTransaction();

		$stmt = $this->account_db->prepare("insert into user_wealth (wealth_item_id,wealth_item_name,wealth_item_title,wealth_item_cateid,wealth_item_gender,wealth_from,wealth_price_type,wealth_price,wealth_time,wealth_expiry_time,user_id) values (?,?,?,?,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL ? MONTH)),?)");
		$stmt->bindParam(1, $wealth_item_id);
		$stmt->bindParam(2, $wealth_item_name);
		$stmt->bindParam(3, $wealth_item_title);
		$stmt->bindParam(4, $wealth_item_cateid);
		$stmt->bindParam(5, $wealth_item_gender);
		$stmt->bindParam(6, $wealth_from);
		$stmt->bindParam(7, $wealth_price_type);
		$stmt->bindParam(8, $wealth_price);
		$stmt->bindParam(9, $wealth_expiry_time);
		$stmt->bindParam(10, $user_id);

		$stmt2 = $this->account_db->prepare("insert into user_pay_log (user_id,pay_time,item_name,item_title,pay_type,pay_value,pay_ym,item_cate) values (?,NOW(),?,?,?,?,?,?)");

		$stmt2->bindParam(1, $user_id);
		$stmt2->bindParam(2, $item_name);
		$stmt2->bindParam(3, $item_title);
		$stmt2->bindParam(4, $pay_type);
		$stmt2->bindParam(5, $pay_value);
		$stmt2->bindParam(6, $pay_ym);
		$stmt2->bindParam(7, $item_cate);

		foreach ($cart as $item_id=>$row){
			$wealth_item_id = $item_id;
			$wealth_item_name = $row['iteminfo']['item_name'];
			$wealth_item_title = $row['iteminfo']['item_title'];
			$wealth_item_cateid = $row['iteminfo']['cate_id'];
			$wealth_item_gender = $row['iteminfo']['item_gender'];
			$wealth_from = $userinfo['user_id'];
			$wealth_price_type = $row['iteminfo']['item_price_type'];
			$wealth_price = $row['iteminfo']['item_price']*$row['usetime'];
			$wealth_expiry_time = $row['usetime'];

			$user_id = $userinfo['user_id'];
			if(!$stmt->execute()){
				$arr = $stmt->errorInfo();
				return false;
				//print_r($arr);
			}

			$user_id= $userinfo['user_id'];
			$item_name =  $row['iteminfo']['item_name'];
			$item_title =  $row['iteminfo']['item_title'];
			$pay_type =  $row['iteminfo']['item_price_type'];
			$pay_value = $wealth_price;
			$pay_ym =  date('Ym');
			$item_cate = $row['iteminfo']['cate_title'];
			if(!$stmt2->execute()){
				$arr = $stmt->errorInfo();
				return false;
			}

		}
		$this->account_db->execute("update user_extinfo set user_coin=user_coin-?,user_cash=user_cash-? where user_id=?",array($total_gg,$total_gb,$userinfo['user_id']));

		if ($this->account_db->commit ()) {
			
			return true;
		} else {
			$this->account_db->rollBack ();
			return false;
		}
	}
	public function getUserWealth($con,$pageCount){
		$select = $this->account_db->select();
		$select->from ( " user_wealth " ,"*");
		if(isset($con['user_id'])) $select->where ( " user_id='{$con['user_id']}'" );
		if(isset($con['user_gender'])) $select->where ( " (wealth_item_gender='{$con['user_gender']}' or wealth_item_gender=3)" );
		if(isset($con['order'])) $select->order ( $con['order']." desc" );
		return $this->getList ( $select, $pageCount, true );

	}
	public function getWealthById($wealth_id){
		return $this->account_db->getRow("select * from user_wealth where wealth_id='{$wealth_id}'");
	}

 

	public function reBuy($item,$userinfo){
		$this->account_db->beginTransaction();

		//写入支付纪录
		$stmt2 = $this->account_db->prepare("insert into user_pay_log (user_id,pay_time,item_name,item_title,pay_type,pay_value,pay_ym,item_cate) values (?,NOW(),?,?,?,?,?,?)");

		$stmt2->bindParam(1, $user_id);
		$stmt2->bindParam(2, $item_name);
		$stmt2->bindParam(3, $item_title);
		$stmt2->bindParam(4, $pay_type);
		$stmt2->bindParam(5, $pay_value);
		$stmt2->bindParam(6, $pay_ym);
		$stmt2->bindParam(7, $item_cate);

		$user_id= $userinfo['user_id'];
		$item_name =  $item['item_name'];
		$item_title = $item['item_title'];
		$pay_type =  $item['item_price_type'];
		$pay_value = $item['item_price']*$item['usetime'];
		$pay_ym =  date('Ym');
		$item_cate = $item['cate_title'];
		$stmt2->execute();

		//更新财产过期时间
		$this->account_db->execute("update user_wealth set wealth_expiry_time= UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(wealth_expiry_time), INTERVAL ? MONTH)) where wealth_id=?",array($item['usetime'],$item['wealth_id']));

		//扣钱
		$total_gg =0;
		$total_gb =0;
		if($item['item_price_type']=='GG') $total_gg = $pay_value;
		if($item['item_price_type']=='GB') $total_gb = $pay_value;
		$this->account_db->execute("update user_extinfo set user_coin=user_coin-?,user_cash=user_cash-? where user_id=?",array($total_gg,$total_gb,$userinfo['user_id']));

		if ($this->account_db->commit ()) {
			return true;
		} else {
			$this->account_db->rollBack ();
			return false;
		}
	}
	private function setListStyle($total,$pageCount)
	{
		include_once("HTML_Pager.class.php");
		$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
		$offset = $list ['page']->offset ();                    //获得记录偏移量
		$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'gray4_12 none', 'numBarMain' => 'gray4_12 none' );                      //翻页条的样式
		$list ['page']->setLinkStyle ( $pagerStyle );
		$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '' );
		$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '上一页' );
		$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '7', '', '', '', '' );
		$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '下一页' );
		$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '' );
		$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
		$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		return (array)$list;
	}
	private function getList($select, $pageCount = -1, $pagination = false) {

		$list = array();
		//是否获得分页对象
		$offset = '';
		if ($pagination !== false) {
			$total = $select->count (); //获得查询到的记录数
			$list=$this->setListStyle($total,$pageCount);
		}
		$select->limit ( $list['page']->offset(), $pageCount );
		$rs = $select->query();
		if ($rs) {
			foreach ( $rs as $key => $record ) {
				$list ['records'] [$key] = $record;
			}
		}
		//$this->show_db->showDebug();  //显示运行的sql语句，调试使用
		return ( array ) $list;
	}

	//查询用户VIP属性
	public function getUserVip( $userId )
	{
		return $this->account_db->getOne("select user_vip from user_extinfo where user_id = '{$userId}'");
	}

	//用户加入VIP操作
	public function joinVip( $userId , $vipCash )
	{
		//加入VIP
		$sql = "update user_extinfo set
					user_cash = user_cash - {$vipCash},user_vip = 1,user_vip_time = UNIX_TIMESTAMP()
					where user_id = '{$userId}'";
		if ( $this->account_db->query( $sql ) )
		{
			//发送系统消息
			$content = '您消耗了'.$vipCash.'G币，加入了VIP，现在您可以免费使用所有道具，<a href="/index.php/show/frameset/clothes">马上就去吧!</a>';
			$title = '加入VIP成功!';
			$msg = "insert into user_msg(user_id,msg_time,msg_content,msg_title)
						values ('{$userId}',UNIX_TIMESTAMP(),'{$content}','{$title}')";
			if ( $this->account_db->query($msg) )
			{
				return true;
			}
			else
			{
				//数据回滚
				$rollback_sql = "update user_extinfo set
									user_cash = user_cash + {$vipCash},user_vip = 0,user_vip_time = 0
									where user_id = '{$userId}'";
				$this->account_db->query( $rollback_sql );
				return false;
			}
		}
	}

	public function getUserMsgNum($userId){

		return $this->account_db->getOne("select count(*) from user_msg where user_id='{$userId}' and msg_isread=0");
	}
	public function addMsg($data){
		$sql = "insert into user_msg (user_id,msg_time,msg_content,msg_title) values (?,UNIX_TIMESTAMP(),?,?)";
		return $this->account_db->execute($sql,array($data['user_id'],$data['content'],$data['title']));
	}
	
	//得到用户购买道具ID
	public function getUserItemWealthId( $userId , $itemId )
	{
		$sql = "select wealth_id from user_wealth where wealth_item_id = '{$itemId}' and user_id = '{$userId}'";
		return $this->account_db->getOne($sql);
	}
}
?>