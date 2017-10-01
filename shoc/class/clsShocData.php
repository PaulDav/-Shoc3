<?php

require_once("class/clsSystem.php");
require_once("class/clsGraph.php");
require_once("class/clsSparql.php");

require_once("class/clsShocList.php");

require_once("function/utils.inc");


class clsShoc {
	
	const nsSHOC = "http://data.shocdata.com/schema#";
	
	const nsRDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	const nsRDFS = 'http://www.w3.org/2000/01/rdf-schema#';
	const nsXSD = 'http://www.w3.org/2001/XMLSchema#';
	const nsDCT = 'http://purl.org/dc/terms/';
	
	const prefixSHOC = "http://data.shocdata.com/def/";
	
	private $Objects = array();
	
	public function __construct(){
		
	}

	public function getActivity($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsActivity($uri);
		}
		return $this->Objects[$uri];
		
	}

	public function getBox($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsBox($uri);
		}
		return $this->Objects[$uri];
		
	}
	
	public function getGroup($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsGroup($uri);
		}
		return $this->Objects[$uri];
		
	}
	
	public function getDocument($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsDocument($uri);
		}
		return $this->Objects[$uri];
		
	}

	
	public function getRevision($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsRevision($uri);
		}
		return $this->Objects[$uri];
		
	}

	public function getSubject($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsSubject($uri);
		}
		return $this->Objects[$uri];		
	}	

	public function getLink($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsLink($uri);
		}
		return $this->Objects[$uri];		
	}	

	public function getBoxLink($uri){
		if (!isset($this->Objects[$uri])){
			$this->Objects[$uri] = new clsBoxLink($uri);
		}
		return $this->Objects[$uri];		
	}	
	
}


function gObjects(){
	global $gObjects;
	if (!isset($gObjects)){
		$gObjects = new clsObjects();
	}		
//	return $gObjects;
}

class clsObjects{
	public $Items = array();
}

// ----------




class clsActivities {
	
//	const nsSHOC = "http://data.shocdata.com/schema/";
	
//	const prefixSHOC = "http://data.shocdata.com/def/";
		
	
//	const nsRDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
//	const nsRDFS = 'http://www.w3.org/2000/01/rdf-schema#';
//	const nsXSD = 'http://www.w3.org/2001/XMLSchema#';
//	const nsDCT = 'http://purl.org/dc/terms/';
	
	
	
	private $Items = null;
	
	public $MemberId = null;
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Shoc = null;
	
	private $Models = null;
	private $Archetypes = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		
		$gObjects = gObjects();
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		
	}
	
	public function getItems(){
		
		if (!is_null($this->Items)){
			return $this->Items;
		}

		$this->Items = array();
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <".clsShoc::nsRDFS.">";
		$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsShoc::nsDCT.">";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uri WHERE {
			?uri a shoc:Activity.
			";

		if (!is_null($this->MemberId)){
			$Query .= "
			?uri 			shoc:member 		?member .
			?member			shoc:user			" . chr(34) . $this->MemberId . chr(34) ." .
			?member			shoc:status			" . chr(34) . '100' . chr(34). " .";
		}
		
		
		$Query .= "}";
				
		$xmlResults = $objSparql->Query($Query);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
				
		$this->Refresh();
		
	}
	
	public function __get($name){
		switch ($name){
			case "Models":
				$this->getModels();
				break;
			case "Archetypes":
				$this->getArchetypes();
				break;
			case "Items":
				$this->getItems();
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){
		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	

	private function getArchetypes(){
		if (!is_null($this->Archetypes)){
			return $this->Archetypes;
		}
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes();
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}

	public function refresh(){
		
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){
			$uriActivity = $xmlUri->nodeValue;
			$objActivity = $this->Shoc->getActivity($uriActivity);
			$this->Items[$uriActivity] = $objActivity;
		}
	}
		
}



class clsActivity {

	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	private $xmlUri = null;
		
	public $Uri = null;
	public $Id = null;
	public $Title = null;
	public $Description = null;
	private $Template = null;

	public $Members = array();
	private $Groups = null;
	
	private $MyRights = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Models = null;
	private $Archetypes = null;
	
	private $dot = null;

		
	public function __construct($Uri = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		$gObjects = gObjects();

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->Refresh();
		
		$this->getMyRights();
		
		switch ($this->MyRights->Id){
			case 1;
				$this->canView = true;
				break;
			case 100;
				$this->canView = true;
				$this->canEdit = true;
				$this->canControl = true;
				break;				
		}
		
		$gObjects->Items[$Uri] = $this;
						
	}	

	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function refresh(){

		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){
			
			$this->xmlUri = $xmlUri;
			
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			

			if ($xmlTitle = $this->xpath->query("shoc:title[1]", $xmlUri)->item(0)){
				$this->Title = $xmlTitle->nodeValue;
			}			
			if ($xmlDescription = $this->xpath->query("shoc:description[1]", $xmlUri)->item(0)){
				$this->Description = $xmlDescription->nodeValue;
			}			

			
			foreach ($this->xpath->query("shoc:member", $xmlUri) as $xmlMember){
				$objMember =new clsActivityMember();
				if ($xmlUserId = $this->xpath->query("shoc:user[1]", $xmlMember)->item(0)){
					$objMember->User = new clsUser($xmlUserId->nodeValue);
				}			
				if ($xmlStatusId = $this->xpath->query("shoc:status[1]", $xmlMember)->item(0)){
					$StatusId = $xmlStatusId->nodeValue;
					if (isset($this->System->Config->UserStatus[$StatusId])){
						$objMember->Status = $this->System->Config->UserStatus[$StatusId];
					}
				}
				
				if ($xmlRightsId = $this->xpath->query("shoc:rights[1]", $xmlMember)->item(0)){
					$RightsId = $xmlRightsId->nodeValue;
					if (isset($this->System->Config->ActivityMemberRights[$RightsId])){
						$objMember->Rights = $this->System->Config->ActivityMemberRights[$RightsId];
					}
				}			
				
				$this->Members[$objMember->User->Id] = $objMember;
			}						
			
		}
		
	}
	
	
	
	public function __get($name){
		switch ($name){
			case 'Models':
				$this->Models = $this->getModels();
				break;			
			case 'Archetypes':
				$this->Archetypes = $this->getArchetypes();
				break;
			case 'Template':
				$this->getTemplate();
				break;
			case 'Groups':
				$this->getGroups();
				break;
				
			case 'Documents':
				$this->getDocuments();
				break;
			case 'Subjects':
				$this->getSubjects();
				break;
			case 'Links':
				$this->getLinks();
				break;
				
			case 'MyRights':
				$this->getMyRights();
				break;
				
			case 'dot':
				$this->getDot();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	public function __set($name, $value){
		switch ($name){
			case 'Models':
				$this->Models = $value;
				break;
		}
	}
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}



	
	private function getTemplate(){
		if (!is_null($this->Template)){
			return $this->Template;
		}
		global $gObjects;
		
		$this->getArchetypes();
		$xmlTemplateId = $this->xpath->query("shoc:template[1]", $this->xmlUri)->item(0);
		if ($xmlTemplateId){
			$TemplateId = $xmlTemplateId->nodeValue;
			$this->Template = $this->Archetypes->getItem($TemplateId);			
		}			
		
		return $this->Template;
		
	}
	

	
	private function getGroups(){

		if (!is_null($this->Groups)){
			return $this->Groups;
		}
		global $gObjects;

		$objGroups = new clsGroups();
		$objGroups->uriActivity = $this->Uri;
		$objGroups->getItems();
		$this->Groups = $objGroups->Items;
		
		return $this->Groups;
		
	}
	
		
	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	private function getMyRights(){
		if (!is_null($this->MyRights)){
			return $this->MyRights;
		}
		
		$this->MyRights = new clsMemberRights();
		
		if ($this->System->LoggedOn){
			if (isset($this->Members[$this->System->User->Id])){
				$objActivityMember = $this->Members[$this->System->User->Id];
				if ($objActivityMember->Status->Id == 100){
					$this->MyRights = $objActivityMember->Rights;
				}
			}
		}
		
		return $this->MyRights;
		
	}
	
}



class clsActivityMember{
	
	public $User = null;
	public $Status = null;
	public $Rights = null;	
	
}




class clsGroups {
	
	private $Items = null;
	
	public $uriActivity = null;
	public $MemberId = null;
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;
	private $Archetypes = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		
	}
	
	public function getItems(){

		if (!is_null($this->Items)){
			return $this->Items;
		}

		$this->Items = array();
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uri WHERE {
			?uri a shoc:Group.
			";

		
		if (!is_null($this->uriActivity)){
			$Query .= "
			?uri 			shoc:activity		<".$this->uriActivity."> .";
		}
		
		
		if (!is_null($this->MemberId)){
			$Query .= "
			?uri 			shoc:member 		?member .
			?member			shoc:user			" . chr(34) . $this->MemberId . chr(34) ." .			
			?uri			shoc:activity		?activity .
			?activity		shoc:member			?activitymember .
			?activitymember	shoc:user			" . chr(34) . $this->MemberId . chr(34) ." .			
			?activitymember	shoc:status			" . chr(34) . '100' . chr(34). " .";
		}

		$Query .= "}";
				
		$xmlResults = $objSparql->Query($Query);
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
				
		$this->Refresh();
		
	}
	
	public function __get($name){
		switch ($name){
			case "Models":
				$this->getModels();
				break;
			case "Archetypes":
				$this->getArchetypes();
				break;
			case "Items":
				$this->getItems();
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){
		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	

	private function getArchetypes(){
		if (!is_null($this->Archetypes)){
			return $this->Archetypes;
		}
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes();
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}

	public function refresh(){
		
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){
			$uriGroup = $xmlUri->nodeValue;
			$objGroup = $this->Shoc->getGroup($uriGroup);
			$this->Items[$uriGroup] = $objGroup;
		}
	}
		
}



class clsGroup {

	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	private $xmlUri = null;
		
	public $Uri = null;
	public $Id = null;
	public $Title = null;
	public $Description = null;
	
	public $Picture = null;

	public $Members = array();
	public $MyMembership = null;

	private $uriActivity = null;
	private $Activity = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	
	private $Models = null;
	private $Archetypes = null;
	
	private $dot = null;

		
	public function __construct($Uri = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		

		$this->Refresh();

		$this->getActivity();
		if ($this->Activity->canControl){
			$this->canView = true;
			$this->canEdit = true;
			$this->canControl = true;
		}
		else
		{
			if (!is_null($this->MyMembership)){
				$this->canView = true;				
			}
		}		
		
		$gObjects->Items[$Uri] = $this;
						
	}	

	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function refresh(){

		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){
			
			$this->xmlUri = $xmlUri;
			
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			

			if ($xmlTitle = $this->xpath->query("shoc:title[1]", $xmlUri)->item(0)){
				$this->Title = $xmlTitle->nodeValue;
			}			
			if ($xmlDescription = $this->xpath->query("shoc:description[1]", $xmlUri)->item(0)){
				$this->Description = $xmlDescription->nodeValue;
			}

			if ($xmlPicture = $this->xpath->query("shoc:image[1]", $xmlUri)->item(0)){
				$this->Picture = $xmlPicture->nodeValue;
			}
			
			
			foreach ($this->xpath->query("shoc:member", $xmlUri) as $xmlMember){
				$objMember =new clsGroupMember();
				$objMember->Status = $this->System->Config->UserStatus[100];
				if ($xmlUserId = $this->xpath->query("shoc:user[1]", $xmlMember)->item(0)){
					$objMember->User = new clsUser($xmlUserId->nodeValue);
				}			
				
				if ($xmlRightsId = $this->xpath->query("shoc:rights[1]", $xmlMember)->item(0)){
					$RightsId = $xmlRightsId->nodeValue;
					if (isset($this->System->Config->GroupMemberRights[$RightsId])){
						$objMember->Rights = $this->System->Config->GroupMemberRights[$RightsId];
					}
				}			
				
				$this->Members[$objMember->User->Id] = $objMember;
				if ($this->System->LoggedOn){
					if ($objMember->User->Id == $this->System->User->Id){
						$this->MyMembership = $objMember;
					}
				}
			}						
			
		}
		
	}
	
	
	
	public function __get($name){
		switch ($name){
			case 'Models':
				$this->Models = $this->getModels();
				break;			
			case 'Archetypes':
				$this->Archetypes = $this->getArchetypes();
				break;
			case 'Activity':
				$this->getActivity();
				break;				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	public function __set($name, $value){
		switch ($name){
			case 'Models':
				$this->Models = $value;
				break;
		}
	}
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}



	
	private function getActivity(){
		if (!is_null($this->Activity)){
			return $this->Activity;
		}
		global $gObjects;
		
		$xmlActivity = $this->xpath->query("shoc:activity[1]", $this->xmlUri)->item(0);
		if ($xmlActivity){
			$uriActivity = $xmlActivity->getAttribute("rdf:resource");
			$this->Activity = $this->Shoc->getActivity($uriActivity);
		}			
				
		return $this->Activity;
		
	}
	

		
	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	
}


class clsGroupMember{
	
	public $User = null;
	public $Status = null;
	public $Rights = null;	
	
}









class clsBoxes {
	
	
	private $Items = null;
	
	public $uriActivity = null;
	public $MemberId = null;
	public $MemberRightsId = null;
	public $uriGroup = null;
	public $ObjectId = null;
	public $UserId = null;
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
			
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		
	}
	
	public function getItems(){
		
		if (!is_null($this->Items)){
			return $this->Items;
		}

		$this->Items = array();
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT DISTINCT ?uri WHERE {
			?uri a shoc:Box.
			";

		$Query .= "
			OPTIONAL { ?uri 			shoc:group 			?group } .
			";
		

		if (!is_null($this->ObjectId)){
			$Query .= "
			?uri 			shoc:object		".chr(34).$this->ObjectId.chr(34)." .
			";
		}
		
		
		if (!is_null($this->uriActivity)){
			$Query .= "
			?group 			shoc:activity		<".$this->uriActivity."> .
			";
		}
		
		
		if (!is_null($this->MemberId)){
			$Query .= "
			?group 			shoc:member 		?member .
			?member			shoc:user			" . chr(34) . $this->MemberId . chr(34) ." .
			
			?group			shoc:activity		?activity .
			?activity		shoc:member			?activitymember .
			?activitymember	shoc:user			" . chr(34) . $this->MemberId . chr(34) ." .			
			?activitymember	shoc:status			" . chr(34) . '100' . chr(34). " .
			
			";
			
			if (!is_null($this->MemberRightsId)){
				$Query .= "
			?member			shoc:rights			" . chr(34) . $this->MemberRightsId . chr(34). " .
			";
			}
		}
		
		
		if (!is_null($this->uriGroup)){
			$Query .= "?uri shoc:group <" . $this->uriGroup . "> .
			"; 			
		}
		
		if (!is_null($this->UserId)){
			$Query .= "
			?uri shoc:user " . chr(34) . $this->UserId . chr(34) .".
			";
		}
		
		$Query .= "}";
				
		$xmlResults = $objSparql->Query($Query);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
				
		$this->Refresh();
		
	}
	
	public function __get($name){
		switch ($name){
			case "Models":
				$this->getModels();
				break;
			case "Items":
				$this->getItems();
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){
		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}

	public function refresh(){
		
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){			
			$uriBox = $xmlUri->nodeValue;
			$objBox = $this->Shoc->getBox($uriBox);
			$this->Items[$uriBox] = $objBox;
		}
	}
	
	
}



class clsBox {

	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	private $xmlUri = null;
		
	public $Uri = null;
	public $Id = null;
	public $Title = null;
	public $Description = null;

	private $Objects = null;	
	
	public $uriGroup = null;
	private $Group = null;
	
	private $Documents = null;
	private $Subjects = null;
	private $Links = null;
	
	private $BoxLinks = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	
	private $Models = null;
	private $Archetypes = null;
	
	private $dot = null;

		
	public function __construct($Uri = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);

		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			
			
			$xmlGroup = $this->xpath->query("shoc:group[1]", $xmlUri)->item(0);
			if ($xmlGroup){
				$this->uriGroup = $xmlGroup->getAttribute("rdf:resource");
			}			
			
			if ($xmlTitle = $this->xpath->query("shoc:title[1]", $xmlUri)->item(0)){
				$this->Title = $xmlTitle->nodeValue;
			}			
			if ($xmlDescription = $this->xpath->query("shoc:description[1]", $xmlUri)->item(0)){
				$this->Description = $xmlDescription->nodeValue;
			}			
			
			
		}
		$this->xmlUri = $xmlUri;

		
		$this->getGroup();
		if ($this->Group->Activity->canControl){
			$this->canView = true;
			$this->canEdit = true;
			$this->canControl = true;
		}
		else
		{
			if ($this->Group->Activity->canView){
				$this->canView = true;
			}
			if (!is_null($this->Group->MyMembership)){
				$this->canView = true;
				if ($this->Group->MyMembership->Rights->Id >= 100){
					$this->canEdit = true;					
				}
			}
		}
		
		
		$gObjects->Items[$Uri] = $this;
						
	}	

	
	public function __get($name){
		switch ($name){
			case 'Models':
				$this->Models = $this->getModels();
				break;			
			case 'Archetypes':
				$this->Archetypes = $this->getArchetypes();
				break;
			case 'Objects':
				$this->getObjects();
				break;
			case 'Group':
				$this->getGroup();
				break;
				
			case 'Documents':
				$this->getDocuments();
				break;
			case 'Subjects':
				$this->getSubjects();
				break;
			case 'Links':
				$this->getLinks();
				break;

			case 'BoxLinks':
				$this->getBoxLinks();
				break;
				
			case 'dot':
				$this->getDot();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	public function __set($name, $value){
		switch ($name){
			case 'Models':
				$this->Models = $value;
				break;
		}
	}
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}


	private function getDocuments(){
		if (!is_null($this->Documents)){
			return $this->Documents;
		}
		
		global $gObjects;

		$this->Documents = array();
				
		$uriBox = $this->Uri;
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uriDocument WHERE {
			?uriDocument	a			shoc:Document.
			?uriDocument 	shoc:box 	<$uriBox>.
			}";
		$xmlResults = $objSparql->Query($Query);
		
		$domRevisions = new DOMDocument('1.0', 'utf-8');
		$domRevisions->formatOutput = true;
		$domRevisions->loadXMl($xmlResults);

		$xpathRevisions = new domxpath($domRevisions);
		
		
		$xpathRevisions->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpathRevisions->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpathRevisions->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpathRevisions->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpathRevisions->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpathRevisions->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$nodelistDocuments = $xpathRevisions->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uriDocument']/sparql:uri");
		
		foreach ($nodelistDocuments as $xmlUriDocument){
			$uriDocument = $xmlUriDocument->nodeValue;
			
			$objDocument = $this->Shoc->getDocument($uriDocument);
									
			$this->Documents[$uriDocument] = $objDocument;
		}
		
		return $this->Documents;
		
	}
	
	
	private function getObjects(){
		
		if (!is_null($this->Objects)){
			return $this->Objects;
		}
		
		global $gObjects;

		$this->getGroup();
		
		$this->Objects = array();

		foreach ($this->xpath->query("shoc:object", $this->xmlUri) as $xmlObjectId){
			$ObjectId = $xmlObjectId->nodeValue;
			
			if (isset($this->Group->Activity->Template->Objects[$ObjectId])){			
				$this->Objects[$ObjectId] = $this->Group->Activity->Template->Objects[$ObjectId];
			}
		}		
		
		return $this->Objects;
		
	}
	
	
	private function getSubjects(){
		if (!is_null($this->Subjects)){
			return $this->Subjects;
		}
		
		global $gObjects;

		$this->Subjects = array();
		foreach ($this->getDocuments() as $objDocument){
			if (!is_null($objDocument->CurrentRevision)){
				foreach ($objDocument->CurrentRevision->Abouts as $objAbout){
					
					if (!is_null($objAbout->uriSubject)){
						if (!isset($this->Subjects[$objAbout->uriSubject])){
//							$objSubject = new clsSubject($objAbout->uriSubject);
							$objSubject = $this->Shoc->getSubject($objAbout->uriSubject);
//							$objSubject->Object = $objAbout->Object;
							$this->Subjects[$objSubject->Uri] = $objSubject;
						}
					}
				}
			}
		}				
		
		return $this->Subjects;
		
	}
	

	private function getLinks(){
		if (!is_null($this->Links)){
			return $this->Links;
		}
		
		$gObjects = gObjects();
		$this->Links = array();
		
		$this->getDocuments();
		
		foreach ($this->Documents as $objDocument){
			if (!is_object($objDocument->CurrentRevision)){
				continue;
			}
			
			if ($objDocument->CurrentRevision->Action == 'remove'){
				continue;
			};
			
			foreach ($objDocument->CurrentRevision->Abouts as $objAbout){
				
				if (!is_null($objAbout->uriLink)){
//					$objLink = new clsLink($objAbout->uriLink);
					$objLink = $this->Shoc->getLink($objAbout->uriLink);					
					$this->Links[] = $objLink;
				}
				
			}
		}
			

// box links

		$this->getBoxLinks();
		foreach ($this->BoxLinks as $objBoxLink){
			foreach ($this->getDocuments() as $objDocument){
				if (!is_null($objDocument->CurrentRevision)){
					foreach ($objDocument->CurrentRevision->Abouts as $objAbout){
						if ($objAbout->idObject == $objBoxLink->idObject){
							$objLink = new clsLink();
							
							$objLink->BoxLink = $objBoxLink;
							
							$objLink->Uri = $this->Uri;
				
							switch($objBoxLink->Inverse){
								case false:
									$objLink->uriFromSubject = $objAbout->uriSubject;
									$objLink->uriToSubject = $objBoxLink->Subject->Uri;
									break;
								default:
									$objLink->uriFromSubject = $objBoxLink->Subject->Uri;
									$objLink->uriToSubject = $objAbout->uriSubject;
									break;
							}
				
							$objLink->uriRelationship = $objBoxLink->uriRelationship;
							
							$objLink->Description = $objBoxLink->Description;			
							
							$this->Links[] = $objLink;
						}
						
					}
				}
			}
		}
		
		return $this->Links;
	}
		
	
	private function getBoxLinks(){
		
		if (!is_null($this->BoxLinks)){
			return $this->BoxLinks;
		}
		
		global $gObjects;

		$this->BoxLinks = array();
				
		$uriBox = $this->Uri;
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uriBoxLink WHERE {
			?uriBoxLink		a		shoc:BoxLink.
			?uriBoxLink 	shoc:box <$uriBox>.
			}";
		$xmlResults = $objSparql->Query($Query);

		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->loadXMl($xmlResults);

		$xpath = new domxpath($dom);
		
		
		$xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$nodelist = $xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uriBoxLink']/sparql:uri");
		
		foreach ($nodelist as $xmlUri){
			$uriBoxLink = $xmlUri->nodeValue;
			
			if (isset($gObjects->Items[$uriBoxLink])){
				$objBoxLink = $gObjects->Items[$uriBoxLink];
			}
			else 
			{
//				$objBoxLink = new clsBoxLink($uriBoxLink);
				$objBoxLink = $this->Shoc->getBoxLink($uriBoxLink);			
			}
						
			$this->BoxLinks[$uriBoxLink] = $objBoxLink;
		}
		
		return $this->BoxLinks;
		
	}
	
	
	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	private function getType(){

		if (!is_null($this->Type)){
			return $this->Type;
		}
		if (is_null($this->TypeId)){
			return null;
		}
		
		$this->getModels();

		if (isset($this->Models->Items[$this->TypeId])){
			$this->Type = $this->Models->Items[$this->TypeId];
		}
		return $this->Type;
	}
	
	private function getGroup(){

		if (!is_null($this->Group)){
			return $this->Group;
		}
		if (empty($this->uriGroup)){
			return null;
		}
		$this->Group = $this->Shoc->getGroup($this->uriGroup);
		return $this->Group;
	}
	
	
	public function getDot($objDot = null, $objGraph = null){
		
		if (!is_null($this->dot)){
			return $this->dot;
		}
		$this->dot = '';

		if (is_null($objDot)){
			$objDot = new clsShocDot();
			$objDot->Style = 1;
		}
		
		$this->getSubjects();
		$this->getLinks();
		
		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
			$objGraph->FlowDirection = 'LR';
		}

		foreach ($this->Subjects as $objSubject){
			$dotLabel = $objGraph->FormatDotLabel($objSubject->Title);
			$dotShape = null;
			$NodeId = null;				
			$objSubject->getDot($objDot, $objGraph);
		}
		foreach ($this->Links as $objLink){
			
			if (!isset($objDot->uriSubjects[$objLink->FromSubject->Uri])){
				$dotLabel = $objGraph->FormatDotLabel($objLink->FromSubject->Title);
				$dotShape = 'doublecircle';
				$NodeId = 'subject_'.(count($objDot->uriSubjects) + 1);
				$Color = null;
				if (is_object($objLink->FromSubject->Object)){
					$Color = $objLink->FromSubject->Object->Color;
					
					if (!isset($objDot->keys[$objLink->FromSubject->Object->Id])){
						$objKey = new clsShocDotKey();
						$objKey->Legend = $objLink->FromSubject->Object->Label;
						$objKey->Color = $Color;
						$objDot->keys[$objLink->FromSubject->Object->Id] = $objKey;
					}
					
				}
				$objGraph->addNode($NodeId, $dotLabel, $dotShape, $Color,0.5, 0.5, 'subject.php?urisubject='.$objLink->FromSubject->Uri, null, null);
				$objDot->uriSubjects[$objLink->FromSubject->Uri] = $NodeId;
			}
				
			if (!isset($objDot->uriSubjects[$objLink->ToSubject->Uri])){
				$dotLabel = $objGraph->FormatDotLabel($objLink->ToSubject->Title);
				$dotShape = 'doublecircle';
				$NodeId = 'subject_'.(count($objDot->uriSubjects) + 1);
				$Color = null;
				if (is_object($objLink->ToSubject->Object)){
					$Color = $objLink->ToSubject->Object->Color;
					
					if (!isset($objDot->keys[$objLink->ToSubject->Object->Id])){
						$objKey = new clsShocDotKey();
						$objKey->Legend = $objLink->ToSubject->Object->Label;
						$objKey->Color = $Color;
						$objDot->keys[$objLink->ToSubject->Object->Id] = $objKey;
					}
				}
				$objGraph->addNode($NodeId, $dotLabel, $dotShape, $Color,0.5, 0.5, 'subject.php?urisubject='.$objLink->ToSubject->Uri, null, null);
				$objDot->uriSubjects[$objLink->ToSubject->Uri] = $NodeId;
			}
				
			$ArrowHead = null;
			$ArrowTail = null;
			if ($objLink->Relationship->Extending){
				$ArrowTail = 'diamond';
				$ArrowHead = "none";
			}
			
			
			if (isset($objDot->uriSubjects[$objLink->FromSubject->Uri])){
				if (isset($objDot->uriSubjects[$objLink->ToSubject->Uri])){
					$dotLabel = $objGraph->FormatDotLabel($objLink->Relationship->Label);						
					$objGraph->addEdge($objDot->uriSubjects[$objLink->FromSubject->Uri],$objDot->uriSubjects[$objLink->ToSubject->Uri], $dotLabel, null, null, $ArrowHead, $ArrowTail );
				}
				
			}
		}
			

		$objDot->makeKey($objGraph);
		
		$this->dot = $objGraph->script;
		return $this->dot;		
		
	}
	
	
}



