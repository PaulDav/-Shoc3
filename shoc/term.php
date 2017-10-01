<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
		
	require_once("function/utils.inc");

	require_once("panel/pnlList.php");
	require_once("panel/pnlModel.php");
	
	define('PAGE_NAME', 'term');

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
		
		$objTerm = null;
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

		if (isset($_REQUEST['termid'])){
			$TermId = $_REQUEST['termid'];
			
			if (!isset($Models->Terms[$TermId])){
				throw new exception("Unknown Term");
			}
			$objTerm = $Models->Terms[$TermId];
			$objList = $objTerm->List;
			$objModel = $objList->Model;			
		}
		
		$Page->Title = $Mode." Term";
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
				$PanelB .= pnlTerm( $TermId );

				$Tabs .= "<li><a href='#list'>List";
				$TabContent .= "<div class='tabContent hide' id='list'>";
				$TabContent .= pnlList($objList->Id);
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
								
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
		

?>