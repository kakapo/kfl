<?php
include_once ("sdk/appinclude.php");

$user = $OpenApp_51->get_user ();
error_log ( "xxxxxxxxxxxxxin app_nanme2" );
error_log ( var_export ( $user, true ) );
$userInfo = $OpenApp_51->api_client->users_getInfo ( array ($user ), array ("user", "sex", "facebig", "facesmall", "face", "nickname" ) );
error_log ( var_export ( $userInfo, true ) );
$userInfo = array ("uid" => $userInfo [0] ['uid'], "real_name" => $userInfo [0] ['nickname'], "nick" => $userInfo [0] ['nickname'], "logo120" => $userInfo [0] ['facebig'], "logo50" => $userInfo [0] ['face'], "logo25" => $userInfo [0] ['facesmall'] );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>缘分测试 -</title>
<link href="/css/s.css" rel="stylesheet" type="text/css" />
<script src="/js/head.js" language="JavaScript" type="text/javascript"></script>
<script src="/js/common.js" type="text/javascript"></script>
<script src="/js/prototype-1.4.0.js" language="JavaScript"
	type="text/javascript"></script>
<script src="/js/s.js" language="JavaScript" type="text/javascript"></script>
<script src="/js/cookie.js" type="text/javascript"></script>
<script src="/js/dialog.js" language="JavaScript" type="text/javascript"></script>
<script src="/js/app_friend.js" language="JavaScript"
	type="text/javascript"></script>
<script src="/js/swfobject.js" type="text/javascript"></script>
<link href="/css/z.css" rel="stylesheet" type="text/css" />
<script language=javascript src="/js/friend_singlesuggest.js"></script>

</head>

<body
	onload="javascript:if ('function' == typeof(_bodyonload)) { _bodyonload(event); };"
	onunload="javascript:if ('function' == typeof(_bodyonunload)) { _bodyonunload(event); };"
	onbeforeunload="javascript:if ('function' == typeof(_bodyonbeforeunload)) { _bodyonbeforeunload(event); };">
<script language="JavaScript">
</script>

<div id="main">
<link href="/css/z.css" rel="stylesheet" type="text/css" />
<script language=javascript src="/js/friend_singlesuggest.js"></script>
<script type="text/javascript">

var fs2_data = [];
var fs2_pars = "f1";
var this_uid = parseInt("150190");
var fs2_sig = "";
 
function fs2_onclear()
{
	$("realname").style.display = "none";
}

function fs2_onrefresh(r)
{
	$("rn2").value = r.real_name;
	
	$("realname").style.display = "block";
}

function _bodyonload()
{
	fs2_superView();
}

function checkGift()
{
	var gifts = document.form1.gid;
	for(var i=0 ; i<gifts.length ; i++)
	{
		if(gifts[i].checked)
		{
			return gifts[i].value;
		}
	}
	return false;
}

function send()
{	
	if(!fs2_data[0] || fs2_data[0].type == "active")
	{
		alert("请先选择你跟哪位好友进行姓名缘分测试！");
		return false;
	}
	
	if ("none" == $("realname").style.display)
	{
		alert("请先选择你跟哪位好友进行姓名缘分测试！");
		return false;
	}

	//$("submit").disabled = true;
	//var content = document.form1.content.value;
	
	var url = "/match.php";
	
	var reveuid = fs2_data[0].uid;
	if (this_uid == reveuid)
	{
		alert("自己和自己也要看看缘分吗=_= ");
		return false;
	}

	var rn1 = $("rn1").value;
	var rn2 = $("rn2").value;
	
	if (rn1=="" || rn2=="")
	{
		alert("真实姓名不能为空");
		return false;
	}
		
	var toanother = $("toanother").checked;
	var tofriends = $("tofriends").checked;
	
	//alert(reveuid);
	var pars = "reveuid=" + reveuid + "&rn1=" + encodeURIComponent(rn1) + "&rn2=" + encodeURIComponent(rn2) + "&ta=" + toanother + "&tf=" + tofriends;
	
	var myAjax = new Ajax.Request(url, {method: "get", parameters: pars, onComplete: function (req) { sendShow(req); } });
}


function sendShow(req)
{
	var r = req.responseText;
	var reveuid = fs2_data[0].uid;
	var rn1 = $("rn1").value;
	var rn2 = $("rn2").value;
	
	var pars = "reveuid=" + reveuid + "&rn1=" + encodeURIComponent(rn1) + "&rn2=" + encodeURIComponent(rn2);
	var rUrl = "/result.php?" + pars;
	
	self.location.href= rUrl ;
	return true;
}

