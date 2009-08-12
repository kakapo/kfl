<?php
class Authenticate
{
	var $mAuthPrivs;
	var $mUserPrivs;
	var $mLoginUser = array();
	var $mLoginUserId;
	
	/**
     * initialize authen obj
     * @param $name difine component main object name
     * @return void
     */
	function __construct($name){
	    $GLOBALS[$name] = $this;
	}
	/**
     * setLoginUser
     * @param array $login_user
     * @param int $login_userid
     * @return void
     */
	function setLoginUser($login_user,$login_userid){
		$this->mLoginUser = $login_user;
		$this->mLoginUserId = $login_userid;
	}
	/**
     * isLogin interface
     * @param string $priv
     * @access public
     * @return mix
     */	
	function isLogin(){
		if(empty($this->mLoginUser)){
			show_message("请先登录!");
//			echo '<SCRIPT>
//       				 setTimeout("parent.window.location.replace(\"admin.php?action=user&view=login\")",1000);
//        		   </SCRIPT>';
			return false;
		}else {
			return true;
		}
	}
	/**
     * setLoginUserPrivs interface
     * @access private
     * @return mix
     */	
	function _setLoginUserPrivs(){
		$this->mUserPrivs = $this->mLoginUser['Privs'];
	}
	/**
     * isAllowed interface
     * @param string $priv
     * @access public
     * @return mix
     */	
	function isAllowed($priv){
		if(!$this->isLogin()){
			return false;
		}
		$this->_setLoginUserPrivs();
		$user_id = $this->mLoginUserId;
		if($user_id==1){
			return true;
		}
		$allows = array();
		foreach ($this->mUserPrivs as $k=>$v){
			if($v==1){
				$allows[] = $k;
			}
		}
		if(!in_array($priv,$allows)){
			return false;
		}else{
			return true;
		}
	}
	/**
     * setAuthPrivs 
     * @access public
     * @return array
     */	
	function setAuthPrivs($app_controller){
	
		$d = dir($app_controller);
		while (false !== ($entry = $d->read())) {
			if($entry!="." && $entry!='..' && substr($entry,0,1)!='.'){
				 $len = strlen(strstr($entry,'.'));
				 $class_names[] = substr($entry,0,-$len);
				 include_once($app_controller."/".$entry);
			}
		}
		$d->close();
		
	    if(is_array($class_names)){
			foreach ($class_names as $class){
				
				$methods = get_class_methods($class);
				if(is_array($methods)){
					foreach ($methods as $method){
						if(preg_match("/^view\_|op\_/i",$method)){
							
							$method_arr[] = $class.".".str_replace("_",".",$method);
						}
					}
				
					$this->mAuthPrivs[] = array($class,$method_arr);
					unset($method_arr);
				}
			}
		}
	}
	/**
     * getAuthPrivs 
     * @access public
     * @return array
     */	
	function getAuthPrivs(){
		return $this->mAuthPrivs;
	}
}
?>