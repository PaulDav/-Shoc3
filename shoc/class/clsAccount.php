<?php

require_once('class/clsSession.php');
require_once('class/clsSystem.php');


class clsAccount {

	public $User;
	public $Level = null;

	public function __construct ($User=null){

		global $System;

		if (!isset($System)){
			$System = new clsSystem();
		}

		if (is_null($User)){
	 		throw new Exception('No User');
	 	}

	 	$this->User = $User;
	 	$UserId = $User->Id;
	 	
		$sql = "select * from tbl_user where usrRecnum=$UserId";
		$rst = $System->db->query($sql);
		 
		if (!$rst) {
			throw new Exception('Could not execute query');
		}

		if ($rst->num_rows==1) {

			 
			$rstRow = $rst->fetch_assoc();
			
			$UserLevelId = 1;
			if (!is_null($rstRow['usrLevel'])){			
				$UserLevelId = $rstRow['usrLevel'];
			}
			if (isset($System->Config->UserLevels[$UserLevelId])){
				$this->UserLevel = $System->Config->UserLevels[$UserLevelId];
			}		
		}

	}

}