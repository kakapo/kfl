<?php
class index{

    function view_defaults(){
		global $tpl;	
		$user = authenticate();		
		if($user){
			$msg = "Login on. Welcome, ".$user['user_nickname']." go to <a href='/index.php/passport/logout'>log out</a>";
			echo $msg;
		}
		$tpl->assign("name","It's a demo.");
    }
}
?>