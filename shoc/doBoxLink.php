<?php

require_once("path.php");

require_once("class/clsSystem.php");

require_once("class/clsPage.php");
require_once("class/clsModel.php");
require_once("class/clsShocData.php");

require_once("update/updateData.php");
		
require_once("function/utils.inc");

	define('PAGE_NAME', 'boxlink');

	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
	

	SaveUserInput(PAGE_NAME);

	
	try {
	
		if (!$System->LoggedOn){
			throw new exception('You must be logged on to change a link');
		}
	
		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
		
		$uriBoxLink = null;
		$uriBox = null;

		$objBoxLink = null;
		$objBox = null;
		
		$ArchRelId = null;
		$Description = null;
		$ObjectId = null;
		$uriSubject = null;
		$uriRelationship = null;
		$Inverse = false;

		
		if (isset($_SESSION['forms'][PAGE_NAME]['uriboxlink'])){
			$uriBoxLink = $_SESSION['forms'][PAGE_NAME]['uriboxlink'];
		}		
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
		}		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['urisubject'])){
			$uriSubject = $_SESSION['forms'][PAGE_NAME]['urisubject'];
//			$objSubject = new clsSubject($uriSubject);
			$objSubject = $Shoc->getSubject($uriSubject);
			
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['archrelid'])){
			$ArchRelId = $_SESSION['forms'][PAGE_NAME]['archrelid'];
		}
		

		if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
			$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['urirelationship'])){
			$uriRelationship = $_SESSION['forms'][PAGE_NAME]['urirelationship'];
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['inverse'])){
			if ($_SESSION['forms'][PAGE_NAME]['inverse'] == 'true'){
				$Inverse = true;
			}
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];
		}
		
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}
		switch ($Mode) {
			case 'new':

				if (is_null($uriBox)){
					throw new exception("Box not specified");
				}
				$objBox = $Shoc->getBox($uriBox);				
				if (!$objBox->canControl){
					throw new exception("You cannot update this Box");
				}
				
				
				if (is_null($uriSubject)){
					throw new exception("Subject not specified");
				}
				if (is_null($ArchRelId)){
					throw new exception("Template Relationship not specified");
				}
				if (!isset($Archetypes->Relationships[$ArchRelId])){
					throw new exception("Unknown Template Relationship");					
				}
				$objArchRel = $Archetypes->Relationships[$ArchRelId];

				$Object = $objArchRel->FromObject;
				$ObjectId = $Object->Id;				
				$idRel = $objArchRel->Relationship->Id;
				$uriRelationship = $objArchRel->Relationship->Uri;
				$Inverse = $objArchRel->Inverse;
				
				break;
			case 'edit':
			case 'delete':
				if (is_null($uriBoxLink)){
					throw new exception("Box Link not specified");
				}
//				$objBoxLink = new clsBoxLink($uriBoxLink);
				$objBoxLink = $Shoc->getBoxLink($uriBoxLink);
				
				if (!$objBoxLink->canControl){
					throw new exception("You cannot update this Box Link");
				}
				$objBox = $objBoxLink->Box;
				if (is_object($objBox)){
					$uriBox = $objBox->Uri;
				}
				break;
			default:
				throw new exception("Invalid Mode");
		}
	
	
		switch ($Mode) {
			case 'new':
			case 'edit':

				$uriBoxLink = dataBoxLinkUpdate($Mode,$uriBoxLink, $uriBox, $uriRelationship, $ObjectId, $uriSubject,  $Inverse, $Description);
				break;
			case 'delete':
				dataBoxLinkDelete($uriBoxLink);
				break;
		}

		
		$ParamSid = $System->Session->ParamSid;
		$ReturnUrl = "box.php?$ParamSid&uribox=$uriBox";
		
		unset($_SESSION['forms'][PAGE_NAME]);
		header("Location: $ReturnUrl");
		exit;
	
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}


?>