<?php

require_once("path.php");

require_once("class/clsSystem.php");

require_once("class/clsPage.php");
require_once("class/clsModel.php");
require_once("class/clsShocData.php");

require_once("update/updateData.php");
		
require_once("function/utils.inc");

	define('PAGE_NAME', 'link');

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
		
		$uriLink = null;
		$uriDocument = null;
		$uriRevisionOf = null;
		$uriBox = null;

		$objLink = null;
		$objDocument = null;
		$objBox = null;
		$objRevision = null;
		
		$DocumentType = 'link';

		$ArchRelId = null;
		$Description = null;
		$uriFromSubject = null;
		$uriToSubject = null;

		
		if (isset($_SESSION['forms'][PAGE_NAME]['urilink'])){
			$uriLink = $_SESSION['forms'][PAGE_NAME]['urilink'];
//			$objLink = new clsLink($uriLink);
			$objLink = $Shoc->getLink($uriLink);
			
			if (is_object($objLink->Revision)){
				$objRevision = $objLink->Revision;
				$objDocument = $objRevision->Document;
				$objBox = $objDocument->Box;
				
				if (count($objRevision->Abouts) >0){
					$arrAbouts = $objRevision->Abouts;
					$objAbout = reset($arrAbouts);
					$objArchRel = $objAbout->ArchRel;
					if (is_object($objArchRel)){
						$ArchRelId = $objArchRel->Id;						
						switch ($objArchRel->Inverse){
							case false:
								$objFromSubject = $objLink->FromSubject;
								$objToSubject = $objLink->ToSubject;
								break;	
							default:
								$objFromSubject = $objLink->ToSubject;
								$objToSubject = $objLink->FromSubject;
								break;	
						}
						if (is_object($objFromSubject)){
							$uriFromSubject = $objFromSubject->Uri;
						}
						if (is_object($objToSubject)){						
							$uriToSubject = $objToSubject->Uri;
						}
					}
				}
			}
		}		
				
		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
			$objBox = $Shoc->getBox($uriBox);
		}		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uridocument'])){
			$uriDocument = $_SESSION['forms'][PAGE_NAME]['uridocument'];
			$objDocument = $Shoc-getDocument($uriDocument);
			$objBox = $objDocument->Box;
			$objRevision = $objDocument->CurrentRevision;
			$DocumentType = $objDocument->Type;
		}

		if (is_null($objBox)){
			throw new exception("Box or Document not specified");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['urifromsubject'])){
			$uriFromSubject = $_SESSION['forms'][PAGE_NAME]['urifromsubject'];
//			$objFromSubject = new clsSubject($uriFromSubject);
			$objFromSubject = $Shoc->getSubject($uriFromSubject);
			
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['uritosubject'])){
			$uriToSubject = $_SESSION['forms'][PAGE_NAME]['uritosubject'];
//			$objToSubject = new clsSubject($uriToSubject);
			$objToSubject = $Shoc->getSubject($uriToSubject);
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['archrelid'])){
			$ArchRelId = $_SESSION['forms'][PAGE_NAME]['archrelid'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];
		}
		
		
//		if (isset($_SESSION['forms'][PAGE_NAME]['urirevisionof'])){
//			$uriRevisionOf = $_SESSION['forms'][PAGE_NAME]['urirevisionof'];
//		}
		
		
		if (!is_null($objDocument)){
			$uriDocument = $objDocument->Uri;
		}
	
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}
		switch ($Mode) {
			case 'new':

				if (is_null($uriFromSubject)){
					throw new exception("From Subject not specified");
				}
				if (is_null($uriToSubject)){
					throw new exception("To Subject not specified");
				}
				if (is_null($ArchRelId)){
					throw new exception("Template Relationship not specified");
				}
				if (!isset($Archetypes->Relationships[$ArchRelId])){
					throw new exception("Unknown Template Relationship");					
				}
				$objArchRel = $Archetypes->Relationships[$ArchRelId];
				
				
				break;
			case 'edit':
			case 'delete':
			case 'remove':
				if (is_null($objDocument)){
					throw new exception("Document not specified");
				}
	
				break;
			default:
				throw new exception("Invalid Mode");
		}
	
		switch ($Mode) {
			case 'new':
				
				if (!$objBox->canEdit){
					throw new exception("You cannot edit this Link");
				}
				break;
				
				
			case 'edit':
			case 'remove':
				if (!$objDocument->canEdit){
					throw new exception("You cannot edit this Link");
				}
				break;
			case 'delete':				
				if (!$objDocument->canControl){
					throw new exception("You cannot delete this Form");
				}
				break;
		}
	
	
		switch ($Mode) {
			case 'new':

				$idRel = $objArchRel->Relationship->Id;
				$uriRel = $objArchRel->Relationship->Uri;
				switch ($objArchRel->Inverse){
					case false:
						$uriLink = dataLink($uriRel, $idRel, $uriFromSubject, $uriToSubject);						
						break;
					default:
						$uriLink = dataLink($uriRel, $idRel, $uriToSubject, $uriFromSubject);						
						break;						
				}
				$objLink = new clsLink($uriLink);
				break;
		}

		
		switch ($Mode) {
			case 'new':
			case 'edit':
		
		
		// create document if it does not exist
		
				if (is_null($objDocument)){
					if (is_null($ArchRelId)){
						throw new exception("Can't create Document");
					}
					$uriDocument = dataDocumentUpdate('new', null , $DocumentType, $uriBox, null, $ArchRelId);
				}
				break;
		}
		
		$ParamSid = $System->Session->ParamSid;
//		$ReturnUrl = "document.php?$ParamSid&uridocument=$uriDocument";
		$ReturnUrl = "subject.php?$ParamSid&urisubject=$uriFromSubject";
		
		
		$RevisionAction = null;
		if ($Mode == 'remove'){
			$RevisionAction = 'remove';
		}

		$uriRevision = dataRevision($uriDocument, $RevisionAction);
		dataAboutLink($uriRevision, $uriLink, $ArchRelId);

		switch ($Mode){
			case 'new':
			case 'edit':
		
				$objDescriptionStatement = null;
				
				if (is_object($objRevision)){		
					foreach ($objRevision->objStatements->Items as $objStatement){
						if ($objStatement->uriSubject == $uriLink){						
							if ($objStatement->uriProperty == clsStatements::nsSHOC.'description'){
								$objDescriptionStatement = $objStatement;
							}
						}
					}
				}
						
				
				$Value = $Description;
				$boolChanged = true;
				if (!is_null($objDescriptionStatement)){
					if ($objDescriptionStatement->Value == $Value){
						$boolChanged = false;
					}
				}
			
				if ($Value != ''){	
					if ($boolChanged){
						$uriStatement = dataStatement($uriLink, clsShoc::nsSHOC.'description', null, $Value);
					}	
					dataRevisionStatement($uriRevision, $uriStatement);
				}
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