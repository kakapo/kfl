<?php
class ProjectManage extends Model{
	private $db;
	function __construct(){
		$this->db = parent::dbConnect($GLOBALS ['gDataBase'] ['db_setting.db3']);
	}

	function createApp($app_name,$app_root){
		
		return $this->db->execute("insert into project (app_name,app_dir) values ('$app_name','$app_root')");
	}
	
	function getAppList(){
		return $this->db->getAll("select app_id ,app_name ,app_dir  from project");
	}
	
	function getAppById($id){
		return $this->db->getRow("select * from project where app_id='$id'");
	}
	
	function getAppByName($app_name){
		return $this->db->getRow("select * from project where app_name='$app_name'");
	}
	
	function deleteApp($app_name){
		return $this->db->execute("delete from project where app_name='$app_name'");
	}
	
	function getLastApp(){
		return $this->db->getRow("select * from project order by app_id desc limit 1 ");
	}
}