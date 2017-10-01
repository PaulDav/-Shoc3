<?php


	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsPage.php");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
	require_once('update/updateData.php');
	
	define('PAGE_NAME', 'group');
	
	session_start();
	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$uriGroup = null;
	$uriActivity = null;
	
	try {
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':
				
				if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){
					$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];			
				}
				if (is_null($uriActivity)){
					throw new exception("Activity not specified");
				}
				
				$objActivity = $Shoc->getActivity($uriActivity);				
				
				break;
			case 'edit':
				
				if (isset($_SESSION['forms'][PAGE_NAME]['urigroup'])){
					$uriGroup = $_SESSION['forms'][PAGE_NAME]['urigroup'];			
				}
				if (is_null($uriGroup)){
					throw new exception("Group not specified");
				}
				
				$objGroup = $Shoc->getGroup($uriGroup);
				$objActivity = $objGroup->Activity;
				$uriActivity = $objActivity->Uri;
				
				break;
			default:
				throw new exception("Invalid Mode");
		}

		
		$Title = null;
		if (isset($_SESSION['forms'][PAGE_NAME]['title'])){
			$Title = $_SESSION['forms'][PAGE_NAME]['title'];			
		}
		if (is_null($Title)){
			throw new exception("Title not specified");
		}
		
		$Description = "";
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
		}		
				
		switch ( $Mode ){
			case "new":
			case "edit":
				$uriGroup = dataGroupUpdate($Mode, $uriGroup , $uriActivity, $Title, $Description);
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: group.php?urigroup=$uriGroup");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>