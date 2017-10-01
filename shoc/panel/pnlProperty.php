<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlProperty($PropertyId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	if (!isset($Models->Properties[$PropertyId])){
		throw new exception("Unknown Property");
	}

	$objProperty = $Models->Properties[$PropertyId];
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Name</th><td><a href='property.php?propertyid=$PropertyId'>".$objProperty->Name."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objProperty->Version."</td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objProperty->Label."</td></tr>";
	$Content .= "<tr><th>Definition</th><td>".nl2br($objProperty->Definition)."</td></tr>";
	$Content .= "<tr><th>Class</th><td><a href='class.php?classid=".$objProperty->Class->Id."&modelid=".$objProperty->Class->Model->Id."'>".$objProperty->Class->Name."</a></td></tr>";
	$Content .= "<tr><th>Model</th><td><a href='model.php?modelid=".$objProperty->Class->Model->Id."'>".$objProperty->Class->Model->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

Function pnlField($PropertyId){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	if (!isset($Models->Properties[$PropertyId])){
		throw new exception("Unknown Property");
	}

	$objProperty = $Models->Properties[$PropertyId];
	
	$Content = '';
	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	if (is_object($objProperty->DataType)){
		$Content .= "<tr><th>Data Type</th><td><a href='datatype.php?datatypeid=".$objProperty->DataType->Id."'>".$objProperty->DataType->Name."</a></td></tr>";
	}
	if (!is_null($objProperty->MinLength)){
		$Content .= "<tr><th>Minimum Length</th><td>".$objProperty->MinLength."</td></tr>";
	}
	if (!is_null($objProperty->MaxLength)){
		$Content .= "<tr><th>Maximum Length</th><td>".$objProperty->MaxLength."</td></tr>";
	}
	if (!is_null($objProperty->Pattern)){
		$Content .= "<tr><th>Pattern</th><td>".$objProperty->Pattern."</td></tr>";
	}
		
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

?>