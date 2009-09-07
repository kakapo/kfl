<?php
class index{

    function view_defaults(){
	global $tpl;
	echo "Hello World!";
	$tpl->assign("name","It's a demo.");
    }
}
?>