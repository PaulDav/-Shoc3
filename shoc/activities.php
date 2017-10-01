<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsAccount.php");	
	require_once("class/clsThread.php");	
	
	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
				
	define('PAGE_NAME', 'activities');
		
	session_start();

	$System = new clsSystem();
	$Page = new clsPage();
	
	$objAccount = null;
	if ($System->LoggedOn){
		$objAccount = new clsAccount($System->User);
	}
	

	$Tabs = "";
	$TabContent = "";
	
	$objThread = new clsThread();
	$objThread->Clear();
	
	try {
		
		$Page->Title = "Activities";

		$PanelB = '';
			
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		if ($System->LoggedOn){
			$cnt = 0;
			$Tabs .= "<li><a href='#my' id='tab0'>My Activities";
			$TabContent .= "<div class='tabContent hide' id='my'>";
			$TabContent .= ListActivities('my', $cnt);
			
			if ($objAccount->UserLevel->Id >= 100){
				$TabContent .= "<div class='hmenu'>";
				$TabContent .= "<ul>";
				$TabContent .= "<li><a href='activity.php?mode=new'>&bull; add</a></li>";
				$TabContent .= "</ul>";
				$TabContent .= "</div>";
			}
			
			$TabContent .= "</div>";
			$Tabs .= "($cnt)</a></li>";
		}

/*		
		$cnt = 0;
		$Tabs .= "<li><a href='#public' id='tab1'>Public Activities";
		$TabContent .= "<div class='tabContent hide' id='public'>";
//		$TabContent .= ListActivities('public', $cnt);
		$TabContent .= "</div>";
		$Tabs .= "($cnt)</a></li>";		
*/	 	
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	
	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
	

	
function ListActivities($Selection, &$cnt = 0){
	
	global $System;
	$cnt = 0;
	
	$Content = "";

	$objActivities = new clsActivities();

	switch ($Selection){
		case "public":
			$objActivities->Published = true;
			break;			
		case "my":
			$objActivities->MemberId = $System->User->Id;
			break;
	}
	
	$objActivities->getItems();
	$cnt = count($objActivities->Items);

	if (count($objActivities->Items) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
		
		$Content .= "<th>Title</th><th>Description</th>";
		if ($System->LoggedOn){					
			$Content .= "<th>My Rights</th>";
		}
		$Content .= "<th>Template</th>";
		
		$Content .= '</tr>';		
		$Content .= '</thead>';

		foreach ( $objActivities->Items as $objActivity){
			$Content .= "<tr>";

			$Title = $objActivity->Title;
			if (trim($Title) == ''){
				$Title = '...';
			}
			$ParamSid = $System->Session->ParamSid;
			$Content .= "<td><a href='activity.php?$ParamSid&uriactivity=".$objActivity->Uri."'>".$Title."</a></td>";
			
			$Content .= "<td>".nl2br(Truncate($objActivity->Description))."</td>";
			
			if ($System->LoggedOn){
				$MyRights = '';
/*
				if (isset($objActivity->MemberIds[$System->User->Id])){
					$MyRights = 'member';
				}				
				if (isset($objActivity->AdminIds[$System->User->Id])){
					$MyRights = 'admin';
				}
*/
				
/*				
				if (isset($objActivity->Members[$System->User->Id])){
					$obyActivityMember = $objActivity->Members[$System->User->Id];
					$MyRights = $obyActivityMember->Rights->Label;
				}
*/				
				$Content .= "<td>".$objActivity->MyRights->Label."</td>";			

			}

			$Content .= "<td>";
			if (is_object($objActivity->Template)){
				$Content .= $objActivity->Template->Name;
			}
			$Content .= "</td>";			
						
			$Content .= "</tr>";
			
		}
 		$Content .= '</table>';
		
	}
	
	return $Content;	
		
}	
	
?>