class clsBoxLink {
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	private $xmlUri = null;
		
	public $Uri = null;
	public $Id = null;
	
	public $uriBox = null;
	private $Box = null;

	public $idObject = null;
	private $Object = null;
	
	public $uriRelationship = null;
	private $Relationship = null;
	
	public $Inverse = false;
	private $RelLabel = null;

	public $uriSubject = null;
	private $Subject = null;
	
	private $SubjectsForObject = null;
	
	public $Description = null;

	
	public $canView = true;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	
	private $Models = null;
	private $Archetypes = null;
	
		
	public function __construct($Uri = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		$gObjects->Items[$Uri] = $this;
		
		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');
		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}


		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			
			
			$xmlBox = $this->xpath->query("shoc:box[1]", $xmlUri)->item(0);
			if ($xmlBox){
				$this->uriBox = $xmlBox->getAttribute("rdf:resource");
			}			

			
			$xmlIdObject = $this->xpath->query("shoc:object[1]", $xmlUri)->item(0);
			if ($xmlIdObject){
				$this->idObject = $xmlIdObject->nodeValue;
			}			

			$xmlRelationship = $this->xpath->query("shoc:relationship[1]", $xmlUri)->item(0);
			if ($xmlRelationship){
				$this->uriRelationship = $xmlRelationship->getAttribute("rdf:resource");
			}			

			$xmlInverse = $this->xpath->query("shoc:inverse[1]", $xmlUri)->item(0);
			if ($xmlInverse){
				if ($xmlInverse->nodeValue == 'true'){
					$this->Inverse = true;
				}
			}			
			
