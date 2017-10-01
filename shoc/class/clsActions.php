<?php

require_once("class/clsSystem.php");
require_once("class/clsGraph.php");
require_once("class/clsSparql.php");

require_once("class/clsShocData.php");


require_once("function/utils.inc");


class clsActions {
  
	public $NumberOfActions = 0;
	public $uriActivityInvitations = array();
	
	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		if (!$System->LoggedOn){
			return;
		}

		
		$objSparql = new clsSparql();
		
		$objSparql->Prefixes['rdf'] = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>";
		$objSparql->Prefixes['rdfs'] = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>";			
		$objSparql->Prefixes['dct'] = "PREFIX dct: <http://purl.org/dc/terms/>";
		$objSparql->Prefixes['xsd'] = "PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>";
				
		$objSparql->Prefixes['shoc'] = "PREFIX shoc: <".clsSHOC::prefixSHOC.">";
		
		$Query = "SELECT ?uri WHERE {
			?uri a shoc:Activity .
			?uri shoc:member ?member .
			?member shoc:user ".chr(34).$System->User->Id.chr(34)." .
			?member shoc:status ".chr(34).'2'.chr(34)." .
		}";
			
				
		$xmlResults = $objSparql->Query($Query);
		
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
		
				
		foreach ($this->xpath->query("/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name='uri']/sparql:uri") as $xmlUri){
			++$this->NumberOfActions;
			$uriActivity = $xmlUri->nodeValue;
			$this->uriActivityInvitations[] = $uriActivity;
		}
		
		
		
/*		
		$UserId = $System->User->Id;
		$sql = "SELECT * from tbl_usergroup WHERE usrgrpUser = $UserId";
		
		$rst = $System->dbExecute($sql);
				
		while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
			$UserGroupId = $row['usrgrpRecnum'];			
			switch ($row['usrgrpStatus']){
				case 1:
					++$this->NumberOfActions;					
					$this->RequestIds[$UserGroupId] = $UserGroupId;
					break;
				case 2:
					++$this->NumberOfActions;
					$this->InvitationIds[$UserGroupId] = $UserGroupId;
					break;
			}
			
			
		}
*/
		
		
		
		
	}
}
