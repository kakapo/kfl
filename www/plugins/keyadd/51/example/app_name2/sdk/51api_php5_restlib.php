<?php
header ( 'Cache-control: no-cache, no-store, must-revalidate' );
require_once ("xmlParser_Open51.php");
/* 
  +-----------------------------------------------------------------------------+
  | 51.com Platform PHP5 client	                                                |
  +-----------------------------------------------------------------------------+
  | Copyright (c) 2008 51.com, Inc.                                             |
  | All rights reserved.                                                        |
  |	                                                                            |
  | Redistribution and use in source and binary forms, with or without          |
  | modification, are permitted provided that the following conditions          |
  | are met:                                                                    |
  |	                                                                            |
  | 1. Redistributions of source code must retain the above copyright           |
  |    notice, this list of conditions and the following disclaimer.            |
  | 2. Redistributions in binary form must reproduce the above copyright        |
  |    notice, this list of conditions and the following disclaimer in the      |
  |    documentation and/or other materials provided with the distribution.     |
  |	                                                                            |
  | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR        |
  | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES   |
  | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.     |
  | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,            |
  | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT    |
  | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,   |
  | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY       |
  | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT         |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF    |
  | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.           |
  +-----------------------------------------------------------------------------+
  | For help with this library, contact slei@mail.51.com                        |
  +-----------------------------------------------------------------------------+
*/

if (! defined ( "FIVEONE_OP_API_DOMAIN" )) {
	define ( "FIVEONE_OP_API_DOMAIN", "api" );
}

class FiveOneRestClient {
	var $app_key;
	var $app_secret;
	var $session_key;
	var $user;
	var $time;
	var $friends_list;
	
	var $request_file; // ����Ľӿ��ļ����
	

	var $php5_sdk_version = "0_1"; // the version of the SDK
	

	/**
	 * Create the client.
	 * @param string $session_key if you haven't gotten a session key yet, leave
	 *                            this as null and then set it later by just
	 *                            directly accessing the $session_key member
	 *                            variable.
	 */
	function __construct($app_key, $app_secret, $session_key = null, $user = null, $time = null) {
		$this->app_key = $app_key;
		$this->app_secret = $app_secret;
		$this->session_key = $session_key;
		$this->user = $user;
		$this->time = $time;
		
		$this->request_file = "restserver_" . $this->php5_sdk_version . ".php";
		$this->server_addr = OpenApp_51::get_fiveone_url ( FIVEONE_OP_API_DOMAIN ) . "/" . $this->request_file;
	}
	
	/**
	 * Compatible php4
	 */
	function FiveOneRestClient($app_key, $app_secret, $session_key = null, $user = null, $time = null) {
		$this->__construct ( $app_key, $app_secret, $session_key, $user, $time );
	}
	
	/**
	 * Returns the friends of the current session user.
	 * @return array of friends
	 */
	function &friends_get($uid = null) {
		if (isset ( $this->friends_list )) {
			return $this->friends_list;
		}
		return $this->call_method ( 'fiveone.friends.get', array ('uid' => $uid ) );
	}
	
	/**
	 * Returns whether or not pairs of users are friends or reverse friends.
	 * @param array $uids1: array of ids (id_1, id_2,...) of some length X
	 * @param array $uids2: array of ids (id_A, id_B,...) of SAME length X
	 * @return array of uid pairs with bool, true if pair are friends, and ture if pari are reverse friends, e.g.
	 *   array( 0 => array('uid1' => id_1, 'uid2' => id_A, 'are_friends' => 1, 'are_friends_reverse' => 0),
	 *          1 => array('uid1' => id_2, 'uid2' => id_B, 'are_friends' => 0, 'are_friends_reverse' => 1)
	 *         ...)
	 */
	function &friends_areFriends($uids1, $uids2) {
		return $this->call_method ( 'fiveone.friends.areFriends', array ('uids1' => $uids1, 'uids2' => $uids2 ) );
	}
	
	/**
	 * Returns the requested info fields for the requested set of users
	 * @param array $uids an array of user ids
	 * @param array $fields an array of strings describing the info fields desired
	 * @return array of users
	 */
	function &users_getInfo($uids, $fields) {
		return $this->call_method ( 'fiveone.users.getInfo', array ('uids' => $uids, 'fields' => $fields ) );
	}
	
