<?php
/*************/
/* KFL entrance
/* portal of the application
/*
/*************/
include_once (dirname ( __file__ ) . "/config/config.ini.php");
include_once (KFL_DIR . "/KFL.php");


// new application
$kfl = new KFL ( 0 );

// set default controller (option)
$kfl->setDefController ( 'index' );

//set default view style
$kfl->setDefView ( 'admin' );


$kfl->run ();


?>