<?php
/*
	*功能：设置商品有关信息
	*版本：2.0
	*日期：2008-08-01
	*作者：支付宝公司销售部技术支持团队
	*联系：0571-26888888
	*版权：支付宝公司
*/

require_once("alipay_service.php");
require_once("alipay_config.php");

$money=$_POST['money'];   //冲值金额
//$money="0.01";         //测试时用

$tradeno=date('YmdHis').substr(microtime(),2,6);
if(isset($_POST['user_id'])&&!empty($_POST['user_id'])){
  $tradeno.=$_POST['user_id'];
}else{
  echo "user_id is not exist!";
  die;
}
$return_url=$_POST['return_url'];
$notify_url='';
$parameter = array(
	"service"         => "create_direct_pay_by_user",  //交易类型
	"partner"         => $partner,          //合作商户号
	"return_url"      => $return_url,       //同步返回
	"notify_url"      => $notify_url,       //异步返回
	"_input_charset"  => $_input_charset,   //字符集，默认为GBK
	"subject"         => "果动网G币",        //商品名称，必填
	"body"            => "在果动网充值".$money."元人民币",        //商品描述，必填
	"out_trade_no"    =>  $tradeno,      //商品外部交易号，必填（保证唯一性）
	"total_fee"       => $money,            //商品单价，必填（价格不能为0）
	"payment_type"    => "1",               //默认为1,不需要修改

	"show_url"        => $show_url,         //商品相关网站
	"seller_email"    => $seller_email      //卖家邮箱，必填
);

 $domain = strstr($_SERVER['HTTP_HOST'],".");
 @setcookie ('CHARGE_ID', $tradeno, time()+3600, '/', $domain );
$alipay = new alipay_service($parameter,$security_code,$sign_type);
$link=$alipay->create_url();
echo "<script>window.location =\"$link\";</script>"; 
?>

