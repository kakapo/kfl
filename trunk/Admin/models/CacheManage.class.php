<?php
class CacheManage extends Model{
	private $db;
	function __construct(){
		$this->db = parent::dbConnect($GLOBALS ['gDataBase'] ['setting']);
	}
	

	function saveMemcached($host){		
		return $this->db->execute("insert into memcached (host) values ('$host')");
	}
	
	function deleteMemcached($host){
		
		$res = $this->db->execute("delete from items where type='".$host."'");
		if($res){
			return $this->db->execute("delete from memcached where host='$host'");
		}else{
			return false;
		}
	}
	
	
	function getMemcached(){
		return $this->db->getAll("select * from memcached",'',60);
	}
	
	function getMemcachedByName($host){
		return $this->db->getOne("select host from memcached where host='$host'");
	}
}