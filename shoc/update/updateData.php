<?php

require_once("class/clsSparql.php");
require_once("class/clsShocData.php");

$BASEURI = "http://data.shocdata.com/id";



function dataClearAll(){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
		
	$UserId = null;
	$UserId = $System->User->Id;

	$objSparql = new clsSparql();
			
	$SparqlUpdate = "
CLEAR DEFAULT";
			
	if (!($objSparql->Update($SparqlUpdate))){
		throw new exception("Cannot Clear the Default Graph");
	}

	return true;
	
}  	




function dataActivityUpdate($Mode, $Id = null, $TemplateId = null, $Title = null, $Description = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an activity');
	}
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;
	
	switch ($Mode) {
		case 'edit':
			
			$objActivity = $Shoc->getActivity($Id);
			
			break;
		case 'new':

			if (is_null($TemplateId)){
				throw new exception("Template not specified");
			}
	
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}

	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
	
	$uriActivity = null;
	
	switch ($Mode){
		case 'edit':
			
			$uriActivity = $Id;
			
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriActivity> 	shoc:template 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriActivity> 	shoc:title 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriActivity> 	shoc:description 	?o .
};";
			
			break;
		case 'new':

			$Id = uniqid();
			
			$uriActivity = $BASEURI . "/activity/" . $Id;
			$DateTime = date("c", time());

			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriActivity>	a 							shoc:Activity ;
      				shoc:id						".chr(34).$Id.chr(34)." ;  
    				dct:time					".chr(34).$DateTime.chr(34)."^^xsd:dateTime .    				
};";

			break;
	}

	
	switch ($Mode){
		case 'edit':
		case 'new':
	
			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriActivity>
  					shoc:template				".chr(34).$TemplateId.chr(34)." ;
  					shoc:title					".chr(34).chr(34).chr(34).$Title.chr(34).chr(34).chr(34)." ;
  					shoc:description			".chr(34).chr(34).chr(34).$Description.chr(34).chr(34).chr(34)." .}
			";
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
	}	

	
	switch ($Mode){
		case 'new':
// add the current user as an administrator
			dataActivityMemberUpdate('new', $uriActivity, 100, 100, $System->User->Id );			
			break;
	}
	
	
	
	
	return $uriActivity;
	
}  	


function dataActivityDelete($uri = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete an activity');
	}
	
	$UserId = $System->User->Id;

	$objActivity = $Shoc->getActivity($uri);
	
	$objSparql = new clsSparql();	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	$SparqlUpdate = "
DELETE WHERE
{ 
  <$uri> 	?p 	?o .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	
	return true;
	
}


function dataActivityMemberUpdate($Mode, $uriActivity, $Status = 2, $Rights = 1, $UserId ){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a member on an activity');
	}
		
	$objActivity = $Shoc->getActivity($uriActivity);
	
	global $BASEURI;
	
	switch ($Mode) {
		case 'edit':			
			
			break;
		case 'new':

			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}

	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
		

	$SparqlUpdate .= "
DELETE 
{ <$uriActivity> 	shoc:member		?member }
WHERE
{ ?member			shoc:user		".chr(34).$UserId.chr(34)."};";
	
	
	switch ($Mode){
		case 'new':
		case 'edit':
			
			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriActivity>	shoc:member 				[
  												shoc:user		".chr(34).$UserId.chr(34).";
  												shoc:status		".chr(34).$Status.chr(34).";
  												shoc:rights		".chr(34).$Rights.chr(34).";
  												];
};
";

			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}
			
			break;
	}
	  	
	return true;
	
}  	

function dataActivityMemberDelete($uriActivity, $UserId ){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to remove a member from an activity');
	}
		
	global $BASEURI;
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
		

	$SparqlUpdate .= "
DELETE 
{ <$uriActivity> 	shoc:member		?member }
WHERE
{ ?member			shoc:user		".chr(34).$UserId.chr(34)."};";

			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	  	
	return true;
	
}  	





