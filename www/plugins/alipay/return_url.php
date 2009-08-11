<?php
/*
	*功能：付完款后跳转的页面
	*版本：2.0
	*日期：2008-08-01
	*作者：支付宝公司销售部技术支持团队
	*联系：0571-26888888
	*版权：支付宝公司
*/
header("Content-type: text/html; charset=utf-8"); 
require_once("alipay_notify.php");
require_once("alipay_config.php");
$alipay = new alipay_notify($partner,$security_code,$sign_type,$_input_charset,$transport);
$verify_result = $alipay->return_verify();

 //获取支付宝的反馈参数
   $dingdan    = $_GET['out_trade_no'];   //获取订单号
   $total_fee  = $_GET['total_fee'];      //获取总价格
   $user_name= $_GET['u'];

if($verify_result) {    //认证合格
	//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
	//log_result("verify_success"); 
   //如果您申请了支付宝的购物卷功能，请在返回的信息里面不要做金额的判断，否则会出现校验通不过，出现调单。如果您需要获取买家所使用购物卷的金额,
  //请获取返回信息的这个字段discount的值，取绝对值，就是买家付款优惠的金额。即 原订单的总金额=买家付款返回的金额total_fee +|discount|.
	include_once('../../config/config.ini.php');
    include_once (KFL_DIR."/Libs/mypdo.class.php");
    include_once (KFL_DIR."/KFL.php");
    include_once ('../../models/ApiUser.class.php');
    include_once ('../../models/ChargeModel.php');   
   $mymain=new ChargeModel();
   $record=array(
	 'chg_rmb'=>$total_fee,
	 'chg_no'=>$dingdan,
	 'chg_time'=>date('Y-m-d H:i:s'),
	 'chg_type'=>'支付宝',
	 'user_id'=>substr($dingdan,20),
	 'chg_ym'=>date('Ym'),
    );
	$result=$mymain->commonCharge($record,$user_name);
    if($result){      
     echo '<center>恭喜您，支付成功！</center>';
    }else{
     echo '<center>记录没有保存，请即时和管理员联系！</center>';
    }
}else {    //认证不合格
	 echo "<center>充值失败！</center>";
}
?>