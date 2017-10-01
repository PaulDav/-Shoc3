<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	require_once("class/clsThread.php");
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlDocument.php");
	require_once("panel/pnlBox.php");
	
	require_once("panel/pnlThread.php");
	
	define('PAGE_NAME', 'form');

	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
			
	$Page = new clsPage();
	
	$objThread = new clsThread();
	

	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);

	$Page->Script .= "<script type='text/javascript' src='../pdlib/java/ajax.js'></script>";
	$Page->Script .= "<script type='text/javascript' src='java/shoc.js'></script>";

	$ParamSid = $System->Session->ParamSid;
			
	
	try {

	
		$InitScript = '';
	
		$InitScript .= "<script>\n";
		$InitScript .= "function init(){ \n";		
		$InitScript .= "	gShoc.SessionId = '".$System->Session->Sid."'; \n";
		
		$Script = "<script> \n";

		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$uriActivity = null;
		$uriRevision = null;		
		$uriDocument = null;
		$uriBox = null;
//		$TemplateId = null;
		$ObjectId = null;
		
		$FormType = 'subject';

		$objActivity = null;		
		$objRevision = null;
		$objDocument = null;
		$objForm = null;
		$objBox = null;
		$objObject = null;

		if (isset($_REQUEST['objectid'])){
			$ObjectId = $_REQUEST['objectid'];
			if (!isset($Archetypes->Objects[$ObjectId])){
				throw new exception("Unknown Object");
			}
			$objObject = $Archetypes->Objects[$ObjectId];
		}		
		if (isset($_REQUEST['uribox'])){
			$uriBox = $_REQUEST['uribox'];
			$objBox = $Shoc->getBox($uriBox);
		}

		if (isset($_REQUEST['urirevision'])){
			$uriRevision = $_REQUEST['urirevision'];
			$objRevision = $Shoc->getRevision($uriRevision);
			$objDocument = $objRevision->Document;
			$uriDocument = $objDocument->Uri;
			$objForm = $objRevision->Form;
		}
		else
		{
			if (isset($_REQUEST['uridocument'])){
				$uriDocument = $_REQUEST['uridocument'];
				$objDocument = $Shoc->getDocument($uriDocument);
				$FormType = $objDocument->Type;
				$objForm = $objDocument->Form;
			}
		}
		
		if (!is_null($objDocument)){
			$objBox = $objDocument->getBox();
		}	

		if (is_null($objForm)){
			$objForm = new clsForm();
			$objForm->Object = $objObject;
		}

		if (!is_null($objBox)){
			$objActivity = $objBox->Group->Activity;
		}
		if (is_null($objActivity)){
			if (!is_null($objThread->uriActivity)){
				$objActivity = $Shoc->getActivity($objThread->uriActivity);
			}
		}

		if (!is_null($objActivity)){
			$objThread->uriActivity = $objActivity->Uri;
			$PanelC = pnlActivityMenu($objActivity);
		}

		switch ($Mode){
			case 'new':
	
				break;
			default:
				if ($uriDocument =='') {
					throw new exception("uriDocument not specified");
				}
	
				break;
		}


		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');
		}
	
		$Page->Title = $Mode." form";
		$PanelB .= "<h1>".$Page->Title."</h1>";


	$ModeOk = false;
	switch ($Mode){
		case 'view':
			if ($objDocument->canView){
				$ModeOk = true;
			}
			break;
		case 'new':
			if ($objBox->canEdit){
				$ModeOk = true;
			}
			break;
			
		case 'edit':
			if ($objDocument->canEdit){
				$ModeOk = true;
			}
			break;
		case 'delete':
			if ($objDocument->canControl){
				$ModeOk = true;
			}
			break;
	}
	if (!$ModeOk){
		throw new Exception("Invalid Mode");
		break;
	}


		if (!is_null($objDocument)){
			$Tabs .= "<li><a href='#document'>Document</a></li>";
			$TabContent .= "<div class='tabContent hide' id='document'>";
		
			$TabContent .= "<h3>Document</h3>";
			$TabContent .= pnlDocument($objDocument);
				
			$TabContent .= "</div>";
		}

		if (!is_null($objBox)){
			$Tabs .= "<li><a href='#box'>Box</a></li>";
			$TabContent .= "<div class='tabContent hide' id='box'>";
		
			$TabContent .= "<h3>Box</h3>";
			$TabContent .= pnlBox($objBox);
				
			$TabContent .= "</div>";
		}
	
	
		
		$Tabs .= "<li><a href='#formtemplate'>Form Template";
		$TabContent .= "<div class='tabContent hide' id='formtemplate'>";
	
		$TabContent .= "<pre>".htmlentities($objForm->xml)."</pre>";
						
		$TabContent .= "</div>";
		$Tabs .= "</a></li>";
				
		
		
		
		switch ($Mode){
			case 'view':
	/*
	*/
	
				break;
			case 'new':
			case 'edit':
	
				$PanelB .= "<div id='form1'></div>";
				$Script .= "   var Form1; \n";
				
				
				
				$PanelB .= "<form method='post' action='doForm.php?$ParamSid'>";
				switch ($FormType){
					case 'link':
						$PanelB .= makeLinkForm();
						$PanelB .= "<input type='submit' value='Create New Revision'>";
						break;
					default:
						$InitScript .= "Form1 = new clsShocForm('form1', 'xmlform1'); \n";
						$PanelB .= "<input type='hidden' name='xmlform' id='xmlform1' value='".encode($objForm->xml)."'/>";
						$PanelB .= "<input onClick='Form1.makeXml();' type='submit' value='Create New Revision'/>";
						break;
				}
	
				if (!is_null($objRevision)){
					$PanelB .= "<input type='hidden' name='revisionof' value='".$objRevision->Uri."'/>";
				}
	
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!isemptystring($uriDocument)){
					$PanelB .= "<input type='hidden' name='uridocument' value='$uriDocument'/>";
				}
				if (!isemptystring($uriBox)){
					$PanelB .= "<input type='hidden' name='uribox' value='$uriBox'/>";
				}
				if (!isemptystring($ObjectId)){
					$PanelB .= "<input type='hidden' name='objectid' value='$ObjectId'/>";
				}
				
	
	/*			
				switch ( $Mode ){
					case "new":
					case "edit":
						$PanelB .= "<input onClick='Form1.makeXml();' type='submit' value='Create New Revision'>";
						break;
				}
	*/
				$PanelB .= '</form>';
				
				break;
	
				//			case 'delete':
	
				//				$PanelB .= pnlShape( $ShapeId );
	
				//				$PanelB .= "<a href='doDocument.php?documentid=$DocumentId&mode=delete'>confirm delete?</a><br/>";
	
				//				break;
	
		}
	
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
	
		$PanelB = pnlThread().$PanelB;
	
		$Page->ContentPanelB = $PanelB;
		$Page->ContentPanelC = $PanelC;
		
		
		
		$Script .= "</script>";
		$Page->Script .= $Script;
		
		
		$InitScript .= "}</script> \n";
		$Page->Script .= $InitScript;
		
		 
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 
	$Page -> Display();



