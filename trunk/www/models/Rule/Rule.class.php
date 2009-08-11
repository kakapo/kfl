<?php
date_default_timezone_set ( 'Asia/Shanghai' );
include_once 'Rule.config.php';
include_once 'RuleManage.class.php';

class Rule {

	private static $manage;
	private static $user_id;
	private static $user_name;
	/**
	 * 获取规则
	 * @param int $type 规则表ID
	 * @return mix
	 */
	private static function prepareRule($type) {
		if (! is_object ( self::$manage )) {
			self::$manage = new RuleManage ( );
		}

		$arr = self::$manage->getRule ( $type );
		if ($arr !== false) {
			$arr ['user_id'] = self::$user_id;
			$arr ['user_name'] = self::$user_name;
			return $arr;
		} else {
			return false;
		}

	}
	/**
	 * 记录活动次数
	 *
	 * @param int $rule_id 规则表ID
	 * @return bool
	 */
	private  static function addAction($rule_id) {
		$data = array ();
		$data ['uid'] = self::$user_id;
		$data ['rule_id'] = $rule_id;
		return self::$manage->saveAction ( $data );

	}
	/**
	 * 提交数据
	 * @param array $data
	 * @return boolen
	 */
	private  static function commitData(&$data) {

		self::$manage->db->beginTransaction ();
		if (! self::$manage->addCoin ( $data )) {
			self::$manage->db->rollBack ();
			return false;
		}
		if ($data ['score_value'] > 0) {
			if (! self::$manage->addScore ( $data )) {
				self::$manage->db->rollBack ();
				return false;
			}
		}

		if (! self::$manage->updateUserCoinScore ( self::$user_id, $data ['coin_value'], $data ['score_value'], $data ['op'] )) {
			self::$manage->db->rollBack ();
			return false;
		}
		//更新cookie里面的值
		$arr = self::$manage->getUserCoinScore ( self::$user_id );
		if ($arr !== false) {
			@setcookie ( 'IDOL_COIN_' . self::$user_name, $arr ['user_coin'], 0, '/', COOKIE_DOMAIN );
		}

		//增加系统消息
		if (! self::$manage->addSysMsg ( $data )) {
			//self::$manage->db->rollBack ();
			return false;
		}
		return self::$manage->db->commit ();

	}

