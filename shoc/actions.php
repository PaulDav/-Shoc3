<?php

require_once("path.php");

require_once("class/clsSystem.php");

require_once("class/clsPage.php");
require_once("class/clsActions.php");
require_once("class/clsShocData.php");


require_once("function/utils.inc");
	
define('PAGE_NAME', 'actions');


		
	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
		
	$Page = new clsPage();


	try {

		if (!$System->LoggedOn){
			throw new exception("Please Login first");	
		}		

		$Page->Title = "my actions";

		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
					
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$objActions = new clsActions();
		

	    if (count($objActions->uriActivityInvitations) > 0){
	    	
	    	
	    	$Tabs .= "<li><a href='#invitations'>Invitations";
	    	$cnt = 0;
	    	
				
			$TabContent .= "<div class='tabContent hide' id='invitations'>";
	    	
	    	$TabContent .= "<h3>Invitations to join Activities</h3>";
	    	
			$TabContent .= "<table>";
			$TabContent .= "<thead><tr><th>Activity</th><th>Desription</th></tr></thead>";
			$TabContent .= "<tbody>";
			
			foreach ($objActions->uriActivityInvitations as $uriActivity){
				++$cnt;
				$objActivity = $Shoc->getActivity($uriActivity);

				$TabContent .= "<tr>";
				
				$TabContent .= "<td>";				
				$TabContent .= $objActivity->Title;									
				$TabContent .= "</td>";								
				$TabContent .= "<td>".nl2br($objActivity->Description)."</td>";
				
				$TabContent .= "<td><a href='doActivityMember.php?uriactivity=$uriActivity&mode=user&status=100'>&bull; accept</a></td>";
								
			}
			$TabContent .= "<tbody>";
			$TabContent .= "</table>";
			$TabContent .= "</div>";

			
			$Tabs .= "($cnt)</a></li>";
			
			
	    }

	    if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
	    
	    
 		$Page->ContentPanelB = $PanelB;
	 	
	}
	catch(Exception $e)  {
		$Session->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
	
?>