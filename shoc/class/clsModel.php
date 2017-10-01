<?php

require_once("class/clsSystem.php");
require_once("class/clsGraph.php");


require_once("function/utils.inc");


class clsModels {
	
	const nsSHOC = "http:/data.shocdata.com/schema/";
	
	public $Items = array();

	public $Classes = array();
	public $uriClasses = array();
	
	
	public $Properties = array();
	public $uriProperties = array();
	
	public $DataTypes = array();
	public $Relationships = array();
	private $uriRelationships = null;
	public $Lists = array();
	public $Terms = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "data";
	private $filename = "DataModels.xml";
	private $path = null;

	private $domDefaults = null;
	private $xpathDefaults = null;	
	
	public $Namespace = "http://www.istanduk.org/schemas/DataModeller";
	
	private $System = null;

	
	public function __get($name){
		switch ($name){
			case 'uriRelationships':
				$this->getUriRelationships();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;

		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->path = $System->path."/".$this->folder."//".$this->filename;
		
		if (@$this->dom->load($this->path) === false){
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->Namespace, 'DataModeller');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->Refresh();
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('mod', $this->Namespace);
		
	}

	public function refresh(){

		foreach ($this->xpath->query("/mod:DataModeller/mod:DataTypes/mod:DataType") as $xmlDataType){
			$objDataType = new clsDataType($this, $xmlDataType);
			$this->DataTypes[$objDataType->Id] = $objDataType;
		}
		
		
		foreach ($this->xpath->query("/mod:DataModeller/mod:Models/mod:Model") as $xmlModel){
			
			$objModel = new clsModel;
			
			$objModel->canView = $this->canView;
			$objModel->canEdit = $this->canEdit;
			$objModel->canControl = $this->canControl;
						
			$objModel->objModels = $this;
			
			$objModel->xpath = $this->xpath;
			
			$objModel->xml = $xmlModel;
			$objModel->Id = $xmlModel->getAttribute("id");
			
			$objModel->Name = xmlelementvalue($xmlModel, 'Name');
			$objModel->Version = xmlelementvalue($xmlModel, 'Version');
			$objModel->Definition = xmlelementvalue($xmlModel, 'Definition');
			$objModel->BaseUri = xmlelementvalue($xmlModel, 'BaseUri');

			
			foreach ($this->xpath->query("mod:Packages/mod:Package", $xmlModel) as $xmlPackage){				
				$objPackage = new clsPackage($objModel, $xmlPackage);
				$objModel->Packages[$objPackage->Id] = $objPackage;
			}
			
			
			foreach ($this->xpath->query("mod:Classes/mod:Class", $xmlModel) as $xmlClass){				
				$objClass = new clsClass($objModel, $xmlClass);
				$objModel->Classes[$objClass->Id] = $objClass;				
			}

			
			foreach ($this->xpath->query("mod:Relationships/mod:Relationship", $xmlModel) as $xmlRel){				
				$objRel = new clsRelationship($objModel, $xmlRel);
				$objModel->Relationships[$objRel->Id] = $objRel;				
			}

			
			foreach ($this->xpath->query("mod:Lists/mod:List", $xmlModel) as $xmlList){				
				$objList = new clsList($objModel, $xmlList);
				$objModel->Lists[$objList->Id] = $objList;
			}
			
			
			$this->Items[$objModel->Id] = $objModel;
			
		}
	}
	
	public function getItem($Id){
		if (isset($this->Items[$Id])){
			return $this->Items[$Id];			
		}
		return false;
	}
	
	public function getUriRelationships(){
		
		if (!is_null($this->uriRelationships)){
			return $this->uriRelationhips;
		}
		
		$this->uriRelationships = array();
		
		foreach ($this->Relationships as $objRelationship){
			$this->uriRelationships[$objRelationship->Uri] = $objRelationship;
		}
		
		return $this->uriRelationships;
	}
	
}

class clsModel {
		
	public $xml = null;
	public $Id = null;
	public $Name = null;
	public $Version = null;
	Public $Definition = null;
	Public $BaseUri = null;

	public $objModels = null;
	public $Packages = array();	
	public $Classes = array();
	public $Relationships = array();
	public $Lists = array();

	private $Archetypes = null;

	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	public $xpath = null;
	
	private $dot = null;
	
	public function __get($name){
		switch ($name){
			case 'dot':
				$this->dot = $this->getDot();
				break;
			case 'Archetypes':
				$this->getArchetypes();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	
	public function getArchetypes(){
		
		if (!is_null($this->Archetypes)){
			return $this->Archetypes;
		}

		$this->Archetypes = array();

		foreach ($this->Classes as $objClass){
			foreach ($this->xpath->query("/mod:DataModeller/mod:Archetypes/mod:Archetype[mod:Objects/mod:Object/@classId=".$objClass->Id."]") as $xmlArchetype){
				$objArchetype = new clsArchetype(null,$xmlArchetype, $this->xpath);
				$this->Archetypes[$objArchetype->Id] = $objArchetype;				
			}
		}
		
		return $this->Archetypes;
		
	}
	
	public function getDot($objDot = null, $objGraph = null){

		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsModelDot();
			$Top = true;
		}
		if (is_null($objGraph)){
			$objGraph = new clsGraph();
			$objGraph->FlowDirection='LR';
		}
				
		foreach ($this->Packages as $objPackage){
			$objPackage->getDot($objDot, $objGraph);
		}
		

		foreach ($this->Classes as $objClass){
			$objClass->getDot($objDot, $objGraph);
		}
		

		if ($Top){
			$this->getDotRelationships($objDot, $objGraph);
			$this->getDotSuperClasses($objDot, $objGraph);
		}

		return $objGraph->script;

	}
	
	public function getDotRelationships($objDot, $objGraph){
		
		$arrStartClassIds = $objDot->ClassIds;
		
		foreach ($this->Relationships as $objRel){
			
			if (isset($arrStartClassIds[$objRel->FromClassId]) || isset($arrStartClassIds[$objRel->ToClassId] )){
				if (!isset($objDot->ClassIds[$objRel->FromClassId])){
					if (isset($this->Classes[$objRel->FromClassId])){
						$objFromClass = $this->Classes[$objRel->FromClassId];
						$objFromClass->getDot($objDot, $objGraph);						
					}
				}
				
				if (!isset($objDot->ClassIds[$objRel->ToClassId])){
					if (isset($this->Classes[$objRel->ToClassId])){
						$objToClass = $this->Classes[$objRel->ToClassId];
						$objToClass->getDot($objDot, $objGraph);
					}
				}

				
			}
			
			
		}

		
		foreach ($this->Relationships as $objRel){
			if (isset($objDot->RelIds[$objRel->Id])){
				continue;
			}
			

			if (isset($objDot->ClassIds[$objRel->FromClassId]) && isset($objDot->ClassIds[$objRel->ToClassId])){
				
				$ArrowHead = null;
				$ArrowTail = null;
				if ($objRel->Extending){
					$ArrowTail = 'diamond';
					$ArrowHead = "none";
				}
				
				$objGraph->addEdge('class_'.$objRel->FromClassId, 'class_'.$objRel->ToClassId, $objRel->Label,null,null,$ArrowHead, $ArrowTail);
				$objDot->RelIds[$objRel->Id] = $objRel->Id;
			}
			
		}
		
		
	}
	

	
	public function getDotSuperClasses($objDot, $objGraph){

		foreach ($objDot->ClassIds as $ClassId){
			if (isset($this->Classes[$ClassId])){
				$objClass = $this->Classes[$ClassId];
				foreach ($objClass->SuperClasses as $objSuperClass){
					if (isset($objDot->ClassIds[$objSuperClass->Id])){
						$objGraph->addEdge('class_'.$objClass->Id, 'class_'.$objSuperClass->Id, 'sub class of',null,'dotted');
					}
				}
			}			
		}
		
		
	}
	
	
}


class clsPackage {

