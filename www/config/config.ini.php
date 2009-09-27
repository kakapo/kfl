<?php
	
//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
	define("APP_STATUS", "dev");
	define("KFL_DIR", "D:/GoogleCodeKFL/KFL");
	define("APP_LANG", "ch");
	define("APP_TEMP_DIR", APP_DIR."/tmp/");
	
//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
		
	$GLOBALS ["gSiteInfo"] ["web_charset"] =  "UTF-8";
	$GLOBALS ["gSiteInfo"] ["web_keyword"] =  "KFL";
	$GLOBALS ["gSiteInfo"] ["web_description"] =  "KFL是一个轻快的、友好的、MVC模式的PHP开发框架，试图在应用层解决Webserver集群带来的系列问题。 ";
	$GLOBALS ["gSiteInfo"] ["web_title"] =  "欢迎访问&quot;KFL&quot;";
	
//////////////////////////////////////////////////////
//				Email   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gEmail"] ["smtp_host"] =  "smtp.guodong.com";
	$GLOBALS ["gEmail"] ["smtp_account"] =  "no-reply@guodong.com";
	$GLOBALS ["gEmail"] ["smtp_pass"] =  "tsong-0810";
	$GLOBALS ["gEmail"] ["smtp_from"] =  "no_reply@guodong.com";
	$GLOBALS ["gEmail"] ["pop3_host"] =  "pop3.guodong.com";
	
//////////////////////////////////////////////////////
//				TimeZone   Settings	                //
//////////////////////////////////////////////////////		
		
	date_default_timezone_set("Asia/Shanghai");
	
//////////////////////////////////////////////////////
//				Database   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gDataBase"] ["db_kakapo"] = array (
  'dbname' => 'kakapo',
  'type' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'user' => 'kakapo',
  'passwd' => '123456',
  'charset' => 'utf8',
);
	$GLOBALS ["gDataBase"] ["db_setting.db3"] = array (
  'dbname' => 'setting.db3',
  'type' => 'sqlite',
  'path' => APP_DIR."/../Admin/config",
);
	
//////////////////////////////////////////////////////
//				Memcached  Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11212"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11212',
);
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11211"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11211',
);
	$GLOBALS ["gMemcacheServer"] ["192.168.1.5:11213"] = array (
  'mmhost' => '192.168.1.5',
  'mmport' => '11213',
);
	
//////////////////////////////////////////////////////
//				Packet   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gPacket"] ["cacheOpen"] =  1;
	$GLOBALS ["gPacket"] ["cacheStore"] =  "memcache";
	$GLOBALS ["gPacket"] ["cacheTime"] =  3600;
	$GLOBALS ["gPacket"] ["cacheDir"] =  APP_TEMP_DIR;
	$GLOBALS ["gPacket"] ["cacheServer"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);
	
//////////////////////////////////////////////////////
//				PageCache  Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gPageCache"] ["index"] ["rulename"]=  "index";
	$GLOBALS ["gPageCache"] ["index"] ["cachestore"]=  "file";
	$GLOBALS ["gPageCache"] ["index"] ["cacheserver"]=  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11211"]);;
	$GLOBALS ["gPageCache"] ["index"] ["cachedir"]=  APP_TEMP_DIR;
	$GLOBALS ["gPageCache"] ["index"] ["cachetime"]=  60;
	$GLOBALS ["gPageCache"] ["index"] ["compressed"]=  1;
	$GLOBALS ["gPageCache"] ["index"] ["action"]=  "index";
	$GLOBALS ["gPageCache"] ["index"] ["view"]=  "*";
	
//////////////////////////////////////////////////////
//				Session   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gSession"] ["sessionHandle"] =  "file";
	$GLOBALS ["gSession"] ["lifeTime"] =  1440;
	$GLOBALS ["gSession"] ["database"] =  $GLOBALS ["gDataBase"]["db_setting.db3"];
	$GLOBALS ["gSession"] ["memcached"] =  array($GLOBALS ["gMemcacheServer"]["192.168.1.5:11212"],
$GLOBALS ["gMemcacheServer"]["192.168.1.5:11213"]);;
	
//////////////////////////////////////////////////////
//				Log   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["gLog"] ["sendemail"] =  "1";
	$GLOBALS ["gLog"] ["subject"] =  "应用错误报告";
	$GLOBALS ["gLog"] ["receiver"] =  "kakapowu@gmail.com";
	$GLOBALS ["gLog"] ["maxExecTime"] =  "2";
	$GLOBALS ["gLog"] ["maxMemUsed"] =  "1048576";
	
?>