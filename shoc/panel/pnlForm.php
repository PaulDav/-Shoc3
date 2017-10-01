<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

require_once('panel/pnlSubject.php');


Function pnlForm($Form = null){

	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
//	$Content .= "<tr><th>uri</th><td><a href='document.php?uridocument=".$Document->Uri."'>".$Document->Uri."</a></td></tr>";
//	$Content .= "<tr><th>Template Id</th><td><a href='archetype.php?archetypeid=".$Document->TemplateId."'>".$Document->TemplateId."</a></td></tr>";
//	$Content .= "<tr><th>Template</th><td><a href='list.php?listid=$ListId'>".$objList->Name."</a></td></tr>";

	if (!is_null($Form)){

		switch ($Form->Document->Type){
			case 'link':
				$Content .= "<tr><td>";	
				$Content .= pnlFormLink($Form);
				$Content .= "</td></tr>";
				
				break;
			
			default:
				$Content .= "<tr><td>";	
				$Content .= pnlFormObjects($Form);
				$Content .= "</td></tr>";
				
				break;
		}
		
	}
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}

Function pnlFormObjects($Form = null){
	
	$Content = '';

	if (is_object($Form->Document->Object)){
		foreach ($Form->Revision->Abouts as $objAbout){
			if ($objAbout->idObject == $Form->Document->Object->Id ){
				$Content .= pnlFormObject($Form, $Form->Document->Object, $objAbout);
			}
		}
	}

	return $Content;
}


Function pnlFormObject($Form, $Object = null, $About = null){
	
	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	$Content = '';

	$Content .= "<table>";
	$Content .= "<tr>";
	$Content .= "<th>".$Object->Label."</th>";
//	$Content .= "<td>".$About->uriSubject."</td>";
	
	$Content .= "</tr>";
	$Content .= "<tr><td/><td>";

	$Content .= "<table>";
	
	
	foreach ($Object->ObjectProperties as $objObjectProperty){
		$boolGivePropertyLabel = true;
		foreach ($Form->Revision->objStatements->Items as $objStatement){
			if ($objStatement->uriSubject == $About->uriSubject){			
				if ($objStatement->uriProperty == $objObjectProperty->Property->Uri){
					$Content .= pnlFormObjectPropertyRow($Form, $objObjectProperty, $objStatement, $boolGivePropertyLabel );
					
					$boolGivePropertyLabel = false;
					
				}
			}
		}
	}

	
	
	foreach ($Object->Relationships as $objRelationship){
		foreach ($Form->Revision->objStatements->Items as $objStatement){			
			if (!($objRelationship->Inverse) && ($objStatement->uriSubject != $About->uriSubject)){
			}
			elseif (($objRelationship->Inverse) && ($objStatement->uriLinkSubject != $About->uriSubject)){
			}
			else 
			{
				if ($objStatement->uriRelationship == $objRelationship->Relationship->Uri){
					
					$objLinkObject = $objRelationship->ToObject;
					$uriLinkSubject = $objStatement->uriLinkSubject;
					if ($objRelationship->Inverse){
						$uriLinkSubject = $objStatement->uriSubject;
					}

					$Content .= "<tr>";		
					$Content .= "<th>".$objRelationship->Label."</th>";
					
					$Content .= "<td>";
					
					if (!isset($Form->Revision->Abouts[$uriLinkSubject])){
//						$objSubject = new clsSubject($uriLinkSubject);
						$objSubject = $Shoc->getSubject($uriLinkSubject);
						$Content .= pnlSubject($objSubject);
					}
					else
					{
						$objLinkAbout = $Form->Revision->Abouts[$uriLinkSubject];
						$Content .= pnlFormObject($Form, $objLinkObject, $objLinkAbout);
					}

					$Content .= "</td>";
					$Content .= "</tr>";
					
				}				
			}
		}		
	}
	
	
	$Content .= "</table>";
	
	$Content .= "<td/></tr>";
	
	
	$Content .= "</table>";
		
	return $Content;
}



