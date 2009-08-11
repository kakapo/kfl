<?php
/*
	*功能：付款过程中服务器通知页面
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
$verify_result = $alipay->notify_verify();
if($verify_result) {   //认证合格
    $dingdan  = $_POST['out_trade_no'];    //获取支付宝传递过来的订单号
    $total    = $_POST['total_fee'];       //获取支付宝传递过来的总价格
   if($_POST['trade_status'] == 'TRADE_FINISHED' ||$_POST['trade_status'] == 'TRADE_SUCCESS') {    //交易成功结束
      echo "success";
	}else {
		echo "fail";
	}
}else {   
	echo "fail";
}

?>