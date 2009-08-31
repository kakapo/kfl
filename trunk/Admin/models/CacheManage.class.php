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
		return $this->db->getAll("select * from memcached");
	}
	
	function getMemcachedByName($host){
		return $this->db->getOne("select host from memcached where host='$host'");
	}
	
	function getPageRule(){
		return $this->db->getAll("select * from pagerule");
	}
	
	function getPageRuleByName($rulename){
		return $this->db->getOne("select rule_name from pagerule where rule_name='$rulename'");
	}
	
	function savePageRule($rulename){
		return $this->db->execute("insert into pagerule (rule_name) values ('$rulename')");
	}
	
	function deletePageRule($pagerule){
		$res = $this->db->execute("delete from items where type='".$pagerule."'");
		if($res){
			return $this->db->execute("delete from pagerule where rule_name='$pagerule'");
		}else{
			return false;
		}
	}
}