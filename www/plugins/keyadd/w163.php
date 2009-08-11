<?
$username = $_POST ["username"];
$password = $_POST ["password"];

$htmlBoxTitle = mb_convert_encoding ( "我的3D形象", 'GBK', 'UTF-8' );
$htmlBoxContent = urlencode ( $_POST ["flash"] );
$scriptSessionId = isset($scriptSessionId)?$scriptSessionId:'';
$password = md5 ( $password );
$c = "callCount=1\n";
$c = $c . "scriptSessionId=${scriptSessionId}71\n";
$c = $c . "c0-scriptName=UserBean\n";
$c = $c . "c0-methodName=checkPassportVN\n";
$c = $c . "c0-id=0\n";
$c = $c . "c0-param0=string:$username\n";
$c = $c . "c0-param1=string:$password\n";
$c = $c . "c0-param2=boolean:false\n";
$c = $c . "c0-param3=number:0\n";
$c = $c . "c0-param4=boolean:false\n";
$c = $c . "c0-param5=boolean:false\n";
$c = $c . "c0-param6=boolean:false\n";
$c = $c . "batchId=3";
//登录网易


$rl = get1 ( "blog.163.com/dwr/call/plaincall/UserBean.checkPassport.dwr", $c, "", "text/plain" );

eregi ( "NTES_SESS=([^;]*)", $rl, $regex );

$authid = $regex [0];
$regex = null;
if (strpos ( $rl, "var s0=null;" ) != "" || $authid == "") {
	echo "no";
	exit ();
}

//获取layout地址
$rl = get ( "http://$username.blog.163.com/edit/", $authid );

eregi ( "visitorId[[:space:]]*\:[[:space:]]*([[:digit:]]*)\,", $rl, $regex );
$visitorId = $regex [1];
$regex = null;

eregi ( "dataDigest[[:space:]]*\:[[:space:]]*\'([[:digit:]]*)\'\,", $rl, $regex );
$dataDigest = $regex [1];
$regex = null;

$s = strpos ( $rl, "src=\"http://blog.163.com/$username/$dataDigest" );
$e = strpos ( $rl, "edit.js", $s );

$s = $s + 5;
$el = $e + 7 - $s;
$layouturl = substr ( $rl, $s, $el );

if ($visitorId == "" || $dataDigest == "" || $layouturl == "") {
	echo "no";
	exit ();
}

$rl = get ( "$layouturl", $authid );

eregi ( "\"userId\":$visitorId,\"pageModuleStr\":\"([0-9,;]*)\"", $rl, $regex );
$layoutWords = urlencode ( $regex [1] );
$regex = null;

/*  modify above code : */
eregi ( "\"themeId\"\:([0-9]*),", $rl, $regex );
$pageThemeId = $regex [1];
$regex = null;
eregi ( "\"pageLayoutId\"\:([0-9]*),", $rl, $regex );
$pageLayoutID = $regex [1];
$regex = null;
//"id":84028213,"styleThemeType":1
eregi ( "\"id\":([0-9]*),\"styleThemeType\"\:([0-9]*)", $rl, $regex );

$unkonwId = $regex [1];
$styleThemeType = $regex [2];

/* modify end---- */
if ($pageThemeId == "" || $pageLayoutID == "" || $styleThemeType == "") {
	echo "no";
	exit ();
}

//添加htmlbox
$c = "callCount=1\n";
$c = $c . "scriptSessionId=${scriptSessionId}925\n";
$c = $c . "c0-scriptName=CustomHtmlBean\n";
$c = $c . "c0-methodName=addCustomHtml\n";
$c = $c . "c0-id=0\n";
$c = $c . "c0-e1=string:$htmlBoxContent\n";
$c = $c . "c0-e2=string:$htmlBoxTitle\n";
$c = $c . "c0-e3=string:10\n";
$c = $c . "c0-param0=Object:{htmlCode:reference:c0-e1, chModuleName:reference:c0-e2, id:reference:c0-e3}\n";
$c = $c . "batchId=15";
//$rl = get1("http://$username.blog.163.com/edit/dwr/exec/CustomHtmlBean.addCustomHtml.dwr",$c,$authid,"text/plain");
$rl = get1 ( "http://$username.blog.163.com/edit/dwr/call/plaincall/CustomHtmlBean.addCustomHtml.dwr", $c, $authid, "text/plain" );

$s = strpos ( $rl, "var s3=" ) + 7;
$e = strpos ( $rl, ";", $s );
$el = $e - $s;
$s3 = substr ( $rl, $s, $el );

$s = strpos ( $rl, "var s4=" ) + 7;
$e = strpos ( $rl, ";", $s );
$el = $e - $s;
$s4 = substr ( $rl, $s, $el );
$regex = null;

/* add by jacky 2008-6-19 **/
$rl = get ( "$layouturl", $authid );

eregi ( "\"title\":\"$htmlBoxTitle\",\"type\":10,\"userId\":$visitorId,\"column\":0,\"loaded\":false,\"content\":\"\",\"size\":true,\"id\":([0-9]*)", $rl, $regex );

/****  end ***/

$s3 = $regex [1];
$s4 = $visitorId;
$regex = null;

//存储布局
$c = "callCount=1\n";
$c = $c . "scriptSessionId=${scriptSessionId}582\n";
$c = $c . "c0-scriptName=ModuleTemplateBean\n";
$c = $c . "c0-methodName=getEditHtml\n";
$c = $c . "c0-id=0\n";
$c = $c . "c0-param0=number:$s3\n";
$c = $c . "c0-param1=number:10\n";
$c = $c . "c0-param2=number:50\n";
$c = $c . "c0-param3=number:0\n";
$c = $c . "c0-param4=boolean:false\n";
$c = $c . "batchId=17";

