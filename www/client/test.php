<?php
include("config.php");
include("XPassClient.class.php");
$xpc = new XPassClient($private_key_str);
$res = $xpc->isLogin('jessie');
//print_r($res);
if($res['s']==200){
	$user = $xpc->getLoginUser($res['d']);
	print_r($user);
}elseif($res['s']==300){
	header("location:".$res['d']);
}else{
	echo $res['m'];
}

?>