function dataGroupUpdate($Mode, $Id = null, $uriActivity = null, $Title = null, $Description = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a group');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;
	
	switch ($Mode) {
		case 'edit':
			
			$objGroup = $Shoc->getGroup($Id);
			
			break;
		case 'new':

			if (is_null($uriActivity)){
				throw new exception("Activity not specified");
			}
	
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}

	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
		
	switch ($Mode){
		case 'edit':
			
			$uriGroup = $Id;
			
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriGroup> 	shoc:activity 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriGroup> 	shoc:title 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriGroup> 	shoc:description 	?o .
};";
			
			break;
		case 'new':

			$Id = uniqid();
			
			$uriGroup = $BASEURI . "/group/" . $Id;
			$DateTime = date("c", time());

			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriGroup>		a 							shoc:Group ;
      				shoc:id						".chr(34).$Id.chr(34)." ;  
    				dct:time					".chr(34).$DateTime.chr(34)."^^xsd:dateTime .    				
};";

			break;
	}

	
	switch ($Mode){
		case 'edit':
		case 'new':
	
			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriGroup>
  					shoc:activity				<$uriActivity> ;
  					shoc:title					".chr(34).chr(34).chr(34).$Title.chr(34).chr(34).chr(34)." ;
  					shoc:description			".chr(34).chr(34).chr(34).$Description.chr(34).chr(34).chr(34)." .
}";
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
	}	

	
	return $uriGroup;
	
}  	


function dataGroupDelete($uri = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a group');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = $System->User->Id;

	$objGroup = $Shoc->getGroup($uri);
	
	$objSparql = new clsSparql();	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	$SparqlUpdate = "
DELETE WHERE
{ 
  <$uri> 	?p 	?o .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	
	return true;
	
}


function dataGroupMemberUpdate($Mode, $uriGroup, $MemberId, $Rights = 1 ){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a group');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$objGroup = $Shoc->getGroup($uriGroup);
	
	global $BASEURI;
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
		

	$SparqlUpdate .= "
DELETE 
{ <$uriGroup> 	shoc:member		?member }
WHERE
{ ?member			shoc:user		".chr(34).$MemberId.chr(34)."};";
	
	
			
	$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriGroup>	shoc:member 	[
  									shoc:user		".chr(34).$MemberId.chr(34).";
  									shoc:rights		".chr(34).$Rights.chr(34).";
  								];
};";

			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	  	
	return true;
	
}


function dataGroupImageUpdate($Id = null, $ImageId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a group');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;
	
	$uriGroup = $Id;
	$objGroup = $Shoc->getGroup($Id);

	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
		
	$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriGroup> 	shoc:image 	?o .
};";
			
	$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriGroup>		shoc:image 					".chr(34).$ImageId.chr(34)." .
};";

	
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return true;
	
}  	




function dataBoxUpdate($Mode, $Id = null, $uriGroup = null, $Title = null, $Description = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a box');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;
	
	switch ($Mode) {
		case 'edit':
			
			$objBox = $Shoc->getBox($Id);
			if ($objBox === false){
				throw new exception("Box does not exist");
			}
/*						
			if (is_null($TypeId)){
				$TypeId = $objBox->TypeId;
			}
*/			
			break;
		case 'new':

			if (is_null($uriGroup)){
				throw new exception("Group does not specified");
			}
			$objGroup = $Shoc->getGroup($uriGroup);
	
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
	
	$uriBoxLink = null;
	
	switch ($Mode){
		case 'edit':
			
			$uriBox = $Id;

			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBox> 	shoc:title 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBox> 	shoc:description 	?o .
};";
			
			break;
		case 'new':

			$Id = uniqid();
			
			$uriBox = $BASEURI . "/box/" . $Id;
			$DateTime = date("c", time());

			$SparqlUpdate .= "			
INSERT DATA
{ 
	<$uriBox>	 	a					shoc:Box ;
    				shoc:id				".chr(34).$Id.chr(34)." ;  
  					shoc:group			<$uriGroup> ;
  					shoc:user			".chr(34).$UserId.chr(34)." ;
				  	dct:time			".chr(34).$DateTime.chr(34)."^^xsd:dateTime .
};";

			break;
	}
	
			
	$SparqlUpdate .= "
INSERT DATA
{ 
  <$uriBox>	 		shoc:title					".chr(34).chr(34).chr(34).$Title.chr(34).chr(34).chr(34)." ;
  					shoc:description			".chr(34).chr(34).chr(34).$Description.chr(34).chr(34).chr(34)." .
}";

	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	  	
	return $uriBox;
	
}  	



