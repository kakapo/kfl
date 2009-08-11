<?php

class RuleManage extends Model {

	public $db;
	private $_indexDb;
	private $_accountDb;

	/**
	 * 连接数据库
	 *
	 * @param array $faDB
	 * @return bool
	 */
	function __construct() {
		$this->db = parent::dbConnect();
	}
	/**
	 * 查找不同类型的奖励分数
	 *
	 * @param array $faDB
	 * @param int $rule_id
	 * @return int
	 */
	function getRule($rule_id) {
		return $this->db->getRow ( "select rule_name,rule_intro,rule_coin,rule_score,rule_limit,rule_op from rule where rule_id = '" . $rule_id . "'" );
	}
	/**
	 * 添加货币
	 *
	 * @param array $data 添加数据
	 * @return bool
	 */
	function addCoin(&$data) {

		if ($data ['op'] == 'del')
			$coin = - $data ['coin_value'];
		if ($data ['op'] == 'add')
			$coin = $data ['coin_value'];
		$sql = "insert into `rule_coin_log` (user_id,coin_type,coin_value,coin_time) values (?,?,?,UNIX_TIMESTAMP())";
		$stmt = $this->db->prepare ( $sql );

		$stmt->bindParam ( 1, $data ['user_id'] );
		$stmt->bindParam ( 2, $data ['rule_type'] );
		$stmt->bindParam ( 3, $coin );
		return $stmt->execute ();
	}
	function addScore(&$data) {
		$sql = "insert into `rule_score_log` (user_id,score_type,score_value,score_time) values (?,?,?,UNIX_TIMESTAMP())";
		$stmt = $this->db->prepare ( $sql );
		$stmt->bindParam ( 1, $data ['user_id'] );
		$stmt->bindParam ( 2, $data ['rule_type'] );
		$stmt->bindParam ( 3, $data ['score_value'] );
		return $stmt->execute ();
	}
	/**
	 * 得到行为数量
	 *
	 * @param int $user_id 用户ID
	 * @param str $rule_name 规则名字简写
	 * @return unknown
	 */
	function getActionCount($user_id, $rule_id) {
		return $this->db->getOne ( "select count(*) as c from rule_action where uid = '{$user_id}' and rule_id = '{$rule_id}'" );
	}
	/**
	 * 得到行为数量
	 *
	 * @param int $user_id 用户ID
	 * @param str $rule_name 规则名字简写
	 * @return unknown
	 */
	function getActionCountToday($user_id, $rule_id) {
		return $this->db->getOne ( "select count(*) as c from rule_action where uid = '{$user_id}' and rule_id = '{$rule_id}' and time > UNIX_TIMESTAMP(SUBDATE(CURDATE(),INTERVAL 1 DAY))" );
	}
	/**
	 * 添加行为数量
	 *
	 * @param array $data
	 * @param str $tbl 表名
	 * @return bool
	 */
	function saveAction(&$data) {
		$ip = getip();
		$sql = "insert into `rule_action` (uid,rule_id,time,ip) values (?,?,UNIX_TIMESTAMP(),?)";
		$stmt = $this->db->prepare ( $sql );
		$stmt->bindParam ( 1, $data ['uid'] );
		$stmt->bindParam ( 2, $data ['rule_id'] );
		$stmt->bindParam ( 3, $ip );
		return $stmt->execute ();
	}


	function addSysMsg(&$data) {
		$this->_connectAccountDb($data ['user_id']);
		if ($data ['coin_value'] != 0) {
			$sql = "insert into `user_msg` (user_id,msg_title,msg_content,msg_time) values (?,?,?,UNIX_TIMESTAMP())";
			$stmt = $this->_accountDb->prepare ( $sql );
			//$content = "您因为" . $data ['rule_intro'] . $op . $data ['coin_value'] . '个果果.';
			$title = "果果奖励通知";
			$stmt->bindParam ( 1, $data ['user_id'] );
			$stmt->bindParam ( 2, $title );
			$stmt->bindParam ( 3, $data['msg_coin_content'] );
			$stmt->execute ();
		}
		if ($data ['score_value'] > 0) {
			$sql = "insert into `user_msg` (user_id,msg_title,msg_content,msg_time) values (?,?,?,UNIX_TIMESTAMP())";
			$stmt = $this->_accountDb->prepare ( $sql );
			$title = "积分奖励通知";
			$stmt->bindParam ( 1, $data ['user_id'] );
			$stmt->bindParam ( 2, $title );
			$stmt->bindParam ( 3, $data['msg_score_content'] );
			$stmt->execute ();
		}

		return true;
	}


	//===================== 用户财产方法 =================================
	private  function _connectAccountIndexDb(){
		$this->_indexDb =  parent::dbConnect ($GLOBALS ['gDataBase'] ['account_index']);
	}
	private function _connectAccountDb($user_id){
		$this->_connectAccountIndexDb();
		$use_db_key= $this->_indexDb->getOne("select user_db_key from user_index where user_id=$user_id limit 1");
		if(isset($GLOBALS ['gDataBase'] [$use_db_key])){
			$this->_accountDb = parent::dbConnect ($GLOBALS ['gDataBase'] [$use_db_key]);
		}
	}
	/**
	 * 更新用户虚拟货币数量
	 *@param float $coins
	 *@param string $op 操作选项为add or del
	 *@return mix if true return coins, else return false
	 */
	function updateUserCoinScore($user_id, $coin, $score, $op) {
		$this->_connectAccountDb($user_id);
		$sql = "";
		if ($op == 'add') {
			$sql = "update user_extinfo set user_coin=user_coin+'{$coin}' , user_score=user_score+'{$score}' where user_id='{$user_id}'";
		} else if ($op == 'del') {
			$sql = "update user_extinfo set user_coin=user_coin-'{$coin}', user_score=user_score-'{$score}' where user_id='{$user_id}'";
		}

		if (! empty ( $sql )) {
			return $this->_accountDb->execute ( $sql );
		}

	}
	function getUserCoinScore($user_id) {
		$this->_connectAccountDb($user_id);
		return $this->_accountDb->getRow ( "select user_coin,user_score from user_extinfo where user_id='{$user_id}'" );
	}

}

?>