			$xmlSubject = $this->xpath->query("shoc:subject[1]", $xmlUri)->item(0);
			if ($xmlSubject){
				$this->uriSubject = $xmlSubject->getAttribute("rdf:resource");
			}			
			
			
			if ($xmlDescription = $this->xpath->query("shoc:description[1]", $xmlUri)->item(0)){
				$this->Description = $xmlDescription->nodeValue;
			}			
			
			
		}
		$this->xmlUri = $xmlUri;
		
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		
	}
	

	public function __get($name){
		switch ($name){
			case 'Models':
				$this->Models = $this->getModels();
				break;			
			case 'Archetypes':
				$this->Archetypes = $this->getArchetypes();
				break;
			case 'Object':
				$this->getObject();
				break;
			case 'SubjectsForObject':
				$this->getSubjectsForObject();
				break;				
			case 'Relationship':
				$this->getRelationship();
				break;
			case 'RelLabel':
				$this->getRelLabel();
				break;				
			case 'Subject':
				$this->getSubject();
				break;
			case 'Box':
				$this->getBox();
				break;
				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getObject(){

		if (!is_null($this->Object)){
			return $this->Object;
		}
		
		if (empty($this->idObject)){
			return null;
		}
		
		$this->getArchetypes();
		
		if (isset($this->Archetypes->Objects[$this->idObject])){
			$this->Object = $this->Archetypes->Objects[$this->idObject];
		}
		
		return $this->Object;
	}	

	private function getSubjectsForObject(){

		if (!is_null($this->SubjectsForObject)){
			return $this->SubjectsForObject;
		}

		$this->SubjectsForObject = array();

		$this->getObject();
		$this->getBox();
		
		foreach ($this->Box->Subjects as $objSubject){
			if ($objSubject->Object == $this->Object){
				$this->SubjectsForObject[$objSubject->Uri] = $objSubject;
			}
		}
		
		return $this->SubjectsForObject;
	}	
	
	
	private function getRelationship(){

		if (!is_null($this->Relationship)){
			return $this->Relationship;
		}
		
		if (empty($this->uriRelationship)){
			return null;
		}
		
		$this->getModels();
		
		if (isset($this->Models->uriRelationships[$this->uriRelationship])){
			$this->Relationship = $this->Models->uriRelationships[$this->uriRelationship];
		}
		return $this->Relationship;
	}	
	

	private function getRelLabel(){
		
		if (!is_null($this->RelLabel)){
			return $this->RelLabel;
		}

		$this->getRelationship();
		
		if (!is_object($this->Relationship)){
			return null;
		}
		
		switch ($this->Inverse){
			case false:
				$this->RelLabel = $this->Relationship->Label;
				break;
			default:
				$this->RelLabel = $this->Relationship->InverseLabel;
				break;				
		}
		
		return $this->RelLabel;
	}	
	
	
	private function getSubject(){
		
		if (!is_null($this->Subject)){
			return $this->Subject;
		}
		
		if (empty($this->uriSubject)){
			return null;
		}
//		$this->Subject = new clsSubject($this->uriSubject);
		$this->Subject = $this->Shoc->getSubject($this->uriSubject);		
		
		return $this->Subject;
	}	

	
	private function getBox(){
		
		if (!is_null($this->Box)){
			return $this->Box;
		}
		
		if (empty($this->uriBox)){
			return null;
		}
		$this->Box = $this->Shoc->getBox($this->uriBox);
		
		return $this->Box;
	}	
	
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	
	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
}


// ----------


class clsDocuments {
	
	
	private $Items = null;
	
	public $ObjectId = null;
	public $UserId = null;
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		
		$this->canView = true;
		if ($this->System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		
	}
	
	public function getItems(){
		
		if (!is_null($this->Items)){
			return $this->Items;
		}

		$this->Items = array();
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uri WHERE {
			?uri a shoc:Document.
			";

		if (!is_null($this->ObjectId)){
			$Query .= "?uri shoc:object " . chr(34) . $this->ObjectId . chr(34) .".
			"; 			
		}

		
		if (!is_null($this->UserId)){
			$Query .= "
			?revision shoc:document ?uri .
			?revision shoc:user " . chr(34) . $this->UserId . chr(34) .".
			";
		}
		
		$Query .= "}";
				
		$xmlResults = $objSparql->Query($Query);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
				
		$this->Refresh();
		
	}
	
	public function __get($name){
		switch ($name){
			case "Models":
				$this->getModels();
				break;
			case "Items":
				$this->getItems();
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){
		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}

	public function refresh(){
		
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){			
			$uriDocument = $xmlUri->nodeValue;
			$objDocument = $this->Shoc->getDocument($uriDocument);
			$this->Items[$uriDocument] = $objDocument;
		}
	}
	
/*	
	public function forTemplate($TemplateId){
		
		$this->Items = null;
		
		$this->TemplateId = $TemplateId;
		$this->getItems();
		
	}
*/	
	
}



class clsDocument {
		
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
		
	public $Uri = null;
	public $Id = null;
	public $Title = null;
	public $Type = 'subject';
	public $ObjectId = null;
	private $Object = null;
	public $ArchRelId = null;
	private $ArchRel = null;
	public $Version = null;
	public $Time = null;
	
	private $Revisions = null;
	private $CurrentRevision = null;

	private $uriBox = null;
	private $Box = null;


	private $uriFromSubject = null;
	private $FromSubject = null;
	
	private $uriToSubject = null;
	private $ToSubject = null;
	
	
	private $Form = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;
	private $Archetypes = null;
		
	public function __construct($Uri = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->Refresh();

		if ($System->LoggedOn){
			$this->getBox();
			if (is_object($this->Box)){
				$this->canView = $this->Box->canView;
				$this->canEdit = $this->Box->canEdit;
				$this->canControl = $this->Box->canEdit;
			}
		}
		
		
		$gObjects->Items[$Uri] = $this;
						
	}	

	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function refresh(){

		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			

			$xmlType = $this->xpath->query("shoc:documentType[1]", $xmlUri)->item(0);
			if ($xmlType){
				$this->Type = $xmlType->nodeValue;
			}			

			$xmlObjectId = $this->xpath->query("shoc:object[1]", $xmlUri)->item(0);
			if ($xmlObjectId){
				$this->ObjectId = $xmlObjectId->nodeValue;
			}			

			$xmlArchRelId = $this->xpath->query("shoc:archrel[1]", $xmlUri)->item(0);
			if ($xmlArchRelId){
				$this->ArchRelId = $xmlArchRelId->nodeValue;
			}			
			
			
			$xmlFromSubject = $this->xpath->query("shoc:fromSubject[1]", $xmlUri)->item(0);
			if ($xmlFromSubject){
				$this->uriFromSubject = $xmlFromSubject->getAttribute("rdf:resource");
			}			
	
			$xmlToSubject = $this->xpath->query("shoc:toSubject[1]", $xmlUri)->item(0);
			if ($xmlToSubject){
				$this->uriToSubject = $xmlToSubject->getAttribute("rdf:resource");
			}			
			
			
			$xmlBox = $this->xpath->query("shoc:box[1]", $xmlUri)->item(0);
			if ($xmlBox){
				$this->uriBox = $xmlBox->getAttribute("rdf:resource");
			}			
			
		}
		
	}
	
	
	
	public function __get($name){
		switch ($name){
			case 'Revisions':
				$this->getRevisions();
				break;						
			case 'CurrentRevision':
				$this->getCurrentRevision();
				break;										
			case 'Models':
				$this->Models = $this->getModels();
				break;			
			case 'Archetypes':
				$this->Archetypes = $this->getArchetypes();
				break;
			case 'Form':
				$this->Form = $this->getForm();
				break;
			case 'Object':
				$this->Object = $this->getObject();
				break;
			case 'ArchRel':
				$this->ArchRel = $this->getArchRel();
				break;				
			case 'Box':
				$this->getBox();
				break;
			case 'FromSubject':
				$this->getFromSubject();
				break;
			case 'ToSubject':
				$this->getToSubject();
				break;				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	public function __set($name, $value){
		switch ($name){
			case 'Models':
				$this->Models = $value;
				break;
		}
	}

	
	public function getBox(){
		if (!is_null($this->Box)){
			return $this->Box;
		}
		
		if (!is_null($this->uriBox)){
			$this->Box = $this->Shoc->getBox($this->uriBox);
		}

		return $this->Box;
		
	}

	
	public function getFromSubject(){
		
		if (is_null($this->uriFromSubject)){
			return null;
		}
		
		if (!is_null($this->FromSubject)){
			return $this->FromSubject;
		}
		
//		$this->FromSubject = new clsSubject($this->uriFromSubject);
		$this->FromSubject = $this->Shoc->getSubject($this->uriFromSubject);

		return $this->FromSubject;
		
	}
	
	public function getToSubject(){
		
		if (is_null($this->uriToSubject)){
			return null;
		}
		
		if (!is_null($this->ToSubject)){
			return $this->ToSubject;
		}
		
//		$this->ToSubject = new clsSubject($this->uriToSubject);
		$this->ToSubject = $this->Shoc->getSubject($this->uriToSubject);
		
		return $this->ToSubject;
		
	}
	

	private function getRevisions(){
		if (!is_null($this->Revisions)){
			return $this->Revisions;
		}
		
		global $gObjects;
		
		$this->Revisions = array();
				
		$uriDocument = $this->Uri;
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uriRevision WHERE {
			?uriRevision shoc:document <$uriDocument>.
			OPTIONAL { ?uriRevision dct:time ?DateTime }.
			}
			ORDER BY DESC(?DateTime)";
		$xmlResults = $objSparql->Query($Query);
		
		$domRevisions = new DOMDocument('1.0', 'utf-8');
		$domRevisions->formatOutput = true;
		$domRevisions->loadXMl($xmlResults);

		$xpathRevisions = new domxpath($domRevisions);
		
		
		$xpathRevisions->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpathRevisions->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpathRevisions->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpathRevisions->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpathRevisions->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpathRevisions->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$nodelistRevisions = $xpathRevisions->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uriRevision']/sparql:uri");
		
		$RevisionNumber = $nodelistRevisions->length;
		
		foreach ($nodelistRevisions as $xmlUriRevision){
			$uriRevision = $xmlUriRevision->nodeValue;
			
			if (isset($gObjects->Items[$uriRevision])){
				$objRevision = $gObjects->Items[$uriRevision];
			}
			else 
			{
				$objRevision = $this->Shoc->getRevision($uriRevision);
				$objRevision->Document = $this;
				$objRevision->Number = $RevisionNumber--;
			}
						
			$this->Revisions[$uriRevision] = $objRevision;
		}		
		
	}
	
	private function getCurrentRevision(){

		if (!is_null($this->CurrentRevision)){
			return $this->CurrentRevision;
		}
		
		$this->getRevisions();

		if (count($this->Revisions) > 0){
			$this->CurrentRevision = current($this->Revisions);
		}
		return $this->CurrentRevision;			
		
	}
	
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}

	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	private function getObject(){
		if (!is_null($this->Object)){
			return $this->Object;
		}
		if (is_null($this->ObjectId)){
			return null;
		}

		$this->getBox();
		if (isset($this->Box->Objects[$this->ObjectId])){
			$this->Object = $this->Box->Objects[$this->ObjectId];
		}
		return $this->Object;
	}

	private function getArchRel(){
		
		if (!is_null($this->ArchRel)){
			return $this->ArchRel;
		}
		if (is_null($this->ArchRelId)){
			return null;
		}

		$this->getArchetypes();
		
		if (isset($this->Archetypes->Relationships[$this->ArchRelId])){
			$this->ArchRel = $this->Archetypes->Relationships[$this->ArchRelId];
		}
		return $this->ArchRel;
	}
	
	
	
	private function getForm(){
		if (!is_null($this->Form)){
			return $this->Form;
		}
		
		$this->Form = new clsForm();
		$this->Form->Document = $this;
		
		return $this->Form;
		
	}
}


class clsRevision{
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $Uri = null;	
	public $Id = null;
	
	public $Action = null;
	
	
	public $Document = null;
	public $Number = null;
	public $Timestamp = null;
	
	private $Abouts = array();
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;
	private $Archetypes = null;
	private $Object = null;
	
	private $Title = null;
		
	private $objStatements = null;
	private $Form = null;
	
	public $canView = true;
	public $canEdit = false;
	public $canControl = false;
	
	private $UserId = null;
	private $User = null;
	
	

	public function __construct($Uri = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;

		
		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->Refresh();
		
		$gObjects->Items[$Uri] = $this;		
	}	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function refresh(){

		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
//		echo htmlentities($this->dom->saveXML());
//		exit;
		
		if ($xmlUri){
			
			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}			
			
			$xmlAction = $this->xpath->query("shoc:action[1]", $xmlUri)->item(0);
			if ($xmlAction){
				$this->Action = $xmlAction->nodeValue;
			}			
			
			$xmlUriDocument = $this->xpath->query("shoc:document", $xmlUri)->item(0);
			if ($xmlUriDocument){
				$uriDocument = $xmlUriDocument->getAttribute('rdf:resource');

				$this->Document = $this->Shoc->getDocument($uriDocument);
			}			

			
			$xmlUserId = $this->xpath->query("shoc:user[1]", $xmlUri)->item(0);
			if ($xmlUserId){
				$this->UserId = $xmlUserId->nodeValue;
			}			
			
			
			$xmlTime = $this->xpath->query("dct:time", $xmlUri)->item(0);
			if ($xmlTime){
				$this->Timestamp = strtotime($xmlTime->nodeValue);
			}			
			
			foreach ($this->xpath->query("shoc:about/shoc:About", $xmlUri) as $xmlAbout){
				
				$objAbout = new clsAbout();
				
				$xmlUriSubject = $this->xpath->query("shoc:subject", $xmlAbout)->item(0);
				if ($xmlUriSubject){
					$objAbout->uriSubject = $xmlUriSubject->getAttribute('rdf:resource');				
				}
				
				$xmlIdObject = $this->xpath->query("shoc:idObject", $xmlAbout)->item(0);				
				if ($xmlIdObject){
					$objAbout->idObject = $xmlIdObject->nodeValue;
				}

				
				$xmlUriLink = $this->xpath->query("shoc:link", $xmlAbout)->item(0);
				if ($xmlUriLink){
					$objAbout->uriLink = $xmlUriLink->getAttribute('rdf:resource');				
				}
				
				
				$xmlIdArchRel = $this->xpath->query("shoc:idArchRel", $xmlAbout)->item(0);				
				if ($xmlIdArchRel){
					$objAbout->idArchRel = $xmlIdArchRel->nodeValue;
				}
				
				
				
				$objAbout->Revision = $this;
				$this->Abouts[$objAbout->uriSubject] = $objAbout;
			}
		}
		
		
	}
	
	
	public function __get($name){
		switch ($name){
			case 'Form':
				$this->Form = $this->getForm();
				break;
			case 'objStatements':
				$this->getStatements();
				break;
			case 'Title':
				$this->getTitle();
				break;				
			case 'User':
				$this->getUser();
				break;				
				
				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	public function __set($name, $value){
		switch ($name){
			case 'Document':
				$this->Document = $value;
				
				$this->System = $this->Document->System;
				$this->Models = $this->Document->Models;
				$this->Archetypes = $this->Document->Archetypes;
				$this->Object = $this->Document->Object;
				
				break;
		}
	}
	
		
	private function getForm(){
		if (!is_null($this->Form)){
			return $this->Form;
		}
		$this->Form = new clsForm();
		$this->Form->Revision = $this;
		
		return $this->Form;
		
	}

	
	private function getUser(){
		if (!is_null($this->User)){
			return $this->User;
		}
		
		if (!is_null($this->UserId)){
			$this->User = new clsUser($this->UserId);
		}
		
		return $this->User;
		
	}
	
	
	
	
	private function getStatements(){
		if (!is_null($this->objStatements)){
			return $this->objStatements;
		}
		
		$this->objStatements = new clsStatements();
		$this->objStatements->forRevision($this);
		
		return $this->objStatements;
				
	}

	private function getTitle(){
		
		if (!is_null($this->Title)){
			return $this->Title;
		}
		$this->Title = "";
		
		foreach ($this->Abouts as $objAbout){
			if ($objAbout->Title != ""){
				if ($this->Title != ""){
					$this->Title .= "/";
				}
				$this->Title .= $objAbout->Title;
			}
		}

	}	
	
}

class clsAbout{
	
	public $Revision = null;
	
	public $uriSubject = null;
	public $uriLink = null;
	public $idObject = null;
	public $idArchRel = null;	
	
	private $Title = null;
	private $Object = null;
	private $ArchRel = null;
	private $Archetypes = null;
	
	private $System = null;
	

	public function __construct(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
	}
	
	
	public function __get($name){
		switch ($name){
			case 'Title':
				$this->getTitle();
				break;
		case 'Object':
				$this->getObject();
				break;
		case 'ArchRel':
				$this->getArchRel();
				break;
				
		}		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getTitle(){
		
		if (!is_null($this->Title)){
			return $this->Title;
		}
		
		$this->getObject();
		
		$this->Title = "";
		
		$arrTitleProperties = null;
		
		if (is_object($this->Object)){
			
			$arrTitleProperties = array();
			foreach ($this->System->Config->Vars['settings']['titleproperties'] as $uriTitleProperty){
				$arrTitleProperties = $arrTitleProperties + $this->Object->Class->getPropertiesForSuperProperty($uriTitleProperty);
			}
		
			if (count($arrTitleProperties) == 0){
				$AllProperties = $this->Object->Class->AllProperties;
				
				if (current($AllProperties)){
					$arrTitleProperties[] = current($AllProperties);
				}
			}
			
		}
		
		
		if (!is_null($arrTitleProperties)){
			foreach ($this->Revision->objStatements->Items as $objStatement){
				if ($objStatement->uriSubject == $this->uriSubject){
					foreach ($arrTitleProperties as $TitleProperty){
						if ($objStatement->uriProperty == $TitleProperty->Uri){
							if ($this->Title != ""){
								$this->Title .= "-";
							}
							$this->Title .= $objStatement->ValueLabel;
						}
					}
				}
			}		
		}		
	}

	
	private function getArchetypes(){
		
		if (!is_null($this->Archetypes)){
			return $this->Archetypes;
		}
		
		global $Archetypes;
		if (isset($Archetypes)){
			$this->Archetypes = $Archetypes;
		}
		else
		{
			$this->Archetypes = $this->Revision->Archetypes;
		}
		return $this->Archetypes;
	}

	private function getObject(){
		
		if (!is_null($this->Object)){
			return $this->Object;
		}
		$this->getArchetypes();
		if (isset($this->Archetypes->Objects[$this->idObject])){
			$this->Object = $this->Archetypes->Objects[$this->idObject];
		}
		return $this->Object;
	}
	
	private function getArchRel(){
		
		if (!is_null($this->ArchRel)){
			return $this->ArchRel;
		}
		$this->getArchetypes();
		if (isset($this->Archetypes->Relationships[$this->idArchRel])){
			$this->ArchRel = $this->Archetypes->Relationships[$this->idArchRel];
		}
		return $this->ArchRel;
	}
	
	
}

class clsStatements {
	
	
	public $Items = array();
	public $objSubjects = array();
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Models = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		
		$this->canView = true;
//		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
//		}
		
	}

	public function __get($name){
		switch ($name){
			case "Models":
				$this->Models = $this->getModels();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	
	
	
	public function forRevision($Revision){
		
		$uriRevision = $Revision->Uri;
		
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT ?uri ?uriSubject ?uriProperty ?Value ?uriRelationship ?uriLinkSubject ?uriPartOf WHERE {		
			?uri a shoc:Statement.
			<$uriRevision> shoc:statement ?uri.
			?uri shoc:subject ?uriSubject.
			OPTIONAL { ?uri shoc:property ?uriProperty }.
			OPTIONAL { ?uri shoc:value ?Value }.
			OPTIONAL { ?uri shoc:relationship ?uriRelationship }.
			OPTIONAL { ?uri shoc:linkSubject ?uriLinkSubject }.
			OPTIONAL { ?uri shoc:partOf ?uriPartOf }.
		}";
		$xmlResults = $objSparql->Query($Query);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
				
		$this->canView = true;
//		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
//		}

		$this->Refresh();
		
	}
	

	public function forSubject($Subject){
		
		$uriSubject = $Subject->Uri;
		foreach ($Subject->RevisionsForDateTime() as $objRevision){
			$uriRevision = $objRevision->Uri;						
			$objSparql = new clsSparql();			
			
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
					
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
			
			$Query = "SELECT ?uri ?uriSubject ?uriProperty ?Value ?uriRelationship ?uriLinkSubject ?uriPartOf WHERE {
				?uri a shoc:Statement.
				?uri shoc:subject <$uriSubject>.
				?uri shoc:subject ?uriSubject.				
				<$uriRevision> shoc:statement ?uri.				
				OPTIONAL { ?uri shoc:property ?uriProperty }.
				OPTIONAL { ?uri shoc:value ?Value }.
				OPTIONAL { ?uri shoc:relationship ?uriRelationship }.
				OPTIONAL { ?uri shoc:linkSubject ?uriLinkSubject }.
				OPTIONAL { ?uri shoc:partOf ?uriPartOf }.
			}";
			$xmlResults = $objSparql->Query($Query);
			
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;
			$this->dom->loadXMl($xmlResults);
			
			$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
			$this->refreshXpath();
					
	
			$this->Refresh();
			
			
			
			$objSparql = new clsSparql();			
			
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
					
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
			
			$Query = "SELECT ?uri ?uriSubject ?uriProperty ?Value ?uriRelationship ?uriLinkSubject ?uriPartOf WHERE {
				?uri a shoc:Statement.
				?uri shoc:subject ?uriSubject.
				
				?uri shoc:linkSubject <$uriSubject>.
				?uri shoc:linkSubject ?uriLinkSubject.
				
				<$uriRevision> shoc:statement ?uri.				
				?uri shoc:relationship ?uriRelationship.
				OPTIONAL { ?uri shoc:property ?uriProperty }.
				OPTIONAL { ?uri shoc:value ?Value }.
				OPTIONAL { ?uri shoc:partOf ?uriPartOf }.
			}";
			
			$xmlResults = $objSparql->Query($Query);
			
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;
			$this->dom->loadXMl($xmlResults);
			
			$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
			$this->refreshXpath();
					
	
			$this->Refresh();
						
		}
	}
	
	
	public function forLink($Link){
		
		$uriLink = $Link->Uri;
		foreach ($Link->RevisionsForDateTime() as $objRevision){
			$uriRevision = $objRevision->Uri;						
			
			$objSparql = new clsSparql();			
			
			$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
			$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
			$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
			$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
					
			$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
			
			$Query = "SELECT ?uri ?uriLink ?uriProperty ?Value ?uriRelationship ?uriLinkSubject ?uriPartOf WHERE {
				?uri a shoc:Statement.
				?uri shoc:subject <$uriLink>.								
				<$uriRevision> shoc:statement ?uri.				
				OPTIONAL { ?uri shoc:property ?uriProperty }.
				OPTIONAL { ?uri shoc:value ?Value }.
				OPTIONAL { ?uri shoc:partOf ?uriPartOf }.
			}";
			
			$xmlResults = $objSparql->Query($Query);
			
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;
			$this->dom->loadXMl($xmlResults);
			
			$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
			$this->refreshXpath();
					
	
			$this->Refresh();
						
		}
	}
	
	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}

	public function refresh(){

		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result") as $xmlResult){
			$xmlUri = $this->xpath->query("sparql:binding[@name='uri']/sparql:uri",$xmlResult)->item(0);
			if ($xmlUri){
				$uriStatement = $xmlUri->nodeValue;

				$uriSubject = null;
				$uriProperty = null;				
				$Value = null;
				$uriRelationship = null;
				$uriLinkSubject = null;
				$uriPartOf = null;
				
				$xmlUriSubject = $this->xpath->query("sparql:binding[@name='uriSubject']/sparql:uri",$xmlResult)->item(0);
				if ($xmlUriSubject){
					$uriSubject = $xmlUriSubject->nodeValue;
				}

				$xmlUriProperty = $this->xpath->query("sparql:binding[@name='uriProperty']/sparql:uri",$xmlResult)->item(0);
				if ($xmlUriProperty){
					$uriProperty = $xmlUriProperty->nodeValue;
				}

				$xmlValue = $this->xpath->query("sparql:binding[@name='Value']/sparql:literal",$xmlResult)->item(0);
				if ($xmlValue){
					$Value = $xmlValue->nodeValue;
				}

				$xmlUriRelationship = $this->xpath->query("sparql:binding[@name='uriRelationship']/sparql:uri",$xmlResult)->item(0);
				if ($xmlUriRelationship){
					$uriRelationship = $xmlUriRelationship->nodeValue;
				}
				
				$xmlUriLinkSubject = $this->xpath->query("sparql:binding[@name='uriLinkSubject']/sparql:uri",$xmlResult)->item(0);
				if ($xmlUriLinkSubject){
					$uriLinkSubject = $xmlUriLinkSubject->nodeValue;
				}
				
				$xmlUriPartOf = $this->xpath->query("sparql:binding[@name='uriPartOf']/sparql:uri",$xmlResult)->item(0);
				if ($xmlUriPartOf){
					$uriPartOf = $xmlUriPartOf->nodeValue;
				}
				
				
				$objStatement = new clsStatement();
				$objStatement->Uri = $uriStatement;
				$objStatement->uriSubject = $uriSubject;
				$objStatement->uriProperty = $uriProperty;
				$objStatement->Value = $Value;
				$objStatement->uriRelationship = $uriRelationship;
				$objStatement->uriLinkSubject = $uriLinkSubject;
				$objStatement->uriPartOf = $uriPartOf;
				
				$this->Items[$uriStatement] = $objStatement;
			}
		}
	}
}


