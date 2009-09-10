<?php
class SessionManage extends Model{
	private $db;
	function __construct(){
		
	}
	
	
	function getOnlineStats($type='',$slabid=0,$limit=100){
		$this->mMemcacheObj = new Memcache;
		if(is_array($GLOBALS['gSession']['memcached'])){
			foreach ($GLOBALS['gSession']['memcached'] as $server){
				$this->mMemcacheObj->addserver($server['mmhost'],$server['mmport']);
			}
		}
		return $this->mMemcacheObj->getExtendedStats($type,$slabid,$limit);
	}
	
	function getOnlineStatsDb(){
		$db = parent::dbConnect($GLOBALS['gSession']['database']);
		if($GLOBALS['gSession']['database']['type']=='sqlite') 
		$sql = "select sesskey,expiry,strftime('%s','now') as nowtime from session";
		if($GLOBALS['gSession']['database']['type']=='mysql') 
		$sql = "select sesskey,expiry,UNIX_TIMESTAMP() as nowtime from session";
		return $db->getAll($sql);
	}
	
	
	function getSessionByKey($server,$key){
		$this->mMemcacheObj = new Memcache;
		list($host,$port) = explode(":",$server);
		$this->mMemcacheObj->addserver($host,$port);
		return $this->mMemcacheObj->get($key);
	}
	function getSessionBySesskey($sesskey){
		$db = parent::dbConnect($GLOBALS['gSession']['database']);
		return $db->getOne("select data from session where sesskey='$sesskey'");
	}
}