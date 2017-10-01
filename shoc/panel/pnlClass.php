<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlClass( $ModelId, $ClassId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	$objModel = null;
	if (!($objModel = $Models->getItem($ModelId))){
		throw new exception("Unknown Model");
	}

	$objClass = null;
	if (!isset($objModel->Classes[$ClassId])){
		throw new exception("Unknown Class");
	}
	$objClass = $objModel->Classes[$ClassId];
	
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Name</th><td><a href='class.php?modelid=$ModelId&classid=$ClassId'>".$objClass->Name."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objClass->Version."</td></tr>";
	$Content .= "<tr><th>Label</th><td>".nl2br($objClass->Label)."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objClass->Definition)."</td></tr>";
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=$ModelId'>".$objModel->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}


?>