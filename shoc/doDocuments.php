<?php

	require_once("path.php");

		
	require_once("function/utils.inc");

	require_once("class/clsSystem.php");
	require_once("class/clsModel.php");
	
	require_once("update/updateData.php");
	
			
	define('PAGE_NAME', 'documents');
	
	session_start();
	$System = new clsSystem();
	
	
	
	try {
			
		SaveUserInput(PAGE_NAME);
		
//		if (!$System->LoggedOn){
//			throw new exception("You must be logged on");
//		}
		
		
		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
						
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode){
			case 'delete':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}

		
		switch ( $Mode ){
			case "delete":
				dataDocumentsDelete();
				break;
		}

		$ReturnUrl = "documents.php";
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>