class clsStatement {

	public $dom = null;
	private $xml = null;
	public $xpath = null;

	public $Models = null;
	public $Document = null;
	public $Revision = null;

	public $Uri = null;
	public $uriSubject = null;
	public $uriProperty = null;
	public $uriRelationship = null;
	public $Value = null;
	public $uriLinkSubject = null;
	public $uriPartOf = null;
	
	private $ValueLabel = null;
	
	public function __get($name){
		switch ($name){
			case "Models":
				$this->getModels();
				break;
			case "ValueLabel":
				$this->getValueLabel();
				break;
				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		
		$this->Models = $Models;
		return $this->Models;
		
	}

	private function getValueLabel(){
			
		if (!is_null($this->ValueLabel)){
			return $this->ValueLabel;
		}
		
		if (is_null($this->Value)){
			return null;
		}
		$this->ValueLabel = $this->Value;
					
		$this->getModels();
		
		if (isset($this->Models->uriProperties[$this->uriProperty])){
			
			$objProperty = $this->Models->uriProperties[$this->uriProperty];
			foreach ($objProperty->Lists as $objList){
				if (isset($objList->Terms[$this->Value])){
					$this->ValueLabel = $objList->Terms[$this->Value]->Label;
				}
			}
		}
		
		return $this->ValueLabel;
	}
	
}



class clsSubjects {
	
	private $ForSelection = false;
	
	private $Items = null;
	private $SortedItems = null;
	
	private $ActivityItems = null;
	private $GroupItems	= null;
	private $BoxItems = null;
	private $LinkItems = null;
	private $BoxLinkItems = null;
	
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $ClassId = null;
	private $Class = null;

	
	private $ObjectId = null;
	private $Object = null;
	
	private $uriLinkSubject = null;
	private $RelId = null;
	private $Relationship = null;
	private $Inverse = false;
	
	private $uriActivity = null;
	private $uriBox = null;
	
	public $FilterPrefix = 'filter';
	public $FilterFields = array();
	
	private $FilterVariables = null;
	private $FilterStatements = null;
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	
	private $System = null;
	private $Shoc = null;
	
	private $Models = null;
	private $Archetypes = null;
	
	private $Sparql = null;
	private $Query = null;
	
	private $numFilter = 0;
	private $numVar = 0;
	
	private $dot;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}
		$this->Shoc = $Shoc;
		

		$gObjects = gObjects();
		
				
		$this->canView = true;
//		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
//		}

		
	}

	private function setupFilters(){
		
		$this->FilterVariables = '';
		$this->FilterStatements = '';
		
		foreach ($this->FilterFields as $FieldName=>$FieldValue){
			++$this->numFilter;
			$FieldNameParts = explode('_',$FieldName);

			if (array_shift($FieldNameParts) == $this->FilterPrefix){			
				$this->SetupFilter($FieldNameParts, $FieldValue);
			}
						
		}
		
	}
	
	
	
	private function SetupFilter(&$FieldNameParts, $FieldValue = null, $varSubject = '?uri'){
		
		switch (array_shift($FieldNameParts)){
					
			case 'propid':
				$PropertyId = array_shift($FieldNameParts);
				$FilterType = array_shift($FieldNameParts);						
				$PropertyValue = $FieldValue;

				if (isset($this->Class->Model->objModels->Properties[$PropertyId])){
					$objProperty = $this->Class->Model->objModels->Properties[$PropertyId];
							
					$varStatement = '?statement'.++$this->numVar;
					$varProperty = '?prop'.++$this->numVar;
						
					$this->FilterVariables .= "$varStatement a shoc:Statement . \n";
					$this->FilterVariables .= "$varStatement shoc:subject $varSubject . \n";
					$this->FilterVariables .= "$varStatement shoc:property <".$objProperty->Uri."> . \n";
												
					switch ($FilterType){
						case "is":
							$this->FilterVariables .= "$varStatement shoc:value ".chr(34).chr(34).chr(34).$PropertyValue.chr(34).chr(34).chr(34)." . \n";
							break;
						default:
							$this->FilterVariables .= "$varStatement shoc:value $varProperty . \n";
							break;
					}
					
					switch ($FilterType){
						case "contains":
							$this->FilterStatements .= "FILTER regex(str($varProperty), ".chr(34).chr(34).chr(34).$PropertyValue.chr(34).chr(34).chr(34).",'i') .\n";
							break;
						case "greater":
							$this->FilterStatements .= "FILTER $varProperty > $PropertyValue .\n";
							break;
						case "less":
							$this->FilterStatements .= "FILTER $varProperty < $PropertyValue .\n";
							break;
						default:
							break;
					}
				}
					
				
				break;
						
						
			case 'relid':
				$RelId = array_shift($FieldNameParts);
				
				if (isset($this->Archetypes->Relationships[$RelId])){
					$objArchetypeRelationship = $this->Archetypes->Relationships[$RelId];

					$varLink = '?link'.++$this->numVar;
					
					$varBox = '?box'.++$this->numVar;
					$varBoxLink = '?boxlink'.++$this->numVar;
					$varLinkSubject = '?subject'.++$this->numVar;
					
					
					$this->FilterVariables .= "{ \n";
					$this->FilterVariables .= "$varLink a shoc:Link. \n";
					$this->FilterVariables .= "$varLink shoc:relationship <".$objArchetypeRelationship->Relationship->Uri."> . \n";
					
					switch($objArchetypeRelationship->Inverse){
						case false:	
							$this->FilterVariables .= "$varLink shoc:fromSubject $varSubject . \n";					
							$this->FilterVariables .= "$varLink shoc:toSubject $varLinkSubject . \n";
							break;
						default:
							$this->FilterVariables .= "$varLink shoc:fromSubject $varLinkSubject . \n";					
							$this->FilterVariables .= "$varLink shoc:toSubject $varSubject . \n";
							break;
					}
					$this->FilterVariables .= "} \n";

					$this->FilterVariables .= "UNION";					
// box link					
					$this->FilterVariables .= "{ \n";
					$this->FilterVariables .= "?uriDocument shoc:box $varBox . \n";
					$this->FilterVariables .= "$varBoxLink shoc:box $varBox . \n";
					$this->FilterVariables .= "$varBoxLink shoc:relationship <".$objArchetypeRelationship->Relationship->Uri."> . \n";
					$this->FilterVariables .= "$varBoxLink shoc:subject $varLinkSubject . \n";					
					$this->FilterVariables .= "} \n";
					
					$this->SetupFilter($FieldNameParts, $FieldValue, $varLinkSubject);										
					
					
				}
				
				break;
						
						
		}
				
		
	}
	
	public function getLinkItems(){
		
		if (!is_null($this->LinkItems)){
			return $this->LinkItems;
		}

		$this->getItems();

	}

	public function getBoxLinkItems(){
		
		if (!is_null($this->BoxLinkItems)){
			return $this->BoxLinkItems;
		}

		$this->getItems();

	}
	
	
	private function getItems(){

		if (!is_null($this->Items)){
			return $this->Items;
		}

		$this->Items = array();
		$this->LinkItems = array();
		$this->BoxLinkItems = array();
		
		if (!($this->ForSelection)){
			return $this->Items;
		}
		
		$this->setupFilters();
		
		$this->Sparql = new clsSparql();
		
		$this->Sparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$this->Sparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$this->Sparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$this->Sparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$this->Sparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT DISTINCT ?uri ?uriLink ?uriBoxLink WHERE {
			?uriAbout 		a shoc:About.
			?uriRevision 	shoc:about 			?uriAbout .
			?uriRevision 	shoc:document 		?uriDocument .			
			?uriRevision 	dct:time 			?revTime .  
			OPTIONAL {?uriRevision shoc:action ?revAction }.			
			
		";

		
		if (is_null($this->Relationship)){
			
			$Query .= "
				?uriAbout shoc:subject ?uri.
			";
		}
		else
		{
			
			$Query .= "
				{
			";
			
			$Query .= "
				?uriAbout 	shoc:link 			?uriLink.			
			    ?uriLink 	a 					shoc:Link.
				?uriLink 	shoc:relationship	<" . $this->Relationship->Uri.">.
			";
			switch ($this->Inverse){
				case false:
					$Query .= "
						?uriLink 	shoc:fromSubject	<" . $this->uriLinkSubject.">.				
						?uriLink 	shoc:toSubject		?uri.				
					";
					break;
				case true:
					$Query .= "
						?uriLink 	shoc:toSubject	<" . $this->uriLinkSubject.">.				
						?uriLink 	shoc:fromSubject		?uri.				
					";
					break;
			}
			
			
			$Query .= "		
				filter not exists {
		    		?uriAbout2 shoc:link ?uriLink.
					?uriRevision2 shoc:about ?uriAbout2 .
		    		?uriRevision2 dct:time ?revTime2 
      				filter (?revTime2 > ?revTime)
      			}
      		";
			
			$Query .= "
				}
				UNION
				{
			";
			
			
			$Query .= "
	 
	 
				?uriBoxLink 		a 					shoc:BoxLink .
				?uriBoxLink 		shoc:box 			?uriBox .
				?uriBoxLink			shoc:object			?object .	
				?uriBoxLink			shoc:relationship 	<" . $this->Relationship->Uri."> .
	
	
				?uriDocument		shoc:box			?uriBox .
				?uriDocument		a					shoc:Document .
				?uriDocument		shoc:documentType	".chr(34)."subject".chr(34)." .
				?uriRevision		shoc:document		?uriDocument .
				?uriAbout			shoc:idObject		?object .
	
				{
					?uriBoxLink				shoc:subject		?uri.
					?uriAbout				shoc:subject		<" . $this->uriLinkSubject.">.
				}

				UNION

				{	
					?uriBoxLink				shoc:subject		<" . $this->uriLinkSubject.">.
					?uriAbout				shoc:subject		?uri .
					?uriBoxLink				shoc:inverse		true .
				}.
			";

			$Query .= "
	 		}.
	 		";
			
		}
			
		
		if (!is_null($this->Class)){
			$Query .= "    ?uri shoc:class <" . $this->Class->Uri.">.
			";
		}

		
		
		if (!is_null($this->uriBox)){
			$Query .= "    ?uriDocument 	shoc:box 	<" . $this->uriBox.">.			
			";
		}
		
		
		if (!is_null($this->uriActivity)){
			$Query .= "
				?uriDocument 	shoc:box 			?uriBox .
				?uriBox			shoc:group			?uriGroup .
				?uriGroup		shoc:activity		<" . $this->uriActivity.">.
			";
		}
		
		
		
		
		$Query .= $this->FilterVariables;

		$Query .= $this->FilterStatements;
		
		
		$Query .= '
			filter (!BOUND(?revAction) || ?revAction != "remove")
		';
		

		$Query .= "}";
		$xmlResults = $this->Sparql->Query($Query);
//		echo htmlentities($this->Sparql->Query).'<br/>';
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();

// multiple describes could go here
//		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){	
//			$uriSubject = $xmlUri->nodeValue;
//		}
		
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){	
			$uriSubject = $xmlUri->nodeValue;
			
//			$objSubject = new clsSubject($uriSubject);
			$objSubject = $this->Shoc->getSubject($uriSubject);
			
			$this->Items[$uriSubject] = $objSubject;

			$xmlLink = $this->xpath->query("../../sparql:binding[@name='uriLink']/sparql:uri[1]",$xmlUri)->item(0);
			if ($xmlLink){
				$uriLink = $xmlLink->nodeValue;
				$this->LinkItems[$uriLink] = $objSubject;				
			}

			
			$xmlBoxLink = $this->xpath->query("../../sparql:binding[@name='uriBoxLink']/sparql:uri[1]",$xmlUri)->item(0);
			if ($xmlBoxLink){
				$uriBoxLink = $xmlBoxLink->nodeValue;
				$this->BoxLinkItems[$uriBoxLink][$uriSubject] = $objSubject;				
			}
			
			
			if (is_object($objSubject->Box)){
				if (is_object($objSubject->Box->Group)){
					$uriGroup = $objSubject->Box->Group->Uri;
					$this->GroupItems[$uriSubject] = $objSubject;
				}
			}
			
		}
		
		
		
		
	}
	
	public function __get($name){
		switch ($name){
			case "Query":
				$this->getQuery();
				break;			
			case "Items":
				$this->getItems();
				break;
			case "LinkItems":
				$this->getLinkItems();
				break;				
			case "SortedItems":
				$this->getSortedItems();
				break;				
			case "ActivityItems":
			case "GroupItems":
			case "BoxItems":
				$this->arrangeItems();
				break;				
			case "Models":
				$this->getModels();
				break;
			case "Archetypes":
				$this->getArchetypes();
				break;
				
			case 'dot':
				$this->dot = $this->getDot();
				break;
								
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	
	public function __set($name, $value){
		switch ($name){
			case 'uriActivity':
				$this->forActivity($value);
				break;
			case 'uriBox':
				$this->forBox($value);
				break;
		}
	}

	
	private function getSortedItems(){
		
		$this->getItems();
		$this->SortedItems = $this->Items;
		usort($this->SortedItems, array($this, 'cmpSubject'));
	}
	
	private function cmpSubject($a, $b){
	    return strcmp( strtolower($a->Title),  strtolower($b->Title));
	}
	
	public function addItem($Subject){
		if (is_null($this->Items)){
			$this->Items = array();
		}
		
		$this->Items[$Subject->Uri] = $Subject;
		
		$this->ActvityItems = null;
		$this->GroupItems = null;
		$this->BoxItems = null;
		$this->LinkItems = null;
		
	}	
	

	private function getQuery(){
		if (!is_null($this->Query)){		
			return $this->Query;
		}		
		
		if (is_null($this->Sparql)){
			return null;
		}
		
		if (is_null($this->Sparql->Query)){
			return null;
		}
		$this->Query = $this->Sparql->Query;
		return $this->Query;

	}
	
	
	private function getModels(){

		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}
	
	private function getArchetypes(){

		if (!is_null($this->Archetypes)){
			return $this->Archetypes;
		}
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes();
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function forClass($ClassId){

		$this->ForSelection = true;
		
		$this->ClassId = $ClassId;
		$this->Class = null;
		$this->getModels();
		
		if (isset($this->Models->Classes[$ClassId])){
			$this->Class = $this->Models->Classes[$ClassId];
		}
		
	}

	
	public function forObject($ObjectId){
		
		$this->ForSelection = true;		
		
		$this->ObjectId = $ObjectId;
		$this->Object = null;
		$this->getArchetypes();
		
		if (isset($this->Archetypes->Objects[$ObjectId])){
			$this->Object = $this->Archetypes->Objects[$ObjectId];
			$this->Class = $this->Object->Class;
			$this->ClassId = $this->Class->Id;
		}

	}
	

	
	public function forRelationship($uriLinkSubject,$RelId, $Inverse){
		
		$this->ForSelection = true;		
		
		$this->uriLinkSubject = $uriLinkSubject;
		$this->RelId = $RelId;
		
		$this->Relationship = null;
		$this->getModels();
		
		if (isset($this->Models->Relationships[$RelId])){
			$this->Relationship = $this->Models->Relationships[$RelId];
			$this->Inverse = $Inverse;
		}

	}
	
	
	private function forActivity($uriActivity){
		
		$this->ForSelection = true;
		
		$this->uriActivity = $uriActivity;
		$this->Items = null;
				
	}
	
	private function forBox($uriBox){
		
		$this->ForSelection = true;
		
		$this->uriBox = $uriBox;
		$this->Items = null;
				
	}
	
	
	public function arrangeItems(){

		if (!is_null($this->BoxItems)){
			return;
		}

		$this->	Items = array();
		$this->GroupItems = array();
		$this->ActivityItems = array();

		if (is_null($this->Items)){
			return;
		}
		
		foreach ($this->Items as $objSubject){
			$this->ActivityItems[$objSubject->Box->Group->Activity->Uri] = $objSubject;
			$this->GroupItems[$objSubject->Box->Group->Uri] = $objSubject;
			$this->BoxItems[$objSubject->Box->Uri] = $objSubject;
		}
		
	}
	
	
	public function getDot($objDot = null, $objGraph = null){
		
		if (!is_null($this->dot)){
			return $this->dot;
		}
		$this->dot = '';

		$this->getArchetypes();
		
		$Top = false;
		if (is_null($objDot)){
			$objDot = new clsShocDot();
			$objDot->Style = 1;
		}
//		if (isset($objDot->uriSubjects[$this->Uri])){
//			return;
//		}
		
		$this->getItems();
		
		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
			$objGraph->FlowDirection = 'LR';
			$Top = true;			
		}

		$arrLinks = array();		
		foreach ($this->Items as $objSubject){
			$objSubject->getDot($objDot, $objGraph);
			foreach ($objSubject->Links as $objLink){
				$arrLinks[] = $objLink;
			}
		}
		
		foreach ($arrLinks as $objLink){
			if (isset($objDot->uriSubjects[$objLink->FromSubject->Uri])){
				$FromNodeId = $objDot->uriSubjects[$objLink->FromSubject->Uri];
				if (isset($objDot->uriSubjects[$objLink->ToSubject->Uri])){
					$ToNodeId = $objDot->uriSubjects[$objLink->ToSubject->Uri];
					$RelLabel = $objGraph->FormatDotLabel($objLink->Relationship->Label);
					
					
					$ArrowHead = null;
					$ArrowTail = null;
					if ($objLink->Relationship->Extending){
						$ArrowTail = 'diamond';
						$ArrowHead = "none";
					}
					
					
					$objGraph->addEdge($FromNodeId, $ToNodeId, $RelLabel, null, null, $ArrowHead, $ArrowTail);
				}
			}				
		}

		$objDot->makeKey($objGraph);
			
		$this->dot = $objGraph->script;
		return $this->dot;		
		
		
	}
	
}




class clsSubject {

	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
		
	public $Uri = null;	
	public $Id = null;
	
	public $Loaded = false;
	
	public $ClassId = null;
	public $uriClass = null;
	private $Class = null;
	
	private $Revisions = null;
	
	private $objStatements = null;
	private $Attributes = null;
	private $Links = null;
	
	private $Template = null;
	private $Object = null;
		
		
	private $dot = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;
		
	
	
	private $Title = null;	
	private $Box = null;
	
	public function __construct($Uri = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		

		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		$this->dom->loadXMl($xmlResults);
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->Refresh();

		$this->getClass();
		
		if ($System->LoggedOn){
			$this->getBox();
			if (is_object($this->Box)){
				$this->canView = $this->Box->canView;
				$this->canEdit = $this->Box->canEdit;
				$this->canControl = $this->Box->canEdit;
			}
		}
		
		
		$gObjects->Items[$this->Uri] = $this;
		
	}	

	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);

		$this->xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$this->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$this->xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$this->xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$this->xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$this->xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
	}
	
	public function refresh(){

		$xmlUri = $this->xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){

			$xmlId = $this->xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}

			
			$xmlClass = $this->xpath->query("shoc:class[1]", $xmlUri)->item(0);
			if ($xmlClass){
				$this->uriClass = $xmlClass->getAttribute("rdf:resource");
			}
			
			$this->Loaded = true;
		}			
		
	}
	
	
	public function __get($name){
		switch ($name){
			case 'objStatements':
				$this->getStatements();
				break;				
			case 'Revisions':
				$this->getRevisions();
				break;								
			case 'Class':
				$this->getClass();
				break;
			case 'Attributes':
				$this->getAttributes();
				break;
			case 'Links':
				$this->getLinks();
				break;				
			case 'dot':
				$this->dot = $this->getDot();
				break;
			case 'Title':
				$this->getTitle();
				break;
			case 'Box':
				$this->getBox();
				break;
			case 'Object':
				$this->getObject();
				break;				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}


	private function getObject(){
		if (!is_null($this->Object)){
			return $this->Object;
		}
		$this->getRevisions();
		$objRevision = reset($this->Revisions);
		if (is_object($objRevision)){
			foreach ($objRevision->Abouts as $objAbout){
				if ($objAbout->uriSubject == $this->Uri){
					$this->Object = $objAbout->Object;
				}
			}
		}
		return $this->Object;

	}
	
	private function getClass(){
		if (!is_null($this->Class)){
			return $this->Class;
		}
		$this->getModels();
		
		if (!isset($this->Models->uriClasses[$this->uriClass])){
			return false;
		}
		
		$this->Class = $this->Models->uriClasses[$this->uriClass];
		
		return $this->Class;
				
	}	
	
	private function getStatements(){
		if (!is_null($this->objStatements)){
			return $this->objStatements;
		}
		
		$this->objStatements = new clsStatements();
		$this->objStatements->forSubject($this);
		
		return $this->objStatements;
				
	}

	private function getAttributes(){

		if (!is_null($this->Attributes)){
			return $this->Attributes;
		}
		$this->Attributes = array();
		$this->getClass();
		$this->getStatements();
		if (is_object($this->Class)){
			foreach ($this->Class->AllProperties as $objProperty){
				foreach ($this->objStatements->Items as $objStatement){
					if ($objStatement->uriProperty == $objProperty->Uri){
						$objAttribute = new clsAttribute();
						$objAttribute->Subject = $this;
						$objAttribute->Property = $objProperty;
						$objAttribute->Statement = $objStatement;
						$objAttribute->getParts();
						$this->Attributes[] = $objAttribute;					
					}
				}			
			}
		}
		return $this->Attributes;
	}
	
	private function getLinks(){

		if (!is_null($this->Links)){
			return $this->Links;
		}
		$gObjects = gObjects();
		
		$this->Links = array();
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <".clsShoc::nsRDFS.">";
		$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsShoc::nsDCT.">";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT DISTINCT ?uri WHERE {
			?uri a shoc:Link .
			{ ?uri	shoc:fromSubject	<". $this->Uri . "> }
			UNION { ?uri	shoc:toSubject	<". $this->Uri . "> } .			
			
			?uriAbout		shoc:link		?uri .	
			?uriRevision 	shoc:about		?uriAbout .	
		
			";
		
		$Query .= "}";
		$xmlResults = $objSparql->Query($Query);
//		echo htmlentities($objSparql->Query);
		
		
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->loadXMl($xmlResults);
		
		$DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
				
		$xpath = new domxpath($dom);

		$xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');
		$xpath->registerNamespace('shoc', clsShoc::prefixSHOC);

		foreach ($xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){
			$uriLink = $xmlUri->nodeValue;
//			$objLink = new clsLink($uriLink);
			$objLink = $this->Shoc->getLink($uriLink);
			
			
			if (is_object($objLink->Revision)){
				if ($objLink->Revision->Action == 'remove'){
					continue;
				}
			}
			$this->Links[$objLink->Uri] = $objLink;	
		}

// add metadata links for the box

// first where this subject is an object in a box				
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <".clsShoc::nsRDFS.">";
		$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsShoc::nsDCT.">";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "
SELECT DISTINCT ?uri WHERE {
		
	?about 			shoc:subject 		<". $this->Uri . "> .
	?about			shoc:idObject		?object .
	?revision 		shoc:about 			?about .
	?revision 		shoc:document 		?document .			
	?document 		shoc:box 			?box .
					
	?uri 			a 					shoc:BoxLink .
	?uri 			shoc:box 			?box .
	?uri			shoc:object			?object .
		
}";

		$xmlResults = $objSparql->Query($Query);

		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->loadXMl($xmlResults);
		
		$DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
				
		$xpath = new domxpath($dom);

		$xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');
		$xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
				
		foreach ($xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){

			$uriBoxLink = $xmlUri->nodeValue;
//			$objBoxLink = new clsBoxLink($uriBoxLink);
			$objBoxLink = $this->Shoc->getBoxLink($uriBoxLink);			
			
			$objLink = new clsLink();
		
			$objLink->BoxLink = $objBoxLink;
			
			$objLink->Uri = $uriBoxLink;
			
			switch($objBoxLink->Inverse){
				case false:
					$objLink->uriFromSubject = $this->Uri;
					$objLink->uriToSubject = $objBoxLink->Subject->Uri;
					break;
				default:
					$objLink->uriFromSubject = $objBoxLink->Subject->Uri;
					$objLink->uriToSubject = $this->Uri;
					break;
			}
			
			$objLink->uriRelationship = $objBoxLink->uriRelationship;
			
			$objLink->Description = $objBoxLink->Description;			
			$objLink->Id = $objBoxLink->Id;
								
			$this->Links[$objLink->Uri] = $objLink;			
		}
		
		
		
		
		
		
// second where this subject is the target of the BoxLink				
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <".clsShoc::nsRDF.">";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <".clsShoc::nsRDFS.">";
		$objSparql->Prefixes['dct'] = "PREFIX dct: <".clsShoc::nsDCT.">";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <".clsShoc::nsXSD.">";				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "
SELECT DISTINCT ?uri WHERE {
							
	?uri 			a 					shoc:BoxLink .
	?uri 			shoc:box 			?box .
	?uri			shoc:object			?object .
	?uri 			shoc:subject 		<". $this->Uri . "> .
	
}";

		$xmlResults = $objSparql->Query($Query);

		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->loadXMl($xmlResults);
		
		$DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
				
		$xpath = new domxpath($dom);

		$xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');
		$xpath->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		foreach ($xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){

			$uriBoxLink = $xmlUri->nodeValue;
//			$objBoxLink = new clsBoxLink($uriBoxLink);
			$objBoxLink = $this->Shoc->getBoxLink($uriBoxLink);
			
			foreach ($objBoxLink->SubjectsForObject as $objLinkBoxSubject){
				$objLink = new clsLink();
				
				$objLink->BoxLink = $objBoxLink;
			
				$objLink->Uri = $objLinkBoxSubject->Uri;
				
				switch($objBoxLink->Inverse){
					case true:
						$objLink->uriFromSubject = $this->Uri;
						$objLink->uriToSubject = $objLinkBoxSubject->Uri;
						break;
					default:
						$objLink->uriFromSubject = $objLinkBoxSubject->Uri;
						$objLink->uriToSubject = $this->Uri;
						break;
				}
				
				$objLink->uriRelationship = $objBoxLink->uriRelationship;
				
				$objLink->Description = $objBoxLink->Description;			
				$objLink->Id = $objBoxLink->Id;
									
				$this->Links[$objLink->Uri] = $objLink;			

			}
			
		}
		
		
		
		
	}
	
	private function getRevisions(){
		if (!is_null($this->Revisions)){
			return $this->Revisions;
		}
		
		global $gObjects;
				
		$this->Revisions = array();
				
		$uriSubject = $this->Uri;
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";

		
		
		$Query = "SELECT DISTINCT ?uriRevision WHERE {
			{
				?uriRevision shoc:about ?About.
				?About shoc:subject <$uriSubject>.
			}
			UNION
			{
				?uriStatement shoc:relationship ?uriRelationship.
				?uriStatement shoc:linkSubject <$uriSubject>.
				?uriRevision  shoc:statement 	?uriStatement.
			}
			UNION
			{
				?uriStatement shoc:relationship ?uriRelationship.
				?uriStatement shoc:subject 		<$uriSubject>.
				?uriRevision  shoc:statement    ?uriStatement.
			}
			OPTIONAL { ?uriRevision dct:time ?DateTime }.			
		}
		ORDER BY DESC(?DateTime)";
	
		
		
		$xmlResults = $objSparql->Query($Query);
		
		$domRevisions = new DOMDocument('1.0', 'utf-8');
		$domRevisions->formatOutput = true;
		$domRevisions->loadXMl($xmlResults);

		$xpathRevisions = new domxpath($domRevisions);
				
		$xpathRevisions->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpathRevisions->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpathRevisions->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpathRevisions->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpathRevisions->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpathRevisions->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$nodelistRevisions = $xpathRevisions->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uriRevision']/sparql:uri");
		
		foreach ($nodelistRevisions as $xmlUriRevision){
			$uriRevision = $xmlUriRevision->nodeValue;
			$objRevision = $this->Shoc->getRevision($uriRevision);			
			$this->Revisions[$uriRevision] = $objRevision;
		}		
		
	}
	
	public function RevisionsForDateTime($DateTime = null){

		$this->getRevisions();

		if (is_null($DateTime)){
			$DateTime = time();
		}
		
		$arrRevisions = array();
				
		foreach ($this->Revisions as $objRevision){
			if ($objRevision->Timestamp > $DateTime){
				continue;
			}
			
			if (!isset($arrRevisions[$objRevision->Document->Uri])){
				$arrRevisions[$objRevision->Document->Uri] = $objRevision;
			}
			else
			{
				if ($objRevision->Timestamp > $arrRevisions[$objRevision->Document->Uri]->Timestamp){
					$arrRevisions[$objRevision->Document->Uri] = $objRevision;
				}
			}
		}
		return $arrRevisions;		
	}
	
	private function getModels(){
		if (!is_null($this->Models)){
			return $this->Models;
		}

		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}
		$this->Models = $Models;
		return $this->Models;
		
	}

	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this->Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	private function getTemplate(){

		if (!is_null($this->Template)){
			return $this->Template;
		}
		if (is_null($this->TemplateId)){
			return null;
		}
		
		$this->getArchetypes();

		if (isset($this->Archetypes->Items[$this->TemplateId])){
			$this->Template = $this->Archetypes->Items[$this->TemplateId];
		}
		return $this->Template;
	}
	
	private function getForm(){
		if (!is_null($this->Form)){
			return $this->Form;
		}
		
		$this->Form = new clsForm();
		$this->Form->Document = $this;
		
		return $this->Form;
		
	}
	
	
	public function getDot($objDot = null, $objGraph = null, $Scale=1, $Depth = 1, $Level = 1 ){

		if ($Level > $Depth){
			return null;
		}
		
		if (!is_null($this->dot)){
			return $this->dot;
		}
		$this->dot = '';

		$this->getArchetypes();
		
		$Top = false;
		if (is_null($objDot)){
			$objDot = new clsShocDot();
			$objDot->Style = 2;
		}
		if (isset($objDot->uriSubjects[$this->Uri])){
			return;
		}
		
		$this->getAttributes();
		
		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
			$Top = true;			
		}
				

		$dotLabel = null;
