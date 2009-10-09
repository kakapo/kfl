<?php
class api{
	function view_defaults(){
			
		$user = authenticate();
		print_r($user);
	
	}
	function verifySign($client,$text,$sign){
		require_once 'Crypt/RSA.php';
		$rsa_obj = new Crypt_RSA(array(), 'BCMath', 'check_error');
		// check signing/sign validating
		$params = array(
		    'public_key' => Crypt_RSA_Key::fromString($client['public_key'],'BCMath'),
		    'private_key' => Crypt_RSA_Key::fromString($client['private_key'],'BCMath')
		);
		$rsa_obj->setParams($params);
		if (!$rsa_obj->validateSign($text, $sign)) {
			return true;
		  
		}else{
			return false;
		}
	}
	function encryptToken($client,$arr){
		require_once 'Crypt/RSA.php';
		$rsa_obj = new Crypt_RSA(array(), 'BCMath', 'check_error');
	
		// check signing/sign validating
		$params = array(
		    'enc_key' => Crypt_RSA_Key::fromString($client['public_key'],'BCMath')    
		);
		$rsa_obj->setParams($params);
		
		return $rsa_obj->encrypt($arr);
	}
	function view_islogin(){
		$user = $_GET['user'];
		$sign = $_GET['sign'];
		$domain = $_GET['domain'];
		require_once 'ToolManage.class.php';
		
		
		$toolManage = new ToolManage();
		$client = $toolManage->getClientByDomain($domain);
		
		
		if($this->verifySign($client,md5($user.$domain),$sign)){	
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
	
	function view_getuser(){
		$ticket = $_GET['ticket'];
		$sign = $_GET['sign'];
		$domain = $_GET['domain'];
		require_once 'ToolManage.class.php';
		
		$toolManage = new ToolManage();
		$client = $toolManage->getClientByDomain($domain);
		
		
		if($this->verifySign($client,md5($ticket),$sign)){
			require_once 'PassportModel.class.php';
			$pass = new PassportModel();
			$data = $pass->getDataByTicket($ticket);
			if($data){
				$msg['s'] = 200; 
			    $msg['m'] = "success!"; 
			    $msg['d'] = encrypt($data,$ticket); 
			}else{
				$msg['s'] = 400; 
				$msg['m'] = "Please  Relogin!"; 
		   		$msg['d'] = '';
			}
			
		}else{
		   $msg['s'] = 400; 
		   $msg['m'] = "Signature Invalid!"; 
		   $msg['d'] = ''; 
		}
		
		json_output($msg);
	}
}
function check_error(&$obj)
{
    if ($obj->isError()) {
        $error = $obj->getLastError();
        switch ($error->getCode()) {
        case CRYPT_RSA_ERROR_WRONG_TAIL :
            // nothing to do
            break;
        default:
            // echo error message and exit
            echo 'error: ', $error->getMessage();
            exit;
        }
    }
}
?>