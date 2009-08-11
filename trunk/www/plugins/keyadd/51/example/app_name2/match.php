<?php
include_once ("sdk/appinclude.php");
include_once ("db.php");

$src_user = $OpenApp_51->get_user ();
$tar_user = $_GET ["reveuid"];
$src_name = $_GET ["rn1"];
$tar_name = $_GET ["rn2"];

DEFINE ( 'DB_DSN', 'your dsn here' );
$db = new Database ( );
$db->connect ( DB_DSN );

$match_arr = array ("相互敬仰", "死党", "你的人", "关系暧昧", "一见衷情" );
$rand_num = rand ( 1, sizeof ( $match_arr ) );
error_log ( $rand_num );
$matched = $match_arr [$rand_num - 1];
error_log ( "xxxxxxxxxxxxxxxx" . $matched );
$sql = "insert into user_match (src_user, src_name, tar_user, tar_name, matched) values ('$src_user', '$src_name', '$tar_user', '$tar_name','$matched')";
//error_log("xxxxxxxxxxxxxxx".$sql."xxxxxxxxxxxxxxxxxxxxxxxxx");
$db->query ( $sql );
echo json_encode ( array () );

?>
