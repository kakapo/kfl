<meta name="TENCENT_ONELINE_PAYMENT" content="China TENCENT">
<html>
<?
  function ShowExitMsg($msg)
  {
    $strMsg = "<script language=javascript>\n";
    $strMsg.= "window.location.href=\"http://www.guodong.dev3/plugins/tenpay/showResult.php?msg=";
    $strMsg.= $msg;
    $strMsg.= "\";\n";
    $strMsg.= "</script></body></html>";
    Exit($strMsg);
  };

  import_request_variables("gpc", "frm_");
  /*取返回参数*/
  $strCmdno			= $frm_cmdno;
  $strPayResult		= $frm_pay_result;
  $strPayInfo		= $frm_pay_info;
  $strBillDate		= $frm_date;
  $strBargainorId	= $frm_bargainor_id;
  $strTransactionId	= $frm_transaction_id;
  $strSpBillno		= $frm_sp_billno;
  $strTotalFee		= $frm_total_fee;
  $strFeeType		= $frm_fee_type;
  $strAttach			= $frm_attach;
  $strMd5Sign		= $frm_sign;
  /*本地参数*/
    /*这里替换为您的实际商户号*/
    $strSpid    = "2000000301"; 
    /*商户密钥,测试时即为商户号,正式上线后需修改*/
    $strSpkey   = "2000000301"; 
  /*返回值定义*/
  $iRetOK       = 0;		// 成功					
  $iInvalidSpid = 1;		// 商户号错误
  $iInvalidSign = 2;		// 签名错误
  $iTenpayErr	  = 3;		// 财付通返回支付失败

  /*验签*/
  $strResponseText  = "cmdno=" . $strCmdno . "&pay_result=" . $strPayResult . 
		                  "&date=" . $strBillDate . "&transaction_id=" . $strTransactionId .
			                "&sp_billno=" . $strSpBillno . "&total_fee=" . $strTotalFee .
			                "&fee_type=" . $strFeeType . "&attach=" . $strAttach .
			                "&key=" . $strSpkey;
  $strLocalSign = strtoupper(md5($strResponseText));     
  
  if( $strLocalSign  != $strMd5Sign)
  {
    ShowExitMsg( "验证MD5签名失败."); 
  }  
  
  if( $strSpid != $strBargainorId )
  {
    ShowExitMsg( "错误的商户号."); 
  }

  if( $strPayResult != "0" )
  {
    ShowExitMsg( "支付失败."); 
  }
  ShowExitMsg( "支付成功."); 


?>
</html>
