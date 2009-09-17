<?php
// This code was created by phpMyBackupPro v.2.1 
// http://www.phpMyBackupPro.net
$_POST['db']=array("mysql", );
$_POST['tables']="on";
$_POST['data']="on";
$_POST['drop']="on";
$period=(3600*24)/24;
$security_key="";
// This is the relative path to the phpMyBackupPro v.2.1 directory
@chdir("../../phpMyBackupPro/");
@include("backup.php");
?>