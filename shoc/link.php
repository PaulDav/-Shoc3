<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");

	require_once("class/clsThread.php");
	require_once("panel/pnlThread.php");
	
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlLink.php");
	
	
	define('PAGE_NAME', 'link');

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
		$uriLink = null;		
		$objLink = null;
		$uriBox = null;
		$objBox = null;
		$uriDocument = null;
		$objDocument = null;
		$uriRevision =  null;
		$objRevision = null;
		
		$uriFromSubject = null;
		$objFromSubject = null;

		$uriToSubject = null;
		$objToSubject = null;

		$ArchRelId = null;		
		$objArchRel = null;

		$RelationshipId = null;		
		$objRelationship = null;
		
		$optBoxes = null;
				
		$objThread = new clsThread();
		if (!is_null($objThread->uriActivity)){
			$objActivity = $Shoc->getActivity($objThread->uriActivity);
			$PanelC = pnlActivityMenu($objActivity);
		}
		if (!is_null($objThread->uriSubject)){
			$uriFromSubject = $objThread->uriSubject;
		}
		
		if (isset($_REQUEST['urilink'])){
			$uriLink = $_REQUEST['urilink'];
//			$objLink = new clsLink($uriLink);
			$objLink = $Shoc->getLink($uriLink);
			
			if (is_null($objLink->Revision)){
				throw new exception("Unknown Link");
			}
			$objRevision = $objLink->Revision;
			$objBox = $objLink->Box;
			$uriRevision = $objRevision->Uri;
		}

		if (isset($_REQUEST['urirevision'])){
			$uriRevision = $_REQUEST['urirevision'];
			$objRevision = $Shoc->getRevision($uriRevision);
		}
		
		
		if (isset($_REQUEST['archrelid'])){
			$ArchRelId = $_REQUEST['archrelid'];
		}
		

		if (isset($_REQUEST['urifromsubject'])){
			$uriFromSubject = $_REQUEST['urifromsubject'];
		}
		
		if (isset($_REQUEST['uritosubject'])){
			$uriToSubject = $_REQUEST['uritosubject'];
		}		

		if (isset($_REQUEST['uribox'])){
			$uriBox = $_REQUEST['uribox'];
		}
		
		
		$PageType = 'link';
		
		$Page->Title = $Mode.' '.$PageType;
		$PanelB .= "<h1>".$Page->Title."</h1>";

		
		switch ($Mode){
			case 'view':
			case 'edit':
			case 'remove':
				if (is_null($objLink)){
					throw new exception('Link not specified');
				}

				if (is_null($objLink->Revision)){
					throw new exception('Link Revision not found');
				}
				
				if (count($objRevision->Abouts) >0){
					$arrAbouts = $objRevision->Abouts;
					$objAbout = reset($arrAbouts);
					$objArchRel = $objAbout->ArchRel;
					if (is_object($objArchRel)){
						$ArchRelId = $objArchRel->Id;
						
						switch ($objArchRel->Inverse){
							case false:
								$objFromSubject = $objLink->FromSubject;
								$objToSubject = $objLink->ToSubject;
								break;	
							default:
								$objFromSubject = $objLink->ToSubject;
								$objToSubject = $objLink->FromSubject;
								break;	
						}
					}
					if (is_object($objFromSubject)){
						$uriFromSubject = $objFromSubject->Uri;
					}
					if (is_object($objToSubject)){						
						$uriToSubject = $objToSubject->Uri;
					}					
				}
				
				$Description = $objLink->Description;
								
				break;
			case 'new':
				
				if (is_null($objActivity)){
					throw new Exception("No Activity");
				}
				
				if (is_null($uriFromSubject)){
					throw new Exception("From Subject not specified");
				}
//				$objFromSubject = new clsSubject($uriFromSubject);
				$objFromSubject = $Shoc->getSubject($uriFromSubject);
				
				if (is_null($objBox)){
					$objBox = $objFromSubject->Box;
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
				
				$optBoxes = new clsBoxes();
				$optBoxes->uriActivity = $objActivity->Uri;
				$optBoxes->MemberId = $System->User->Id;
				$optBoxes->MemberRightsId = 100;
				$optBoxes->ObjectId = $objArchRel->FromObject->Id;

				if (count($optBoxes->Items) == 0){
					throw new Exception("No Box for the Link");					
				}

				break;				

		}
		
		

		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objLink->canView){
					$ModeOk = true;
				}
				break;				
			case 'new':
				if ($objFromSubject->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objLink->canEdit){
					$ModeOk = true;
				}
				break;
			case 'remove':
				
				if ($objLink->canEdit){
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
				$PanelB .= pnlLink( $objLink );

				$CurrentRevision = null;
				foreach ($objLink->Revisions as $objRevision){
					if (is_null($CurrentRevision)){
						if ($objRevision->Document->Type == 'link'){
							$CurrentRevision = $objRevision;
						}
					}
				}
				
				
				$PanelB .= "<div class='hmenu'><ul>";
				if (!is_null($CurrentRevision)){
					if ($objLink->canEdit){
						$PanelB .= "<li><a href='link.php?$ParamSid&urilink=$uriLink&urirevision=".$CurrentRevision->Uri."&mode=edit'>&bull; edit</a></li>";
						$PanelB .= "<li><a href='link.php?$ParamSid&urilink=$uriLink&mode=remove'>&bull; remove</a></li>";
					}
				}
				$PanelB .= "</ul></div>";
				
				
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

				$InitScript .= "	viz0 = new clsPdViz(".json_encode($objLink->dot).", 'viz', 'vizloading'); \n";
				$InitScript .= "    viz0.show()";

				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
								
				$numRel = 0;
				
				$Tabs .= "<li><a href='#boxes'>Boxes";
				$TabContent .= "<div class='tabContent hide' id='boxes'>";
				$TabContent .= ListBoxes($cnt);												
				$TabContent .= "</div>";
				$Tabs .= "($cnt)</a></li>";
				
				
				$Tabs .= "<li><a href='#revisions'>Revisions";
				$TabContent .= "<div class='tabContent hide' id='revisions'>";
				$TabContent .= ListRevisions($cnt);												
				$TabContent .= "</div>";
				$Tabs .= "($cnt)</a></li>";
				
				
				break;
				
				
		case 'new':
		case 'edit':

			$PanelB .= "<form method='post' action='doLink.php?$ParamSid'>";
			
			$PanelB .= '<table class="sdbluebox">';		
			
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'From Subject';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $objFromSubject->Title;
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			$PanelB .= "<input type='hidden' name='urifromsubject' value='$uriFromSubject'/>";
			
			
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'Relationship';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $objArchRel->Label;			
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			$PanelB .= "<input type='hidden' name='archrelid' value='$ArchRelId'/>";
			
			
		
			$PanelB .= '<tr>';
			$PanelB .= '<th>';
			$PanelB .= 'To Subject';
			$PanelB .= '</th>';
			$PanelB .= '<td>';
			$PanelB .= $objToSubject->Title;
			$PanelB .= '</td>';
			$PanelB .= '</tr>';
			
			$PanelB .= "<input type='hidden' name='uritosubject' value='$uriToSubject'/>";
			
						
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

			if (is_object($objBox)){
				$PanelB .= "<input type='hidden' name='uribox' value='".$objBox->Uri."'/>";
			}
			else
			{
				if ($Mode == 'new'){			
					$PanelB .= "<br/>";
					$PanelB .= "<div class='sdgreybox'>";
					$PanelB .= "<table>";
					$PanelB .= "<tr><th>Add to Box</th>";
					$PanelB .= "<td><select name='uribox'>";
					foreach ($optBoxes->Items as $optBox){
						$PanelB .= "<option value='".$optBox->Uri."'>".$optBox->Title."</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= "</td></tr></table>";
					$PanelB .= "</div>";
				}
			}
			
			$PanelB .= "<input type='submit' value='Update the Link'/>";

			if (!is_null($objLink)){
				$PanelB .= "<input type='hidden' name='urilink' value='".$objLink->Uri."'/>";
			}
			
			
			if (!is_null($objRevision)){
				$PanelB .= "<input type='hidden' name='revisionof' value='".$objRevision->Uri."'/>";
			}

			$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
			if (!isemptystring($uriDocument)){
				$PanelB .= "<input type='hidden' name='uridocument' value='$uriDocument'/>";
			}

			$PanelB .= '</form>';
			
			break;
				
		case 'remove':
				$PanelB .= pnlLink( $objLink );
				$PanelB .= "<div class='hmenu'><ul>";
				$PanelB .= "<li><a href='dolink.php?$ParamSid&urilink=$uriLink&mode=remove'>&bull; confirm remove</a></li>";
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
	global $objLink;

	
	$count = count($objLink->Revisions);
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Template</th><th>Date/Time</th><th>By</th>";
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";

	foreach ($objLink->Revisions as $objRevision){
		$Content .= "<tr>";
		$Content .= "<td><a href='document.php?urirevision=".$objRevision->Uri."'>".$objRevision->Id."</a></td>";
		$Content .= "<td>";
		if (!is_null($objRevision->Document->Template)){
			$Content .= $objRevision->Document->Template->Name;
		}
		$Content .= "</td>";
		$Content .= "<td>".date('d/m/Y H:i:s',$objRevision->Timestamp)."</a></td>";

		$Content .= "<td>";		
		if (!is_null($objRevision->User)){
			if (!is_null($objRevision->User->PictureOf)) {
				$Content .= '<img height = "20" src="image.php?Id='.$objRevision->User->PictureOf.'" /><br/>'."\n";
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
	


function ListBoxes(&$count){

	global $System;
	global $objLink;

	$arrBoxes = array();
	foreach ($objLink->Revisions as $objRevision){
		if (isset($objRevision->Document)){
			$objBox = $objRevision->Document->Box;
			$arrBoxes[$objBox->Uri] = $objBox;
		}
	}
	$count = count($arrBoxes);
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Title</th><th>By</th>";
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";
	
	foreach ($arrBoxes as $objBox){
		$Content .= "<tr>";
		$Content .= "<td><a href='box.php?uribox=".$objBox->Uri."'>".$objBox->Id."</a></td>";
		$Content .= "<td>".$objBox->Title."</td>";

		$Content .= "<td>";		
		if (!is_null($objBox->User)){
			if (!is_null($objBox->User->PictureOf)) {
				$Content .= '<img height = "20" src="image.php?Id='.$objBox->User->PictureOf.'" /><br/>'."\n";
			}
			$Content .= $objBox->User->Name;
		}
		$Content .= "</td>";		
		
		
		$Content .= "</tr>";		
	}
	
	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}		



?>