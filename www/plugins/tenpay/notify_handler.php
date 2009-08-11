<meta name="TENCENT_ONELINE_PAYMENT" content="China TENCENT">
<html>
<title>财付通支付响应 PHP示例</title>
<meta http-equiv="Cache-Control" content="no-cache" charset="UTF-8"/> 
<body>
<? 
  function ShowExitMsg($msg)
  {
    $strMsg = "<script language=javascript>\n";
    $strMsg.= "window.location.href=\"http://{$_SERVER["HTTP_HOST"]}/plugins/tenpay/showResult.php?msg=";
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
  $strAttach		= $frm_attach;
  $strMd5Sign		= $frm_sign;

  /*本地参数*/
    /*这里替换为您的实际商户号*/
    $strSpid    = "1204711701"; 
    /*strSpkey是32位商户密钥, 请替换为您的实际密钥*/
    $strSpkey   = "tangsong20090616tangsong20090616"; 

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
  }else{
  	include_once('../../config/config.ini.php');
    include_once (KFL_DIR."/Libs/mypdo.class.php");
    include_once (KFL_DIR."/KFL.php");
    include_once ('../../models/ApiUser.class.php');
    include_once ('../../models/ChargeModel.php');
    
    $mycharge=new ChargeModel(); 
    $strTotalFee=$strTotalFee/100; 
    $userarr=explode('|',$strAttach);
    $user_id=$userarr[0];
    $user_name=$userarr[1];
    $vo=array(
		 'chg_rmb'=>$strTotalFee,
		 'chg_no'=>$strTransactionId,
		 'chg_time'=> date('Y-m-d H:i:s'),
		 'chg_type'=>'财付通',
		 'user_id'=> $user_id,
		 'chg_ym'=>date('Ym'),	 
	);
	$result=$mycharge->commonCharge($vo,$user_name);
	$msg="恭喜您，支付成功！";
	if($result){
	   ShowExitMsg($msg); 
	}else{
	   ShowExitMsg( "支付成功，记录没有保存，请即时和管理员联系！");
	}
  }
  
 
  
?>
