<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('class/clsThread.php');

require_once('function/utils.inc');

require_once('class/clsModel.php');
require_once('class/clsShocData.php');


function pnlThread(){

	global $System;
	if (isset($System)){
		if (is_object($System->Config)){
			$objConfig = $System->Config;
		}
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	$ParamSid = $System->Session->ParamSid;
	
	$objThread = new clsThread();
	
	$Content = '';

	$Content .= "<div  class='sdthreadbox'>";
	
	if (!is_null($objThread->uriActivity)){
		$objActivity = $Shoc->getActivity($objThread->uriActivity);
		$Content .= "<div>activity-&gt;<a href='activity.php?$ParamSid&uriactivity=".$objActivity->Uri."'>".$objActivity->Title."</a></div>";
	}
	if (!is_null($objThread->uriGroup)){
		$objGroup = $Shoc->getGroup($objThread->uriGroup);
		$Content .= "<div>group-&gt;<a href='group.php?$ParamSid&urigroup=".$objGroup->Uri."'>".$objGroup->Title."</a></div>";
	}
	if (!is_null($objThread->uriBox)){
		$objBox = $Shoc->getBox($objThread->uriBox);
		$Content .= "<div>box-&gt;<a href='box.php?$ParamSid&uribox=".$objBox->Uri."'>".$objBox->Title."</a></div>";
	}
	if (!is_null($objThread->uriDocument)){
		$objDocument = $Shoc->getDocument($objThread->uriDocument);
		$Content .= "<div>document-&gt;<a href='document.php?$ParamSid&uridocument=".$objDocument->Uri."'>".$objDocument->Title."</a></div>";
	}

	$Content .= '</div>';

	$Content .= '<br/>';
	
	return $Content;
}



?>