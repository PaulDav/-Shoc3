<?php

	require_once("path.php");

		
	require_once("function/utils.inc");

	require_once("class/clsSystem.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	require_once("update/updateData.php");
			
	define('PAGE_NAME', 'boxobject');
	
	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	
	
	try {
			
		SaveUserInput(PAGE_NAME);
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		
		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
				
		$uriBox = null;
		$objBox = null;
		
		$ObjectId = null;
		$objObject = null;
				
		$Title = null;
		$Description =  null;
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){			
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
			$objBox = $Shoc->getBox($uriBox);
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
			$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
			if (!isset($objBox->Group->Activity->Template->Objects[$ObjectId])){
				throw new exception ("Unknown Object");
			}
			$objObject = $objBox->Group->Activity->Template->Objects[$ObjectId];
		}
		
		if (is_null($objBox)){
			throw new exception("Box not specified");			
		}
		if (is_null($objObject)){
			throw new exception("Object not specified");			
		}
		
		$Mode = null;
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode){
			case 'add':
			case 'remove':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}


		dataBoxObjectUpdate($Mode, $uriBox , $ObjectId);

		$ReturnUrl = "box.php?uribox=$uriBox#classes";
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>