function dataBoxDelete($uri = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a box');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = $System->User->Id;

	$objBox = $Shoc->getBox($uri);
	
	$objSparql = new clsSparql();	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	$SparqlUpdate = "
DELETE WHERE
{ 
  <$uri> 	?p 	?o .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	
/*	
	foreach($objDocument->Revisions as $objRevision){

		$uriRevision = $objRevision->Uri;
		
		$objSparql = new clsSparql();	
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
		$SparqlUpdate = "
	DELETE WHERE
	{ 
	  <$uriRevision> 	?p 	?o .
	}";
				
		if (!($objSparql->Update($SparqlUpdate))){
			return false;
		}
		
		foreach($objRevision->Abouts as $objAbout){
	
			$uriSubject = $objAbout->uriSubject;

			$objSparql = new clsSparql();	
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
			$SparqlUpdate = "
		DELETE WHERE
		{ 
		  <$uriSubject> 	?p 	?o .
		}";
					
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}
		}
		

		foreach($objRevision->objStatements->Items as $objStatement){
	
			$uriStatement = $objStatement->Uri;

			$objSparql = new clsSparql();	
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
			$SparqlUpdate = "
		DELETE WHERE
		{ 
		  <$uriStatement> 	?p 	?o .
		}";
					
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}
		}
		
	}
*/
	return true;
	
}


function dataBoxObjectUpdate($Mode, $uriBox = null, $ObjectId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a box');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;

	$objBox = $Shoc->getBox($uriBox);
	if ($objBox === false){
		throw new exception("Box does not exist");
	}
	
	
	
	switch ($Mode){
		case 'add':

			$objSparql = new clsSparql();
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
			
			$SparqlUpdate = "
INSERT DATA
{ 
  <$uriBox>	 		shoc:object				".chr(34).$ObjectId.chr(34)." .
}";

			
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
			
		case 'remove':

						$objSparql = new clsSparql();
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
			
			$SparqlUpdate = "
DELETE WHERE
{ 
  <$uriBox>	 		shoc:object				".chr(34).$ObjectId.chr(34)." .
}";

			
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
			
			
	}	
	  	
	return true;
	
}  	




function dataBoxLinkUpdate($Mode, $Id = null, $uriBox = null, $uriRel = null, $ObjectId = null, $uriSubject = null,  $Inverse = false, $Description = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a box');
	}
	
	$UserId = null;
	$UserId = $System->User->Id;
	
	$xsdInverse = 'false';
	if ($Inverse){
		$xsdInverse = 'true';	
	}

	global $BASEURI;
	
	switch ($Mode) {
		case 'edit':
		case 'new':
			break;
		default:
			throw new exception("Invalid Mode");
			break;
	}

	$objSparql = new clsSparql();
	$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
	$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <".clsshoc::nsRDFS.">";			
	$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsshoc::nsDCT.">";
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	
	$SparqlUpdate = '';
	
	$uriBoxLink = null;
	
	switch ($Mode){
		case 'edit':
			
			$uriBoxLink = $Id;

			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:box 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:object 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:relationship 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:subject 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:inverse 	?o .
};";
			$SparqlUpdate .= "
DELETE WHERE
{ 
  <$uriBoxLink> 	shoc:description 	?o .
};";
			
			break;
		case 'new':

			$Id = uniqid();
			
			$uriBoxLink = $BASEURI . "/boxlink/" . $Id;
			$DateTime = date("c", time());

			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriBoxLink>		a 								shoc:BoxLink ;
      				shoc:id						".chr(34).$Id.chr(34)." ;  
    				dct:time					".chr(34).$DateTime.chr(34)."^^xsd:dateTime .  
    				
};";

			break;
	}

	
	switch ($Mode){
		case 'edit':
		case 'new':
	
			$SparqlUpdate .= "			
INSERT DATA
{ 
  <$uriBoxLink>  
      				shoc:box					<".$uriBox."> ;
			 		shoc:object					".chr(34).$ObjectId.chr(34)." ;
			 		shoc:relationship			<".$uriRel."> ;
			 		shoc:subject				<".$uriSubject."> ;
			 		shoc:inverse				".chr(34).$xsdInverse.chr(34)."^^xsd:boolean ;
  					shoc:description			".chr(34).chr(34).chr(34).$Description.chr(34).chr(34).chr(34)." .}
			";
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
	}	

	
	return $uriBoxLink;
	
}  	



