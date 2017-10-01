<?php

	require_once("path.php");

	require_once("class/clsSystem.php");
	require_once("class/clsShocData.php");		
	require_once('class/clsMail.php');
	require_once("function/utils.inc");
	require_once('update/updateData.php');
		
	define('PAGE_NAME', 'activitymember');
	
	session_start();

	$System = new clsSystem();
	$Shoc = new clsShoc();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}

	$uriActivity = '';
	$MemberId = '';
	$Email = '';
	$Rights = null;
	$Status = null;
	
	try {
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		$uriActivity = null;
		if (isset($_SESSION['forms'][PAGE_NAME]['uriactivity'])){
			$uriActivity = $_SESSION['forms'][PAGE_NAME]['uriactivity'];			
		}
		if ( is_null($uriActivity)){
			throw new exception("Activity not specified");
		}

		$objActivity = $Shoc->getActivity($uriActivity);

		if (isset($_SESSION['forms'][PAGE_NAME]['memberid'])){
			$MemberId = $_SESSION['forms'][PAGE_NAME]['memberid'];			
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['rights'])){
			$Rights = $_SESSION['forms'][PAGE_NAME]['rights'];			
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['status'])){
			$Status = $_SESSION['forms'][PAGE_NAME]['status'];			
		}
		
		
		switch ($Mode) {
			case 'new':
			case 'edit':
				if (!isset($System->Config->ActivityMemberRights[$Rights])){
					throw new exception("Invalid Rights");
				}				
				break;
		}
		
		
		switch ($Mode) {
			case 'new':

				if (!$objActivity->canControl){
					throw new exception("You cannot update this User for this Activity");
				}
								
				$Email = '';
				if (isset($_SESSION['forms'][PAGE_NAME]['email'])){
					$Email = $_SESSION['forms'][PAGE_NAME]['email'];			
				}
				if ( $Email == ''){
					throw new exception("email address not specified");
				}
				if (!valid_email($Email)){
					throw new exception("invalid email address");
				}				
								
				$Status = 2; // invited
				
				break;				

			case 'request':

				$Status = 1; // requested
				
				break;
				
			case 'edit':
			case 'delete':

				if (!$objActivity->canControl){
					throw new exception("You cannot update this User for this Activity");
				}
				
				break;
			case 'user':
// the current user changing a status
				if (!isset($objActivity->Members[$System->User->Id])){
					throw new exception("You have not been invited to join this Activity");
				}
				$objMember = $objActivity->Members[$System->User->Id];
				if ($objMember->Status->Id != 2){
					throw new exception("You have not been invited to join this Activity");
				}
				$Rights = $objMember->Rights->Id;
												
				break;
			default:
				throw new exception("Invalid Mode");
		}
				
