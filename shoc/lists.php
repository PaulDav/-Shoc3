<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	
	
	define('PAGE_NAME', 'lists');

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
		
		$Page->Title = "Lists";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#public' id='tab0'>Public Lists";
		$TabContent .= "<div class='tabContent hide' id='public'>";
		$TabContent .= ListLists('public');
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


function ListLists($Selection){

	global $System;
	global $Models;

	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Name</th><th>Version</th><th>Model</th></tr></thead>";

	$Content .= "<tbody>";

	$arrLists = array();
	foreach($Models->Lists as $objList){
		switch ($Selection){
			case 'public':
				$arrLists[$objList->Id] = $objList;
				break;
		}
	}		
	usort($arrLists, 'cmpName');

	foreach ($arrLists as $objList){
		$Content .= "<tr>";
		$Content .= "<td><a href='list.php?listid=".$objList->Id."'>".$objList->Name."</a></td>";
		$Content .= "<td>".$objList->Version."</td>";
		$Content .= "<td><a href='model.php?modelid=".$objList->Model->Id."'>".$objList->Model->Name."</a></td>";
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