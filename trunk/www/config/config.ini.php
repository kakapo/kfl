<?php
if (! class_exists ( 'Memcache' )) {
	die ( 'Failed: Memcache extension not install, please download from http://pecl.php.net' );
}

//////////////////////////////////////////////////////
//					Application	Settings			//
//////////////////////////////////////////////////////
// define error_handler. options(dev, online)
define ( "APP_STATUS", "dev" );

// define application directory
define ( "APP_DIR", dirname ( __file__ ) . '/..' );

// define system KFL directory, it can be relative
define ( 'KFL_DIR', APP_DIR . "/../KFL" );

// define language
define ( "APP_LANG", "ch" );

// define use database or not
define ( "USE_DATABASE", "1" );

// define session save handle (file, mysql, memcache)
define ( "SESSION_HANDLE", 'file' );

//if you set SESSION_HANDLE to 'memcache', you must set following difines like:
$GLOBALS['gMemcacheServer']['Session'] = array(
array('host'=>"192.168.1.5",'port'=>11211),
array('host'=>"192.168.1.5",'port'=>11212)
);
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

$GLOBALS ['gDataBase'] ['ad'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'gd_ad', 'charset' => 'utf8', 'cache_time' => 3600 );

$GLOBALS ['gDataBase'] ['event'] = array ('host' => '192.168.1.5', 'port' => '3306', 'type' => 'mysql', 'user' => 'newidol', 'passwd' => 'newidol', 'dbname' => 'guodong_event', 'charset' => 'utf8', 'cache_time' => 3600 );

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



// user define
define ( "PAGE_NUM", $GLOBALS ['gSiteInfo'] ['pagenum'] );
define ( "RELATED_ART_NUM", 12 );
define ( "FILE_EXT", ".html" );
define ( "CACHE_TIME", 1800 );
define ( "COOKIE_DOMAIN", '.guodong.dev3' );
define ( "RSS_CACHE_TIME", 1800 );
define ( "TICKET_KEY", 'TsKey2008' );

$GLOBALS ['account'] ['invite'] = array ("qq_main_url" => "http://m59.mail.qq.com" );

//邀请链接的复制地址
$GLOBALS ['account'] ['inviteurl'] = $GLOBALS ['gSiteInfo'] ['www_site_url'] . "/index.php?action=passport&view=regframe2&sponsor=";

//发送的邮件配置
$GLOBALS ['account'] ['subject'] = "果动为你送回密码，我们期待你早日归来"; //邮件主题
$GLOBALS ['account'] ['pop3_host'] = "pop3.guodong.com"; //"pop3.tsong.cn";				  //外部邮件pop3地址
$GLOBALS ['account'] ['smtp_host'] = "smtp.guodong.com"; //"smtp.tsong.cn"; 	          //外部邮件smtp地址
$GLOBALS ['account'] ['smtp_account'] = "no-reply@guodong.com"; //"cdwei@tsong.cn";          //发送邮件的帐号
$GLOBALS ['account'] ['smtp_pass'] = "tsong-0810"; //tsong-0810";                         //发送帐号的密码
$GLOBALS ['account'] ['from'] = "no-reply@guodong.com"; //显示的发件人名称
$GLOBALS ['account'] ['urlValidSecond'] = 3600; //修改密码连接地址有效的时间
$GLOBALS ['account'] ['content'] = '
									如果您点击上述链接无效，请把代码拷贝到浏览器的地址栏中</p>
									<p>祝你在在果动网玩的愉快！</p>
									<p>以下是一些常用链接：<br>
									  新手指南：<a href="' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '/index.php?action=help&view=newuser">' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '/index.php?action=help&amp;view=newuser</a><br>
									  常见问题：<a href="' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '/index.php?action=help&view=kfzx">' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '/index.php?action=help&amp;view=kfzx</a><br><br>
									  果动网&lt;<a href="' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '">' . $GLOBALS ['gSiteInfo'] ['www_site_url'] . '</a>&gt; 分享3D生活<br>
									</p>

';


