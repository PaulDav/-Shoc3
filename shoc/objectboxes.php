<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	define('PAGE_NAME', 'objectboxes');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$Page = new clsPage();	
	
	$Models = new clsModels();
	$Archetypes = new clsArchetypes($Models);
	
	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
		
	try {

		if (!$System->LoggedOn){
			throw new exception("You must be logged on to add data");
		}
		
		$ParamSid = $System->Session->ParamSid;
		
		
		$Page->Title = "Add Data to a Box";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		

		$ModeOk = false;
		
		$Boxes = new clsBoxes();
	
		$Boxes->MemberId = $System->User->Id;			
		
		$Boxes->getItems();
		
		$arrObjects = array();
		$arrObjectBoxes = array();
		
		foreach($Boxes->Items as $objBox){
			
			if (is_object($objBox->Group)){
				if (is_object($objBox->Group->MyMembership)){
					if (is_object($objBox->Group->MyMembership)){
						if ($objBox->Group->MyMembership->Rights->Id >= 100){
							foreach ($objBox->Objects as $objObject){
								$arrObjects[$objObject->Id] = $objObject;			
								$arrObjectBoxes[$objObject->Id][$objBox->Uri] = $objBox;
							}										
						}
					}
				}
			}
			
		}

		usort($arrObjects, "cmpName");
		
		$Content = '';
		$Content .= "<table class='list'>";
	
		$Content .="<thead><tr>";
		$Content .= "<th>Class</th><th>Box</th><th></th>";
		$Content .="</tr></thead>";
		
		$Content .= "<tbody>";

		
		foreach ($arrObjects as $objObject){
			$ObjectRowspan = count($arrObjectBoxes[$objObject->Id]);
			$boolObject = true;
			foreach($arrObjectBoxes[$objObject->Id] as $objBox){			
				$Content .= "<tr>";
				if ($boolObject){
					$Content .= "<td rowspan='$ObjectRowspan'>".$objObject->Label."</td>";
					$boolObject = false;
				}
				$Content .= "<td>".$objBox->Title."</td>";
				$uriBox = $objBox->Uri;
				$ObjectId = $objObject->Id;
				$Content .= "<td><a href='form.php?$ParamSid&uribox=$uriBox&objectid=$ObjectId&mode=new'>&bull; add</a></td>";
				$Content .= "</tr>";
			}
		}

		$Content .= "</tbody>";
		
		$Content .= "</table>";

		$PanelB .= $Content;
			
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 		 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
		
	
function cmpName($a, $b){
    return strcmp( strtolower($a->Name),  strtolower($b->Name));
}
	


?>