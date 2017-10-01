<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsShocList.php");
	
	require_once("class/clsThread.php");
	
	
	define('PAGE_NAME', 'apiSubjectDot');
	
	session_start();
	$System = new clsSystem();
	session_write_close();
		
	SaveUserInput(PAGE_NAME);

	$Shoc = new clsShoc();	
	
	$uriActivity = null;
	$ObjectId = null;	
	$uriSubject = null;
	
	$Style = 1;
	$Depth = 2;
	
	if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){
		$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['urisubject'])){
		$uriSubject = $_SESSION['forms'][PAGE_NAME]['urisubject'];		
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['style'])){
		$Style = $_SESSION['forms'][PAGE_NAME]['style'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['depth'])){
		$Depth = $_SESSION['forms'][PAGE_NAME]['depth'];		
	}	
	
	
	unset($_SESSION['forms'][PAGE_NAME]);

	$Models = new clsModels;
	$Archetypes = new clsArchetypes($Models);

	$objObject = null;
	$objSubject = null;
	
	$Content = '';

	if (!is_null($uriSubject)){
//		$objSubject = new clsSubject($uriSubject);
		$objSubject = $Shoc->getSubject($uriSubject);
		
		$objDot = new clsShocDot();
		$objDot->Style = $Style;
		$Content .= $objSubject->getDot($objDot, null, 1, $Depth);
	}


	header ('Content-type: text');
	echo $Content;
	exit;
	
?>