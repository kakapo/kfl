<?php
/*************/
/* KFL entrance
/* portal of the application
/*
/*************/
include_once (dirname ( __file__ ) . "/config/config.ini.php");
include_once (APP_DIR . "/../KFL/KFL.php");
$kfl = new KFL ( );
$kfl->setDefController ( 'index' );
$kfl->setDefView ( 'index' );
$kfl->run ();
?>