	public $xml = null;

	public $Model = null;
	public $Id = null;
	public $Name = null;
	public $Label = null;	
	public $Version = null;
	Public $Definition = null;
	
	private $Classes = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
	
	private $dot = null;	
	
	public function __construct($Model = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Model)){
			$this->Model = $Model;
			$this->xpath = $this->Model->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
//			foreach ($this->xpath->query("/mod:DataModeller/mod:Models/mod:Model") as $xmlModel){
			
			$this->Id = $this->xml->getAttribute("id");
			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Label = xmlelementvalue($this->xml, 'Label');
			$this->Definition = xmlelementvalue($this->xml, 'Definition');
			
		}
		
	}
	
	public function __get($name){
		
		switch ($name){
			case 'Classes':
				$this->getClasses();
				break;
			case 'dot':
				$this->dot = $this->getDot();
				break;				
			default:
				return null;
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	private function getClasses(){
		
		if (!is_null($this->Classes)){
			return $this->Classes;
		}

		$this->Classes = array();

		foreach ($this->Model->Classes as $objClass){
			if ($objClass->PackageId == $this->Id){
				$this->Classes[$objClass->Id] = $objClass;
			}
			
		}
		
		
	}
	
	public function getDot($objDot = null, $objGraph = null){

		$this->getClasses();
		
		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsModelDot();
			$Top = true;			
		}
		
		if (is_null($objGraph)){
			$objGraph = new clsGraph();
		}
		
		$objPackageGraph = $objGraph->addSubGraph('cluster',$this->Label);
				
		foreach ($this->Classes as $objClass){
			$objClass->getDot($objDot, $objPackageGraph);
		}
		
		if ($Top){
			$this->Model->getDotRelationships($objDot, $objGraph);
		}
		
		
		$this->dot = $objGraph->script;
		return $this->dot;
	}
	
	
}

class clsClass {

	public $xml = null;

	public $Model = null;
	public $Id = null;
	public $PackageId = null;
	
	private $Uri = null;
	
	public $Name = null;
	public $Label = null;	
	public $Heading = null;
	public $Version = null;
	Public $Definition = null;
	public $Color = null;
	public $FontColor =  null;
	
	public $Properties = array();
	
	private $AllProperties = null;
	private $AllRelationships = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
	
	private $dot = null;
	
	private $SuperClasses = null;
	private $EquivalentClasses = null;
	private $InheritedProperties = null;
	
	private $dom = null;
	
	public function __construct($Model = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Model)){
			$this->Model = $Model;
			$this->xpath = $this->Model->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");
			if (!($this->xml->getAttribute("packageId") == "")){
				$this->PackageId = $this->xml->getAttribute("packageId");
			}
			
			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Label = xmlelementvalue($this->xml, 'Label');
			$this->Heading = xmlelementvalue($this->xml, 'Heading');
			if (empty($this->Heading)){
				$this->Heading = $this->Label;
			}
			$this->Definition = xmlelementvalue($this->xml, 'Definition');
			

			if (!(xmlelementvalue($this->xml, 'Color') == '')){
				$this->Color = xmlelementvalue($this->xml, 'Color');
			}
			
			if (!(xmlelementvalue($this->xml, 'FontColor') == '')){
				$this->FontColor = xmlelementvalue($this->xml, 'FontColor');
			}
			
			
		}
		$Model->objModels->Classes[$this->Id] = $this;
		
		$this->getUri();		
		$Model->objModels->uriClasses[$this->Uri] = $this;
		
