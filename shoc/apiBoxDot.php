<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsShocList.php");
	
	require_once("class/clsThread.php");
	
	
	define('PAGE_NAME', 'apiBoxDot');
	
	session_start();
	$System = new clsSystem();
	session_write_close();

	$Shoc = new clsShoc();
	
	SaveUserInput(PAGE_NAME);

	$uriBox = null;
	
	
	$Style = 1;

	if (isset($_SESSION['forms'][PAGE_NAME]['uribox'])){
		$uriBox = $_SESSION['forms'][PAGE_NAME]['uribox'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['style'])){
		$Style = $_SESSION['forms'][PAGE_NAME]['style'];		
	}	
	
	
	unset($_SESSION['forms'][PAGE_NAME]);

	$objBox = null;
	if (!is_null($uriBox)){
		$objBox = $Shoc->getBox($uriBox);
	}
	
	$Content = '';

	if (!is_null($objBox)){
		$objDot = new clsShocDot();
		$objDot->Style = $Style;
		$Content .= $objBox->getDot($objDot);
	}
	
	header ('Content-type: text');
	echo $Content;
	exit;
	
?>