<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsShocList.php");
	
	require_once("class/clsThread.php");
	
	
	define('PAGE_NAME', 'apiSubjectList');
	
	session_start();
	$System = new clsSystem();
	session_write_close();

	SaveUserInput(PAGE_NAME);
	
	$FilterPrefix = 'filter';

	$uriActivity = null;
	$ObjectId = null;	
	$ArchRelId = null;
	$uriBox = null;
	$RelId = null;
	$Inverse = false;
	$uriLinkSubject = null;
	
	$objArchRel = null;
	$objObject = null;
	$objBox = null;

	
	$Accept = $System->AcceptType;
	if (isset($_SESSION['forms'][PAGE_NAME]['accept'])){
		$Accept = $_SESSION['forms'][PAGE_NAME]['accept'];
	}	
	
	
	$Format = 'html';	
	if (isset($_SESSION['forms'][PAGE_NAME]['format'])){
		$Format = $_SESSION['forms'][PAGE_NAME]['format'];
	}	

	
	if (isset($_SESSION['forms'][PAGE_NAME]['filterprefix'])){
		$FilterPrefix = $_SESSION['forms'][PAGE_NAME]['filterprefix'];
	}	
	
	
	$Action = null;
	if (isset($_SESSION['forms'][PAGE_NAME]['action'])){
		$Action = $_SESSION['forms'][PAGE_NAME]['action'];		
	}	
	

	if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){
		$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['archrelid'])){
		$ArchRelId = $_SESSION['forms'][PAGE_NAME]['archrelid'];		
	}	

	if (isset($_SESSION['forms'][PAGE_NAME]['urilinksubject'])){
		$uriLinkSubject = $_SESSION['forms'][PAGE_NAME]['urilinksubject'];		
	}	
	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['relid'])){
		$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];		
	}	

	if (isset($_SESSION['forms'][PAGE_NAME]['inverse'])){
		if ($_SESSION['forms'][PAGE_NAME]['inverse'] == 'true'){
			$Inverse = true;
		}
	}	
	
	
	$Models = new clsModels;
	$Archetypes = new clsArchetypes($Models);

	if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
		$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
		if (isset($Archetypes->Objects[$ObjectId])){
			$objObject =  $Archetypes->Objects[$ObjectId];
		}
	}	
	

	
	$Content = '';

	if (isset($Archetypes->Relationships[$ArchRelId])){
		$objArchRel = $Archetypes->Relationships[$ArchRelId];
		$objObject = $objArchRel->ToObject;
	}

	
	if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
		$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];
	}	
	
	
	$Subjects = new clsSubjects();
	$Subjects->uriActivity = $uriActivity;
	
	if (is_object($objObject)){
		$Subjects->forObject($objObject->Id);
	}
	
	if (!is_null($uriLinkSubject) && !(is_null($RelId))){
		$Subjects->forRelationship($uriLinkSubject,$RelId, $Inverse);
	}
	
	
	$Subjects->uriBox = $uriBox;

	$Subjects->FilterPrefix = $FilterPrefix;
		
	foreach ($_REQUEST as $FieldName=>$FieldValue){
//		if (substr($FieldName,0,7) == "filter_"){
		if (substr($FieldName,0, strlen($FilterPrefix)) == $FilterPrefix){
		
			if ($FieldValue != ''){
				$Subjects->FilterFields[$FieldName] = $FieldValue;
			}
		}
	}
	
	

	$objSubjectList = new clsShocList();
	$objSubjectList->Object = $objObject;
	$objSubjectList->Subjects = $Subjects;
	
	
	$ParamSid = $System->Session->ParamSid;
	
	switch ($Action){
		case 'link':
			$objSubjectList->ReturnUrl = "link.php?$ParamSid&mode=new&archrelid=$ArchRelId&uritosubject=";
			break;
		case 'boxlink':
			$objSubjectList->ReturnUrl = "boxlink.php?$ParamSid&mode=new&archrelid=$ArchRelId&uritosubject=";
			break;
		case 'subjectlink':
//			$objSubjectList->ReturnUrl = "link.php?$ParamSid&mode=view&urilink=";
//			$objSubjectList->ReturnParam = "uriLink";
			$objSubjectList->ShowLink = true;
			break;
	}

	
	switch ($Format){
		case 'html':
			$Content .= $objSubjectList->html;
			break;
		case 'csv':
			$Content .= $objSubjectList->csv;
			break;
	}
	
	
	switch ($Accept){
		case 'html':
			header ('Content-type: text/html');
			break;
		case 'json':
			$arrJson = array();
			$arrJson[$Format] = $Content;
			$arrJson['count'] = count($Subjects->Items);
			$Content = json_encode($arrJson);
			header ('Content-type: application/json');
			break;
	}

	unset($_SESSION['forms'][PAGE_NAME]);
	
	echo $Content;
	exit;
	
?>