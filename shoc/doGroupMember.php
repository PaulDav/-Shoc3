<?php

	require_once("path.php");
	
	require_once("class/clsSystem.php");
	require_once("class/clsPage.php");
	require_once("class/clsShocData.php");		
	require_once("function/utils.inc");
	require_once('update/updateData.php');
	
		
	define('PAGE_NAME', 'groupmember');
	
	session_start();

	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}

	$uriGroup = null;
	$MemberId = null;
	$Rights = 1;
	
	try {
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['urigroup'])){
			$uriGroup = $_SESSION['forms'][PAGE_NAME]['urigroup'];			
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['memberid'])){
			$MemberId = $_SESSION['forms'][PAGE_NAME]['memberid'];			
		}


		if (is_null($uriGroup)){
			throw new exception("Group not specified");
		}
				
		$objGroup = $Shoc->getGroup($uriGroup);
		if (!$objGroup->canControl){
			throw new exception("You cannot update members for this Group");
		}

		if (is_null($MemberId)){
			throw new exception("Member not specified");
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['Rights'])){
			$Rights = $_SESSION['forms'][PAGE_NAME]['Rights'];			
		}
		
		if (!isset($System->Config->GroupMemberRights[$Rights])){
			throw new exception("Invalid Rights");
		}
		
		
		switch ($Mode) {
			case 'new':

				if (!isset($objGroup->Activity->Members[$MemberId])){
					throw new exception("This is not a Member of the Activity");					
				}
				
				
				break;				
				
			case 'edit':

				
				if (!isset($objGroup->Members[$MemberId])){
					throw new exception("This is not a Member of the Group");					
				}
				
				break;
				
			default:
				throw new exception("Invalid Mode");
		}

		
		switch ( $Mode ){
			case 'new':
			case 'edit':
				dataGroupMemberUpdate($Mode, $uriGroup, $MemberId, $Rights);
				break;
		}

		$ReturnURL = "group.php?urigroup=$uriGroup";

		unset($_SESSION['forms'][PAGE_NAME]);

		header("Location: $ReturnURL");
		
    	exit;

	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
		header("Location: .?mode=fail");
		exit;
	}

?>