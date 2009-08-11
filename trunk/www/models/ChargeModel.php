<?php

class ChargeModel extends Model {
    private $vardb;
	function __construct(){

	}
	//
    function setVarDb($user_db_key)
    {
    	if ( isset( $GLOBALS [ 'gDataBase' ][ $user_db_key ] ) )
		{
			$this->vardb = parent::dbConnect( $GLOBALS[ 'gDataBase' ][ $user_db_key ] );
		}
		else
		{
			return "database {$user_db_key} not defined!";
		}
    }

	function getChargeMoney($username,$userid){
		$userbase = ApiUser::getUserByName($username);
		$this->setVarDb($userbase['user_db_key']);
        if(isset($_COOKIE['CHARGE_ID'])){
          $chrgid=$_COOKIE['CHARGE_ID'];
          if(!empty($chrgid)){
		    $rs=$this->vardb->getRow("select * from user_charge_log where user_id='".$userid."' and chg_no=".$chrgid);
             return  $rs;
		  }else{
			return false;
		 }
		}else{
			return false;
		}		
	}
	//通用
	function commonCharge($vo,$username)
	{
		$userbase = ApiUser::getUserByName($username);
		$this->setVarDb($userbase['user_db_key']);
		$user_id=$this->vardb->getOne("select user_id from user where user_name='{$username}' limit 1");
		if($user_id!=$vo['user_id']){
		  return false;
		}
	    $rs=$this->vardb->getRow("select * from user_charge_log where chg_no='{$vo['chg_no']}' and chg_type='{$vo['chg_type']}' and user_id=".$vo['user_id']);
	    if($rs){
	      return true;
	    }
	    $this->vardb->beginTrans();
		try{
	     $v1=$this->vardb->insert($vo, 'user_charge_log');    
	     $xiaofei=$this->vardb->getOne("select sum(pay_value) from user_pay_log where pay_type='GB' and user_id=".$vo['user_id']);
		 $chongzhi=$this->vardb->getOne("select sum(chg_rmb) from user_charge_log where user_id=".$vo['user_id']);
		 $current=$chongzhi-$xiaofei;
	     $v2=$this->vardb->query("update user_extinfo set user_cash='{$current}' where user_id=".$vo['user_id']);
	     $v3=$this->vardb->query("insert into user_msg (user_id,msg_time,msg_content,msg_title)values('{$vo['user_id']}',UNIX_TIMESTAMP(),'您已经成功充值 {$vo['chg_rmb']} G币 加入VIP，全部道具免费使用','充值通知')");
	     if($v1 && $v2!==false){
		 	$this->vardb->commitTrans();	
		    return true;
		 }else{
		 	$this->vardb->rollbackTrans();
		    return false;
		 }
		}catch (Exception $e) {
		  $this->vardb->rollbackTrans();
		  return false;
		}
	}
	
	function getMyHistoryList($num = '', $pagination,$query=array())
	{
		$userbase = ApiUser::getUserByName($query['user_name']);
		$this->setVarDb($userbase['user_db_key']);
		$select = $this->vardb->select();
		$select->from('user_charge_log','*');
		$select->where("user_id=".$query['user_id']);
		$time=$query['year'];
		if(!empty($query['month'])){
		  $time.="-".$query['month'];
		}
		$select->where("chg_time like '{$time}%'");
		$select->order('chg_time desc');
		return $this->getList ( $select, $num, $pagination );
	}

	function getMyConsumeList($num = '', $pagination,$query=array())
	{

		$userbase = ApiUser::getUserByName($query['user_name']);
		$this->setVarDb($userbase['user_db_key']);
		$select = $this->vardb->select();
		$select->from('user_pay_log','*');
		$select->where("user_id=".$query['user_id']);
		$time=$query['year'];
		if(!empty($query['month'])){
		  $time.="-".$query['month'];
		}
		$select->where("pay_time like '{$time}%'");
		if(!empty($query['moneytype'])){
		 $select->where("pay_type='{$query['moneytype']}'");
		}
		$select->order('pay_time desc');
		return $this->getList ( $select, $num, $pagination );
	}
		//获得道具列表
	function getList($select, $pageCount = -1, $pagination = false) {

		//是否获得分页对象
		if ($pagination !== false) {
			include_once('HTML_Pager.class.php');
			$total = $select->count (); //获得查询到的记录数
			$list ['total']=$total;
			$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
			$offset = $list ['page']->offset (); //获得记录偏移量
			$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'yellowf3_12b none', 'numBarMain' => 'gray4_12 none' );                      //翻页条的样式
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
}



?>