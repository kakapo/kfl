<?php
include_once 'MonitorManage.class.php';
include_once 'SettingManage.class.php';
class monitor{
	public $mMonitorObj;
	public $mSettingObj;
	
	function __construct(){
		$this->mMonitorObj = new MonitorManage();
		$this->mSettingObj = new SettingManage();
	}
	function view_noticeset(){
		global $tpl;	
		$sets = array("subject"=>'From KFL',"receiver"=>'');
		$items = $this->mSettingObj->getSettings('noticeset');
		$tpl->assign('set_type','noticeset');
		
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
	
		$tpl->assign("monitorHadle",$GLOBALS['monitor']['monitorHandle']);
		if($GLOBALS['monitor']['monitorHandle']=='memcache'){
			$server_slabs = array();
			$server_slabs = $this->mMonitorObj->getOnlineStats("slabs");
			$all_stats = $this->mMonitorObj->getOnlineStats();
			
			$key_arr = array();
			foreach($server_slabs as $server=>$slabs){
				
				foreach ($slabs as $slab_id=>$slab_info){
					if(is_int($slab_id)){
						$items = $this->mMonitorObj->getOnlineStats("cachedump",$slab_id,0);
	
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
		
		if($GLOBALS['monitor']['monitorHandle']=='database'){
			$all_stats = $this->mMonitorObj->getOnlineStatsDb();
			$tpl->assign("all_stats",$all_stats);
		}
		
	}
	function view_viewmonitor(){
		if($GLOBALS['monitor']['monitorHandle']=='memcache'){
			$value  = $this->mMonitorObj->getMonitorByKey($_GET['server'],$_GET['key']);
		}
		if($GLOBALS['monitor']['monitorHandle']=='database'){
			$value  = $this->mMonitorObj->getMonitorBySesskey($_GET['key']);
		}
		echo "<pre>";
		print_r($value);
		die;
	}
	
	
}
?>