function clear()
{
	fs2_data = [];
	fs2_superView();
	
	var gifts = document.form1.gid;
	for(var i=0 ; i<gifts.length ; i++)
	{
		gifts[i].checked = false;
	}
	
	document.form1.quiet.checked = false;
	document.form1.anon.checked = false;
	document.form1.content.value = "";

}

function contentChange(thisobj)
{
	if(thisobj.value.length>200)
	{
		thisobj.value=thisobj.value.substr(0,200);
	}
}

</script> <!----中间内容---->
<div class="m2 wr1">
<div id="r2_2">
<div id="r3">
<div class="l"><img src="/i/ico_match2.gif" align="absmiddle" /> <b
	class="f14">缘分测试</b></div>
<div class="r"><a href="javascript:window.history.back();" class="sl"
	title="返回上一页">&lt;&lt;返回上一页</a></div>
<div class="c"></div>
</div>
<div class="p10">
<div class="bqc_bg">
<div class="bqc_on">开始测试</div>
<div class="bqc_of" onmouseover="this.className='bqc_of_mo';"
	onmouseout="this.className='bqc_of';"
	onclick="window.location='/result.php';">测试结果</div>
<div class="l"><img src="/i/bqc_or.gif" /></div>
</div>
</div>
<div class="czhy_zx">
<div class="p30">
<div class="l l120_s"><img width="120" height="120"
	src="<?php
	echo $userInfo ["logo120"];
	?>" /></div>
<div class="l w120 tac" style="padding-top: 55px;"><img src="/i/vs.gif"
	align="absmiddle" /></div>
<div class="l l120_s" id="icon2"><img id="icon120" width="120"
	height="120" src="/i/120_0_0.gif" /></div>
</div>
<div class="c"></div>
<div class="l w300">&nbsp;</div>
<div class="l" style="margin-left: 10px;">
<div class="it_s" style="width: 135px;">

<div class="it1">

<div id="superinput"
	style="cursor: text; height: 23px; float: left; width: 130px;"
	onclick="fs2_superOnclick()">&nbsp;</div>

<div class="c"></div>

</div>
</div>
</div>

<div class="l"
	style="padding: 3px 2px 0px 0px; position: relative; margin: 3px 2px 0 0;">

<div id="xx_sh" onclick="fs2_viewAllfriend();"><img src="/i/xx_xx1.gif"
	class="cp" onmouseover="this.src='/i/xx_xx2.gif';"
	onmouseout="this.src='/i/xx_xx1.gif';" alt="选择好友" /></div>
<div class="fsg_nr" style="width: 310px;" id="fsg_nr">
<div class="sgt_on" style="width: 300px;">请选择测试对象</div>
<div id="allfriend"
	style="width: 300px; height: 100px; overflow: scroll; overflow-x: hidden;"></div>
<div class="tac p5">
<div class="gbs1"><input type="button" id="btn_qd" value="确定" title="确定"
	class="gb1-12" onmouseover="this.className='gb2-12';"
	onmouseout="this.className='gb1-12';" onclick="fs2_selectFriend();" /></div>
<div class="c"></div>
</div>
</div>
</div>


<div id="result" style="display: none;">

<div class="xxkarea">
<div class="l p010"><img id="resultimage" src="/i/fzcg_dh.gif"
	align="absmiddle" /></div>
<div id="resultword" class="l f13 w480"></div>
<div class="c"></div>
</div>

<div class="c"></div>
</div>

<div class="c">&nbsp;</div>

<div id="realname" style="display: none;">
<div class="qh l">昵称：</div>
<div class="l"><input disabled type="text" id="rn1" name="rn1"
	value="<?php
	echo $userInfo ["nick"];
	?>"
	style="width: 100px; height: 15px; font-size: 14px; border: solid 1px #818181" /></div>
<div class="qh l" style="padding-left: 82px;">昵称：</div>
<div class="l"><span class="it_s"><input type="text" id="rn2" name="rn2"
	value="" style="width: 100px; height: 15px; font-size: 14px;"
	class="it1" onfocus="this.className='it2';"
	onblur="this.className='it1';" /></span></div>
<div class="c"></div>
</div>


<div style="padding: 30px 0 6px 192px;"><input type="checkbox"
	id="toanother" name="toanother" checked />通知对方测试结果<br />
<input type="checkbox" id="tofriends" name="tofriends" />显示在好友动态里<br />
<div class="mb5"></div>
<div class="rbs1"><input type="button" id="btn_fb"
	value="&nbsp;&nbsp;&nbsp;开始测试&nbsp;&nbsp;&nbsp;" class="rb1"
	onmouseover="this.className='rb2';" onmouseout="this.className='rb1';"
	onclick="javascript:send();" /></div>
</div>


<div class="c"></div>

</div>
<div class="h100"></div>
</div>
</div>



<div class="c"></div>
</div>

</body>
</html>
