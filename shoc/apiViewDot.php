<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsShocList.php");
	
	require_once("class/clsThread.php");
	
	
	define('PAGE_NAME', 'apiViewDot');
	
	session_start();
	$System = new clsSystem();
	session_write_close();
		
	SaveUserInput(PAGE_NAME);

	$uriActivity = null;
	$uriBox = null;
	
	$ObjectId = null;	
	$uriSubject = null;
	
	$Style = 1;

	if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){
		$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];		
	}	
	if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
		$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['urisubject'])){
		$uriSubject = $_SESSION['forms'][PAGE_NAME]['urisubject'];		
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['style'])){
		$Style = $_SESSION['forms'][PAGE_NAME]['style'];		
	}	
	
	
	unset($_SESSION['forms'][PAGE_NAME]);

	$Models = new clsModels;
	$Archetypes = new clsArchetypes($Models);

	$objObject = null;
	$objSubject = null;
	
	$objSubjects = new clsSubjects();
	if (!is_null($uriActivity)){
		$objSubjects->uriActivity = $uriActivity;
	}
	if (!is_null($uriBox)){
		$objSubjects->uriBox = $uriBox;
	}
	
	$Content = '';

	$objDot = new clsShocDot();
	$objDot->Style = $Style;
	$Content .= $objSubjects->getDot($objDot);

	
	header ('Content-type: text');
	echo $Content;
	exit;
	
?>