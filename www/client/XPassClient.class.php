<?php
class XPassClient{
	
	private $_private_key;

	function __construct($private_key){
		
		$this->_private_key = $private_key;
	}
	
	private function _createSign($text)
	{	
		return hmac($this->_private_key,$text,'sha1');
	}
	
	private function _decryptToken($enc_text){
		return decrypt($enc_text,$this->_private_key);
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
	 * @param boolen $redirect
	 * @return array
	 **/
	public function isLogin($user,$redirect=false){
			
		if(isset($_GET['ticket']) && !empty($_GET['ticket'])){
			return array('s'=>200,'m'=>'success','d'=>$_GET['ticket']);
		}
		
		$url = $this->_getLoginUrl($user);
		
		if($redirect) {
			header("Location: ".$url."&redirect=1&return=".urlencode(selfURL()));
			die;
		}
		
		$res = $this->_xpassServer($url);
		
		list($head,$body) = explode("\r\n\r\n",$res);
		
		$msg = json_decode($body,true);
		
		return $msg;
	}
	/**
	 * getLoginUser 
	 * @param string $ticket
	 * @return array
	 **/	
	public function getLoginUser($ticket){
		global $server_url;
		
		$domain = $_SERVER['HTTP_HOST'];
		
		$sign = $this->_createSign(md5($ticket.$domain));
	
		$url = $server_url."/index.php?action=api&view=getuser&ticket=".$ticket."&domain=$domain&sign=".$sign;
		
		$res = $this->_xpassServer($url);
		
		list($head,$body) = explode("\r\n\r\n",$res);
		
		$msg = json_decode($body,true);
		
		if($msg['s']==200){
			$msg['d'] = $this->_decryptToken($msg['d']);			
		}
		return $msg;
	}
	

	
}

function selfURL() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($protocol, 0, strpos($protocol, "/")).$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
} 
function hmac($key, $data, $hash="md5") {
    // RFC 2104 HMAC implementation for php. Hacked by Lance Rushing
    $b = 64;
    if (strlen($key) > $b)
        $key = pack("H*", call_user_func($hash, $key));
     $key = str_pad($key, $b, chr(0x00));
     $ipad = str_pad("", $b, chr(0x36));
     $opad = str_pad("", $b, chr(0x5c));
     $k_ipad = $key ^ $ipad ;
     $k_opad = $key ^ $opad;
    
     return call_user_func($hash, $k_opad . pack("H*", call_user_func($hash, $k_ipad . $data)));
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