		foreach ($this->xpath->query("mod:Properties/mod:Property",$this->xml) as $xmlProperty){
			$objProperty = new clsProperty($this, $xmlProperty);
			$this->Properties[$objProperty->Id] = $objProperty;
		}
		
		
	}
	
	
	public function __get($name){
		
		switch ($name){
			case 'dot':
				$this->dot = $this->getDot();
				break;
			case 'SuperClasses':
				$this->getSuperClasses();
				break;				
			case 'EquivalentClasses':
				$this->getEquivalentClasses();
				break;				
			case 'InheritedProperties':
				$this->getInheritedProperties();
				break;
			case 'AllProperties':
				$this->getAllProperties();
				break;				
			case 'AllRelationships':
				$this->getAllRelationships();
				break;				
			case 'Uri':
				$this->getUri();
				break;
			case 'dom':
				$this->getDom();
				break;
			default:
				return null;
				break;
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	private function getDom(){
		
		if (!is_null($this->dom)){
			return;
		}

		$this->getUri();
		$this->getAllProperties();
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;

		$DocumentElement = $this->dom->createElementNS(clsModels::nsSHOC, 'Class');
		
		$DocumentElement->setAttribute('id', $this->Id);
		$DocumentElement->setAttribute('uri', $this->Uri);
		

		$xmlName = $this->dom->createElementNS(clsModels::nsSHOC, 'Name');
		$xmlName->nodeValue = $this->Name;
		$DocumentElement->appendChild($xmlName);

		if (!is_null($this->Label)){		
			$xmlLabel = $this->dom->createElementNS(clsModels::nsSHOC, 'Label');
			$xmlLabel->nodeValue = $this->Label;
			$DocumentElement->appendChild($xmlLabel);
		}

		if (!is_null($this->Definition)){
			$xmlDefinition = $this->dom->createElementNS(clsModels::nsSHOC, 'Definition');
			$xmlDefinition->nodeValue = $this->Definition;
			$DocumentElement->appendChild($xmlDefinition);
		}


		if (count($this->AllProperties > 0)){
			$xmlProperties = $this->dom->createElementNS(clsModels::nsSHOC, 'Properties');
			$DocumentElement->appendChild($xmlProperties);
			foreach ($this->AllProperties as $objProperty){
				$xmlProperties->appendChild($this->dom->importNode($objProperty->dom->documentElement,true));
			}
		}
		
		$this->dom->appendChild($DocumentElement);
		
		return;
		
	}
	
	
	public function getUri(){
		
		if (!is_null($this->Uri)){
			return $this->Uri;
		}
		
		$this->Uri = $this->Model->BaseUri . $this->Name;
		return $this->Uri;
		
	}
	

	public function getSuperClasses(&$arrClasses = array()){
		
		if (!is_null($this->SuperClasses)){
			return $this->SuperClasses;
		}
		
		$this->getUri();
		
		if (isset($arrClasses[$this->Uri])){
			return array();
		}
		
		$this->SuperClasses = array();

		foreach ($this->xpath->query("mod:SuperClasses/mod:SuperClass",$this->xml) as $xmlSuperClass){
			$SuperClassId = $xmlSuperClass->getAttribute("classId");
			if (isset($this->Model->objModels->Classes[$SuperClassId])){
				$SuperClass = $this->Model->objModels->Classes[$SuperClassId];
				$this->SuperClasses[$SuperClassId] = $SuperClass;
				
				$this->SuperClasses = $SuperClass->getSuperClasses($arrClasses) + $this->SuperClasses;
				
			}
		}		
		
		return $this->SuperClasses;
		
	}
	
	
	public function getEquivalentClasses(&$arrClasses = array()){
		
		if (!is_null($this->EquivalentClasses)){
			return $this->EquivalentClasses;
		}
		
		$this->getUri();
		
		if (isset($arrClasses[$this->Uri])){
			return array();
		}
		
		$this->EquivalentClasses = array();

		foreach ($this->xpath->query("mod:EquivalentClasses/mod:EquivalentClass",$this->xml) as $xmlEquivClass){
			$EquivClassId = $xmlEquivClass->getAttribute("classId");
			if (isset($this->Model->objModels->Classes[$EquivClassId])){
				$EquivClass = $this->Model->objModels->Classes[$EquivClassId];
				$this->EquivalentClasses[$EquivClassId] = $EquivClass;
				
				$this->EquivalentClasses =  $this->EquivalentClasses + $EquivClass->getEquivalentClasses($arrClasses);
				
			}
		}		
		
		return $this->EquivalentClasses;
		
	}
	
	public function getInheritedProperties(&$arrClasses = array()){
		
		$arrClasses[$this->Id] = $this;
		
		if (!is_null($this->InheritedProperties)){
			return $this->InheritedProperties;
		}
		
		$this->InheritedProperties = array();

		$this->getSuperClasses();
		
		foreach ($this->SuperClasses as $objSuperClass){
			if (isset($arrClasses[$objSuperClass->Id])){
				continue;
			}
			$arrClasses[$objSuperClass->Id] = $objSuperClass;			
			
			foreach ($objSuperClass->Properties as $objProperty){
				$this->InheritedProperties[$objProperty->Id] = $objProperty;
			}
			$this->InheritedProperties = $this->InheritedProperties + $objSuperClass->getInheritedProperties();			
		}
		
		return $this->InheritedProperties;
		
	}
	
	
	public function getAllProperties(){

		if (!is_null($this->AllProperties)){
			return $this->AllProperties;
		}

		$this->getUri();
		
		$this->AllProperties = array();

		$AllClasses = $this->getSuperClasses();
		$AllClasses[$this->Uri] = $this;
		
		foreach ($AllClasses as $objClass){
			$arrProperties = array();			
			foreach ($this->AllProperties as $objAllProperty){
				$boolAddAllProperty = true;
				foreach ($objClass->Properties as $objProperty){
					if (!isset($this->AllProperties[$objProperty->Uri])){
						foreach ($objProperty->SuperProperties as $objSuperProperty){
							if ($objAllProperty->Uri == $objSuperProperty->Uri){
								$boolAddAllProperty = false;
								$arrProperties[$objProperty->Uri] = $objProperty;
								break;
							}
						}
					}
				}
				if ($boolAddAllProperty){
					$arrProperties[$objAllProperty->Uri] = $objAllProperty;
				}
			}

			$this->AllProperties = $arrProperties;

			foreach ($objClass->Properties as $objProperty){
				if (!isset($this->AllProperties[$objProperty->Uri])){
					$this->AllProperties[$objProperty->Uri] = $objProperty;
				}
			}
			
		}
		
		return $this->AllProperties;
		
	}
	
	
	public function getPropertiesForSuperProperty($uriSuperProperty){

		$arrProperties = array();
		
		$this->getAllProperties();

		foreach ($this->AllProperties as $objProperty){
			if ($objProperty->Uri == $uriSuperProperty){
				$arrProperties[$objProperty->Id] = $objProperty;
			}
			else
			{
				foreach ($objProperty->SuperProperties as $objSuperProperty){
					if ($objSuperProperty->Uri == $uriSuperProperty){
						$arrProperties[$objProperty->Id] = $objProperty;
					}					
				}
			}
		}
		
		return $arrProperties;
		
	} 
	
	public function getAllRelationships(){

		if (!is_null($this->AllRelationships)){
			return $this->AllRelationships;
		}

		$this->getUri();
		
		$this->AllRelationships = array();

		$AllClasses = $this->getSuperClasses();
		$AllClasses[$this->Uri] = $this;
		foreach ($AllClasses as $objClass){
			foreach ($this->Model->objModels->Relationships as $objRelationship){
				if (!isset($this->AllRelationships[$objRelationship->Uri])){
					if (is_object($objRelationship->FromClass)){
						if ($objRelationship->FromClass->Id == $objClass->Id){
							$this->AllRelationships[$objRelationship->Uri] = $objRelationship;						
						}
					}
					if (is_object($objRelationship->ToClass)){					
						if ($objRelationship->ToClass->Id == $objClass->Id){
							$this->AllRelationships[$objRelationship->Uri] = $objRelationship;						
						}					
					}
				}
			}
			
		}
		return $this->AllRelationships;
	}
	
	
	public function getDot($objDot = null, $objGraph = null, $Style=null){

		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsModelDot();
			$objDot->Style = 2;
			$Top = true;
		}
		if (is_null($Style)){
			$Style = $objDot->Style;
		}

		if (isset($objDot->ClassIds[$this->Id])){
			return;
		}
		
		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
				
		$NodeId = 'class_'.$this->Id;

		$this->getAllProperties();
		$this->getAllRelationships();		
		
		$dotLabel = $this->Label;
		$dotShape = null;
		if ($Style == 2){
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			$dotLabel .= "<tr><td colspan='2' bgcolor='lightblue'>".$this->Label."</td></tr>"; 
			
			foreach ($this->AllProperties as $objProperty){
				$portId = 'property_'.$objProperty->Id;
				$dotLabel .= "<tr><td align='left' balign='left' valign='top' port='$portId'>".$objProperty->Name."</td></tr>";				
				
				foreach ($objProperty->Lists as $objList){
					$objList->getDot($objDot, $objGraph);
					$EdgeLabel = '';
					$objGraph->addEdge($NodeId.':'.$portId, 'list_'.$objList->Id, $EdgeLabel,  null, 'dotted');
				}


				if (count($objProperty->Parts) > 0){
					$NodeParts = $this->getDotParts($objProperty, $objDot, $objGraph);
					$objGraph->addEdge($NodeId.':'.$portId, $NodeParts);
				}
				
			}
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';
		}

		$objGraph->addNode('class_'.$this->Id, $dotLabel, $dotShape, $this->Color,null, null, 'class.php?modelid='.$this->Model->Id.'&classid='.$this->Id, null, null);
		$objDot->ClassIds[$this->Id] = $this->Id;		

		if ($Top){
			foreach ($this->AllRelationships as $objRelationship){
				
				if ($objRelationship->Model->Id == $this->Model->Id){
				
				
					if (!isset($objDot->RelIds[$objRelationship->Id])){
						
						$FromNodeId = "class_".$objRelationship->FromClass->Id;
						// check if this is a super class and add as a concept
						$RelNodeStyle = $Style;
						if (isset($this->SuperClasses[$objRelationship->FromClass->Id])){
							$RelNodeStyle = 1;
							if (!isset($objDot->SuperClassIds[$this->Id][$objRelationship->FromClass->Id])){
								$objGraph->addEdge($NodeId, $FromNodeId, null, null, "dotted");
								$objDot->SuperClassIds[$this->Id][$objRelationship->FromClass->Id] = true;
							}
						}
						$objRelationship->FromClass->getDot($objDot, $objGraph, $RelNodeStyle);
	
						$ToNodeId = "class_".$objRelationship->ToClass->Id;
						// check if this is a super class and add as a concept
						$RelNodeStyle = $Style;
						if (isset($this->SuperClasses[$objRelationship->ToClass->Id])){
							$RelNodeStyle = 1;
							if (!isset($objDot->SuperClassIds[$this->Id][$objRelationship->ToClass->Id])){
								$objGraph->addEdge($NodeId, $ToNodeId, null, null, "dotted");
								$objDot->SuperClassIds[$this->Id][$objRelationship->ToClass->Id] = true;
							}						
						}
						$objRelationship->ToClass->getDot($objDot, $objGraph, $RelNodeStyle);
						
						
						$objGraph->addEdge($FromNodeId, $ToNodeId, $objRelationship->Label);
						$objDot->RelIds[$objRelationship->Id] = $objRelationship->Id;
					}
				}
			}


		
		
//			$this->Model->getDotRelationships($objDot, $objGraph);
		}
		
		$this->dot = $objGraph->script;
		return $this->dot;		
		
	}

	
	public function getDotParts($objProperty, $objDot = null, $objGraph = null){

		$Top = false;
		if (is_null($objDot)){
			$objDot = new clsModelDot();
			$objDot->Style = 2;
			$Top = true;
		}

		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
				
		$NodeId = 'parts_'.$objProperty->Id;
		
		$dotLabel = $objProperty->Label;
		$dotShape = null;
		if ($objDot->Style == 2){
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			
			foreach ($objProperty->Parts as $objPropertyPart){
				$portId = 'property_'.$objPropertyPart->Id;
				$dotLabel .= "<tr><td align='left' balign='left' valign='top' port='$portId'>".$objPropertyPart->Name."</td></tr>";
				
				foreach ($objPropertyPart->Lists as $objList){
					$objList->getDot($objDot, $objGraph);
					$EdgeLabel = 'permitted values';
					$objGraph->addEdge($NodeId.':'.$portId, 'list_'.$objList->Id, $EdgeLabel,  null, 'dotted');
				}


				if (count($objPropertyPart->Parts) > 0){
					$NodeParts = $this->getDotParts($objPropertyPart, $objDot, $objGraph);
					$objGraph->addEdge($NodeId.':'.$portId, $NodeParts, null,  null, 'dotted');
				}
				
			}
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';
		}

		$objGraph->addNode($NodeId, $dotLabel, $dotShape, $this->Color,null, null, 'property.php?propertyid='.$objProperty->Id, null, null);
				
		$this->dot = $objGraph->script;
		return $NodeId;
		
	}
	
}


