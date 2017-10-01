<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsPage.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
	
	require_once("panel/pnlActivity.php");
	
	define('PAGE_NAME', 'activitymember');

	session_start();
	
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	$objPage = new clsPage();

	
	try {

		if (!$System->LoggedOn){
			throw new exception("Please log on to view a member in an activity");
		}
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		
		if (isset($_REQUEST['mode'])){
			if ($_REQUEST['mode'] == "fail"){
				$Fail = true;
				if (isset($FormFields['mode'])){
					$Mode = $FormFields['mode'];
				}
			}
		}		
		
		$objPage->Title = $Mode." member in an activity";
		$ContentPanelB = '';
		$ContentPanelC = '';
		
		
		$Tabs = "";
		$TabContent = "";
		
		
		$ContentPanelB .= "<h1>".$objPage->Title."</h1>";
		
		$uriActivity = null;
		$MemberId = null;

		if (isset($_REQUEST['uriactivity'])){
			$uriActivity = $_REQUEST['uriactivity'];
		}

		if (isset($_REQUEST['memberid'])){
			$MemberId = $_REQUEST['memberid'];
		}

		
		if (is_null($uriActivity)){
			throw new exception("Activity not specified");					
		}
		$objActivity = $Shoc->getActivity($uriActivity);
		
		
		switch ( $Mode ){
			case "new":
				break;
			default:
				if (is_null($MemberId)) {
					throw new exception("Member not specified");
				}
				$objMember = new clsUser($MemberId);
				if (!isset($objActivity->Members[$MemberId])){
					throw new exception("Not a Member of the Activity");					
				}
				$objActivityMember = $objActivity->Members[$MemberId];
				break;
		}


		$ModeOk = false;	
		switch ($Mode){
			case 'new':
				if ($objActivity->canControl){
					$ModeOk = true;
				}							
				break;
			case 'view':
				if ($objActivity->canView){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objActivity->canControl){
					$ModeOk = true;
				}							
				break;
			case 'delete':
				if ($objActivity->canControl){
					$ModeOk = true;
				}							
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
		}

		
		$Tabs .= "<li><a href='#group'>Activity</a></li>";				

		$TabContent .= "<div class='tabContent' id='activity'>";
		$TabContent .= "<h3>Activity</h3>";
		$TabContent .= pnlActivity($objActivity);
		$TabContent .= "</div>";				
		
		
		switch ($Mode){
			case 'view':
				
				$ContentPanelB .= pnlActivityMember( $objActivityMember );
				if ($objActivity->canControl){
					
					$ContentPanelB .= "<div class='hmenu'><ul>";
					$ContentPanelB .= "<li><a href='activitymember.php?uriactivity=$uriActivity&memberid=$MemberId&mode=delete'>&bull; remove</a></li>";
					$ContentPanelB .= "</ul></div>";
					
				}
				
				break;
				
			case 'new':

				$ContentPanelB .= '<form method="post" action="doActivityMember.php">';

				$ReturnURL = "activity.php?uriactivity=$uriActivity";
		
				$ContentPanelB .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
				$ContentPanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				$ContentPanelB .= "<input type='hidden' name='uriactivity' value='$uriActivity'/>";
				
				$ContentPanelB .= '<table class="sdbluebox">';
				
				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<th>';
					$ContentPanelB .= '<label for="email">email address</label>';
					$ContentPanelB .= '</th>';
					$ContentPanelB .= '<td>';
					$ContentPanelB .= "<input type='text' name='email' id='email' size='100' maxlength='100'/>";
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';					

				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<th>';
					$ContentPanelB .= '<label for="Rights">Rights</label>';
					$ContentPanelB .= '</th>';
					$ContentPanelB .= '<td>';
					
					$ContentPanelB .= "<select name='rights' id='Rights'>";
					$ContentPanelB .= "<option/>";
					foreach ($System->Config->ActivityMemberRights as $optLevelId=>$optLevel){
						$ContentPanelB .= "<option value='$optLevelId'";
						$ContentPanelB .= ">$optLevel->Label</option>";
					}
					$ContentPanelB .= "</select>";
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';
					
					
				$ContentPanelB .= '<tr>';
					$ContentPanelB .= '<td></td>';
					$ContentPanelB .= '<td>';
					
					$ContentPanelB .= '<input type="submit" value="Invite User">';
		
					$ContentPanelB .= '</td>';
				$ContentPanelB .= '</tr>';
		
			 	$ContentPanelB .= '</table>';
				$ContentPanelB .= '</form>';
			 	
				
				break;
				
				
			case 'delete':
				$ContentPanelB .= pnlActivityMember( $objActivityMember );
				$ContentPanelB .= "<div class='hmenu'><ul>";
				$ContentPanelB .= "<li><a href='doActivityMember.php?uriactivity=$uriActivity&memberid=$MemberId&mode=delete'>&bull; confirm remove?</a></li>";
				$ContentPanelB .= "</ul></div>";
				
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