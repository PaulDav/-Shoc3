<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlLink($Link = null){
	
	$Content = '';
	
	if (is_null($Link)){
		return $Content;
	}

	$Content .= "<div  class='sdgreybox'>";
	
	$Content .= "<table>";

	if (!is_null($Link->FromSubject)){
		$Content .= "<tr>";
		$Content .= "<th>From Subject</th>";
		$Content .= "<td>".$Link->FromSubject->Title."</td>";
		$Content .= "</tr>";
	}


	if (!is_null($Link->Relationship)){
		$Content .= "<tr>";
		$Content .= "<th>Relationship</th>";
		$Content .= "<td><a href='link.php?urilink=".$Link->Uri."'>".$Link->Relationship->Label."</a></td></tr>";
		$Content .= "</tr>";		
	}
	
	if (!is_null($Link->ToSubject)){
		$Content .= "<tr>";
		$Content .= "<th>To Subject</th>";
		$Content .= "<td>".$Link->ToSubject->Title."</td>";
		$Content .= "</tr>";
	}

	
	if (!is_null($Link->Description)){
		$Content .= "<tr>";
		$Content .= "<th>Description</th>";
		$Content .= "<td>".nl2br($Link->Description)."</td>";
		$Content .= "</tr>";		
	}
	
	
	$Content .= "</table>";
	$Content .= "</div>";
		
	return $Content;
}



?>