<?php
	
//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
	define("APP_DIR", dirname(__FILE__)."/../");
	define("KFL_DIR", APP_DIR."/../");
	define("APP_DIR_M", APP_DIR. "/models");
	define("APP_DIR_V", APP_DIR. "/views");
	define("APP_DIR_C", APP_DIR. "/controllers");
	define("APP_TEMP_DIR", APP_DIR. "/tmp");
	define("APP_LANG_DIR", APP_DIR. "/languages");
	define("LOG_FILE", APP_DIR . "/tmp/logs");
	define("UPLOAD_DIR", APP_DIR . "/tmp/uploads");
	define("APP_STATUS", dev);
	define("APP_LANG", ch);
	
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
		
	$GLOBALS ["gDataBase"] ["db_account"] = 
	array (
  'dbname' => 'account',
  'type' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'user' => 'newidol',
  'passwd' => 'newidol',
);
	$GLOBALS ["gDataBase"] ["db_main"] = 
	array (
  'dbname' => 'main',
  'type' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'user' => 'root',
  'passwd' => '111111',
);
	
//////////////////////////////////////////////////////
//				Packet   Settings	                //
//////////////////////////////////////////////////////		
		
	$GLOBALS ["packet"] ["cacheOpen"] =  1;
	$GLOBALS ["packet"] ["cacheStore"] =  file;
	$GLOBALS ["packet"] ["cacheTime"] =  3600;
	$GLOBALS ["packet"] ["cacheDir"] =  APP_TEMP_DIR;
	$GLOBALS ["packet"] ["cacheServer"] =  192.168.1.5:11211;
	
?>