function dataBoxLinkDelete($uri = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a box link');
	}
	
	$UserId = $System->User->Id;

	
	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
//	$objBoxLink = new clsBoxLink($uri);
	$objBoxLink = $Shoc->getBoxLink($uri);
	
	
	$objSparql = new clsSparql();	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <". clsShoc::prefixSHOC .">";
	$SparqlUpdate = "
DELETE WHERE
{ 
  <$uri> 	?p 	?o .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	
	return true;
	
}



function dataDocumentUpdate($Mode, $Id = null, $DocumentType = 'subject', $uriBox = null, $ObjectId = null, $ArchRelId = null ){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a document');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = null;
	$UserId = $System->User->Id;

	global $BASEURI;
		
	switch ($Mode) {
		case 'edit':
			
			$objDoc = $Shoc->getDocument($Id);
			if ($objDoc === false){
				throw new exception("Document does not exist");
			}
						
			if (is_null($SetId)){
				$SetId = $objDoc->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	
	switch ($Mode){
		case 'edit':
//			$sql = "update tbl_document set docSet=$SetId, docShape = $ShapeId WHERE docRecnum = $Id";
//			$result = $System->DbExecute($sql);
			break;
		case 'new':

			$Id = uniqid();
			
			$uriDocument = $BASEURI . "/document/" . $Id;
			$DateTime = date("c", time());
			
			$objSparql = new clsSparql();
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
			
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";						
			
			$SparqlUpdate = "
INSERT DATA
{ 
  <$uriDocument> 	a 							shoc:Document ;
    				shoc:id						".chr(34).$Id.chr(34)." ;  ";

			
			if (!is_null($uriBox)){
				$SparqlUpdate .= "
  					shoc:box					 <".$uriBox."> ; ";
			}
			

			if (!is_null($DocumentType)){
				$SparqlUpdate .= "
  					shoc:documentType			".chr(34).$DocumentType.chr(34)." ; ";
			}
			

			if (!is_null($ObjectId)){
				$SparqlUpdate .= "
  					shoc:object					".chr(34).$ObjectId.chr(34)." ; ";
			}

			if (!is_null($ArchRelId)){
				$SparqlUpdate .= "
  					shoc:archrel					".chr(34).$ArchRelId.chr(34)." ; ";
			}
						
			$SparqlUpdate .= "
  					dct:time					".chr(34).$DateTime.chr(34)."^^xsd:dateTime .
}
			";
			
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}

			break;
	}	
	  	
	return $uriDocument;
	
}  	



function dataDocumentDelete($uri = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set');
	}

	global $Shoc;
	if (!isset($Shoc)){
		$Shoc = new clsShoc();
	}
	
	
	$UserId = $System->User->Id;

	$objDocument = $Shoc->getDocument($uri);
	
	$objSparql = new clsSparql();	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
	$SparqlUpdate = "
DELETE WHERE
{ 
  <$uri> 	?p 	?o .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}
	
	
	foreach($objDocument->Revisions as $objRevision){

		$uriRevision = $objRevision->Uri;
		
		$objSparql = new clsSparql();	
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
		$SparqlUpdate = "
	DELETE WHERE
	{ 
	  <$uriRevision> 	?p 	?o .
	}";
				
		if (!($objSparql->Update($SparqlUpdate))){
			return false;
		}
		
		foreach($objRevision->Abouts as $objAbout){
	
			$uriSubject = $objAbout->uriSubject;

			$objSparql = new clsSparql();	
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
			$SparqlUpdate = "
		DELETE WHERE
		{ 
		  <$uriSubject> 	?p 	?o .
		}";
					
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}
		}
		

		foreach($objRevision->objStatements->Items as $objStatement){
	
			$uriStatement = $objStatement->Uri;

			$objSparql = new clsSparql();	
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";		
			$SparqlUpdate = "
		DELETE WHERE
		{ 
		  <$uriStatement> 	?p 	?o .
		}";
					
			if (!($objSparql->Update($SparqlUpdate))){
				return false;
			}
		}
		
	}

	return true;
	
}


