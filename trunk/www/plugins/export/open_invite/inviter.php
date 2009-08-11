<?php

$_POST['email_box']="weichaoduo@hotmail.com";
$_POST['password_box']="79720699";
$_POST['provider_box']="hotmail";

 

include('openinviter.php');
$inviter=new OpenInviter();
$oi_services=$inviter->getPlugins();

$plugType='email';
$plugType='social';

$ers=array();$oks=array();$import_ok=false;$done=false;

$inviter->startPlugin($_POST['provider_box']);
$inviter->login($_POST['email_box'],$_POST['password_box']);
$contacts=$inviter->getMyContacts();
$finally=array();

foreach ($contacts as $key => $v) {
	$finally[0][]=$v;
	$finally[1][]=$key;
}


print_r($finally);die;




?>