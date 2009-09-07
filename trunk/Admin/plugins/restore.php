<?php
define ( 'ADMIN_USERNAME', 'admin' ); // Admin Username
define ( 'ADMIN_PASSWORD', '123456' ); // Admin Password
/////////////////// Password protect ////////////////////////////////////////////////////////////////
if (! isset ( $_SERVER ['PHP_AUTH_USER'] ) || ! isset ( $_SERVER ['PHP_AUTH_PW'] ) || $_SERVER ['PHP_AUTH_USER'] != ADMIN_USERNAME || $_SERVER ['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
	Header ( "WWW-Authenticate: Basic realm=\"Memcache Login\"" );
	Header ( "HTTP/1.0 401 Unauthorized" );

	echo <<<EOB
				<html><body>
				<h1>Rejected!</h1>
				<big>Wrong Username or Password!</big>
				</body></html>
EOB;
	exit ();
}
include("../config/config.ini.php");
include("../../KFL/KFL.php");
include(KFL_DIR."/Libs/Database.class.php");
$db = Model::dbConnect($GLOBALS ['gDataBase'] ['db_setting.db3']);
$app_info = $db->getRow("select * from project limit 1");
$res = 0;
$rs2 = 0;
$rs	= file_exists($app_info['app_dir']."/config/config.ini.php.bak");
$rs1= file_exists($app_info['app_dir']."/config/config.ini.php");
if($rs && $rs1) {
	$rs2=rename($app_info['app_dir']."/config/config.ini.php",$app_info['app_dir']."/config/config.ini.php.cur");
}
if($rs2) {
	$res= rename($app_info['app_dir']."/config/config.ini.php.bak",$app_info['app_dir']."/config/config.ini.php");
}


if($res){
	unlink($app_info['app_dir']."/config/config.ini.php.cur");
	$msg['s'] = 200;
	$msg['m'] = "ok!";
	$msg['d'] = 'null';	
}else{
	$msg['s'] = 400;
	$msg['m'] = "fail!";
	$msg['d'] = 'null';	
}
echo json_encode($msg);
die;
?>