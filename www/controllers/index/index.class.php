<?php
class index{

    function view_defaults(){
		global $tpl;	
		$user = authenticate();		
		if($user){
			$msg = "<h1>欢迎！ ".$user['user_nickname']."  <a href='/index.php/passport/logout'>安全退出</a>";
			echo $msg;
		}
		$tpl->assign("name","It's a demo.");
    }
}
?>