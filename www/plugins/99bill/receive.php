<?PHP
include_once('../../config/config.ini.php');
require_once (KFL_DIR."/Libs/mypdo.class.php");
require_once (KFL_DIR."/KFL.php");
require_once ('../../models/ApiUser.class.php');
require_once ('../../models/ChargeModel.php');
/**
 * @Description: 快钱网关接口范例
 * @Copyright (c) 上海快钱信息服务有限公司
 * @version 2.0
 */
	$merchant_key ="99billKeyForTest";		       ///商户密钥
	$merchant_id =trim($_REQUEST['merchant_id']);  ///获取商户编号
	$orderid = trim($_REQUEST['orderid']);		   ///获取订单编号
	$amount = trim($_REQUEST['amount']);	       ///获取订单金额
	$dealdate = trim($_REQUEST['date']);		   ///获取交易日期
	$succeed = trim($_REQUEST['succeed']);         ///获取交易结果,Y成功,N失败
	$mac = trim($_REQUEST['mac']);		           ///获取安全加密串
	$merchant_param = trim($_REQUEST['merchant_param']);///获取商户私有参数

	$couponid = trim($_REQUEST['couponid']);		///获取优惠券编码
	$couponvalue = trim($_REQUEST['couponvalue']);		///获取优惠券面额

	///生成加密串,注意顺序
   $ScrtStr = "merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&date=".$dealdate."&succeed=".$succeed."&merchant_key=".$merchant_key;  
   $mymac = md5($ScrtStr); 
		
		
	$v_result="失败";
	if(strtoupper($mac)==strtoupper($mymac)){
		
			if($succeed=="Y"){		///支付成功
				$arr=explode('|',$merchant_param);
				$user_name=$arr[1];
				$mycharge=new ChargeModel();
				$vo=array(
		         'chg_rmb'=>$amount,
		         'chg_no'=>$orderid,
		         'chg_time'=> date('Y-m-d H:i:s'),
		         'chg_type'=>'快钱',
		         'user_id'=> substr($orderid,14),
		         'chg_ym'=>date('Ym'),	 
	            );
	            $result=$mycharge->commonCharge($vo,$user_name);
	            if($result){
	            	$v_result="成功";
	            }else{
	            	$v_result="冲值成功，但交易未完成，请与网站管理员联系！";
	            }			
			
			}else{		///支付失败
		
				
			}
		
	}else{		///签名错误
	
			
	}
?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en" >
<html>
	<head>
		<title>快钱99bill</title>
		<meta http-equiv="content-type" content="text/html; charset=gb2312" />
	</head>
	<body>	
		<div align="center">
		<table width="259" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC" >
			<tr bgcolor="#FFFFFF">
				<td width="68">订单编号:</td>
			  <td width="182"><?php echo $orderid;?></td>
			</tr>
			<tr bgcolor="#FFFFFF">
				<td>冲值金额:</td>
			  <td><?php echo $amount;?></td>
			</tr>
			<tr bgcolor="#FFFFFF">
				<td>冲值结果:</td>
			  <td><?php echo $v_result;?></td>
			</tr>
	  </table>
	</div>
	</body>
</html>