class clsRelationship {

	public $xml = null;

	public $Model = null;
	public $Id = null;
	
	public $Name = null;
	public $Label = null;
	public $InverseLabel = null;
	
	public $Version = null;
	Public $Definition = null;
	public $Color = null;
	
	public $ShowInLists = false;
	public $InverseShowInLists = false;
	
	
	public $Extending = false;
	
	private $Uri = null;
	
	public $FromClassId = null;
	public $ToClassId = null;
	
	private $FromClass = null;
	private $ToClass = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
	
	public function __construct($Model = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Model)){
			$this->Model = $Model;
			$this->xpath = $this->Model->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");
			
			if ($this->xml->getAttribute("extending") == "true"){
				$this->Extending = true;
			}			
			
			
			
			if ($this->xml->getAttribute("showInLists") == 'true'){
				$this->ShowInLists = true;
			}
			if ($this->xml->getAttribute("inverseShowInLists") == 'true'){
				$this->InverseShowInLists = true;
			}
			
			
			
			if ($xmlFromClass = $this->xpath->query("mod:FromClass", $this->xml)->item(0)){
				$this->FromClassId = $xmlFromClass->getAttribute("classId");				
			}
			if ($xmlToClass = $this->xpath->query("mod:ToClass", $this->xml)->item(0)){
				$this->ToClassId = $xmlToClass->getAttribute("classId");				
			}
			

			if (!(xmlelementvalue($this->xml, 'Name') == '')){
				$this->Name = xmlelementvalue($this->xml, 'Name');
			}

			if (!(xmlelementvalue($this->xml, 'Version') == '')){			
				$this->Version = xmlelementvalue($this->xml, 'Version');
			}

			if (!(xmlelementvalue($this->xml, 'Label') == '')){			
				$this->Label = xmlelementvalue($this->xml, 'Label');
			}

			if (!(xmlelementvalue($this->xml, 'InverseLabel') == '')){			
				$this->InverseLabel = xmlelementvalue($this->xml, 'InverseLabel');
			}
			
			
			if (!(xmlelementvalue($this->xml, 'Definition') == '')){						
				$this->Definition = xmlelementvalue($this->xml, 'Definition');
			}

			if (!(xmlelementvalue($this->xml, 'Color') == '')){
				$this->Color = xmlelementvalue($this->xml, 'Color');
			}
			
		}	

		$Model->objModels->Relationships[$this->Id] = $this;

	}
	
	
	public function __get($name){
		
		switch ($name){
			case 'FromClass':
				$this->getFromClass();
				break;
			case 'ToClass':
				$this->getToClass();
				break;
			case 'Uri':
				$this->getUri();
				break;				
			default:
				return null;
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	private function getFromClass(){
		
		if (is_null($this->FromClass)){
			if (isset($this->Model->objModels->Classes[$this->FromClassId])){			
				$this->FromClass = $this->Model->objModels->Classes[$this->FromClassId];
			}
		}

		return $this->FromClass;		
		
	}

	private function getToClass(){
		
		if (is_null($this->ToClass)){
			if (isset($this->Model->objModels->Classes[$this->ToClassId])){
				$this->ToClass = $this->Model->objModels->Classes[$this->ToClassId];
			}
		}

		return $this->ToClass;		
		
	}
	
	public function getUri(){
		
		if (!is_null($this->Uri)){
			return $this->Uri;
		}
		
		$this->Uri = $this->Model->BaseUri . $this->Name;
		return $this->Uri;
		
	}
	
	
}




class clsProperty {

	public $xml = null;

	public $Class = null;
	public $Id = null;
	
	private $Uri = null;
	
	public $Name = null;
	public $Label = null;	
	public $Version = null;
	Public $Definition = null;
	public $Color = null;
	
	public $ShowInLists = true;
	public $ShowInForms = true;
	
	public $DataType = null;
	public $MinLength = null;
	public $MaxLength = null;
	public $Pattern = null;

	private $SuperProperties = null;
	private $EquivalentProperties = null;
	public $PartOf = null;
	public $Parts = array();
	
	private $dom = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
	
	private $Lists = null;
		
	public function __construct($Class = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Class)){
			$this->Class = $Class;
			$this->xpath = $this->Class->Model->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");

			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Label = $this->Name;
			if (xmlelementvalue($this->xml, 'Label') != ''){			
				$this->Label = xmlelementvalue($this->xml, 'Label');
			}
			if (xmlelementvalue($this->xml, 'Label') != ''){			
				$this->Definition = xmlelementvalue($this->xml, 'Definition');
			}
			if (!(xmlelementvalue($this->xml, 'Color') == '')){
				$this->Color = xmlelementvalue($this->xml, 'Color');
			}

			$this->DataType = $this->Class->Model->objModels->DataTypes[1];			
			$xmlField = $this->xpath->query("mod:Field",$this->xml)->item(0);
			if ($xmlField){
				$DataTypeId = $xmlField->getAttribute("datatypeId");
				if (isset($this->Class->Model->objModels->DataTypes[$DataTypeId])){
					$this->DataType = $this->Class->Model->objModels->DataTypes[$DataTypeId];
				}
				if ($xmlField->getAttribute("minLength") != ''){			
					$this->MinLength = $xmlField->getAttribute("minLength");
				}
				if ($xmlField->getAttribute("maxLength") != ''){			
					$this->MaxLength = $xmlField->getAttribute("maxLength");
				}
				
				if (xmlelementvalue($xmlField, 'Pattern') != ''){			
					$this->Pattern = xmlelementvalue($xmlField, 'Pattern');
				}
				
			}
			
			if ($this->xml->getAttribute("showInLists") == 'false'){
				$this->ShowInLists = false;
			}
			if ($this->xml->getAttribute("showInForms") == 'false'){
				$this->ShowInForms = false;
			}
			
			
		}
		if (!is_null($Class)){
			$Class->Model->objModels->Properties[$this->Id] = $this;
			$this->getUri();		
			$Class->Model->objModels->uriProperties[$this->Uri] = $this;
			
			foreach ($this->xpath->query("mod:Parts/mod:Property",$this->xml) as $xmlPropertyPart){
				$objPart = new clsProperty($Class, $xmlPropertyPart);
				$objPart->PartOf = $this;
				$this->Parts[$objPart->Id] = $objPart;
			}		
		}
	}
	

	public function __set($name, $value){
		
		switch ($name){
			case 'Uri':
				$this->Uri = $value;
				break;
		}
	}
	
	
	public function __get($name){
		
		switch ($name){
			case 'Lists':
				$this->getLists();
				break;
			case 'Uri':
				$this->getUri();
				break;
			case 'SuperProperties':
				$this->getSuperProperties();
				break;				
			case 'EquivalentProperties':
				$this->getEquivalentProperties();
				break;
			case 'dom':
				$this->getDom();
				break;
			default:
				return null;
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	private function getDom(){
		
		if (!is_null($this->dom)){
			return;
		}

		$this->getUri();
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;

		$DocumentElement = $this->dom->createElementNS(clsModels::nsSHOC, 'Property');
		
		$DocumentElement->setAttribute('id', $this->Id);
		$DocumentElement->setAttribute('uri', $this->Uri);

				$xmlName = $this->dom->createElementNS(clsModels::nsSHOC, 'Name');
		$xmlName->nodeValue = $this->Name;
		$DocumentElement->appendChild($xmlName);

		if (!is_null($this->Label)){		
			$xmlLabel = $this->dom->createElementNS(clsModels::nsSHOC, 'Label');
			$xmlLabel->nodeValue = $this->Label;
			$DocumentElement->appendChild($xmlLabel);
		}

		if (!is_null($this->Definition)){
			$xmlDefinition = $this->dom->createElementNS(clsModels::nsSHOC, 'Definition');
			$xmlDefinition->nodeValue = $this->Definition;
			$DocumentElement->appendChild($xmlDefinition);
		}
		
		$this->dom->appendChild($DocumentElement);

		return $this->dom;
		
	}
	
	public function getUri(){

		if (!is_null($this->Uri)){
			return $this->Uri;
		}
		
		$this->Uri = $this->Class->Model->BaseUri . $this->Name;
		return $this->Uri;
		
	}
	
	
	private function getLists(){
		if (!is_null($this->Lists)){
			return $this->Lists;
		}
		$this->Lists = array();
		
		foreach ($this->xpath->query("mod:PropertyLists/mod:PropertyList", $this->xml) as $xmlPropertyList){
			$ListId = $xmlPropertyList->getAttribute('listId');
			if (isset($this->Class->Model->objModels->Lists[$ListId])){
				$this->Lists[$ListId] = $this->Class->Model->objModels->Lists[$ListId];
			}
		}
		
		return $this->Lists;
		
	}	
	
	public function getSuperProperties(&$arrProperties = array()){

		if (!is_null($this->SuperProperties)){
			return $this->SuperProperties;
		}
		
		$this->getUri();
		
		if (isset($arrProperties[$this->Uri])){
			return array();
		}

		$arrProperties[$this->Uri] = $this;		
		
		$this->SuperProperties = array();

		foreach ($this->xpath->query("mod:SuperProperties/mod:SuperProperty",$this->xml) as $xmlSuperProperty){
			$SuperPropertyId = $xmlSuperProperty->getAttribute("propertyId");
			if (isset($this->Class->Model->objModels->Properties[$SuperPropertyId])){
				$SuperProperty = $this->Class->Model->objModels->Properties[$SuperPropertyId];
				$this->SuperProperties[$SuperPropertyId] = $SuperProperty;

// get the super properties of that property
				$this->SuperProperties = $this->SuperProperties + $SuperProperty->getSuperProperties($arrProperties);
			}
		}		
		
		return $this->SuperProperties;
		
	}

	public function getEquivalentProperties(&$arrProperties = array()){
		
		if (!is_null($this->EquivalentProperties)){
			return $this->EquivalentProperties;
		}
		
		$this->getUri();
		
		if (isset($arrProperties[$this->Uri])){
			return array();
		}

		$this->EquivalentProperties = array();

		foreach ($this->xpath->query("mod:EquivalentProperties/mod:EquivalentProperty",$this->xml) as $xmlEquivProperty){
			$EquivPropertyId = $xmlEquivProperty->getAttribute("propertyId");
			if (isset($this->Class->Model->objModels->Properties[$EquivPropertyId])){
				$EquivProperty = $this->Class->Model->objModels->Properties[$EquivPropertyId];
				$this->EquivalentProperties[$EquivPropertyId] = $EquivProperty;
				
				$this->EquivalentProperties =  $this->EquivalentProperties + $EquivProperty->getEquivalentProperties($arrProperties);
				
			}
		}		
		
		return $this->EquivalentProperties;
		
	}
	
	
	
}



class clsDataType {

	public $xml = null;

	public $Models = null;
	public $Id = null;
	
	public $Name = null;
	public $Uri = null;	
	
	private $System = null;
	public $xpath = null;
		
	public function __construct($Models = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Models)){
			$this->Models = $Models;
			$this->xpath = $this->Models->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");

			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Uri = xmlelementvalue($this->xml, 'Uri');			
		}
		
	}
}



