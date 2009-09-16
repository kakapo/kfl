<?php
	
//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
	define("APP_STATUS", "online");
	define("APP_LANG", "ch");
	define("APP_TEMP_DIR", APP_DIR."/tmp/");
	define("KFL_DIR", "D:/GoogleCodeKFL/KFL");
	
//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
		
	$GLOBALS ["gSiteInfo"] ["title"] =  "KFL--项目开发框架及在线管理平台";
	$GLOBALS ["gSiteInfo"] ["vision"] =  "V2.0.0";
	$GLOBALS ["gSiteInfo"] ["www_site_url"] =  "http://www.kfl.net/Admin";
	$GLOBALS ["gSiteInfo"] ["webcharset"] =  "utf-8";
	$GLOBALS ["gSiteInfo"] ["site_name"] =  "KFL项目开发框架及在线管理平台";
	
//////////////////////////////////////////////////////
//				Email   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gEmail"] ["smtp_host"] =  "smtp.163.com";
	$GLOBALS ["gEmail"] ["smtp_account"] =  "cuckoolaugh@163.com";
	$GLOBALS ["gEmail"] ["smtp_pass"] =  "810600";
	$GLOBALS ["gEmail"] ["smtp_from"] =  "cuckoolaugh@163.com";
	$GLOBALS ["gEmail"] ["pop3_host"] =  "pop3.guodong.com";
	
//////////////////////////////////////////////////////
//				TimeZone   Settings	                //
//////////////////////////////////////////////////////		
		
	date_default_timezone_set("Asia/Shanghai");
	
//////////////////////////////////////////////////////
//				Database   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gDataBase"] ["db_setting.db3"] = array (
  'dbname' => 'setting.db3',
  'type' => 'sqlite',
  'path' => APP_DIR."/config",
);
	$GLOBALS ["gDataBase"] ["db_main"] = array (
  'dbname' => 'main',
  'type' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'user' => 'root',
  'passwd' => '111111',
);
	
//////////////////////////////////////////////////////
//				Memcached  Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11213"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11213',
);
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11212"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11212',
);
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11211"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11211',
);
	$GLOBALS ["gMemcacheServer"] ["222.73.242.74:11211"] = array (
  'mmhost' => '222.73.242.74',
  'mmport' => '11211',
);
	$GLOBALS ["gMemcacheServer"] ["222.73.242.74:11311"] = array (
  'mmhost' => '222.73.242.74',
  'mmport' => '11311',
);
	$GLOBALS ["gMemcacheServer"] ["222.73.242.74:11511"] = array (
  'mmhost' => '222.73.242.74',
  'mmport' => '11511',
);
	$GLOBALS ["gMemcacheServer"] ["222.73.242.74:11611"] = array (
  'mmhost' => '222.73.242.74',
  'mmport' => '11611',
);
	
//////////////////////////////////////////////////////
//				Packet   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gPacket"] ["cacheOpen"] =  1;
	$GLOBALS ["gPacket"] ["cacheStore"] =  "file";
	$GLOBALS ["gPacket"] ["cacheTime"] =  3600;
	$GLOBALS ["gPacket"] ["cacheDir"] =  APP_TEMP_DIR;
	$GLOBALS ["gPacket"] ["cacheServer"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);
	
//////////////////////////////////////////////////////
//				PageCache  Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gPageCache"] ["index"] ["rulename"]=  "index";
	$GLOBALS ["gPageCache"] ["index"] ["cachestore"]=  "file";
	$GLOBALS ["gPageCache"] ["index"] ["cacheserver"]=  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11212"]);;
	$GLOBALS ["gPageCache"] ["index"] ["cachedir"]=  APP_TEMP_DIR ."/_cache";
	$GLOBALS ["gPageCache"] ["index"] ["cachetime"]=  10;
	$GLOBALS ["gPageCache"] ["index"] ["compressed"]=  1;
	$GLOBALS ["gPageCache"] ["index"] ["action"]=  "index";
	$GLOBALS ["gPageCache"] ["index"] ["view"]=  "*";
	
//////////////////////////////////////////////////////
//				Session   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gSession"] ["sessionHandle"] =  "file";
	$GLOBALS ["gSession"] ["lifeTime"] =  1440;
	$GLOBALS ["gSession"] ["database"] =  $GLOBALS ["gDataBase"] ["db_setting.db3"];
	$GLOBALS ["gSession"] ["memcached"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"],
$GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);;
	
//////////////////////////////////////////////////////
//				Log   Settings	                //
//////////////////////////////////////////////////////		
	$GLOBALS ['gLog'] ['sendemail'] = 1;	
	$GLOBALS ["gLog"] ["subject"] =  "应用错误报告";
	$GLOBALS ["gLog"] ["receiver"] =  "kakapowu@gmail.com";
	
	$GLOBALS ['gLog'] ['maxExecTime'] = 2;
	$GLOBALS ['gLog'] ['maxMemUsed'] = 1048576;
	
?>