//		if (count($this->Attributes) > 0){
//			reset($this->Attributes);
//			$dotLabel = current($this->Attributes)->Statement->Value;
//		}
		
		$dotLabel = $this->getTitle();
		
		$Height = null;
		$Width = null;
		
		if ($Scale != 1){
			$Height = 0.8 * $Scale;
			$Width = 1 * $Scale;
		}
		
		$dotShape = null;
		
		
		$Color = null;
		$FontColor = null;
		$this->getObject();
		
		if (is_object($this->Object)){
			$Color = $this->Object->Color;
			$FontColor = $this->Object->FontColor;
			if (!isset($objDot->keys[$this->Object->Id])){
				$objKey = new clsShocDotKey();
				$objKey->Legend = $this->Object->Label;
				$objKey->Color = $Color;
				$objDot->keys[$this->Object->Id] = $objKey;
			}			
		}
		
		
		
		if ($objDot->Style == 1){
			$dotLabel = $objGraph->FormatDotLabel($dotLabel);			
		}
		if ($objDot->Style == 2){
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			$dotLabel .= "<tr><td colspan='2' bgcolor='$Color'><font color='$FontColor'>".$this->Class->Label."</font></td></tr>"; 
			
			foreach ($this->Attributes as $objAttribute){
				$dotLabel .= "<tr>";
				$dotLabel .= "<td align='left' balign='left' valign='top'  ><b>".$objGraph->FormatDotCell($objAttribute->Property->Label)."  </b></td>";
				$dotLabel .= "<td align='left' balign='left' valign='top'>";
				
				if (count($objAttribute->Parts) == 0){
					$dotLabel .= $objGraph->FormatDotCell(truncate($objAttribute->Statement->ValueLabel),100);					
				}
				else
				{
					$dotLabel .= $this->getDotPartsTable($objGraph, $objAttribute);
				}
				
				$dotLabel .= "</td>";
				$dotLabel .= "</tr>";				
			}			
			
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';
		}

		$ParamSid = $this->System->Session->ParamSid;
		
		$NodeId = null;
		if (!isset($objDot->uriSubjects[$this->Uri])){
			$NodeId = 'subject_'.(count($objDot->uriSubjects) + 1);
			$objGraph->addNode($NodeId, $dotLabel, $dotShape, $Color,$Height, $Width, "subject.php?$ParamSid&urisubject=".$this->Uri, null, null, $FontColor);
			$objDot->uriSubjects[$this->Uri] = $NodeId;
		}


		if ($Depth > $Level){			
			
			$this->getLinks();			

			$arrLinks = array();
			foreach ($this->Links as $objLink){
				
				$objLinkSubject = $objLink->ToSubject;
				$LinkObjectId = null;
				if ($objLink->FromSubject->Uri !== $this->Uri){
					$objLinkSubject = $objLink->FromSubject;
				}
				$objLinkSubject->getObject();
				if (!is_null($objLinkSubject->Object)){
					$LinkObjectId = $objLinkSubject->Object->Id;
				}
				
				if (!is_null($LinkObjectId)){
					$arrLinks[$objLink->uriRelationship][$LinkObjectId][] = $objLink;
				}
			}

			foreach ($this->Class->AllRelationships as $objRelationship){
				if (!isset($arrLinks[$objRelationship->Uri])){
					continue;
				}
								
				for ($i = 1; $i <= 2; $i++) {
					switch ($i){
						case 1:
							$Inverse = false;
							$RelLabel = $objRelationship->Label;
							$objLinkClass = $objRelationship->ToClass;
							break;
						case 2:
							$Inverse = true;
							$RelLabel = $objRelationship->Label;						
							$objLinkClass = $objRelationship->FromClass;
					}
					
					$RelLabel = $objGraph->FormatDotLabel($RelLabel);
					$ArrowHead = null;
					$ArrowTail = null;
					if ($objRelationship->Extending){
						$ArrowTail = 'diamond';
						$ArrowHead = "none";
					}
					
					
					foreach ($arrLinks[$objRelationship->Uri] as $LinkObjectId=>$arrObjectLinks){
						$LinkNodeId = null;					
						$RelSubjects = new clsSubjects();
						
						$objLinkObject = null;
						if (!is_null($LinkObjectId)){
							$objLinkObject = $this->Archetypes->Objects[$LinkObjectId];
						}
							
						foreach ($arrObjectLinks as $objLink){
							$objLinkSubject = null;
							switch ($Inverse){
								case false:
									if ($objLink->FromSubject->Uri == $this->Uri){
										$objLinkSubject = $objLink->ToSubject;
									};
									break;
								default:
									if ($objLink->ToSubject->Uri == $this->Uri){
										$objLinkSubject = $objLink->FromSubject;
									};
									break;
							}
							if (!is_null($objLinkSubject)){
								$RelSubjects->addItem($objLinkSubject);
							}
						}
					
						if (count($RelSubjects->Items) >0){
							switch ($objDot->Style){
								case 1:
	
									foreach ($RelSubjects->Items as $objLinkSubject){
	
										$objLinkSubject->getDot($objDot, $objGraph, $Scale * 0.8, $Depth, $Level + 1);
										$LinkNodeId = null;
										if (isset($objDot->uriSubjects[$objLinkSubject->Uri])){
											$LinkNodeId = $objDot->uriSubjects[$objLinkSubject->Uri];
											
	
											if (!is_null($LinkNodeId)){
												$FromNodeId = $NodeId;
												$ToNodeId = $LinkNodeId;
												if ($Inverse){
													$ToNodeId = $NodeId;
													$FromNodeId = $LinkNodeId;
												}
												if (!is_null($FromNodeId) && (!is_null($LinkNodeId))){
													$objGraph->addEdge($FromNodeId, $ToNodeId, $RelLabel, null,  null, $ArrowHead, $ArrowTail);
												}
											}
										}
									}
									
									
									break;
								case 2:									
									if ((count($RelSubjects->Items) == 1) || ( $Depth > $Level+1)){	
										foreach ($RelSubjects->Items as $objLinkSubject){
											$objLinkSubject->getDot($objDot, $objGraph, null, $Depth, $Level + 1);
											$LinkNodeId = null;
											if (isset($objDot->uriSubjects[$objLinkSubject->Uri])){
												$LinkNodeId = $objDot->uriSubjects[$objLinkSubject->Uri];
											}
										}
									}
									elseif (count($RelSubjects->Items) > 1){
										
										$dotLabel = $objLinkClass->Label;
										if ($objDot->Style == 2){
											$dotLabel = "<";
																			
											$objList = new clsShocList();
											$objList->Subjects = $RelSubjects;
											$objList->Class = $objLinkClass;
											$objList->Object = $objLinkObject;
											
											$dotLabel .= $objList->dot;
											$dotLabel .= ">";			
											$dotShape = 'plaintext';
											
											if (is_object($objLinkObject)){
												if (!isset($objDot->keys[$objLinkObject->Id])){
													$objKey = new clsShocDotKey();
													$objKey->Legend = $objLinkObject->Label;
													$objKey->Color = $objLinkObject->Color;
													$objDot->keys[$objLinkObject->Id] = $objKey;
												}			
											}
										}
			
										$ListNodeId = 'subjectlist_'.$objDot->NextSubjectListNum++;
										
										$objGraph->addNode($ListNodeId, $dotLabel, $dotShape, null ,null, null, null, null, null);
										
										$LinkNodeId = $ListNodeId;
										
									}
									
									if (!is_null($LinkNodeId)){
										$FromNodeId = $NodeId;
										$ToNodeId = $LinkNodeId;
										if ($Inverse){
											$ToNodeId = $NodeId;
											$FromNodeId = $LinkNodeId;
										}
										if (!is_null($FromNodeId) && (!is_null($LinkNodeId))){
											$objGraph->addEdge($FromNodeId, $ToNodeId, $RelLabel, null, null, $ArrowHead, $ArrowTail);
										}
									}
									break;
							}
							
						}
					}
				}				
			}
			
		}
		
		
		if ($Top){
			$objDot->makeKey($objGraph);
		}
		
		

		$this->dot = $objGraph->script;
		return $this->dot;		
		
		
	}



	public function getDotPartsTable($objGraph = null, $objAttribute){

		$dotLabel = '';

		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
						
		$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			
		foreach ($objAttribute->Parts as $objAttributePart){
			$dotLabel .= "<tr>";
			$dotLabel .= "<td align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($objAttributePart->Property->Label)."</td>";
			$dotLabel .= "<td align='left' balign='left' valign='top'>";
				
			if (count($objAttributePart->Parts) == 0){
				$dotLabel .= $objGraph->FormatDotCell($objAttributePart->Statement->ValueLabel);					
			}
			else
			{
				$dotLabel .= $this->getDotPartsTable($objGraph, $objAttributePart);
			}
				
			$dotLabel .= "</td>";
			$dotLabel .= "</tr>";				
		}			
			
		$dotLabel .= "</table>";

		return $dotLabel;		
		
		
	}
	
	
	
	private function getTitle(){

		if (!is_null($this->Title)){
			return $this->Title;
		}
		$this->Title = "";

		$arrTitleProperties = null;
		
		$arrTitleProperties = array();
		if (is_object($this->Class)){			
			foreach ($this->System->Config->Vars['settings']['titleproperties'] as $uriTitleProperty){
				$arrTitleProperties = $arrTitleProperties + $this->Class->getPropertiesForSuperProperty($uriTitleProperty);
			}
		}
	
		if (count($arrTitleProperties) == 0){
			if (is_object($this->Object)){
				$AllProperties = $this->Object->Class->AllProperties;			
				if (current($AllProperties)){
					$arrTitleProperties[] = current($AllProperties);
				}
			}
		}
			
		if (!is_null($arrTitleProperties)){
			foreach ($this->getAttributes() as $objAttribute){
				foreach ($arrTitleProperties as $TitleProperty){
					if ($objAttribute->Property->Uri == $TitleProperty->Uri){
						if ($this->Title != ""){
							$this->Title .= "-";
						}
						$this->Title .= $objAttribute->Statement->ValueLabel;
					}
				}
			}		
		}		
		return $this->Title;
	}	
	
	
	
	private function getBox(){
		if (!is_null($this->Box)){
			return $this->Box;
		}
		
		$this->getRevisions();
		
		$this->Revisions = $this->getRevisions();
		$objRevision = reset($this->Revisions);
		if (is_object($objRevision)){
			$objDocument = $objRevision->Document;
			$this->Box = $objDocument->Box;
		}
		return $this->Box;
		
	}
	
	
	
	
}

