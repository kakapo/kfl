<?php
header('Cache-Control: no-cache');
include_once (dirname ( __file__ ) . "/config/config.ini.php");
$s = isset($_GET['s'])?$_GET['s']:'';

switch ($s){
	case 'click':
		echo curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url'].'/itemhit.php?type='.$_GET['type'].'&itemid='.$_GET['itemid'].'&item='.urlencode($_GET['item']));
		break;
	case 'used':
		echo curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url'].'/itemuse.php?type='.$_GET['type'].'&itemidstr='.$_GET['itemidstr']);
		break;
	case 'keyadd':
		echo curl_get_content($GLOBALS ['gSiteInfo'] ['stats_site_url'].'/widget_keyadd_log.php?user='.$_GET['user'].'&site='.$_GET['site']."&account=".$_GET['account']."&result=".$_GET['result']);
}

function curl_get_content($url){
	if(function_exists('curl_init')){
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 1 );
		$result = curl_exec ( $ch );
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close ( $ch );
		if($http_code=='200')
			return $result;
		else
			return false;
	}else{
		return false;
	}
}

?>