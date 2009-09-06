<?php
$res = 0;
$rs=file_exists(dirname(__FILE__)."/../config/config.ini.php.bak");
$rs1=unlink(dirname(__FILE__)."/../config/config.ini.php");
if($rs && $rs1) {
	$res= rename(dirname(__FILE__)."/../config/config.ini.php.bak",dirname(__FILE__)."/../config/config.ini.php");
}


if($res){
	$msg['s'] = 200;
	$msg['m'] = "生成成功!";
	$msg['d'] = 'null';	
}else{
	$msg['s'] = 400;
	$msg['m'] = "生成失败!";
	$msg['d'] = 'null';	
}
echo json_encode($msg);
die;
?>