	static function reg($user_id, $user_name) {
		self::$user_id = $user_id;
		self::$user_name = $user_name;
		$data = self::prepareRule ( $GLOBALS['gRule']['S_REG'] );
		if ($data !== false && $data ['rule_limit'] == 0) {
			$data ['coin_value'] = intval ( $data ['rule_coin'] );
			$data ['score_value'] = intval ( $data ['rule_score'] );
			$data ['rule_type'] = $data ['rule_name'];
			$data ['op'] = $data ['rule_op'];
			$data ['msg_coin_content'] = "恭喜，您已经成功注册为果动网的用户！系统赠送给你".$data ['coin_value']."个果果。";
			$data ['msg_score_content'] = "恭喜，您已经成功注册为果动网的用户！系统赠送给你".$data ['score_value']."积分。";
			self::commitData ( $data );
			return $data ['coin_value'];
		} else {
			return false;
		}
	}
	static function login($user_id, $user_name){
		self::$user_id = $user_id;
		self::$user_name = $user_name;
		$data = self::prepareRule ( $GLOBALS['gRule']['S_LOGIN'] );
		if ($data !== false) {
			$count = self::$manage->getActionCountToday ( self::$user_id, $GLOBALS['gRule']['S_LOGIN'] );
			if($count==0){
				$data ['coin_value'] = intval ( $data ['rule_coin'] );
				$data ['score_value'] = intval ( $data ['rule_score'] );
				$data ['rule_type'] = $data ['rule_name'];
				$data ['op'] = $data ['rule_op'];
				$today = date("m月d日");
				$data ['msg_coin_content'] = "恭喜，您[$today]登陆了网站！系统赠送给你".$data ['coin_value']."个果果。";
				$data ['msg_score_content'] = "恭喜，您[$today]登陆了网站！系统赠送给你".$data ['score_value']."积分。";

				 self::commitData ( $data );
				 self::addAction ( $GLOBALS['gRule']['S_LOGIN'] );
				 return true;
			}else{
				return false;
			}
		} else {
			return false;
		}

	}
	static function email($user_id, $user_name) {

		self::$user_id = $user_id;
		self::$user_name = $user_name;
		self::$manage = new RuleManage ( );
		$data = self::prepareRule ( S_EMAIL );
		if ($data !== false && $data ['rule_limit'] == 0) {
			$arr = self::$manage->getUserCoinScore ( self::$user_id );
			$data ['coin_value'] = $arr ['user_coin'] * 2;
			$data ['score_value'] = $arr ['user_score'] * 2;
			$data ['rule_type'] = $data ['rule_name'];
			$data ['op'] = $data ['rule_op'];
			$data ['msg_coin_content'] = "恭喜，您成功验证了Email！系统赠送给你".$data ['coin_value']."个果果。";
			$data ['msg_score_content'] = "恭喜，您成功验证了Email！系统赠送给你".$data ['score_value']."积分。";

			return self::commitData ( $data );
		}
		return false;
	}
	static function widget($user_id, $user_name) {
		self::$user_id = $user_id;
		self::$user_name = $user_name;
		self::$manage = new RuleManage ( );
		$count = self::$manage->getActionCount ( self::$user_id, $GLOBALS['gRule']['S_WIGET']  );
		if ($count == 0) {
			$data = self::prepareRule ( $GLOBALS['gRule']['S_WIGET']  );
			if ($data !== false && $data ['rule_limit'] == 0) {
				$data ['coin_value'] = $data ['rule_coin'];
				$data ['score_value'] = $data ['rule_score'];
				$data ['rule_type'] = $data ['rule_name'];
				$data ['op'] = $data ['rule_op'];
				$data ['msg_coin_content'] = "恭喜，您成功将Widget嵌入外网！系统赠送给你".$data ['coin_value']."个果果。";
				$data ['msg_score_content'] = "恭喜，您成功将Widget嵌入外网！系统赠送给你".$data ['score_value']."积分。";
				self::commitData ( $data );
				self::addAction ( $GLOBALS['gRule']['S_WIGET'] );
				return true;
			}
		}
		return false;
	}

	static function invite_reg($user_id, $user_name, $invitee) {
		self::$user_id = $user_id;
		self::$user_name = $user_name;
		$data = self::prepareRule ( $GLOBALS['gRule']['S_INV_REG'] );
		if ($data !== false) {
				$data ['coin_value'] = $data ['rule_coin'];
				$data ['score_value'] = $data ['rule_score'];
				$data ['rule_type'] = $data ['rule_name'];
				$data ['op'] = $data ['rule_op'];
				$data ['msg_coin_content'] = "通过您的邀请， $invitee 已经加入到果动网，您获得了".$data ['coin_value']."果果的奖励";
				$data ['msg_score_content'] =  "通过您的邀请， $invitee 已经加入到果动网，您获得了".$data ['score_value']."积分的奖励";
				self::commitData ( $data );
				return true;
		}
		return false;
	}
	static function invite_widget($user_id, $user_name,$invitee){
		self::$user_id = $user_id;
		self::$user_name = $user_name;
		self::$manage = new RuleManage ( );
		$count = self::$manage->getActionCount ( self::$user_id, $GLOBALS['gRule']['S_INV_WIDGET']  );
		if ($count == 0) {
			$data = self::prepareRule ( $GLOBALS['gRule']['S_INV_WIDGET']  );
			if ($data !== false && $data ['rule_limit'] == 0) {
				$data ['coin_value'] = $data ['rule_coin'];
				$data ['score_value'] = $data ['rule_score'];
				$data ['rule_type'] = $data ['rule_name'];
				$data ['op'] = $data ['rule_op'];
				$data ['msg_coin_content'] = "您邀请的用户 $invitee 成功把TA的widget形象嵌入到TA的空间内，您获得了".$data ['coin_value']."果果的奖励";
				$data ['msg_score_content'] ="您邀请的用户 $invitee 成功把TA的widget形象嵌入到TA的空间内，您获得了".$data ['score_value']."积分的奖励";
				self::commitData ( $data );
				self::addAction ( $GLOBALS['gRule']['S_INV_WIDGET'] );
				return true;
			}
		}
		return false;
	}

}

?>