<?  
  /*�����滻Ϊ����ʵ���̻���*/
  $strSpid    = "1204711701"; 
  /*strSpkey��32λ�̻���Կ, ���滻Ϊ����ʵ����Կ*/
  $strSpkey   = "tangsong20090616tangsong20090616"; 
  /*�Ƹ�֧ͨ��Ϊ"1" (��ǰֻ֧�� cmdno=1)*/
  $strCmdNo   = "1";
  /*�������� (yyyymmdd)*/
  $strBillDate= date('Ymd');  
  /*��������:	
      0		  �Ƹ�ͨ
  		1001	��������   
  		1002	�й���������  
  		1003	�й���������  
  		1004	�Ϻ��ֶ���չ����   
  		1005	�й�ũҵ����  
  		1006	�й���������  
  		1008	���ڷ�չ����   
  		1009	��ҵ����   */
  $strBankType= "0";  
  /*��Ʒ����*/
  
  $strDesc    ="������G��";		
  /*�û�QQ����, ������Ϊ�մ�*/
  $strBuyerId = "";
  /*�̻���*/	
  $strSaler   = $strSpid;		
  		
  
  
  
  /*�̻����ɵĶ�����(���10λ)*/	
  $strSpBillNo= date('mdHis'); 
  /*post ����*/
  $money=$_POST['money'];
  $strTotalFee=$money*100;         //תΪ��
  //���ߺ���������
//  $strTotalFee=1;
  
  /*�ܽ��, ��Ϊ��λ*/
 // $strTotalFee = "1";
  /*�̻�˽������, ����ص�ҳ��ʱԭ������*/
  $strAttach  = $_POST['user_id']."|".$_POST['user_name'];
  /*�Ƹ�ͨ�ص�ҳ���ַ, �Ƽ�ʹ��ip��ַ�ķ�ʽ(�255���ַ�)*/
  $strRetUrl  = $_POST['reurl'];
  
  /*��Ҫ: ���׵���
	  ���׵���(28λ): �̻���(10λ) + ����(8λ) + ��ˮ��(10λ), ���밴�˸�ʽ����, �Ҳ����ظ�
	  ���sp_billno����10λ, ���ȡ���е���ˮ�Ų��ּӵ�transaction_id��(����10λ��0)
	  ���sp_billno����10λ, ����0, �ӵ�transaction_id��*/
   $strTransactionId = $strSpid . $strBillDate . $strSpBillNo;

	/*��������: 1 �C RMB(�����) 2 - USD(��Ԫ) 3 - HKD(�۱�)*/
	$strFeeType  = "1";


	/*����MD5ǩ��*/
	$strSignText = "cmdno=" . $strCmdNo . "&date=" . $strBillDate . "&bargainor_id=" . $strSaler .
	      "&transaction_id=" . $strTransactionId . "&sp_billno=" . $strSpBillNo .        
	      "&total_fee=" . $strTotalFee . "&fee_type=" . $strFeeType . "&return_url=" . $strRetUrl .
	      "&attach=" . $strAttach . "&key=" . $strSpkey;
  $strSign = strtoupper(md5($strSignText));
  
  /*����֧����*/
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
<title> �Ƹ�֧ͨ����</title>
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