//		if (!isset($System->Config->UserStatus[$Status])){
//			throw new exception("Invalid Status");
//		}				
		

		
		switch ( $Mode ){
			case 'new': // invite
// check if user exists for email address				
				$UserExists = false;
				$sql = "SELECT usrRecnum FROM tbl_user INNER JOIN tbl_user_email ON emlUser = usrRecnum WHERE emlEmail = '$Email' AND usrName IS NOT NULL ";
				$rst = $System->DbExecute($sql);
				while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
					$UserExists = true;
					$UserId = $row['usrRecnum'];
					dataActivityMemberUpdate($Mode, $uriActivity, $Status, $Rights, $UserId );

					$Body = '';
					
					$Body .= "<p>You have been sent this email because you have been invited to join an activity at ".$System->Config->Vars['instance']['host']."</p>";

			 		$Body .= "<p>The activity is <b>".$objActivity->Title."</b></p>";
			 		
			 		$Body .= "<p>If you do not wish to join the activity, please ignore this email.</p>";

			 		$Href = $System->Config->Vars['instance']['host']."/login.php";
			 		
			 		$Body .= "<p>If you do wish to join the activity, please logon with your existing user id at <a href='$Href'>$Href</a> and follow the instructions to accept the invitation.</p>";

			 		$Href = $System->Config->Vars['instance']['host']."/usrreset.php";
			 		
			 		$Body .= "<p>If you have forgotten your user id or password, you can reset it at <a href='$Href'>$Href</a></p>";
					
					if (isset($System->Config->Vars['mail']['from'])){
						$objMail = new clsMail();
				 		$objMail->To = $Email;
				 		$objMail->Subject = "SHOC - Invitation to join an activity";
				 		
				 		$Body = "<html>".$Body;
			 			$Body .= "<p><br/>The SHOC team</p>";	 		
				 		$Body .= "</html>";
			 			
				 		$objMail->Content = $Body;
				 		$objMail->Send();
					}
					else
					{
						$System->Session->Message = $Body;						
					}
					
				}
				
				if (!$UserExists){
// check for a null user for the email address
					//$sql = "SELECT usrRecnum FROM tbl_user WHERE usrEmail = '$Email' AND usrName IS NULL ";
					
					
					$sql = "SELECT usrRecnum FROM tbl_user INNER JOIN tbl_user_email ON emlUser = usrRecnum WHERE emlEmail = '$Email' AND usrName IS NULL ";
					
					
					$rst = $System->DbExecute($sql);
						while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
						$UserId = $System->CreateUser(null,null,$Email);
						$objUser = new clsUser($UserId);
						$objUser->SetHash();
						$UserExists = true;
					}
					if (!$UserExists){
						$UserId = $System->CreateUser(null,null,$Email);
						$objUser = new clsUser($UserId);
						$objUser->SetHash();
					}
					dataActivityMemberUpdate($Mode, $uriActivity, $Status, $Rights, $UserId );

					$Body = '';
					
					$Body .= "<p>You have been sent this email because you have been invited to join an activity at ".$System->Config->Vars['instance']['host']."</p>";

			 		$Body .= "<p>The activity is <b>".$objActivity->Title."</b></p>";
			 		
			 		$Body .= "<p>If you do not wish to join the activity, please ignore this email.</p>";
			 		
			 		$Body .= "<p>If you do wish to join the activity, please register by following the link below, or by copying and pasting it into your browser.</p>";
	 		
			 		$Href = $System->Config->Vars['instance']['host']."/doHashLogin.php?key=".$objUser->Hash;
	 				$Body .= "<p><a href='$Href'>$Href</a></p>";
			 		
			 		$Body .= "... and follow the instructions to accept the invitation.</p>";
					
					if (isset($System->Config->Vars['mail']['from'])){
			 							
						$objMail = new clsMail();
				 		$objMail->To = $Email;
				 		$objMail->Subject = "SHOC - Invitation to join an activity";
		
				 		$Body = "<html>".$Body;
				 		$Body .= "<p><br/>The SHOC team</p>";	 		
				 		$Body .= "</html>";
				 		
				 		$objMail->Content = $Body;
				 		$objMail->Send();
					}
					else
					{
						$System->Session->Message = $Body;						
					}
					
					
					
				}
				break;
//			case "request":
//				$UserGroupId = dataUserGroupUpdate($Mode, NULL , $GroupId, $Status, $Rights);
//				break;
//			case "edit":
//				break;
			case 'user':
				dataActivityMemberUpdate('edit', $uriActivity, $Status , $Rights , $System->User->Id );
				break;
			case "delete":
				dataActivityMemberDelete($uriActivity, $MemberId);
				$System->Session->Message = "User removed from Activity";
				break;
				
		}
		$ReturnURL = ".";
		switch ($Mode){
//			case 'request':
//				$System->Session->Message = "Request sent";
//				break;
//			case 'user':
//				switch ($Status){
//					case 100:
//						$System->Session->Message = "You are now a member of the group";
//						break;
//				}
//				break;
				
/*			case "delete":
				$ReturnURL = "dataset.php?datasetid=$DatasetId";
				break;
			default:
				$ReturnURL = "dataitem.php?dataitemid=$DataitemId";
			    break;
*/		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['ReturnURL'])){
			$ReturnURL = $_SESSION['forms'][PAGE_NAME]['ReturnURL'];		
		}		

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