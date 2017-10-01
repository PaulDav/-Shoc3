<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlModel( $ModelId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	$objModel = null;
	if (!($objModel = $Models->getItem($ModelId))){
		throw new exception("Unknown Model");
	}
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Name</th><td><a href='model.php?modelid=$ModelId'>".$objModel->Name."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objModel->Version."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objModel->Definition)."</td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}


?>