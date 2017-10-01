<?php

	require_once("path.php");

		
	require_once("function/utils.inc");

	require_once("class/clsSystem.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	require_once("update/updateData.php");
			
	define('PAGE_NAME', 'box');
	
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
//		$objType = null;
		$objGroup = null;
		$Title = null;
		$Description =  null;
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){			
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
			$objBox = $Shoc->getBox($uriBox);
			if (!is_null($objBox->Type)){
				$objModel = $objBox->Type;
				$TypeId = $objModel->Id;
			}
		}
/*
		if (isset($_SESSION['forms'][PAGE_NAME]['typeid'])){
			$TypeId = $_SESSION['forms'][PAGE_NAME]['typeid'];			
			if (!isset($Models->Items[$TypeId])){
				throw new exception ("Unknown Type");
			}
			$objType = $Models->Items[$TypeId];
		}
*/
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['urigroup'])){
			$uriGroup = $_SESSION['forms'][PAGE_NAME]['urigroup'];			
			$objGroup = $Shoc->getGroup($uriGroup);
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['title'])){
			$Title = $_SESSION['forms'][PAGE_NAME]['title'];			
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];			
		}
		
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode){
			case 'edit':
			case 'delete':
				if (!isset($uriBox)){
					throw new exception("uriBox not specified");
				}
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}


		switch ($Mode){
			case 'new':
			case 'edit':
/*
				if (is_null($objType)){
					throw new exception ("Type not specified");
				}
*/
				break;
		}
		
		$newBox = false;		
		switch ( $Mode ){
			case "new":
			case "edit":
				$newDoc = true;
				$uriBox = dataBoxUpdate($Mode, $uriBox , $uriGroup, $Title, $Description);
				break;
			case 'delete':
				dataBoxDelete($uriBox);
				break;
		}

		$ReturnUrl = "box.php?uribox=$uriBox";
		
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = ".";
				break;
		}
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>