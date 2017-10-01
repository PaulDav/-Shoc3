<?php

	require_once("path.php");

		
	require_once("function/utils.inc");

	require_once("class/clsSystem.php");
	require_once("class/clsAccount.php");		
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	
	
	require_once("update/updateData.php");
			
	define('PAGE_NAME', 'activity');
	
	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	
	try {
			
		SaveUserInput(PAGE_NAME);
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}

		
		$UserLevelId = 0;
		$objAccount = null;
		if ($System->LoggedOn){
			$objAccount = new clsAccount($System->User);
			$UserLevelId = $objAccount->UserLevel->Id;
		}
		
		
		$Models = new clsModels();
		$Archetypes = new clsArchetypes($Models);
				
		$uriActivity = null;
		$objTemplate = null;
		
		$Title = null;
		$Description =  null;
		$TemplateId = null;
		
		if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){			
			$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];
			$objActivity = $Shoc->getActivity($uriActivity);
			if (!is_null($objActivity->Template)){
				$objTemplate = $objActivity->Template;
			}
		}
		
				
		if (isset($_SESSION['forms'][PAGE_NAME]['title'])){
			$Title = $_SESSION['forms'][PAGE_NAME]['title'];			
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];			
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['templateid'])){
			$TemplateId = $_SESSION['forms'][PAGE_NAME]['templateid'];			
		}
		
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode){
			case 'edit':
			case 'delete':
				if (!isset($uriActivity)){
					throw new exception("uriActivity not specified");
				}
				break;
			case 'new':				
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}


		switch ($Mode){
			case 'new':
			case 'edit':

				if (is_null($Title)){
					throw new exception ("Title not specified");
				}				
				
				if (is_null($TemplateId)){
					throw new exception ("Template not specified");
				}
				
				break;
		}
		
		switch ( $Mode ){
			case "new":
			case "edit":
				$uriActivity = dataActivityUpdate($Mode, $uriActivity , $TemplateId, $Title, $Description);
				break;
			case 'delete':
				dataActivityDelete($uriActivity);
				break;
		}

		$ReturnUrl = "activity.php?uriactivity=$uriActivity";
		
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = ".";
				break;
		}
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>