class clsShocDot{
	public $Style = 1;

	public $uriSubjects = array();
	public $uriLinks = array();
	public $NextSubjectListNum = 0;
	public $keys = array();

	public function makeKey($objGraph){
		
		if (count($this->keys) > 0){
			$objGraphKey = $objGraph->addSubGraph('cluster','Key');
			
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellpadding = '2' cellborder='0' cellspacing='0'>";
			foreach ($this->keys as $objKey){
				$dotLabel .= "<tr>";
				$dotLabel .= "<td align='left' balign='left' valign='top' bgcolor='".$objKey->Color."'> </td>";
				$dotLabel .= "<td align='left' balign='left' valign='top'>".$objKey->Legend."</td>";
				$dotLabel .= "</tr>";				
			}			
			
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';

			$objGraphKey->addNode('key', $dotLabel, $dotShape);
		}
	}	
	
}

class clsShocDotKey{
	public $Legend = null;
	public $Color = null;
	public $Shape = null;
}

class clsAttribute{
	public $Subject = null;
	public $Property = null;
	public $Statement = null;
	public $Parts = array();
	
	
	public function getParts(){
		
		foreach ($this->Property->Parts as $objPropertyPart){
			foreach ($this->Subject->objStatements->Items as $objStatement){
				if ($objStatement->uriPartOf == $this->Statement->Uri){								
					if ($objStatement->uriProperty == $objPropertyPart->Uri){
						$objAttribute = new clsAttribute();
						$objAttribute->Subject = $this->Subject;
						$objAttribute->Property = $objPropertyPart;
						$objAttribute->Statement = $objStatement;
						$objAttribute->getParts();
						$this->Parts[] = $objAttribute;					
					}
				}
			}
			
		}
		
		
	}
	
}	


class clsLink{
	
	public $Uri = null;
	public $uriFromSubject = null;
	public $uriToSubject = null;
	public $uriRelationship = null;
	public $RelId = null;
	public $Document = null;
	
	public $BoxLink = null;
	private $Box = null;

	private $Description = null;
			
	public $Id = null;

	private $FromSubject = null;
	private $ToSubject = null;
	private $Relationship = null;
	
	
	private $Revisions = null;
	private $Revision = null;
	private $Attributes = null;
	
	private $objStatements = null;
		
	private $dot = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	private $Shoc = null;
	private $Models = null;
	private $Archetypes = null;
	
	
	public function __construct($Uri = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		global $Shoc;
		if (!isset($Shoc)){
			$Shoc = new clsShoc();
		}		
		$this->Shoc = $Shoc;
		
		
		$gObjects = gObjects();
		
		$this->Uri = $Uri;
		
		$objSparql = new clsSparql();
		
		$xmlResults = $objSparql->Describe($Uri);
		
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->loadXMl($xmlResults);
		
		$DefaultNS = $dom->lookupNamespaceUri($dom->namespaceURI);

		
		$xpath = new domxpath($dom);

		$xpath->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpath->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpath->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpath->registerNamespace('shoc', clsShoc::prefixSHOC);

		
		$xmlUri = $xpath->query("*[@rdf:about='".$this->Uri."'][1]")->item(0);
		
		if ($xmlUri){

			$xmlId = $xpath->query("shoc:id[1]", $xmlUri)->item(0);
			if ($xmlId){
				$this->Id = $xmlId->nodeValue;
			}

			$xmlRelationship = $xpath->query("shoc:relationship[1]", $xmlUri)->item(0);
			if ($xmlRelationship){
				$this->uriRelationship = $xmlRelationship->getAttribute("rdf:resource");
			}

			$xmlIdRel = $xpath->query("shoc:idRel[1]", $xmlUri)->item(0);
			if ($xmlIdRel){
				$this->RelId = $xmlIdRel->nodeValue;
			}

			$xmlFromSubject = $xpath->query("shoc:fromSubject[1]", $xmlUri)->item(0);
			if ($xmlFromSubject){
				$this->uriFromSubject = $xmlFromSubject->getAttribute("rdf:resource");
			}
			
			$xmlToSubject = $xpath->query("shoc:toSubject[1]", $xmlUri)->item(0);
			if ($xmlToSubject){
				$this->uriToSubject = $xmlToSubject->getAttribute("rdf:resource");
			}
			

		}
		
/*
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
*/		
		
		if ($System->LoggedOn){
			$this->getBox();
			if (is_object($this->Box)){
				$this->canView = $this->Box->canView;
				$this->canEdit = $this->Box->canEdit;
				$this->canControl = $this->Box->canEdit;
			}
		}
		
		$gObjects->Items[$this->Uri] = $this;
		
	}	

	
	
