<?  
  /*这里替换为您的实际商户号*/
  $strSpid    = "1204711701"; 
  /*strSpkey是32位商户密钥, 请替换为您的实际密钥*/
  $strSpkey   = "tangsong20090616tangsong20090616"; 
  /*财付通支付为"1" (当前只支持 cmdno=1)*/
  $strCmdNo   = "1";
  /*交易日期 (yyyymmdd)*/
  $strBillDate= date('Ymd');  
  /*银行类型:	
      0		  财付通
  		1001	招商银行   
  		1002	中国工商银行  
  		1003	中国建设银行  
  		1004	上海浦东发展银行   
  		1005	中国农业银行  
  		1006	中国民生银行  
  		1008	深圳发展银行   
  		1009	兴业银行   */
  $strBankType= "0";  
  /*商品名称*/
  
  $strDesc    ="果动网G币";		
  /*用户QQ号码, 现在置为空串*/
  $strBuyerId = "";
  /*商户号*/	
  $strSaler   = $strSpid;		
  		
  
  
  
  /*商户生成的订单号(最多10位)*/	
  $strSpBillNo= date('mdHis'); 
  /*post 数据*/
  $money=$_POST['money'];
  $strTotalFee=$money*100;         //转为分
  //上线后屏蔽下行
//  $strTotalFee=1;
  
  /*总金额, 分为单位*/
 // $strTotalFee = "1";
  /*商户私有数据, 请求回调页面时原样返回*/
  $strAttach  = $_POST['user_id']."|".$_POST['user_name'];
  /*财付通回调页面地址, 推荐使用ip地址的方式(最长255个字符)*/
  $strRetUrl  = $_POST['reurl'];
  
  /*重要: 交易单号
	  交易单号(28位): 商户号(10位) + 日期(8位) + 流水号(10位), 必须按此格式生成, 且不能重复
	  如果sp_billno超过10位, 则截取其中的流水号部分加到transaction_id后部(不足10位左补0)
	  如果sp_billno不足10位, 则左补0, 加到transaction_id后部*/
   $strTransactionId = $strSpid . $strBillDate . $strSpBillNo;

	/*货币类型: 1 – RMB(人民币) 2 - USD(美元) 3 - HKD(港币)*/
	$strFeeType  = "1";


	/*生成MD5签名*/
	$strSignText = "cmdno=" . $strCmdNo . "&date=" . $strBillDate . "&bargainor_id=" . $strSaler .
	      "&transaction_id=" . $strTransactionId . "&sp_billno=" . $strSpBillNo .        
	      "&total_fee=" . $strTotalFee . "&fee_type=" . $strFeeType . "&return_url=" . $strRetUrl .
	      "&attach=" . $strAttach . "&key=" . $strSpkey;
  $strSign = strtoupper(md5($strSignText));
  
  /*请求支付串*/
  $strRequest = "cmdno=" . $strCmdNo . "&date=" . $strBillDate . "&bargainor_id=" . $strSaler .        
  "&transaction_id=" . $strTransactionId . "&sp_billno=" . $strSpBillNo .        
  "&total_fee=" . $strTotalFee . "&fee_type=" . $strFeeType . "&return_url=" . $strRetUrl .        
  "&attach=" . $strAttach . "&bank_type=" . $strBankType . "&desc=" . $strDesc .        
  "&purchaser_id=" . $strBuyerId .        
  "&sign=" . $strSign ; 
  $domain = strstr($_SERVER['HTTP_HOST'],".");
  @setcookie ('CHARGE_ID', $strTransactionId, time()+3600, '/', $domain );
?>
<html>
<title> 财付通支付接</title>
<meta http-equiv="Cache-Control" content="no-cache" charset="gb2312"/> 
<body onload="form1.submit();">
<form action="https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi" name="form1">
<input type=hidden name="cmdno"				value=<?echo $strCmdNo; ?>>
<input type=hidden name="date"			    value=<?echo $strBillDate; ?>>
<input type=hidden name="bank_type"			value=<?echo $strBankType; ?>>
<input type=hidden name="desc"				value=<?echo $strDesc; ?>>
<input type=hidden name="purchaser_id"		value=<?echo $strBuyerId; ?>>
<input type=hidden name="bargainor_id"		value=<?echo $strSaler; ?>>
<input type=hidden name="transaction_id"	value=<?echo $strTransactionId; ?>>
<input type=hidden name="sp_billno"			value=<?echo $strSpBillNo; ?>>
<input type=hidden name="total_fee"			value=<?echo $strTotalFee; ?>>
<input type=hidden name="fee_type"			value=<?echo $strFeeType; ?>>
<input type=hidden name="return_url"		value=<?echo $strRetUrl; ?>>
<input type=hidden name="attach"			value=<?echo $strAttach; ?>>
<input type=hidden name="sign"				value=<?echo $strSign; ?>>
</form>
</body>
</html>