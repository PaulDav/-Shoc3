<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	
	
	define('PAGE_NAME', 'relationships');

	session_start();
	
	$System = new clsSystem();
	
	$Page = new clsPage();	
	
	$Models = new clsModels();
	
	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	try {
		
		$Page->Title = "Relationships";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#public' id='tab0'>Public Relationships";
		$TabContent .= "<div class='tabContent hide' id='public'>";
		$TabContent .= ListRelationships('public');
		$TabContent .= "</div>";
		$Tabs .= "</a></li>";

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


function ListRelationships($Selection){

	global $System;
	global $Models;

	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>From Class</th><th>Relationships</th><th>To Class</th><th>Model</th></tr></thead>";

	$Content .= "<tbody>";

	$arrRelationships = array();
	foreach($Models->Relationships as $objRelationship){
		switch ($Selection){
			case 'public':
				$arrRelationships[$objRelationship->Id] = $objRelationship;
				break;
		}
	}		
	usort($arrRelationships, 'cmpClassName');

	foreach ($arrRelationships as $objRelationship){
		$Content .= "<tr>";
		$Content .= "<td><a href='class.php?classid=".$objRelationship->FromClass->Id."'>".$objRelationship->FromClass->Name."</a></td>";		
		$Content .= "<td><a href='relationship.php?relationshipid=".$objRelationship->Id."'>".$objRelationship->Label."</a></td>";
		$Content .= "<td><a href='class.php?classid=".$objRelationship->ToClass->Id."'>".$objRelationship->ToClass->Name."</a></td>";		
		$Content .= "<td><a href='model.php?modelid=".$objRelationship->Model->Id."'>".$objRelationship->Model->Name."</a></td>";
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	
	
function cmpClassName($a, $b){
    return strcmp( strtolower($a->FromClass->Name),  strtolower($b->FromClass->Name));
}
	
	
?>