	/**
	 * Returns the requested info fields for the requested set of users
	 * @param array $uids an array of user ids
	 * @param array $fields an array of strings describing the info fields desired
	 * @return array of homes
	 */
	function &homes_getInfo($uids, $fields) {
		return $this->call_method ( 'fiveone.homes.getInfo', array ('uids' => $uids, 'fields' => $fields ) );
	}
	
	/**
	 * Returns the tags on all photos specified.
	 * @param string $uid : a uid to query
	 * @return pictures user recommended at homepage
	 */
	function &photos_getHome($uid = null) {
		$param = $uid ? array ('uid' => strtolower ( trim ( $uid ) ) ) : array ('uid' => strtolower ( trim ( $this->user ) ) );
		return $this->call_method ( 'fiveone.photos.getHome', $param );
	}
	
	/**
	 * Returns the albums created by the given user.
	 * @param string $uid Optional: the user whose albums you want.
	 *   A null value will return the albums of the session user.
	 * @param array $aids Optional: a list of aids to restrict the query.
	 * Note that at least one of the (uid, aids) parameters must be specified.
	 * @returns an array of album objects.
	 */
	function &photos_getAlbums($uid = null, $aids = null) {
		$param = $uid ? array ('uid' => strtolower ( trim ( $uid ) ), 'aids' => $aids ) : array ('uid' => strtolower ( trim ( $this->user ) ), 'aids' => $aids );
		return $this->call_method ( 'fiveone.photos.getAlbums', array ('uid' => $uid, 'aids' => $aids ) );
	}
	
	/**
	 * Returns photos according to the filters specified.
	 * @param string $uid Optional: the user whose photos you want.
	 *   A null value will return the photos of the session user.
	 *	If you want session user's photo,set 'null'
	 * @param int $aid Optional: Filter by an album, as returned by photos_getAlbums.
	 * @param array $pids Optional: Restrict to a list of pids
	 * Note that at least one of the (aid, pids) parameters must be specified.
	 * @returns an array of photo objects.
	 */
	function &photos_get($uid, $aid, $pids) {
		$curr_user = $uid ? $uid : $this->user;
		return $this->call_method ( 'fiveone.photos.get', array ('uid' => $curr_user, 'aid' => $aid, 'pids' => $pids ) );
	}
	
	/**
	 * Returns the current user.
	 * @return an array of username and a key generated by username and app secret key
	 */
	function &users_getLoggedInUser() {
		$login_user = OpenApp_51::get_user ();
		if ($login_user) {
			$arr [0] ['uid'] = $login_user;
			$arr [0] ['token'] = md5 ( $appsecret . $login_user );
		} else {
			$arr [0] ['uid'] = "";
			$arr [0] ['token'] = "";
		}
		return $arr;
	}
	
	/**
	 * post params to the API at 51.com
	 */
	function create_post_string($method, $params) {
		
		$namespace = "51_sig";
		
		$params ['user'] = $this->user;
		$params ['session_key'] = $this->session_key;
		$params ['app_key'] = $this->app_key;
		$params ['time'] = $this->time;
		$params ['method'] = $method;
		
		$prefix = $namespace . '_';
		$prefix_len = strlen ( $prefix );
		$fb_params = array ();
		$post_data = "";
		
		foreach ( $params as $name => $val ) {
			if (is_array ( $val )) {
				$val = implode ( ',', $val );
			}
			$params [$name] = OpenApp_51::no_magic_quotes ( $val );
		}
		
		$fb_params [$namespace] = OpenApp_51::generate_sig ( $params, $this->app_secret );
		
		foreach ( $params as $name => $val ) {
			if (is_array ( $val )) {
				$val = implode ( ',', $val );
			}
			$fb_params [$prefix . $name] = OpenApp_51::no_magic_quotes ( $val );
		}
		
		foreach ( $fb_params as $k => $v ) {
			$post_data .= $k . "=" . $v . "&";
		}
		return $post_data;
	}
	
	/**
	 * Interprets a string of XML into an array
	 */
	function convert_xml_to_result($xml, $method, $params) {
		
		$xmlParser = new xmlParser_Open51 ( );
		$result = $xmlParser->xmlParse ( $xml, "UTF-8" );
		
		return $result;
	}
	
	function post_request($method, $params) {
		
		$post_string = $this->create_post_string ( $method, $params );
		$result = httpRequest ( $this->server_addr, $post_string );
		return $result;
	}
	
