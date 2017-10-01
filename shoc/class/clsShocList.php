<?php

	require_once("class/clsSystem.php");
	require_once("function/utils.inc");
	require_once("class/clsModel.php");
	require_once("class/clsShocData.php");
	require_once("class/clsThread.php");
	require_once("class/clsGraph.php");
	
	require_once("function/utils.inc");
	
	

class clsShocList {

	public $dom = null;
	private $xml = null;
	private $xpath = null;	
	
	private $html = null;
	private $csv = null;
	private $json = null;
	private $dot = null;
	
	private $Heading = null;

	private $Subjects = null;	
	private $Class = null;
	private $Object = null;
	
	private $ShowClass = false;
	
	public $Page = 1;
	public $RowsPerPage = 30;
	public $cssClass = 'list';
	
	private $idColumn = 0;
	
	private $System = null;
	
	public $ReturnUrl = null;
//	public $ReturnParam = 'uriSubject';
	public $ShowLink = false;
	
	
	public function __construct(){
		
		global $System;
		if (isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;
		
	}
	
	public function __set($name, $value){
		switch ($name){
			case 'Subjects':
				$this->Subjects = $value;
				break;
				
			case 'Object':
				$this->Object = $value;
				break;				

			case 'Class':
				$this->Class = $value;
				break;				
				
		}
		
	}
	
	public function __get($name){
		switch ($name){
			case 'html':
				$this->makeHtml();
				break;
			case 'csv':
				$this->makeCsv();
				break;
			case 'json':
				$this->makeJson();
				break;
				
			case 'dot':
				$this->makeDot();
				break;
				
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;
		
		
	}
	
	public function make(){
		
		$this->Heading = new clsShocListHeading($this);
				
		$this->makeHeading($this->Heading);
	}

	
	private function makeHeading($Heading = null){
		
		if (is_null($Heading)){
			$Heading = new clsShocListHeading();
			$Heading->ShowClass = $this->ShowClass;
		}
		
		$Heading->Labels = array();
		$Heading->numRows = 0;
		
		if (is_null($this->Object)){
			return;
		}
		
		$Class = $this->Object->Class;

		$arrProperties = array();
		foreach ($this->Object->ObjectProperties as $objObjectProperty){
			$arrProperties[] = $objObjectProperty->Property;
		}		

		$this->MakeLabels($Heading->Labels, $arrProperties);
		
// do relationships

		foreach ($this->Object->Relationships as $objObjectRelationship){
			$objRelationship = $objObjectRelationship->Relationship;
			$useRelationship = false;
			
			if ($objObjectRelationship->FromObject === $this->Object){
				if ($objRelationship->Extending){
					$useRelationship = true;
				}
			}

			if ($objObjectRelationship->FromObject === $this->Object){
				switch ($objObjectRelationship->Inverse){
					case false:
						if ($objRelationship->ShowInLists){
							$useRelationship = true;
						}
						break;
					default:
						if ($objRelationship->InverseShowInLists){
							$useRelationship = true;
						}
						break;
				}
			}
			
			if ($useRelationship){
				$RelLabel = $objObjectRelationship->Label;
				$objLinkObject = $objObjectRelationship->ToObject;
				
				$objLabel = new clsListLabel();
				$objLabel->Class = $objLinkObject->Class;
				$objLabel->Relationship = $objRelationship;
				$objLabel->Inverse = $objObjectRelationship->Inverse;
				$objLabel->Label = $RelLabel .' '.$objLinkObject->Label;
				$Heading->Labels[] = $objLabel;
				
				$arrProperties = array();
				foreach ($objLinkObject->ObjectProperties as $objObjectProperty){
					$arrProperties[] = $objObjectProperty->Property;
				}		
				
				$this->MakeLabels($objLabel->Labels, $arrProperties, $objLabel);		
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
	
	private function makeHtml(){

		if (!is_null($this->html)){
			return $this->html;
		}

		$this->make();

		$Content = "";
		$Content .= "<table class='".$this->cssClass."'>";
		
		$Content .= $this->Heading->html;
/*		
		switch ($this->ReturnParam){
			case 'uriLink':
				foreach ($this->Subjects->LinkItems as $uriLink=>$objSubject){
					$objListSubject = new clsShocListSubject($this, $objSubject, $uriLink);
					$Content .= $objListSubject->html;
				}
				
				foreach ($this->Subjects->BoxLinkItems as $uriBoxLink=>$arrBoxLinkItems){
					foreach ($arrBoxLinkItems as $uriSubject=>$objSubject){					
						$objListSubject = new clsShocListSubject($this, $objSubject, $uriBoxLink);
						$Content .= $objListSubject->html;
					}
				}
				break;
				
			default:
				foreach ($this->Subjects->SortedItems as $objSubject){
					$objListSubject = new clsShocListSubject($this, $objSubject);
					$Content .= $objListSubject->html;
				}
				break;
		}
*/		
		
		
		if ($this->ShowLink){
			$ParamSid = $this->System->Session->ParamSid;
			
			foreach ($this->Subjects->LinkItems as $uriLink=>$objSubject){
				$objListSubject = new clsShocListSubject($this, $objSubject, "link.php?$ParamSid&urilink=$uriLink");
				$Content .= $objListSubject->html;
			}
				
			foreach ($this->Subjects->BoxLinkItems as $uriBoxLink=>$arrBoxLinkItems){
				foreach ($arrBoxLinkItems as $uriSubject=>$objSubject){					
					$objListSubject = new clsShocListSubject($this, $objSubject, "boxlink.php?$ParamSid&uriboxlink=$uriBoxLink");
					$Content .= $objListSubject->html;
				}
			}
		}
		else
		{
			foreach ($this->Subjects->SortedItems as $objSubject){
				$objListSubject = new clsShocListSubject($this, $objSubject);
				$Content .= $objListSubject->html;
			}
		}

		
		
		$Content .= "</table>";

		$this->html = $Content;
		
		return $this->html;
		
	}

	
	private function makeDot(){

		if (!is_null($this->dot)){
			return $this->dot;
		}

		$this->make();

		$Content = "";
		$Content .= "<table border='0' cellborder='1' cellspacing='0'>";
		
		$Content .= $this->Heading->dot;
		
		foreach ($this->Subjects->SortedItems as $objSubject){
			$objListSubject = new clsShocListSubject($this, $objSubject);
			$Content .= $objListSubject->dot;
			
		}
		
		$Content .= "</table>";	
		
		$this->dot = $Content;
	
		return $this->dot;
				
	}
	
	
	private function makeCsv(){

		if (!is_null($this->csv)){
			return $this->csv;
		}

		$Content = '';
		
		$this->make();

		$Content .= $this->Heading->csv;
		
		foreach ($this->Subjects->SortedItems as $objSubject){
			$objListSubject = new clsShocListSubject($this, $objSubject);
			$Content .= $objListSubject->csv;
		}
		
		$this->csv = $Content;
		
		return $this->csv;
		
	}
	
	private function makeJson(){

		if (!is_null($this->json)){
			return $this->json;
		}

		$Content = '';
		
		$this->make();
		
		$arrJson = array();
		$arrJson['heading'] = json_decode($this->Heading->json, true);

		$Content .= $this->Heading->json;
		
		foreach ($this->Subjects->SortedItems as $objSubject){
			$objListSubject = new clsShocListSubject($this, $objSubject);
			$arrJson['subjects'][$objSubject->Id]= json_decode($objListSubject->json,true);
		}
		
		$this->json = json_encode($arrJson);
		
		return $this->json;
		
	}
	
	
}	
	



class clsShocListHeading {
	
	private $List = null;
	
	public $Labels = null;
	private $ColumnLabels = null;
	public $numRows = null;
	public $ShowClass = false;
	
	private $html = null;
	private $csv = null;
	private $json = null;
	private $dot = null;
	
	
	public function __get($name){
		switch ($name){
			case 'ColumnLabels':
				if (is_null($this->ColumnLabels)){
					$this->makeColumnLabels();
				}
				break;				
			case 'html':
				$this->makeHtml();
				break;
			case 'dot':
				$this->makeDot();
				break;
			case 'csv':
				$this->makeCsv();
				break;
			case 'json':
				$this->makeJson();
				break;				
		}
		
		if (property_exists($this, $name)){
			return $this->$name;
		}
		return null;	
		
	}
	
	
	public function __construct($List){		
		
		$this->List = $List;

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
		return;
	}
	
	private function makeColumnLabels($Labels = null){
		
		if (is_null($this->ColumnLabels)){
			$this->ColumnLabels = array();
		}

		if (is_null($Labels)){
			$Labels = $this->Labels;
		}
		
		
		foreach ($Labels as $objLabel){
			if (count($objLabel->Labels) >0){
				$this->makeColumnLabels($objLabel->Labels);
			}
			else
			{
				$this->ColumnLabels[] = $objLabel;
			}
		}
		return;
		
	}


	private function makeRows($RowNum = 1, $Labels = null, $Rows = null){

		if (is_null($Rows)){
			$Rows = array();
			for ($i=1; $i<=$this->numRows;$i++){
				$Row = new clsShocListRow();
				$Row->RowNum = $i;
				$Rows[$i] = $Row;
			}
		}
		
		if (is_null($Labels)){
			$Labels = $this->Labels;
		}

		$Row = $Rows[$RowNum];
		
		foreach ($Labels as $Label){
			$Col = new clsShocListColumn();
			$Col->Label = $Label->Label;
			$Row->Columns[] = $Col;

			for ($i=1;$i<=($this->getColspan($Label) - 1);$i++){
				$Col = new clsShocListColumn();
				$Col->Label = '';
				$Row->Columns[] = $Col;				
			}
			
			if (count($Label->Labels) > 0){
				$Rows = $this->makeRows($RowNum + 1, $Label->Labels, $Rows);
			}
			else
			{
				for ($i=$RowNum + 1; $i<=($this->numRows);$i++){
					$Col = new clsShocListColumn();
					$Col->Label = '';
					$Rows[$i]->Columns[] = $Col;									
				}
			}
			
		}
		
		return $Rows;
	}

	
	private function getColspan($Label){

		$Colspan = 0;
		if (count($Label->Labels) == 0){
			$Colspan = 1;
		}
		foreach ($Label->Labels as $SubLabel){
			$Colspan += $this->getColspan($SubLabel);
		}
		return ($Colspan);
		
	}
	
	
	
	private function makeHtml(){
		
		$this->numRows = 0;
		$this->getNumRows();	
		
		$Content = '';

		$Content .= "<thead>";
		
		for ($i = 1; $i <= $this->numRows; $i++) {
			$Content .= '<tr>';
			$Content .= $this->MakeHtmlRow($i);
			$Content .= "</tr>";
		}
		$Content .= "</thead>";
		
		$this->html = $Content;
		
		return $this->html;
		
		
	}

	private function makeDot(){
		
		$this->numRows = 0;
		$this->getNumRows();	
		
		$Content = '';
		for ($i = 1; $i <= $this->numRows; $i++) {
			$Content .= '<tr>';
			$Content .= $this->MakeHtmlRow($i,'dot');
			$Content .= "</tr>";
		}
		
		$this->dot = $Content;
		
		return $this->dot;
				
	}
	
	
	private function MakeHtmlRow($Level = 1, $Mode='html', $Labels = null, $CurrentLevel= 1){

		$Content = '';
		if (is_null($Labels)){
			$Labels = $this->Labels;
		}

		if (($this->List->ShowLink === true)){
			if ($Mode == 'html'){
				if ($CurrentLevel == $Level){
					if ($Level == 1){
						// extra column for the link URL
						$Content .= "<th";
						$RowSpan = $this->numRows;
						if ($RowSpan > 1){
							$Content .= " rowspan='$RowSpan'";
						}
						$Content .= "/>";						
					}
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

						
						
						$Content .= " align='left' balign='left' valign='top' ";
						
						
						if (is_object($this->List->Object)){
							$Content .= " bgcolor='".$this->List->Object->Color."'";												
						}
						else
						{
							$Content .= " bgcolor='lightblue'";
						}
						
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
					$Content .= $this->MakeHtmlRow($Level, $Mode, $objLabel->Labels, $CurrentLevel+1);
				}
			}

		}
		
		return $Content;
		
	}
	
	
	
	private function makeCsv(){

		$this->numRows = 0;
		$this->getNumRows();	
		
		$Rows = $this->MakeRows();
		$Content = '';
		
		foreach ($Rows as $Row){
			foreach ($Row->Columns as $Column){
				$Content .= $Column->Label.',';
			}
			$Content .= "\n";
		}


		$this->csv = $Content;
		
		return $this->csv;
		
		
	}

	
	
	private function MakeCsvRow($Level = 1, $Labels = null, $CurrentLevel= 1){

		$Content = '';
		if (is_null($Labels)){
			$Labels = $this->Labels;
		}

		foreach ($Labels as $objLabel){
			if ($CurrentLevel == $Level){		

				$ColSpan = 1;
				if (count($objLabel->Labels) > 0){
					$ColSpan = count($objLabel->Labels);
				}
				
				$Content .= CsvField($objLabel->Label);				
				for ($i=1;$i<$ColSpan+1;$i++){
					$Content .= ",";
				}
			}

			if (count($objLabel->Labels) > 0){
				if ($CurrentLevel < $Level){
					$Content .= $this->MakeCsvRow($Level, $objLabel->Labels, $CurrentLevel+1);
				}
			}

		}
		
		return rtrim($Content,',');
		
	}
	

	private function makeJson(){
		
		if (!is_null($this->json)){
			return $this->json;
		}		
		
		$this->numRows = 0;
		$this->getNumRows();	
		
		$Rows = $this->MakeRows();
		$this->json = json_encode($Rows);
		
		return $this->json;
				
	}

}


class clsShocListLabel {
	public $Label = '';	
	public $Class = null;
	public $Property = null;
	public $Relationship = null;
	public $Labels = array();
}

class clsShocListSubject {

	private $Rows = array();
	private $numRows = 0;

	private $List = null;
	private $Subject = null;
	
	private $LinkURL = null;
	
	private $html = null;
	private $csv = null;
	private $json = null;
	private $dot = null;
	
	private $System = null;
	
	private $NextRowNum = 1;

	public function __construct($List, $Subject, $LinkURL = null){

		global $System;
		if (isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;
		
		$this->List = $List;
		$this->Subject = $Subject;

		if (!is_null($LinkURL)){
			$this->LinkURL = $LinkURL;
		}
				
		$this->makeSubject();

		$this->numRows = count($this->Rows);
		
	}
	
	private function makeSubject($Subject = null, $Labels = null){

		if (is_null($Subject)){
			$Subject = $this->Subject;
		}
		
		
		if (is_null($Labels)){
			$Labels = $this->List->Heading->Labels;
		}
		
		$StartRowNum = $this->NextRowNum;
		
		$MaxRow = 0;
		
		foreach ($Labels as $objLabel){
			$RowNum = $StartRowNum;
			$this->NextRowNum = $RowNum;
			
			if (is_null($objLabel->Relationship)){
				$objProperty = $objLabel->Property;
				foreach ($Subject->Attributes as $objAttribute){
					if ($objAttribute->Property === $objLabel->Property){
						if (!isset($this->Rows[$RowNum])){
							$objRow = new clsShocListRow();
							$objRow->RowNum = $RowNum;
							$this->Rows[$RowNum] = $objRow;
						}
						$objRow = $this->Rows[$RowNum];
						if (count($objLabel->Labels) == 0){
							$objCol = new clsShocListColumn();
							$objCol->Label = $objLabel;
							$objCol->Value = $objAttribute->Statement->ValueLabel;
							$objRow->Columns[] = $objCol;
														
							if ($RowNum > $MaxRow){
								$MaxRow = $RowNum;
							}
														
						}
						++$RowNum;
					}
				}
			}
			else
			{
				$boolGotLink = false;				
				foreach ($Subject->Links as $objLink){
					if ($objLink->Relationship == $objLabel->Relationship){
						switch ($objLabel->Inverse){
							case false:
								if ($objLink->FromSubject->Uri == $Subject->Uri){
									$this->makeSubject($objLink->ToSubject, $objLabel->Labels, $RowNum);
									$boolGotLink = true;
								}
								
								break;
							default:
								if ($objLink->ToSubject->Uri== $Subject->Uri){
									$this->makeSubject($objLink->FromSubject, $objLabel->Labels, $RowNum);
									$boolGotLink = true;								
								}
								break;
						}
						
						$RowNum += $this->NextRowNum;
						
					}
				}
			}
		}
		$this->NextRowNum = $MaxRow + 1;
		
	}
	
	private function makeRowLabels($Labels = null, $RowLabels = null){
// creates an array of the lowest level of labels

		if (is_null($Labels)){
			$Labels = $this->List->Heading->Labels;
		}

		if (is_null($RowLabels)){
			$RowLabels = array();
		}

		foreach ($Labels as $objLabel){
			if (count($objLabel->Labels) > 0){
				$RowLabels = $this->makeRowLabels($objLabel->Labels, $RowLabels);
			}
			else
			{
				$RowLabels[] = $objLabel;
			}
			
		}
		
		return $RowLabels;

	}
	
	public function __get($name){
		switch ($name){
			case 'html':
				$this->getHtml();
				break;				
			case 'csv':
				$this->getCsv();
				break;				
			case 'json':
				$this->getJson();
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
		
	private function getHtml($Mode = 'html'){

		if ($Mode == 'html'){
			if (!is_null($this->html)){
				return $this->html;
			}
		}

		$RowLabels = $this->makeRowLabels();
		
		$Content = '';
		
		$boolDoneSubjectHref = false;
		$boolDoneLinkHref = false;
		
		if ($Mode !== 'html'){
			$boolDoneSubjectHref = true;
			$boolDoneLinkHref = true;
		}

		
		
		foreach ($this->Rows as $objRow){
			$Content .= "<tr>";
			
			if (!is_null($this->LinkURL)){
				$Content .= "<td>";
				if (!$boolDoneLinkHref){
					$Content .= "<a href='".$this->LinkURL."'>link</a>";
					$boolDoneLinkHref = true;
				}
				$Content .= "</td>";				
			}
			
			
			foreach ($RowLabels as $objLabel){
				$Value = '';
				foreach ($objRow->Columns as $objColumn){
					if ($objColumn->Label === $objLabel){
		
						$Value = $objColumn->Value;					
						if (!$boolDoneSubjectHref){													
							$uriSubject = $this->Subject->Uri;							
							$SelectValue = "<a href='subject.php?";						
							switch ($this->List->ReturnUrl){
								case null:
									$SelectValue = "<a href='subject.php?";
									if (!is_null($this->System->Session->ParamSid)){
										$SelectValue .= $this->System->Session->ParamSid.'&';
									}
									$SelectValue .= "urisubject=".$this->Subject->Uri."'>$Value</a>";
									break;
								default:
									$SelectValue = "<a href='".$this->List->ReturnUrl.$this->Subject->Uri."'>$Value</a>";								
									break;
							}
							$Value = $SelectValue;
							$boolDoneSubjectHref = true;
						}
					}
				}
				$boolUseValue = false;
				if ($objRow->RowNum == 1){
					$boolUseValue = true;
				}
				if ($Value != ''){
					$boolUseValue = true;
				}
				if ($boolUseValue){
					$RowSpan = $this->getRowSpan($objRow, $objLabel);
					
					switch ($Mode){
						case 'dot':
							$Content .= $this->getColumnDot(null, $Value, $RowSpan);
							break;
						default:
							$Content .= $this->getColumnHtml(null, $Value, $RowSpan);
							break;
					}
				}
					
			}
			
			$Content .= "</tr>";
		}
		
		if ($Mode == 'html'){		
			$this->html = $Content;
		}
		
		return $Content;

	}
	
	
	private function getDot(){

		if (!is_null($this->dot)){
			return $this->dot;
		}
		$Content = $this->getHtml('dot');
		
		$this->dot = $Content;
		
		return $this->dot;

	}

	private function getColumnHtml($Label = null,$Value, $RowSpan = 1){

		$Content = '';
		
		$Content .= "<td";
		if ($RowSpan > 1) {
			$Content .= " rowspan='$RowSpan'";
		}
		$Content .= ">";
		if (substr($Value, 0,1) != '<'){
			if (strlen($Value) > 100){
				$Value = truncate($Value,100);
			}		
		}
		
		$Content .= $Value;
		$Content .= "</td>";

		return $Content;
		
	}

	
	private function getColumnDot($Label = null,$Value, $RowSpan = 1){

		$Content = '';
		
		$Content .= "<td";
		if ($RowSpan > 1) {
			$Content .= " rowspan='$RowSpan'";
		}		
		$Content .= " align='left' balign='left' valign='top' ";
		
		$uriSubject = $this->Subject->Uri;
		$ParamSid = $this->System->Session->ParamSid;
		$Content .= " href='subject.php?$ParamSid&amp;urisubject=$uriSubject' ";		
		$Content .= ">";
		
		
		if (strlen($Value) > 100){
			$objGraph = new clsGraph();
			$Value = $objGraph->FormatDotCell(truncate($Value),100);
		}
		
		$Content .= $Value;
		$Content .= "</td>";

		return $Content;
		
	}
	
	
	
	private function getRowSpan($StartRow, $Label){
		
		$Rowspan = 1;
		$Start = false;
		foreach ($this->Rows as $objRow){
			if ($objRow === $StartRow){
				$Start = true;
			}
			else
			{
				if ($Start){
					$Found = false;
					foreach ($objRow->Columns as $objColumn){
						if ($objColumn->Label === $Label){
							if ($objColumn->Value != ''){
								$Found = true;
							}
						}
					}
					if ($Found){
						$Start = false;
					}
					else
					{
						++$Rowspan;
					}
				}
			}
		}
		return $Rowspan;
		
	}
	
	
	
	private function getCsv(){

		if (!is_null($this->csv)){
			return $this->csv;
		}

		$RowLabels = $this->makeRowLabels();
		
		$Content = '';
		foreach ($this->Rows as $objRow){
			foreach ($RowLabels as $objLabel){			
					
				foreach ($objRow->Columns as $objColumn){
					if ($objColumn->Label === $objLabel){
						$Value = $objColumn->Value;
						$Content .= Csvfield($Value);
					}
				}
				$Content .= ',';
			}
			$Content = rtrim($Content,',');
			$Content .= "\n";
		}
		
		$this->csv = $Content;
		
		return $this->csv;

	}	
	
	
	private function getJson(){

		if (!is_null($this->json)){
			return $this->json;
		}

		$RowLabels = $this->makeRowLabels();
		
		$Content = '';
		foreach ($this->Rows as $objRow){
			foreach ($RowLabels as $objLabel){			
					
				foreach ($objRow->Columns as $objColumn){
					if ($objColumn->Label === $objLabel){
						$Value = $objColumn->Value;
						$Content .= Csvfield($Value);
					}
				}
				$Content .= ',';
			}
			$Content = rtrim($Content,',');
			$Content .= "\n";
		}
		
		$this->csv = $Content;
		
		return $this->csv;

	}	
	
}

class clsShocListRow {

	public $RowNum = 0;
	public $Columns = array();
	
}

class clsShocListColumn {

	public $Label = null;
	public $Value = null;
	
}

?>