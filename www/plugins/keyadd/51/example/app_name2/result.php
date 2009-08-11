<?php
include_once ("sdk/appinclude.php");
include_once ("db.php");

$src_user = $OpenApp_51->get_user ();
$sql = "select * from user_match where src_user='$src_user' or tar_user='$src_user'";

DEFINE ( 'DB_DSN', 'your dsn here' );
$db = new Database ( );
$db->connect ( DB_DSN );
$res = $db->find ( $sql );
error_log ( var_export ( $res, true ) );

$friends = array ();
foreach ( $res as $row ) {
	$friends [] = $row ["tar_user"];
	$friends [] = $row ["src_user"];
}
$friends = array_unique ( $friends );
error_log ( var_export ( $friends, true ) );

$arrInfo = $OpenApp_51->api_client->users_getInfo ( $friends, array ("user", "sex", "facebig", "facesmall", "face", "nickname" ) );
error_log ( var_export ( $friends, true ) );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>缘分 -</title>
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
init();

</script>

<div id="main">
<link href="/css/z.css" rel="stylesheet" type="text/css" />
<script language=javascript src="/js/friend_singlesuggest.js"></script>


<script type="text/javascript">
setTimeout('eval("h(\'resultarea\')")', 3000);	
</script>

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

<div class="bqc_of1" onmouseover="this.className='bqc_of1_mo';"
	onmouseout="this.className='bqc_of1';"
	onclick="window.location='/index.php';">开始测试</div>
<div class="bqc_on">测试结果</div>
<div class="l"><img src="/i/bqc_or.gif" /></div>
</div>
</div>
<div class="czhy_zx">
<div class="">

<div class="xxkarea" id="resultarea"
	style="margin-left: 0px; display: none;">
<p><img src="/i/fzcg_dh.gif" align="absmiddle" /></p>
<p class="f13 w480">你与的缘分测试完成！请查看以下结果：</p>
<div class="c"></div>
</div>


<span class="c6">以下是您与<?php
echo count ( $res );
?>位好友的缘分测试结果</span>
				<?php
				error_log ( var_export ( $res, true ) );
				error_log ( var_export ( $arrInfo, true ) );
				for($i = 0; $i < count ( $res ); $i ++) {
					$src_user = $res [$i] ["src_user"];
					$tar_user = $res [$i] ["tar_user"];
					for($j = 0; $j < count ( $arrInfo ); $j ++) {
						if ($src_user == $arrInfo [$j] ["user"]) {
							$src_img = $arrInfo [$j] ["face"];
							$src_nick = $arrInfo [$j] ["nickname"];
						} elseif ($tar_user == $arrInfo [$j] ["user"]) {
							$tar_img = $arrInfo [$j] ["face"];
							$tar_nick = $arrInfo [$j] ["nickname"];
						}
					}
					?>
				<div class="bqarea_w" style="margin-left: 0px;">
<div class="bqarea_n" style="padding-bottom: 0px;">`
<div class="l50_s l" style="height: 75px;"><a
	href="<?php
					echo "http://$src_user.51.com";
					?>"> <img src="<?php
					echo $src_img;
					?>" width="50"
	height="50" align="absmiddle" style="padding-left: 7px;" /></a>
<div class="mt10 tac"><strong class="f14 sl"><?php
					echo $src_nick;
					?></strong></div>
</div>
<div class="l w65 tac" style="padding-top: 20px;"><img src="/i/vs.gif" />
</div>
<div class="l50_s l" style="height: 75px;"><a
	href="<?php
					echo "http://$tar_user.51.com";
					?>"> <img src="<?php
					echo $tar_img;
					?>" width="50"
	height="50" align="absmiddle" style="padding-left: 7px;" /></a>
<div class="mt10 tac"><strong class="f14 sl"><?php
					echo $tar_nick;
					?></strong></div>
</div>
<div class="l w65">&nbsp;</div>

<div class="l f14 p10"><strong>缘分潜力：<span class="qh"><?php
					echo $res [$i] ["matched"];
					?></span></strong></div>
<div class="c"></div>
<div class="c tar c9">本测试由<?Php
					echo $src_nick;
					?>发起</div>
</div>
</div>
				<?php
				}
				?>

			</div>
<br />

</div>
<div class="h200"></div>
</div>
</div>




<div class="c"></div>
</div>


</body>
</html>
