<?php

	require_once("path.php");

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("class/clsThread.php");

	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");

	require_once("class/clsShocList.php");
	
	
	require_once("panel/pnlThread.php");
	

	require_once("form/frmSubjectFilter.php");
	
	define('PAGE_NAME', 'subjects');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();	
	
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/ajax.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='java/shoc.js'></script> \n";	
	$jsScript .= "<script> \n";
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";	

	$Page = new clsPage();
	
	$ParamSid = $System->Session->ParamSid;
	$InitScript .= "	gShoc.SessionId = '".$System->Session->Sid."'; \n";
		
	$objThread = new clsThread();
	

	$ExportFormat = null;
	if (isset($_REQUEST['exportformat'])){
		if (trim($_REQUEST['exportformat']) != ''){		
			$ExportFormat = $_REQUEST['exportformat'];
		}
	}

	
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

	$ModelId = null;
	if (isset($_REQUEST['modelid'])){
		if (trim($_REQUEST['modelid']) != ''){		
			$ModelId = $_REQUEST['modelid'];
		}
	}
	
	
	$ClassId = null;
	$objClass = null;
	if (isset($_REQUEST['classid'])){
		if (trim($_REQUEST['classid']) != ''){		
			$ClassId = $_REQUEST['classid'];
		}
	}

	$ObjectId = null;
	$objObject = null;
	if (isset($_REQUEST['objectid'])){
		if (trim($_REQUEST['objectid']) != ''){		
			$ObjectId = $_REQUEST['objectid'];
		}
	}
	
	$uriActivity = null;
	$objActivity = null;
	if (!is_null($objThread->uriActivity)){
		$uriActivity = $objThread->uriActivity;
	}
	if (isset($_REQUEST['uriactivity'])){
		$uriActivity = $_REQUEST['uriactivity'];
	}
	if (!is_null($uriActivity)){
		$objActivity = $Shoc->getActivity($uriActivity);
		$PanelC = pnlActivityMenu($objActivity);
	}

	
	
	try {

		if (!LoggedOn()){
			throw new  exception("Please log on");
		}
		if (!is_object($objActivity)){
			throw new  exception("No Activity Specified");			
		}
		if (!$objActivity->canView){
			throw new  exception("Invalid Mode");			
		}
		
		
		if (!is_null($ClassId)){
			if (!isset($Models->Classes[$ClassId])){
				throw new exception("Unkown Class");
			}
			$objClass = $Models->Classes[$ClassId];
		}

		if (!is_null($ObjectId)){		
			if (!isset($Archetypes->Objects[$ObjectId])){
				throw new exception("Unkown Object");
			}
			$objObject = $Archetypes->Objects[$ObjectId];
			$objClass = $objObject->Class;
			$ClassId = $objClass->Id;
		}
		
	
		
		$Page->Title = "Subjects";
		if (!is_null($objClass)){
			$Page->Title = $objClass->Heading;
		}
		$PanelB .= "<h1>".$Page->Title."</h1>";

		
		
		if (is_null($objClass)){
			$PanelB .= frmFilter();
		}

		if (!is_null($ClassId)){			
			$Tabs .= "<li><a href='#list' id='tab0'>List";
			$TabContent .= "<div class='tabContent hide' id='list'>";
			$TabContent .= ListSubjects();
			$TabContent .= "</div>";
			$Tabs .= "(<span id='SubjectsCount'></span>)</a></li>";
		}

		if (!is_null($objActivity)){
			$optBoxes = new clsBoxes();
			$optBoxes->uriActivity = $objActivity->Uri;
			if ($System->LoggedOn){
				$optBoxes->MemberId = $System->User->Id;
			}
			$optBoxes->MemberRightsId = 100;
			$optBoxes->ObjectId = $objObject->Id;

			if (count($optBoxes->Items) > 0){
		
				$Tabs .= "<li><a href='#add' id='tab2'>Add";
				$TabContent .= "<div class='tabContent hide' id='add'>";
				
				$ParamSid = $System->Session->ParamSid;	
				
				$TabContent .= "<div class='sdgreybox'>";
				$TabContent .= "<table>";
				$TabContent .= "<tr><th>Add to Box</th>";
				$BoxOnChange = 'ShowFormIfBoxSelected("selbox","form1","addsubmit","uribox");';
				$InitScript .= "
				$BoxOnChange;
				";
				$TabContent .= "<td><select id='selbox'  onChange='$BoxOnChange'>";
				
				$TabContent .= "<option/>";
				foreach ($optBoxes->Items as $optBox){
					$TabContent .= "<option value='".$optBox->Uri."'>".$optBox->Title."</option>";
				}
				$TabContent .= "</select>";
				$TabContent .= "</td></tr></table>";
				$TabContent .= "</div>";

				$TabContent .= "<div id='form1'></div>";
				

				$jsScript .= "   var Form1; \n";
				
				$jsScript .= "
	function ShowFormIfBoxSelected(fldidBox ,fldidDiv, fldidSubmit, fldidFormFieldBox ){
		
		var tagBox = document.getElementById(fldidBox);
		var tagDiv = document.getElementById(fldidDiv);
		var tagSubmit = document.getElementById(fldidSubmit);
		var tagFormFieldBox = document.getElementById(fldidFormFieldBox);
		
		var uriBox = getElementValue(tagBox);
		
		if (uriBox){
			tagDiv.style.display = 'block';
			tagSubmit.style.display = 'block';
		}
		else
		{
        	tagDiv.style.display = 'none';
        	tagSubmit.style.display = 'none';
		}

		tagFormFieldBox.value = uriBox;
		
	}
";
				
				$TabContent .= "<form method='post' action='doForm.php?$ParamSid'>";

				$TabContent .= "<input type='hidden' name='mode' value='new'/>";
				$TabContent .= "<input type='hidden' id='uribox' name='uribox'/>";			
				
				
				$objForm = new clsForm();
				$objForm->Object = $objObject;

				$TabContent .= "<input type='hidden' name='objectid' value='".$objObject->Id."'/>";
				
				
				$InitScript .= "Form1 = new clsShocForm('form1', 'xmlform1'); \n";
				$TabContent .= "<input type='hidden' name='xmlform' id='xmlform1' value='".encode($objForm->xml)."'/>";
				$TabContent .= "<input onClick='Form1.makeXml();' id='addsubmit' type='submit' value='Add'/>";
				$TabContent .= '</form>';
						
				$TabContent .= "</div>";
				
				
				$Tabs .= "</a></li>";
			}		
		}
		


		
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		$PanelB = pnlThread().$PanelB;
				
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;

	 	$jsScript .= "</script> \n";
	 	
	 	
	 	$InitScript .= "} \n";
	 	$InitScript .= "</script> \n";
	 	
	 	$Page->Script .= $jsScript;
	 	$Page->Script .= $InitScript;
 	
	 	
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();


function ListSubjects(){

	global $System;
	global $Models;
	global $uriActivity;
	global $ObjectId;
	
	global $jsScript;
	global $InitScript;	
	
	$Content = '';
	
	if (is_null($ObjectId)){
		return '';
	}
	
	
	
	$Content .= "<div id='ListSubjects'></div>";
	
	$jsScript .= "    var List1;";
	
	$InitScript .= "	List1 = new clsShocListSubject('ListSubjects'); \n";
	$InitScript .= "    List1.fldidCount ='SubjectsCount'; \n";
	$InitScript .= "    List1.uriActivity = '$uriActivity'; \n";
	$InitScript .= "    List1.ObjectId = '$ObjectId'; \n";
	
	$InitScript .= "    List1.get(); \n";
	
	
	
	return $Content;
	
}	
	


function frmFilter(){

global $Models;
global $ModelId;
global $ClassId;

global $System;
	
	$Content = '';

	$ParamSid = $System->Session->ParamSid;	
	$Content .= "<form method='post' action='subjects.php?$ParamSid'>";
	
	$Content .= "<table>";

	
	$Content .= "<tr><th>Model<th><td>";
	$Content .= "<select name='modelid' onchange=".chr(34)."this.form.submit()".chr(34).">";
	$Content .= "<option/>";
	
	$objModel = null;
	foreach ($Models->Items as $optModel){
		$Content .= "<option value='".$optModel->Id."'";		
		if ($optModel->Id == $ModelId){
			$Content .= " selected='selected' ";
			$objModel = $optModel;			
		}
		$Content .= ">".$optModel->Name."</option>";
	}

	$Content .= "</select>";
	
	$Content .= "</th></tr>";
	
	
	$Content .= "<tr><th>Class<th><td>";
	$Content .= "<select name='classid' onchange=".chr(34)."this.form.submit()".chr(34).">";
	$Content .= "<option/>";

	if (!is_null($objModel)){
		foreach ($objModel->Classes as $objClass){
			$Content .= "<option value='".$objClass->Id."'";		
			if ($objClass->Id == $ClassId){
				$Content .= " selected='selected' ";
			}
			$Content .= ">".$objClass->Name."</option>";
		}
	}

	$Content .= "</select>";
	
	$Content .= "</th></tr>";
	
	
	$Content .= "</table>";
	
	$Content .= "<input type='submit' value='Search'>";
	
	$Content .= "</form>";
	
	return $Content;
}

	
?>