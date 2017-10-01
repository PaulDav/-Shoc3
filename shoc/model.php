<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlModel.php");
		
	define('PAGE_NAME', 'model');

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
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$ModelId = null;

		if (isset($_REQUEST['modelid'])){
			$ModelId = $_REQUEST['modelid'];
			
			if (!($objModel = $Models->getItem($ModelId))){
				throw new exception("Unknown Model");
			}
		}

		
		$Page->Title = $Mode." Model";
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
				$PanelB .= pnlModel( $ModelId );
				
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
				
				$InitScript .= "	viz0 = new clsPdViz(".json_encode($objModel->dot).", 'viz', 'vizloading'); \n";
				$InitScript .= "    viz0.show()";
				
				$TabContent .= "</div>";
				
				
				

				$Tabs .= "<li><a href='#packages'>Packages";
				$TabContent .= "<div class='tabContent hide' id='packages'>";
				$TabContent .= ListPackages($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				
				$Tabs .= "<li><a href='#classes'>Classes";
				$TabContent .= "<div class='tabContent hide' id='classes'>";
				$TabContent .= ListClasses($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";

				$Tabs .= "<li><a href='#lists'>Lists";
				$TabContent .= "<div class='tabContent hide' id='lists'>";
				$TabContent .= ListLists($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				
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
	
function ListPackages(&$count){
	
	global $objModel;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objModel->Packages) > 0){
		
		usort($objModel->Packages, 'cmpName');
		
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Definition</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objModel->Packages as $objPackage){
			
			$count = $count + 1;

			$Content .= "<tr>";												
			$Content .= "<td><a href='package.php?modelid=".$objModel->Id."&packageid=".$objPackage->Id."'>".$objPackage->Name."</a></td>";
			$Content .= "<td>".$objPackage->Version."</td>";		
			$Content .= "<td>".make_links($objPackage->Definition)."</td>";			
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	
	
	
	
function ListClasses(&$count){
	
	global $objModel;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objModel->Classes) > 0){
		
		usort($objModel->Classes, 'cmpName');
		
		
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th><th>Definition</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objModel->Classes as $objClass){
			
			$count = $count + 1;

			$Content .= "<tr>";												
			$Content .= "<td><a href='class.php?modelid=".$objModel->Id."&classid=".$objClass->Id."'>".$objClass->Name."</a></td>";
			$Content .= "<td>".$objClass->Version."</td>";		
			$Content .= "<td>".make_links($objClass->Definition)."</td>";			
			
			$Content .= "</tr>";
		
		}

		$Content .= '</table>';
	}
	
	return $Content;
				
}	


function ListLists(&$count){

	global $objModel;
	
	$count = 0;
	
	$Content = '';
	
	if (count($objModel->Lists) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		$Content .= "<th>Name</th><th>Version</th>";
		$Content .= '</tr>';
		$Content .= '</thead>';
					
		foreach ( $objModel->Lists as $objList){

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


function cmpName($a, $b){
    return strcmp( strtolower($a->Name),  strtolower($b->Name));
}


?>