<?php

require_once("path.php");

require_once("class/clsSystem.php");

require_once("class/clsPage.php");
require_once("class/clsModel.php");
require_once("class/clsShocData.php");

require_once("update/updateData.php");
		
require_once("function/utils.inc");

	define('PAGE_NAME', 'form');

	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
	

	SaveUserInput(PAGE_NAME);

	try {
	
		if (!$System->LoggedOn){
			throw new exception('You must be logged on to change a Form');
		}
	
		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
		
		$uriDocument = null;
		$uriRevisionOf = null;
		$uriBox = null;
		$uriSubject = null;
		$ObjectId = null;

		$Extending = false;
		$ArchRelId = null;
		$uriFromSubject = null;
		
		
		$xmlForm = null;

		$objForm = null;
		$objDocument = null;
		$objBox = null;
		$objRevision = null;
		$objArchRel = null;
		$objFromSubject = null;
		
		$DocumentType = 'subject';

		$uriRelationship = null;
		$Description = null;

		if (isset($_SESSION['forms'][PAGE_NAME]['extending'])){
			if ($_SESSION['forms'][PAGE_NAME]['extending'] == 'true'){
				$Extending = true;
			}
		}		
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
			$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
			$objBox = $Shoc->getBox($uriBox);
		}		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uridocument'])){
			$uriDocument = $_SESSION['forms'][PAGE_NAME]['uridocument'];
			$objDocument = $Shoc->getDocument($uriDocument);
			$objBox = $objDocument->Box;
			$objRevision = $objDocument->CurrentRevision;
			$DocumentType = $objDocument->Type;
		}

		if (is_null($objBox)){
			throw new exception("Box or Document not specified");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
			$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
			if (!$Extending){
				if (!isset($objBox->Objects[$ObjectId])){
					throw new exception("Wrong Object for Box");
				}
			}
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['urifromsubject'])){
			$uriFromSubject = $_SESSION['forms'][PAGE_NAME]['urifromsubject'];
//			$objFromSubject = new clsSubject($uriFromSubject);
			$objFromSubject = $Shoc->getSubject($uriFromSubject);
			
		}				

		
		if ($Extending){		
			if (!isset($_SESSION['forms'][PAGE_NAME]['archrelid'])){
				throw new exception ("Archetype Relationship not specified");				
			}
			$ArchRelId = $_SESSION['forms'][PAGE_NAME]['archrelid'];
			if (!isset($Archetypes->Relationships[$ArchRelId])){
				throw new exception ("Unknown Archetype Relationship");
			}
			$objArchRel = $Archetypes->Relationships[$ArchRelId];
			if (is_null($objFromSubject)){
				throw new exception ("Extending Relationship has no From Subject");
			}
			if (is_null($objFromSubject->Class)){
				throw new exception ("From Subject has no Class");
			}				
			if (!$objArchRel->Relationship->Extending){
				throw new exception ("Relationship is not extending");
			}
			if ($objArchRel->Relationship->FromClassId != $objFromSubject->Class->Id){
				throw new exception ("Wrong Subject Class for Relationship");
			}
			if ($ObjectId != $objArchRel->ToObjectId){
				throw new exception ("Wrong Object for extending Relationship");
			}
		}
		
		
		switch ($DocumentType){
			case 'subject':
				if (isset($_SESSION['forms'][PAGE_NAME]['xmlform'])){
					$xmlForm = $_SESSION['forms'][PAGE_NAME]['xmlform'];
				}
				
				$objForm = new clsForm();
				$objForm->loadXml($xmlForm);
				
				break;
			case 'link':
				if (isset($_SESSION['forms'][PAGE_NAME]['urirelationship'])){
					$uriRelationship = $_SESSION['forms'][PAGE_NAME]['urirelationship'];
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];
				}
				break;
		}
		
		
//		echo "<pre>".htmlentities($xmlForm)."</pre>";
//		exit;
		
	
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}
		switch ($Mode) {
			case 'new':
	
				break;
			case 'edit':
			case 'delete':
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
					throw new exception("You cannot edit this Form");
				}
				break;
				
				
			case 'edit':
				if (!$objDocument->canEdit){
					throw new exception("You cannot edit this Form");
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
			case 'edit':
	
	
				break;
		}

