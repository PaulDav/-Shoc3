<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');
require_once('class/clsUri.php');

Function pnlSubject($Subject = null){

	$Content = '';
	
	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><th>Id</th><td><a href='subject.php?urisubject=".$Subject->Uri."'>".$Subject->Id."</a></td></tr>";
	$Content .= "<tr><th>Class</th><td><a href='class.php?classid=".$Subject->Class->Id."'>".$Subject->Class->Label."</a></td></tr>";

	$objUri = new clsUri();
	$objUri->forSubject($Subject);
	$Content .= "<tr><th>URI</th><td><a href='".$objUri->Uri."'>".$objUri->Uri."</a></td></tr>";
	
	$Content .= '</table>';	
	$Content .= '</div>';

	return $Content;
}

Function pnlSubjectAttributes($Subject){

	$Content = '';	
	
	$Properties = $Subject->Class->AllProperties;
	if (is_object($Subject->Object)){
		$Properties = array();
		foreach ($Subject->Object->ObjectProperties as $objObjectProperty){
			$Properties[] = $objObjectProperty->Property;
		}
	}
	
	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	$Content .= "<tr><td>";	
	$Content .= pnlAttributes($Properties, $Subject->Attributes);
	$Content .= "</td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';
	
	return $Content;	
	
}


Function pnlAttributes($Properties, $Attributes = null ){

	if (is_null($Attributes)){
		return;
	}
		
	$Content = '';
	$Content .= "<table>";	
	
	foreach ($Properties as $objProperty){
		
		
		foreach ($Attributes as $objAttribute){
			if ($objAttribute->Property !== $objProperty){
				continue;
			}
		
			if (count($objAttribute->Parts) == 0){
				if (isMapShape($objAttribute->Statement->Value)){
					pnlAttributeMap($objAttribute);
					continue;
				}
			}

			$Content .= "<tr>";		
			$Content .= "<th>".$objProperty->Label."</th>";		
			
			$Content .= "<td>";
			if (count($objAttribute->Parts) == 0){
				$Content .= pnlAttributeValue($objAttribute);
			}
			else
			{
				$Content .= pnlAttributes($objAttribute->Property->Parts,$objAttribute->Parts);
			}
			$Content .= "</td>";
			$Content .= "</tr>";			
			
		}
		
	}
		
/*		
		
		$Content .= "</tr>";		
	}

	foreach ($Attributes as $objAttribute){
		
		if (count($objAttribute->Parts) == 0){
			if (isMapShape($objAttribute->Statement->Value)){
				pnlAttributeMap($objAttribute);
				continue;
			}
		}
		
		
		$Content .= "<tr>";		
		$Content .= "<th>".$objAttribute->Property->Label."</th>";

		$Content .= "<td>";
		if (count($objAttribute->Parts) == 0){
			$Content .= pnlAttributeValue($objAttribute);
		}
		else
		{
			$Content .= pnlAttributes($objAttribute->Parts);
		}
		$Content .= "</td>";		
		
		$Content .= "</tr>";			
	}
*/		
	$Content .= "</table>";
	
	return $Content;
}

function isMapShape($Value){

	$arrJson = json_decode($Value, true);
	if (is_null($arrJson)){
		return false;
	}
	if (isset($arrJson['geometry']['coordinates'])){
		return true;
	}
	return false;
	
}

function pnlAttributeMap($objAttribute){

	global $hasMap;
	global $jsScript;	
	
	$hasMap = true;
	
	$Value = $objAttribute->Statement->Value;
	
	$jsScript .= "<script>    arrMapLayers.push({label:".json_encode($objAttribute->Property->Label).", polygon:".json_encode($Value)."}); </script>";
	
}

function pnlAttributeValue($objAttribute){

	$Content = '';

	$Value = $objAttribute->Statement->ValueLabel;
	
	if ($objAttribute->Property->DataType->Name == 'anyURI'){
		if (substr( $Value, 0, 4 ) === "http"){
			$Content  = "<a href='$Value'>$Value</a>";
			return $Content;
		}
	}
	
	return nl2br($Value);
	
}
?>