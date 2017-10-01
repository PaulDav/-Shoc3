<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");

	require_once("panel/pnlList.php");
	require_once("panel/pnlModel.php");
	
	define('PAGE_NAME', 'list');

	session_start();
		
	$System = new clsSystem();
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";
	$jsScript .= "\n";
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";		
	
	
	$Page = new clsPage();

	try {

		$Models = new clsModels();
		
		$objList = null;
		$objModel = null;
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$PropertyId = null;

		if (isset($_REQUEST['listid'])){
			$ListId = $_REQUEST['listid'];
			
			if (!isset($Models->Lists[$ListId])){
				throw new exception("Unknown List");
			}
			$objList = $Models->Lists[$ListId];
			$objModel = $objList->Model;			
		}
		
		$Page->Title = $Mode." List";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objModel->canView){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}
								
				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlList( $ListId );

				$Tabs .= "<li><a href='#terms'>Terms";
				$TabContent .= "<div class='tabContent hide' id='terms'>";
				$TabContent .= ListTerms($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				$Tabs .= "<li><a href='#properties'>Properties";
				$TabContent .= "<div class='tabContent hide' id='properties'>";
				$TabContent .= ListProperties($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				$Tabs .= "<li><a href='#model'>Model";
				$TabContent .= "<div class='tabContent hide' id='model'>";
				$TabContent .= pnlModel($objModel->Id);
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
												
				break;
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	 	$InitScript .= "} \n";
	 	$InitScript .= "</script> \n";
	 	
	 	$Page->Script .= $jsScript;
	 	$Page->Script .= $InitScript;
	 	
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
	
function ListTerms(&$count){

	global $objList;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objList->Terms) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Label</th><th>Reference</th><th>Version</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objList->Terms as $objTerm){

			$count = $count + 1;
			
			$Content .= "<tr>";
			$Content .= "<td><a href='term.php?termid=".$objTerm->Id."'>".$objTerm->Label."</a></td>";
			$Content .= "<td>".$objTerm->Reference."</td>";
			$Content .= "<td>".$objTerm->Version."</td>";
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	
	
function ListProperties(&$count){

	global $objList;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objList->Properties) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Definition</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objList->Properties as $objProperty){

			$count = $count + 1;

			$Content .= "<tr>";												
			$Content .= "<td><a href='property.php?propertyid=".$objProperty->Id."'>".$objProperty->Name."</a></td>";
			$Content .= "<td>".$objProperty->Version."</a></td>";
			$Content .= "<td>".make_links($objProperty->Definition)."</td>";
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	

?>