	/**
	 * Performs a character set conversion on the string str from utf-8 to gbk
	 * @param	string	str		the string want to convert
	 * @param	bool	ignore	if ignore the chars that represented failed,
	 * @return	string	str		represented string
	 */
	function utf2Gbk($str, $ignore = true) {
		if ($ignore) {
			return iconv ( "utf-8", "gbk//IGNORE", $str );
		} else {
			return iconv ( "utf-8", "gbk", $str );
		}
	}
	
	function convert_simplexml_to_array($sxml) {
		$arr = array ();
		if ($sxml) {
			foreach ( $sxml as $k => $v ) {
				if ($sxml ['list']) {
					$arr [] = self::convert_simplexml_to_array ( $v );
				} else {
					$tmp = self::convert_simplexml_to_array ( $v );
					$arr [$k] = self::utf2Gbk ( $tmp );
				}
			}
		}
		if (count ( $arr ) > 0) {
			return $arr;
		} else {
			return ( string ) $sxml;
		}
	}
	
	/* UTILITY FUNCTIONS */
	function &call_method($method, $params) {
		
		$params ['sdk_from'] = "php";
		$retStr = $this->post_request ( $method, $params );
		if ($retStr == false) {
			$arr [0] ['error_code'] = 2;
			$arr [0] ['error_msg'] = "Service temporarily unavailable";
			return $arr;
		}
		$result = $this->convert_xml_to_result ( $retStr, $method, $params );
		if ($result === "") {
			$result = array ();
		}
		return $result;
	}
} // end class


/**
 * http post
 */
function httpRequest($url, $post_string, $connectTimeout = 5, $readTimeout = 10) {
	$result = "";
	if (function_exists ( 'curl_init' )) {
		$timeout = $connectTimeout + $readTimeout;
		// Use CURL if installed...
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_string );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, '51.com API PHP5 Client 1.1 (curl) ' . phpversion () );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
	} else {
		// Non-CURL based version...
		$result = socketPost ( $url, $post_string, $connectTimeout = 5, $readTimeout = 10 );
	}
	return $result;
}

/**
 * http post
 */
function socketPost($url, $post_string, $connectTimeout = 5, $readTimeout = 10) {
	$urlInfo = parse_url ( $url );
	$urlInfo ["path"] = ($urlInfo ["path"] == "" ? "/" : $urlInfo ["path"]);
	$urlInfo ["port"] = ($urlInfo ["port"] == "" ? 80 : $urlInfo ["port"]);
	$hostIp = gethostbyname ( $urlInfo ["host"] );
	
	$urlInfo ["request"] = $urlInfo ["path"] . (empty ( $urlInfo ["query"] ) ? "" : "?" . $urlInfo ["query"]) . (empty ( $urlInfo ["fragment"] ) ? "" : "#" . $urlInfo ["fragment"]);
	
	$fsock = fsockopen ( $hostIp, $urlInfo ["port"], $errno, $errstr, $connectTimeout );
	if (false == $fsock) {
		return false;
	}
	/* begin send data */
	$in = "POST " . $urlInfo ["request"] . " HTTP/1.0\r\n";
	$in .= "Accept: */*\r\n";
	$in .= "User-Agent: 51.com API PHP5 Client 1.1 (non-curl)\r\n";
	$in .= "Host: " . $urlInfo ["host"] . "\r\n";
	$in .= "Content-type: application/x-www-form-urlencoded\r\n";
	$in .= "Content-Length: " . strlen ( $post_string ) . "\r\n";
	$in .= "Connection: Close\r\n\r\n";
	$in .= $post_string . "\r\n\r\n";
	
	stream_set_timeout ( $fsock, $readTimeout );
	if (! fwrite ( $fsock, $in, strlen ( $in ) )) {
		fclose ( $fsock );
		return false;
	}
	unset ( $in );
	
	//process response
	$out = "";
	while ( $buff = fgets ( $fsock, 2048 ) ) {
		$out .= $buff;
	}
	//finish socket
	fclose ( $fsock );
	$pos = strpos ( $out, "\r\n\r\n" );
	$head = substr ( $out, 0, $pos ); //http head
	$status = substr ( $head, 0, strpos ( $head, "\r\n" ) ); //http status line
	$body = substr ( $out, $pos + 4, strlen ( $out ) - ($pos + 4) ); //page body
	if (preg_match ( "/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches )) {
		if (intval ( $matches [1] ) / 100 == 2) { //return http get body
			return $body;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>