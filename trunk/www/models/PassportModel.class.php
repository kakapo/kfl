<?php
class PassportModel extends Model {

	/**
	 *  获取所有禁词
	 * @access public
	 * @return array
	 **/
	private function _getBlockword() {
		$allblockwords = array ();
		$this->db  = $this->_connectMainDb();
		$sql = "select word From user_blockword";
		$result = $this->db->getOne ( $sql );
		$arr = explode ( '|', $result );
		foreach ( $arr as $row ) {
			$allblockwords [] = $row;
		}
		return $allblockwords;
	}
	private  function _connectAccountIndexDb(){
		return parent::dbConnect ($GLOBALS ['gDataBase'] ['account_index']);
	}
	private  function _connectMainDb(){
		return parent::dbConnect ();
	}
	private function _getTblPrefix(){
		$tb_prefix = '00';
		if(isset($multi_tbl) && $multi_tbl==true) $tb_prefix = substr(md5($user['user_email']),0,2);
		return $tb_prefix;
	}
	public function __construct(){
		$this->db = parent::dbConnect($GLOBALS ["gDataBase"] ["db_kakapo"]);
	}
	public function checkUserName($username) {	
		return $this->db->getOne ( "select user_id from user_index where user_name = '{$username}'" );
	}
	public function getUserByEmail($email){
		return $this->db->getRow( "select * from user_index where user_email='{$email}'");
	}
	public function updateUser($item,$user_id){
		$tb_prefix = $this->_getTblPrefix();	
		$this->db->update($item,"user_".$tb_prefix," user_id=".$user_id);
	}
	
	public function getUserById($user_id,$email){
		$tb_prefix = $this->_getTblPrefix();
		return $this->db->getRow("select * from user_$tb_prefix where user_id='$user_id'");
	}
	public function checkEmail($email){
		
		return $this->db->getOne ( "select user_id from user_index where user_email = '{$email}'" );
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
	
		$res = $this->db->execute("insert into user_index (`user_name`,`user_email`) values ('{$user['user_name']}','{$user['user_email']}')");
		if(!$res) return false;

		$user_id = $this->db->getOne("select last_insert_id() from user_index");
		if(!$user_id) return false;
		
		$tb_prefix = $this->_getTblPrefix();
	
		
		//user table
		$this->db->execute ( "insert into user_$tb_prefix (user_id,user_email,user_password,user_name,user_nickname,user_realname,user_sex,user_state,user_reg_time,user_reg_ip,user_lastlogin_time,user_lastlogin_ip)
		values ('{$user_id}','{$user['user_email']}','" . $user ['user_password'] . "','{$user['user_name']}','{$user['user_nickname']}','{$user['user_realname']}','{$user['user_sex']}',1,UNIX_TIMESTAMP(),'{$user['user_reg_ip']}',UNIX_TIMESTAMP(),'{$user['user_reg_ip']}')" );
			
		return $user_id;
		

	}
	

	/**
	 *  新增邀请记录
	 * @param string $this->db_key
	 * @param integer $user_id
	 * @param string $sponsor
	 * @param string $user_name
	 * @param string $user_email
	 * @access public
	 * @return mix
	 **/
	public function addUserSponsor($db,$user_id, $sponsor, $user_name, $user_email, $user_nickname) {
		include_once('ApiUser.class.php');

		$sponsor_user = ApiUser::getUserByName($sponsor);
		if ($sponsor_user !== false) {
			//为发起者添加成功邀请记录
			$sql = "insert into user_invitee (`user_id`, `invitee_name`,`invitee_email`, `invitee_user_id`,`invitee_regtime`) values (?,?,?,?,UNIX_TIMESTAMP())";
			$account_db = parent::dbConnect ($GLOBALS ['gDataBase'] [$sponsor_user['user_db_key']]);
			$res = $account_db->execute ( $sql, array ($sponsor_user['user_id'], $user_name, $user_email, $user_id ) );

			include_once("Rule/Rule.class.php");
			$reg_coin = Rule::invite_reg($sponsor_user['user_id'],$sponsor,$user_nickname);
//			为发起者添加好友请求
//			$sql = "insert into user_friend_ask (user_id,ask_user_id,ask_user_name,ask_user_nickname,ask_time) values (?,?,?,?,now())";
//			$this->db->execute($sql, array( $sponsor_user['user_id'],$user_id, $user_name, $user_nickname));

			$account_db2 = parent::dbConnect ($GLOBALS ['gDataBase'] [$this->db_key]);
			// 为受邀请者添加好友
			$user_gender=$account_db2->getOne("select user_gender from user_extinfo where user_id=".$sponsor_user['user_id']);
			$sql = "insert into user_friend (user_id,friend_id,friend_name,friend_nickname,friend_gender,friend_order,friend_pass) values (?,?,?,?,?,0,0)";
			$account_db2->execute ( $sql, array ($user_id, $sponsor_user ['user_id'], $sponsor_user ['user_name'], $sponsor_user ['user_nickname'], $user_gender ) );

			$account_db2->execute("update user_extinfo set user_sponsor='$sponsor' where user_id=$user_id");

			if ($res) {
				return $sponsor_user ['user_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function checkForgetEmail( $user_name,$email) {
		if (empty ( $email ) or empty ( $user_name )) {
			return 0;
		}
		$indexdb = $this->_connectAccountIndexDb();
		$sql = "select count(*) from user_index where user_email = '{$email}' and user_name='{$user_name}'";
		return $indexdb->getOne ( $sql );
	}
	public function addForgetPwd($username) {

		if (empty ( $username )) {
			return 2;
		}
		$validSec = $GLOBALS ['account'] ['urlValidSecond'];
		$this->db = $this->_connectMainDb();
		$sql2 = "SELECT count(*) FROM `forget_pwd`  WHERE  `user_name` = '$username'    AND states=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		$count = $this->db->getOne ( $sql2 );
		//$count=$st->rowCount();
		if ($count > 1) {
			return 5; //
		}

		$sql2 = "UPDATE `forget_pwd`  SET  `states` =0 WHERE  `user_name` ='$username'";
		$this->db->query ( $sql2 );

		$code = $this->randomkeys ( 10 );
		$sql = "INSERT INTO `forget_pwd` ( `user_name` , `start_ts` , `rand_code` , `states`  )
								VALUES (  '$username', UNIX_TIMESTAMP(), '$code', '1' );";

		if ($this->db->query ( $sql ) < 1) {
			return 2;
		} else {
			return $code;
		}

	}
	public function randomkeys($length) {
		$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
		$key = $pattern {rand ( 0, 35 )};
		for($i = 1; $i < $length; $i ++) {
			$key .= $pattern {rand ( 0, 35 )};
		}
		return $key;
	}
	/**
	 * 检查忘记密码是否存在于用户
	 *
	 * @param string $code
	 * @param string $username
	 * @return int
	 */
	public function checkForget($code, $username) {
		if (empty ( $code ) or empty ( $username )) {
			return 0;
		}
		$validSec = $GLOBALS ['account'] ['urlValidSecond'];
		$this->db = $this->_connectMainDb();
		$sql = "SELECT count(*) FROM `forget_pwd`  WHERE `user_name` = '$username' AND `rand_code` = '$code' AND states=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		return $this->db->getOne ( $sql );

	}
	public function updateForgetPwd($username){
		$this->db = $this->_connectMainDb();
		$sql1 = "UPDATE `forget_pwd` SET states=0 WHERE user_name='$username'";
		$this->db->execute ( $sql1 );
	}


}
?>