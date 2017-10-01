<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");

	require_once("class/clsThread.php");
	require_once("panel/pnlThread.php");
	
		
	require_once("function/utils.inc");

	
	require_once("panel/pnlBox.php");
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlBoxLink.php");
	
	
	
	define('PAGE_NAME', 'boxlink');

	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$ParamSid = $System->Session->ParamSid;
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";
	$jsScript .= "\n";
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";		
	
	
	$Page = new clsPage();
	
	$Models = new clsModels();
	$Archetypes = new clsArchetypes($Models);

	try {

		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";

		
		$Description = '';
		
		$uriActivity = null;
		$objActivity = null;
		$uriBoxLink = null;		
		$objBoxLink = null;
		$uriBox = null;
		$objBox = null;
		
		$ObjectId = null;
		$objObject = null;
		
		$uriToSubject = null;
		$objToSubject = null;

		$ArchRelId = null;		
		$objArchRel = null;

		$uriRelationship = null;
		$objRelationship = null;
		
		$Inverse = false;
		$RelLabel = null;
						
		$objThread = new clsThread();
		if (!is_null($objThread->uriActivity)){
			$objActivity = $Shoc->getActivity($objThread->uriActivity);
			$PanelC = pnlActivityMenu($objActivity);
		}
		if (!is_null($objThread->uriBox)){
			$uriBox = $objThread->uriBox;
			$objBox = $Shoc->getBox($uriBox);
		}
		
		if (isset($_REQUEST['uriboxlink'])){
			$uriBoxLink = $_REQUEST['uriboxlink'];
//			$objBoxLink = new clsBoxLink($uriBoxLink);
			$objBoxLink = $Shoc->getBoxLink($uriBoxLink);
			
			$objBox = $objBoxLink->Box;
			$objRelationship = $objBoxLink->Relationship;
			if (is_object($objRelationship)){
				$uriRelationship = $objRelationship->Uri;
			}
			$Inverse = $objBoxLink->Inverse;
			$RelLabel = $objBoxLink->RelLabel;
			$objObject = $objBoxLink->Object;
			if (is_object($objObject)){			
				$ObjectId = $objObject->Id;
			}
			$objToSubject = $objBoxLink->Subject;
			if (is_object($objToSubject)){			
				$uriToSubject = $objToSubject->Uri;
			}
			$Description = $objBoxLink->Description;			
			
		}

		if (isset($_REQUEST['archrelid'])){
			$ArchRelId = $_REQUEST['archrelid'];
		}
		
		
		if (isset($_REQUEST['uritosubject'])){
			$uriToSubject = $_REQUEST['uritosubject'];
		}
		
		
		$PageType = 'boxlink';
		
		$Page->Title = $Mode.' '.$PageType;
		$PanelB .= "<h1>".$Page->Title."</h1>";

		
		switch ($Mode){
			case 'view':
			case 'edit':
			case 'delete':
				if (is_null($objBoxLink)){
					throw new exception('Box Link not specified');
				}
				break;
			case 'new':
				
				if (is_null($objActivity)){
					throw new Exception("No Activity");
				}

				if (is_null($objBox)){
					throw new Exception("No Box");
				}
				
				
				if (is_null($uriToSubject)){
					throw new Exception("To Subject not specified");
				}
//				$objToSubject = new clsSubject($uriToSubject);
				$objToSubject = $Shoc->getSubject($uriToSubject);
				

				if (is_null($ArchRelId)){
					throw new Exception("Template Relationship not specified");
				}
				$objArchRel = $Archetypes->Relationships[$ArchRelId];
				$RelLabel = $objArchRel->Label;
				$objObject = $objArchRel->FromObject;
				
				break;				

		}
		
		

/*		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objModel->canView){
					$ModeOk = true;
				}
				break;				
			case 'new':
				if ($objModel->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objModel->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objModel->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}
*/								
				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlBoxLink( $objBoxLink );
				if ($objBoxLink->canControl){
					$PanelB .= "<div class='hmenu'><ul>";					
					$PanelB .= "<li><a href='boxlink.php?$ParamSid&uriboxlink=$uriBoxLink&mode=edit'>&bull; edit</a></li>";
					$PanelB .= "<li><a href='boxlink.php?$ParamSid&uriboxlink=$uriBoxLink&mode=delete'>&bull; delete</a></li>";
					$PanelB .= "</ul></div>";					
				}
				
				break;
				
				
		case 'new':
		case 'edit':

			$PanelB .= "<form method='post' action='doBoxLink.php?$ParamSid'>";
			
			$PanelB .= '<table class="sdbluebox">';		
			
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'From Class';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $objObject->Label;
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'Relationship';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $RelLabel;			
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			if ($Mode == 'new'){
				$PanelB .= "<input type='hidden' name='archrelid' value='$ArchRelId'/>";
			}
			else
			{
				$PanelB .= "<input type='hidden' name='urirelationship' value='$uriRelationship'/>";
				if ($Inverse){
					$PanelB .= "<input type='hidden' name='inverse' value='true'/>";
				}
				$PanelB .= "<input type='hidden' name='objectid' value='$ObjectId'/>";				
			}
			
			
		
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'To Subject';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $objToSubject->Title;
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			$PanelB .= "<input type='hidden' name='urisubject' value='$uriToSubject'/>";
			
						
			$PanelB .= '<tr>';
				$PanelB .= '<th>';
				$PanelB .= 'Description';
				$PanelB .= '</th>';
				$PanelB .= '<td>';
				$PanelB .= '<textarea rows = "5" cols = "80" name="description" >';
				$PanelB .= $Description;
				$PanelB .= '</textarea>';
				$PanelB .= '</td>';
			$PanelB .= '</tr>';
						
			$PanelB .= "</table>";
						
			
			$PanelB .= "<input type='submit' value='Update the Link'>";

			if (!is_null($objBox)){
				$PanelB .= "<input type='hidden' name='uribox' value='".$objBox->Uri."'/>";
			}
			
			
			if (!is_null($objBoxLink)){
				$PanelB .= "<input type='hidden' name='uriboxlink' value='".$objBoxLink->Uri."'/>";
			}
			
			$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";

			$PanelB .= '</form>';
			
			break;
				
		case 'delete':			
			
			$PanelB .= pnlBoxLink( $objBoxLink );
			if ($objBoxLink->canControl){
				$PanelB .= "<div class='hmenu'><ul>";
				$PanelB .= "<li><a href='doboxlink.php?$ParamSid&uriboxlink=$uriBoxLink&mode=delete'>&bull; confirm delete?</a></li>";
				$PanelB .= "</ul></div>";	
			}
				
			break;
			
		}
		
		
		$Tabs .= "<li><a href='#box'>Box";
		$TabContent .= "<div class='tabContent hide' id='box'>";
		$TabContent .= pnlBox($objBox);
		$TabContent .= "</div>";
		$Tabs .= "</a></li>";
		
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
		$PanelB = pnlThread().$PanelB;		
		
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