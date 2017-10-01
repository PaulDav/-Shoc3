<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');
require_once('class/clsShocData.php');

Function pnlBoxLink($BoxLink = null){
	
	$Content = '';
	
	if (is_null($BoxLink)){
		return $Content;
	}

	$Content .= "<div  class='sdgreybox'>";
	
	$Content .= "<table>";

	if (!is_null($BoxLink->Object)){
		$Content .= "<tr>";
		$Content .= "<th>Object</th>";
		$Content .= "<td>".$BoxLink->Object->Label."</td>";
		$Content .= "</tr>";
	}


	if (!is_null($BoxLink->Relationship)){
		$Content .= "<tr>";
		$Content .= "<th>Relationship</th>";
		$Content .= "<td>";
		switch ($BoxLink->Inverse){
			case false:
				$Content .= $BoxLink->Relationship->Label;
				break;
			default:
				$Content .= $BoxLink->Relationship->InverseLabel;				
				break;
		}
		$Content .= "</td></tr>";
	}
	
	if (!is_null($BoxLink->Subject)){
		$Content .= "<tr>";
		$Content .= "<th>Subject</th>";
		$Content .= "<td>".$BoxLink->Subject->Title."</td>";
		$Content .= "</tr>";
	}

	
	if (!is_null($BoxLink->Description)){
		$Content .= "<tr>";
		$Content .= "<th>Description</th>";
		$Content .= "<td>".nl2br($BoxLink->Description)."</td>";
		$Content .= "</tr>";		
	}
	
	
	$Content .= "</table>";
	$Content .= "</div>";
		
	return $Content;
}



?>