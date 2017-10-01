<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("class/clsThread.php");
	

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	require_once("panel/pnlThread.php");
	
	
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlDocument.php");
	require_once("panel/pnlRevision.php");
	require_once("panel/pnlForm.php");
	require_once("panel/pnlBox.php");
	
	define('PAGE_NAME', 'document');

	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";	
	$jsScript .= "\n";
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";		
	
	
	$Page = new clsPage();

	$objThread = new clsThread();
	
	try {

		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
		
		$objTemplate = null;
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$uriDocument = null;
		$uriRevision = null;
		
		$TemplateId = null;

		
		$objDocument = null;
		$objRevision = null;
		$objBox = null;
		
		if (isset($_REQUEST['uridocument'])){
			$uriDocument = $_REQUEST['uridocument'];
			
			$objDocument = $Shoc->getDocument($uriDocument);
			$objRevision = $objDocument->CurrentRevision;
			$objBox = $objDocument->getBox();
		}

		
		if (isset($_REQUEST['urirevision'])){
			$uriRevision = $_REQUEST['urirevision'];

			$objRevision = $Shoc->getRevision($uriRevision);			
			$objDocument = $objRevision->Document;
			$uriDocument = $objDocument->Uri;
			$objBox = $objDocument->getBox();
			
			if (isset($objDocument->Revisions[$uriRevision])){
// this is to set the Revision Number
				$objRevision = $objDocument->Revisions[$uriRevision];
			}
			
		}
		
		$objThread->uriDocument = $uriDocument;

		if (is_null($objThread->uriBox)){
			if (is_object($objBox)){
				$objThread->uriBox = $objBox->Uri;	
				$objThread->uriGroup = $objBox->Group->Uri;	
				$objThread->uriActivity = $objBox->Group->Activity->Uri;	
			}
		}
		
		$objThread->uriSubject = null;
		
		
		$Page->Title = $Mode." Document";
		$PanelB .= "<h1>".$Page->Title."</h1>";

		
		switch ($Mode){
			case 'view':
			case 'edit':
			case 'delete':
				if (is_null($objDocument)){
					throw new exception('Document not specified');
				}
				break;
		}
		
		


		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objDocument->canView){
					$ModeOk = true;
				}
				break;				
			case 'new':
				if ($System->LoggedOn){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objRevision->canEdit){
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
				
				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlDocument( $objDocument );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDocument->canControl){
					$PanelB .= "<li><a href='document.php?uridocument=$uriDocument&mode=delete'>&bull; delete</a></li>";
				}
				$PanelB .= "</ul></div>";
				
				
				$Tabs .= "<li><a href='#form'>Form";
				$TabContent .= "<div class='tabContent hide' id='form'>";
				
				if (!is_null($objRevision)){
					$TabContent .= pnlForm($objRevision->Form);					
					$uriRevision = $objRevision->Uri;

					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='form.php?urirevision=$uriRevision&mode=edit'>&bull; edit</a></li>";
					$TabContent .= "</ul></div>";
					
				}
				else
				{
					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='form.php?uridocument=$uriDocument&mode=edit'>&bull; edit</a></li>";
					$TabContent .= "</ul></div>";
				}
								
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";

				$Tabs .= "<li><a href='#box'>Box</a></li>";
				$TabContent .= "<div class='tabContent hide' id='box'>";			
				$TabContent .= "<h3>Box</h3>";
				$TabContent .= pnlBox($objBox);					
				$TabContent .= "</div>";
				
				
				if (!is_null($objRevision)){
					$Tabs .= "<li><a href='#thisrevision'>This Revision";
					$TabContent .= "<div class='tabContent hide' id='thisrevision'>";
					$TabContent .= '<h3>Revision</h3>';
					$TabContent .= pnlRevision( $objRevision );
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}
				
				$Tabs .= "<li><a href='#revisions'>Revisions";
				$TabContent .= "<div class='tabContent hide' id='revisions'>";
				$TabContent .= ListRevisions($count);
				
								
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
								
				break;
				
				
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doDocument.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";

				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'uri';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $uriDocument;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Template';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='templateid'>";
					$PanelB .= "<option/>";
					foreach ($Archetypes->Items as $optTemplate){
						$PanelB .= "<option";
						$PanelB .= " value='".$optTemplate->Id."'";
						if ($optTemplate->Id == $TemplateId){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optTemplate->Label."</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Document">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Document">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlDocument( $objDocument );
				if (!is_null($objDocument->CurrentRevision)){
					$PanelB .= pnlForm($objDocument->CurrentRevision->Form);
				}					

				$PanelB .= "<div class='hmenu'><ul>";
				$PanelB .= "<li><a href='doDocument.php?uridocument=$uriDocument&mode=delete'>&bull; confirm delete?</a></li>";
				$PanelB .= "</ul></div>";
				
				
				break;
				
		}
		
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
	

function ListRevisions(&$count){

	global $System;
	global $Models;
	global $objDocument;

	
	$count = count($objDocument->Revisions);
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Number</th><th>Date/Time</th><th>By</th>";
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";

	foreach ($objDocument->Revisions as $objRevision){
		$Content .= "<tr>";
		$Content .= "<td><a href='document.php?urirevision=".$objRevision->Uri."'>".$objRevision->Id."</a></td>";		
		$Content .= "<td>".$objRevision->Number--."</td>";
		$Content .= "<td>".date('d/m/Y H:i:s',$objRevision->Timestamp)."</td>";

		$Content .= "<td>";		
		if (!is_null($objRevision->User)){
			if (!is_null($objRevision->User->PictureOf)) {
				$Content .= '<img height = "20" src="image.php?Id='.$objRevision->User->PictureOf.'" alt="'.$objRevision->User->Name.'" /><br/>'."\n";
			}
			$Content .= $objRevision->User->Name;
		}
		$Content .= "</td>";		
		
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}		
	
?>