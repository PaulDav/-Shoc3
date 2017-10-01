<?php

require_once("path.php");

require_once('class/clsSystem.php');
require_once('function/utils.inc');

require_once('class/clsModel.php');

Function pnlRevision($Revision = null){

	global $Models;
	if (!isset($Models)){
		$Models = new clsModels;
	}

	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
//	$Content .= "<tr><th>uri</th><td><a href='document.php?urirevision=".$Revision->Uri."'>".$Revision->Uri."</a></td></tr>";
	$Content .= "<tr><th>Id</th><td><a href='document.php?urirevision=".$Revision->Uri."'>".$Revision->Id."</a></td></tr>";
	$Content .= "<tr><th>Revision Number</th><td>".$Revision->Number."</td></tr>";
	$Content .= "<tr><th>Date/Time</th><td>".date('d/m/Y H:i:s',$Revision->Timestamp)."</td></tr>";
	$Content .= "<tr><th>By</th><td>";
	if (!is_null($Revision->User)){
		if (!is_null($Revision->User->PictureOf)) {
			$Content .= '<img height = "20" src="image.php?Id='.$Revision->User->PictureOf.'" alt="'.$Revision->User->Name.'" /><br/>'."\n";
		}
		$Content .= $Revision->User->Name;
	}
	$Content .= "</td></tr>";
		
//	$Content .= "<tr><th>Template</th><td><a href='list.php?listid=$ListId'>".$objList->Name."</a></td></tr>";
	
	$Content .= '</table>';
	$Content .= '</div>';

	return $Content;
}



?>