class clsList {

	public $xml = null;

	public $Model = null;
	public $Id = null;
	
	public $Name = null;
	public $Version = null;
	Public $Definition = null;
	
	public $Terms = array();
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
	
	private $Properties = null;
		
	public function __construct($Model = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Model)){
			$this->Model = $Model;
			$this->xpath = $this->Model->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");

			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Definition = xmlelementvalue($this->xml, 'Definition');
		}
		$Model->objModels->Lists[$this->Id] = $this;
		
		foreach ($this->xpath->query("mod:Terms/mod:Term",$this->xml) as $xmlTerm){
			$objTerm = new clsTerm($this, $xmlTerm);
			$this->Terms[$objTerm->Id] = $objTerm;
		}
		
		
	}
		
	public function __get($name){
		
		switch ($name){
			case 'Properties':
				$this->getProperties();
				break;
			case 'dot':
				$this->dot = $this->getDot();
				break;				
			default:
				return null;
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	private function getProperties(){

		if (!is_null($this->Properties)){
			return $this->Properties;
		}

		foreach ($this->Model->objModels->Properties as $objProperty){
			if (isset($objProperty->Lists[$this->Id])){
				$this->Properties[$objProperty->Id] = $objProperty;
			}
		}

		return $this->Properties;
		
	}
		
	
	public function getDot($objDot = null, $objGraph = null){

		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsModelDot();
			$objDot->Style = 2;
			$Top = true;
		}

		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
				

		$dotLabel = $this->Name;
		$dotShape = null;
		if ($objDot->Style == 2){
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			$dotLabel .= "<tr><td colspan='2' bgcolor='lightyellow'>".'&lt;&lt;list&gt;&gt;'."</td></tr>"; 
			$dotLabel .= "<tr><td colspan='2' bgcolor='lightyellow'>".$this->Name."</td></tr>"; 
			
			foreach ($this->Terms as $objTerm){
				$dotLabel .= "<tr><td align='left' balign='left' valign='top'>".$objTerm->Label."</td></tr>";				
			}
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';
		}

		if (!isset($objDot->ListIds[$this->Id])){
			$objGraph->addNode('list_'.$this->Id, $dotLabel, $dotShape, null,null, null, 'list.php?listid='.$this->Id, null, null);
			$objDot->ListIds[$this->Id] = $this->Id;		
		}
				
		$this->dot = $objGraph->script;
		return $this->dot;		
		
	}
}


