<?php
$config['admin']='admin';
$config['password']='123456';
/*************/
/* KFL entrance
/* portal of the application
/*
/*************/
define("APP_DIR", dirname(__FILE__));
include_once (APP_DIR . "/config/config.ini.php");
include_once (KFL_DIR . "/KFL.php");


// new application
$kfl = new KFL ( 0 );

// set default controller (option)
$kfl->setDefController ( 'index' );

//set default view style
$kfl->setDefView ( 'admin' );


$kfl->run ();


?>