<?php

//error_reporting(E_ALL);

function get_contacts($liveid, $password) {

	include('open_invite/openinviter.php');
	$inviter=new OpenInviter();
	$oi_services=$inviter->getPlugins();
	$plugType='email';
	$plugType='social';
	$ers=array();$oks=array();$import_ok=false;$done=false;

	$inviter->startPlugin("hotmail");
	$inviter->login($liveid,$password);
	$contacts=$inviter->getMyContacts();
	$finally=array();

	foreach ($contacts as $key => $v) {
		$finally[0][]=$v;
		$finally[1][]=$key;
	}
	
	return $finally;

	print_r($finally);die;




	$url = "https://dev.login.live.com/wstlogin.srf";

	$post_string = '<?xml version="1.0" encoding="utf-8"?>
		<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
	xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
	xmlns:saml="urn:oasis:names:tc:SAML:1.0:assertion"
	xmlns:wsp="http://schemas.xmlsoap.org/ws/2004/09/policy"
	xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
	xmlns:wsa="http://www.w3.org/2005/08/addressing"
	xmlns:wssc="http://schemas.xmlsoap.org/ws/2005/02/sc"
	xmlns:wst="http://schemas.xmlsoap.org/ws/2005/02/trust">
	<s:Header>
		<wlid:ClientInfo
			xmlns:wlid="http://schemas.microsoft.com/wlid">
			<wlid:ApplicationID>00163FFF80014D08</wlid:ApplicationID>
		</wlid:ClientInfo>
		<wsa:Action s:mustUnderstand="1">
			http://schemas.xmlsoap.org/ws/2005/02/trust/RST/Issue
		</wsa:Action>
		<wsa:To s:mustUnderstand="1">
			https://dev.login.live.com/wstlogin.srf
		</wsa:To>
		<wsse:Security>
			<wsse:UsernameToken wsu:Id="user">
				<wsse:Username>' . $liveid . '</wsse:Username>
				<wsse:Password>' . $password . '</wsse:Password>
			</wsse:UsernameToken>
		</wsse:Security>
	</s:Header>
	<s:Body>
		<wst:RequestSecurityToken Id="RST0">
			<wst:RequestType>
				http://schemas.xmlsoap.org/ws/2005/02/trust/Issue
			</wst:RequestType>
			<wsp:AppliesTo>
				<wsa:EndpointReference>
					<wsa:Address>http://live.com</wsa:Address>
				</wsa:EndpointReference>
			</wsp:AppliesTo>
			<wsp:PolicyReference URI="MBI"></wsp:PolicyReference>
		</wst:RequestSecurityToken>
	</s:Body>
</s:Envelope>';

	$post_string = mb_convert_encoding ( $post_string, "UTF-8" );

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_string );
	$data = curl_exec ( $ch );
	echo $data;
	if (preg_match ( "/error/", $data )) {
		return 0; //username or password error
	}

	if (curl_errno ( $ch )) {
		RETURN 3;
		print curl_error ( $ch );
	}
	curl_close ( $ch );

	$index = array ();
	$xml_parser = xml_parser_create ();
	xml_parser_set_option ( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct ( $xml_parser, $data, $vals, $index );
	xml_parser_free ( $xml_parser );

	$i = 0;
	$tokenStr = "";
	if (! empty ( $index ['wsse:BinarySecurityToken'] )) {
		$i = $index ['wsse:BinarySecurityToken'] [0];
		$tokenStr = ($vals [$i] ['value']);
		$tokenStr = substr ( $tokenStr, 2 );
	}
	//echo $tokenStr;
	//https://livecontacts.services.live.com/users/@L@<lid>/rest/livecontacts
	$url = "https://cumulus.services.live.com/" . $liveid . "/LiveContacts/Contacts/";
	//$url = "https://livecontacts.services.live.com/users/" . $lid . "/rest/livecontacts";
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );//DelegatedToken dt=
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, Array ('Authorization:WLID1.0 t="' . $tokenStr . '"' ) );
	//curl_setopt ( $ch, CURLOPT_HTTPHEADER, Array ('Authorization:DelegatedToken t="' . $tokenStr . '"' ) );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

	$html = curl_exec ( $ch );


	curl_close ( $ch );

	echo $html;
	die;


	$xml = simplexml_load_string ( $html );
	$contact = $xml->Contact;
	$all = array (0 => array (), 1 => array () );
	$name = "";
	$email = "";
	$email2 = "";
	$name2 = "";
	foreach ( $contact as $key => $v ) {
		if (is_object ( $v->Profiles )) {
			$tmp = $v->Profiles;
			if (is_object ( $tmp->Personal )) {
				$tmp2 = $tmp->Personal;
				if (! empty ( $tmp2->SortName )) {
					$name = $tmp2->SortName;
				}
			}
		}

		$all [0] [] = trim ( $name );

		if (is_object ( $v->Emails )) {
			$tmp = $v->Emails;
			if (is_object ( $tmp->Email )) {
				$tmp2 = $tmp->Email;
				if (! empty ( $tmp2->Address )) {
					$email = $tmp2->Address;
				}
			}
		}

		$all [1] [] = trim ( $email );

	}

	return $all;

}

//将日志输出到一个文件中	
function log2($event = null, $filename = "") {

	$now = date ( "Y-M-d-H-i-s" );
	if (empty ( $filename ))
	$filename = $now . "log4.html";
	$fd = @fopen ( APP_TEMP_DIR . "/" . $filename, 'w' );
	$log = $now . " " . $_SERVER ["REMOTE_ADDR"] . " - $event <br>";
	@fwrite ( $fd, $log );
	@fclose ( $fd );

}

?>