class clsTerm {

	public $xml = null;

	public $List = null;
	public $Id = null;
	
	public $Reference = null;
	public $Label = null;
	public $Version = null;
	Public $Definition = null;
	
	public $Terms = array();
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
		
	public function __construct($List = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($List)){
			$this->List = $List;
			$this->xpath = $this->List->xpath;
		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");

			$this->Reference = xmlelementvalue($this->xml, 'Reference');
			$this->Label = xmlelementvalue($this->xml, 'Label');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Definition = xmlelementvalue($this->xml, 'Definition');			
		}
		
		$List->Model->objModels->Terms[$this->Id] = $this;		
		
	}
	
	
	public function __get($name){
		
		switch ($name){
			default:
				return null;
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
		
}




class clsModelDot{
	public $Style = 1;

	public $ClassIds = array();
	public $RelIds = array();
	public $ListIds = array();
	public $SuperClassIds = array();
	
}

class clsArchetypeDot{
	public $Style = 1;

	public $ClassIds = array();
	public $ObjectIds = array();
	public $RelIds = array();
	public $ListIds = array();
	public $SuperClassIds = array();
	
}


class clsArchetypes {
	
	public $Items = array();

	public $objModels = null;
	
	public $Objects = array();
	public $Relationships = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "data";
	private $filename = "DataModels.xml";
	private $path = null;

	private $domDefaults = null;
	private $xpathDefaults = null;	
	
	public $Namespace = "http://www.istanduk.org/schemas/DataModeller";
	
	private $System = null;

	public function __construct($Models = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Models)){
			$this->objModels = $Models;
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->path = $System->path."/".$this->folder."//".$this->filename;
		
		if (@$this->dom->load($this->path) === false){

			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->Namespace, 'DataModeller');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->Refresh();
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('mod', $this->Namespace);
		
	}

	public function refresh(){

		foreach ($this->xpath->query("/mod:DataModeller/mod:Archetypes/mod:Archetype") as $xmlArchetype){
			$objArchetype = new clsArchetype($this,$xmlArchetype);
			$this->Items[$objArchetype->Id] = $objArchetype;				
		}
	}
	
	public function getItem($Id){
		if (isset($this->Items[$Id])){
			return $this->Items[$Id];			
		}
		return false;
	}
	
}
	
class clsArchetype{
	
	public $xml = null;

	public $objArchetypes = null;
	public $Id = null;
	
	public $Name = null;
	public $Version = null;
	public $Label = null;
	Public $Definition = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
			
	public $Objects = array();
	public $ObjectProperties = array();
	public $Relationships = array();
	
	
	private $dot = null;
	
	public function __get($name){
		switch ($name){
			case 'dot':
				$this->dot = $this->getDot();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	public function __construct($objArchetypes = null, $xml = null, $xpath = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($objArchetypes)){
			$this->objArchetypes = $objArchetypes;
			$this->xpath = $this->objArchetypes->xpath;
			
			$this->canView = $objArchetypes->canView;
			$this->canEdit = $objArchetypes->canEdit;
			$this->canControl = $objArchetypes->canControl;
			
		}

		
		if (!is_null($xpath)){
			$this->xpath = $xpath;
		}
		
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");

			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Definition = xmlelementvalue($this->xml, 'Definition');

			$this->Label = xmlelementvalue($this->xml, 'Label');
			if (empty($this->Label)){
				$this->Label = $this->Name;
			}
			
			
			
			foreach ($this->xpath->query("mod:Objects/mod:Object",$this->xml) as $xmlObject){
				$objObject = new clsObject($this,$xmlObject);
				$this->Objects[$objObject->Id] = $objObject;
				if (!is_null($objArchetypes)){
					$this->objArchetypes->Objects[$objObject->Id] = $objObject;
				}
			}

			
			foreach ($this->xpath->query("mod:ArchetypeRelationships/mod:ArchetypeRelationship",$this->xml) as $xmlArchetypeRelationship){
				$objArchetypeRelationship = new clsArchetypeRelationship($this,$xmlArchetypeRelationship);
				$this->Relationships[$objArchetypeRelationship->Id] = $objArchetypeRelationship;
				if (!is_null($objArchetypes)){
					$this->objArchetypes->Relationships[$objArchetypeRelationship->Id] = $objArchetypeRelationship;
				}
			}
		}
	}
		
	
	
	
	public function getDot($objDot = null, $objGraph = null){

		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsArchetypeDot();
			$Top = true;
		}
		if (is_null($objGraph)){
			$objGraph = new clsGraph();
			$objGraph->FlowDirection='LR';
		}

		$arrModelGraphs = array();
		
		foreach ($this->Objects as $objObject){
			
			$objModel = $objObject->Class->Model;
			$ModelId = $objModel->Id;
			
			if (!isset($arrModelGraphs[$ModelId])){
				$objModelGraph = $objGraph->addSubGraph('cluster','model:'.$objModel->Name);
				$arrModelGraphs[$ModelId] = $objModelGraph;				
			}
			else
			{
				$objModelGraph = $arrModelGraphs[$ModelId];
			}
			
			$objObject->getDot($objDot, $objModelGraph);
		}


		if ($Top){
			$this->getDotRelationships($objDot, $objGraph);
		}

