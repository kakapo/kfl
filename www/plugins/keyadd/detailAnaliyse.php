<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Google</title>
<style type="text/css">
.tongji{ width:200px;}
.tongji .list_div{ width:85px; float:left; margin-right:3px; border:#ddd solid 0px;}
.tongji .list_div .tit_div{ width:70px; float:left;}
.tongji .list_div .tit_div .tit{ width:70px; height:23px;}
.tongji .list_div .pic{ width:15px; text-align:center; font:Tahoma; font-size:12px;}

</style>
</head>

<?php
 
date_default_timezone_set('PRC');
 
$server_addr2=array(
				 
					1=>"http://222.179.185.83/logs/qqloginlog.html", 
 					2=>"http://125.46.39.25/logs/qqloginlog.html", 
					3=>"http://60.2.152.181/logs/qqloginlog.html", 
					4=>"http://123.130.124.158/logs/qqloginlog.html", 
					5=>"http://222.189.239.167/logs/qqloginlog.html", 
					6=>"http://58.211.84.121/logs/qqloginlog.html", 
					7=>"http://58.221.37.110/logs/qqloginlog.html", 
					8=>"http://113.11.194.22/logs/qqloginlog.html", 
					9=>"http://122.224.199.240/logs/qqloginlog.html",  
					11=>"http://116.11.32.14/logs/qqloginlog.html" ,
					12=>"http://61.142.66.91/logs/qqloginlog.html" ,
					13=>"http://222.135.146.160/logs/qqloginlog.html" ,
					14=>"http://122.11.51.222/logs/qqloginlog.html" ,
					15=>"http://117.34.73.31/logs/qqloginlog.html" ,
					16=>"http://dl.guodong.com/logs/qqloginlog.html" 
);

$second=60;
 

if(isset($_GET['second'])) $second=intval($_GET['second']);


if(isset($_GET['first_time'])){
	$first_time=strtotime($_GET['first_time']);
}else{
	$first_time=time()-3600*8;
}

if(isset($_GET['end_time'])){
	$end_time=strtotime($_GET['end_time']);
}else{
	$end_time=time()-3600;
}


?>

<form name="form1" method="get" action=""  >
  开始时间：
  <input name="first_time" type="text" size="15" value="<?=date('y-m-d H:i:s',$first_time)?>">
  结束时间：
  <input name="end_time" type="text" size="15" value="<?=date('y-m-d H:i:s',$end_time)?>">
  时间间隔：
  
  <input name="second" type="text" id="second" size="10" value="<?=$second?>">
秒 ------服务器选择:
<select name="server">
<?php
if(isset($_GET['server'])) {
list($ser_key,$ser_value)=explode("=>",$_GET['server']);
	 echo ' <option value="'.$ser_key.'=>'.$ser_value.'" selected="selected">'.$ser_value.'</option>';
}

$test=array();
foreach ($server_addr2 as $xx=>  $url){
 echo '<option value="'.$xx.'=>'."logs/".$xx.".html".'" >'."logs/".$xx.".html".'</option>';
 
}
$server_addr=array();
if(isset($_GET['server'])) {
list($ser_key,$ser_value)=explode("=>",$_GET['server']);
	$server_addr=array($ser_key=>$ser_value);  
} 
?>
  
</select>
------>
<input type="submit" name="Submit" value="提交">
</form>
Note:红颜色代表嵌入失败，绿颜色代表成功

<?php
 

$server = array();
$finally= array();

$server_addr_res=array();
foreach ($server_addr as $kkk =>$url){
	$arr = file($url);
	$session = array();
	$succ_count=0;
	$all_count=0;
	foreach ($arr as $a){
		$t= explode(" ",trim($a));
		if($t[1]!="210.14.71.26"){
			$temp=strtotime($t[2]." ".$t[3]);
			$time_start=$first_time;
			if($kkk<0) $temp=$temp+3600*8;
			if($temp>$time_start){
				$result=0;
				if($t[6]=="sucessed"){
					$result=1;
				}
				$t[5]=str_replace("User-key","",$t[5]);
				$server[$url][$temp][]=array($t[5]=>$result);
			}
		}//end if($t[1]!="210.14.71.26"){
	}
	//$first_time=strtotime("2009-03-26 14:00:00");
	 
	$temp=$server[$url];
	 
 	$now=$end_time;
	while($first_time<$now){  
		$temp=array();
		$finally[$first_time]=getSum($server[$url],$first_time,intval($first_time+$second));
		$first_time=$first_time+$second;
	} 
 
	$html='<table  height="289" border="1" cellpadding="0" cellspacing="0">
 		 <tr>
  		';
	foreach ($finally as $key => $v) {
		list($v1,$v2,$html1,$html2)=$v;
		$html.='<td   align="center" valign="bottom" width="280">  
					<div class="tongji"> 
				    <table border="0" class="list_div"  height="700">
				      <tr>
				        <td class="tit_div" valign="bottom">
				        	'.$html1.'
				         </td>
				        <td class="pic" valign="bottom">
				        	'.$v1.'<br /><img src=""    width="15" height="'.strval(30*$v1).'"   style="background-color: #FF0000" />
				        </td>
				      </tr>
				    </table>
				    <table border="0" class="list_div"  height="700">
				      <tr >
				   <td class="pic" valign="bottom">
				        	'.$v2.'<br /><img src=""   width="15" height="'.strval(30*$v2).'"   style="background-color: #009900" />
				        </td>
				      <td class="tit_div" valign="bottom">
				        	'.$html2.'
				         </td>
				        	</tr>
				    </table>
				</div>
		 </td>
	';
	
	}
	
	$html.='  </tr><tr style="background-color: yellow"  >';
	foreach ($finally as $key => $v) {
		$html.='  <td>&nbsp;'.date('H:i:s',$key).'</td>';
	}
 $html.=' </tr>
</table><br>';
 echo $html;
 

}

 
function getSum($arr,$strat_time,$end_time){
	
	global $second;
	$sum=0;
	$succ_sum=0;
	$html1='';
	$html2='';
	for(;$strat_time<$end_time;$strat_time++){ 
	   if(isset($arr[$strat_time])){  
	   	//print_r($arr[$strat_time]);die;
	   	$sum_=0;
	   	foreach ($arr[$strat_time] as $jj	 => $gg) {
	   		$sum_=$sum_+array_sum($gg);
	   		foreach ($gg as $yy => $uu) {
	   			if($uu==1){
	   				$html2.='<div class="tit">'.substr($yy,0,8).'</div>'; 
	   			}else{
	   				$html1.='<div class="tit">'.substr($yy,0,8).'</div>'; 
	   			}
	   		}
	   		
	   	}
	       $sum=$sum+(count($arr[$strat_time])-$sum_);
		   $succ_sum=$succ_sum+$sum_;
	   } 
	} 
	return array($sum,$succ_sum,$html1,$html2);

}

 
 

?>