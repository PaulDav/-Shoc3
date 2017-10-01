<?php

	require_once("path.php");

		
	require_once("update/updateData.php");
			
	define('PAGE_NAME', 'clear');
	
	session_start();
	$System = new clsSystem();
	
	
	
	try {
			
		SaveUserInput(PAGE_NAME);
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}

		dataClearAll();
		$ReturnUrl = '.';
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>