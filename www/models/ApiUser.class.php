<?php
class ApiUser extends Model {
	private static $memcache;
	function __construct(){
		self::_setupMemcache();
	}
	/**
	 * 获取用户资料
	 *
	 * @param string $username
	 * @return mix false or array
	 */
	public static  function getUserByName($username){
		self::_setupMemcache();
		$user = '';//self::$memcache->get($username);
		if(!$user){
			$user = self::_getUserFromDb($username);
		}
		if($user)
			return $user;
		else
			return false;
	}
	public static function getUserById($userid){
		$indexdb = parent::dbConnect($GLOBALS ['gDataBase'] ['account_index']);
		$row = $indexdb->getRow ( "select user_db_key,user_name from user_index where user_id = '{$userid}'" );
		if($row && isset($GLOBALS ['gDataBase'] [$row['user_db_key']])){
			$account_db = parent::dbConnect ($GLOBALS ['gDataBase'] [$row['user_db_key']]);
			$user_arr = array();
			$user_arr = $account_db->getRow("select user_id,user_name,user_email,user_nickname,user_reg_time,user_status from user where user_id='{$row['user_id']}'");
			$user_arr['user_db_key'] = $row['user_db_key'];
			return $user_arr;
		}else{
			return false;
		}
	}
	private static function _getUserFromDb($username){
		$indexdb = parent::dbConnect($GLOBALS ['gDataBase'] ['account_index']);
		$row = $indexdb->getRow ( "select user_db_key,user_id from user_index where user_name = '{$username}'" );
		if($row && isset($GLOBALS ['gDataBase'] [$row['user_db_key']])){
			$account_db = parent::dbConnect ($GLOBALS ['gDataBase'] [$row['user_db_key']]);
			$user_arr = array();
			$user_arr = $account_db->getRow("select user_id,user_name,user_email,user_nickname,user_reg_time,user_status from user where user_id='{$row['user_id']}'");
			$user_arr['user_db_key'] = $row['user_db_key'];
			self::$memcache->set($username,$user_arr,0,0);
			return $user_arr;
		}else{
			return false;
		}
	}
	private static function _setupMemcache(){
		self::$memcache = new Memcache;
		if(is_array($GLOBALS ['gMemcacheServer'] ['UserIndex'])){
			foreach ($GLOBALS ['gMemcacheServer'] ['UserIndex'] as $server){
				self::$memcache->addserver($server['host'],$server['port'],$server['persistent'],$server['weight'],$server['timeout'],$server['retry_interval'],$server['status']);
			}
		}
	}
}

?>