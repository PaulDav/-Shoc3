<?php

	require_once("path.php");

		
	require_once("function/utils.inc");

	require_once("class/clsSystem.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	require_once("update/updateData.php");
			
	define('PAGE_NAME', 'document');
	
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
				
		$uriDocument = null;
		$ObjectId = null;
		$objObject = null;

		$uriBox = null;
		$objBox = null;
		
		
		$uriFromSubject = null;
		$objFromSubject = null;
		$uriToSubject = null;
		$objToSubject = null;
		
		

		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
			$objBox = $Shoc->getBox($uriBox);
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uridocument'])){			
			$uriDocument = $_SESSION['forms'][PAGE_NAME]['uridocument'];
			$objDocument = $Shoc->getDocument($uriDocument);
			if (!is_null($objDocument->Object)){
				$objObject = $objDocument->Object;
				$ObjectId = $objDocument->Object->Id;
			}
		}

		$Type = 'subject';
		if (isset($_SESSION['forms'][PAGE_NAME]['type'])){
			$Type = $_SESSION['forms'][PAGE_NAME]['type'];
		}
		switch ($Type){
			case 'subject':
				if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
					$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
					if (!isset($objBox->Objects[$ObjectId])){
						throw new exception ("Unknown Class");
					}
					$objObject = $objBox->Objects[$ObjectId];
				}
				break;
				
			case 'link':
				
				if (!isset($_SESSION['forms'][PAGE_NAME]['urifromsubject'])){
					throw new exception ("From Subject not specified");
				}				
				$uriFromSubject = $_SESSION['forms'][PAGE_NAME]['urifromsubject'];
	//			$objFromSubject = new clsSubject($uriFromSubject);
				$objFromSubject = $Shoc->getSubject($uriFromSubject);
				
				
				if (!isset($_SESSION['forms'][PAGE_NAME]['uritosubject'])){
					throw new exception ("To Subject not specified");
				}
								
				$uriToSubject = $_SESSION['forms'][PAGE_NAME]['uritosubject'];
//				$objToSubject = new clsSubject($uriToSubject);
				$objToSubject = $Shoc->getSubject($uriToSubject);
				
// check that the model contains a relationship for these subjects

				$hasRelationship = false;
				foreach ($objFromSubject->Class->AllRelationships as $objRelationship){
					if ($objRelationship->ToClass === $objToSubject->Class){
						$hasRelationship = true;
					}
				}
				if (!$hasRelationship){
					throw new exception ("There is no relationship for these subjects");
				}				

				break;
			default:
				throw new exception ("Invalid Document Type");				
		}

		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode){
			case 'edit':
			case 'delete':
				if (!isset($uriDocument)){
					throw new exception("uriDocument not specified");
				}
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}

/*
		switch ($Mode){
			case 'new':
			case 'edit':

				if (is_null($objTemplate)){
					throw new exception ("Template not specified");
				}
				break;
		}
*/		
		$newDoc = false;		
		switch ( $Mode ){
			case "new":
			case "edit":
				if (is_null($uriDocument)){
					$newDoc = true;
					$uriDocument = dataDocumentUpdate($Mode, $uriDocument , $Type, $uriBox, $ObjectId, $uriFromSubject, $uriToSubject );
				}
				break;
			case 'delete':
				dataDocumentDelete($uriDocument);
				break;
		}

		$ReturnUrl = "document.php?uridocument=$uriDocument";
		if ($newDoc){
			$ReturnUrl = "form.php?uridocument=$uriDocument&mode=edit";			
		}
		
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