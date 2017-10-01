<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlDocument($Document = null){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
//	$Content .= "<tr><th>uri</th><td><a href='document.php?uridocument=".$Document->Uri."'>".$Document->Uri."</a></td></tr>";
	$Content .= "<tr><th>Id</th><td><a href='document.php?uridocument=".$Document->Uri."'>".$Document->Id."</a></td></tr>";
	
	if (is_object($Document->Template)){
//		$Content .= "<tr><th>Template</th><td><a href='archetype.php?archetypeid=".$Document->TemplateId."'>".$Document->Template->Label."</a></td></tr>";
		$Content .= "<tr><th>Template</th><td>".$Document->Template->Label."</td></tr>";
	}
	
//	$Content .= "<tr><th>Template</th><td><a href='list.php?listid=$ListId'>".$objList->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}



?>