//进阶编辑尺寸
$GLOBALS ['account'] ['size'] = array ('qq' => array ('0' => '171*245', '1' => '354*506' ), 'baidu' => array ('0' => '300*430' ), 'sina' => array ('0' => '190*271', '1' => '300*430' ), 'w163' => array ('0' => '198*283', '1' => '265*378' ), '51com' => array ('0' => '265*379', '1' => '527*753' ), 'sohu' => array ('0' => '215*307', '1' => '280*400' ), 'blogbus' => array ('0' => '180*257', '1' => '240*343' ), 'maopu' => array ('0' => '180*257' ), 'msn' => array ('0' => '300*430' ), 'myspace' => array ('0' => '300*430' ), '19lou' => array ('0' => '300*430' ), 'poco' => array ('0' => '215*307' ), 'tudou' => array ('0' => '215*307' ) );

//商城变量配置
$GLOBALS ['showConfig'] = array (//主分类
'favor' => 10000,
'newer' => 86400*30,
'grade_star' => array (array('3000','★'),array('7000','★★'),array('12000','★★★'),array('20000','★★★★'),array('30000','★★★★★')),
'item_price' => array ('1' => array ('title' => '1个月（原价*0.3）', 'discount' => '0.3', 'selected' => 0 ), '2' => array ('title' => '3个月（原价*0.65）', 'discount' => '0.65', 'selected' => 0 ), '3' => array ('title' => '6个月（原价）', 'discount' => '1', 'selected' => 1 ), '4' => array ('title' => '12个月（原价*1.8）', 'discount' => '1.8', 'selected' => 0 ) ) );

//定义相册分页行数
define ( "AM_PAGE_NUM", 6 );

//整合论坛的私钥
$GLOBALS ['bbs']['key']='dsfsdfsdfsdfsdfsdfsdsdf';

//激活服务管理
$gservices = array ('1' => array ('0' => '1', '1' => '动感聊天', '2' => 'dtlt.jpg', '3' => 'http://apps.xiaonei.com/guodongchat' ), '2' => array ('0' => '2', '1' => '炒房浮生记', '2' => 'fsclj.jpg', '3' => 'index.php?action=index&view=gameintro' ) );//http://space.sina.com.cn/plugin/app.php?id=002f5f6a14afe4c925477e6aa5735064_101


/**
* change me to correct tracker server list, assoc array element:
*    ip_addr: the ip address or hostname of the tracker server
*    port:    the port of the tracker server
*    sock:    the socket handle to the tracker server, should init to -1 or null
*/
$fdfs_network_timeout = 30;  //seconds
$fdfs_tracker_servers = array();
$fdfs_tracker_server_index = 0;
$fdfs_tracker_servers[0] = array(
		'ip_addr' => '192.168.1.5',
		'port' => 22122,
		'sock' => -1);
//$fdfs_tracker_servers[1] = array(
//		'ip_addr' => '10.62.164.84',
//		'port' => 22122,
//		'sock' => -1);

$fdfs_tracker_server_count = count($fdfs_tracker_servers);

//存储服务器域名设置，group为key值。
$fdfs_storage_settings = array(
"group1"=>"http://user.guodong.dev3",
"group2"=>"http://user2.guodong.dev3"
);

//用户添加的图片数量,10张
$GLOBALS['uploadImageTotal'] = 8;

//flash尺寸
$GLOBALS['sizeArray'] = array(
							'qzone' => array( 'width' => '300' , 'height' => '430' ),
							'sina' => array( 'width' => '187' , 'height' => '400' ),
							'w163' => array( 'width' => '265' , 'height' => '379' ),
							'51com' => array( 'width' => '400' , 'height' => '268' ),
							'blogbus' => array( 'width' => '240' , 'height' => '343' ),
							'msn' => array( 'width' => '300' , 'height' => '430' ),
							'myspace' => array( 'width' => '300' , 'height' => '430' ),
							'19lou' => array( 'width' => '300' , 'height' => '430' ),
							'poco' => array( 'width' => '215' , 'height' => '307' ),
							'bbs' => array( 'width' => '300' , 'height' => '430' ),
							'currency' => array( 'width' => '300' , 'height' => '430' ),
							);

//vip所需G币
$GLOBALS['vip_cash'] = 5;

//禁词
$GLOBALS['blockword'] = array('fuck','他妈的','操你妈',"胡锦涛");

?>
