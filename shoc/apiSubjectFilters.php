<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	require_once("form/frmSubjectFilter.php");
	
		
	define('PAGE_NAME', 'apiSubjectFilter');
	
	session_start();
	$System = new clsSystem();
	session_write_close();
		
	SaveUserInput(PAGE_NAME);

	$FilterPrefix = 'filter';
	
	$uriClass = null;
	$ObjectId = null;	
	$ArchRelId = null;
	
	$objArchRel = null;
	$objObject = null;
	$objClass = null;
	
	$Models = new clsModels;
	$Archetypes = new clsArchetypes($Models);
	
	if (isset($_SESSION['forms'][PAGE_NAME]['archrelid'])){
		$ArchRelId = $_SESSION['forms'][PAGE_NAME]['archrelid'];		
	}	

	if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
		$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['uriclass'])){
		$uriClass = $_SESSION['forms'][PAGE_NAME]['uriclass'];
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['filterprefix'])){
		$FilterPrefix = $_SESSION['forms'][PAGE_NAME]['filterprefix'];
	}
	
	
	$Content = '';

	if (!is_null($ArchRelId)){
		if (isset($Archetypes->Relationships[$ArchRelId])){
			$objArchRel = $Archetypes->Relationships[$ArchRelId];
			$objObject = $objArchRel->ToObject;
			$objClass = $objObject->Class;
		}
	}

	if (!is_null($ObjectId)){
		if (isset($Archetypes->Objects[$ObjectId])){
			$objObject =  $Archetypes->Objects[$ObjectId];
			$objClass = $objObject->Class;
		}
	}	
	
	if (!is_null($uriClass)){
		if (isset($Models->Classes[$uriClass])){
			$objClass = $Models->Classes[$uriClass];
		}
	}	
	
	if (is_object($objObject)){	
		$Content .= frmObjectFilter($objObject, $FilterPrefix);
	}
	elseif (is_object($objClass)){	
		$Content .= frmSubjectFilter($objClass, $FilterPrefix);
	}	
	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/html');
	echo $Content;
	exit;
	
?>