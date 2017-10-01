<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlRelationship.php");
	require_once("panel/pnlModel.php");
	
	define('PAGE_NAME', 'relationship');

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
		$objRelationship = null;
		$objModel = null;
		$ModelId = null;
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$RelationshipId = null;

		if (isset($_REQUEST['relationshipid'])){
			$RelationshipId = $_REQUEST['relationshipid'];
			
			if (!isset($Models->Relationships[$RelationshipId])){
				throw new exception("Unknown Relationship");
			}
			$objRelationship = $Models->Relationships[$RelationshipId];
			
			$objModel = $objRelationship->Model;
			$ModelId = $objModel->Id;			
			
		}
		
		$Page->Title = $Mode." Relationship";
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
				$PanelB .= pnlRelationship( $RelationshipId );				
				
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
		

?>