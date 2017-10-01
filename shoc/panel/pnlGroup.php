<?php

Function pnlGroup( $objGroup){
	
	
	$Content = '';

	$Content = "<div class='sdgreybox'>";

	if (!is_null($objGroup->Picture)) {
		$Content .= "<a href='group.php?groupid=".$objGroup->Id."'>";
		$Content .= '<img class="byimage" src="image.php?Id='.$objGroup->Picture.'" /><br/>';
		$Content .= "</a>";
	}
	
	$Content .= '<table>';
	if ($objGroup->Title == ""){
		$Content .= "<tr><th>Id</th><td><a href='group.php?urigroup=".$objGroup->Uri."'>".$objGroup->Id."</a></td></tr>";
	}
	$Content .= "<tr><th>Title</th><td><a href='group.php?urigroup=".$objGroup->Uri."'>".$objGroup->Title."</a></td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objGroup->Description)."</td></tr>";
	
    $Content .= '</table>';

    $Content .= '</div>';
    
    
    return $Content;
}


Function pnlGroupMember( $objMember){
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Name</th><td>".$objMember->User->Name."</td></tr>";
	$Content .= "<tr><th>Rights</th><td>".$objMember->Rights->Label."</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>