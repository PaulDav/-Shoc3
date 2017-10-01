<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlRelationship($RelationshipId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	if (!isset($Models->Relationships[$RelationshipId])){
		throw new exception("Unknown Relationship");
	}

	$objRelationship = $Models->Relationships[$RelationshipId];
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>From Class</th><td><a href='class.php?classid=".$objRelationship->FromClass->Id."'>".$objRelationship->FromClass->Name."</a></td></tr>";	
	$Content .= "<tr><th>Relationship</th><td><a href='relationship.php?relationshipid=$RelationshipId'>".$objRelationship->Label."</a></td></tr>";
	$Content .= "<tr><th>To Class</th><td><a href='class.php?classid=".$objRelationship->ToClass->Id."'>".$objRelationship->ToClass->Name."</a></td></tr>";	
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=".$objRelationship->Model->Id."'>".$objRelationship->Model->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

?>