		return $objGraph->script;

	}
	
	public function getDotRelationships($objDot, $objGraph){
		
		$arrStartObjectIds = $objDot->ObjectIds;
		
		foreach ($this->Relationships as $objRel){
			
			if (isset($arrStartObjectIds[$objRel->FromObjectId]) || isset($arrStartObjectIds[$objRel->ToObjectId] )){
				if (!isset($objDot->ObjectIds[$objRel->FromObjectId])){
					if (isset($this->Objects[$objRel->FromObjectId])){
						$objFromObject = $this->Objects[$objRel->FromObjectId];
						$objFromObject->getDot($objDot, $objGraph);						
					}
				}
				
				if (!isset($objDot->ObjectIds[$objRel->ToObjectId])){
					if (isset($this->Objects[$objRel->ToObjectId])){
						$objToObject = $this->Objects[$objRel->ToObjectId];
						$objToObject->getDot($objDot, $objGraph);
					}
				}
				
			}
			
			
		}

		
		foreach ($this->Relationships as $objRel){
			if (isset($objDot->RelIds[$objRel->Id])){
				continue;
			}

			if (isset($objDot->ObjectIds[$objRel->FromObjectId]) && isset($objDot->ObjectIds[$objRel->ToObjectId])){
				$ArrowHead = null;
				$ArrowTail = null;
				if ($objRel->Relationship->Extending){
					$ArrowTail = 'diamond';
					$ArrowHead = "none";
				}
				
				$objGraph->addEdge('object_'.$objRel->FromObjectId, 'object_'.$objRel->ToObjectId, $objRel->Label, null, null, $ArrowHead, $ArrowTail);
				$objDot->RelIds[$objRel->Id] = $objRel->Id;
			}
			
		}
		
		
	}
	
	
	
}


class clsObject{
	
	public $xml = null;

	public $Archetype = null;
	public $Id = null;
	
	public $Start = false;
	
	public $Name = null;
	public $Version = null;
	public $Label = null;
	
	Public $Definition = null;
	
	public $Color = 'lightblue';
	public $FontColor = null;
	
	
	private $ClassId = null;
	private $Class = null;
	
	private $dom = null;
	
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
			
	public $ObjectProperties = array();
	private $Relationships = null;
	
