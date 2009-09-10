<?php
include_once 'SessionManage.class.php';
include_once 'SettingManage.class.php';
class session{
	public $mSessionObj;
	public $mSettingObj;
	
	function __construct(){
		$this->mSessionObj = new SessionManage();
		$this->mSettingObj = new SettingManage();
	}
	function view_basicset(){
		global $tpl;	
		$items = $this->mSettingObj->getSettings('basicset');
		$tpl->assign('set_type','basicset');
		$sets= array();
		if(is_array($items)){
			foreach($items as $item){
				$sets[$item['name']] = $item['value'];
			}
		}
		
		$tpl->assign('sets',$sets);
		//$tpl->assign('items',$items);
		
	}
	function view_stats(){
		global $tpl;
	
		$tpl->assign("sessionHadle",$GLOBALS['gSession']['sessionHandle']);
		if($GLOBALS['gSession']['sessionHandle']=='memcache'){
			$server_slabs = array();
			$server_slabs = $this->mSessionObj->getOnlineStats("slabs");
			$all_stats = $this->mSessionObj->getOnlineStats();
			
			$key_arr = array();
			foreach($server_slabs as $server=>$slabs){
				
				foreach ($slabs as $slab_id=>$slab_info){
					if(is_int($slab_id)){
						$items = $this->mSessionObj->getOnlineStats("cachedump",$slab_id,0);
	
						foreach($items as $ser=>$item){
							if(!empty($item)){						
								foreach($item as $key=>$v){
									if($server==$ser){
										$life_time = $v[1] - $all_stats[$server]['time'];
										//if($life_time>0){
											$key_arr[$key][0] = $life_time;
											$key_arr[$key][1] = $v[0];
										//}
									}
								}
							}
						}
					}		
				}
			
				asort($key_arr);
				$total_stats[$server] = $key_arr;
				$key_arr = array();
			}
			$tpl->assign("all_stats",$all_stats);
			$tpl->assign("total_stats",$total_stats);
		}
		
		if($GLOBALS['gSession']['sessionHandle']=='database'){
			$all_stats = $this->mSessionObj->getOnlineStatsDb();
			$tpl->assign("all_stats",$all_stats);
		}
		
	}
	function view_viewsession(){
		if($GLOBALS['gSession']['sessionHandle']=='memcache'){
			$value  = $this->mSessionObj->getSessionByKey($_GET['server'],$_GET['key']);
		}
		if($GLOBALS['gSession']['sessionHandle']=='database'){
			$value  = $this->mSessionObj->getSessionBySesskey($_GET['key']);
		}
		echo "<pre>";
		print_r($value);
		die;
	}
	
	
}
?>