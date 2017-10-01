<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlModel.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlProperty.php");
	
	define('PAGE_NAME', 'property');

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
		
		$objProperty = null;
		$objClass = null;
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

		if (isset($_REQUEST['propertyid'])){
			$PropertyId = $_REQUEST['propertyid'];
			
			if (!isset($Models->Properties[$PropertyId])){
				throw new exception("Unknown Property");
			}
			$objProperty = $Models->Properties[$PropertyId];
			$objClass = $objProperty->Class;
			$objModel = $objClass->Model;
			
		}
		
		$Page->Title = $Mode." Property";
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
				$PanelB .= pnlProperty( $PropertyId );
				
				if (count($objProperty->Parts) == 0){				
					$Tabs .= "<li><a href='#field'>Field";
					$TabContent .= "<div class='tabContent hide' id='field'>";
					$TabContent .= pnlField($PropertyId);
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
					
					$Tabs .= "<li><a href='#lists'>Lists";
					$TabContent .= "<div class='tabContent hide' id='lists'>";
					$TabContent .= ListLists($count);
					$TabContent .= "</div>";
					$Tabs .= "($count)</a></li>";
					
				}
				if (!is_null($objProperty->PartOf)){				
					$Tabs .= "<li><a href='#partof'>Part of";
					$TabContent .= "<div class='tabContent hide' id='partof'>";
					$TabContent .= pnlProperty($objProperty->PartOf->Id);
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}

				if (count($objProperty->Parts > 0)){				
					$Tabs .= "<li><a href='#parts'>Parts";
					$TabContent .= "<div class='tabContent hide' id='parts'>";
					$TabContent .= ListParts($count);
					$TabContent .= "</div>";
					$Tabs .= "($count)</a></li>";
				}

				if (count($objProperty->SuperProperties) > 0){				
					$Tabs .= "<li><a href='#super'>Super Properties";
					$TabContent .= "<div class='tabContent hide' id='super'>";
					$TabContent .= ListSuperProperties($count);
					$TabContent .= "</div>";
					$Tabs .= "($count)</a></li>";
				}
				
				if (count($objProperty->EquivalentProperties) > 0){				
					$Tabs .= "<li><a href='#equiv'>Equivalent Properties";
					$TabContent .= "<div class='tabContent hide' id='equiv'>";
					$TabContent .= ListEquivalentProperties($count);
					$TabContent .= "</div>";
					$Tabs .= "($count)</a></li>";
				}
				
				$Tabs .= "<li><a href='#class'>Class";
				$TabContent .= "<div class='tabContent hide' id='class'>";
				$TabContent .= pnlClass($objProperty->Class->Model->Id, $objProperty->Class->Id);
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";

				$Tabs .= "<li><a href='#model'>Model";
				$TabContent .= "<div class='tabContent hide' id='model'>";
				$TabContent .= pnlModel($objProperty->Class->Model->Id);
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
	
function ListLists(&$count){

	global $objProperty;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objProperty->Lists) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objProperty->Lists as $objList){

			$count = $count + 1;

			$Content .= "<tr>";
			$Content .= "<td><a href='list.php?listid=".$objList->Id."'>".$objList->Name."</a></td>";
			$Content .= "<td>".$objList->Version."</td>";
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	
	

function ListParts(&$count){

	global $objProperty;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objProperty->Parts) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objProperty->Parts as $objPropertyPart){

			$count = $count + 1;

			$Content .= "<tr>";
			$Content .= "<td><a href='property.php?propertyid=".$objPropertyPart->Id."'>".$objPropertyPart->Name."</a></td>";
			$Content .= "<td>".$objPropertyPart->Version."</td>";
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	


function ListSuperProperties(&$count){

	global $objProperty;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objProperty->SuperProperties) > 0){
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Class</th><th>Model</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objProperty->SuperProperties as $objSuperProperty){

			$count = $count + 1;

			$Content .= "<tr>";
			$Content .= "<td><a href='property.php?propertyid=".$objSuperProperty->Id."'>".$objSuperProperty->Name."</a></td>";
			$Content .= "<td>".$objSuperProperty->Version."</a></td>";
			$Content .= "<td>";
			$Content .= "<a href='class.php?classid=".$objSuperProperty->Class->Id."'>".$objSuperProperty->Class->Name."</a>";
			$Content .= "</td>";
			$Content .= "<td>";
			$Content .= "<a href='model.php?modelid=".$objSuperProperty->Class->Model->Id."'>".$objSuperProperty->Class->Model->Name."</a>";
			$Content .= "</td>";			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}	
	
	return $Content;
				
}	

function ListEquivalentProperties(&$count){

	global $objProperty;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objProperty->EquivalentProperties) > 0){
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Class</th><th>Model</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objProperty->EquivalentProperties as $objEquivProperty){

			$count = $count + 1;

			$Content .= "<tr>";
			$Content .= "<td><a href='property.php?propertyid=".$objEquivProperty->Id."'>".$objEquivProperty->Name."</a></td>";
			$Content .= "<td>".$objEquivProperty->Version."</a></td>";
			$Content .= "<td>";
			$Content .= "<a href='class.php?classid=".$objEquivProperty->Class->Id."'>".$objEquivProperty->Class->Name."</a>";
			$Content .= "</td>";
			$Content .= "<td>";
			$Content .= "<a href='model.php?modelid=".$objEquivProperty->Class->Model->Id."'>".$objEquivProperty->Class->Model->Name."</a>";
			$Content .= "</td>";			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}	
	
	return $Content;
				
}	

function cmpReference($a, $b){
    return strcmp( strtolower($a->Reference),  strtolower($b->Reference));
}


?>