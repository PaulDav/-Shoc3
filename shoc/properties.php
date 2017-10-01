<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	
	
	define('PAGE_NAME', 'properties');

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
		
		$Page->Title = "Properties";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#public' id='tab0'>Public Properties";
		$TabContent .= "<div class='tabContent hide' id='public'>";
		$TabContent .= ListProperties('public');
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


function ListProperties($Selection){

	global $System;
	global $Models;

	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Name</th><th>Version</th><th>Class</th><th>Model</th></tr></thead>";

	$Content .= "<tbody>";

	$arrProperties = array();
	foreach($Models->Properties as $objProperty){
		switch ($Selection){
			case 'public':
				$arrProperties[$objProperty->Id] = $objProperty;
				break;
		}
	}		
	usort($arrProperties, 'cmpName');

	foreach ($arrProperties as $objProperty){
		$Content .= "<tr>";
		$Content .= "<td><a href='property.php?propertyid=".$objProperty->Id."'>".$objProperty->Name."</a></td>";
		$Content .= "<td>".$objProperty->Version."</td>";
		$Content .= "<td><a href='class.php?modelid=".$objProperty->Class->Model->Id."&classid=".$objProperty->Class->Id."'>".$objProperty->Class->Name."</a></td>";
		$Content .= "<td><a href='model.php?modelid=".$objProperty->Class->Model->Id."'>".$objProperty->Class->Model->Name."</a></td>";
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	
	
function cmpName($a, $b){
    return strcmp( strtolower($a->Name),  strtolower($b->Name));
}
	
	
?>