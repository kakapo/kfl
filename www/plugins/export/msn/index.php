<?php

	//  Example of how to use the library  -- contents put in $ret_array
	include "contacts_fn.php";
	$ret_array = get_contacts("@","@");
	
	//to see a array dump...
    print_r($ret_array);
	
?>   
