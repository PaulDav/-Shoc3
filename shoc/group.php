<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsImage.php");	
	require_once("class/clsThread.php");
	
	
	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
	
	require_once("panel/pnlGroup.php");
	require_once("panel/pnlActivity.php");

	require_once("panel/pnlThread.php");
	
	
	
	require_once('update/updateData.php');
	
	
	define('PAGE_NAME', 'group');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$objPage = new clsPage();	
	
	$objGroup = null;

	$Mode = 'view';
	$uriGroup = null;
	$uriActivity = null;
	$Title = '';
	$Description = '';
	
	
	$objActivity = null;
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	$objThread = new clsThread();
	
	
	try {
				
				
		if (isset($_REQUEST['urigroup'])){
			$uriGroup = $_REQUEST['urigroup'];
		}
		if (isset($_REQUEST['uriactivity'])){
			$uriActivity = $_REQUEST['uriactivity'];
		}

		switch ($Mode){
			case 'new':
				if (is_null($uriActivity)) {
					throw new exception("Activity not specified");
				}
				$objActivity = $Shoc->getActivity($uriActivity);

				break;
			default:
				if (is_null($uriGroup)) {
					throw new exception("Group not specified");
				}
				$objGroup = $Shoc->getGroup($uriGroup);
				if ($objGroup->canView === false){
					throw new exception("You cannot view this Group");
				}
				$Title = $objGroup->Title;
				$Description = $objGroup->Description;
				$objActivity = $objGroup->Activity;
				$uriActivity = $objActivity->Uri;
				break;
		}

		$objPage->Title = 'Group';
		if (is_object($objGroup)){
			$objPage->Title .= ":".$objGroup->Title;
		}
		$PanelB .= "<h1>".$objPage->Title."</h1>";
		
		
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objGroup->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if (!$System->LoggedOn){
					throw new Exception("Please log on");
				}
				break;
			case 'edit':
				if ($objGroup->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objGroup->canControl){
					$ModeOk = true;
				}
				break;
		}
		

		$objThread->uriGroup = $uriGroup;
		if (is_null($objThread->uriActivity)){
			if (is_object($objGroup)){
				$objThread->uriActivity = $objGroup->Activity->Uri;	
			}
		}
		$objThread->uriBox = null;
		$objThread->uriSubject = null;

		
		switch ($Mode){
			case 'view':
				$PanelB .= pnlGroup( $objGroup );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objGroup->canControl === true){
					$PanelB .= "<li><a href='group.php?urigroup=$uriGroup&mode=edit'>&bull; edit</a></li> ";
				}
				$PanelB .= "</ul></div>";				


				$Tabs .= "<li><a href='#activity'>Activity</a></li>";
				$TabContent .= "<div class='tabContent hide' id='activity'>";
				$TabContent .= "<h3>Activity</h3>";
				$TabContent .= pnlActivity($objGroup->Activity);
				$TabContent .= "</div>";

				
				$Tabs .= "<li><a href='#boxes'>Boxes";
				$num = 0;
				
				$TabContent .= "<div class='tabContent hide' id='boxes'>";

				$TabContent .= "<div><h3>Boxes</h3></div>";
				
				if ($objGroup->canControl === true){
					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='box.php?urigroup=$uriGroup&mode=new'>&bull; add a new box</a></li>";
					$TabContent .= "</ul></div>";						
				}
				
				$objBoxes = new clsBoxes();
				$objBoxes->uriGroup = $uriGroup;
				$objBoxes->getItems();
									
				if (count($objBoxes->Items) > 0){
					$TabContent .= ListBoxes($num);
				}

				$TabContent .= "</div>";
			    				
	    		$Tabs .= "($num)</a></li>";
				
				
				
				if ($objGroup->canControl === true){
				    $Tabs .= "<li><a href='#picture'>Picture";
				    $num = 0;
				    
					$TabContent .= "<div class='tabContent hide' id='picture'>";

										
					if (isset($_FILES['grpImage'])){
						
						$objImage = new clsImage();
						$objImage->Upload($_FILES['grpImage']);
						dataGroupImageUpdate($uriGroup, $objImage->Id);
		
					  	$objGroup = $Shoc->getGroup($uriGroup);
					  	
					  	$System->Session->Message = "group picture set ok";

					}

					$TabContent .= "<form enctype='multipart/form-data' action='group.php?urigroup=$uriGroup#picture' method='post'>";
					$TabContent .= '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
					$TabContent .= 'Group Picture : <input name="grpImage" type="file">';
					$TabContent .= '<input type="submit" value="Send Picture">';
					$TabContent .= '</form>';
				
					if (!$objGroup->Picture == ""){
	 					$TabContent .= '<img src="image.php?Id='.$objGroup->Picture.'" /><br/>';
					}
										
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}


				$Tabs .= "<li><a href='#members'>Members";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='members'>";

				$TabContent .= "<div><h3>Members</h3></div>";
				
				if ($objGroup->canControl === true){
					$TabContent .= "<a href='groupmember.php?mode=new&urigroup=$uriGroup'>&bull; add a member to the group</a><br/>";
				}
									
				if (count($objGroup->Members) > 0){
					
					$TabContent .= "<table><thead><tr><th>User</th><th>Rights</th></tr></thead>";
					$TabContent .= "<tbody>";
					foreach ($objGroup->Members as $objMember){
						
						++$num;

						$MemberName = $objMember->User->Name;										
						if (empty($MemberName)){
							$MemberName = $objMember->User->Email;
						}											
						
						
						$TabContent .= "<tr>";
						$TabContent .= "<td>";
							
						if (!is_null($objMember->User->PictureOf)) {
							$TabContent .= "<img height = '30' src='image.php?Id=".$objMember->User->PictureOf."' alt='".$MemberName."' /><br/>";
						}
						if ($objGroup->canControl){
							$TabContent .= "<a href='groupmember.php?urigroup=$uriGroup&memberid=".$objMember->User->Id."'>".$MemberName."</a>";
						}
						else
						{
							$TabContent .= $MemberName;
						}
						$TabContent .= "</td>";
							
						$TabContent .= "<td>".$objMember->Rights->Label."</td>";
							
						$TabContent .= "</tr>";

					}
					$TabContent .= "</tbody></table>";
					
				}

				$TabContent .= "</div>";
			    				
	    		$Tabs .= "($num)</a></li>";

				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doGroup.php">';

				$onErrorURL = $_SERVER['SCRIPT_NAME'];
				$QueryString = $_SERVER['QUERY_STRING'];
				$onErrorURL .= '?'.$QueryString;
				$PanelB .= "<input type='hidden' name='onErrorURL' value='$onErrorURL'/>";

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($uriGroup)){
					$PanelB .= "<input type='hidden' name='urigroup' value='$uriGroup'/>";
				}
				if (!is_null($uriActivity)){
					$PanelB .= "<input type='hidden' name='uriactivity' value='$uriActivity'/>";
				}
				
				$PanelB .= '<table class="sdbluebox">';
								
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
/*
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Publish?';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					
					$PanelB .= "<select name='publish'>";
					$PanelB .= "<option>Yes</option>";
					$PanelB .= "<option";
					if (isset($objGroup)){
						if ($objGroup->Publish === false){
							$PanelB .= " selected='true' ";
						}
					}
					else
					{
						$PanelB .= " selected='true' ";
					}
					$PanelB .= ">No</option>";
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
*/				

				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Group">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Group">';
							break;
					}
		
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
			 	
				break;
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		$PanelB = pnlThread().$PanelB;
		
	 	$objPage->ContentPanelB = $PanelB;
	 	$objPage->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$objPage->ErrorMessage = $e->getMessage();
	}
	 	
	$objPage -> Display();

	
function ListBoxes(&$cnt){

	global $System;
	global $Models;
	global $objBoxes;
	
	$cnt = 0;
		
	$Content = '';
	$Content .= "<table class='list'>";

	$Content .="<thead><tr>";
	$Content .= "<th>Title</th><th>Description</th><th>Classes</th>";
	$Content .= "<tbody>";

	foreach($objBoxes->Items as $objBox){
		++$cnt;
		$Content .= "<tr>";
		$Content .= "<td><a href='box.php?uribox=".$objBox->Uri."'>".$objBox->Title."</a></td>";
		$Content .= "<td>".nl2br(truncate($objBox->Description))."</td>";
		$Content .= "<td>";
		foreach ($objBox->Objects as $objObject){
			$Content .= $objObject->Label.'<br/>';			
		}
		
		$Content .= "</td>";
		$Content .= "</tr>";
	}

	$Content .= "</tbody>";
	
	$Content .= "</table>";
	
	return $Content;
	
}		
	
	
?>