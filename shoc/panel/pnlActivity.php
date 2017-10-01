<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlActivity($objActivity = null){
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	
	$Title = $objActivity->Title;
	if (empty($Title)){
		$Title = $objActivity->Id;
	}
	
	$Content .= "<tr><th>Title</th><td><a href='activity.php?uriactivity=".$objActivity->Uri."'>$Title</a></td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objActivity->Description)."</td></tr>";	

	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}


Function pnlActivityMember($objActivityMember = null){
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	
	$Email = '';
	if (is_object($objActivityMember->User)){
		$Email = $objActivityMember->User->Email;
	}

	$Rights = '';
	if (is_object($objActivityMember->Rights)){
		$Rights = $objActivityMember->Rights->Label;
	}

	$Status = '';
	if (is_object($objActivityMember->Status)){
		$Status = $objActivityMember->Status->Label;
	}
	
	
	$Content .= "<tr><th>email address</th><td>$Email</td></tr>";
	$Content .= "<tr><th>Rights</th><td>$Rights</td></tr>";	
	$Content .= "<tr><th>Status</th><td>$Status</td></tr>";	
	
	
	$Content .= '</table>';
	$Content .= '</div>';

	
	return $Content;
}



?>