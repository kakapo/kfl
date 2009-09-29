<?php
class api{
	function view_defaults(){
			
		$user = authenticate();
		print_r($user);
	
	}
}

?>