function dataRevision($uriDocument, $Action = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	
	$UserId = $System->User->Id;
	
	global $BASEURI;
	
	$DateTime = date("c", time());
	
		
	$objSparql = new clsSparql();
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
	$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
	
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
	$Id = uniqid();

	$uriRevision = $BASEURI . "/revision/" . $Id;
	
			
	$SparqlUpdate = "
	INSERT DATA
{ 
  <$uriRevision> 	a 							shoc:Revision ;
      				shoc:id						".chr(34).$Id.chr(34)." ;
  					shoc:document				<$uriDocument>;
  					shoc:user					".chr(34).$UserId.chr(34)." ;
 ";
	
	if (!is_null($Action)){
		$SparqlUpdate .= "
					shoc:action					".chr(34).$Action.chr(34)." ;
";		
	}
	
	$SparqlUpdate .= "	
  					dct:time					".chr(34).$DateTime.chr(34)."^^xsd:dateTime .
}
			";	
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriRevision;
	
} 


function dataSubject($uriClass){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

	global $BASEURI;
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
	$Id = uniqid();
	$uriSubject = $BASEURI . "/subject/" . $Id;
	
			
	$SparqlUpdate = "
INSERT DATA
{ 
  <$uriSubject> 	a 				shoc:Subject .
  <$uriSubject>		shoc:class		<$uriClass> .
  <$uriSubject>     shoc:id			".chr(34).$Id.chr(34)." .
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriSubject;
	
}  	


function xxxdataSubject($uriClass){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

	global $BASEURI;
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
	$Id = uniqid();
	$uriSubject = $BASEURI . "/subject/" . $Id;
	
			
	$SparqlUpdate = "
INSERT DATA
{ 
  <$uriSubject> 	a 				<$uriClass> .
  <$uriSubject>     shoc:id			".chr(34).$Id.chr(34)." ;  
}";
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriSubject;
	
}  	


function dataAbout($uriRevision, $uriSubject, $idObject){

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

//	$BaseUri = "http://data.sedgemoor.gov.uk/ecosystem";
//	$DateTime = date("c", time());
	
		
	$objSparql = new clsSparql();
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
			
	$SparqlUpdate = "
	INSERT DATA
{ 
  <$uriRevision> 	shoc:about		[
  										a					shoc:About;
  										shoc:subject		<$uriSubject>;
  										shoc:idObject		".chr(34).$idObject.chr(34)."
  									] .
}";	
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return true;
	
}


function dataLink( $uriRel = null, $idRel = null, $uriFromSubject, $uriToSubject){

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a link');
	}

	
	global $BASEURI;
			
	$Id = uniqid();
	$uriLink = $BASEURI . "/link/" . $Id;
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
			
	$SparqlUpdate = "
	INSERT DATA
{ 
  <$uriLink> 	a			shoc:Link;
  				shoc:fromSubject	<$uriFromSubject>;
  				shoc:toSubject		<$uriToSubject>;
 ";
	if (!is_null($idRel)){
		$SparqlUpdate .= "
  				shoc:idRel			".chr(34).$idRel.chr(34)." ;
";
	}
	if (!is_null($uriRel)){
		$SparqlUpdate .= "
		  		shoc:relationship		<$uriRel>;
";
	}
	$SparqlUpdate .= ".";
	
	$SparqlUpdate .= "  				
  				
}";	
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriLink;
	
}