Function pnlFormObjectPropertyParts($Form, $ParentStatement, $Parts){
	
	$Content = '';
	$Content .= "<table>";
	
	foreach ($Parts as $objObjectProperty){
		$boolGivePropertyLabel = true;
		foreach ($Form->Revision->objStatements->Items as $objStatement){
			if ($objStatement->uriPartOf == $ParentStatement->Uri){			
				if ($objStatement->uriProperty == $objObjectProperty->Property->Uri){
					$Content .= pnlFormObjectPropertyRow($Form, $objObjectProperty, $objStatement, $boolGivePropertyLabel );
					$boolGivePropertyLabel = false;
				}
			}
		}
	}
	
	$Content .= "</table>";
	
	return $Content;
	
}


function pnlFormObjectPropertyRow($Form, $ObjectProperty, $Statement, $boolGivePropertyLabel = true){

	$Content = '';
	$Content .= "<tr>";
	if ($boolGivePropertyLabel){					
		$Content .= "<th>".$ObjectProperty->Property->Label."</th>";
	}
	else
	{
		$Content .= "<th/>";
	}
	$Content .= "<td>";
	if (count($ObjectProperty->Parts) > 0){
		$Content .= pnlFormObjectPropertyParts($Form, $Statement, $ObjectProperty->Parts);
	}
	else
	{
		$Content .= nl2br($Statement->ValueLabel);
	}
	
	$Content .= "</td>";
	$Content .= "</tr>";

	return $Content;
}



Function pnlFormLink($Form){
	
	$Content = '';
	
	if (is_null($Form->Revision)){
		return $Content;
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$objArchRel = null;
	$objRelationship = null;
	
	$objFromSubject = null;
	$objToSubject = null;
	
	
	$objDescriptionStatement = null;
	if (!is_null($Form->Revision->Document->ArchRel)){
		$objArchRel = $Form->Revision->Document->ArchRel;
		$objRelationship = $objArchRel->Relationship;
	}	
	
	
	$uriLink = null;
	$objLink = null;
	$arrAbouts = $Form->Revision->Abouts;
	if (count($arrAbouts) > 0){
		$objAbout = reset($arrAbouts);
		$uriLink = $objAbout->uriLink;
//		$objLink = new clsLink($uriLink);
		$objLink = $Shoc->getLink($uriLink);
	}
	
	if (!is_null($objArchRel)){
		if (!is_null($objLink)){
			switch ($objArchRel->Inverse){
				case false:
					$objFromSubject = $objLink->FromSubject;
					$objToSubject = $objLink->ToSubject;
					break;
				default:
					$objFromSubject = $objLink->ToSubject;
					$objToSubject = $objLink->FromSubject;
					break;					
			}
		}
		
	}	
	
	foreach ($Form->Revision->objStatements->Items as $objStatement){
		if ($objStatement->uriProperty == clsShoc::nsSHOC.'description'){
			$objDescriptionStatement = $objStatement;
		}
	}
	
	$Content .= "<table>";
	
	$Content .= "<tr>";
	$Content .= "<th>From Subject</th>";
	if (!is_null($objFromSubject)){
		$Content .= "<td>".$objFromSubject->Title."</td>";
		
	}	
	$Content .= "</tr>";

	
	$Content .= "<tr>";
	$Content .= "<th>Relationship</th>";
	if (!is_null($objRelationship)){
		$Content .= "<td>".$objArchRel->Label."</td>";		
	}	
	$Content .= "</tr>";
	
	
	$Content .= "<tr>";
	$Content .= "<th>To Subject</th>";
	if (!is_null($objToSubject)){
		$Content .= "<td>".$objToSubject->Title."</td>";
		
	}	
	$Content .= "</tr>";

	if (!is_null($objDescriptionStatement)){
		$Content .= "<tr>";
		$Content .= "<th>Description</th>";
		$Content .= "<td>".nl2br($objDescriptionStatement->Value)."</td>";
		$Content .= "</tr>";		
	}
	
	
	$Content .= "</table>";
		
	return $Content;
}


?>