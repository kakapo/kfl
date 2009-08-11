<?php

$xml = '<?xml version="1.0" encoding="UTF-8"?><photos_getHome_response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="" list="true"></photos_getHome_response>';
function convert_simplexml_to_array($xml) {
	$sxml = simplexml_load_string ( $xml );
	$arr = array ();
	if ($sxml) {
		foreach ( $sxml as $k => $v ) {
			if ($sxml ['list']) {
				$arr [] = self::convert_simplexml_to_array ( $v );
			} else {
				$arr [$k] = FiveOneCommClass::utf2Gbk ( self::convert_simplexml_to_array ( $v ) );
			}
		}
	}
	echo "<pre><font color=''>arr == ";
	print_r ( $arr );
	echo "</font></pre>";
	if (sizeof ( $arr ) > 0) {
		return $arr;
	} else {
		return ( string ) $sxml;
	}
}

$r = convert_simplexml_to_array ( $xml );
echo "r = ";
var_dump ( $r );
echo "<br>";
?>