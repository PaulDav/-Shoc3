<?php

class clsConfig {

	public $Vars = array();

	public $Namespaces = array();

	public $DotRenderer = null;
	public $VizFormats = array();

	public $Defaults = null;

	public $ActionTypes = array();
//	public $AboutTypes = array();
	public $ValueTypes = array();
	public $DataTypes = array();

	public $UserStatus = array();
	public $ActivityMemberRights = array();
	public $GroupMemberRights = array();
	
	
	public $UserLevels = array();
	public $UserGroupRights = array();
	public $UserGroupStatus = array();
	public $UserGroupStatusControllerOption = array();
	public $UserGroupStatusUserOption = array();
	
	public function __construct($path=null){

		if (is_null($path)){
			//			$parse = parse_url($_SERVER["REQUEST_URI"]);
			$parse = parse_url($_SERVER["SCRIPT_NAME"]);
			$path = $parse['path'];
		}
		if (!(substr($path,-1) == '/')){
			$path = dirname($path)."/";
		}
		$path = $_SERVER['DOCUMENT_ROOT']."/".$path."config";
	 	
		$this->Vars = parse_ini_file("$path/config.php",true);
	
		
		$ValidDotRenderers = array();
		$ValidDotRenderers[] = 'google chart';
		$ValidDotRenderers[] = 'viz.js';

		if (isset($this->Vars['instance']['dotrenderer'])){
			if (in_array($this->Vars['instance']['dotrenderer'],$ValidDotRenderers)){
				$this->DotRenderer = $this->Vars['instance']['dotrenderer'];
			}
		}

		$this->VizFormats[1] = 'image';
		$this->VizFormats[9] = 'dot';

/*		
		$this->UserGroupStatus[1] = "requested";
	  	$this->UserGroupStatus[2] = "invited";
	  	$this->UserGroupStatus[100] = "member";
	  	$this->UserGroupStatus[150] = "rejected";
	  	$this->UserGroupStatus[200] = "removed";
	  	$this->UserGroupStatus[300] = "left";
	  	
	  	$this->UserGroupStatusControllerOption[100] = 100;
	  	$this->UserGroupStatusControllerOption[150] = 150;
	  	$this->UserGroupStatusControllerOption[200] = 200;
	  	
	  	$this->UserGroupStatusUserOption[100] = 100;
	  	$this->UserGroupStatusUserOption[150] = 150;
	  	$this->UserGroupStatusUserOption[300] = 300;
		
		
		$this->UserGroupRights[1] = "view";
	  	$this->UserGroupRights[100] = "edit";
	  	$this->UserGroupRights[200] = "admin";
*/
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 1;
		$UserStatus->Label = "requested";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 2;
		$UserStatus->Label = "invited";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 100;
		$UserStatus->Label = "member";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 150;
		$UserStatus->Label = "rejected";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 200;
		$UserStatus->Label = "removed";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	$UserStatus = new clsUserStatus();
		$UserStatus->Id = 300;
		$UserStatus->Label = "left";
	  	$this->UserStatus[$UserStatus->Id] = $UserStatus;
	  	
	  	
	  	
	  	
		$UserLevel = new clsUserLevel();
		$UserLevel->Id = 1;
		$UserLevel->Label = "regular";
	  	$this->UserLevels[1] = $UserLevel;

		$UserLevel = new clsUserLevel();
		$UserLevel->Id = 100;
		$UserLevel->Label = "premium";
	  	$this->UserLevels[100] = $UserLevel;

	  	$MemberRights = new clsMemberRights();
		$MemberRights->Id = 1;
		$MemberRights->Label = "member";
	  	$this->ActivityMemberRights[$MemberRights->Id] = $MemberRights;

	  	$MemberRights = new clsMemberRights();
		$MemberRights->Id = 100;
		$MemberRights->Label = "admin";
	  	$this->ActivityMemberRights[$MemberRights->Id] = $MemberRights;
	  	

	  	$MemberRights = new clsMemberRights();
		$MemberRights->Id = 1;
		$MemberRights->Label = "view";
	  	$this->GroupMemberRights[$MemberRights->Id] = $MemberRights;

	  	$MemberRights = new clsMemberRights();
		$MemberRights->Id = 100;
		$MemberRights->Label = "edit";
	  	$this->GroupMemberRights[$MemberRights->Id] = $MemberRights;
	  	
	}

}


class clsDefaults{
	public $LineLength = 30;

}

class clsUserLevel{
	public $Id = null;
	public $Label = null;	
}

class clsUserStatus{
	public $Id = null;
	public $Label = null;	
}

class clsMemberRights{
	public $Id = null;
	public $Label = null;	
}

