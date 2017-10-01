<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlBox($objBox = null){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Title</th><td><a href='box.php?uribox=".$objBox->Uri."'>".$objBox->Title."</a></td></tr>";
	
	if (is_object($objBox->Type)){
		$Content .= "<tr><th>Type</th><td>".$objBox->Type->Name."</td></tr>";
	}

	$Content .= "<tr><th>Description</th><td>".nl2br($objBox->Description)."</td></tr>";
	
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

Function pnlBoxId($Box = null){

	$Content = '';
	
	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Id</th><td><a href='box.php?uribox=".$Box->Uri."'>".$Box->Id."</a></td></tr>";
	
	$Content .= "<tr><th>URI</th><td><a href='uri.php?uri=".$Box->Uri."&type=box'>".$Box->Uri."</a></td></tr>";
	
	
	$Content .= '</table>';	
	$Content .= '</div>';

	return $Content;
}


?>