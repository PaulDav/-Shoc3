<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	
	
	define('PAGE_NAME', 'classes');

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
		
		$Page->Title = "Classes";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#public' id='tab0'>Public Classes";
		$TabContent .= "<div class='tabContent hide' id='public'>";
		$TabContent .= ListClasses('public');		
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


function ListClasses($Selection){

	global $System;
	global $Models;

	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Name</th><th>Version</th><th>Model</th></tr></thead>";

	$Content .= "<tbody>";

	$arrClasses = array();
	foreach($Models->Items as $objModel){
		foreach ($objModel->Classes as $objClass){
			switch ($Selection){
				case 'public':
					$arrClasses[$objClass->Id] = $objClass;
					break;
			}
		}
	}		
	usort($arrClasses, 'cmpName');

	foreach ($arrClasses as $objClass){
		$Content .= "<tr>";
		$Content .= "<td><a href='class.php?modelid=".$objClass->Model->Id."&classid=".$objClass->Id."'>".$objClass->Name."</a></td>";
		$Content .= "<td>".$objClass->Version."</td>";
		$Content .= "<td><a href='model.php?modelid=".$objClass->Model->Id."'>".$objClass->Model->Name."</a></td>";
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