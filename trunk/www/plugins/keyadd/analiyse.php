<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Google</title>
</head>
<?php  

if(isset($_GET['first_time'])){
	$first_time=strtotime($_GET['first_time']);
}else{
	$first_time=time()-3600*8;
}

if(isset($_GET['end_time'])){
	$end_time=strtotime($_GET['end_time']);
}else{
	$end_time=time();
}

?>


<form name="form1" method="get" action=""  >
  开始时间：
  <input name="first_time" type="text" size="15" value="<?=date('y-m-d H:i:s',$first_time)?>">
  刷新新的数据：<input type="checkbox"  name="remote" value="yes"/> 
   ------>
<input type="submit" name="Submit" value="提交">
</form>
<hr />
<?php

$server_addr=array(
				 
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

$test=array();
foreach ($server_addr as $xx=>  $url){
	$tmp="";
	if($_GET['remote']=="yes"){
	$tmp=file_get_contents($url);
	//将日志输出到一个文件中	 
	$fd = fopen ( "logs/".$xx.".html", 'w' );
	fwrite ( $fd, $tmp );
	fclose ( $fd );
	}
	$test[$url]="logs/".$xx.".html";
 
}
$server_addr=$test;
 
 
$server_addr_res=array();
foreach ($server_addr as $kkk =>  $url){
	$arr = file($url);
	$session = array();
	$server = array();
	$succ_count=0;
	$all_count=0;
	foreach ($arr as $a){
		$t= explode(" ",trim($a));
		if($t[1]!="210.14.71.26"){
			
			$temp=strtotime($t[2]." ".$t[3]);
			if($kkk<0)$temp=$temp+3600*8;
			$time_start=$first_time;
			if($temp>$time_start){
				$session[$t[4]][] = $t[6];
				if($t[6]=="sucessed"){
					$succ_count++;
				}
				$all_count++;	
			}
		}
	}
	$server_addr_res[$url]['succ_count']	=	$succ_count;
	$server_addr_res[$url]['all_count']		=	$all_count;
	$new_session  = array();
	foreach ($session as $s=>$var){ 
		foreach ($var as $v){
			if($v=='sucessed'){
				$new_session[$s] = 1;
			}
		}
	}
	 
	//print_r($session);
	$server_addr_res[$url]['user_succ_count']	=	count($new_session);
	$server_addr_res[$url]['user_all_count']	=	count($session);
	
	
	echo "<h3>SERVER ".$url." analiysed:</h3>";
	echo '嵌入操作数:'.$server_addr_res[$url]['all_count']."<br>";
	echo '嵌入操作成功数:'.$server_addr_res[$url]['succ_count']."<br>";
	echo "嵌入操作成功率:<font color=red >".strval(($server_addr_res[$url]['succ_count']/$server_addr_res[$url]['all_count'])*100)."% </font><p>";
	
	echo '用户嵌入数:'.$server_addr_res[$url]['user_all_count']."<br>";
	echo '用户嵌入成功数:'.$server_addr_res[$url]['user_succ_count']."<br>";
	echo "用户成功嵌入率:<font color=red >".strval(($server_addr_res[$url]['user_succ_count']/$server_addr_res[$url]['user_all_count'])*100)."% </font><p>";
	
}
$server_count=0;
$server_succ_count=0;
$server_user_count=0;
$server_user_succ_count=0;
foreach ($server_addr_res as $v){
			$server_count=$server_count+$v['all_count'];
			$server_succ_count=$server_succ_count+$v['succ_count'];
			$server_user_count=$server_user_count+$v['user_all_count'];
			$server_user_succ_count=$server_user_succ_count+$v['user_succ_count'];
		}



echo "<h1>全部服务器嵌入操作成功率:<font color=red >".strval(($server_succ_count/$server_count)*100)."% </font></h1><br>";
echo "<h1>全部服务器用户嵌入成功率:<font color=red >".strval(($server_user_succ_count/$server_user_count)*100)."% </font></h1><p>";
	
?>
