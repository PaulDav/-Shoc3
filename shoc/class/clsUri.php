<?php
require_once('class/clsSystem.php');
require_once('class/clsShocData.php');
require_once('function/utils.inc');

class clsUri{

	private $System = null;
	private $Shoc = null;
	
	private $Subject = null;
	
	public $Uri = null;
	
	public $Doc = null;
	public $dom = null;
	
	private $BaseUri = null;

	const nsRDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	const nsRDFS = 'http://www.w3.org/2000/01/rdf-schema#';
	
	private $arrNamespaces = array();
	
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
		
		$this->BaseUri = "http://data.shocdata.com/";
		if (isset($this->System->Config->Vars['instance']['baseuri'])){
			$this->BaseUri = $this->System->Config->Vars['instance']['baseuri'];
		}
		elseif (isset($this->System->Config->Vars['instance']['host'])){
			$this->BaseUri = $this->System->Config->Vars['instance']['host'];
		}
		if (substr($this->BaseUri,-1) != '/'){
			$this->BaseUri .= '/';
		}
	}
	
	public function forUri($Uri){

// get the subject URI from the PublishedUri

		$this->Uri = $Uri;
		
		$SubjectUri = $Uri;
		if (substr($Uri,0,strlen($this->BaseUri)) == $this->BaseUri){
			$SubjectUri = "http://data.shocdata.com/id/subject/";
			$UriParts = explode('/',$Uri);
			$SubjectUri .= end($UriParts);
		}
		$this->Subject = $this->Shoc->getSubject($SubjectUri);
	}

	public function forSubject($Subject){

		$this->Uri = $this->BaseUri;
		
		$this->Uri .= "id/";
		
		$this->Uri .= strtolower($Subject->Class->Name).'/';
		
		$SubjectUriParts = explode('/',$Subject->Uri);
		$this->Uri .= end($SubjectUriParts);
		
		$this->Subject = $Subject;		
	}
	
	
/*	
	private function Build($Uri){
		$this->Subject = $this->Shoc->getSubject($Uri);				
	}
*/	
	public function Dereference($Format){

		switch ($Format){
			case 'ttl':
				return $this->DereferenceTtl();				
				break;			
			case 'rdf/xml':
			default:
				return $this->DereferenceRdfXml();
				break;
		}

	}
		
	public function DereferenceRdfXml(){
	
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$DocumentElement = $this->dom->createElementNS(self::nsRDF, 'rdf:RDF');
		$DocumentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:rdf', self::nsRDF);
		$this->dom->appendChild($DocumentElement);
				
		$this->DereferenceSubject($DocumentElement);

		foreach ($this->arrNamespaces as $posNS=>$Namespace){
			$DocumentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ns'.$posNS, $Namespace);
		}
		
		$this->dom->formatOutput = true;
//		$this->Doc = $this->dom->saveXML();
		return $this->dom->saveXML();
		
	}
	
	private function addNamespace($Namespace){
		if (!in_array($Namespace, $this->arrNamespaces)){
			$this->arrNamespaces[] = $Namespace;
		}
		return;
		
	}

	
	private function DereferenceSubject($xmlParent,$AboutId= null ){
				
		$xmlSubject = $this->dom->createElementNS(self::nsRDF, 'rdf:Description');
		
		if (is_null($AboutId)){
			
			$objUriString = new clsUriString($this->Subject->uriClass);
			if (!is_null($objUriString->Namespace)){
				$this->addNamespace($objUriString->Namespace);
				$xmlSubject = $this->dom->createElementNS($objUriString->Namespace, $objUriString->LocalName);
			}
			else
			{
				$xmlType = $this->dom->createElementNS(self::nsRDF, 'rdf:type');
				$xmlType->setAttribute('rdf:resource',$this->Subject->uriClass);
				$xmlSubject->appendChild($xmlType);
			}
//			$xmlSubject->setAttribute('rdf:about',$this->Subject->Uri);
			$xmlSubject->setAttribute('rdf:about',$this->Uri);
		}
		$xmlParent->appendChild($xmlSubject);
		
		foreach ($this->Subject->Attributes as $objAttribute){
			$objUriString = new clsUriString($objAttribute->Property->Uri);
			if (!is_null($objUriString->Namespace)){
				$this->addNamespace($objUriString->Namespace);
				$xmlProperty = $this->dom->createElementNS($objUriString->Namespace, $objUriString->LocalName);
				
				switch ($objAttribute->Property->DataType->Uri){			
					case 'http://www.w3.org/2001/XMLSchema#anyURI':
						$xmlProperty->setAttribute('rdf:resource',$objAttribute->Statement->ValueLabel);
						break;
					default:
						$xmlProperty->nodeValue = $objAttribute->Statement->ValueLabel;
						break;
				}
				$xmlSubject->appendChild($xmlProperty);					
			}
		}
				
	}
	
	
	
	public function DereferenceTtl(){
	
		$this->Doc = '';
						
		$this->DereferenceSubjectTtl();
		
		return $this->Doc;
		
	}
	
	
	private function DereferenceSubjectTtl($AboutId= null ){

		$this->Doc .= '<'.$this->Uri.'>'."\n";
		$this->Doc .= "\t".'a'."\t".'<'.$this->Subject->uriClass.'>'.';'."\n";
		

		foreach ($this->Subject->Attributes as $objAttribute){
			$objUriString = new clsUriString($objAttribute->Property->Uri);
			$this->Doc .= "\t".'<'.$objAttribute->Property->Uri.'>'."\t";
			

			switch ($objAttribute->Property->DataType->Uri){			
				case 'http://www.w3.org/2001/XMLSchema#anyURI':
					$this->Doc .= '<'.$objAttribute->Statement->Value.'>';					
					break;
				default:
					$this->Doc .= chr(34).chr(34).chr(34).$objAttribute->Statement->ValueLabel.chr(34).chr(34).chr(34);
					break;
			}
					
			switch ($objAttribute->Property->DataType->Uri){
				case 'http://www.w3.org/2001/XMLSchema#anyURI':
				case "http://www.w3.org/2001/XMLSchema#string":
					break;
				default:
					$this->Doc .= '^^'.$objAttribute->Property->DataType->Uri;
					break;
			}
			$this->Doc .= ';'."\n";
		}
		$this->Doc .= "\t.\n\n";
		
	}
	
}

