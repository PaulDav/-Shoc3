<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlList($ListId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	if (!isset($Models->Lists[$ListId])){
		throw new exception("Unknown List");
	}

	$objList = $Models->Lists[$ListId];
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Name</th><td><a href='list.php?listid=$ListId'>".$objList->Name."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objList->Version."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objList->Definition)."</td></tr>";
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=".$objList->Model->Id."'>".$objList->Model->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}


Function pnlTerm($TermId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	if (!isset($Models->Terms[$TermId])){
		throw new exception("Unknown Term");
	}

	$objTerm = $Models->Terms[$TermId];
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Label</th><td><a href='term.php?termid=$TermId'>".$objTerm->Label."</a></td></tr>";
	$Content .= "<tr><th>Reference</th><td>".$objTerm->Reference."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objTerm->Version."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objTerm->Definition)."</td></tr>";
	$Content .= "<tr><th>List</th><td><a href='list.php?listid=".$objTerm->List->Id."'>".$objTerm->List->Name."</a></td></tr>";
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=".$objTerm->List->Model->Id."'>".$objTerm->List->Model->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

?>