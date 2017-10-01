<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsUri.php");
	
	define('PAGE_NAME', 'api');

	$System = new clsSystem();	
	$System->path = dirname(__FILE__);
		
	$Models = new clsModels();
	$Archetypes = new clsArchetypes();
	
	$Format = 'xml';
	$ContentType = 'application/xml';

	
	$Dom = new DOMDocument('1.0', 'utf-8');
	$Dom->formatOutput = true;
	
	$DocumentElement = $Dom->createElementNS(clsDocuments::nsSHOC, 'API');
	$DocumentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', clsDocuments::nsXSD);	
	$Dom->appendChild($DocumentElement);
	
//	if (isset($_SERVER['HTTP_ACCEPT'])){
//		$Accept = $_SERVER['HTTP_ACCEPT'];
//		if (substr($Accept,0,9) == 'text/html'){
//			$ContentType = 'text/html';
//		}
//	}

	if (isset($_SERVER['REDIRECT_URL'])){
		$RedirectFrom = $_SERVER['REDIRECT_URL'];
	}


	$arrParts = explode('/',$RedirectFrom);
	
	$ClassId = null;
	$ObjectId = null;
	$SubjectId = null;

	
	
	for ($PartNum = 1; $PartNum < count($arrParts); $PartNum++) {
		$Part = $arrParts[$PartNum];
		
		switch ($Part){
			case 'uri':
				getUri($ClassId);
				break;
			case 'subjectid':
				if ($PartNum < count($arrParts)){
					$PartNum++;
					$SubjectId = $arrParts[$PartNum];
					getSubject($SubjectId);
				}
				break;				
			case 'classid':
				if ($PartNum < count($arrParts)){
					$PartNum++;
					$ClassId = $arrParts[$PartNum];
				}
				break;
			case 'objectid':
				if ($PartNum < count($arrParts)){
					$PartNum++;
					$ObjectId = $arrParts[$PartNum];
				}
				break;				
			case 'subjects':
				getSubjects($ClassId);
				break;
			case 'class':
				getClass($ClassId);
				break;
			case 'object':
				getObject($ObjectId);
				break;
		}
		
	}
	
	
	$Content = $Dom->saveXML();
	
	header("Content-Type: $ContentType");
	echo $Content;

	
function getSubjects($ClassId){
	
	global $System;
	global $Models;
	
	global $Dom;

	$objSubjects = new clsSubjects();
	if (!is_null($ClassId)){
		$objSubjects->forClass($ClassId);
	}
	foreach ($objSubjects->Items as $objSubject){
		$Uri = $objSubject->Uri;
		$objUri = new clsUri($Uri);
		$Dom->documentElement->appendChild($Dom->importNode($objUri->dom->documentElement,true));
	}	
	
}



function getClass($ClassId){
	
	global $System;
	global $Models;
	
	global $Dom;

	if (isset($Models->Classes[$ClassId])){	
		$objClass = $Models->Classes[$ClassId];
		$Dom->documentElement->appendChild($Dom->importNode($objClass->dom->documentElement,true));
	}
}

function getObject($ObjectId){
	
	global $System;
	global $Models;
	global $Archetypes;
	
	global $Dom;

	if (isset($Archetypes->Objects[$ObjectId])){
		$objObject = $Archetypes->Objects[$ObjectId];
		$Dom->documentElement->appendChild($Dom->importNode($objObject->dom->documentElement,true));
	}
}



function getSubject($SubjectId){
	
	global $System;	
	global $Dom;
	
	$BaseUri = "http://data.sedgemoor.gov.uk/ecosystem";
	
	$Uri = "$BaseUri/subject/$SubjectId";

	$objUri = new clsUri($Uri);
	$Dom->documentElement->appendChild($Dom->importNode($objUri->dom->documentElement,true));
		
}


?>