//$rl = get1("http://$username.blog.163.com/edit/dwr/exec/ModuleTemplateBean.getEditHtml.dwr",$c,$authid,"text/plain");
$rl = get1 ( "http://$username.blog.163.com/edit/dwr/call/plaincall/ModuleTemplateBean.getEditHtml.dwr", $c, $authid, "text/plain" );

//$layoutWords = rawurlencode("$s3,$layoutWords");
$layoutWords = urlencode ( "$s3," ) . $layoutWords;
//echo "<br>s4:".$s4."<br>s3:".$s3."<br>pageLayoutID:".$pageLayoutID."<br>".$layoutWords."<br>";


$c = "callCount=1\n";
$c = $c . "scriptSessionId=${scriptSessionId}529\n";
$c = $c . "c0-scriptName=HomepageSetupBean\n";
$c = $c . "c0-methodName=savePageSetup\n";
$c = $c . "c0-id=0\n";
$c = $c . "c0-e1=number:$unkonwId\n";
$c = $c . "c0-e2=number:$s4\n";
$c = $c . "c0-e3=string:$layoutWords\n";
$c = $c . "c0-e4=number:$pageThemeId\n";
$c = $c . "c0-e6=number:$pageLayoutID\n"; //4
$c = $c . "c0-e7=string:66%2C34\n";
$c = $c . "c0-e8=number:2\n";

$c = $c . "c0-e5=Object_Object:{id:reference:c0-e6, colPercentStr:reference:c0-e7, columnCount:reference:c0-e8}\n";
$c = $c . "c0-param0=Object_Object:{id:reference:c0-e1, userId:reference:c0-e2, pageModuleStr:reference:c0-e3, themeId:reference:c0-e4, pageLayout:reference:c0-e5}\n";
$c = $c . "c0-param1=boolean:false\n";
$c = $c . "batchId=18\n";

//$rl = get1("http://$username.blog.163.com/edit/dwr/exec/HomepageSetupBean.savePageSetup.dwr",$c,$authid,"text/plain");
$rl = get1 ( "http://$username.blog.163.com/edit/dwr/call/plaincall/HomepageSetupBean.savePageSetup.dwr", $c, $authid, "text/plain" );

echo "ok";
exit ();

function get1($url, $content, $cookies, $ctype) {
	$url = eregi_replace ( '^http://', '', $url );
	$temp = explode ( '/', $url );
	$host = array_shift ( $temp );
	$path = '/' . implode ( '/', $temp );
	$temp = explode ( ':', $host );
	$host = $temp [0];
	$port = isset ( $temp [1] ) ? $temp [1] : 80;
	
	$contentlen = strlen ( $content );
	
	$fp = @fsockopen ( $host, $port, &$errno, &$errstr, 30 );
	if ($fp) {
		@fputs ( $fp, "POST $path HTTP/1.1\n" );
		@fputs ( $fp, "Host: $host\n" );
		@fputs ( $fp, "Accept: */*\n" );
		@fputs ( $fp, "Accept-Language: zh-cn\n" );
		@fputs ( $fp, "Content-Type: $ctype\n" );
		//@fputs($fp, "Accept-Encoding: gzip, deflate\n");
		@fputs ( $fp, "Referer: http://$host/\n" );
		@fputs ( $fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\n" );
		@fputs ( $fp, "Pragma: no-cache\n" );
		@fputs ( $fp, "Content-Length: $contentlen\n" );
		if ($cookies != "") {
			@fputs ( $fp, "Cookie: $cookies;\n" );
		}
		
		@fputs ( $fp, "\n" );
		@fputs ( $fp, "$content\n" );
	
	}
	
	$Content = '';
	while ( $str = @fread ( $fp, 4096 ) )
		$Content .= $str;
	
	@fclose ( $fp );
	if (trim ( $Content ) == "") {
		echo "no";
		exit ();
	}
	return $Content;
}

function get($url, $cookies) {
	$url = eregi_replace ( '^http://', '', $url );
	$temp = explode ( '/', $url );
	$host = array_shift ( $temp );
	$path = '/' . implode ( '/', $temp );
	$temp = explode ( ':', $host );
	$host = $temp [0];
	$port = isset ( $temp [1] ) ? $temp [1] : 80;
	
	//$contentlen=strlen($content);
	

	$fp = @fsockopen ( $host, $port, &$errno, &$errstr, 30 );
	if ($fp) {
		@fputs ( $fp, "GET $path HTTP/1.1\n" );
		@fputs ( $fp, "Host: $host\n" );
		@fputs ( $fp, "Accept: */*\n" );
		@fputs ( $fp, "Accept-Language: zh-cn\n" );
		@fputs ( $fp, "Referer: http://$host/\n" );
		@fputs ( $fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\n" );
		@fputs ( $fp, "Pragma: no-cache\n" );
		
		if ($cookies != "") {
			@fputs ( $fp, "Cookie: $cookies;\n" );
		}
		
		@fputs ( $fp, "\n" );
	
	}
	
	$Content = '';
	while ( $str = @fread ( $fp, 4096 ) )
		$Content .= $str;
	
	@fclose ( $fp );
	if (trim ( $Content ) == "") {
		echo "no";
		exit ();
	}
	return $Content;
}
?>