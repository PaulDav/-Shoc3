<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("class/clsAccount.php");
	require_once("class/clsThread.php");
	

	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	require_once("panel/pnlThread.php");
	require_once("panel/pnlMenus.php");
	
		
	require_once("function/utils.inc");
	
	require_once("panel/pnlActivity.php");
	
	define('PAGE_NAME', 'activity');
	session_start();
		
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$jsScript = '';
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/utils.js'></script> \n";
	$jsScript .= "<script type='text/javascript' src='../pdlib/libs/viz-js/viz.js'></script> \n";	
	$jsScript .= "<script type='text/javascript' src='../pdlib/java/ajax.js'></script>";
	$jsScript .= "<script type='text/javascript' src='java/shoc.js'></script> \n";	
	$jsScript .= "\n";
	
	$jsScript .= "
<script>
	var viz0;
	var dot1;
	var viz1;
</script>
";
	
	
	$InitScript = '';	
	$InitScript .= "<script>\n";
	$InitScript .= "function init(){ \n";		
	
	$ParamSid = $System->Session->ParamSid;
	$InitScript .= "	gShoc.SessionId = '".$System->Session->Sid."'; \n";
	
	
	$Page = new clsPage();
	$UserLevelId = 0;
	$objAccount = null;
	if ($System->LoggedOn){
		$objAccount = new clsAccount($System->User);
		$UserLevelId = $objAccount->UserLevel->Id;
	}
	
	
	$objThread = new clsThread();
	$objThread->Clear();
	
	
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

		$uriActivity = null;
		
		$Title = '';
		$Description = '';	
		$TemplateId = '';
		
		$objActivity = null;
		
		if (isset($_REQUEST['uriactivity'])){
			$uriActivity = $_REQUEST['uriactivity'];
			$objActivity = $Shoc->getActivity($uriActivity);
			$Title = $objActivity->Title;
			$Description = $objActivity->Description;
			if (isset($objActivity->Template->Id)){
				$TemplateId = $objActivity->Template->Id;
			}
		}
		
		$objThread->uriActivity = $uriActivity;

		$Page->Title = 'Activity';
		if (is_object($objActivity)){
			$Page->Title .= ":".$objActivity->Title;
		}
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		
		switch ($Mode){
			case 'new':
				break;
			case 'view':
			case 'edit':
			case 'delete':
				if (is_null($objActivity)){
					throw new exception('Activity not specified');
				}
				break;
		}
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objActivity->canView){
					$ModeOk = true;
				}
				$PanelC = pnlActivityMenu($objActivity);
				break;				
			case 'new':
				if ($System->LoggedOn){
					if (is_object($objAccount)){
						if ($objAccount->UserLevel->Id >= 100){
							$ModeOk = true;
						}
					}
				}
				break;
			case 'edit':
			case 'delete':
				if ($objActivity->canControl){
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
				
				$PanelB .= pnlActivity( $objActivity );
				
				$PanelB .= "<div class='hmenu'><ul>";				
				if ($objActivity->canEdit ){
					$PanelB .= "<li><a href='Activity.php?uriactivity=$uriActivity&mode=edit'>&bull; edit</a></li>";
				}
				if ($objActivity->canControl ){				
					$PanelB .= "<li><a href='Activity.php?uriactivity=$uriActivity&mode=delete'>&bull; delete</a></li>";
				}

				$PanelB .= "</ul></div>";
				
				
				$Tabs .= "<li><a href='#template' id='viz0loading'>Template</a></li>";
				
				$TabContent .= "<div class='tabContent hide' id='template'>";

				$TabContent .= "<h3>Activity Template</h3>";
				
				$TabContent .= "<div  class='sdgreybox'>";
				$TabContent .= '<table>';
								
				if (is_object($objActivity->Template)){
					$TabContent .= "<tr><th>Template</th><td>".$objActivity->Template->Name."</td></tr>";
				}
				
				$TabContent .= '</table>';
				$TabContent .= "</div>";
				
				
				$TabContent .= "<h3>Visualize</h3>";

				$TabContent .= "<div>";
				$TabContent .= "Format";
				$vizOnChange = "	viz0.show(this.options[this.selectedIndex].value)";
				$TabContent .= "<select onchange='$vizOnChange'>";
					$TabContent .= "<option>image</option>";
					$TabContent .= "<option>dot script</option>";
				$TabContent .= "</select>";
				$TabContent .= "</div>";
				
				$TabContent .= "<div id='viz0'></div>";
				
				$InitScript .= "	viz0 = new clsPdViz(".json_encode($objActivity->Template->dot).", 'viz0', 'viz0loading'); \n";
				$InitScript .= "    viz0.show(); \n";
				
				$TabContent .= "<h3>Data Classes</h3>";
				$TabContent .= "<div>";
				
				
//					$ParamSid = $System->Session->ParamSid;
	
				$TabContent .= "<table class='list'>";
				$TabContent .= '<thead>';
				$TabContent .= '<tr>';
				$TabContent .= "<th>Class</th><th>Description</th>";
				$TabContent .= '</tr>';		
				$TabContent .= '</thead>';

				foreach ($objActivity->Template->Objects as $objObject){
					if ($objObject->Start){
						$TabContent .= "<tr>";
						$TabContent .= "<td><a href='subjects.php?$ParamSid&objectid=".$objObject->Id."'>".$objObject->Class->Heading."</a></td>";
						$TabContent .= "<td>";
						
						if (!empty($objObject->Definition)){
							$TabContent .= nl2br($objObject->Definition);
						}
						elseif (!empty($objObject->Class->Definition)){
							$TabContent .= nl2br($objObject->Class->Definition);
						}
						
						$TabContent .= "</td>";
						$TabContent .= "</tr>";
					}
				}
				$TabContent .= "</table>";
				
				$TabContent .= "</div>";
				
				
				
				$TabContent .= "</div>";
								
//====				
				
				$Tabs .= "<li><a href='#visualize' id='viz1loading'>Visualize</a></li>";
				
				
				$TabContent .= "<div class='tabContent hide' id='visualize'>";
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
				$InitScript .= "	dot1 = new clsShocViewDot('dot1Style', viz1); \n";
				$InitScript .= "	dot1.uriActivity = '$uriActivity'; \n";
				$InitScript .= "	dot1.idLayout = 'dot1Layout'; \n";
				$InitScript .= "	dot1.idFormat = 'dot1Format'; \n";
				
				$InitScript .= "	dot1.get(); \n";
				
				$TabContent .= "</div>";
				

				$Tabs .= "<li><a href='#boxes'>My Boxes";
			    $num = 0;
				    
				$TabContent .= "<div class='tabContent hide' id='boxes'>";

				$TabContent .= "<div><h3>My Boxes</h3></div>";
				
				$Boxes = new clsBoxes();

				$Boxes->uriActivity = $uriActivity;
				if ($System->LoggedOn){
					$Boxes->MemberId = $System->User->Id;			
				}
		
				$Boxes->getItems();
	
				$TabContent .= "<table class='list'>";
			
				$TabContent .="<thead><tr>";
				$TabContent .= "<th>Title</th><th>Description</th><th>Classes</th><th>Group</th>";
				
				if ($System->LoggedOn){					
					$TabContent .= "<th>My Rights</th>";
				}
				
				
				$TabContent .="</tr></thead>";
				
				$TabContent .= "<tbody>";
			
				$arrBoxes = array();
				foreach($Boxes->Items as $objBox){
					++$num;
					$TabContent .= "<tr>";
					$TabContent .= "<td><a href='box.php?uribox=".$objBox->Uri."'>".$objBox->Title."</a></td>";
					$TabContent .= "<td>".nl2br(truncate($objBox->Description))."</td>";
			
					$TabContent .= "<td>";
					foreach ($objBox->Objects as $objObject){
						$TabContent .= $objObject->Label.'<br/>';
					}		
					$TabContent .= "</td>";
					
					
					$TabContent .= "<td>";
					if (is_object($objBox->Group)){
						$TabContent .= $objBox->Group->Title;
					}
					$TabContent .= "</td>";
					
					$TabContent .= "<td>";
			
					if (is_object($objBox->Group)){		
						if (!is_null($objBox->Group->MyMembership)){
							$TabContent .= $objBox->Group->MyMembership->Rights->Label;
						}
					}
					$TabContent .= "</td>";
					
					
					$TabContent .= "</tr>";
				}
			
				$TabContent .= "</tbody>";
				
				$TabContent .= "</table>";
							
				
				
				
				$TabContent .= "</div>";   
				    
	    		$Tabs .= "($num)";							
			    $Tabs .= "</a></li>";		
				

			    
			    if ($objActivity->canControl){
				    $Tabs .= "<li><a href='#groups'>Groups";
					$TabContent .= "<div class='tabContent hide' id='groups'>";
					$TabContent .= "<div><h3>Groups</h3></div>";
					
					$TabContent .= "<div class='hmenu'><ul>";
					$TabContent .= "<li><a href='group.php?$ParamSid&mode=new&uriactivity=$uriActivity'>&bull; create a new group for this activity</a></li>";
					$TabContent .= "</ul></div>";
					
				}

				else
				{
				    $Tabs .= "<li><a href='#groups'>My Groups";
					$TabContent .= "<div class='tabContent hide' id='groups'>";	
					$TabContent .= "<div><h3>My Groups</h3></div>";
				}
				
					
			    $num = 0;
				
				if (count($objActivity->Groups) > 0){
					
					$TabContent .= "<table><thead><tr><th>Title</th><th>Description</th><th>My Rights</th></tr></thead>";
					$TabContent .= "<tbody>";
					foreach ($objActivity->Groups as $objGroup){

						$boolListGroup = true;
						if (!$objActivity->canControl){
							if (is_null($objGroup->MyMembership)){
								$boolListGroup = false;							
							}
						}

						if ($boolListGroup){
						
							++$num;
								
							$TabContent .= "<tr>";
							$TabContent .= "<td>";
							$GroupTitle = $objGroup->Title;
							
							$TabContent .= "<a href='group.php?$ParamSid&urigroup=".$objGroup->Uri."'>".$GroupTitle."</a>";
							
							$TabContent .= "</td>";
							
							$TabContent .= "<td>".truncate(nl2br($objGroup->Description))."</td>";
							
							$TabContent .= "<td>";
	
							if (!is_null($objGroup->MyMembership)){
								$TabContent .= $objGroup->MyMembership->Rights->Label;
							}
							
							$TabContent .= "</td>";
							
							
							$TabContent .= "</tr>";
						}
					}
					$TabContent .= "</tbody></table>";
					
				}
					
				$TabContent .= "</div>";
				    
				    
	    		$Tabs .= "($num)";							
			    $Tabs .= "</a></li>";    
			    
				
				
				$Tabs .= "<li><a href='#members'>Members";
			    $num = 0;
				    
				$TabContent .= "<div class='tabContent hide' id='members'>";

				$TabContent .= "<div><h3>Members</h3></div>";
										
				if (count($objActivity->Members) > 0){
					
					$TabContent .= "<table><thead><tr><th colspan='2'>User</th><th>Rights</th></tr></thead>";
					$TabContent .= "<tbody>";
					foreach ($objActivity->Members as $objMember){

						if ($objMember->Status->Id == 100){
							
							++$num;
							
							$TabContent .= "<tr>";
							$TabContent .= "<td>";
							
							if (!is_null($objMember->User->PictureOf)) {
								$TabContent .= "<img height = '30' src='image.php?Id=".$objMember->User->PictureOf."' alt='".$objMember->User->Name."' /><br/>";
							}
							$TabContent .= "</td>";
							$TabContent .= "<td>";
							$MemberName = $objMember->User->Name;										
							$TabContent .= $MemberName;
							$TabContent .= "</td>";
							
							$TabContent .= "<td>".$objMember->Rights->Label."</td>";
							
							$TabContent .= "</tr>";
						}
					}
					$TabContent .= "</tbody></table>";
					
				}
				$TabContent .= "</div>";   
				    
	    		$Tabs .= "($num)";							
			    $Tabs .= "</a></li>";		
				
			    
			    
				if ($objActivity->canControl === true){
				    $Tabs .= "<li><a href='#invites'>Invites";
				    $num = 0;
				    
					$TabContent .= "<div class='tabContent hide' id='invites'>";

						$TabContent .= "<div><h3>Invited Users</h3></div>";

						$TabContent .= "<div class='hmenu'><ul>";
						$TabContent .= "<li><a href='activitymember.php?mode=new&uriactivity=$uriActivity'>&bull; invite a user to join the activity</a></li>";
						$TabContent .= "</ul></div>";
						
						if (count($objActivity->Members) > 0){
							
							$TabContent .= "<table><thead><tr><th>User</th><th>Status</th><th>Rights</th></tr></thead>";
							$TabContent .= "<tbody>";
							foreach ($objActivity->Members as $objMember){
								if ($objMember->Status->Id == 2){
									
									++$num;
									
									$TabContent .= "<tr>";
									$TabContent .= "<td>";
									
									if (!is_null($objMember->User->PictureOf)) {
										$TabContent .= "<img height = '30' src='image.php?Id=".$objMember->User->PictureOf."' /><br/>";
									}
									$MemberName = $objMember->User->Name;										
									if (empty($MemberName)){
										$MemberName = $objMember->User->Email;
									}											
									$TabContent .= "<a href='activitymember.php?uriactivity=$uriActivity&memberid=".$objMember->User->Id."'>".$MemberName."</a>";
									
									$TabContent .= "</td>";
									
									$TabContent .= "<td>".$objMember->Status->Label."</td><td>".$objMember->Rights->Label."</td>";
									
									$TabContent .= "</tr>";
								}
							}
							$TabContent .= "</tbody></table>";
							
						}
					$TabContent .= "</div>";
				    
				    
		    		$Tabs .= "($num)";							
				    $Tabs .= "</a></li>";				    
				}

				

			    
			    
		
				break;
				
				
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doActivity.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= "<input type='hidden' name='uriactivity' value='$uriActivity'/>";
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Template';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='templateid'>";
					$PanelB .= "<option/>";
					foreach ($Archetypes->Items as $optArchetype){
						$PanelB .= "<option";
						$PanelB .= " value='".$optArchetype->Id."'";
						if ($optArchetype->Id == $TemplateId){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optArchetype->Name."</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				

				
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
							$PanelB .= '<input type="submit" value="Create a New Activity">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update this Activity">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlActivity( $objActivity );

				$PanelB .= "<div class='hmenu'><ul>";				
				$PanelB .= "<li><a href='doActivity.php?uriactivity=$uriActivity&mode=delete'>&bull; confirm delete?</a></li>";
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
	


?>