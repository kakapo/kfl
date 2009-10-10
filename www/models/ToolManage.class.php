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
  `user` varchar(64) NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->db->execute($sql_index);
		
		$sql ="CREATE TABLE `__tblname__` ( `user_id` int(11) NOT NULL ,`user` varchar(64) NOT NULL,`user_password` char(32) NOT NULL,`user_email` varchar(64) NOT NULL,`user_nickname` varchar(12) NOT NULL,`user_realname` varchar(9) NOT NULL,`user_sex` tinyint(1) NOT NULL,
`user_state` tinyint(1) NOT NULL,`user_reg_time` int(11) NOT NULL,`user_reg_ip` varchar(16) NOT NULL,`user_lastlogin_time` int(11) NOT NULL,`user_lastlogin_ip` varchar(16) NOT NULL,`user_question` VARCHAR( 128 ) NOT NULL,`user_answer` VARCHAR( 30 ) NOT NULL, PRIMARY KEY  (`user_id`),UNIQUE KEY `user` (`user`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		foreach ($prefixs as $v){
			$tbl_name = "user_".$v;
			$new_sql = str_replace("__tblname__",$tbl_name,$sql);		
			$this->db->execute($new_sql);
		}
		return;
	}
	
	public function generateKey(){
		$tmp = array_merge(range(0, 9), range('A', 'Z'));
	    $key = '';
	    for ($i = 0; $i < 16; $i++) {
	        $key .= $tmp[mt_rand(0, 35)];
	    }
	    return md5($key);    
	}
	
	public function addNewClient($arr){
		$sql = "insert into `client` (`domain`, `private_key`) values (?,?)";
		return $this->db->execute($sql,array($arr['domain'],$arr['key']));
	}
	
	public function getClientByDomain($domain){
		return $this->db->getRow("select * from client where domain='$domain'");
	}
	
}

?>