function dataAboutLink($uriRevision, $uriLink, $idArchRel){

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a link');
	}
	
	$UserId = $System->User->Id;

	$objSparql = new clsSparql();
	$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
		
			
	$SparqlUpdate = "
	INSERT DATA
{ 
  <$uriRevision> 	shoc:about		[
  										a					shoc:About;
   										shoc:link			<$uriLink>;										
  										shoc:idArchRel		".chr(34).$idArchRel.chr(34)."
  									] .
}";	
			
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return true;
	
}


function dataStatement($uriAbout = null, $uriProperty, $uriParentStatement = null, $Value = null, $uriDataType = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

	global $BASEURI;
	
	$uriStatement = $BASEURI . "/statement/" . uniqid();
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
	
			
	$SparqlUpdate = "
INSERT DATA
{ 
  <$uriStatement>
  			 	a 						shoc:Statement ;
";
	
	if (!is_null($uriAbout)){
		$SparqlUpdate .= "
	
  			 	shoc:subject			<$uriAbout> ;
";
	}

	$SparqlUpdate .= "	
  			 	shoc:property			<$uriProperty> ;
";

	
	if (!is_null($uriParentStatement)){
		$SparqlUpdate .= "
  			 	shoc:partOf				<$uriParentStatement> ;
";
	}
	
	
	if (!is_null($Value)){
		$SparqlUpdate .= "
  			 	shoc:value				".chr(34).chr(34).chr(34).$Value.chr(34).chr(34).chr(34).";
";
	}
	  			 	
	$SparqlUpdate .= "	
	.	
}";
	
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriStatement;

}  	


function dataLinkStatement($uriAbout, $uriRelationship, $uriLink){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

	global $BASEURI;
	
	
	$uriStatement = $BASEURI . "/statement/" . uniqid();
	
	$objSparql = new clsSparql();
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
	
			
	$SparqlUpdate = "
INSERT DATA
{ 
  <$uriStatement>
  			 	a 						shoc:Statement ;
  			 	shoc:subject			<$uriAbout> ;
  			 	shoc:relationship		<$uriRelationship> ;
  			 	shoc:linkSubject		<$uriLink> ;
	.	
}";
	
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriStatement;

}  	


function dataRevisionStatement($uriRevision, $uriStatement){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
//	if (!$System->LoggedOn){
//		throw new exception('You must be logged on to update a set');
//	}
	
	$UserId = null;
//	$UserId = $System->User->Id;

	global $BASEURI;
		
	$objSparql = new clsSparql();
	$objSparql->Prefixes['shoc'] = "PREFIX shoc: <http://data.shocdata.com/def/>";
	
			
	$SparqlUpdate = "
INSERT DATA
{ 
  <$uriRevision>
  			 	shoc:statement			<$uriStatement> ;
	.	
}";
	
	if (!($objSparql->Update($SparqlUpdate))){
		return false;
	}

	return $uriStatement;

}  	

?>