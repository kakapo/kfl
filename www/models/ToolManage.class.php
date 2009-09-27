<?php
class ToolManage extends Model{
	public function __construct(){
		$this->db = parent::dbConnect($GLOBALS ["gDataBase"] ["db_kakapo"]);
	}
	private function _genPrefix(){
		$str = '0123456789abcdef';
		$tbl_prefix = array();
		$len = strlen($str);
		for($i=0;$i<$len;$i++){
			for($j=0;$j<$len;$j++){
				$tbl_prefix[] = $str[$i].$str[$j];
			}
		}
		return $tbl_prefix;
	}
	
	public function createTbl($multitbl=0){
		$prefixs = array('00');
		if($multitbl>0) $prefixs = $this->_genPrefix();
		//print_r($prefixs);die;
		$sql_index = "CREATE TABLE IF NOT EXISTS `user_index` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_email` varchar(64) NOT NULL,
  `user_name` varchar(16) NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `user_email` (`user_email`),
  KEY `user_name` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->db->execute($sql_index);
		
		$sql ="CREATE TABLE `__tblname__` ( `user_id` int(11) NOT NULL ,`user_email` varchar(64) NOT NULL,`user_password` char(32) NOT NULL,`user_name` varchar(16) NOT NULL,`user_nickname` varchar(12) NOT NULL,`user_realname` varchar(9) NOT NULL,`user_sex` tinyint(1) NOT NULL,
`user_state` tinyint(1) NOT NULL,`user_reg_time` int(11) NOT NULL,`user_reg_ip` varchar(16) NOT NULL,`user_lastlogin_time` int(11) NOT NULL,`user_lastlogin_ip` varchar(16) NOT NULL,PRIMARY KEY  (`user_id`),UNIQUE KEY `user_email` (`user_email`),UNIQUE KEY `user_name` (`user_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		foreach ($prefixs as $v){
			$tbl_name = "user_".$v;
			$new_sql = str_replace("__tblname__",$tbl_name,$sql);		
			$this->db->execute($new_sql);
		}
		return;
	}
}
?>