	public function __get($name){
		switch ($name){
			case 'objStatements':
				$this->getStatements();
				break;				
			case 'Revisions':
				$this->getRevisions();
				break;								
			case 'Revision':
				$this->getRevision();
				break;								
				
			case 'Attributes':
				$this->getAttributes();
				break;												
			case 'dot':
				$this->dot = $this->getDot();
				break;
			case 'FromSubject':
				$this->getFromSubject();
				break;
			case 'ToSubject':
				$this->getToSubject();
				break;
			case 'Relationship':
				$this->getRelationship();
				break;
			case 'Description':
				$this->getDescription();
				break;
			case 'Box':
				$this->getBox();
				break;				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	
	public function __set($name, $value){
		switch ($name){
			case 'description':
				$this->Description = $value;
				break;
		}
	}
	
	
	public function getRelationship(){

		if (!is_null($this->Relationship)){
			return $this->Relationship;
		}
		
		if (is_null($this->RelId) && is_null($this->uriRelationship)){
			return null;
		}

		$this->getModels();
		
		if (isset($this->Models->Relationships[$this->RelId])){
			$this->Relationship = $this->Models->Relationships[$this->RelId];
		}
		else
		{	
			if (isset($this->Models->uriRelationships[$this->uriRelationship])){
				$this->Relationship = $this->Models->uriRelationships[$this->uriRelationship];
			}
		}
		
		return $this->Relationship;
		
	}
	
	private function getBox(){
		if (!is_null($this->Box)){
			return $this->Box;
		}
		if (!is_null($this->BoxLink)){
			$this->Box = $this->BoxLink->Box;
			return $this->Box;
		}
		
		$this->getRevision();
		if (is_object($this->Revision)){
			$this->Box = $this->Revision->Document->Box;
		}
		
	}
	
	public function getFromSubject(){

		if (!is_null($this->FromSubject)){
			return $this->FromSubject;
		}
		
		if (is_null($this->uriFromSubject)){
			return null;
		}		
		
//		$this->FromSubject = new clsSubject($this->uriFromSubject);
		$this->FromSubject = $this->Shoc->getSubject($this->uriFromSubject);
		
		return $this->FromSubject;
		
	}

	public function getToSubject(){

		if (!is_null($this->ToSubject)){
			return $this->ToSubject;
		}
		
		if (is_null($this->uriToSubject)){
			return null;
		}		
		
//		$this->ToSubject = new clsSubject($this->uriToSubject);
		$this->ToSubject = $this->Shoc->getSubject($this->uriToSubject);
		
		return $this->ToSubject;
		
	}
	
	
	private function getStatements(){
		if (!is_null($this->objStatements)){
			return $this->objStatements;
		}
		$this->objStatements = new clsStatements();
		$this->objStatements->forLink($this);
		
		return $this->objStatements;
				
	}

	private function getAttributes(){

		if (!is_null($this->Attributes)){
			return $this->Attributes;
		}
		$this->Attributes = array();

		$this->getStatements();

//		$uriDescription = clsShoc::nsSHOC.'description';
		
		$objProperty = new clsProperty;
		$objProperty->Uri = clsShoc::nsSHOC.'description';
		$objProperty->Name = 'Description';
		$objProperty->Label = $objProperty->Name;
		
		foreach ($this->objStatements->Items as $objStatement){
			if ($objStatement->uriProperty == $objProperty->Uri){
				$objAttribute = new clsAttribute();
				$objAttribute->Subject = $this;
				$objAttribute->Property = $objProperty;
				$objAttribute->Statement = $objStatement;
				$this->Attributes[] = $objAttribute;					
			}
		}
			
		return $this->Attributes;
	}
	

	private function getDescription(){

		if (!is_null($this->Description)){
			return $this->Description;
		}
		$this->Description = '';
		$this->getAttributes();

		$uriDescription = clsShoc::nsSHOC.'description';
		foreach ($this->Attributes as $objAttribute){
			if ($objAttribute->Property->Uri == $uriDescription){
				$this->Description = $objAttribute->Statement->ValueLabel;
			}
		}
			
		return $this->Description;
	}
	
	
	
	private function getRevisions(){
		
		if (!is_null($this->Revisions)){
			return $this->Revisions;
		}

		global $gObjects;

		$this->Revisions = array();
				
		$uriLink = $this->Uri;
		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsShoc::prefixSHOC.">";
		
		$Query = "SELECT DISTINCT ?uriRevision WHERE {
			?uriRevision shoc:about ?About.
			?About shoc:link <$uriLink>.
		
			OPTIONAL { ?uriRevision dct:time ?DateTime }.			
		}
		ORDER BY DESC(?DateTime)";
	
		$xmlResults = $objSparql->Query($Query);
//echo '<br/>'.htmlentities($objSparql->Query).'<br/>';		
		$domRevisions = new DOMDocument('1.0', 'utf-8');
		$domRevisions->formatOutput = true;
		$domRevisions->loadXMl($xmlResults);

		$xpathRevisions = new domxpath($domRevisions);
				
		$xpathRevisions->registerNamespace('sparql', 'http://www.w3.org/2005/sparql-results#');			
		$xpathRevisions->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');	
		$xpathRevisions->registerNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');	
		$xpathRevisions->registerNamespace('dct', 'http://purl.org/dc/terms/');	
		$xpathRevisions->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema#');

		$xpathRevisions->registerNamespace('shoc', clsShoc::prefixSHOC);
		
		$nodelistRevisions = $xpathRevisions->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uriRevision']/sparql:uri");
		
		foreach ($nodelistRevisions as $xmlUriRevision){
			$uriRevision = $xmlUriRevision->nodeValue;
			$objRevision = $this->Shoc->getRevision($uriRevision);
			$this->Revisions[$uriRevision] = $objRevision;
		}		
		
	}
	
	public function RevisionsForDateTime($DateTime = null){

		$this->getRevisions();

		if (is_null($DateTime)){
			$DateTime = time();
		}
		
		$arrRevisions = array();
				
		foreach ($this->Revisions as $objRevision){
			if ($objRevision->Timestamp > $DateTime){
				continue;
			}
			
			if (!isset($arrRevisions[$objRevision->Document->Uri])){
				$arrRevisions[$objRevision->Document->Uri] = $objRevision;
			}
			else
			{
				if ($objRevision->Timestamp > $arrRevisions[$objRevision->Document->Uri]->Timestamp){
					$arrRevisions[$objRevision->Document->Uri] = $objRevision;
				}
			}
		}
		return $arrRevisions;		
	}
	
	private function getRevision(){
		
		if (!is_null($this->Revision)){
			return $this->Revision;
		}
		
		$this->getRevisions();
		foreach ($this->Revisions as $objRevision){
			if (is_null($this->Revision)){
				if ($objRevision->Document->Type == 'link'){
					$this->Revision = $objRevision;
				}
			}
		}
		return $this->Revision;
		
	}
	
	
	private function getModels(){
		
		if (!is_null($this->Models)){
			return $this->Models;
		}
		
		global $Models;
		if (!isset($Models)){
			$Models = new clsModels();
		}		
		$this->Models = $Models;
		return $this->Models;
		
	}

	
	private function getArchetypes(){
		if (isset($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->getModels();
		
		global $Archetypes;
		if (!isset($Archetypes)){
			$Archetypes = new clsArchetypes($this-Models);
		}		
		$this->Archetypes = $Archetypes;
		return $this->Archetypes;
		
	}
	
	
	
	private function getForm(){
		if (!is_null($this->Form)){
			return $this->Form;
		}
		
		$this->Form = new clsForm();
		$this->Form->Document = $this;
		
		return $this->Form;
		
	}
	
	
	public function getDot($objDot = null, $objGraph = null){
		if (!is_null($this->dot)){
			return $this->dot;
		}
		$this->dot = '';

		$Top = false;
		if (is_null($objDot)){
			$objDot = new clsShocDot();
			$objDot->Style = 2;
			$Top = true;
		}

		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
		
		
		if (!is_null($this->FromSubject)){
			$this->FromSubject->getDot($objDot, $objGraph);
		}

		if (!is_null($this->ToSubject)){
			$this->ToSubject->getDot($objDot, $objGraph);
		}

		if (isset($objDot->uriSubjects[$this->FromSubject->Uri])){
			if (isset($objDot->uriSubjects[$this->ToSubject->Uri])){
				$dotLabel = $objGraph->FormatDotLabel($this->Relationship->Label);						
				$objGraph->addEdge($objDot->uriSubjects[$this->FromSubject->Uri],$objDot->uriSubjects[$this->ToSubject->Uri], $dotLabel);
			}			
		}
		
		
		$this->dot = $objGraph->script;
		return $this->dot;		
		
	}
	
	
	

}


class clsForm {

	public $dom = null;
	private $xml = null;
	public $xpath = null;

	private $Document = null;
	private $Revision = null;
	private $Object = null;
	private $Type = 'subject';
	
//	private $Template = null;

	private $idField = 0;

	public function __set($name, $value){
		switch ($name){
			case 'Document':
				$this->Document = $value;
				$this->Object = $this->Document->Object;
				$this->Type = $this->Document->Type;
				break;			
			case 'Revision':
				$this->Revision = $value;
				$this->Document = $this->Revision->Document;
				$this->Object = $this->Document->Object;
				$this->Type = $this->Revision->Document->Type;
				
				break;
			case 'Object':
				$this->Object = $value;
				break;
			case 'Type':
				$this->Type = $value;
				break;				
				
		}
	}
	
	public function __get($name){
		switch ($name){
			case 'xml':
				$this->xml = $this->getXml();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	

	public function loadXml($xmlString){
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
		
		$this->dom->loadXML($xmlString);
		$this->xml = $this->dom->documentElement;
		$this->RefreshXpath();
		
	}
	
	
	private function getXml(){

		if (is_null($this->xml)){
		
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;
	
			$DocumentElement = $this->dom->createElementNS(clsShoc::nsSHOC, 'Form');				
			$DocumentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', clsShoc::nsXSD);
	
			$this->dom->appendChild($DocumentElement);

			if (is_object($this->Document)){
				$DocumentElement->setAttribute('uriDocument',$this->Document->Uri);
			}
			
			if (!is_null($this->Revision)){
				$DocumentElement->setAttribute('uriRevision',$this->Revision->Uri);
			}

//			$objObject = $this->Document->Object;
			$objObject = $this->Object;
			
			switch ($this->Type){
				case 'subject':
					if (!is_null($objObject)){			
						$xmlTemplate = $this->getXmlTemplate($DocumentElement, $objObject);
					}
					break;
				case 'link':
					$xmlTemplate = $this->getXmlLinkTemplate($DocumentElement);
					break;
			}
			
			$this->RefreshXpath();
			$xmlStatements = $this->getXmlStatements($DocumentElement, $this->Object);
			$this->RefreshXpath();
			
			$this->xml = $this->dom->saveXML();
			
		}
		return $this->xml;

	}
	
	private function getXmlTemplate($xmlParent, $objObject = null){
		$xmlTemplate = $this->dom->createElementNS(clsShoc::nsSHOC, 'Template');
		
		if (!is_object($objObject)){
			return $xmlTemplate;
		}
		
		$xmlTemplate->setAttribute('id',$objObject->Id);
		$xmlParent->appendChild($xmlTemplate);

		$xmlSections = $this->dom->createElementNS(clsShoc::nsSHOC, 'Sections');
		$xmlTemplate->appendChild($xmlSections);		
		
		$xmlSection = $this->dom->createElementNS(clsShoc::nsSHOC, 'Section');
		$xmlSections->appendChild($xmlSection);			
		
		$xmlSection->setAttribute("idObject",$objObject->Id);
		if ($objObject->Start){
			$xmlSection->setAttribute("start",'true');
		}
		
		$xmlPrompt = $this->dom->createElementNS(clsShoc::nsSHOC, 'Prompt');
		$xmlPrompt->nodeValue = $objObject->Label;
		$xmlSection->appendChild($xmlPrompt);
		
		foreach ($objObject->ObjectProperties as $objObjectProperty){
			$this->getXmlTemplateObjectProperty($objObjectProperty, $xmlSection);
		}
				
				
						
/*		
						$MaxLength = xmlElementValue($xmlProperty, 'maxLength');
						if (!is_null($MaxLength)){
							$xmlResponse->setAttribute('maximumCharacterQuantity',$MaxLength);
						}
		
						$xmlTextField = $this->dom->createElementNS(clsDocuments::nsFDR, 'TextField');
						
						if ($NodeKind == "sh:IRI"){
							$xmlTextField->setAttribute('type',150);
						}
*/						
//						$xmlResponse->appendChild($xmlTextField);	
	
		
		$xmlRelationships = $this->dom->createElementNS(clsShoc::nsSHOC, 'Relationships');
		$xmlTemplate->appendChild($xmlRelationships);		

/*
		foreach ($objTemplate->Relationships as $objRelationship){
			$xmlRelationship = $this->dom->createElementNS(clsShoc::nsSHOC, 'Relationship');
			$xmlRelationships->appendChild($xmlRelationship);
			
			$xmlRelationship->setAttribute("id",$objRelationship->Id);
			if ($objRelationship->Inverse){
				$xmlRelationship->setAttribute("inverse",'true');
			}
			
			if ($objRelationship->Relationship->Extending){
				$xmlRelationship->setAttribute("extending",'true');
			}
			

			if ($objRelationship->Create){
				$xmlRelationship->setAttribute("create",'true');
			}
			if ($objRelationship->Select){
				$xmlRelationship->setAttribute("select",'true');
			}
						
//			$xmlRelationship->setAttribute("idRelationship",$objRelationship->RelationshipId);
			$xmlRelationship->setAttribute("idFromObject",$objRelationship->FromObjectId);
			$xmlRelationship->setAttribute("idToObject",$objRelationship->ToObjectId);
			$xmlRelationship->setAttribute("cardinality",$objRelationship->Cardinality);
			
			
			$xmlPrompt = $this->dom->createElementNS(clsShoc::nsSHOC, 'Prompt');
			$xmlPrompt->nodeValue = $objRelationship->Label;
			$xmlRelationship->appendChild($xmlPrompt);
			
		}
*/		
		
		return $xmlTemplate;

	}

	
	
	private function getXmlTemplateObjectProperty($objObjectProperty, $xmlParent){
		
		$this->idField++;
				
		$objObjectProperty->idField = $this->idField;
								
		$xmlQuestion = $this->dom->createElementNS(clsShoc::nsSHOC, 'Question');
		$xmlQuestion->setAttribute('idField',$this->idField);
		$xmlQuestion->setAttribute('idObjectProperty',$objObjectProperty->Id);

		$xmlParent->appendChild($xmlQuestion);
		$xmlPrompt = $this->dom->createElementNS(clsShoc::nsSHOC, 'Prompt');
		$xmlPrompt->nodeValue = $objObjectProperty->Property->Label;
		$xmlQuestion->appendChild($xmlPrompt);
		
		$xmlQuestion->setAttribute('cardinality',$objObjectProperty->Cardinality);
								
		if (count($objObjectProperty->Parts) > 0 ){

			$xmlParts = $this->dom->createElementNS(clsShoc::nsSHOC, 'Parts');
			$xmlQuestion->appendChild($xmlParts);
			
			foreach ($objObjectProperty->Parts as $objObjectPropertyPart){
				$this->getXmlTemplateObjectProperty($objObjectPropertyPart, $xmlParts);
			}
					
		}
		else 
		{
		
			$xmlResponses = $this->dom->createElementNS(clsShoc::nsSHOC, 'Responses');
			$xmlQuestion->appendChild($xmlResponses);
			
			
			$xmlResponse = $this->dom->createElementNS(clsShoc::nsSHOC, 'Response');
			$xmlResponses->appendChild($xmlResponse);
			
			if (count($objObjectProperty->Property->Lists) > 0){
				$xmlLists = $this->dom->createElementNS(clsShoc::nsSHOC, 'Lists');
				$xmlResponse->appendChild($xmlLists);
				foreach ($objObjectProperty->Property->Lists as $objList){
					$xmlList = $this->dom->createElementNS(clsShoc::nsSHOC, 'List');
					$xmlLists->appendChild($xmlList);
					$xmlList->setAttribute('id',$objList->Id);
					foreach ($objList->Terms as $objTerm){
						$xmlTerm = $this->dom->createElementNS(clsShoc::nsSHOC, 'Term');
						$xmlList->appendChild($xmlTerm);
						$xmlTerm->setAttribute('id',$objTerm->Id);
						$xmlLabel = $this->dom->createElementNS(clsShoc::nsSHOC, 'Label');
						$xmlTerm->appendChild($xmlLabel);
						$xmlLabel->nodeValue = $objTerm->Label;							
					}						
				}
			}
			
			if (is_object($objObjectProperty->Property->DataType)){
					
				$xmlDataType = $this->dom->createElementNS(clsShoc::nsSHOC, 'DataType');
				$xmlResponse->appendChild($xmlDataType);
				$xmlDataType->nodeValue = $objObjectProperty->Property->DataType->Name;
				$xmlDataType->setAttribute('uri',$objObjectProperty->Property->DataType->Uri);
			}
			
			if (!is_null($objObjectProperty->Property->MinLength)){
				$xmlResponse->setAttribute('minLength',$objObjectProperty->Property->MinLength);						
			}
			if (!is_null($objObjectProperty->Property->MaxLength)){
				$xmlResponse->setAttribute('maxLength',$objObjectProperty->Property->MaxLength);						
			}
			if (!is_null($objObjectProperty->Property->Pattern)){
				$xmlPattern = $this->dom->createElementNS(clsShoc::nsSHOC, 'Pattern');
				$xmlResponse->appendChild($xmlPattern);
				$xmlPatern->nodeValue = $objObjectProperty->Property->Pattern;					
			}
		}
	}
	
	
	
	private function getXmlLinkTemplate($xmlParent){
		$xmlTemplate = $this->dom->createElementNS(clsShoc::nsSHOC, 'Template');
		
		$xmlParent->appendChild($xmlTemplate);
		
		$xmlRelationships = $this->dom->createElementNS(clsShoc::nsSHOC, 'Relationships');
		$xmlTemplate->appendChild($xmlRelationships);		
		
		if (!is_null($this->Document->FromSubject)){
			foreach ($this->Document->FromSubject->Class->AllRelationships as $objRelationship){
				if ($objRelationship->ToClass === $this->Document->ToSubject->Class){
					$xmlRelationship = $this->dom->createElementNS(clsShoc::nsSHOC, 'Relationship');
					$xmlRelationships->appendChild($xmlRelationship);

					$xmlRelationship->setAttribute("id",$objRelationship->Id);
					
					if ($objRelationship->Extending){
						$xmlRelationship->setAttribute("extending",'true');
					}

					$xmlRelationship->setAttribute("idFromClass",$objRelationship->FromClassId);
					$xmlRelationship->setAttribute("idToClass",$objRelationship->ToClassId);
										
					$xmlLabel = $this->dom->createElementNS(clsShoc::nsSHOC, 'Label');
					$xmlLabel->nodeValue = $objRelationship->Label;
					$xmlRelationship->appendChild($xmlLabel);					
				}
			}
		}
		
		return $xmlTemplate;

	}
	
	
	
	
	private function getXmlStatements($xmlParent, $objTemplate = null){
		$xmlStatements = $this->dom->createElementNS(clsShoc::nsSHOC, 'Statements');
		$xmlParent->appendChild($xmlStatements);
		
		$NextSubjectId = 1;
		$arrSubjects = array();

		if (is_object($this->Revision)){

			foreach ($this->Revision->Abouts as $objAbout){
				$idSubject = $NextSubjectId++;						
				if (!is_null($objAbout->uriSubject)){
					$arrSubjects[$objAbout->uriSubject] = $idSubject;
				}				
			}

			
			switch ($this->Type){
				case 'link':
					break;
				default:
					
					$objObject = $this->Document->Object;
						
					foreach ($this->Revision->Abouts as $objAbout){
						if ($objAbout->idObject == $objObject->Id){
							$xmlAbout = $this->dom->createElementNS(clsShoc::nsSHOC, 'About');
							$xmlAbout->setAttribute("idObject",$objAbout->idObject);
		
							if (!is_null($objAbout->uriSubject)){
								if (isset($arrSubjects[$objAbout->uriSubject])){
									$xmlAbout->setAttribute("idSubject",$arrSubjects[$objAbout->uriSubject]);
								}
								$xmlAbout->setAttribute("uriSubject",$objAbout->uriSubject);
							}
							$xmlStatements->appendChild($xmlAbout);
		
							foreach ($objObject->ObjectProperties as $objObjectProperty){
								foreach ($this->Revision->objStatements->Items as $objStatement){
									if ($objStatement->uriSubject == $objAbout->uriSubject){
										if ($objStatement->uriProperty == $objObjectProperty->Property->Uri){
											
											$xmlStatement = $this->dom->createElementNS(clsShoc::nsSHOC, 'Statement');
											$xmlStatement->setAttribute('uri', $objStatement->Uri);
											$xmlStatement->setAttribute('idField', $objObjectProperty->idField);
										
											$xmlAbout->appendChild($xmlStatement);
																
											if (count($objObjectProperty->Parts) > 0){
												$this->getXmlStatementParts($objStatement, $xmlStatement, $objObjectProperty->Parts);
											}
											else
											{										
												$xmlValue = $this->dom->createElementNS(clsShoc::nsSHOC, 'Value');
												$xmlValue->nodeValue = $objStatement->Value;
												$xmlStatement->appendChild($xmlValue);
											}
										}
									}
								}
							}
							
							foreach ($objObject->Relationships as $objRelationship){
								
								foreach ($this->Revision->objStatements->Items as $objStatement){
									if ($objStatement->uriRelationship == $objRelationship->Relationship->Uri){								
										if (!$objRelationship->Inverse){
											if ($objStatement->uriSubject == $objAbout->uriSubject){
												$xmlStatement = $this->dom->createElementNS(clsShoc::nsSHOC, 'Statement');
												$xmlStatement->setAttribute('uri', $objStatement->Uri);
												$xmlStatement->setAttribute('idRelationship', $objRelationship->Id);								
												$xmlAbout->appendChild($xmlStatement);
		
												
												if (!is_null($objStatement->uriLinkSubject)){
													if (isset($arrSubjects[$objStatement->uriLinkSubject])){
														$xmlStatement->setAttribute("idLinkSubject",$arrSubjects[$objStatement->uriLinkSubject]);
													}
												}
												$xmlStatement->setAttribute('uriLinkSubject', $objStatement->uriLinkSubject);
											}
										}
										else
										{
											if ($objStatement->uriLinkSubject == $objAbout->uriSubject){
												$xmlStatement = $this->dom->createElementNS(clsShoc::nsSHOC, 'Statement');
												$xmlStatement->setAttribute('uri', $objStatement->Uri);
												$xmlStatement->setAttribute('idRelationship', $objRelationship->Id);								
												$xmlAbout->appendChild($xmlStatement);
		
												if (!is_null($objStatement->uriSubject)){
													if (isset($arrSubjects[$objStatement->uriSubject])){
														$xmlStatement->setAttribute("idLinkSubject",$arrSubjects[$objStatement->uriSubject]);
													}
												}											
												$xmlStatement->setAttribute('uriLinkSubject', $objStatement->uriSubject);
											}										
										}
									}								
								}
							}
						
						}
		
					}
					break;
			}
		}
		
		return $xmlStatements;
		
	}

	
	private function getXmlStatementParts($objParentStatement, $xmlParent, $Parts){
		
		foreach ($Parts as $objObjectProperty){
			foreach ($this->Revision->objStatements->Items as $objStatement){
				if ($objStatement->uriPartOf == $objParentStatement->Uri){
					if ($objStatement->uriProperty == $objObjectProperty->Property->Uri){
						
						$xmlStatement = $this->dom->createElementNS(clsShoc::nsSHOC, 'Statement');
						$xmlStatement->setAttribute('uri', $objStatement->Uri);
						$xmlStatement->setAttribute('idField', $objObjectProperty->idField);
									
						$xmlParent->appendChild($xmlStatement);
															
						if (count($objObjectProperty->Parts) > 0){
							$this->getXmlStatementParts($objStatement, $xmlStatement, $objObjectProperty);
						}
						else
						{										
							$xmlValue = $this->dom->createElementNS(clsShoc::nsSHOC, 'Value');
							$xmlValue->nodeValue = $objStatement->Value;
							$xmlStatement->appendChild($xmlValue);
						}
					}
				}
			}
		}
	}
	
	
	private function RefreshXpath(){
					
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('xsd', clsShoc::nsXSD);
		
		$this->xpath->registerNamespace('shoc', clsShoc::nsSHOC);
		
	}
	
}


class clsListHeading {
	
	private $Class = null;
	private $Object = null;
	private $Labels = array();
	private $numRows = 0;
	
	private $html = null;
	private $dot = null;
	
	public $ShowClass = true;
	
	public function __construct($Class){
		$this->Class = $Class;
	}
	
	public function __get($name){
		switch ($name){
			case 'html':
				$this->getHtml();
				break;				
			case 'dot':
				$this->getDot();
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	
	public function __set($name, $value){
		switch ($name){
			case 'Object':
				$this->Object = $value;
				$this->Class = $this->Object->Class;
				break;
		}
		return true;
	}
	
	
	private function getHtml(){

		if (!is_null($this->html)){
			return $this->html;
		}

//		$this->MakeLabels($this->Labels);

		if (is_object($this->Object)){
			$this->MakeObjectLabels($this->Object);			
		}
		else
		{
			$this->MakeClassLabels();			
		}
		
		$this->html = $this->Make();
		
		return $this->html;

	}

	
	private function getDot(){

		if (!is_null($this->dot)){
			return $this->dot;
		}

		$this->MakeClassLabels();
		
		$this->dot = $this->MakeDot();
		
		return $this->dot;

	}
	
	
	private function MakeClassLabels($Class = null){
		
		if (is_null($Class)){
			$Class = $this->Class;
		}

		$this->MakeLabels($this->Labels, $Class->AllProperties);		
// do extending relationships

		foreach ($Class->AllRelationships as $objRelationship){
			$useRelationship = false;
			if ($objRelationship->FromClass === $Class){
				if ($objRelationship->Extending){
					$useRelationship = true;
				}
				if ($useRelationship){
					$RelLabel = $objRelationship->Label;
					$objLinkClass = $objRelationship->ToClass;
					
					$objLabel = new clsListLabel();
					$objLabel->Class = $objLinkClass;
					$objLabel->Relationship = $objRelationship;
					$objLabel->Label = $RelLabel .' '.$objLinkClass->Label;
					$this->Labels[] = $objLabel;
					$this->MakeLabels($objLabel->Labels, $objLinkClass->AllProperties);		
				}
			}
		}
	}
	
	
	private function MakeObjectLabels($Object){
		
		$Class = $Object->Class;

		$arrProperties = array();
		foreach ($Object->ObjectProperties as $objObjectProperty){
			$arrProperties[] = $objObjectProperty->Property;
		}		
		
		
		$this->MakeLabels($this->Labels, $arrProperties);
		
		
		
// do extending relationships

		foreach ($Object->Relationships as $objObjectRelationship){
			$objRelationship = $objObjectRelationship->Relationship;
			$useRelationship = false;
			if ($objObjectRelationship->FromObject === $Object){
				if ($objRelationship->Extending){
					$useRelationship = true;
				}
				if ($useRelationship){
					$RelLabel = $objObjectRelationship->Label;
					$objLinkObject = $objObjectRelationship->ToObject;
					
					$objLabel = new clsListLabel();
					$objLabel->Class = $objLinkObject->Class;
					$objLabel->Relationship = $objRelationship;
					$objLabel->Label = $RelLabel .' '.$objLinkObject->Label;
					$this->Labels[] = $objLabel;
					$this->MakeLabels($objLabel->Labels, $objLinkClass->AllProperties);		
				}
			}
		}
	}
	
	private function MakeLabels(&$Labels, $Properties = null){

		if (is_null($Properties)){
			$Properties = $this->Class->AllProperties;
		}
		
		foreach ($Properties as $objProperty){
			if ($objProperty->ShowInLists){
				$objLabel = new clsListLabel();
				$objLabel->Class = $this->Class;
				$objLabel->Property = $objProperty;
				$objLabel->Label = $objProperty->Label;
				$Labels[] = $objLabel;
				$this->MakeLabels($objLabel->Labels, $objProperty->Parts);
			}
		}
	}
	
	
	
	
	
	private function Make(){

		$this->numRows = 0;
		$this->getNumRows();	
		
		$Content = '';

		$Content .= "<thead>";
		
		for ($i = 1; $i <= $this->numRows; $i++) {
			$Content .= '<tr>';
			$Content .= $this->MakeRow($i);
			$Content .= "</tr>";
		}
		$Content .= "</thead>";
		
		return $Content;
		
	}

	
	private function MakeDot(){

		$this->numRows = 0;
		$this->getNumRows();	
		
		$Content = '';

		for ($i = 1; $i <= $this->numRows; $i++) {
			$Content .= '<tr>';
			$Content .= $this->MakeRow($i,'dot');
			$Content .= "</tr>";
		}
		
		return $Content;
		
	}
	
	
	
	private function MakeRow($Level = 1, $Mode='html', $Labels = null, $CurrentLevel= 1){

		$Content = '';
		if (is_null($Labels)){
			$Labels = $this->Labels;
		}

		if ($Level == 1){
			$RowSpan = $this->numRows - $Level + 1;
			
			if ($this->ShowClass){
						
				switch ($Mode){
					case 'dot':
						
						$Content .= "<td";
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
											
						$Content .= " bgcolor='lightblue'";
						
						$Content .= ">";
											
						$Content .= "<b>";
						$Content .= "Class";
						$Content .= "</b>";					
						
						$Content .= "</td>";					
						
						break;					
						
					default:
						$Content .= "<th";
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
						$Content .= ">";
						
						$Content .= "Class";
						
						$Content .= "</th>";
						break;					
				}
			}

						
		}
		
		foreach ($Labels as $objLabel){
			if ($CurrentLevel == $Level){		

				$RowSpan = 1;
				$ColSpan = 1;
				if (count($objLabel->Labels) == 0){
					$RowSpan = $this->numRows - $Level + 1;
				}
				if (count($objLabel->Labels) > 0){
					$ColSpan = count($objLabel->Labels);
				}

				switch ($Mode){
					case 'dot':
						$Content .= "<td";
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
						if ($ColSpan > 1){
							$Content .= " colspan='$ColSpan'";
						}						
						
						$Content .= " bgcolor='lightblue'";						
						
						$Content .= ">";
						
						$Content .= "<b>";
						$Content .= $objLabel->Label;
						$Content .= "</b>";
						$Content .= "</td>";	
						break;					
						
					default:						
						$Content .= "<th";
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
						if ($ColSpan > 1){
							$Content .= " colspan='$ColSpan'";
						}						
						$Content .= ">";
						
						$Content .= $objLabel->Label;
						$Content .= "</th>";						
						break;					
				}
				
			}

			if (count($objLabel->Labels) > 0){
				if ($CurrentLevel < $Level){
					$Content .= $this->MakeRow($Level, $Mode, $objLabel->Labels, $CurrentLevel+1);
				}
			}

		}
		
		return $Content;
		
	}
	
	
	
	private function getNumRows($Level = 1, $Labels = null){
		if (is_null($Labels)){
			$Labels = $this->Labels;
		}
		if ($Level > $this->numRows){
			$this->numRows = $Level;
		}
		foreach ($Labels as $objLabel){
			if (count($objLabel->Labels) >0){
				$this->getNumRows($Level + 1, $objLabel->Labels);
			}
		}
	}
}

class clsListLabel {
	public $Label = '';	
	public $Class = null;
	public $Property = null;
	public $Relationship = null;
	public $Inverse = false;
	public $Labels = array();
}


class clsSubjectList {

	public $dom = null;
	private $xml = null;
	private $xpath = null;	
	
	private $html = null;
	private $dot = null;
	private $csv = null;
	
	public $Subjects = null;
	public $Heading = null;
	public $Class = null;
	public $Object = null;
	
	private $ShowClass = true;
	
	public $Page = 1;
	public $RowsPerPage = 30;
	public $cssClass = 'list';
	
	private $idColumn = 0;
	
	private $System = null;
	
	public function __construct($Subjects, $Class = null, $Object = null){
		
		global $System;
		if (isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;
		
		$this->Subjects = $Subjects;
		$this->Class = $Subjects->Class;
		
		if (!is_null($Class)){
			$this->Class = $Class;
		}

		if (!is_null($Object)){
			$this->Object = $Object;
			$this->Class = $Object->Class;
		}
		
		
		$this->Heading = new clsListHeading($this->Class);
		if (!is_null($this->Object)){
			$this->Heading->Object = $this->Object;
			$this->ShowClass = false;
		}
		
		$this->Heading->ShowClass = $this->ShowClass;
		
	}
	
	public function __get($name){
		switch ($name){
			case 'xml':
			case 'xpath':
				$this->getXml();
				break;
			case 'html':
				$this->getHtml();
				break;
			case 'csv':
				$this->getCsv();
				break;
				
			case 'dot':
				$this->getDot();
				break;
				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	public function getXml(){
		
		if (!is_null($this->xml)){
			return $this->xml;
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$DocumentElement = $this->dom->createElementNS(clsShoc::nsSHOC, 'List');
		$DocumentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', clsShoc::nsXSD);

		$this->dom->appendChild($DocumentElement);
				
		$DocumentElement->setAttribute('page',$this->Page);		
		$DocumentElement->setAttribute('uriClass',$this->Class->Uri);		
		$DocumentElement->setAttribute('idClass',$this->Class->Id);		
		$DocumentElement->setAttribute('classLabel',$this->Class->Label);
		
		if (!is_null($this->Object)){
			$DocumentElement->setAttribute('idObject',$this->Object->Id);		
			$DocumentElement->setAttribute('objectLabel',$this->Object->Label);
		}

		foreach ($this->Subjects->SortedItems as $objSubject){
			
			$xmlListSubject = $this->dom->createElementNS(clsShoc::nsSHOC, 'ListSubject');
			$xmlListSubject->setAttribute('uriSubject', $objSubject->Uri);
			$xmlListSubject->setAttribute('classLabel', $objSubject->Class->Label);
			
			$DocumentElement->appendChild($xmlListSubject);

			$this->xmlSubjectRows($xmlListSubject,$objSubject, $this->Class);
			
		}
		
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('xsd', clsShoc::nsXSD);	
		$this->xpath->registerNamespace('shoc', clsShoc::nsSHOC);
					
		$this->xml = $this->dom->saveXML();
		return $this->xml;
		
	}

	public function xmlSubjectRows($xmlParent, $Subject, $Class){
				
		$this->idColumn = 0;
		$this->xmlColumns($xmlParent, $Subject, $Class->AllProperties);

		// do extending relationships
		
		foreach ($Class->AllRelationships as $objRelationship){
			$useRelationship = false;
			if ($objRelationship->FromClass === $Class){
				if ($objRelationship->Extending){
					$useRelationship = true;
				}
				if ($useRelationship){
					$objLinkClass = $objRelationship->ToClass;
					$RelSubjects = new clsSubjects();
															
					foreach ($Subject->Links as $objLink){
						if ($objLink->Relationship == $objRelationship){
							$objLinkSubject = null;
							if ($objLink->FromSubject == $Subject){
								$objLinkSubject = $objLink->ToSubject;
							};
							if (!is_null($objLinkSubject)){
								$RelSubjects->addItem($objLinkSubject);
							}
						}
					}

					$numRow = 0;
					$idColumn = $this->idColumn;
					
					foreach ($RelSubjects->Items as $RelSubject){
						$numRow++;
						$this->idColumn = $idColumn;						
						$this->xmlColumns($xmlParent, $RelSubject, $objLinkClass->AllProperties, $numRow);
					}					
				}
			}
		}
		
	}

	private function xmlListRow($num, $xmlParent){
		
		$xmlListRow = null;

		$i = 0;
		foreach ($xmlParent->getElementsByTagNameNS ( clsShoc::nsSHOC , 'ListRow' ) as $optXmlListRow){
			$i++;
			if ($i == $num){
				$xmlListRow = $optXmlListRow;
			}		
		}
		if (is_null($xmlListRow)){
			$xmlListRow = $this->dom->createElementNS(clsShoc::nsSHOC, 'ListRow');
			$xmlParent->appendChild($xmlListRow);					
		}

		return $xmlListRow;
		
	}
	
	public function xmlColumns($xmlParent, $Subject, $Properties, $numRow = 1, $uriPartOf = null ){

		global $Models;

		$xmlListRow = $this->xmlListRow($numRow, $xmlParent);
						
		foreach ($Properties as $objProperty){
			if ($objProperty->ShowInLists){
				if (count($objProperty->Parts) == 0){
					
					$this->idColumn++;
					
					$xmlListColumn = $this->dom->createElementNS(clsShoc::nsSHOC, 'ListColumn');
					$xmlListColumn->setAttribute('idColumn',$this->idColumn);
					$xmlListRow->appendChild($xmlListColumn);
					
					foreach ($Subject->objStatements->Items as $objStatement){
						
						if ($objStatement->uriPartOf == $uriPartOf){
							if ($objStatement->uriProperty == $objProperty->Uri){
								
								$xmlListValue = $this->dom->createElementNS(clsShoc::nsSHOC, 'ListValue');
								$xmlListColumn->appendChild($xmlListValue);
								$xmlListValue->nodeValue = $objStatement->ValueLabel;
							}
						}
					}
				}
				
				if (count($objProperty->Parts) > 0){
					foreach ($Subject->objStatements->Items as $objStatement){				
						if ($objStatement->uriPartOf == $uriPartOf){
							if ($objStatement->uriProperty == $objProperty->Uri){
								$this->xmlColumns($xmlParent, $Subject, $objProperty->Parts, $numRow, $objStatement->Uri);
							}
						}
					}
				}
			}
		}		
	}
	
	private function getHtml(){
		
		if (!is_null($this->html)){
			return $this->html;
		}
		
		$this->getXml();
		
		$Content = "";
		$Content .= "<table class='".$this->cssClass."'>";
		
		$Content .= $this->Heading->html;		
		
		foreach ($this->xpath->query("/shoc:List/shoc:ListSubject") as $xmlListSubject){
			$boolDoneSubjectHref = false;
			
			$numRows = $this->xpath->query("shoc:ListRow",$xmlListSubject)->length;

			$numRow = 0;
			foreach ($this->xpath->query("shoc:ListRow",$xmlListSubject) as $xmlListRow){
				
				$numRow++;
				
				$Content .= "<tr>";
				if ($this->ShowClass){
					if ($numRow == 1){
						$RowSpan = $numRows - $numRow + 1;
						$Content .= "<td";
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
						$Content .= ">".$xmlListSubject->getAttribute('classLabel')."</td>";
					}
				}
				
				foreach ($this->xpath->query("shoc:ListColumn",$xmlListRow) as $xmlListColumn){
					
					$RowSpan = 1;

					$idColumn = $xmlListColumn->getAttribute('idColumn');
					$xmlNextRow = $xmlListRow->nextSibling;
					if ($xmlNextRow){
						if ($this->xpath->query("shoc:ListColumn[@idColumn=$idColumn]",$xmlNextRow)->length == 0){
							$RowSpan = $numRows - $numRow + 1;
						}
					}
					
					$Content .= "<td";
					if ($RowSpan > 1){
						$Content .= " rowspan='$RowSpan'";
					}
					$Content .= ">";
					foreach ($this->xpath->query("shoc:ListValue",$xmlListColumn) as $xmlListValue){
						if ($boolDoneSubjectHref){
							$Content .= $xmlListValue->nodeValue;
						}
						else
						{
							$uriSubject = $xmlListSubject->getAttribute('uriSubject');
							$Content .= "<a href='subject.php?";
							if (!is_null($this->System->Session->ParamSid)){
								$Content .= $this->System->Session->ParamSid.'&';
							}
							$Content .= "urisubject=$uriSubject'>".$xmlListValue->nodeValue."</a>";
							$boolDoneSubjectHref = true;							
						}
						$Content .= '<br/>';
					}
					$Content .= "</td>";
					
				}
				$Content .= "</tr>";						
			}
		}
		
		
		$Content .= "</tbody>";	
		$Content .= "</table>";	
		
		$this->html = $Content;
	
		return $this->html;
		
	}
	
	
	private function getCsv(){
		if (!is_null($this->csv)){
			return $this->csv;
		}
		
		$this->getXml();
		
		$Content = "";
/*
		$Content .= "<table class='".$this->cssClass."'>";
		
		$Content .= $this->Heading->html;		
*/		
		foreach ($this->xpath->query("/shoc:List/shoc:ListSubject") as $xmlListSubject){
			
			$numRows = $this->xpath->query("shoc:ListRow",$xmlListSubject)->length;

			$numRow = 0;
			foreach ($this->xpath->query("shoc:ListRow",$xmlListSubject) as $xmlListRow){
				
				$numRow++;
								
				foreach ($this->xpath->query("shoc:ListColumn",$xmlListRow) as $xmlListColumn){
					
					$idColumn = $xmlListColumn->getAttribute('idColumn');
					$xmlNextRow = $xmlListRow->nextSibling;
					if ($xmlNextRow){
						if ($this->xpath->query("shoc:ListColumn[@idColumn=$idColumn]",$xmlNextRow)->length == 0){
							$RowSpan = $numRows - $numRow + 1;
						}
					}
					
					foreach ($this->xpath->query("shoc:ListValue",$xmlListColumn) as $xmlListValue){
						$Content .= $xmlListValue->nodeValue . ",";
					}
					
				}
				$Content .= "\n";						
			}
		}
				
		$this->csv = $Content;	
		return $this->csv;
		
	}
	
	
	
	private function getDot(){
		
		if (!is_null($this->dot)){
			return $this->dot;
		}
		
		$this->getXml();
		
		$Content = "";
		$Content .= "<table border='0' cellborder='1' cellspacing='0'>";
		
		$Content .= $this->Heading->dot;
		
		foreach ($this->xpath->query("/shoc:List/shoc:ListSubject") as $xmlListSubject){
			
			$numRows = $this->xpath->query("shoc:ListRow",$xmlListSubject)->length;

			$numRow = 0;
			foreach ($this->xpath->query("shoc:ListRow",$xmlListSubject) as $xmlListRow){
				
				$numRow++;
				
				$Content .= "<tr>";
				if ($numRow == 1){
					$RowSpan = $numRows - $numRow + 1;
					$Content .= "<td";
					if ($RowSpan > 1){
						$Content .= " rowspan='$RowSpan'";
					}
					$Content .= " align='left' balign='left' valign='top'";
					$Content .= ">".$xmlListSubject->getAttribute('classLabel')."</td>";
				}
				
				foreach ($this->xpath->query("shoc:ListColumn",$xmlListRow) as $xmlListColumn){
					
					$RowSpan = 1;

					$idColumn = $xmlListColumn->getAttribute('idColumn');
					$xmlNextRow = $xmlListRow->nextSibling;
					if ($xmlNextRow){
						if ($this->xpath->query("shoc:ListColumn[@idColumn=$idColumn]",$xmlNextRow)->length == 0){
							$RowSpan = $numRows - $numRow + 1;
						}
					}
					
					$Content .= "<td";
					if ($RowSpan > 1){
						$Content .= " rowspan='$RowSpan'";
					}
					
					
					$Content .= " align='left' balign='left' valign='top'";
					
					$Content .= ">";
					foreach ($this->xpath->query("shoc:ListValue",$xmlListColumn) as $xmlListValue){
						$Content .= truncate($xmlListValue->nodeValue,100);
						$Content .= '<br/>';
					}
					$Content .= "</td>";
					
				}
				$Content .= "</tr>";						
			}
		}
				
		$Content .= "</table>";	
		
		$this->dot = $Content;
	
		return $this->dot;
		
	}
	
	
}