// create document if it does not exist

		if (is_null($objDocument)){
			if (is_null($ObjectId)){
				throw new exception("Can't create Document");
			}
			$uriDocument = dataDocumentUpdate('new', null , $DocumentType, $uriBox, $ObjectId, null, null );
		}
		
		$ParamSid = $System->Session->ParamSid;
		$ReturnUrl = "document.php?$ParamSid&uridocument=$uriDocument";


		$uriRevision = dataRevision($uriDocument);
		
		switch ($DocumentType){
			case 'subject':
		
				$xmlTemplate = $objForm->xpath->query("shoc:Template")->item(0);
				$xmlStatements = $objForm->xpath->query("shoc:Statements")->item(0);
		
				
				$arrSubjects = array();
				foreach ($objForm->xpath->query("shoc:Sections/shoc:Section",$xmlTemplate) as $xmlSection){
					doSection($xmlSection);
				}
		
				foreach ($objForm->xpath->query("shoc:Relationships/shoc:Relationship",$xmlTemplate) as $xmlRelationship){
					doRelationship($xmlRelationship);
				}
				
				if (count($arrSubjects) >0){
					$uriSubject = reset($arrSubjects);
					$ReturnUrl = "subject.php?$ParamSid&urisubject=$uriSubject";					
				}
				
				if ($Extending){
					$ReturnUrl = "doLink.php?$ParamSid&mode=new&uribox=$uriBox&archrelid=$ArchRelId&urifromsubject=$uriFromSubject&uritosubject=$uriSubject";
				}
				
				
				break;
				
			case 'link':
				
				doBoxLink();
				
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


function doSection($xmlSection){

	global $objForm;
	
	global $uriDocument;
//	global $DocumentRevisionNumber;
	global $xmlStatements;
	
	global $arrSubjects;

	$idObject = $xmlSection->getAttribute('idObject');

	foreach ($objForm->xpath->query("shoc:About[@idObject='$idObject']",$xmlStatements) as $xmlAbout){
		doAbout($xmlAbout);
	}
}

function doAbout($xmlAbout){

	global $objForm;
	
	global $Archetypes;
	
	global $uriDocument;
	global $uriRevision;
	global $xmlStatements;
	
	global $arrSubjects;
	
	
	$idObject = $xmlAbout->getAttribute('idObject');
	if (!isset($Archetypes->Objects[$idObject])){
		return;
	}
	$objObject = $Archetypes->Objects[$idObject];	
	
	$xmlSection = $objForm->xpath->query("//shoc:Section[@idObject='$idObject']")->item(0);
	if (is_null($xmlSection)){
		return;
	}

	$idSubject = $xmlAbout->getAttribute('idSubject');
	$uriSubject = $xmlAbout->getAttribute('uriSubject');	

	if (empty($uriSubject)){
		$uriClass = $objObject->Class->Uri;
		$uriSubject = dataSubject($uriClass);
	}
	
	$arrSubjects[$idSubject] = $uriSubject;
	
	dataAbout($uriRevision, $uriSubject, $idObject);
	
	foreach ($objForm->xpath->query("shoc:Question",$xmlSection) as $xmlQuestion){
		doQuestion($uriSubject, $objObject, $xmlQuestion, $xmlAbout);
	}
	
	return;
	
}



function doQuestion($uriSubject, $objObject, $xmlQuestion, $xmlParent, $uriParentStatement = null){

	global $objForm;

	global $Archetypes;
	
	global $uriDocument;
	global $uriRevision;
	global $xmlStatements;
	
	global $arrSubjects;
	
	$idField = $xmlQuestion->getAttribute('idField');		

	$idObjectProperty = $xmlQuestion->getAttribute('idObjectProperty');
	if (isset($objObject->Archetype->ObjectProperties[$idObjectProperty])){
		$objObjectProperty = $objObject->Archetype->ObjectProperties[$idObjectProperty];
		
		$objProperty = $objObjectProperty->Property;
		$uriProperty = $objProperty->Uri;

		$uriDataType = null;
		
		$xmlResponse = $objForm->xpath->query("shoc:Responses/shoc:Response[1]",$xmlQuestion)->item(0);
		if ($xmlResponse){
			$xmlDataType = $objForm->xpath->query("shoc:Responses/shoc:Response[1]/shoc:DataType",$xmlResponse)->item(0);
			if ($xmlDataType){
				$uriDataType = $xmlDataType->getAttribute('uri');
			}
		}
		
		$xmlParts = $objForm->xpath->query("shoc:Parts",$xmlQuestion)->item(0);
		
		foreach ($objForm->xpath->query("shoc:Statement[@idField='$idField']",$xmlParent) as $xmlStatement){
			
			$uriStatement = null;
			$objStatement = null;
			if ($xmlStatement->getAttribute('uri')){
				$uriStatement = $xmlStatement->getAttribute('uri');
				$objStatement = new clsStatement($uriStatement);
			}

			if ($xmlResponse){	

				$xmlValue = $objForm->xpath->query("shoc:Value",$xmlStatement)->item(0);
				if ($xmlValue){
					$Value = trim($xmlValue->nodeValue);
					$boolChanged = true;
					if (!is_null($objStatement)){
						if ($objStatement->Value == $Value){
							$boolChanged = false;
						}
					}
	
					if ($Value != ''){
					
						if ($boolChanged){					
							$uriStatement = dataStatement($uriSubject, $uriProperty, $uriParentStatement, $Value, $uriDataType);
						}
					
						dataRevisionStatement($uriRevision, $uriStatement);
					}
										
				}
			}
			
			
			if ($xmlParts){
				if (is_null($uriStatement)){
					$uriStatement = dataStatement($uriSubject, $uriProperty, $uriParentStatement);
				}
				dataRevisionStatement($uriRevision, $uriStatement);
				doParts($uriSubject, $objObject, $uriStatement, $xmlParts, $xmlStatement);
			}
			
			
			
		}
	}
	

}


function doParts($uriSubject, $objObject, $uriParentStatement, $xmlParts, $xmlParent){

	global $objForm;
	
	global $Archetypes;
	
	global $uriDocument;
	global $uriRevision;
	global $xmlStatements;
	
	global $arrSubjects;

	foreach ($objForm->xpath->query("shoc:Question",$xmlParts) as $xmlQuestion){
		doQuestion($uriSubject, $objObject, $xmlQuestion, $xmlParent, $uriParentStatement);
	}
	
	return;
	
}


function doRelationship($xmlRelationship){

	global $objForm;
	
	global $uriDocument;
	global $xmlStatements;
	
	global $arrSubjects;

	$idRelationship = $xmlRelationship->getAttribute('id');

	foreach ($objForm->xpath->query("shoc:About/shoc:Statement[@idRelationship='$idRelationship']",$xmlStatements) as $xmlStatement){
		doLink($xmlStatement);
	}
}


function doLink($xmlStatement){

	global $objForm;
	
	global $Archetypes;
	
	global $uriDocument;
	global $uriRevision;
	global $xmlStatements;
	
	global $arrSubjects;
	
	$objStatement = null;
	$uriStatement = null;

	if ($xmlStatement->getAttribute('uri')){
		$uriStatement = $xmlStatement->getAttribute('uri');
		$objStatement = new clsStatement($uriStatement);
	}

	$idFromSubject = $xmlStatement->parentNode->getAttribute('idSubject');
	if (!isset($arrSubjects[$idFromSubject])){
		return;
	}
	$uriFromSubject = $arrSubjects[$idFromSubject];

	if ($xmlStatement->hasAttribute('uriLinkSubject')){
		$uriToSubject = $xmlStatement->getAttribute('uriLinkSubject');		
	}
	else
	{	
		$idToSubject = $xmlStatement->getAttribute('idLinkSubject');
		if (!isset($arrSubjects[$idToSubject])){
			return;
		}
		$uriToSubject = $arrSubjects[$idToSubject];
	}

	$idRelationship = $xmlStatement->getAttribute('idRelationship');
	if (!isset($Archetypes->Relationships[$idRelationship])){
		return;
	}
	$objArchetypeRelationship = $Archetypes->Relationships[$idRelationship];
	$uriRelationship = $objArchetypeRelationship->Relationship->Uri;
	
	if (is_null($uriStatement)){
		switch ($objArchetypeRelationship->Inverse){
			case true:
				$uriStatement = dataLinkStatement($uriToSubject, $uriRelationship, $uriFromSubject);
				break;
			default:
				$uriStatement = dataLinkStatement($uriFromSubject, $uriRelationship, $uriToSubject);
				break;
		}
	}

	dataRevisionStatement($uriRevision, $uriStatement);

	return;
	
}



function doBoxLink(){

	global $objDocument;
	
	global $uriDocument;
	global $uriRevision;
	global $uriRelationship;
	global $Description;
	
	global $objRevision;

	$objLinkStatement = null;
	$objDescriptionStatement = null;
	
	if (is_object($objRevision)){
		foreach ($objRevision->objStatements->Items as $objStatement){
			if ($objStatement->uriRelationship == $uriRelationship){
				$objLinkStatement = $objStatement;
			}
		}
	
		if (!is_null($objLinkStatement)){
			foreach ($objRevision->objStatements->Items as $objStatement){
				if ($objStatement->uriSubject == $objLinkStatement->Uri){		
					if ($objStatement->uriProperty == clsStatements::nsSHOC.'description'){
						$objDescriptionStatement = $objStatement;
					}
				}
			}
		}
	}
			
	
	$uriFromSubject = $objDocument->FromSubject->Uri;
	$uriToSubject = $objDocument->ToSubject->Uri;

	
	$Value = $uriRelationship;
	$boolChanged = true;
	if (!is_null($objLinkStatement)){
		if ($objLinkStatement->Value == $Value){
			$boolChanged = false;
		}
	}
	if ($boolChanged){
		$uriLinkStatement = dataLinkStatement($uriFromSubject, $uriRelationship, $uriToSubject);
	}
	dataRevisionStatement($uriRevision, $uriLinkStatement);


	
	
	$Value = $Description;
	$boolChanged = true;
	if (!is_null($objDescriptionStatement)){
		if ($objDescriptionStatement->Value == $Value){
			$boolChanged = false;
		}
	}

	if ($Value != ''){	
		if ($boolChanged){					
			$uriStatement = dataStatement($uriLinkStatement, clsStatements::nsSHOC.'description', null, $Value);
		}	
		dataRevisionStatement($uriRevision, $uriStatement);
	}
	
	
	
	
	return;
	
}

?>