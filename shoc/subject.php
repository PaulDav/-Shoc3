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
	require_once("panel/pnlSubjectsList.php");
	
	
	
	define('PAGE_NAME', 'subject');

	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$jsScript = "
	<script type='text/javascript' src='../pdlib/java/utils.js'></script>
	<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script>
	<script type='text/javascript' src='../pdlib/java/ajax.js'></script>
	<script type='text/javascript' src='java/shoc.js'></script>
	";
	
	$InitScript = "
	<script>
	function init(){
	";	

	$Page = new clsPage();
	
	$hasMap = false;
	
	$ParamSid = $System->Session->ParamSid;
	$InitScript .= "
		gShoc.SessionId = '".$System->Session->Sid."';
	";
	
	$jsScript .= "
	<script>
		var dot0;
		var viz0;
		var arrMapLayers = [];
		var map0;
	 </script>
	 ";
	
	
	
	try {

		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";
						
		$uriSubject = null;
		$uriBox = null;
		
		$objSubject = null;
		$objBox = null;

		$objThread = new clsThread();
		
		if (isset($_REQUEST['urisubject'])){
			$uriSubject = $_REQUEST['urisubject'];
			if ($uriSubject){
//				$objSubject = new clsSubject($uriSubject);
				$objSubject = $Shoc->getSubject($uriSubject);
				
				
				if (!$objSubject->Loaded){
					throw new exception("Unknown Subject");
				}
				
				$objBox = $objSubject->Box;
				$objThread->uriSubject = $uriSubject;
			}
		}

		
		$objActivity = null;
		if (!is_null($objThread->uriActivity)){
//			$objActivity = new clsActivity($objThread->uriActivity);
			$objActivity = $Shoc->getActivity($objThread->uriActivity);			
		}
		if (is_null($objActivity)){
			if (LoggedOn()){
				if (is_object($objSubject)){
					if (is_object($objSubject->Box)){
						$objActivity = $objSubject->Box->Group->Activity;
					}
				}
			}
		}
		
		if (!is_null($objActivity)){
			$PanelC = pnlActivityMenu($objActivity);
		}		
		
		
		$PageType = 'subject';
		if (!is_null($objSubject)){
			$PageType = $objSubject->Class->Label;			
		}
		
//		$Page->Title = $Mode.' '.$PageType;
		$Page->Title = $PageType;
		if (is_object($objSubject)){
			$Page->Title .= ":".$objSubject->Title;
		}
		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		switch ($Mode){
			case 'new':

				$objBox = null;		
				if (!isset($_REQUEST['uribox'])){
					throw new exception('Box not specified');
				}
				$uriBox = $_REQUEST['uribox'];
				$objBox = $Shoc->getBox($uriBox);

				$Models = new clsModels();
				$Archetypes = new clsArchetypes($Models);
				
				break;
			case 'view':
			case 'edit':
				if (is_null($objSubject)){
					throw new exception('Subject not specified');
				}
				$uriBox = $objSubject->Box->Uri;
				break;
		}
		
		


		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSubject->canView){
					$ModeOk = true;
				}
				break;				
			case 'new':
			case 'edit':
				if ($objBox->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objSubject->canEdit){
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

				$PanelB .= pnlSubjectAttributes( $objSubject );
				
				if ($hasMap){
					$jsScript .= "
						<script type='text/javascript' src='../pdlib/java/map.js'></script>
						<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=AIzaSyCaF3hHf-qfEihqp2AX5s-iMI3b9WGFjOw&sensor=false'></script>
						";					
					
					$PanelB .= "<div id='map0'></div>";
					$InitScript .= "
					    map0 = new clsShocMap(arrMapLayers,'map0');
					";			
					
				}

				$CurrentRevision = null;
				foreach ($objSubject->Revisions as $objRevision){
					if (is_null($CurrentRevision)){
						if ($objRevision->Document->Type == 'subject'){
							$CurrentRevision = $objRevision;
						}
					}
				}
				
				if ($objSubject->canEdit){
					if (!is_null($CurrentRevision)){
						$PanelB .= "<div class='hmenu'>";
						$PanelB .= "<ul>";
						$PanelB .= "<li><a href='form.php?$ParamSid&urirevision=".$CurrentRevision->Uri."&mode=edit'>&bull; edit</a></li>";
						$PanelB .= "<li><a href='document.php?$ParamSid&uridocument=".$CurrentRevision->Document->Uri."&mode=delete'>&bull; delete</a></li>";
						$PanelB .= "</ul>";
						$PanelB .= "</div>";
					}
				}
				
				$Tabs .= "<li><a href='#visualize' id='vizloading'>Visualize</a></li>";
				

				
				$TabContent .= "<div class='tabContent hide' id='visualize'>";
				$TabContent .= "<h3>Visualize</h3>";
				
				$TabContent .= "<div class='sdgreybox'>";
				$TabContent .= "<table class='form'>";
				$TabContent .= "<tr>";
				$TabContent .= "<th>Format</th>";
				$TabContent .= "<td>";
				$vizOnChange = "	viz0.show(this.options[this.selectedIndex].value); \n";
				$TabContent .= "<select id='dot0Format' onchange='dot0.get();'>";
					$TabContent .= "<option>image</option>";
					$TabContent .= "<option>dot script</option>";
				$TabContent .= "</select>";
				$TabContent .= "</td>";

				$TabContent .= "<th>Style</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot0Style' onchange='dot0.get();'>";
					$TabContent .= "<option value='1'>graph</option>";
					$TabContent .= "<option value='2'>tables</option>";
				$TabContent .= "</select>";
				$TabContent .= "</td>";

				$TabContent .= "<th>Depth</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot0Depth' onchange='dot0.get();'>";
					$TabContent .= "<option>1</option>";
					$TabContent .= "<option selected='selected'>2</option>";
					$TabContent .= "<option>3</option>";
					$TabContent .= "<option>4</option>";
					$TabContent .= "<option>5</option>";
					$TabContent .= "<option>6</option>";
					$TabContent .= "<option>7</option>";
					$TabContent .= "<option>8</option>";
					$TabContent .= "</select>";
				$TabContent .= "</td>";
				
				$TabContent .= "<th>Layout</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot0Layout' onchange='dot0.get();'>";
					$TabContent .= "<option value='dot' selected='selected'>rows</option>";
					$TabContent .= "<option value='circo'>stars</option>";
					$TabContent .= "<option value='twopi'>circles</option>";
					$TabContent .= "<option value='neato'>neat</option>";
					$TabContent .= "</select>";
				$TabContent .= "</td>";
				
				
				
				$TabContent .= "</tr>";
				$TabContent .= "</table>";				
				
				
				$TabContent .= "</div>";
				
				
				$TabContent .= "<div id='viz'></div>";
				
				$InitScript .= "
					viz0 = new clsPdViz(null, 'viz', 'vizloading');
					dot0 = new clsShocSubjectDot('$uriSubject','dot0Style', 'dot0Depth', 'dot0Format', viz0);
					dot0.idLayout = 'dot0Layout';
					";
				
				
				$TabContent .= "</div>";
								
				$numRel = 0;
				
				$Tabs .= "<li><a href='#links'>Links";
				$TabContent .= "<div class='tabContent hide' id='links'>";
				$TabContent .= ListLinks();				
				
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";

				
				if ($objSubject->canEdit){				
					$arrTemplateRelationships = array();
					foreach ($objActivity->Template->Relationships as $objTemplateRelationship){
						if ($objTemplateRelationship->FromObject->Class == $objSubject->Class){
							$arrTemplateRelationships[] = $objTemplateRelationship;
						}
					}
				
					if (count($arrTemplateRelationships) > 0){
						$Tabs .= "<li><a href='#addlink'>Add a Link";
						$TabContent .= "<div class='tabContent hide' id='addlink'>";
						$TabContent .= AddLink();				
						$TabContent .= "</div>";
						$Tabs .= "</a></li>";
					}
				}
				
				
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
				
				$Tabs .= "<li><a href='#id'>Id";
				$TabContent .= "<div class='tabContent hide' id='id'>";
				$TabContent .= pnlSubject( $objSubject );				
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
				
				
				break;
								
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		$PanelB = pnlThread().$PanelB;
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	 	$InitScript .= "
			}
	 	</script>
	 	";
	 	
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
	global $objSubject;
	global $ParamSid;

	
	$count = count($objSubject->Revisions);
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Template</th><th>Date/Time</th><th>By</th>";
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";

	foreach ($objSubject->Revisions as $objRevision){
		$Content .= "<tr>";
		$Content .= "<td><a href='document.php?$ParamSid&urirevision=".$objRevision->Uri."'>".$objRevision->Id."</a></td>";
		$Content .= "<td>";
		if (!is_null($objRevision->Document->Template)){
			$Content .= $objRevision->Document->Template->Name;
		}
		$Content .= "</td>";
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
	


function ListBoxes(&$count){

	global $System;
	global $objSubject;
	global $ParamSid;

	$arrBoxes = array();
	foreach ($objSubject->Revisions as $objRevision){
		if (isset($objRevision->Document)){
			$objBox = $objRevision->Document->Box;
			$arrBoxes[$objBox->Uri] = $objBox;
		}
	}
	$count = count($arrBoxes);
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Title</th><th>Description</th>";
	$Content .="</tr></thead>";
	
	$Content .= "<tbody>";
	
	foreach ($arrBoxes as $objBox){
		$Content .= "<tr>";
		$Content .= "<td><a href='box.php?$ParamSid&uribox=".$objBox->Uri."'>".$objBox->Title."</a></td>";
		$Content .= "<td>".nl2br(truncate($objBox->Description))."</td>";
		$Content .= "</tr>";		
	}
	
	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}		
/*
function ListLinks(&$count){
	global $System;
	global $objSubject;
	global $ParamSid;

	$count = 0;
	
	$Content = '';
	foreach ($objSubject->Class->AllRelationships as $objRelationship){
		for ($i = 1; $i <= 2; $i++) {
			switch ($i){
				case 1:
					$Inverse = false;
					$RelLabel = $objRelationship->Label;
					break;
				case 2:
					$Inverse = true;
					$RelLabel = $objRelationship->InverseLabel;
					break;
			}

			$arrLinks = array();
				
			foreach ($objSubject->Links as $objLink){
				if ($objLink->Relationship == $objRelationship){
					$objLinkSubject = null;
					switch ($Inverse){
						case false:
							if ($objLink->FromSubject->Uri == $objSubject->Uri){
								$arrLinks[$objLink->Uri] = $objLink;
							};
							break;
						default:
							if ($objLink->ToSubject->Uri == $objSubject->Uri){
								$arrLinks[$objLink->Uri] = $objLink;
							};
							break;
					}
				}
			}

			if (count($arrLinks) > 0){
				$Content .= "<h3>$RelLabel</h3>";
				$cnt = count($arrLinks);
				$count += $cnt;
				
				$Content .= "<table class='list'>";
			
				$Content .="<thead><tr>";
				$Content .= "<th>From Subject</th><th>Relationship</th><th>To Subject</th>";
				$Content .="</tr></thead>";
				
				$Content .= "<tbody>";
				
				foreach ($arrLinks as $objLink){
					$Content .= "<tr>";
					
					$Content .= "<td>";
					if (!is_null($objLink->FromSubject)){
						$Content .= $objLink->FromSubject->Title;
					}
					$Content .= "</td>";

					$Content .= "<td>";
					if (!is_null($objLink->Relationship)){
						if (is_null($objLink->BoxLink)){
							$Content .= "<a href='link.php?$ParamSid&urilink=".$objLink->Uri."'>".$objLink->Relationship->Label."</a>";
						}
						else
						{
							$Content .= $objLink->Relationship->Label;							
						}
					}
					$Content .= "</td>";
					
					$Content .= "<td>";
					if (!is_null($objLink->ToSubject)){
						$Content .= $objLink->ToSubject->Title;
					}
					$Content .= "</td>";
				}
															
				$Content .= "</tbody>";
				$Content .= "</table>";					
			}
			
		}
	}
	return $Content;
}


function ListLinks1(&$cnt){
	global $System;
	global $objSubject;
	global $ParamSid;
	
	global $objActivity;
	
	global $jsScript;
	global $InitScript;

	$count = 0;
	$RelNum = 0;
	
	$Content = '';
	
	if (is_null($objActivity)){
		return null;
	}
	
	foreach ($objActivity->Template->Relationships as $objArchRel){
		for ($i=1;$i<=2;$i++){
			$useRelationship = false;			
			switch ($i){
				case 1:
					if ($objArchRel->FromObject->Class == $objSubject->Class){
						$useRelationship = true;
						$Inverse = false;
						$RelLabel = $objArchRel->Relationship->Label;
						if ($objArchRel->Inverse === true){
							$Inverse = true;
							$RelLabel = $objArchRel->Relationship->InverseLabel;
						}
						$ObjectId = $objArchRel->ToObject->Id;
					}
					break;
				case 2:
					if ($objArchRel->ToObject->Class == $objSubject->Class){
						$useRelationship = true;
						$Inverse = true;
						$RelLabel = $objArchRel->Relationship->InverseLabel;
						if ($objArchRel->Inverse === true){
							$Inverse = false;
							$RelLabel = $objArchRel->Relationship->Label;
						}
						$ObjectId = $objArchRel->FromObject->Id;						
					}
					break;
			}
		
			if ($useRelationship){
					
				++$RelNum;
				
				$Content .= "<div>";
				$Content .= "<h3>$RelLabel</h3>";
				$Content .= "<div id='ListLinks$RelNum'></div>";				
				$Content .= "</div>";				
			
				$jsScript .= "
				    var List$RelNum;
				";
			
				$InitScript .= "
					List$RelNum = new clsShocListSubject('ListLinks$RelNum');
				";
				
				if (!is_null($objActivity)){
					$InitScript .= "
						List$RelNum.uriActivity = '".$objActivity->Uri."';
					";			
				}
				
				$InitScript .= "
					List$RelNum.uriLinkSubject = '".$objSubject->Uri."';
					List$RelNum.RelId = '".$objArchRel->Relationship->Id."';
				";
				
				if ($Inverse){
					$InitScript .= "
						List$RelNum.Inverse = true;
					";					
				}
				
				$InitScript .= "
					List$RelNum.ObjectId = '$ObjectId';
					List$RelNum.Action = 'subjectlink';
					List$RelNum.get();
				";
			}
		}
	}

	return $Content;
}
*/
function ListLinks(){
	global $System;
	global $objSubject;
	global $ParamSid;
	
	global $objActivity;
	
	global $jsScript;
	global $InitScript;

	$count = 0;
	$RelNum = 0;
	
	$Content = '';
	
	if (is_null($objActivity)){
		return null;
	}
	
	$Content .= "<table>";
	$Content .= "<tr>";
	$Content .= "<th>Relationship</th>";
	$Content .= "<td>";
	$Content .= "<select id='ListLinksRel' onchange='ListLinks.get();'>";
	
	$Content .= "<option/>";

	
		
	foreach ($objActivity->Template->Relationships as $objArchRel){
		for ($i=1;$i<=2;$i++){
			$useRelationship = false;			
			switch ($i){
				case 1:
					if ($objArchRel->FromObject->Class == $objSubject->Class){
						$useRelationship = true;
						$Inverse = false;
						$RelLabel = $objArchRel->Relationship->Label;
						if ($objArchRel->Inverse === true){
							$Inverse = true;
							$RelLabel = $objArchRel->Relationship->InverseLabel;
						}
						$RelObject = $objArchRel->ToObject;
					}
					break;
				case 2:
					if ($objArchRel->ToObject->Class == $objSubject->Class){
						$useRelationship = true;
						$Inverse = true;
						$RelLabel = $objArchRel->Relationship->InverseLabel;
						if ($objArchRel->Inverse === true){
							$Inverse = false;
							$RelLabel = $objArchRel->Relationship->Label;
						}
						$RelObject = $objArchRel->FromObject;						
					}
					break;
			}
		
			if ($useRelationship){
				
// add the object class label to the end of the relationship label if it is not their already

				if (substr(strtolower($RelLabel), -strlen($RelObject->Label)) != strtolower($RelObject->Label)){
					$RelLabel .= ' '.$RelObject->Label;
				}
				$ObjectId = $RelObject->Id;
				
				$Content .= "<option value='relid_".$objArchRel->Relationship->Id;
				if ($Inverse){
					$Content .= "_inverse";
				}
				$Content .= "_objectid_".$ObjectId;				
				$Content .= "'>".$RelLabel."</option>";
			}
		}				
	}
	
	$Content .= "</select>";
	$Content .= "</td>";
	$Content .= "</tr>";
	$Content .= "</table>";
	

	$Content .= "<div id='ListLinks'></div>";				
	$Content .= "</div>";				
			
	$jsScript .= "
		<script>
		    var ListLinks;
		</script>
	";

	$InitScript .= "
		ListLinks = new clsShocListSubject('ListLinks');
	";
	
	if (!is_null($objActivity)){
		$InitScript .= "
			ListLinks.uriActivity = '".$objActivity->Uri."';
		";			
	}
	
	$InitScript .= "
		ListLinks.uriLinkSubject = '".$objSubject->Uri."';
		ListLinks.Action = 'subjectlink';
		ListLinks.fldidListLinksRel = 'ListLinksRel';
		ListLinks.FilterPrefix = 'filterlistlinks';
	";

	
	
	return $Content;
}


function AddLink(){

	global $System;
	global $objSubject;
	global $ParamSid;

	global $objActivity;

	global $jsScript;
	global $InitScript;

	$Content = '';
	
	$uriBox = $objSubject->Box->Uri;
	
	if (is_null($objActivity)){
		return $Content;
	}
	
	$uriActivity = $objActivity->Uri;
	
	$Content .= "<h3>Add a Link</h3>";
	
	$Content .= "<div class='sdgreybox'>";	
	$Content .= "<table>";
	$Content .= "<tr>";
	$Content .= "<th>Relationship</th>";
	$Content .= "<td>";
	$Content .= "<select name='archrelid' id='selLink' onchange='LinkSel1.get()'>";
	
	$Content .= "<option/>";
	
	$XmlContent = '';
			
	foreach ($objActivity->Template->Relationships as $objTemplateRelationship){
		if ($objTemplateRelationship->FromObject->Class == $objSubject->Class){
			$ArchRelId = $objTemplateRelationship->Id;
			$ToObjectId = $objTemplateRelationship->ToObject->Id;
			$Action = 'select';
			if ($objTemplateRelationship->Relationship->Extending === true){
				$Action = 'add';
				$objLinkForm = new clsForm();
				$objLinkForm->Object = $objTemplateRelationship->ToObject;				
				$XmlContent .= "<input type='hidden' id='xmltemplateform$ToObjectId' value='".encode($objLinkForm->xml)."'/>";
			}

			$RelLabel = $objTemplateRelationship->Label;
			$RelObject = $objTemplateRelationship->ToObject;
			if (substr(strtolower($RelLabel), -strlen($RelObject->Label)) != strtolower($RelObject->Label)){
				$RelLabel .= ' '.$RelObject->Label;
			}

			$Content .= "<option value='".$Action."_archrel_".$ArchRelId."_object_".$ToObjectId."'>".$RelLabel."</option>";
			
		}
	}
	
	$Content .= "</select>";
	$Content .= "</td>";
	$Content .= "</tr>";
	$Content .= "</table>";

	$Content .= $XmlContent;

	$Content .= "</div>";	
	
	$Content .= "<div id='divLinkSubject'></div>";
	
	$jsScript .= "
		<script>
			var MakeLink1;
		</script>
	";
	
	
	$InitScript .= "
		LinkSel1 = new clsShocMakeLink( 'divLinkSubject','link', 'filteraddlink');
		LinkSel1.fldidSelect = 'selLink';
		LinkSel1.FilterPrefix = 'filterlink';
		LinkSel1.uriBox = '$uriBox';
		LinkSel1.uriFromSubject = '".$objSubject->Uri."';
		LinkSel1.uriActivity = '$uriActivity';
		LinkSel1.get();
	";
	
	
	return $Content;
}



?>