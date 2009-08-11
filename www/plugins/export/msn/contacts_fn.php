<?php

include_once(APP_DIR.'/plugins/export/export_function.php');
include_once('hotmail.php');

/**
 * 联系人类 
 * @author cdwei
 *
 */
class Person {
	public $first_name, $last_name, $email_address;

	public function __construct($first, $last, $em) {
		$this->first_name = $first;
		$this->last_name = $last;
		$this->email_address = $em;
	}

	public function __toString() {
		return  $this->first_name.$this->$last_name.":".	$this->$email_address ."<BR>";
	}
}
/**
 * 使用windows live application id SDK 取得某个用户的联系人信息 
 * @return unknown_type
 */
function get_contacts($login,$pass)
{
	include 'settings.php';
	include 'windowslivelogin.php';

	//initialize Windows Live Libraries
	$wll = WindowsLiveLogin::initFromXml($KEYFILE);
	$consenturl = $wll->getConsentUrl($OFFERS);
	$ch = curl_init ();
	//取得登录后的ConsentToken
	$finally=loginAuth($login, $pass,$consenturl);
	$cookie="";
	$consent = $wll->processConsent($finally);
	if ($consent) {
		$cookie = $consent->getToken();
	} else {
		die;
	}

	if ($cookie) {
		$token = $wll->processConsentToken($cookie);
	}

	//Check if there's consent and, if not, redirect to the login page
	if ($token && !$token->isValid()) {
		$token = null;
	}

	if ($token) {
		// Convert Unix epoch time stamp to user-friendly format.
		$expiry = $token->getExpiry();
		$expiry = date(DATE_RFC2822, $expiry);


		//*******************CONVERT HEX TO DOUBLE LONG INT ***************************************
		$hexIn = $token->getLocationID();
		include "hex.php";
		$longint=$output;		//here's the magic long integer to be sent to the Windows Live service

		//*******************CURL THE REQUEST ***************************************
		$uri = "https://livecontacts.services.live.com/users/@L@".$token->getLocationID()."/rest/livecontacts";
		//	    https://livecontacts.services.live.com/users/@L@<lid>/rest/livecontacts
		$dat_str=$token->getDelegationToken();
		//$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );//DelegatedToken dt=
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Authorization: DelegatedToken dt="'.$token->getDelegationToken().'"'));
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			
		$response_h = curl_exec ( $ch );
			
			
		curl_close ( $ch );
		$finally=array();
		//*******************PARSING THE RESPONSE ****************************************************
		$response=strstr($response_h,"<?xml version");

		try {
			$xml = new SimpleXMLElement($response);
		}
		catch (Exception $e) {
			echo $response_h."<br>".$uri;
			die;
		}
		$lengthArray=sizeof($xml->Contacts->Contact);
		for ($i=0;$i<$lengthArray;$i++)
		{
			//There can be more fields, depending on how you configure.  Here's
			//where you should access the fields and send them to the constructor

			$fn = $xml->Contacts->Contact[$i]->Profiles->Personal->FirstName;
			$ln = $xml->Contacts->Contact[$i]->Profiles->Personal->LastName;
			$em = $xml->Contacts->Contact[$i]->Emails->Email->Address;
			if(!empty($em)){
				$finally[0][$i]=$ln[0].$fn[0];
				$finally[1][$i]=(string)$em;
			}
			//instantiate an object and add it to the array
			//$person_array[]=new Person($fn,$ln,$em);
		}
	}

	//return the entire array of Person objects
	return $finally;
}
?>