<?php
class SettingManage extends Model{
	private $db;
	function __construct(){
		$this->db = parent::dbConnect($GLOBALS ['gDataBase'] ['db_setting.db3']);
	}
	
	function getSettings($type){
		$sql = "select * from items where type='$type'";
		$sth = $this->db->query($sql);
		$return = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)){
			$row['value'] = htmlspecialchars_decode($row['value']);
			$return [] = $row;
		}
		return $return;
	}
	function saveSettings($data,$type){
		$this->db->execute("delete from items where type='".$type."'");
		$str = '';
		foreach($data as $k=>$v){
			$str ="('".$v['name_']."','".htmlspecialchars(stripslashes($v['valu_']), ENT_QUOTES)."','".$type."')";
			$this->db->execute("insert into items (name,value,type) values $str");
		}
		return true;

	}
	function saveDatabase($dbname){		
		return $this->db->execute("insert into database (dbname) values ('$dbname')");
	}
	
	function deleteDatabase($dbname){
		
		$res = $this->db->execute("delete from items where type='".$dbname."'");
		if($res){
			return $this->db->execute("delete from database where dbname='$dbname'");
		}else{
			return false;
		}
	}
	
	
	function getDatabase(){
		return $this->db->getAll("select * from database");
	}
	
	function getDatabaseByName($dbname){
		return $this->db->getOne("select dbname from database where dbname='$dbname'");
	}
}