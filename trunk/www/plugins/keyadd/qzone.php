<?php
include_once("export_function.php");
//多个服务器设置
 $serverlist=array(
 					 1=>"http://113.11.194.22/qzone_proxy.php", 
                    2=>"http://116.11.32.14/qzone_proxy.php", 
					3=>"http://117.25.131.110/qzone_proxy.php", 
                    4=>"http://117.34.73.31/qzone_proxy.php",
					5=>"http://122.11.51.222/qzone_proxy.php", 
					6=>"http://122.136.46.78/qzone_proxy.php", 
                    7=>"http://122.224.199.240/qzone_proxy.php", 
					8=>"http://123.130.124.158/qzone_proxy.php", 
					9=>"http://125.211.197.86/qzone_proxy.php", 
					10=>"http://125.65.46.70/qzone_proxy.php", 
					11=>"http://221.195.40.149/qzone_proxy.php", 
					12=>"http://222.135.146.160/qzone_proxy.php", 
					13=>"http://222.189.239.167/qzone_proxy.php", 
					14=>"http://58.211.84.121/qzone_proxy.php", 
					15=>"http://58.215.240.106/qzone_proxy.php", 
					16=>"http://58.218.206.6/qzone_proxy.php", 
                    17=>"http://58.221.37.110/qzone_proxy.php", 
                    18=>"http://60.2.152.181/qzone_proxy.php", 
					19=>"http://61.141.5.43/qzone_proxy.php"
 
 ); 
 
 $random_key=array_rand($serverlist);
 $addr		=$serverlist[$random_key];
 $_POST['cookie']	 = $_SESSION['cookie'];
 $_POST['session_id']= session_id();
 $_POST['user_ip']	 = $_SERVER['REMOTE_ADDR'];
 
 echo execute_curl ( $addr."?post_data=".json_encode($_POST), '', 'get', '', 'noheader', '' );
 

?>