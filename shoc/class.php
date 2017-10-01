<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlModel.php");
	require_once("panel/pnlClass.php");
	
	define('PAGE_NAME', 'class');

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
		
		$objModel = null;
		$objClass = null;
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$ModelId = null;
		$ClassId = null;

		if (isset($_REQUEST['modelid'])){
			$ModelId = $_REQUEST['modelid'];
			
			if (!($objModel = $Models->getItem($ModelId))){
				throw new exception("Unknown Model");
			}
		}

		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
			
			if (!isset($Models->Classes[$ClassId])){
				throw new exception("Unknown Class");
			}
			$objClass = $Models->Classes[$ClassId];
			$objModel = $objClass->Model;
			$ModelId = $objModel->Id;			
		}
		
		$Page->Title = $Mode." Class";
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
				$PanelB .= pnlClass( $ModelId, $ClassId );				
				
				$Tabs .= "<li><a href='#visualize' id='vizloading'>Visualize</a></li>";
				
				$jsScript .= "<script> \n";
				$jsScript .= "var viz0;";
				$jsScript .= "</script> \n";

				
				$TabContent .= "<div class='tabContent hide' id='visualize'>";
				$TabContent .= "<h3>Visualize</h3>";
				
				$TabContent .= "<div>";
				$TabContent .= "Format";
				$vizOnChange = "	viz0.show(this.options[this.selectedIndex].value)";
				$TabContent .= "<select onchange='$vizOnChange'>";
					$TabContent .= "<option>image</option>";
					$TabContent .= "<option>dot script</option>";
				$TabContent .= "</select>";
				$TabContent .= "</div>";
				
				$TabContent .= "<div id='viz'></div>";
				
				$InitScript .= "	viz0 = new clsPdViz(".json_encode($objClass->dot).", 'viz', 'vizloading'); \n";
				$InitScript .= "    viz0.show()";
				
				$TabContent .= "</div>";
				
				
				
				$Tabs .= "<li><a href='#properties'>Properties";
				$TabContent .= "<div class='tabContent hide' id='properties'>";
				$TabContent .= ListProperties($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";

				$Tabs .= "<li><a href='#relationships'>Relationships";
				$TabContent .= "<div class='tabContent hide' id='relationships'>";
				$TabContent .= ListRelationships($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				
				
				$Tabs .= "<li><a href='#superclasses'>Super Classes";
				$TabContent .= "<div class='tabContent hide' id='superclasses'>";
				$TabContent .= ListSuperClasses($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";

				$Tabs .= "<li><a href='#equivclasses'>Equivalent Classes";
				$TabContent .= "<div class='tabContent hide' id='equivclasses'>";
				$TabContent .= ListEquivalentClasses($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				
				$Tabs .= "<li><a href='#model'>Model";
				$TabContent .= "<div class='tabContent hide' id='model'>";
				$TabContent .= pnlModel( $ModelId );
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
	
function ListProperties(&$count){

	global $objClass;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objClass->AllProperties) > 0){
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Class</th><th>Model</th><th>Super Property</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objClass->AllProperties as $objProperty){

			$count = $count + 1;

			$Content .= "<tr>";
			$Content .= "<td><a href='property.php?propertyid=".$objProperty->Id."'>".$objProperty->Name."</a></td>";
			$Content .= "<td>".$objProperty->Version."</td>";
			$Content .= "<td>";
			if ($objProperty->Class->Id != $objClass->Id){
				$Content .= "<a href='class.php?classid=".$objProperty->Class->Id."'>".$objProperty->Class->Name."</a>";
			}
			$Content .= "</td>";
			$Content .= "<td>";
			if ($objProperty->Class->Model->Id != $objClass->Model->Id){
				$Content .= "<a href='model.php?modelid=".$objProperty->Class->Model->Id."'>".$objProperty->Class->Model->Name."</a>";
			}
			$Content .= "</td>";
			
			$Content .= "<td>";
			foreach ($objProperty->SuperProperties as $objSuperProperty){
				$Content .= "<a href='property.php?propertyid=".$objSuperProperty->Id."'>".$objSuperProperty->Name."</a><br/>";
			}
			$Content .= "</td>";
			
			
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}	
	

	return $Content;
				
}	


function ListSuperClasses(&$count){

	global $objClass;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objClass->SuperClasses) > 0){
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Model</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objClass->SuperClasses as $objSuperClass){

			$count = $count + 1;

			$Content .= "<tr>";												
			$Content .= "<td><a href='class.php?modelid=".$objSuperClass->Model->Id."&classid=".$objSuperClass->Id."'>".$objSuperClass->Name."</a></td>";
			$Content .= "<td>".$objSuperClass->Version."</td>";
			$Content .= "<td><a href='model.php?modelid=".$objSuperClass->Model->Id."'>".$objSuperClass->Model->Name."</a></td>";
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	

function ListEquivalentClasses(&$count){

	global $objClass;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objClass->EquivalentClasses) > 0){
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Model</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objClass->EquivalentClasses as $objEquivClass){

			$count = $count + 1;

			$Content .= "<tr>";												
			$Content .= "<td><a href='class.php?classid=".$objEquivClass->Id."'>".$objEquivClass->Name."</a></td>";
			$Content .= "<td>".$objEquivClass->Version."</td>";
			$Content .= "<td><a href='model.php?modelid=".$objEquivClass->Model->Id."'>".$objEquivClass->Model->Name."</a></td>";
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	

function ListRelationships(&$count){

	global $objClass;
	
	$count = 0;
	
	$Content = '';
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>From Class</th><th>Relationships</th><th>To Class</th><th>Model</th></tr></thead>";

	$Content .= "<tbody>";

	$arrRelationships = array();
	foreach($objClass->AllRelationships as $objRelationship){
		
		$count = $count + 1;
		
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

function cmpName($a, $b){
    return strcmp( strtolower($a->Name),  strtolower($b->Name));
}
	

?>