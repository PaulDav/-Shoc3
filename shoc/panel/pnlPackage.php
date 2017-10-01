<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlPackage( $ModelId, $PackageId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	$objModel = null;
	if (!($objModel = $Models->getItem($ModelId))){
		throw new exception("Unknown Model");
	}

	$objPackage = null;
	if (!isset($objModel->Packages[$PackageId])){
		throw new exception("Unknown Package");
	}
	$objPackage = $objModel->Packages[$PackageId];
	
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Name</th><td><a href='package.php?modelid=$ModelId&packageid=$PackageId'>".$objPackage->Name."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objPackage->Version."</td></tr>";
	$Content .= "<tr><th>Label</th><td>".nl2br($objPackage->Label)."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objPackage->Definition)."</td></tr>";
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=$ModelId'>".$objModel->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}


?>