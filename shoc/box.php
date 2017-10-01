<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("class/clsThread.php");
	

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	require_once("panel/pnlThread.php");
	
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlBox.php");
	require_once("panel/pnlGroup.php");
	
	define('PAGE_NAME', 'box');

	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/ajax.js'></script>";
	$jsScript .= "<script type='text/javascript' src='java/shoc.js'></script>";	
	$jsScript .= "\n";
	
	$jsScript .= "
<script>
	var dot1;
	var viz1;
</script>
";
	
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";	

	$Page = new clsPage();
	
	$ParamSid = $System->Session->ParamSid;
	$InitScript .= "	gShoc.SessionId = '".$System->Session->Sid."'; \n";
		
	$objThread = new clsThread();
	
	try {

		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
		
		$objModel = null;
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$uriBox = null;
		$TypeId = null;
		$uriGroup = null;
		
		$Title = '';
		$Description = '';

		
		$objGroup = null;
		
		if (isset($_REQUEST['urigroup'])){
			$uriGroup = $_REQUEST['urigroup'];
			$objGroup = $Shoc->getGroup($uriGroup);
		}
			
		$objBox = null;
		if (isset($_REQUEST['uribox'])){
			$uriBox = $_REQUEST['uribox'];
			$objBox = $Shoc->getBox($uriBox);
			$Title = $objBox->Title;
			$Description = $objBox->Description;
			$uriGroup = $objBox->uriGroup;
			$objGroup = $objBox->Group;

			$objThread->uriBox = $uriBox;
		}
		if (is_null($objThread->uriGroup)){
			if (is_object($objGroup)){
				$objThread->uriGroup = $objGroup->Uri;	
			}
		}
		
		if (is_null($objThread->uriActivity)){
			if (is_object($objGroup)){			
				if (is_object($objGroup->Activity)){
					$objThread->uriActivity = $objGroup->Activity->Uri;	
				}
			}
		}
		$objThread->uriSubject = null;
		
		$Page->Title = 'Box';
		if (is_object($objBox)){
			$Page->Title .= ":".$objBox->Title;
		}
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		switch ($Mode){
			case 'new':
				break;
			case 'view':
			case 'edit':
			case 'delete':
				if (is_null($objBox)){
					throw new exception('Box not specified');
				}
				break;
		}
		
		


		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objBox->canView){
					$ModeOk = true;
				}
				break;				
			case 'new':
				if ($System->LoggedOn){
					$ModeOk = true;
				}
				break;
			case 'edit':
			case 'delete':
				if ($objBox->canControl){
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
				$PanelB .= pnlBox( $objBox );
				if ($objBox->canControl){
					
					$PanelB .= "<div class='hmenu'><ul>";
					$PanelB .= "<li><a href='box.php?uribox=$uriBox&mode=edit'>&bull; edit</a></li>";
					$PanelB .= "<li><a href='box.php?uribox=$uriBox&mode=delete'>&bull; delete</a></li>";
					$PanelB .= "</ul></div>";
				
				}
				
				$Tabs .= "<li><a href='#visualize1' id='viz1loading'>Visualize</a></li>";
				
				
				$TabContent .= "<div class='tabContent hide' id='visualize1'>";
				$TabContent .= "<h3>Visualize</h3>";
				
				$TabContent .= "<div class='sdgreybox'>";
				$TabContent .= "<table class='form'>";
				$TabContent .= "<tr>";
				$TabContent .= "<th>Format</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot1Format' onchange='dot1.get();'>";
					$TabContent .= "<option>image</option>";
					$TabContent .= "<option>dot script</option>";
				$TabContent .= "</select>";
				$TabContent .= "</td>";

				$TabContent .= "<th>Style</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot1Style' onchange='dot1.get();'>";
					$TabContent .= "<option value='1'>graph</option>";
					$TabContent .= "<option value='2'>tables</option>";
				$TabContent .= "</select>";
				$TabContent .= "</td>";
				
				
				
				$TabContent .= "<th>Layout</th>";
				$TabContent .= "<td>";
				$TabContent .= "<select id='dot1Layout' onchange='dot1.get();'>";
					$TabContent .= "<option value='dot' selected='selected'>rows</option>";
					$TabContent .= "<option value='circo'>stars</option>";
					$TabContent .= "<option value='twopi'>circles</option>";
					$TabContent .= "<option value='neato'>neat</option>";
					$TabContent .= "</select>";
				$TabContent .= "</td>";
				
				$TabContent .= "</tr>";
				
				
				
				$TabContent .= "</table>";				
				
				$TabContent .= "</div>";
				
				
				$TabContent .= "<div id='viz1'></div>";
				
				$InitScript .= "	viz1 = new clsPdViz(null, 'viz1', 'viz1loading'); \n";
				$InitScript .= "	dot1 = new clsShocBoxDot('dot1Style', viz1); \n";
				$InitScript .= "	dot1.uriBox = '$uriBox'; \n";
				$InitScript .= "	dot1.idLayout = 'dot1Layout'; \n";
				$InitScript .= "	dot1.idFormat = 'dot1Format'; \n";				
				$InitScript .= "	dot1.get(); \n";
				
				$TabContent .= "</div>";
						
				$Tabs .= "<li><a href='#subjects'>Subjects";
				$TabContent .= "<div class='tabContent hide' id='subjects'>";
				$count = 0;
				$TabContent .= ListSubjects();
				$TabContent .= "</div>";
				$Tabs .= "(<span id='SubjectsCount'></span>)</a></li>";
				
				
				$Tabs .= "<li><a href='#addsubject'>Add a Subject";
				$TabContent .= "<div class='tabContent hide' id='addsubject'>";
				
				$ParamSid = $System->Session->ParamSid;	
				
				foreach ($objBox->Objects as $optObject){					
					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='form.php?$ParamSid&uribox=$uriBox&objectid=".$optObject->Id."&mode=new'>&bull; add a new ".$optObject->Label."</a></li>";
					$TabContent .= "</ul></div>";					
				}
				
				$TabContent .= "</div>";
				
				$Tabs .= "</a></li>";
		
				
				$Tabs .= "<li><a href='#links'>Links";
				$TabContent .= "<div class='tabContent hide' id='links'>";
				$count = 0;
				$TabContent .= ListLinks($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
								
				$Tabs .= "<li><a href='#metalinks'>Metadata Links";
				$TabContent .= "<div class='tabContent hide' id='metalinks'>";
				$TabContent .= ListBoxLinks($cnt);				
				$TabContent .= "</div>";
				$Tabs .= "($cnt)</a></li>";
				
				if ($objBox->canControl){				
					if (count($objBox->Objects) > 0){
						$Tabs .= "<li><a href='#addmetalink'>Add a Metadata Link";
						$TabContent .= "<div class='tabContent hide' id='addmetalink'>";
						$TabContent .= AddMetaLink();				
						$TabContent .= "</div>";
						$Tabs .= "</a></li>";
					}
				}
				
				
				
				$Tabs .= "<li><a href='#classes'>Classes";
				$TabContent .= "<div class='tabContent hide' id='classes'>";
				$count = 0;

				if ($objBox->canControl){
										
					$TabContent .= "<form method='post' action='doBoxObject.php?$ParamSid&uribox=$uriBox&mode=add'>";
	
					$TabContent .= "<select name='objectid'>";
					$TabContent .= "<option value = ''>{choose a class from the activity archetype to add}</option>";
					
					if (is_object($objBox->Group)){
						foreach ($objBox->Group->Activity->Template->Objects as $optObject){
							$TabContent .= "<option";
							$TabContent .= " value='".$optObject->Id."'";
							$TabContent .= ">".$optObject->Label."</option>";
						}
					}
					
					$TabContent .= "</select>";

					$TabContent .= "<div><input type='submit' value='Add a Class to the Box'></div>";
					
					$TabContent .= '</form>';
				}
				
				
				$TabContent .= ListObjects($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";
				
				
				$Tabs .= "<li><a href='#group'>Group";
				$TabContent .= "<div class='tabContent hide' id='group'>";
				$count = 0;
				if (!is_null($objGroup)){
					$TabContent .= pnlGroup($objGroup);
				}
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
				
								
				$Tabs .= "<li><a href='#documents'>Documents";
				$TabContent .= "<div class='tabContent hide' id='documents'>";
				$count = 0;				
				$TabContent .= ListDocuments($count);
				$TabContent .= "</div>";
				$Tabs .= "($count)</a></li>";

				$Tabs .= "<li><a href='#id'>Id";
				$TabContent .= "<div class='tabContent hide' id='id'>";
				$TabContent .= pnlBoxId( $objBox );				
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
				
				
				break;
				
				
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doBox.php?$ParamSid">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='urigroup' value='$uriGroup'/>";
				
				if ($Mode == 'edit'){
					$PanelB .= "<input type='hidden' name='uribox' value='$uriBox'/>";					
				}
				
				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'uri';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $uriBox;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Title';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="title" size="100" maxlength="100" value="'.$Title.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

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
				
				

				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create a New Box">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update this Box">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlBox( $objBox );
				$PanelB .= "<div class='hmenu'><ul>";
				$PanelB .= "<li><a href='doBox.php?uribox=$uriBox&mode=delete'>&bull; confirm delete?</a></li>";
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
	

function ListObjects(&$count){

	global $System;
	global $objBox;
	
	$uriBox = $objBox->Uri;

	$count = count($objBox->Objects);

	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Name</th><th/>";
	$Content .="</thead>";
	
	$Content .= "<tbody>";

	foreach($objBox->Objects as $objObject){
		$ObjectId = $objObject->Id;
		$Content .= "<tr>";
		$Content .= "<td>".$objObject->Label."</td>";
		$Content .= "<td>";
		if ($objBox->canControl){
			$Content .= "<div class='hmenu'><ul>";
			$Content .= "<li><a href='doBoxObject.php?uribox=$uriBox&objectid=$ObjectId&mode=remove'>&bull; remove</a></li>";
			$Content .= "</ul></div>";			
		}
		$Content .= "</td>";
		$Content .= "</tr>";		
	}

	
	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	
	

function ListSubjects(){

	global $System;
	global $objBox;
	global $ParamSid;

	global $objActivity;

	global $jsScript;
	global $InitScript;

	$Content = '';
	
	
	if (is_null($objBox)){
		return $Content;
	}

	$uriBox = $objBox->Uri;
	
	$Content .= "<h3>Subjects in the Box</h3>";
	
	$Content .= "<div class='sdgreybox'>";	
	$Content .= "<table>";
	$Content .= "<tr>";
	$Content .= "<th>Class</th>";
	$Content .= "<td>";
	$Content .= "<select name='class' id='fldidObjectId' onchange='List1.FiltersSet = false; List1.get();'>";
			
	foreach ($objBox->Objects as $objObject){
		$Content .= "<option value='".$objObject->Id."'>".$objObject->Label."</option>";
	}
	
	$Content .= "</select>";
	$Content .= "</td>";
	$Content .= "</tr>";
	$Content .= "</table>";
	

	$Content .= "</div>";	
	
	$Content .= "<div id='BoxSubjects'></div>";
	
	$jsScript .= "<script> \n";
	$jsScript .= "var List1;";
	$jsScript .= "</script> \n";
	
	$InitScript .= "	List1 = new clsShocListSubject('BoxSubjects'); \n";
	$InitScript .= "    List1.fldidObjectId ='fldidObjectId'; \n";
	$InitScript .= "    List1.fldidCount ='SubjectsCount'; \n";
	$InitScript .= "    List1.uriBox = '$uriBox'; \n";
	$InitScript .= "    List1.get(); \n";
	
	
	return $Content;
	
	
}
	


function ListLinks(&$count){

	global $System;
	global $objBox;

	$count = count($objBox->Links);
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>From</th><th>Relationship</th><th>To</th>";
	$Content .= "<tbody>";

	foreach($objBox->Links as $objLink){
		$Content .= "<tr>";
		$Content .= "<td>".$objLink->FromSubject->Title."</td>";		
		$Content .= "<td><a href='link.php?urilink=".$objLink->Uri."'>".$objLink->Relationship->Label."</a></td>";
		$Content .= "<td>".$objLink->ToSubject->Title."</td>";
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	



function ListDocuments(&$count){

	global $System;
	global $Models;
	global $objBox;

	$count = count($objBox->Documents);
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Class</th><th>About</th>";
	$Content .= "<tbody>";

	foreach($objBox->Documents as $objDocument){
		$Content .= "<tr>";
		$Content .= "<td><a href='document.php?uridocument=".$objDocument->Uri."'>".$objDocument->Id."</a></td>";
		
		$Content .= "<td>";
		if (is_object($objDocument->Object)){		
			$Content .= $objDocument->Object->Label;
		}
		$Content .= "</td>";
		
		$Content .= "<td>";
		switch ($objDocument->Type){
			case 'subject':
				if (is_object($objDocument->CurrentRevision)){
					$Content .= $objDocument->CurrentRevision->Title;
				}
				break;
			case 'link':
				
				
				
				
				break;
		}
		$Content .= "</td>";
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	


function ListBoxLinks(&$count){

	global $System;
	global $Models;
	global $objBox;
	
	global $ParamSid;

	$count = count($objBox->BoxLinks);
	
	
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Id</th><th>Class</th><th>Relationship</th><th>Subject</th>";
	$Content .= "<tbody>";

	foreach($objBox->BoxLinks as $objBoxLink){
		$Content .= "<tr>";
		$Content .= "<td><a href='boxlink.php?$ParamSid&uriboxlink=".$objBoxLink->Uri."'>".$objBoxLink->Id."</a></td>";
		
		$Content .= "<td>";
		if (is_object($objBoxLink->Object)){		
			$Content .= $objBoxLink->Object->Label;
		}
		$Content .= "</td>";
		
		$Content .= "<td>";
		if (is_object($objBoxLink->Relationship)){
			switch ($objBoxLink->Inverse){
				case false:
					$Content .= $objBoxLink->Relationship->Label;
					break;
				default:
					$Content .= $objBoxLink->Relationship->InverseLabel;
					break;
			}
		}
		$Content .= "</td>";

		$Content .= "<td>";		
		if (is_object($objBoxLink->Subject)){		
			$Content .= $objBoxLink->Subject->Title;
		}
		$Content .= "</td>";
		
		
		$Content .= "</tr>";		
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}	





function AddMetaLink(){

	global $System;
	global $objBox;
	global $ParamSid;

	global $jsScript;
	global $InitScript;

	$Content = '';
	
	$objActivity = $objBox->Group->Activity;
	$uriActivity = $objActivity->Uri;	
	
	if (is_null($objActivity)){
		return $Content;
	}

	$Content .= "<h3>Add a Link that will apply to all Subjects in the Box</h3>";
	
	$Content .= "<div class='sdgreybox'>";	
	$Content .= "<table>";
	$Content .= "<tr>";
	$Content .= "<th>Relationship</th>";
	$Content .= "<td>";
	$Content .= "<select name='archrelid' id='archrelid' onchange='Sel1.reset()'>";
	$Content .= '<option/>';			
	foreach ($objBox->Objects as $objObject){
		foreach ($objActivity->Template->Relationships as $objTemplateRelationship){
			if ($objTemplateRelationship->FromObject === $objObject){
				$Content .= "<option value='".$objTemplateRelationship->Id."'>".$objObject->Label.' '.$objTemplateRelationship->Label."</option>";
			}
		}
	}
	
	$Content .= "</select>";
	$Content .= "</td>";
	$Content .= "</tr>";
	$Content .= "</table>";
	

	$Content .= "</div>";	
	
	$Content .= "<div id='SelectLinkSubject'></div>";
	
	$jsScript .= "<script> \n";
	$jsScript .= "var Sel1;";
	$jsScript .= "</script> \n";
	
//	$InitScript .= "	Sel1 = new clsShocLinkObject('archrelid', '', 'SelectLinkSubject','boxlink');";

	$InitScript .= "	Sel1 = new clsShocListSubject( 'SelectLinkSubject','boxlink', 'filterlink');";
	$InitScript .= "	Sel1.fldidArchRelId = 'archrelid';";
	$InitScript .= "	Sel1.uriActivity = '$uriActivity';";
	$InitScript .= "	Sel1.reset();";
	
	
	return $Content;
}


?>