<?php

require_once('class/clsSystem.php');
require_once('class/clsActions.php');
require_once('class/clsAccount.php');

Function pnlStandardMenu(){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}


	$Content = "";

	$Content .= "<div class='menu'>";

	$Content .= "<ul>";
	$Content .= "<li><a href='.'>Home</a></li>";
			
	$Content .= "</ul>";
	$Content .= "<hr/>";
	
	$Content .= "</div>";


	return $Content;
}

Function pnlLoggedOnMenu(){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	$UserLevelId = 0;
	$objAccount = null;
	if ($System->LoggedOn){
		$objAccount = new clsAccount($System->User);
		$UserLevelId = $objAccount->UserLevel->Id;
	}
	

	$Content = "";

	$Content .= "<div class='menu'>";
	$Content .= "<ul>";
	
	$Content .= "<li><a href='activities.php'>Activities</a></li>";
	$Content .= "<li><a href='objectboxes.php'>Add data to a box</a></li>";
	$Content .= "</ul>";
	
	$Content .= "<hr/>";	
	
	$Content .= "<ul>";
	
	$Content .= "<li><a href='groups.php'>Groups</a></li>";
	$Content .= "<li><a href='boxes.php'>Boxes</a></li>";
	$Content .= "</ul>";
	
	$Content .= "<hr/>";
	
	$Content .= "<ul>";
	
	
	if ($objAccount->UserLevel->Id >= 100){
		$Content .= "<li><a href='models.php'>Models</a></li>";
	}
	
	
	$objActions = new clsActions();	
	if ($objActions->NumberOfActions > 0){
		$Content .= "<li><a href='actions.php'>Actions(".$objActions->NumberOfActions.")</a></li>";
	}

//	$Content .= "<li><a href='doClear.php'>Clear</a></li>";
	
	
	$Content .= "</ul>";
	$Content .= "</div>";
	
	return $Content;
}


Function pnlActivityMenu($objActivity){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	$ParamSid = $System->Session->ParamSid;
	
	$arrObjects = $objActivity->Template->Objects;
	usort($arrObjects, 'cmpObjectHeading');

	$Content = '';
	
	$Content .= "<div class='menu'>";
	$Content .= "<ul>";
	foreach ($arrObjects as $objObject){
		if ($objObject->Start){
			$Content .= "<li><a href='subjects.php?$ParamSid&objectid=".$objObject->Id."'>".$objObject->Class->Heading."</a></li>";
		}
	}
	$Content .= "</ul></div>";
	
	return $Content;
}

function cmpObjectHeading($a, $b){
    return strcmp( strtolower($a->Class->Heading),  strtolower($b->Class->Heading));
}


?>