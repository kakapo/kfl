<?
function getmicrotime() {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return (( float ) $usec + ( float ) $sec);
}
$start_time = getmicrotime ();
/*************/
/* KFL entrance
/* portal of the application
/*
/*************/
include_once (dirname ( __file__ ) . "/config/config.ini.php");
include_once (KFL_DIR . "/KFL.php");
$start1_time = getmicrotime ();
$include_time = $start1_time - $start_time;

// new application
$kfl = new KFL ( 0 );

// set default controller (option)
$kfl->setDefController ( 'index' );

//set default view style
$kfl->setDefView ( 'index2.0' );

// use cache
//$kfl->useCache(200);


$kfl->useDataBase ( 1 );

// use authen
//$kfl->useAuthen();


$kfl->run ();

//echo "<pre>get_included_files:<br>";
//print_r(get_included_files());
//echo "</pre>";
$end_time = getmicrotime ();
//echo "<center>include time: ".$include_time."</center>";
//echo "<center>exectime: ".($end_time-$start1_time)."</center>";
//echo "<center><span style='display:none'>time: ".($end_time-$start_time)."</span></center>";


?>