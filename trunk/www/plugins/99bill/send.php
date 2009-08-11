<?PHP
/**
 * @Description: 快钱网关接口范例
 * @Copyright (c) 上海快钱信息服务有限公司
 * @version 2.0
 */
   $merchant_id = "879905060103109788";		///商户编号
   $merchant_key = "99billKeyForTest";		///商户密钥
   
   $orderid=date('YmdHis');
   $orderid.=$_POST['user_id'];             ///订单编号
   $merchant_url = $_POST['reurl'];         ///支付结果返回地址
   $amount = "0.01";		                ///订单金额
   //$amount=$_POST['money'];             
   $curr = "1";		                        ///货币类型,1为人民币
   $isSupportDES = "2";		                ///是否安全校验,2为必校验,推荐
   $pname = $_POST['user_name'];		    ///支付人姓名
   $commodity_info = '果动网';	            ///商品信息
   $merchant_param =$_POST['user_id']."|".$_POST['user_name'];		    ///商户私有参数	
   $pemail="nobody@tsong.com";		        ///传递email到快钱网关页面
   $pid="";		                            ///代理/合作伙伴商户编号
 
   ///生成加密串,注意顺序
   $ScrtStr="merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&merchant_url=".$merchant_url."&merchant_key=".$merchant_key;
   $mac = strtoupper(md5($ScrtStr)); 	
?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en" >
<html>
	<head>
		<title>快钱99bill</title>
		<meta http-equiv="content-type" content="text/html;">
	</head>	
       <BODY onload="frm.submit();">
		<form name="frm" method="post" action="https://www.99bill.com/webapp/receiveMerchantInfoAction.do">
			<input name="merchant_id" type="hidden" value="<?php echo $merchant_id; ?>">
			<input name="orderid"  type="hidden" value="<?php echo $orderid; ?>">
			<input name="amount"  type="hidden" value="<?php echo $amount; ?>">
			<input name="currency"  type="hidden" value="<?php echo $curr; ?>">
			<input name="isSupportDES"  type="hidden" value="<?php echo $isSupportDES; ?>">
			<input name="mac"  type="hidden" value="<?php echo $mac; ?>">
			
			<input name="merchant_url"  type="hidden"  value="<?php echo $merchant_url; ?>">
			<input name="pname"  type="hidden" value="<?php echo $pname; ?>">
			<input name="commodity_info"  type="hidden"  value="<?php echo $commodity_info; ?>">
			<input name="merchant_param" type="hidden"  value="<?php echo $merchant_param; ?>">
			<input name="pemail" type="hidden"  value="<?php echo $pemail; ?>">
			<input name="pid" type="hidden"  value="<?php echo $pid; ?>">
		</form>
		</div>
</BODY>
</HTML>