function makeLinkForm(){
	
	global $objForm;
	
	$Content = '';
	
	$Content .= '<table class="sdbluebox">';

	
	$Content .= '<tr>';
	$Content .= '<th>';
	$Content .= 'From Subject';
	$Content .= '</th>';
	$Content .= '<td>';
	$Content .= $objForm->Document->FromSubject->Title;
	$Content .= '</td>';
	$Content .= '</tr>';
	
	
	$Content .= '<tr>';
	$Content .= '<th>';
	$Content .= 'Relationship';
	$Content .= '</th>';
	$Content .= '<td>';
	$Content .= "<select name='urirelationship'>";
	$Content .= "<option/>";
	
	if (!is_null($objForm->Document->FromSubject)){
		foreach ($objForm->Document->FromSubject->Class->AllRelationships as $optRelationship){
			if ($optRelationship->ToClass === $objForm->Document->ToSubject->Class){
				
				if (!($optRelationship->Extending)){
					$Content .= "<option";
					$Content .= " value='".$optRelationship->Uri."'";
//					if ($optModel->Id == $TypeId){
//						$PanelB .= " selected='true' ";
//					}
					$Content .= ">".$optRelationship->Label."</option>";
				}
			}
		}
	}
	$Content .= "</select>";
	$Content .= '</td>';
	$Content .= '</tr>';

	$Content .= '<tr>';
	$Content .= '<th>';
	$Content .= 'To Subject';
	$Content .= '</th>';
	$Content .= '<td>';
	$Content .= $objForm->Document->ToSubject->Title;
	$Content .= '</td>';
	$Content .= '</tr>';
	
				
	$Content .= '<tr>';
		$Content .= '<th>';
		$Content .= 'Description';
		$Content .= '</th>';
		$Content .= '<td>';
		$Content .= '<textarea rows = "5" cols = "80" name="description" >';
//		$Content .= $Description;
		$Content .= '</textarea>';
		$Content .= '</td>';
	$Content .= '</tr>';
				
	$Content .= "</table>";

	return $Content;
	
}

?>