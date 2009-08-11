<?php
//error_reporting(8);
$location = "";
$cookiearr = array ();
require_once ("simplehtmldom/simple_html_dom.php");

//scraping_digg("xing5460");


function scraping_digg($username) {
	//echo  $start=time()."---";
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, "http://home.51.com/diary.php?user=" . $username );
	curl_setopt ( $ch, CURLOPT_REFERER, "http://home.51.com" );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	
	$html2 = curl_exec ( $ch );
	$html2 = strip_tags ( $html2, "<html><body><div><a>" );
	curl_close ( $ch );
	// create HTML DOM
	$html = new simple_html_dom ( );
	$html = str_get_html ( $html2 );
	
	//print_r($html);
	$re = array ();
	$re = $html->find ( 'div.diary_title' );
	$finally = array ();
	$i = 0;
	foreach ( $re as $key => $div ) {
		
		$text = trim ( $div->plaintext );
		$finally [$i] ['date'] = substr ( $text, - 16 );
		foreach ( $div->find ( 'a' ) as $a ) {
			if (empty ( $a->href )) {
				$a->href = "";
			}
			$finally [$i] ['href'] = trim ( $a->href );
			if (empty ( $a->plaintext )) {
				$a->plaintext = "";
			}
			$finally [$i] ['title'] = trim ( $a->plaintext );
			break;
		}
		$i ++;
	}
	
	// echo  $end=time();
	// echo "执行时间：".$end-$start;
	unset ( $html );
	
	return $finally;
}