	public function __construct($Archetype = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Archetype)){
			$this->Archetype = $Archetype;
			$this->xpath = $this->Archetype->xpath;

			$this->canView = $Archetype->canView;
			$this->canEdit = $Archetype->canEdit;
			$this->canControl = $Archetype->canControl;

		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");
			
			if ($this->xml->getAttribute("start") == 'true'){
				$this->Start = true;
			}
			
			$this->ClassId = $this->xml->getAttribute("classId");
			
			$this->Name = xmlelementvalue($this->xml, 'Name');
			$this->Version = xmlelementvalue($this->xml, 'Version');
			$this->Label = xmlelementvalue($this->xml, 'Label');
			if ($this->Label == ''){
				$this->Label = $this->Name;
			}
			
			$this->Definition = xmlelementvalue($this->xml, 'Definition');


			if (!empty(xmlelementvalue($this->xml, 'Color'))){
				$this->Color = xmlelementvalue($this->xml, 'Color');
			}

			if (!empty(xmlelementvalue($this->xml, 'FontColor'))){
				$this->FontColor = xmlelementvalue($this->xml, 'FontColor');
			}
			
			
			foreach ($this->xpath->query("mod:ObjectProperties/mod:ObjectProperty",$this->xml) as $xmlObjectProperty){
				$objObjectProperty = new clsObjectProperty($this,$xmlObjectProperty);
				$this->ObjectProperties[$objObjectProperty->Id] = $objObjectProperty;				
			}			
			
		}
	}
	
	
	public function __get($name){
		switch ($name){
			case 'Class':
				$this->getClass();
				break;
			case 'Relationships':
				$this->getRelationships();
				break;
			case 'dom':
				$this->getDom();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	private function getDom(){
		
		if (!is_null($this->dom)){
			return;
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;

		$DocumentElement = $this->dom->createElementNS(clsModels::nsSHOC, 'Object');
		
		$DocumentElement->setAttribute('id', $this->Id);
		$DocumentElement->setAttribute('idClass', $this->ClassId);
		
		$xmlName = $this->dom->createElementNS(clsModels::nsSHOC, 'Name');
		$xmlName->nodeValue = $this->Name;
		$DocumentElement->appendChild($xmlName);

		if (!is_null($this->Label)){		
			$xmlLabel = $this->dom->createElementNS(clsModels::nsSHOC, 'Label');
			$xmlLabel->nodeValue = $this->Label;
			$DocumentElement->appendChild($xmlLabel);
		}

		if (!is_null($this->Definition)){
			$xmlDefinition = $this->dom->createElementNS(clsModels::nsSHOC, 'Definition');
			$xmlDefinition->nodeValue = $this->Definition;
			$DocumentElement->appendChild($xmlDefinition);
		}
		
		$this->dom->appendChild($DocumentElement);
		
		return;
		
	}
	
	
	private function getClass(){
		
		if (!is_null($this->Class)){
			return $this->Class;
		}
		if (isset($this->Archetype->objArchetypes->objModels->Classes[$this->ClassId])){
			$this->Class = $this->Archetype->objArchetypes->objModels->Classes[$this->ClassId];
		}

		return $this->Class;
		
	}

	private function getRelationships(){
		
		if (!is_null($this->Relationships)){
			return $this->Relationships;
		}
		
		$this->Relationships = array();

		foreach ($this->Archetype->Relationships as $objRelationship){
			if ($objRelationship->FromObjectId == $this->Id){
				$this->Relationships[$objRelationship->Relationship->Uri] = $objRelationship;
			}			
		}

		return $this->Relationships;
		
	}
	
	
	public function getDot($objDot = null, $objGraph = null, $Style=null){

		global $System;
		
		$Top = false;

		if (is_null($objDot)){
			$objDot = new clsArchetypeDot();
			$objDot->Style = 2;
			$Top = true;
		}
		if (is_null($Style)){
			$Style = $objDot->Style;
		}

		if (isset($objDot->ObjectIds[$this->Id])){
			return;
		}
		
		if (is_null($objGraph)){		
			$objGraph = new clsGraph();
		}
				
		$NodeId = 'object_'.$this->Id;

//		$this->getAllProperties();
//		$this->getAllRelationships();		
		
		$dotLabel = $this->Label;
		
		$dotShape = null;
		
/*		
		if ($Style == 2){
			$dotLabel = "<";
			$dotLabel .= "<table border='0' cellborder='1' cellspacing='0'>";
			$dotLabel .= "<tr><td colspan='2' bgcolor='lightblue'>".$this->Label."</td></tr>"; 
			
			foreach ($this->AllProperties as $objProperty){
				$portId = 'property_'.$objProperty->Id;
				$dotLabel .= "<tr><td align='left' balign='left' valign='top' port='$portId'>".$objProperty->Name."</td></tr>";				
				
				foreach ($objProperty->Lists as $objList){
					$objList->getDot($objDot, $objGraph);
					$EdgeLabel = '';
					$objGraph->addEdge($NodeId.':'.$portId, 'list_'.$objList->Id, $EdgeLabel,  null, 'dotted');
				}


				if (count($objProperty->Parts) > 0){
					$NodeParts = $this->getDotParts($objProperty, $objDot, $objGraph);
					$objGraph->addEdge($NodeId.':'.$portId, $NodeParts);
				}
				
			}
			$dotLabel .= "</table>";
			$dotLabel .= ">";			
			$dotShape = 'plaintext';
		}
*/
		
		$ParamId = '';
		if (is_object($System)){
			$ParamSid = $System->Session->ParamSid;		
		}
		$Url = "subjects.php?$ParamSid&objectid=".$this->Id;
		
		
		$objGraph->addNode('object_'.$this->Id, $dotLabel, $dotShape, $this->Color ,null, null, $Url, null, null, $this->FontColor);
		$objDot->ObjectIds[$this->Id] = $this->Id;		
/*
		if ($Top){
			foreach ($this->AllRelationships as $objRelationship){
				
				if ($objRelationship->Model->Id == $this->Model->Id){
				
				
					if (!isset($objDot->RelIds[$objRelationship->Id])){
						
						$FromNodeId = "class_".$objRelationship->FromClass->Id;
						// check if this is a super class and add as a concept
						$RelNodeStyle = $Style;
						if (isset($this->SuperClasses[$objRelationship->FromClass->Id])){
							$RelNodeStyle = 1;
							if (!isset($objDot->SuperClassIds[$this->Id][$objRelationship->FromClass->Id])){
								$objGraph->addEdge($NodeId, $FromNodeId, null, null, "dotted");
								$objDot->SuperClassIds[$this->Id][$objRelationship->FromClass->Id] = true;
							}
						}
						$objRelationship->FromClass->getDot($objDot, $objGraph, $RelNodeStyle);
	
						$ToNodeId = "class_".$objRelationship->ToClass->Id;
						// check if this is a super class and add as a concept
						$RelNodeStyle = $Style;
						if (isset($this->SuperClasses[$objRelationship->ToClass->Id])){
							$RelNodeStyle = 1;
							if (!isset($objDot->SuperClassIds[$this->Id][$objRelationship->ToClass->Id])){
								$objGraph->addEdge($NodeId, $ToNodeId, null, null, "dotted");
								$objDot->SuperClassIds[$this->Id][$objRelationship->ToClass->Id] = true;
							}						
						}
						$objRelationship->ToClass->getDot($objDot, $objGraph, $RelNodeStyle);
						
						
						$objGraph->addEdge($FromNodeId, $ToNodeId, $objRelationship->Label);
						$objDot->RelIds[$objRelationship->Id] = $objRelationship->Id;
					}
				}
			}
		
//			$this->Model->getDotRelationships($objDot, $objGraph);
		}
*/		
		$this->dot = $objGraph->script;
		return $this->dot;		
		
	}
	
	
}
	
class clsObjectProperty{
	
	public $xml = null;

	public $Object = null;
	public $idField = null;
	public $Id = null;
	
	public $Name = null;
	public $Version = null;
//	public $Label = null;
	
	Public $Definition = null;
	public $Cardinality = 'once';
	
	
	private $PropertyId = null;
	private $Property = null;
	
	
	public $PartOf = null;
	public $Parts = array();
	
	private $objModels = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
			
	public function __construct($Object = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Object)){
			$this->Object = $Object;
			$this->xpath = $this->Object->xpath;

			$this->canView = $Object->canView;
			$this->canEdit = $Object->canEdit;
			$this->canControl = $Object->canControl;
			
			if (!is_null($this->Object->Archetype->objArchetypes)){
				$this->objModels = $this->Object->Archetype->objArchetypes->objModels;
			}

		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			$this->Id = $this->xml->getAttribute("id");
			$this->PropertyId = $this->xml->getAttribute("propertyId");
			
			$Cardinality = $this->xml->getAttribute("cardinality");
			if (!empty($Cardinality)){
				$this->Cardinality = $Cardinality;
			}			
						
			foreach ($this->xpath->query("mod:ObjectPropertyParts/mod:ObjectProperty",$this->xml) as $xmlObjectPropertyPart){
				$objPart = new clsObjectProperty($Object, $xmlObjectPropertyPart);
				$objPart->PartOf = $this;
				$this->Parts[$objPart->Id] = $objPart;
			}
			
		}
		
		$this->Object->Archetype->ObjectProperties[$this->Id] = $this;
		
	}
	
	public function __get($name){
		switch ($name){
			case 'Property':
				$this->Property = $this->getProperty();
				break;
				
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}

	
	
	private function getProperty(){
		if (!is_null($this->Property)){
			return $this->Property;
		}
		
		if (is_null($this->PropertyId)){
			return null;
		}

		if (isset($this->objModels->Properties[$this->PropertyId])){	
			$this->Property = $this->objModels->Properties[$this->PropertyId];
		}
		
		return $this->Property;
		
	}
	
	
	
}

class clsArchetypeRelationship{
	
	public $xml = null;

	public $Archetype = null;
	public $Id = null;
	
	public $Label = null;
	public $Inverse = false;
	public $Definition = null;
	public $Cardinality = 'once';
	public $Select = false;
	public $Create = false;
	
	
	public $RelationshipId = null;
	private $Relationship = null;

	public $FromObjectId = null;
	private $FromObject = null;
	
	public $ToObjectId = null;
	private $ToObject = null;
	
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	private $System = null;
	public $xpath = null;
			
	public function __construct($Archetype = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		if (!is_null($Archetype)){
			$this->Archetype = $Archetype;
			$this->xpath = $this->Archetype->xpath;

			$this->canView = $Archetype->canView;
			$this->canEdit = $Archetype->canEdit;
			$this->canControl = $Archetype->canControl;

		}
		
		if (!is_null($xml)){
			$this->xml = $xml;
		}
		
		if (!is_null($this->xml)){
			
			$this->Id = $this->xml->getAttribute("id");
			
			if ($this->xml->getAttribute('inverse') == 'true'){
				$this->Inverse = true;
			}
			if ($this->xml->getAttribute('select') == 'true'){
				$this->Select = true;
			}
			if ($this->xml->getAttribute('create') == 'true'){
				$this->Create = true;
			}
			$this->RelationshipId = $this->xml->getAttribute("relationshipId");

			if ($this->xml->getAttribute('cardinality') !== ''){
				$this->Cardinality = $this->xml->getAttribute('cardinality');
			}

			$xmlLabel = $this->xpath->query("mod:Label",$this->xml)->item(0);
			if ($xmlLabel){
				$this->Label = $xmlLabel->nodeValue;
			}			
			
			$xmlFromObject = $this->xpath->query("mod:FromObject",$this->xml)->item(0);
			if ($xmlFromObject){
				$this->FromObjectId = $xmlFromObject->getAttribute("objectId");
			}
			
			$xmlToObject = $this->xpath->query("mod:ToObject",$this->xml)->item(0);
			if ($xmlToObject){
				$this->ToObjectId = $xmlToObject->getAttribute("objectId");
			}			
			
		}
	}
	
	
	public function __get($name){
		switch ($name){
			case 'Relationship':
				$this->getRelationship();
				break;
			case 'FromObject':
				$this->getFromObject();
				break;
			case 'ToObject':
				$this->getToObject();
				break;
		}
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
	}
	
	
	private function getRelationship(){
		
		if (!is_null($this->Relationship)){
			return $this->Relationship;
		}

		if (isset($this->Archetype->objArchetypes->objModels->Relationships[$this->RelationshipId])){
			$this->Relationship = $this->Archetype->objArchetypes->objModels->Relationships[$this->RelationshipId];
		}

		return $this->Relationship;
		
	}

	
	private function getFromObject(){
		
		if (!is_null($this->FromObject)){
			return $this->FromObject;
		}

		if (isset($this->Archetype->Objects[$this->FromObjectId])){
			$this->FromObject = $this->Archetype->Objects[$this->FromObjectId];
		}

		return $this->FromObject;
		
	}
	
	private function getToObject(){
		
		if (!is_null($this->ToObject)){
			return $this->ToObject;
		}

		if (isset($this->Archetype->Objects[$this->ToObjectId])){
			$this->ToObject = $this->Archetype->Objects[$this->ToObjectId];
		}

		return $this->ToObject;
		
	}
	
	
}



?>