<?php
class PassportModel extends Model {

	/**
	 *  获取所有禁词
	 * @access public
	 * @return array
	 **/
	private function _getBlockword() {
		$allblockwords = array ();
		$db  = $this->_connectMainDb();
		$sql = "select word From user_blockword";
		$result = $db->getOne ( $sql );
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
	public function checkUserName($username) {
		$db = $this->_connectAccountIndexDb();
		return $db->getOne ( "select user_id from user_index where user_name = '{$username}'" );
	}
	public function checkNickName($nickname){
		$db = $this->_connectAccountIndexDb();
		return $db->getOne ( "select user_id from user_index where user_nickname = '{$nickname}'" );
	}
	public function checkEmail($email){
		$db = $this->_connectAccountIndexDb();
		return $db->getOne ( "select user_id from user_index where user_email = '{$email}'" );
	}
	public function getRecommendUserName($name, $i) {
		$str = str_shuffle('abcdefghijklmnopqrstuvwxyz_0123456789');
		if ($i == 0) {
			$u = $name.'_'. rand ( 1, 99 );
		}
		if ($i == 1) {
			$u = $name . substr($str,rand ( 0, strlen($str) ),2);
		}
		if ($i == 2) {
			$u = $name . substr($str,rand ( 0, strlen($str) ),3);
		}
		if ($this->checkUserName ( $u ))
			$u = $this->getRecommendUserName ( $name, $i );
		return $u;
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
		//// 用户基本信息
		$indexdb = $this->_connectAccountIndexDb();
		$use_db_key= $indexdb->getOne("select db_key from account_db where db_status=1 limit 1");
		$res = $indexdb->execute("insert into user_index (`user_name`,`user_db_key`,`user_email`,`user_nickname`) values ('{$user['user_name']}','{$use_db_key}','{$user['user_email']}','{$user['user_nickname']}')");
		if(!$res) return false;

		$user_id = $indexdb->getOne("select last_insert_id() from user_index");
		if(!$user_id) return false;

		if(!isset($GLOBALS ['gDataBase'] [$use_db_key])){
			return false;
		}
		$account_db = parent::dbConnect ($GLOBALS ['gDataBase'] [$use_db_key]);
		$account_db->beginTransaction ();
		//user table
		$account_db->execute ( "insert into user (user_id,user_name,user_passwd,user_email,user_nickname,user_reg_time,user_status)
		values ('{$user_id}','{$user['user_name']}','" . $user ['user_passwd'] . "','{$user['user_email']}','{$user['user_nickname']}',UNIX_TIMESTAMP(),1)" );
		// user_extinfo table
		$account_db->execute ( "insert into user_extinfo (user_id,user_name,user_nickname,user_gender,user_province,user_city,user_town,city_code,town_code,province_code,user_coin,user_cash)
		values ('{$user_id}','{$user['user_name']}','{$user['user_nickname']}','{$user['user_gender']}','{$user['user_province']}','{$user['user_city']}','{$user['user_town']}','{$user['city_code']}','{$user['town_code']}','{$user['province_code']}','{$user['user_coin']}',0)" );
		// user_personinfo table
		$account_db->execute ( "insert into user_personinfo (user_id) value ('{$user_id}')" );

		if ($account_db->commit ()) {
			// email queue
			$db = $this->_connectMainDb();
			$db->execute ( "insert into email_queue (user_id,email,addtime) values ('{$user_id}','{$user['user_email']}',NOW())" );
			return array('user_id'=>$user_id,'db_key'=>$use_db_key);
		} else {
			$account_db->rollBack ();
			return false;
		}

	}
	public function updateMediumIDByUser($db_key,$user_id, $medium_id) {
		if(!isset($GLOBALS ['gDataBase'] [$db_key])) return false;
		$account_db = parent::dbConnect ($GLOBALS ['gDataBase'] [$db_key]);
		$sql = " UPDATE `user_extinfo` SET `ad_id` = '$medium_id' WHERE `user_id` =$user_id LIMIT 1";
		return $account_db->query ( $sql );
	}
	/**
	 *  新增邀请记录
	 * @param string $db_key
	 * @param integer $user_id
	 * @param string $sponsor
	 * @param string $user_name
	 * @param string $user_email
	 * @access public
	 * @return mix
	 **/
	public function addUserSponsor($db_key,$user_id, $sponsor, $user_name, $user_email, $user_nickname) {
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

			$account_db2 = parent::dbConnect ($GLOBALS ['gDataBase'] [$db_key]);
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
		$db = $this->_connectMainDb();
		$sql2 = "SELECT count(*) FROM `forget_pwd`  WHERE  `user_name` = '$username'    AND states=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		$count = $db->getOne ( $sql2 );
		//$count=$st->rowCount();
		if ($count > 1) {
			return 5; //
		}

		$sql2 = "UPDATE `forget_pwd`  SET  `states` =0 WHERE  `user_name` ='$username'";
		$db->query ( $sql2 );

		$code = $this->randomkeys ( 10 );
		$sql = "INSERT INTO `forget_pwd` ( `user_name` , `start_ts` , `rand_code` , `states`  )
								VALUES (  '$username', UNIX_TIMESTAMP(), '$code', '1' );";

		if ($db->query ( $sql ) < 1) {
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
		$db = $this->_connectMainDb();
		$sql = "SELECT count(*) FROM `forget_pwd`  WHERE `user_name` = '$username' AND `rand_code` = '$code' AND states=1 AND (UNIX_TIMESTAMP()-`start_ts`)< $validSec ";
		return $db->getOne ( $sql );

	}
	public function updateForgetPwd($username){
		$db = $this->_connectMainDb();
		$sql1 = "UPDATE `forget_pwd` SET states=0 WHERE user_name='$username'";
		$db->execute ( $sql1 );
	}



	/*************************************整合DZ论坛函数开始****************************************/

	/**
	 * Passport 加密函数
	 *
	 * @param		string		等待加密的原字串
	 * @param		string		私有密匙(用于解密和加密)
	 *
	 * @return	string		原字串经过私有密匙加密后的结果
	 */
	static public	function passport_encrypt($txt, $key) {

		// 使用随机数发生器产生 0~32000 的值并 MD5()
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));

		// 变量初始化
		$ctr = 0;
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for($i = 0; $i < strlen($txt); $i++) {
			// 如果 $ctr = $encrypt_key 的长度，则 $ctr 清
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			// $tmp 字串在末尾增加两位，其第一位内容为 $encrypt_key 的第 $ctr 位，
			// 第二位内容为 $txt 的第 $i 位与 $encrypt_key 的 $ctr 位取异或。然后 $ctr = $ctr + 1
			$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
		}

		// 返回结果，结果为 passport_key() 函数返回值的 base64 编码结果
		return base64_encode(PassportModel::passport_key($tmp, $key));

	}

	/**
	 * Passport 解密函数
	 *
	 * @param		string		加密后的字串
	 * @param		string		私有密匙(用于解密和加密)
	 *
	 * @return	string		字串经过私有密匙解密后的结果
	 */
	static public function passport_decrypt($txt, $key) {

		// $txt 的结果为加密后的字串经过 base64 解码，然后与私有密匙一起，
		// 经过 passport_key() 函数处理后的返回值
		$txt = PassportModel::passport_key(base64_decode($txt), $key);

		// 变量初始化
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for ($i = 0; $i < strlen($txt); $i++) {
			// $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
			// 与 $txt 的第 $i + 1 位取异或。然后 $i = $i + 1
			$tmp .= $txt[$i] ^ $txt[++$i];
		}

		// 返回 $tmp 的值作为结果
		return $tmp;

	}

	/**
	 * Passport 密匙处理函数
	 *
	 * @param		string		待加密或待解密的字串
	 * @param		string		私有密匙(用于解密和加密)
	 *
	 * @return	string		处理后的密匙
	 */
	static public function passport_key($txt, $encrypt_key) {

		// 将 $encrypt_key 赋为 $encrypt_key 经 md5() 后的值
		$encrypt_key = md5($encrypt_key);

		// 变量初始化
		$ctr = 0;
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for($i = 0; $i < strlen($txt); $i++) {
			// 如果 $ctr = $encrypt_key 的长度，则 $ctr 清
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			// $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
			// 与 $encrypt_key 的第 $ctr + 1 位取异或。然后 $ctr = $ctr +
			$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
		}

		// 返回 $tmp 的值作为结果
		return $tmp;

	}

	/**
	 * Passport 信息(数组)编码函数
	 *
	 * @param		array		待编码的数组
	 *
	 * @return	string		数组经编码后的字串
	 */
	static public	function passport_encode($array) {

		// 数组变量初始化
		$arrayenc = array();

		// 遍历数组 $array，其中 $key 为当前元素的下标，$val 为其对应的值
		foreach($array as $key => $val) {
			// $arrayenc 数组增加一个元素，其内容为 "$key=经过 urlencode() 后的 $val 值"
			$arrayenc[] = $key.'='.urlencode($val);
		}

		// 返回以 "&" 连接的 $arrayenc 的值(implode)，例如 $arrayenc = array('aa', 'bb', 'cc', 'dd')，
		// 则 implode('&', $arrayenc) 后的结果为 ”aa&bb&cc&dd"
		return implode('&', $arrayenc);

	}

	/*************************************整合DZ论坛函数结束****************************************/




}
?>