<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
	
	require_once("panel/pnlGroup.php");
	
	define('PAGE_NAME', 'groupmember');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$objPage = new clsPage();

	$Tabs = "";
	$TabContent = "";
	
	
	try {

		if (!$System->LoggedOn){
			throw new exception("Please log on to view a user in a group");
		}
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$Fail = false;
		$FormFields = getUserInput(PAGE_NAME);
				
		if (isset($_REQUEST['mode'])){
			if ($_REQUEST['mode'] == "fail"){
				$Fail = true;
				if (isset($FormFields['mode'])){
					$Mode = $FormFields['mode'];
				}
			}
		}		
		
		$objPage->Title = $Mode." member in group";
		$ContentPanelB = '';
		$ContentPanelC = '';
		
		$ContentPanelB .= "<h1>".$objPage->Title."</h1>";
		
		$uriGroup = null;;
		$MemberId = null;
		$RightsId = null;

		if (isset($_REQUEST['urigroup'])){
			$uriGroup = $_REQUEST['urigroup'];
		}
		if (isset($_REQUEST['memberid'])){
			$MemberId = $_REQUEST['memberid'];
		}		


		if (is_null($uriGroup)){
			throw new exception("Group not specified");
		}
		$objGroup = $Shoc->getGroup($uriGroup);
		
		switch ( $Mode ){
			case "new":
				break;
			default:
				if (is_null($MemberId)){
					throw new exception("Member not specified");
				}
				if (!isset($objGroup->Members[$MemberId])){
					throw new exception("User if not a Member");					
				}
				
				$objGroupMember = $objGroup->Members[$MemberId];
				break;
		}

		$ModeOk = false;	
		switch ($Mode){
			case 'new':
				if ($objGroup->canControl){
					$ModeOk = true;
				}							
				break;
			case 'view':
				if ($objGroup->canView){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objGroup->canControl){
					$ModeOk = true;
				}							
				break;
			case 'delete':
				if ($objGroup->canControl){
					$ModeOk = true;
				}							
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
		}
		
		
		switch ($Mode){
			case 'edit':
				$RightsId = $objGroupMember->Rights->Id;
				break;
		}

		$Tabs .= "<li><a href='#group'>Group</a></li>";				
	    $TabContent .= "<div class='tabContent' id='group'>";
	    $TabContent .= "<h3>Data Group</h3>";
		$TabContent .= pnlGroup($objGroup);
	    $TabContent .= "</div>";

		switch ($Mode){
			case 'view':
				
				$ContentPanelB .= pnlGroupMember( $objGroupMember );
				
				$ContentPanelB .= "<div class='hmenu'><ul>";
				if ($objGroup->canControl){
					$ContentPanelB .= "<li><a href='groupmember.php?urigroup=$uriGroup&memberid=$MemberId&mode=edit'>&bull; edit</a></li>";
					$ContentPanelB .= "<li><a href='groupmember.php?urigroup=$uriGroup&memberid=$MemberId&mode=delete'>&bull; remove</a></li>";
				}
				$ContentPanelB .= "</ul></div>";
				
				break;
				
			case 'new':
			case 'edit':
				$ContentPanelB .= '<form method="post" action="doGroupMember.php">';

				$ContentPanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				$ContentPanelB .= "<input type='hidden' name='urigroup' value='$uriGroup'/>";
				
				$ContentPanelB .= '<table class="sdbluebox">';
				

				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<th>';
					$ContentPanelB .= '<label for="Member">Member</label>';
					$ContentPanelB .= '</th>';
					$ContentPanelB .= '<td>';

					switch ($Mode){
						case 'new':
							$ContentPanelB .= "<select name='memberid' id='Member'>";
							$ContentPanelB .= "<option/>";					
							foreach ($objGroup->Activity->Members as $optMember){
								
								$MemberName = $optMember->User->Name;										
								if (empty($MemberName)){
									$MemberName = $optMember->User->Email;
								}											
								
								$ContentPanelB .= "<option value='".$optMember->User->Id."'";
								$ContentPanelB .= ">".$MemberName."</option>";
							}
							$ContentPanelB .= "</select>";
							
							break;
						default:
							$ContentPanelB .= $objGroupMember->User->Name;
							$ContentPanelB .= "<input type='hidden' name='memberid' value='$MemberId'/>";							
							break;
					}
							
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';
				
				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<th>';
					$ContentPanelB .= '<label for="Rights">Rights</label>';
					$ContentPanelB .= '</th>';
					$ContentPanelB .= '<td>';
					
					$ContentPanelB .= "<select name='Rights' id='Rights'>";
					$ContentPanelB .= "<option/>";					
					foreach ($System->Config->GroupMemberRights as $optRights){
						$ContentPanelB .= "<option value='".$optRights->Id."'";
						
						if (isset($objGroupMember)){
							if ($objGroupMember->Rights->Id == $optRights->Id){
								$ContentPanelB .= " selected='selected' ";							
							}
						}
						
						$ContentPanelB .= ">".$optRights->Label."</option>";
					}
					$ContentPanelB .= "</select>";
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';
					
					
				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<td></td>';
					$ContentPanelB .= '<td>';
					
					$ContentPanelB .= '<input type="submit" value="Update Member">';
		
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';
		
			 	$ContentPanelB .= '</table>';
				$ContentPanelB .= '</form>';
			 	
				
				break;
				
		}
		
		
		if (!empty($Tabs)){
			$ContentPanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$objPage->ContentPanelB = $ContentPanelB;
	 	$objPage->ContentPanelC = $ContentPanelC;
	 	
	}
	catch(Exception $e)  {
		$objPage->ErrorMessage = $e->getMessage();
	}
	 	
	$objPage -> Display();
		
?>