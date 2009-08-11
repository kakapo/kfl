<?php
/**
 * @abstract 购物车类
 * @author zswu at
 *
 **/
class cart {
	private $tpl;
	function __construct(){
		global $tpl;
		$this->tpl = $tpl;
		$user = authenticate ();

		if ($user == false) {
			$this->userInfo ['is_login'] = 0;
			$this->tpl->assign ( 'is_login', 0 );
			$this->tpl->assign ( 'user_vip', 0 );
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
		}
	}
	function view_add(){

		$item_id_str  = !empty($_GET['item_id'])?$_GET['item_id']:'';
		$item_id_arr = explode(',',$item_id_str);
		foreach ($item_id_arr as $item_id){
			if(is_numeric($item_id) && $item_id>0){
				$_SESSION['cart'][$item_id]['usetime'] = 1;
			}
		}
		die;
	}
	function view_addall(){
		$aid  = !empty($_GET['aid'])?$_GET['aid']:'';
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		$arr = $item->getRecomendById($aid);
		if($arr){
			$item_id_arr = explode(",",$arr['item_id_list']);
			foreach ($item_id_arr as $item_id){
				if(is_numeric($item_id) && $item_id>0){
					$_SESSION['cart'][$item_id]['usetime'] = 1;
				}
			}
		}
		die;
	}
	function view_modify(){
		$item_id  = !empty($_GET['item_id'])?intval($_GET['item_id']):0;
		$usetime  = !empty($_GET['usetime'])?intval($_GET['usetime']):1;
		if($item_id>0){
			$_SESSION['cart'][$item_id]['usetime'] = $usetime;
		}
		die;
	}
	function view_delete(){
		$item_id  = !empty($_GET['item_id'])?intval($_GET['item_id']):0;
		if($item_id>0){
			unset($_SESSION['cart'][$item_id]);
		}
		die;
	}
	function view_defaults(){
		$_SESSION['cart'] = isset($_SESSION['cart'])?$_SESSION['cart']:'';
		$total_gg = 0;
		$total_gb = 0;
		if(is_array($_SESSION['cart'])){
			include_once("ShowItem.class.php");
			$item = new ShowItem();
			foreach ($_SESSION['cart'] as $item_id=>$row ){
				$iteminfo = $item->getItemById($item_id);
				$total_gg += ($iteminfo['item_price_type']=='GG')?$iteminfo['item_price']*$row['usetime']:0;
				$total_gb += ($iteminfo['item_price_type']=='GB')?$iteminfo['item_price']*$row['usetime']:0;
				$_SESSION['cart'][$item_id]['iteminfo']  = $iteminfo;
			}

		}
		$this->tpl->assign('title','购物车');
		$this->tpl->assign('total_gg',$total_gg);
		$this->tpl->assign('total_gb',$total_gb);
		$this->tpl->assign('cart',$_SESSION['cart']);
	}
	//保存购物车
	function op_buy(){
		$msg = '<meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS ['gSiteInfo'] ['webcharset'].'" />';
		$gb = !empty($_POST['gb'])?$_POST['gb']:0;
		$gg = !empty($_POST['gg'])?$_POST['gg']:0;
		$total_gb = 0;
		$total_gg = 0;
		include_once("UserModel.class.php");
		$userModel = new UserModel($this->userInfo ['user_db_key']);
		$userExtInfo = $userModel->getUserExt($this->userInfo ['user_id']);

		if($gg>0 && $userExtInfo['user_coin']<$gg){
			$msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_alert(\"哎呀！ 您的果果余额不足！<br /><a href='/index.php/help/newuser' class='blue_by12' target='_blank'>如何获得更多果果？ </a></br> <a href='javascript:show_cart();' class='blue_by12'>返回购物车</a>\");</script>";
			echo $msg;
			die;
		}
		if($gb>0 && $userExtInfo['user_cash']<$gb){
			$msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_alert(\"哎呀！ 您的G币余额不足！<br /><a href='/index.php/charge' class='blue_by12' target='_blank'>立即充值</a> &nbsp;&nbsp;&nbsp;<a href='javascript:show_cart();' class='blue_by12'>返回购物车</a>\");</script>";
			echo $msg;
			die;
		}
		$res = false;

		if(!empty($_SESSION['cart']) && is_array($_SESSION['cart'])){
			include_once("ShowItem.class.php");
			$item = new ShowItem();
			$msg_arr['user_id'] = $this->userInfo ['user_id'];
			$msg_arr['title'] = 'G币消费通知';
			$itemarr=array();
			foreach ($_SESSION['cart'] as $item_id=>$row ){
				$iteminfo = $item->getItemById($item_id);
				$item_cates = $item->getCateByItemId($item_id);
				$iteminfo['cate_title'] = $item_cates[0]['title'];
				$iteminfo['cate_id'] = $item_cates[0]['cate_id'];
				$total_gg += ($iteminfo['item_price_type']=='GG')?$iteminfo['item_price']*$row['usetime']:0;
				$total_gb += ($iteminfo['item_price_type']=='GB')?$iteminfo['item_price']*$row['usetime']:0;
				$_SESSION['cart'][$item_id]['iteminfo']  = $iteminfo;	
									
				$itemarr[]=$iteminfo['item_id']."|".$iteminfo['item_type'];
						
				if($iteminfo['item_price_type']=='GB'){
					$msg_arr['content'] = "您购买了一个".$iteminfo['item_title']."道具，花费了".$iteminfo['item_price']*$row['usetime']." G币。";
					$userModel->addMsg($msg_arr);
				}
			}
			$res = $userModel->buyCart($_SESSION['cart'],$this->userInfo,$total_gg,$total_gb);
		}

		if($res){
			$_SESSION['cart'] = '';
			setcookie ( 'IDOL_COIN_' . $this->userInfo ['user_name'], $this->userInfo ['user_coin']-$total_gg, 0, '/', COOKIE_DOMAIN );
			setcookie ( 'IDOL_CASH_' . $this->userInfo ['user_name'], $this->userInfo ['user_cash']-$total_gb, 0, '/', COOKIE_DOMAIN );
			if($itemarr){
			 $itemstr=implode(',',$itemarr);
			 $msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.item_used('{$itemstr}');parent.show_sys_money(".$total_gg.",".$total_gb.");parent.show_ok();</script>";	 
			}else{
			  $msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_sys_money(".$total_gg.",".$total_gb.");parent.show_ok();</script>";
			}
		}else{
			$msg .="<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.closeAlert();</script>";
		}
		echo $msg;
		die;
	}
	
	//查询用户道具
	function op_isuseritem()
	{ 
		$item_id = isset( $_POST['item_id'] ) ? $_POST['item_id'] : '';
		
		if ( empty($item_id) )
		{
			die();
		}
		
		include_once("UserModel.class.php");
		$_userObj = new UserModel( $this->userInfo ['user_db_key'] );
		$wealth_id = $_userObj->getUserItemWealthId($this->userInfo ['user_id'],$item_id);
		echo $wealth_id;
	}

	//续费
	function view_renew(){
		$total_gg = 0;
		$total_gb = 0;
		$wealth_id = !empty($_GET['wealth_id'])?$_GET['wealth_id']:0;
		include_once("UserModel.class.php");
		$userModel = new UserModel($this->userInfo ['user_db_key']);
		$wealth = $userModel->getWealthById($wealth_id);
		include_once("ShowItem.class.php");
		$item = new ShowItem();
		$iteminfo = $item->getItemById($wealth['wealth_item_id']);

		if($wealth['wealth_price_type']=='GG') $total_gg = $iteminfo['item_price'];
		if($wealth['wealth_price_type']=='GB') $total_gb = $iteminfo['item_price'];
		$this->tpl->assign('wealth',$wealth);
		$this->tpl->assign('item',$iteminfo);
		$this->tpl->assign('total_gg',$total_gg);
		$this->tpl->assign('total_gb',$total_gb);
		$this->tpl->assign('title','道具续费');
	}
	function op_rebuy(){
		$total_gg = 0;
		$total_gb = 0;
		$msg = '<meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS ['gSiteInfo'] ['webcharset'].'" />';
		$wealth_id = !empty($_POST['wealth_id'])?$_POST['wealth_id']:0;
		$usetime = !empty($_POST['usetime'])?$_POST['usetime']:1;
		$item_price = !empty($_POST['item_price'])?$_POST['item_price']:0;
		$item_price_type = !empty($_POST['item_price_type'])?$_POST['item_price_type']:'';

		include_once("UserModel.class.php");
		$userModel = new UserModel($this->userInfo ['user_db_key']);
		$userExtInfo = $userModel->getUserExt($this->userInfo ['user_id']);

		if($item_price_type=='GG'&& $userExtInfo['user_coin']<$item_price*$usetime){
			$msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_alert(\"哎呀！ 您的果果余额不足！<br /><a href='/index.php/help/newuser' class='blue_by12' target='_blank'>如何获得更多果果？ </a></br> <a href='javascript:show_cart();' class='blue_by12'>返回购物车</a>\");</script>";
		}
		if($item_price_type=='GB' && $userExtInfo['user_cash']<$item_price*$usetime){
			$msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_alert(\"哎呀！ 您的G币余额不足！<br /><a href='/index.php/charge' class='blue_by12' target='_blank'>立即充值</a> &nbsp;&nbsp;&nbsp;<a href='javascript:show_cart();' class='blue_by12'>返回购物车</a>\");</script>";
		}

		$wealth = $userModel->getWealthById($wealth_id);
		include_once("ShowItem.class.php");
		$showItem = new ShowItem();
		$iteminfo = $showItem->getItemById($wealth['wealth_item_id']);
		$item_cates = $showItem->getCateByItemId($iteminfo['item_id'] );
		$item['cate_title'] = $item_cates[0]['title'];

		$item['wealth_id'] = $wealth_id;
		$item['item_name'] = $iteminfo['item_name'];
		$item['item_title'] = $iteminfo['item_title'];
		$item['item_price_type'] = $iteminfo['item_price_type'];
		$item['item_price'] = $iteminfo['item_price'];
		$item['usetime'] = $usetime;
		$res = false;
		$res = $userModel->reBuy($item,$this->userInfo);
		if($res){
			$_SESSION['cart'] = '';
			if($item['item_price_type']=='GG') $total_gg = $item['item_price']*$item['usetime'];
			if($item['item_price_type']=='GB') $total_gb = $item['item_price']*$item['usetime'];

			$msg_arr['user_id'] = $this->userInfo ['user_id'];
			$msg_arr['title'] = 'G币消费通知';
			if($item['item_price_type']=='GB'){
				$msg_arr['content'] = "您购买了一个".$item['item_title']."道具，花费了".$total_gb." G币。";
				$userModel->addMsg($msg_arr);
			}

			setcookie ( 'IDOL_COIN_' . $this->userInfo ['user_name'], $this->userInfo ['user_coin']-$total_gg, 0, '/', COOKIE_DOMAIN );
			setcookie ( 'IDOL_CASH_' . $this->userInfo ['user_name'], $this->userInfo ['user_cash']-$total_gb, 0, '/', COOKIE_DOMAIN );
			$msg .= "<script>document.domain=\"".substr(COOKIE_DOMAIN,1)."\";parent.show_ok();</script>";
		}
		//print_sql();die;
		echo $msg;
		die;

	}


	//恢复形象
	function op_renew() {
		$id = isset ( $_POST ['defId'] ) ? $_POST ['defId'] : '';

		$def_charInfo = json_encode ( $this->user_obj->getDefCharInfo ( $id ) );
		echo $def_charInfo;
		die ();
	}
	//恢复形象
	function op_renewSave() {
		$username = isset ( $_POST ['username'] ) ? $_POST ['username'] : ''; //$_POST['username'];
		$charInfo = json_encode ( $this->user_obj->getCharInfo ( $username ) );
		echo $charInfo;
		die ();
	}
	//拼音匹配
	function op_pymatching() {
		$pinyin = empty ( $_POST ['pinyin'] ) ? '搜索道具' : strtolower ( $_POST ['pinyin'] );
		$str = array ();
		$str_array = $this->pinyinstr ( $pinyin );
		$gender = $this->userInfo ['user_gender'];
		for($i = 0; $i < count ( $str_array ); $i ++) {
			for($j = 0; $j < strlen ( $str_array [$i] ); $j += 3) {
				$str [$i] .= substr ( $str_array [$i], $j, 3 ) . "|";
			}
			$str [$i] = trim ( $str [$i], "|" );
		}
		$item_name = $this->item_obj->getItemName ( $str, $gender );

		foreach ( $item_name as $k => $v ) {
			echo '<a id="shitem" harf="javascript:;" onclick="javascript:document.getElementById(\'search\').value = this.innerHTML;$(\'absh\').style.display=\'none\';">' . $v ['item_title'] . '</a>';
		}
		exit ();
	}

	function pinyinstr($str) {
		$str_1 = substr ( $str, 0, 1 ); //得到首字母
		$str_array = array ();
		if (strlen ( $str ) <= 1) //只输入一个拼音
		{
			if (! empty ( $this->py [$str_1] )) {
				foreach ( $this->py [$str_1] as $k => $v ) {
					$str_array [] = $v;
				}
			} else {
				$str_array [] = $str;
			}
		} else //输入多个拼音
		{
			if (! empty ( $this->py [$str_1] )) {
				$this->str = $str;
				$this->isok ( $str, $str_array );
			} else {
				$str_array [] = $str;
			}
		}
		return $str_array;
	}


	function op_saveData() {
		$data ['items'] = isset ( $_POST ['items'] ) ? $_POST ['items'] : '';
		$data ['orgiteminfo'] = isset ( $_POST ['orgiteminfo'] ) ? $_POST ['orgiteminfo'] : '';
		$data ['charinfo'] = isset ( $_POST ['charinfo'] ) ? $_POST ['charinfo'] : '';
		$data ['cameradata'] = isset ( $_POST ['cameradata'] ) ? $_POST ['cameradata'] : '';
		$data ['operation'] = isset ( $_POST ['operation'] ) ? $_POST ['operation'] : '';
		$return_msg = $this->user_obj->saveData ( $data );
		echo $return_msg;
		//记录保存日志
		curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url']."/savelog.php?user=".$this->userInfo ['user_name']."&userid=".$this->userInfo ['user_id']);
		die ();

	}
	function isok($str, &$return) {
		$str_1 = substr ( $str, 0, 1 );
		if (isset ( $this->py [$str_1] [$str] )) {
			$return [] = $this->py [$str_1] [$str];
			$str_2 = str_replace ( $str, '', $this->str );
			$this->str = $str_2;
			if (! empty ( $str_2 )) {
				$this->isok ( $str_2, $return );
			}
		} else {
			$left = substr ( $str, 0, - 1 );
			if ($left != '') {
				$this->isok ( $left, $return );
			}
		}
	}



}

?>