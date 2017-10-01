<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	define('PAGE_NAME', 'boxes');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$Page = new clsPage();	
	
	$Models = new clsModels();
	$Archetypes = new clsArchetypes($Models);
	
	$Boxes = new clsBoxes();
	
	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	$filterBoxTypeId = null;
	if (isset($_REQUEST['filterBoxTypeId'])){
		if ($_REQUEST['filterBoxTypeId'] != ''){
			$filterBoxTypeId = $_REQUEST['filterBoxTypeId'];
		}
	}
	
	
	try {

		$Page->Title = "Boxes";
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

		
//		$PanelB .= frmFilter();		
		
		switch ($Mode){
			default:
				
				if ($System->LoggedOn){
					$Tabs .= "<li><a href='#my' id='tab0'>My Boxes";
					$TabContent .= "<div class='tabContent hide' id='my'>";					
					$TabContent .= ListBoxes('my');
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}
				
				
				$Tabs .= "<li><a href='#public' id='tab1'>Public Boxes";
				$TabContent .= "<div class='tabContent hide' id='public'>";
//				$TabContent .= ListBoxes('public');
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


function ListBoxes($Selection){

	global $System;
	global $Models;
	
	global $filterBoxTypeId;

	$Boxes = new clsBoxes();
	
	if (!is_null($filterBoxTypeId)){		
		$Boxes->TypeId = $filterBoxTypeId;
	}
	
	if ($Selection == 'my'){
		if ($System->LoggedOn){
			$Boxes->MemberId = $System->User->Id;			
		}
	}
		
	$Boxes->getItems();
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Title</th><th>Description</th><th>Classes</th><th>Group</th>";
	
	if ($System->LoggedOn){					
		$Content .= "<th>My Rights</th>";
	}
	
	
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";

	$arrBoxes = array();
	foreach($Boxes->Items as $objBox){
		$Content .= "<tr>";
		$Content .= "<td><a href='box.php?uribox=".$objBox->Uri."'>".$objBox->Title."</a></td>";
		$Content .= "<td>".nl2br(truncate($objBox->Description))."</td>";

		$Content .= "<td>";
		foreach ($objBox->Objects as $objObject){
			$Content .= $objObject->Label.'<br/>';
		}		
		$Content .= "</td>";
		
		
		$Content .= "<td>";
		if (is_object($objBox->Group)){
			$Content .= $objBox->Group->Title;
		}
		$Content .= "</td>";
		
		$Content .= "<td>";

		if (is_object($objBox->Group)){		
			if (!is_null($objBox->Group->MyMembership)){
				$Content .= $objBox->Group->MyMembership->Rights->Label;
			}
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
global $filterBoxTypeId;
	
	$Content = '';

	$Content .= "<form method='post' action='boxes.php'>";
	
	$Content .= "<table>";
	
	$Content .= "<tr><th>Type<th><td>";
	$Content .= "<select name='filterBoxTypeId'>";
	$Content .= "<option/>";
	
	
	foreach ($Models->Items as $objModel){
		$Content .= "<option value='".$objModel->Id."'";		
		if ($objModel->Id == $filterBoxTypeId){
			$Content .= " selected='selected' ";
		}
		$Content .= ">".$objModel->Name."</option>";
	}

	$Content .= "</select>";
	
	$Content .= "</th></tr>";
	
	
	$Content .= "</table>";
	
	$Content .= "<input type='submit' value='Search'>";
	
	$Content .= "</form>";
	
	return $Content;
}

?>