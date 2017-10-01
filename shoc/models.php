<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	
	
	define('PAGE_NAME', 'models');

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
		
		$Page->Title = "Models";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#public' id='tab0'>Public Models";
		$TabContent .= "<div class='tabContent hide' id='public'>";
		$TabContent .= ListModels('public');		
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


function ListModels($Selection){

	global $System;
	global $Models;

	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Name</th><th>Version</th></tr></thead>";

	$Content .= "<tbody>";

	$arrModels = array();
	foreach($Models->Items as $objModel){
		switch ($Selection){
			case 'public':
				$arrModels[$objModel->Id] = $objModel;
				break;
		}
	}		
	usort($arrModels, 'cmpName');

	foreach ($arrModels as $objModel){
		$Content .= "<tr>";
		$Content .= "<td><a href='model.php?modelid=".$objModel->Id."'>".$objModel->Name."</a></td>";
		$Content .= "<td>".$objModel->Version."</td>";
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