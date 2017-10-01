<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
				
	define('PAGE_NAME', 'groups');
		
	session_start();

	$System = new clsSystem();
	$Shoc = new clsShoc();
	$objPage = new clsPage();

	$Tabs = "";
	$TabContent = "";
	
	
	try {
		
		$objPage->Title = "user groups";

		$PanelB = '';
			
		$PanelB .= "<h1>".$objPage->Title."</h1>";
		
		if ($System->LoggedOn){
			$Tabs .= "<li><a href='#my' id='tab0'>My Groups";
			$TabContent .= "<div class='tabContent hide' id='my'>";
			$TabContent .= ListGroups('my', $cnt);
			
			$TabContent .= "<div class='hmenu'><ul>";
			$TabContent .= "<li><a href='group.php?mode=new'>&bull; add</a></li>";
			$TabContent .= "</ul></div>";
			
			$TabContent .= "</div>";
			$Tabs .= "($cnt)</a></li>";
		}

		$Tabs .= "<li><a href='#public' id='tab1'>Public Groups";
		$TabContent .= "<div class='tabContent hide' id='public'>";
//		$TabContent .= ListGroups('public', $cnt);
		$TabContent .= "</div>";
		$Tabs .= "($cnt)</a></li>";		
	 	
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$objPage->ContentPanelB = $PanelB;
	 	
	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
	}
	 	
	$objPage -> Display();
	

	
function ListGroups($Selection, &$cnt = 0){
	
	global $System;
	$cnt = 0;
	
	$Content = "";

	$objGroups = new clsGroups();

	switch ($Selection){
		case "public":
			$objGroups->Published = true;
			break;			
		case "my":
			$objGroups->MemberId = $System->User->Id;
			break;
	}

	
	if (count($objGroups->Items) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
			$Content .= "<th colspan='2'>Group</th><th>Description</th>";
			if ($System->LoggedOn){					
				$Content .= "<th>My Membership</th><th>My Rights</th>";
			}
		$Content .= '</tr>';		
		$Content .= '</thead>';

		foreach ( $objGroups->Items as $objGroup){
			$Content .= "<tr>";

			$Content .= "<td><a href='group.php?urigroup=".$objGroup->Uri."'>".$objGroup->Title."</a></td>";
			$Content .= "<td>";
			if (!is_null($objGroup->Picture)) {
				$Content .= "<a href='group.php?groupid=".$objGroup->Id."'><img class='byimage' src='image.php?Id=".$objGroup->Picture."' /></a><br/>";
			}
			$Content .= "</td>";
			
			$Content .= "<td>".nl2br(Truncate($objGroup->Description))."</td>";
			
			if ($System->LoggedOn){
				$Content .= "<td>";
				if (!is_null($objGroup->MyMembership)){
					$Content .= $objGroup->MyMembership->Status->Label;
				}
				else
				{
					$Content .= "<div class='hmenu'><ul>";					
					$Content .= "<li><a href='doUserGroup.php?uriGroup=".$objGroup->Uri."&Mode=request'>&bull; request to join</a></li>";
					$Content .= "</ul></div>";					
				}
				$Content .= "</td>";
			
				$Content .= "<td>";
				if (!is_null($objGroup->MyMembership)){
					$Content .= $objGroup->MyMembership->Rights->Label;
				}
				$Content .= "</td>";
			}
						
			$Content .= "</tr>";
			
			
		}
 		$Content .= '</table>';
		
	}
	return $Content;	
		
}	
	
?>