<?php

function pnlSubjectsList($Subjects, $Class){

	$Content = '';

	$objList = new clsSubjectList($Subjects,$Class);
	$Content .= $objList->html;
		
	return $Content;

}
