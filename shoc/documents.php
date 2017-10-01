<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	define('PAGE_NAME', 'documents');

	session_start();
	
	$System = new clsSystem();
	
	$Page = new clsPage();	
	
	$Models = new clsModels();
	$Archetypes = new clsArchetypes($Models);	
	
	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	$filterTemplateId = null;
	if (isset($_REQUEST['filterTemplateId'])){
		if ($_REQUEST['filterTemplateId'] != ''){
			$filterTemplateId = $_REQUEST['filterTemplateId'];
		}
	}
	
	
	try {

		$Page->Title = "Documents";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		

		$ModeOk = false;
		switch ($Mode){
			case 'view':
				$ModeOk = true;
				break;				
			case 'delete':
				if ($objModel->canControl){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}

		
		$PanelB .= frmFilter();		
		
		switch ($Mode){
			case 'delete':
				$PanelB .= "<div class='hmenu'><ul>";
				$PanelB .= "<li><a href='doDocuments.php?mode=delete'>&bull; Confirm - delete all documents?</a></li>";
				$PanelB .= "</ul></div>";
				break;
			default:
				
				if ($System->LoggedOn){
					$Tabs .= "<li><a href='#my' id='tab0'>My Documents";
					$TabContent .= "<div class='tabContent hide' id='my'>";
					
					
					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='document.php?mode=new'>&bull; add a new document</a></li>";
					$TabContent .= "</ul></div>";
					
					$TabContent .= ListDocuments('my');
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}
				
				
				$Tabs .= "<li><a href='#public' id='tab1'>Public Documents";
				$TabContent .= "<div class='tabContent hide' id='public'>";
				$TabContent .= ListDocuments('public');
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
				break;
		}

		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();


function ListDocuments($Selection){

	global $System;
	global $Models;
	
	global $filterTemplateId;

	$Documents = new clsDocuments();
	
	if (!is_null($filterTemplateId)){		
		$Documents->TemplateId = $filterTemplateId;
	}
	
	if ($Selection == 'my'){
		if ($System->LoggedOn){
			$Documents->UserId = $System->User->Id;			
		}
	}
	
	
	$Documents->getItems();
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Template</th><th>About</th>";
	$Content .= "<tbody>";

	$arrDocuments = array();
	foreach($Documents->Items as $objDocument){
		$Content .= "<tr>";
		$Content .= "<td><a href='document.php?uridocument=".$objDocument->Uri."'>".$objDocument->Id."</a></td>";
		$Content .= "<td>".$objDocument->Template->Label."</td>";
		$Content .= "<td>";
		if (is_object($objDocument->CurrentRevision)){
			$Content .= $objDocument->CurrentRevision->Title;
		}
		$Content .= "</td>";
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	
	
function cmpName($a, $b){
    return strcmp( strtolower($a->Name),  strtolower($b->Name));
}
	
	
function frmFilter(){

global $Models;
global $Archetypes;
global $filterTemplateId;
	
	$Content = '';

	$Content .= "<form method='post' action='documents.php'>";
	
	$Content .= "<table>";
	
	$Content .= "<tr><th>Template<th><td>";
	$Content .= "<select name='filterTemplateId'>";
	$Content .= "<option/>";
	
	
	foreach ($Archetypes->Items as $objTemplate){
		$Content .= "<option value='".$objTemplate->Id."'";		
		if ($objTemplate->Id == $filterTemplateId){
			$Content .= " selected='selected' ";
		}
		$Content .= ">".$objTemplate->Name."</option>";
	}

	$Content .= "</select>";
	
	$Content .= "</th></tr>";
	
	
	$Content .= "</table>";
	
	$Content .= "<input type='submit' value='Search'>";
	
	$Content .= "</form>";
	
	return $Content;
}

?>