class xclsUriSubject{

	public $Recnum = null;
	public $SubjectId = null;
	public $Class = null;
	public $Properties = array();
	public $Stub = '';

	private $System = null;

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;
		$this->Stub = $this->System->Config->Vars['instance']['datauristub'];

	}

	public function getForUri($Uri){
		
		$UriParts = explode($this->Stub, $Uri);
		
		if (count($UriParts) < 2){
			throw new exception('No Stub');
		}
		$UriId = $UriParts[1];
		
		$sql = "SELECT * FROM tbl_subject WHERE subId = $UriId";
		$rst = $this->System->DbExecute($sql);
		if (!($row = mysqli_fetch_array($rst, MYSQLI_ASSOC))) {
			throw new exception('No Subject');
		}
		
		$this->Recnum = $row['subRecnum'];
		$this->SubjectId = $row['subId'];
		$this->Uri = $this->Stub.$this->SubjectId;

		$this->Class = $row['subClass'];
		
		$sql = "SELECT * FROM tbl_statement INNER JOIN qrylateststatementrevision ON starevStatement = staRecnum LEFT JOIN tbl_value ON valStatementRevision = starevRecnum WHERE staSubject = ".$this->Recnum.";";
		$rst = $this->System->DbExecute($sql);
		while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
			$objProperty = new clsUriProperty();
			$objProperty->Subject = $this;
			$objProperty->AboutId = $row['staAbout'];
			$objProperty->StatementId = $row['staId'];
			$objProperty->Predicate = $row['staProperty'];
			$objProperty->Value = $row['valText'];

			$DataType = null;
			if (isset($row['valDataType'])){
				$DataType = $row['valDataType'];
			}
			if (!is_null($DataType)){
				if (isset($this->System->Config->DataTypes[$DataType])){
					$objProperty->DataType = $this->System->Config->DataTypes[$DataType];
				}
			}
						
			$this->Properties[$objProperty->AboutId][] = $objProperty;
		}
		
		return;
	}

}

class xclsUriProperty{
	public $Subject = null;
	public $StatementId = null;
	public $AboutId = null;
	public $Predicate = null;
	public $Value = null;
	public $DataType = null;

	private $PredicateName = null;
	private $PredicateNS = null;

	public function __get($Name){
		switch ($Name){
			case 'PredicateName':
			case 'PredicateNS':
				$this->getPredicateParts();
				break;
		}
		if (property_exists($this, $Name)){
			return $this->$Name;
		}
	}
	
	private function getPredicateParts(){
	
		$objUriString = new clsUriString($this->Predicate);
		
		$this->PredicateName = $objUriString->LocalName;
		$this->PredicateNS = $objUriString->Namespace;
	}
		
}
	






class clsUriString{
	
	public $UriString = null;
	public $LocalName = null;
	public $Namespace = null;
	
	public function __construct($UriString){

		$this->UriString = $UriString;
		
		if (!is_null($this->UriString)){
			if (strpos($this->UriString,'#')){
				$this->LocalName = substr($this->UriString, strpos($this->UriString,'#')+1);
				$this->Namespace = substr($this->UriString, 0, strpos($this->UriString,'#')+1);
			}
			else
			{
				$arrParts = explode('/',$this->UriString);
				$this->LocalName = array_pop($arrParts);
				$this->Namespace = implode('/',$arrParts).'/';
			}
		}
	}
}	



?>