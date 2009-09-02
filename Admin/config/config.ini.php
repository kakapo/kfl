<?php

//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
// define error_handler. options(dev, online)
define ( "APP_STATUS", "online" );

// define application directory
define ( "APP_DIR", dirname ( __file__ ) . '/..' );

// define system KFL directory, it can be relative
define ( 'KFL_DIR', APP_DIR . "/../KFL" );

// define language
define ( "APP_LANG", "ch" );


// define session save handle (file, mysql, memcache)


//if you set SESSION_HANDLE to 'memcache', you must set following difines like:
//$GLOBALS['gMemcacheServer']['Session'] = array(
//array('host'=>"192.168.1.5",'port'=>11211),
//array('host'=>"192.168.1.5",'port'=>11212)
//);
/*
//if you set SESSION_HANDLE to 'mysql', you must set create table session first:
CREATE TABLE `session` (
	      `sesskey` varchar(32) NOT NULL default '',
	      `expiry` bigint(20) NOT NULL default '0',
	      `data` longtext NOT NULL,
	      PRIMARY KEY  (`sesskey`),
	      KEY `expiry` (`expiry`)
	    ) TYPE=MyISAM DEFAULT CHARSET=".$GLOBALS['gDataBase']['charset'];
*/

// set date zone
date_default_timezone_set('Asia/Shanghai');


//////////////////////////////////////////////////////
//				Website Settings	                //
//////////////////////////////////////////////////////
$GLOBALS ['gSiteInfo'] ['site_name'] = "3D-widget-果动网";
$GLOBALS ['gSiteInfo'] ['version'] = "v2.0.0";
$GLOBALS ['gSiteInfo'] ['dress_item_js_version'] = "v2.0.0.1";
$GLOBALS ['gSiteInfo'] ['image_site_url'] = "http://image.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['tool_site_url'] = "http://tool.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['www_site_url'] = "http://www.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['bbs_site_url'] = "http://bbs.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['stats_site_url'] = "http://stats.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['user_site_url'] = "http://user.guodong.dev3";
$GLOBALS ['gSiteInfo'] ['site_title'] = "果动网";
$GLOBALS ['gSiteInfo'] ['webcharset'] = "utf-8";
$GLOBALS ['gSiteInfo'] ['pagenum'] = 5;


//////////////////////////////////////////////////////
//				Database Settings	           		//
//////////////////////////////////////////////////////
$GLOBALS ['gDataBase'] ['main'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'gd_main', 'charset' => 'utf8', 'cache_time' => 3600 );
$GLOBALS ['gDataBase'] ['setting'] = array ('type' => 'sqlite','path' => APP_DIR.'/config','user' => '', 'passwd' => '', 'dbname' => 'setting.db3', 'charset' => 'utf8', 'cache_time' => 3600 );

$GLOBALS ['gDataBase'] ['account_index'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'gd_account_index', 'charset' => 'utf8', 'cache_time' => 3600 );

$GLOBALS ['gDataBase'] ['account_a'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'gd_account_a', 'charset' => 'utf8', 'cache_time' => 3600 );

$GLOBALS ['gDataBase'] ['account_b'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'gd_account_b', 'charset' => 'utf8', 'cache_time' => 3600 );

// database settings
$GLOBALS ['gDataBase'] ['defaults'] = $GLOBALS ['gDataBase'] ['main'];



//////////////////////////////////////////////////////
//				User Settings	           		   //
//////////////////////////////////////////////////////
// Memcached server settings
define ( "USE_MEMCACHE", 1 );
define ( "MEMCACHE_APP_DATA_EXPIRED", 3600 * 24 * 15 );
define ( "MEMCACHE_ONLINE_USER_EXPIRED", 60 );

//用户索引数据缓存
$GLOBALS ['gMemcacheServer'] ['UserIndex'] = array (array ('host' => "192.168.1.5", 'port' => 11213, 'persistent' => true, 'weight' => 1, 'timeout' => 1, 'retry_interval' => 15, "status" => true, 'failure_callback' => 'send_email' ) );
//页面的数据库查询数据缓存
$GLOBALS ['gMemcacheServer'] ['SqlDataCache'] = array (array ('host' => "192.168.1.5", 'port' => 11213, 'persistent' => true, 'weight' => 1, 'timeout' => 1, 'retry_interval' => 15, "status" => true, 'failure_callback' => 'send_email' ) );

$config['admin']='admin';
$config['password']='123456';

//设置数据片缓存
$GLOBALS ['packet']['cacheOpen'] = 0;
$GLOBALS ['packet']['cacheStore'] = 'memcache' ;
$GLOBALS ['packet']['cacheTime'] = 60 ;
$GLOBALS ['packet']['cacheDir'] = APP_DIR."/tmp" ;
$GLOBALS ['packet']['cacheServer'] = $GLOBALS ['gMemcacheServer'] ['SqlDataCache'] ;

//设置页面缓存
$GLOBALS['pagecache']['helpcache']['rulename']='helpcache';
$GLOBALS['pagecache']['helpcache']['cachestore']='memcache';
$GLOBALS['pagecache']['helpcache']['cachedir']= APP_DIR."/tmp/_cache";
$GLOBALS['pagecache']['helpcache']['cacheserver']= $GLOBALS ['gMemcacheServer'] ['SqlDataCache'];
$GLOBALS['pagecache']['helpcache']['cachetime']=600;
$GLOBALS['pagecache']['helpcache']['compressed']=1;
$GLOBALS['pagecache']['helpcache']['action']='index';
$GLOBALS['pagecache']['helpcache']['view']='login,dashboard';


//会话设置
$GLOBALS['session']['sessionHandle'] = 'database';
$GLOBALS['session']['lifeTime'] = 1440;
$GLOBALS['session']['database'] = $GLOBALS ['gDataBase'] ['setting'];
$GLOBALS['session']['memcached'] = array(
array('host'=>"192.168.1.5",'port'=>11211)
);

//监控设置

$GLOBALS['log']['subject']   	= "From KFL 开发";   					     		 //邮件主题
$GLOBALS['log']['pop3_host'] 	= "pop3.163.com";//"pop3.tsong.cn";				 			     //外部邮件pop3地址
$GLOBALS['log']['smtp_host'] 	= "smtp.163.com";//"smtp.tsong.cn"; 	                   	     //外部邮件smtp地址
$GLOBALS['log']['smtp_account'] = "cuckoolaugh@163.com";//"cdwei@tsong.cn";           	  			 //发送邮件的帐号
$GLOBALS['log']['smtp_pass'] 	= "810600";//tsong-0810";                         	    	 //发送帐号的密码
$GLOBALS['log']['from'] 	 	= "cuckoolaugh@163.com";              	     			 //显示的发件人名称
$GLOBALS['log']['receiver'] 	= array("zswu@tsong.cn");



?>