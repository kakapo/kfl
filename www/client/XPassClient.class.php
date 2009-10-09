<?php
class XPassClient{
	
	private $_private_key;
	
	public $math_lib;
	
	function __construct($private_key,$math_library='BCMath'){
		require_once 'Crypt/RSA.php';
		$this->math_lib = $math_library;
		$this->_private_key = Crypt_RSA_Key::fromString($private_key,$this->math_lib);
	}
	
	
	private function _createSign($text)
	{
	 
	   	$rsa_obj = new Crypt_RSA(array(), $this->math_lib, 'check_error');
	
		// check signing/sign validating
		$params = array(
		    'private_key' => $this->_private_key
		);
		$rsa_obj->setParams($params);
		
		$sign = $rsa_obj->createSign($text);
			    
	    return $sign;
	}
	
	private function _decryptToken($enc_text){
		$rsa_obj = new Crypt_RSA(array(), $this->math_lib, 'check_error');
	
		// check signing/sign validating
		$params = array(
		    'dec_key' => $this->_private_key
		);
		$rsa_obj->setParams($params);
		
		return $rsa_obj->decrypt($enc_text);
	}
	
	private function _xpassServer($url,$time_out = "60") {
		
		$urlarr     = parse_url($url);
		$errno      = "";
		$errstr     = "";
		$transports = "";
		if($urlarr["scheme"] == "https") {
			$transports = "ssl://";
			$urlarr["port"] = "443";
		} else {
			$transports = "tcp://";
			$urlarr["port"] = "80";
		}
		
		$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			fputs($fp, "GET ".$urlarr["path"].'?'.$urlarr["query"]." HTTP/1.1\r\n");
			fputs($fp, "Host: ".$urlarr["host"]."\r\n");
			//fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			//fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			//fputs($fp, $urlarr["query"] . "\r\n\r\n");
			while(!feof($fp)) {
				$info.=@fgets($fp, 1024);
			}
			fclose($fp);
			//$info = implode("",$info);
//			while (list ($key, $val) = each ($_POST)) {
//				$arg.=$key."=".$val."&";
//			}
			
			return $info;
		}
	}
	
	private function _getLoginUrl($user){
		global $server_url;
		$domain = $_SERVER['HTTP_HOST'];
		
		$sign = $this->_createSign(md5($user.$domain));
		$url = $server_url."/index.php?action=api&view=islogin&user=".$user."&domain=".$domain."&sign=".$sign;
		
		return $url;
	}
	/**
	 * isLogin 
	 * @param string $user
	 * 
	 * @return array
	 **/
	public function isLogin($user){
		
		$url = $this->_getLoginUrl($user);
		
		$res = $this->_xpassServer($url);
		
		list($head,$body) = explode("\r\n\r\n",$res);
		
		$msg = json_decode($body,true);
		
		return $msg;
	}
	
	public function getLoginUser($ticket){
		global $server_url;
		$domain = $_SERVER['HTTP_HOST'];
		
		$sign = $this->_createSign(md5($ticket.$domain));
		
		$url = $server_url."/index.php?action=api&view=getuser&ticket=".$ticket."&domain=$domain&sign=".$sign;
		
		$res = $this->_xpassServer($url);
		
		list($head,$body) = explode("\r\n\r\n",$res);
		//echo $body;
		$msg = json_decode($body,true);
		if($msg['s']==200){
			//$userinfo = $this->_decryptToken($msg['d']);
			$userinfo = decrypt($msg['d'],$ticket);
			return json_decode($userinfo,true);
		}else{
			return false;
		}
		
	}
	

	
}
function decrypt($s, $key='key')
{
	$r ='';
	$s=base64_decode(urldecode($s));
	for($i=1;$i<=strlen($s);$i++){
		$s[$i-1] = chr(ord($s[$i-1])-ord(substr(md5($key),($i % strlen(md5($key)))-1,1)));
	}
	for($i=1;$i<=strlen($s)-1;$i=$i+2){
		$r .= $s[$i];
	}
	return $r;
}

?>