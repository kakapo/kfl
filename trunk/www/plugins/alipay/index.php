<?php
/*
	*���ܣ�������Ʒ�й���Ϣ
	*�汾��2.0
	*���ڣ�2008-08-01
	*���ߣ�֧������˾���۲�����֧���Ŷ�
	*��ϵ��0571-26888888
	*��Ȩ��֧������˾
*/

require_once("alipay_service.php");
require_once("alipay_config.php");

$money=$_POST['money'];   //��ֵ���
//$money="0.01";         //����ʱ��

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
	"service"         => "create_direct_pay_by_user",  //��������
	"partner"         => $partner,          //�����̻���
	"return_url"      => $return_url,       //ͬ������
	"notify_url"      => $notify_url,       //�첽����
	"_input_charset"  => $_input_charset,   //�ַ�����Ĭ��ΪGBK
	"subject"         => "������G��",        //��Ʒ���ƣ�����
	"body"            => "�ڹ�������ֵ".$money."Ԫ�����",        //��Ʒ����������
	"out_trade_no"    =>  $tradeno,      //��Ʒ�ⲿ���׺ţ������֤Ψһ�ԣ�
	"total_fee"       => $money,            //��Ʒ���ۣ�����۸���Ϊ0��
	"payment_type"    => "1",               //Ĭ��Ϊ1,����Ҫ�޸�

	"show_url"        => $show_url,         //��Ʒ�����վ
	"seller_email"    => $seller_email      //�������䣬����
);

 $domain = strstr($_SERVER['HTTP_HOST'],".");
 @setcookie ('CHARGE_ID', $tradeno, time()+3600, '/', $domain );
$alipay = new alipay_service($parameter,$security_code,$sign_type);
$link=$alipay->create_url();
echo "<script>window.location =\"$link\";</script>"; 
?>

