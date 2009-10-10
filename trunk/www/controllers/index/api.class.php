<?php
class api{
	public $_private_key;
	function view_defaults(){
			
		$user = authenticate();
		print_r($user);
	
	}
	
	function verifySign($domain,$text,$sign){
		require_once 'ToolManage.class.php';		
		$toolManage = new ToolManage();
		$client = $toolManage->getClientByDomain($domain);
		$this->_private_key = $client['private_key'];
		if (hmac($this->_private_key,$text,'sha1')==$sign) {
			return true;	  
		}else{
			return false;
		}
	}
	function encryptToken($data){
		return encrypt($data,$this->_private_key);
	}
	
	function view_islogin(){
		$user = $_GET['user'];
		$sign = $_GET['sign'];
		$domain = $_GET['domain'];
		$redirect = isset($_GET['redirect'])?$_GET['redirect']:0;
		$return = isset($_GET['return'])?urldecode($_GET['return']):'';
		
		if($redirect){
			if($this->verifySign($domain,md5($user.$domain),$sign)){
				$userinfo = authenticate();
				if($userinfo && $userinfo['user']==$user){
					if(strpos($return,'?')!==false) {
						$return .= '&ticket='.$userinfo['ticket'];
					}else{
						$return .= '?ticket='.$userinfo['ticket'];
					}
					header("Location:".$return);
				}else{
					header("Location:".$GLOBALS ["gSiteInfo"]['www_site_url']."/index.php?action=passport&view=login&forward=".urlencode($return));
				}
				
			}else{
				die("Signature Invalid!");
			}
			
		}else{
						
			if($this->verifySign($domain,md5($user.$domain),$sign)){	
				require_once 'PassportModel.class.php';
				$pass = new PassportModel();
				$ticket = $pass->getTicketByUser($user);
				
				if($ticket){
					$msg['s'] = 200; 
				    $msg['m'] = "success!"; 
				    $msg['d'] = $ticket; 
				}else{
					 $msg['s'] = 300; 
					 $msg['m'] = "Not Login!"; 
			   		 $msg['d'] = $GLOBALS ["gSiteInfo"]['www_site_url']."/index.php/passport/login"; 
				}
				
			}else{
			   $msg['s'] = 400; 
			   $msg['m'] = "Signature Invalid!"; 
			   $msg['d'] = ''; 
			}
			
			
			json_output($msg);
		}
		
	}
	
	function view_getuser(){
		$ticket = $_GET['ticket'];
		$sign = $_GET['sign'];
		$domain = $_GET['domain'];
		
		if($this->verifySign($domain,md5($ticket.$domain),$sign)){
			require_once 'PassportModel.class.php';
			$pass = new PassportModel();
			$data = $pass->getDataByTicket($ticket);
			if($data){
				$msg['s'] = 200; 
			    $msg['m'] = "success!"; 
			    $msg['d'] = $this->encryptToken($data); 
			}else{
				$msg['s'] = 300; 
				$msg['m'] = "Please  Relogin!"; 
		   		$msg['d'] = $GLOBALS ["gSiteInfo"]['www_site_url']."/index.php/passport/login";
			}
			
		}else{
		   $msg['s'] = 400; 
		   $msg['m'] = "Signature Invalid!"; 
		   $msg['d'] = ''; 
		}
		
		json_output($msg);
	}
}

?>