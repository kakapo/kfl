<?php
	
//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
	define("APP_DIR", dirname(__FILE__)."/../");
	define("APP_STATUS", "dev");
	define("APP_LANG", "ch");
	define("APP_TEMP_DIR", APP_DIR."/../tmp/");
	
//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
		
	$GLOBALS ["gSiteInfo"] ["title"] =  "果动网";
	$GLOBALS ["gSiteInfo"] ["vision"] =  "V2.0.0";
	$GLOBALS ["gSiteInfo"] ["www_site_url"] =  "http://www.guodong.dev3";
	$GLOBALS ["gSiteInfo"] ["webcharset"] =  "utf-8";
	$GLOBALS ["gSiteInfo"] ["site_name"] =  "3D-widget-果动网";
	
//////////////////////////////////////////////////////
//				Email   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["email"] ["smtp_host"] =  "smtp.guodong.com";
	$GLOBALS ["email"] ["smtp_account"] =  "no-reply@guodong.com";
	$GLOBALS ["email"] ["smtp_pass"] =  "tsong-0810";
	$GLOBALS ["email"] ["from"] =  "no-reply@guodong.com";
	$GLOBALS ["email"] ["pop3_host"] =  "pop3.guodong.com";
	
//////////////////////////////////////////////////////
//				TimeZone   Settings	                //
//////////////////////////////////////////////////////		
		
	date_default_timezone_set("Asia/Shanghai");
	
//////////////////////////////////////////////////////
//				Database   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gDataBase"] ["db_main"] = array (
  'dbname' => 'main',
  'type' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'user' => 'root',
  'passwd' => '111111',
);
	$GLOBALS ["gDataBase"] ["db_setting.db3"] = array (
  'dbname' => 'setting.db3',
  'type' => 'sqlite',
  'path' => APP_DIR."/config",
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
		
	$GLOBALS ["packet"] ["cacheOpen"] =  1;
	$GLOBALS ["packet"] ["cacheStore"] =  "file";
	$GLOBALS ["packet"] ["cacheTime"] =  3600;
	$GLOBALS ["packet"] ["cacheDir"] =  APP_TEMP_DIR;
	$GLOBALS ["packet"] ["cacheServer"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);
	
//////////////////////////////////////////////////////
//				PageCache  Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["pagecache"] ["index"] ["rulename"]=  "index";
	$GLOBALS ["pagecache"] ["index"] ["cachestore"]=  "file";
	$GLOBALS ["pagecache"] ["index"] ["cacheserver"]=  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11212"]);;
	$GLOBALS ["pagecache"] ["index"] ["cachedir"]=  APP_TEMP_DIR ."/_cache";
	$GLOBALS ["pagecache"] ["index"] ["cachetime"]=  10;
	$GLOBALS ["pagecache"] ["index"] ["compressed"]=  1;
	$GLOBALS ["pagecache"] ["index"] ["action"]=  "index";
	$GLOBALS ["pagecache"] ["index"] ["view"]=  "login";
	
//////////////////////////////////////////////////////
//				Session   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["session"] ["sessionHandle"] =  "file";
	$GLOBALS ["session"] ["lifeTime"] =  1440;
	$GLOBALS ["session"] ["database"] =  "db_setting.db3";
	$GLOBALS ["session"] ["memcached"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"],
$GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);;
	
//////////////////////////////////////////////////////
//				Log   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["log"] ["subject"] =  "应用错误报告";
	$GLOBALS ["log"] ["receiver"] =  "zswu@tsong.cn";
	$GLOBALS ["log"] ["sendemail"] =  "0";
	
?>