<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function frmSubjectFilter($objClass = null, $Prefix = 'filter'){

	
	$Content = '';
	
	if (!is_null($objClass)){
	
		$Content .= "<div  class='sdbluebox'>";
		$Content .= '<table>';

		foreach ($objClass->AllProperties as $objProperty){
			$Content .= frmPropertyFilter($objProperty, $Prefix);
		}
		
		foreach ($objClass->AllRelationships as $objRelationship){
			$Content .= "<tr>";
			$Content .= "<th>".$objRelationship->Label."</th>";
		}
		
		
				
		$Content .= '</table>';
		$Content .= '</div>';
		
	}

	return $Content;
}


Function frmObjectFilter($objObject = null, $Prefix = 'filter', $arrRelationships = array()){

	$Content = '';
	
	if (!is_null($objObject)){
	
		$Content .= "<h3>".$objObject->Label."</h3>";
		$Content .= "<div  class='sdbluebox'>";
		$Content .= '<table>';

		foreach ($objObject->ObjectProperties as $objObjectProperty){
			$Content .= frmPropertyFilter($objObjectProperty->Property, $Prefix);
		}

		foreach ($objObject->Relationships as $objObjectRelationship){
			$objRelationship = $objObjectRelationship->Relationship;
			
			if (isset($arrRelationships[$objRelationship->Id])){
				continue;
			}
			$arrRelationships[$objRelationship->Id] = $objRelationship;
			
			$useRelationship = false;
			
			if ($objObjectRelationship->FromObject === $objObject){
				if ($objRelationship->Extending){
					$useRelationship = true;
				}
			}

			if ($objObjectRelationship->FromObject === $objObject){
				$useRelationship = true;
			}
			
			if ($useRelationship){
				$RelLabel = $objObjectRelationship->Label;
				$objLinkObject = $objObjectRelationship->ToObject;

				$Content .= "<tr>";
				$Content .= "<th>".$RelLabel."</th>";
				
				$Content .= "<td>";
				$Content .= frmObjectFilter($objLinkObject, $Prefix.'_relid_'.$objObjectRelationship->Id, $arrRelationships);
				$Content .= "</td>";
				
				$Content .= "</tr>";
				
				
//				foreach ($objLinkObject->ObjectProperties as $objObjectProperty){
//					$arrProperties[] = $objObjectProperty->Property;
//				}		
			}
		}
		
		
		
/*		
		foreach ($objClass->AllRelationships as $objRelationship){
			$Content .= "<tr>";
			$Content .= "<th>".$objRelationship->Label."</th>";
		}
*/		
		
				
		$Content .= '</table>';
		$Content .= '</div>';
		
	}

	return $Content;
}



Function frmPropertyFilter($objProperty = null, $Prefix){

	$Content = '';

	$Content .= "<tr>";
	$Content .= "<th>".$objProperty->Label."</th>";
	
	if (count($objProperty->Parts) == 0){
		$Content .= "<td>";
		
		$Is = false;
		$Contains = false;
		$Greater = false;
		$Less = false;
		
		
		if (count($objProperty->Lists) > 0){
			$Is = true;
			
			$FieldName = $Prefix."_propid_".$objProperty->Id."_is";
			$Content .= "<select name='$FieldName' id='$FieldName'>";
			$Content .= "<option/>";
			
			foreach ($objProperty->Lists as $objList){
				foreach ($objList->Terms as $objTerm){
					$Content .= "<option value='".$objTerm->Id."'>".$objTerm->Label."</option>";
				}
			}
			$Content .= "</select>";

		}
		else
		{
			
			$MaxLength = $objProperty->MaxLenth;
			switch ($objProperty->DataType->Name){
			case 'date':
				$Is = true;
				$Greater = true;
				$Less = true;
				if (is_null($MaxLength)){
					$MaxLength = 10;
				}
				break;
			case 'integer':
				$Is = true;
				$Greater = true;
				$Less = true;
				
				if (is_null($MaxLength)){
					$MaxLength = 10;
				}
				break;
			case 'decimal':
				
				$Greater = true;
				$Less = true;
										
				if (is_null($MaxLength)){
					$MaxLength = 10;
				}
				break;
			default:
				$Is = false;
				$Contains = true;
				
				if (is_null($MaxLength)){
					$MaxLength = 40;
				}
				break;
			}
		
			
			for ($i=1;$i<5;$i++){
				$DoField = false;
				$FieldName = null;
				switch ($i){
					case 1;
						if ($Is){
							$Content .= "is";
							$FieldName = $Prefix."_propid_".$objProperty->Id."_is";
							$DoField = true;									
						}
						break;
					case 2;
						if ($Contains){
							$Content .= "contains";
							$FieldName = $Prefix."_propid_".$objProperty->Id."_contains";
							$DoField = true;									
						}
						break;
					case 3;
						if ($Greater){
							$Content .= "greater than";
							$FieldName = $Prefix."_propid_".$objProperty->Id."_greater";
							$DoField = true;									
						}
						break;
					case 3;
						if ($Less){
							$Content .= "less than";
							$FieldName = $Prefix."_propid_".$objProperty->Id."_less";
							$DoField = true;									
						}
						break;
				}
				
				if ($DoField){
					$Content .= "<input name='$FieldName' id='$FieldName' length='$MaxLength' maxlength = '$MaxLength'/>";							
				}
				
			}	
		}
		
		$Content .= "</td>";
	}
	
	
	$Content .= "</tr>";
	
	return $Content;
}


?>