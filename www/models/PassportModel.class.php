<?php
class PassportModel extends Model {
	public function __construct(){
		$this->db = parent::dbConnect($GLOBALS ["gDataBase"] ["db_kakapo"]);
	}
	/**
	 *  获取所有禁词
	 * @access public
	 * @return array
	 **/
	private function _getBlockword() {
		$allblockwords = array ();
		
		$sql = "select word From user_blockword";
		$result = $this->db->getOne ( $sql );
		$arr = explode ( '|', $result );
		foreach ( $arr as $row ) {
			$allblockwords [] = $row;
		}
		return $allblockwords;
	}

	private function _getTblPrefix($user){
		$tb_prefix = '00';
		if(isset($multi_tbl) && $multi_tbl==true) $tb_prefix = substr(md5($user),0,2);
		return $tb_prefix;
	}

	public function checkUser($user) {	
		return $this->db->getOne ( "select user_id from user_index where user = '{$user}'" );
	}

	public function updateUser($item,$user_id,$user){
		$tb_prefix = $this->_getTblPrefix($user);	
		$this->db->update($item,"user_".$tb_prefix," user_id=".$user_id);
	}
	
	public function getUserById($user_id,$user){
		$tb_prefix = $this->_getTblPrefix($user);
		
		return $this->db->getRow("select * from user_$tb_prefix where user_id='$user_id'");
	}
	public function getUser($user){
		$tb_prefix = $this->_getTblPrefix($user);
		return $this->db->getRow("select * from user_index where user='$user'");
	}
	
	/**
	 *  判断是否为禁词
	 * @param string $newword
	 * @access public
	 * @return boolen
	 **/
	public function isBlockword($newword) {
		$allblockwords = $this->_getBlockword ();
		//print_r($allblockwords);
		$n = 0;
		for($i = 0, $c = count ( $allblockwords ); $i < $c; $i ++) {
			//如果有*号表示要匹配查询
			if (empty ( $allblockwords [$i] ))
				break;
			$res = strpos ( $allblockwords [$i], "*" );
			if ($res !== false) {
				//$res = strripos($newword, $blockword);
				$blockword = str_replace ( "*", "(.*?)", $allblockwords [$i] );
				$pattern = "/" . $blockword . "/i";

				if (preg_match ( $pattern, $newword )) {
					$n ++;
				}
			} else {
				if ($newword == $allblockwords [$i]) {
					$n ++;
				}
			}
		}
		if ($n > 0)
			return true;
		else
			return false;
	}
	/**
	 * 注册新用户
	 *@param array $user
	 *@return boolen
	 */
	public function createNewUser($user) {
	
		$res = $this->db->execute("insert into user_index (`user`) values ('{$user['user']}')");
		if(!$res) return false;

		$user_id = $this->db->getOne("select last_insert_id() from user_index");
		if(!$user_id) return false;
		
		$tb_prefix = $this->_getTblPrefix($user['user']);
	
		
		//user table
		$this->db->execute ( "insert into user_$tb_prefix (user_id,user,user_password,user_email,user_nickname,user_realname,user_sex,user_state,user_reg_time,user_reg_ip,user_lastlogin_time,user_lastlogin_ip,user_question,user_answer)
		values ('{$user_id}','{$user['user']}','" . $user ['user_password'] . "','{$user['user_email']}','{$user['user_nickname']}','{$user['user_realname']}','{$user['user_sex']}',1,UNIX_TIMESTAMP(),'{$user['user_reg_ip']}',UNIX_TIMESTAMP(),'{$user['user_reg_ip']}','{$user['user_question']}','{$user['user_answer']}')" );
			
		return $user_id;
		

	}
	
	public function addForgetPwd($user) {

		$validSec = 3600;
		
		$sql = "SELECT count(*) FROM `forget_pwd`  WHERE  `user` = '$user'  AND state=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		$count = $this->db->getOne ( $sql );
		if ($count > 1) {
			return 5; //
		}
		//将未及时重置密码的请求改为无效状态
		$sql = "delete from `forget_pwd`  WHERE  `user` ='$user' and `state`=0 ";
		$this->db->execute ( $sql );

		$code = md5(microtime().$user);
		$sql = "INSERT INTO `forget_pwd` ( `user` , `start_ts` , `code` , `state`  )
								VALUES (  '$user', UNIX_TIMESTAMP(), '$code', '1' );";

		if (false==$this->db->execute ( $sql )) {
			return 2;
		} else {
			return $code;
		}

	}

	/**
	 * 检查忘记密码是否存在于用户
	 *
	 * @param string $code
	 * @param string $username
	 * @return array
	 */
	public function checkForget($code) {
		if (empty ( $code ) ) {
			return 0;
		}
		$validSec = 3600;
		
		$sql = "SELECT * FROM `forget_pwd`  WHERE `code` = '$code' AND state=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		return $this->db->getRow ( $sql );

	}
	public function updatePassByUser($user,$pwd){
		$tb_prefix = $this->_getTblPrefix($user);
		$sql = "Update  `user_$tb_prefix` set user_password='$pwd' WHERE user='$user' ";
		
		return $this->db->execute ( $sql );
	}
	public function updateForgetPwd($user){	
		$sq1 = "UPDATE `forget_pwd` SET state=0 WHERE user='$user'";
		$this->db->execute ( $sq1 );
	}


}
?>