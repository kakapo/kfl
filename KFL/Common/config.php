<?php

if(!defined("KFL_DIR")) define("KFL_DIR", dirname(__FILE__));

//////////////////////////////////////////////////////
//					PEAR							//
//////////////////////////////////////////////////////


//////////////////////////////////////////////////////
//					Directory						//
//////////////////////////////////////////////////////

// define applications models dicrectory
define("APP_DIR_M",APP_DIR. "/models");

// define applications templates dicrectory
define("APP_DIR_V",APP_DIR. "/views");

// define applications controllers dicrectory
define("APP_DIR_C",APP_DIR. "/controllers");

// define applications temporary dicrectory
define("APP_TEMP_DIR", APP_DIR. '/tmp');

define("APP_LANG_DIR", APP_DIR. '/languages');

// define error log file
define("LOG_FILE", APP_DIR . "/tmp/logs");

// define upload directory
define("UPLOAD_DIR", APP_DIR . "/tmp/uploads");

// if using the KFL/Components/libs instead of your systemwide pear libraries.
if(PHP_OS=='Linux'){
	ini_set('include_path', KFL_DIR. "/:". APP_DIR_M ."/:" . ini_get('include_path').':'); // FOR UNIX
}elseif(PHP_OS=='WINNT'){
	ini_set('include_path', KFL_DIR. "/;". APP_DIR_M."/;". ini_get('include_path')); // FOR WINDOWS
}

/**
 * 发送的邮件配置
 */
$GLOBALS['log']['subject']   	= "com网站日志报告";   					     		 //邮件主题
$GLOBALS['log']['pop3_host'] 	="pop3.guodong.com";//"pop3.tsong.cn";				 			     //外部邮件pop3地址
$GLOBALS['log']['smtp_host'] 	= "smtp.guodong.com";//"smtp.tsong.cn"; 	                   	     //外部邮件smtp地址
$GLOBALS['log']['smtp_account'] = "no-reply@guodong.com";//"cdwei@tsong.cn";           	  			 //发送邮件的帐号
$GLOBALS['log']['smtp_pass'] 	= "tsong-0810";//tsong-0810";                         	    	 //发送帐号的密码
$GLOBALS['log']['from'] 	 	= "no-reply@guodong.com";              	     			 //显示的发件人名称
$GLOBALS['log']['devlopers'] 	= array("cdwei@tsong.cn","gtzhao@tsong.cn","zswu@tsong.cn","zgma@tsong.cn");
$GLOBALS['log']['systemers'] 	= array("zswu@tsong.cn");//




?>