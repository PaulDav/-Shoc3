
const nsSHOC = "http://data.shocdata.com/schema#";

var nsArray = {
		'rdf' : 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
		'rdfs' : 'http://www.w3.org/2000/01/rdf-schema#',
		'xsd' : 'http://www.w3.org/2001/XMLSchema#',
		'dct' : 'http://purl.org/dc/terms/',
	    'sparql' : 'http://www.w3.org/2005/sparql-results#',
		'shoc' : nsSHOC,
		'mod' : 'http://www.istanduk.org/schemas/DataModeller'
		
	  };

var clsShoc = function(){

	this.Models = [];
	this.Classes = [];
	this.Properties = [];
	
	this.Archetypes = [];
	this.Objects = [];
	
	this.SessionId = null;
	
}


var clsShocClass = function(id){

	var objClass = this;
	
	this.dom = null;
	this.xml = null;
	this.complete = false;
	
	this.id = null;
	
	this.Name = null;
	this.Label = null;
	this.Definition = null;
	this.Properties = [];

	var url = "api/classid/" + id + "/class";
	
	var req = getRequest();
	req.open("GET",url,true);
	
	try {req.responseType = "msxml-document";} catch(err) {} // Helping IE11
	req.onreadystatechange = function () {
	
		if (req.readyState == 4){
			var strListResponse = req.responseText;
			objClass.dom = new pdDom();
			objClass.dom.loadXML(strListResponse);
			
			objClass.xml = new pdXpathNodeList(objClass.dom.Dom,"/shoc:API/shoc:Class", objClass.dom.Dom.documentElement, nsArray).iterateNext();
			
			objClass.id = objClass.xml.getAttribute("id");

			objClass.Name = pdXmlElementValue(new pdXpathNodeList(objClass.dom.Dom,"shoc:Name", objClass.xml, nsArray).iterateNext());
			objClass.Label = pdXmlElementValue(new pdXpathNodeList(objClass.dom.Dom,"shoc:Label", objClass.xml, nsArray).iterateNext());

			var xpathProperties = new pdXpathNodeList(objClass.dom.Dom,"shoc:Properties/shoc:Property", objClass.xml, nsArray);
			var xmlProperty = xpathProperties.iterateNext();
			
			while (xmlProperty){
				var objProperty = new clsShocProperty();
				objProperty.make(objClass.dom, xmlProperty);
				objClass.Properties.push(objProperty);
				xmlProperty = xpathProperties.iterateNext();	
			}

			objClass.complete = true;
			
		}
	}
	req.send(null);
	
	return;
	
};



var clsShocProperty = function(id){

	id = (id === undefined) ? null : id;
	
	var objProperty = this;
			
	return;
	
};

clsShocProperty.prototype.make = function(dom,xml){

	var objProperty = this;
	
	objProperty.dom = dom;
	objProperty.xml = xml;
		
	objProperty.id = this.xml.getAttribute("id");
	objProperty.uri = this.xml.getAttribute("uri");
		
	objProperty.Name = pdXmlElementValue(new pdXpathNodeList(this.dom.Dom,"shoc:Name", this.xml, nsArray).iterateNext());
	objProperty.Version = pdXmlElementValue(new pdXpathNodeList(this.dom.Dom,"shoc:Version", this.xml, nsArray).iterateNext());
	objProperty.Label = pdXmlElementValue(new pdXpathNodeList(this.dom.Dom,"shoc:Label", this.xml, nsArray).iterateNext());

};



var clsShocObject = function(id){

	var objObject = this;
	
	this.dom = null;
	this.xml = null;
	this.complete = false;
	
	this.id = null;
	this.idClass = null;
	
	this.Name = null;
	this.Label = null;
	this.Definition = null;

	var url = "api/objectid/" + id + "/object";
	
	var req = getRequest();
	req.open("GET",url,true);
	
	try {req.responseType = "msxml-document";} catch(err) {} // Helping IE11
	req.onreadystatechange = function () {
	
		if (req.readyState == 4){
			var strListResponse = req.responseText;
			objObject.dom = new pdDom();
			objObject.dom.loadXML(strListResponse);
			
			objObject.xml = new pdXpathNodeList(objObject.dom.Dom,"/shoc:API/shoc:Object", objObject.dom.Dom.documentElement, nsArray).iterateNext();
			
			objObject.id = objObject.xml.getAttribute("id");
			objObject.idClass = objObject.xml.getAttribute("idClass");

			objObject.Name = pdXmlElementValue(new pdXpathNodeList(objObject.dom.Dom,"shoc:Name", objObject.xml, nsArray).iterateNext());
			objObject.Label = pdXmlElementValue(new pdXpathNodeList(objObject.dom.Dom,"shoc:Label", objObject.xml, nsArray).iterateNext());

			objObject.complete = true;
			
		}
	}
	req.send(null);
	
	return;
	
};



var clsShocSubjects = function(){

	this.URIs = [];
	this.Items = [];
			
	return;
	
};

clsShocSubjects.prototype.make = function(dom){

	var objSubjects = this;
	this.dom = dom;
	this.xml = dom.Dom.documentElement;
	
	var xpathSubjects = new pdXpathNodeList(objSubjects.dom.Dom,"/shoc:API/rdf:RDF/*[@rdf:about]", objSubjects.xml, nsArray);
	var xmlSubject = xpathSubjects.iterateNext();
	
	while (xmlSubject){
		var objSubject = new clsShocSubject();
		objSubject.make(objSubjects.dom,xmlSubject);
		objSubjects.Items.push(objSubject);
		xmlSubject = xpathSubjects.iterateNext();
	}
	
};


clsShocSubjects.prototype.get = function(){

	var objSubjects = this;
	
	var numURIs = objSubjects.URIs.length;
	for (var i = 0; i < numURIs; i++) {
		var objSubject = new clsShocSubject(objSubjects.URIs[i]);
		objSubjects.Items.push(objSubject);
	}
	
	
	objSubjects.loopSubject = function(){
		var AllComplete = true;
		var numItems = objSubjects.Items.length;
		for (var i = 0; i < numItems; i++) {
			if (!objSubjects.Items[i].complete){
				AllComplete = false;
			}
		}
		if (AllComplete === true){
			objSubjects.complete = true;
		}
		else
		{
			setTimeout(objSubjects.loopSubject,25);				
		}
	};
	objSubjects.loopSubject();

	
};


var clsShocSubject = function(uri){

	var objSubject = this;
	objSubject.complete = false;
	
	uri = (uri === undefined) ? null : uri;
	
	this.Attributes = [];
	this.uri = null;

	if (uri != null){
		
		var url = "uri.php?uri="+uri;
		var req = getRequest();
		req.open("GET",url,true);
		
		try {req.responseType = "msxml-document";} catch(err) {} // Helping IE11
		req.onreadystatechange = function () {
		
			if (req.readyState == 4){
				var strListResponse = req.responseText;
				objSubject.dom = new pdDom();
				objSubject.dom.loadXML(strListResponse);

				var xpathSubject = new pdXpathNodeList(objSubject.dom.Dom,"/rdf:RDF/*[@rdf:about]", objSubject.dom.Dom.documentElement, nsArray);
				var xmlSubject = xpathSubject.iterateNext();
				
				objSubject.make(objSubject.dom, xmlSubject);
				
				objSubject.complete = true;
			}
		}
		req.send(null);
		
	}
	
	return;
	
};


clsShocSubject.prototype.make = function(dom, xml){

	var objSubject = this;
	
	this.dom = dom;
	this.xml = xml;
	
	objSubject.uri = objSubject.xml.getAttribute('rdf:about');
	
	var xpathAttributes = new pdXpathNodeList(objSubject.dom.Dom,"*", objSubject.xml, nsArray);
	var xmlAttribute = xpathAttributes.iterateNext();
	
	while (xmlAttribute){
		
		var objAttribute = new clsShocAttribute();
		objAttribute.make(objSubject.dom,xmlAttribute);
		objSubject.Attributes.push(objAttribute);
		
		xmlAttribute = xpathAttributes.iterateNext();
	}
	
};



var clsShocAttribute = function(){

	this.uriProperty = null;
	this.Value = null;
		
	return;
	
};


clsShocAttribute.prototype.make = function(dom, xml){

	var objAttribute = this;
	
	this.dom = dom;
	this.xml = xml;

	objAttribute.uriProperty = xml.namespaceURI + xml.localName;
	objAttribute.Value = pdXmlElementValue(xml);
	
};




var clsShocForm = function (ElementId, xmlFormId){

	ElementId = (ElementId === undefined) ? null : ElementId;
	xmlFormId = (xmlFormId === undefined) ? null : xmlFormId;

	this.ElementId = ElementId;
	this.xmlFormId = xmlFormId;
	this.tagElement = document.getElementById(this.ElementId);
	if (!this.tagElement){
		alert('no Element');
	}
	
	this.tagXmlForm = document.getElementById(this.xmlFormId);
	if (!this.tagXmlForm){
		alert('no Form');
	}
	this.xmlForm = this.tagXmlForm.value;
		
	this.domForm = new pdDom();
	this.domForm.loadXML(this.xmlForm);

	//	this.idSubject = 0;
	
	this.idNextSubject = 1;
	var xpathSubjectIds = new pdXpathNodeList(this.domForm.Dom,"//shoc:About[@idSubject]", this.domForm.Dom.documentElement, nsArray);
	var xmlSubjectId = xpathSubjectIds.iterateNext();
	while (xmlSubjectId){
		var idSubject = parseInt(xmlSubjectId.getAttribute('idSubject'));
		if ((idSubject + 1) > this.idNextSubject){
			this.idNextSubject = (idSubject + 1);
		}			
		xmlSubjectId = xpathSubjectIds.iterateNext();	
	}

	
	this.Sections= [];
	
	this.make();
	
};


clsShocForm.prototype.makeXml = function(){
	
	objForm = this;	
	objForm.tagXmlForm.value = XmlToString(objForm.domForm.Dom.documentElement);

};



clsShocForm.prototype.make = function(){

	var objForm = this;
	
	objForm.xmlStatements = new pdXpathNodeList(this.domForm.Dom,"shoc:Statements", this.domForm.Dom.documentElement, nsArray).iterateNext();
	objForm.makeSections();

};



clsShocForm.prototype.makeSections = function(tagContainer, objRelationship){	

	var objForm = this;
	
	tagContainer = (tagContainer === undefined) ? null : tagContainer;
	xmlSections = (xmlSections === undefined) ? null : xmlSections;
	objRelationship = (objRelationship === undefined) ? null : objRelationship;

	if (tagContainer == null){
		tagContainer = this.tagElement;
	}
	
	var xmlSections = new pdXpathNodeList(this.domForm.Dom,"shoc:Template/shoc:Sections", this.domForm.Dom.documentElement, nsArray).iterateNext();
	if (xmlSections){
		var divSections = document.createElement('div');
		tagContainer.appendChild(divSections);
		
		if (objRelationship == null){
//			var xpathSection = new pdXpathNodeList(this.domForm.Dom,"shoc:Section[@start='true']", xmlSections, nsArray);
			var xpathSection = new pdXpathNodeList(this.domForm.Dom,"shoc:Section[1]", xmlSections, nsArray);
		}
		else
		{
			var xpathSection = new pdXpathNodeList(this.domForm.Dom,"shoc:Section[@idObject="+objRelationship.idObject+"]", xmlSections, nsArray);
		}
		var xmlSection = xpathSection.iterateNext();
		
		
		while (xmlSection){

			var idObject = xmlSection.getAttribute('idObject');

			var xpathAbout = new pdXpathNodeList(this.domForm.Dom,"shoc:About[@idObject="+idObject+"]", objForm.xmlStatements, nsArray);
			var xmlAbout = xpathAbout.iterateNext();

			var boolAddSection = true;
			
			while (xmlAbout){
				boolAddSection = false;
				objForm.Sections.push(new clsShocFormSection(objForm, xmlSection, divSections, xmlAbout, objRelationship));
				var xmlAbout = xpathAbout.iterateNext();
			}
			
			if (boolAddSection){
				objForm.Sections.push(new clsShocFormSection(objForm, xmlSection, divSections, null, objRelationship));				
			}
			xmlSection = xpathSection.iterateNext();		
		}
	}
};
	

var clsShocFormSection = function (Form, xmlSection, tagContainer, xmlAbout, ParentRelationship, xmlRelationshipStatement){

	xmlSection = (xmlSection === undefined) ? null : xmlSection;
	xmlAbout = (xmlAbout === undefined) ? null : xmlAbout;
	ParentRelationship = (ParentRelationship === undefined) ? null : ParentRelationship;
	xmlRelationshipStatement = (xmlRelationshipStatement === undefined) ? null : xmlRelationshipStatement;

	this.Form = Form;
	this.xmlSection = xmlSection;
	this.tagContainer = tagContainer;
	this.xmlAbout = xmlAbout;
	
	this.Questions = [];
	this.ParentRelationship = ParentRelationship;
	this.xmlRelationshipStatement = xmlRelationshipStatement;
	
	
	this.Relationships = [];
	
	
	this.idSubject = null;
	
	if (xmlAbout != null){
//		if (xmlAbout.hasAttribute('idSubject')){
		if (xmlAbout.getAttribute('idSubject')){		
			this.idSubject = xmlAbout.getAttribute('idSubject');
		}
	}	

	if (this.idSubject == null){
		this.idSubject = this.Form.idNextSubject++;
		if (xmlAbout != null){
			xmlAbout.setAttribute('idSubject',this.idSubject);
		}
	}

	
	this.idObject = xmlSection.getAttribute('idObject');

	var objForm = this.Form;
	var divSection = document.createElement('div');
	divSection.className = "sdbluebox";

	tagContainer.appendChild(divSection);		
	var Prompt = pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Prompt", xmlSection, nsArray).iterateNext());

	var tagPrompt = document.createElement('h1');
	divSection.appendChild(tagPrompt);
	tagPrompt.innerHTML = Prompt;

	var tableSection = document.createElement('table');
	divSection.appendChild(tableSection);
	var xpathQuestion = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Question", xmlSection, nsArray);
	var xmlQuestion = xpathQuestion.iterateNext();
	while (xmlQuestion){
		this.Questions.push(new clsShocFormQuestion(this, xmlQuestion, tableSection));			
		xmlQuestion = xpathQuestion.iterateNext();		
	}
	
	var xpathRelationship = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Template/shoc:Relationships/shoc:Relationship[@idFromObject="+this.idObject+"]", objForm.domForm.Dom.documentElement, nsArray);

//	var xpathRelationship = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Template/shoc:Relationships/shoc:Relationship[@idToObject="+this.idObject+" and @inverse='true']", objForm.domForm.Dom.documentElement, nsArray);

	var xmlRelationship = xpathRelationship.iterateNext();	
	while (xmlRelationship){
		this.Relationships.push(new clsShocFormRelationship(this, xmlRelationship, tableSection));					
		xmlRelationship = xpathRelationship.iterateNext();		
	}

	
};




clsShocFormSection.prototype.getXmlAbout = function(){	

	var objSection = this;
	if (objSection.xmlAbout !== null){
		return objSection.xmlAbout;
	}

	var objForm = objSection.Form;
	var idSubject = objSection.idSubject;
	var idObject = objSection.idObject;
	
	var xmlAbout = new pdXpathNodeList(objForm.domForm.Dom,"shoc:About[@idSubject='"+idSubject+"' and @idObject='"+idObject+"']", objForm.xmlStatements, nsArray).iterateNext();
	if (!xmlAbout){
		var xmlAbout = objForm.domForm.createElementNS(nsSHOC,'About');
		xmlAbout.setAttribute('idSubject',idSubject);
		xmlAbout.setAttribute('idObject',idObject);
		objForm.xmlStatements.appendChild(xmlAbout);
		objSection.xmlAbout = xmlAbout;
	}

	return objSection.xmlAbout;
	
};


var clsShocFormQuestion = function (Section, xmlQuestion, tableSection, ParentParts, xmlParent){

	ParentParts = (ParentParts === undefined) ? null : ParentParts;
	xmlParent = (xmlParent === undefined) ? Section.xmlAbout : xmlParent;
	
	this.Section = Section;
	this.ParentParts = ParentParts;
	this.xmlParent = xmlParent;

	var objForm = Section.Form;
	var objQuestion = this;
	
	this.Form = objForm;
	this.xmlQuestion = xmlQuestion;
	
	this.idSubject = null;
	
	this.Fields = [];
	this.Parts = [];
	
	this.Cardinality = xmlQuestion.getAttribute('cardinality');

	
	var rowQuestion = tableSection.insertRow(-1);

	var cellButtons = rowQuestion.insertCell(0);
	
	
	var cellLabel = rowQuestion.insertCell(1);
	cellLabel.innerHTML = '<b>'+ pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Prompt", xmlQuestion, nsArray).iterateNext()) + '</b>';
	var cellField = rowQuestion.insertCell(2);

	this.idField = xmlQuestion.getAttribute('idField');
	
	
	var xpathResponse = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Responses/shoc:Response", xmlQuestion, nsArray);
	var xmlResponse = xpathResponse.iterateNext();

	var xpathParts = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Parts", xmlQuestion, nsArray);
	var xmlParts = xpathParts.iterateNext();

	
	var boolAddQuestion = true;
	
	if (objQuestion.xmlParent){
		var xpathStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idField="+this.idField+"]", objQuestion.xmlParent, nsArray);
		var xmlStatement = xpathStatement.iterateNext();

		while (xmlStatement){
			boolAddQuestion = false;
			
			if (xmlResponse){
				var Field = new clsShocFormField(this, xmlResponse, cellField, xmlStatement);
				this.Fields.push(Field);										
			}
			
			if (xmlParts){
				var Parts = new clsShocFormParts(this, xmlParts, cellField, xmlStatement);
				this.Parts.push(Parts);								
			}
			
			var xmlStatement = xpathStatement.iterateNext();
		}			
	}

	if (boolAddQuestion){
		if (xmlResponse){
			var Field = new clsShocFormField(this, xmlResponse, cellField);
			this.Fields.push(Field);
		}
		
		if (xmlParts){
			var Parts = new clsShocFormParts(this, xmlParts, cellField);
			this.Parts.push(Parts);								
		}

	}
	

};




var clsShocFormParts = function (ParentQuestion, xmlParts, tagContainer, xmlStatement){

	xmlParts = (xmlParts === undefined) ? null : xmlParts;
	xmlStatement = (xmlStatement === undefined) ? null : xmlStatement;
	ParentQuestion = (ParentQuestion === undefined) ? null : ParentQuestion;

	this.Form = ParentQuestion.Form;
	this.xmlParts = xmlParts;
	this.tagContainer = tagContainer;
	this.xmlStatement = xmlStatement;
	
	this.Questions = [];
	this.ParentQuestion = ParentQuestion;
		
	var objForm = this.Form;
	var divParts = document.createElement('div');
	divParts.className = "sdbluebox";
	tagContainer.appendChild(divParts);		

	var tableParts = document.createElement('table');
	divParts.appendChild(tableParts);
		
	var xpathQuestion = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Question", xmlParts, nsArray);
	var xmlQuestion = xpathQuestion.iterateNext();
	while (xmlQuestion){
		this.Questions.push(new clsShocFormQuestion(ParentQuestion.Section, xmlQuestion, tableParts, this, xmlStatement));			
		xmlQuestion = xpathQuestion.iterateNext();		
	}
	
};



clsShocFormParts.prototype.getXmlStatement = function(){	

	var objParts = this;

	if (objParts.xmlStatement !== null){
		return objParts.xmlStatement;
	}

	var objForm = objParts.Form;

	var xmlParent = null;
	if (objParts.ParentQuestion.ParentParts !== null){
		xmlParent = objParts.ParentQuestion.ParentParts.getXmlStatement();
	}
	else
	{
		xmlParent = objParts.ParentQuestion.Section.getXmlAbout();		
	}
	
	objParts.xmlStatement = objForm.domForm.createElementNS(nsSHOC,'Statement');
	objParts.xmlStatement.setAttribute('idField',objParts.ParentQuestion.idField);
	xmlParent.appendChild(objParts.xmlStatement);

		
	return objParts.xmlStatement;
	
};






var clsShocFormField = function (Question, xmlResponse, tagContainer, xmlStatement){

	xmlStatement = (xmlStatement === undefined) ? null : xmlStatement;
	
	var objQuestion = Question;
	var objForm = Question.Form;
	var objField = this;

	this.Question = objQuestion;
	this.xmlResponse = xmlResponse;
	this.xmlStatement = xmlStatement;
	this.Form = objForm;

	this.Div = document.createElement('div');
	tagContainer.appendChild(this.Div);
	
	this.uriStatement = null;
	this.Value = '';

	var Value = '';
	if (xmlStatement !== null){		
		this.Value = pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Value", xmlStatement, nsArray).iterateNext());
		this.uriStatement = xmlStatement.getAttribute('uri');
	}
	
	var xpathTerm = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Lists/shoc:List/shoc:Term", xmlResponse, nsArray);
	if (xpathTerm.NumberOfRows > 0){
		var tagField = document.createElement('select');

		var tagOption = document.createElement('option');
		tagField.appendChild(tagOption);
		
		var xmlTerm = xpathTerm.iterateNext();
		while (xmlTerm){
			var tagOption = document.createElement('option');
			
			tagOption.value = xmlTerm.getAttribute('id');
			
			var Label = pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Label", xmlTerm, nsArray).iterateNext());
			
			tagOption.innerHTML = Label;
			tagField.appendChild(tagOption);
			
			if (tagOption.value == this.Value){
				tagOption.selected = 'selected';
			}

			xmlTerm = xpathTerm.iterateNext();		
		}
	}
	else
	{
		var DataType = pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:DataType", xmlResponse, nsArray).iterateNext())
		var MaxLength = xmlResponse.getAttribute('maxLength');
		if (!MaxLength){
			MaxLength = null;
		}
			
		var FieldType = 'input';
		switch (DataType){
		case 'date':
			if (MaxLength == null){
				MaxLength = 10;
			}
			break;
		case 'integer':
			if (MaxLength == null){
				MaxLength = 10;
			}
			break;
		case 'decimal':
			if (MaxLength == null){
				MaxLength = 10;
			}
			break;
		case 'memo':
		case 'text':
			var FieldType = 'textarea';
			break;

		default:
			if (MaxLength == null){
				MaxLength = 40;
			}
			break;
		}
		
		
		switch (FieldType){
		case 'textarea':
			var tagField = document.createElement('textarea');
			tagField.cols = 80;
			tagField.rows = 6;
			tagField.innerHTML = this.Value;

			break;
		default:
			var tagField = document.createElement('input');
			tagField.maxlength = MaxLength;
			tagField.size = MaxLength;
			tagField.value = this.Value;
			break;
		}
	}
		
	tagField.id = this.FieldPrefix;
	
	this.tagField = tagField;
	
	tagField.onblur = function(){
		objField.updateXml(tagField.value);
	};
		
	this.Div.appendChild(tagField);
	
	
	
//	var cellButtons = rowQuestion.insertCell(0);
	if (this.Question.Cardinality == 'many'){
		var btnAdd = document.createElement('input');
		this.Div.appendChild(btnAdd);
		btnAdd.type = 'submit';
		btnAdd.value = "+";	
		
		btnAdd.onclick = function(){			
			Question.Fields.push(new clsShocFormField(Question, xmlResponse, tagContainer));
		};
		
		
		var btnDel = document.createElement('input');
		this.Div.appendChild(btnDel);
		btnDel.type = 'submit';
		btnDel.value = "x";	
		
		btnDel.onclick = function(){
			objField.Delete();
		};

		
	}

//	tagContainer.appendChild(document.createElement('br'));


};


clsShocFormField.prototype.Delete = function(){	

	var objField = this;
	
	objField.Div.parentNode.removeChild(objField.Div);
	
	if (objField.xmlStatement != null){
		objField.xmlStatement.parentNode.removeChild(objField.xmlStatement);
	}
	
	return;
}

clsShocFormField.prototype.updateXml = function(Value){	

	var objField = this;
	var objForm = this.Form;
	
	var idSubject = objField.Question.Section.idSubject;
	var idObject = objField.Question.Section.idObject;	
	
	if (objField.Question.Section.ParentRelationship != null){

		if (objField.Question.Section.xmlRelationshipStatement == null){

			var idParentSubject = objField.Question.Section.ParentRelationship.Section.idSubject;
			var xmlParentAbout = new pdXpathNodeList(objForm.domForm.Dom,"shoc:About[@idSubject='"+idParentSubject+"']", objForm.xmlStatements, nsArray).iterateNext();
			if (xmlParentAbout){
				var idRelationship = objField.Question.Section.ParentRelationship.Id;
				var xmlStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idRelationship='"+idRelationship+"' and @idSubject='"+idSubject+"']", xmlParentAbout, nsArray).iterateNext();
				if (!xmlStatement){
					xmlStatement = objForm.domForm.createElementNS(nsSHOC,'Statement');
					xmlStatement.setAttribute('idRelationship',idRelationship);
					xmlStatement.setAttribute('idLinkSubject',idSubject);
					xmlParentAbout.appendChild(xmlStatement);

					objField.Question.Section.xmlRelationshipStatement = xmlStatement;

				}
			}
		}
	}

	objField.getXmlStatement();
		
	objField.xmlValue = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Value", objField.xmlStatement, nsArray).iterateNext();
	if (objField.xmlValue == null){
		objField.xmlValue = objForm.domForm.createElementNS(nsSHOC,'Value');
		objField.xmlStatement.appendChild(objField.xmlValue);
	}


	try {
		if (objField.xmlValue.childNodes.length < 1){
			var t = objForm.domForm.Dom.createTextNode("");
			objField.xmlValue.appendChild(t);
		}
	}
	catch(err) {
	    alert(err.message);
	}
		
	objField.xmlValue.childNodes[0].nodeValue = Value;	
};


clsShocFormField.prototype.getXmlStatement = function(){	

	var objField = this;
	var objForm = this.Form;

	if (objField.xmlStatement !== null){
		return objField.xmlStatement;
	}

	objField.xmlStatement = objForm.domForm.createElementNS(nsSHOC,'Statement');

	
	
	
	objField.xmlStatement.setAttribute('idField',objField.Question.idField);
	
	if (objField.Question.ParentParts !== null){
		var objParts = objField.Question.ParentParts;
		objParts.getXmlStatement();
		objParts.xmlStatement.appendChild(objField.xmlStatement);
	}
	else
	{
		var objSection = objField.Question.Section;
		objSection.getXmlAbout();
		objSection.xmlAbout.appendChild(objField.xmlStatement);
	}
	
	return objField.xmlStatement;

	
};





var clsShocFormRelationship = function (Section, xmlRelationship, tableSection){

	this.Section = Section;
	this.tableSection = tableSection;
	var objForm = Section.Form;
	var objRelationship = this;
	
	this.Form = objForm;
	this.xmlRelationship = xmlRelationship;
	
	this.Id = xmlRelationship.getAttribute("id");

	this.Links = [];
	var boolAddLink = true;

	
	this.inverse = false;
	if (xmlRelationship.getAttribute("inverse") == "true"){
		this.inverse = true;
	}	
	
	this.Extending = false;
	if (xmlRelationship.getAttribute("extending") == "true"){
		this.Extending = true;
	}	

	
	
	this.idToObject = xmlRelationship.getAttribute("idToObject");
	
	if (objRelationship.Extending){
		var xmlSections = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Template/shoc:Sections", objForm.domForm.Dom.documentElement, nsArray).iterateNext();
		if (xmlSections){
			var xpathSection = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Section[@idObject="+this.idToObject+"]", xmlSections, nsArray);
			var xmlLinkSection = xpathSection.iterateNext();
			if (xmlLinkSection){
	
				var rowRelationship = tableSection.insertRow(-1);
				
				var cellButtons = rowRelationship.insertCell(0);
				this.Cardinality = xmlRelationship.getAttribute('cardinality');
				
				if (this.Cardinality == 'many'){
					var btnAdd = document.createElement('input');
					cellButtons.appendChild(btnAdd);
					btnAdd.type = 'submit';
					btnAdd.value = "+";
										
					btnAdd.onclick = function(){
						objRelationship.Links.push(new clsShocFormSection(objForm, xmlLinkSection, cellField, null, objRelationship));
					};
	
					
				}

				var cellLabel = rowRelationship.insertCell(1);
				cellLabel.innerHTML = '<b>'+ pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Prompt", xmlRelationship, nsArray).iterateNext()) + '</b>';
				
				var cellField = rowRelationship.insertCell(2);
		
	
		
				if (Section.xmlAbout){
	
					var xpathStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idRelationship="+this.Id+"]", Section.xmlAbout, nsArray);
					var xmlStatement = xpathStatement.iterateNext();
	
					while (xmlStatement){
	
						var uriLinkSubject = xmlStatement.getAttribute("uriLinkSubject");
						if (uriLinkSubject){
							
							var xpathLinkAbout = new pdXpathNodeList(objForm.domForm.Dom,"shoc:About[@idObject="+this.idToObject+" and @uriSubject='"+uriLinkSubject+"']", objForm.xmlStatements, nsArray);
							var xmlLinkAbout = xpathLinkAbout.iterateNext();
							
							if (xmlLinkAbout){							
								boolAddLink = false;
								var Link = new clsShocFormSection(objForm, xmlLinkSection, cellField, xmlLinkAbout, this, xmlStatement);
								this.Links.push(Link);							
							}
						}
						var xmlStatement = xpathStatement.iterateNext();
					}	
					
				}
	
				if (boolAddLink){
					var Link = new clsShocFormSection(objForm, xmlLinkSection, cellField, null, this);
					this.Links.push(Link);
				}
	
			}
		}
	}
	else
	{
		
		objRelationship.objObject = new clsShocObject(objRelationship.idToObject);
		objRelationship.loopObject = function(){
			if (objRelationship.objObject.complete === true ){
				objRelationship.idClass = objRelationship.objObject.idClass;
				objRelationship.Class()
			}
			else
			{
				setTimeout(objRelationship.loopObject,25);				
			}
		};
		objRelationship.loopObject();
		
	}
};


clsShocFormRelationship.prototype.Class = function(){

	var objRelationship = this;
	
	if (objRelationship.idClass == null){
		return;
	}

	objRelationship.objClass = new clsShocClass(objRelationship.idClass);
	objRelationship.loopClass = function(){
		if (objRelationship.objClass.complete === true ){
			objRelationship.Link();
		}
		else
		{
			setTimeout(objRelationship.loopClass,25);				
		}
	};
	objRelationship.loopClass();

	return;
};		




clsShocFormRelationship.prototype.Link = function(){

	var objRelationship = this;
	var tableSection = this.tableSection;
	var xmlRelationship = this.xmlRelationship;
	var Section = this.Section;
	var objForm = Section.Form;
	var objObject = objRelationship.objObject;
	
	
	var rowRelationship = tableSection.insertRow(-1);
	var cellButtons = rowRelationship.insertCell(0);
	var cellLabel = rowRelationship.insertCell(1);
	cellLabel.innerHTML = '<b>'+ pdXmlElementValue(new pdXpathNodeList(objForm.domForm.Dom,"shoc:Prompt", xmlRelationship, nsArray).iterateNext()) + '</b>';
	var cellField = rowRelationship.insertCell(2);

	var tabstripOptions = new pdTabStrip(cellField);
	objRelationship.tabLinks = tabstripOptions.addTab("Links");
	objRelationship.tabSelect = tabstripOptions.addTab("Select");
	objRelationship.tabCreate = tabstripOptions.addTab("Create");

	objRelationship.LinkExisting();
	objRelationship.LinkSelect();
	objRelationship.LinkCreate();
};

clsShocFormRelationship.prototype.LinkExisting = function(){

	var objRelationship = this;
	var Section = objRelationship.Section;
	var objForm = Section.Form;
	
	objRelationship.tabLinks.div.innerHTML = "";
	
// get existing links		
	if (Section.xmlAbout){
		
		var xpathStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idRelationship="+this.Id+"]", Section.xmlAbout, nsArray);
		var xmlStatement = xpathStatement.iterateNext();
		
		var shocSubjectList = new clsShocSubjectList(objRelationship.tabLinks.div);
		shocSubjectList.objClass = objRelationship.objClass;

		while (xmlStatement){

			var uriLinkSubject = xmlStatement.getAttribute("uriLinkSubject");
			if (uriLinkSubject){
				shocSubjectList.URIs.push(uriLinkSubject);
			}
			var xmlStatement = xpathStatement.iterateNext();
		}
		shocSubjectList.deleteObject = objRelationship;
		shocSubjectList.getURIs();			
	}
};

clsShocFormRelationship.prototype.LinkSelect = function(){

	var objRelationship = this;
		
// get a list of subjects for the class to select from		
	var shocSubjectList = new clsShocSubjectList(objRelationship.tabSelect.div);
	shocSubjectList.objClass = objRelationship.objClass;
	shocSubjectList.addObject = objRelationship;
	shocSubjectList.build();
};		


clsShocFormRelationship.prototype.LinkCreate = function(){

	var objRelationship = this;
	var Section = objRelationship.Section;
	var objForm = Section.Form;

	var xmlSections = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Template/shoc:Sections", objForm.domForm.Dom.documentElement, nsArray).iterateNext();
	if (xmlSections){
		var xpathSection = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Section[@idObject="+this.idToObject+"]", xmlSections, nsArray);
		var xmlLinkSection = xpathSection.iterateNext();
		if (xmlLinkSection){
			var Link = new clsShocFormSection(objForm, xmlLinkSection, objRelationship.tabCreate.div, null, objRelationship);
			objRelationship.Links.push(Link);
		}
	}

};


clsShocFormRelationship.prototype.addSubject = function(uriLinkSubject){

	var objRelationship = this;
	var objForm = this.Form;
	

	var idParentSubject = objRelationship.Section.idSubject;
	var xmlParentAbout = new pdXpathNodeList(objForm.domForm.Dom,"shoc:About[@idSubject='"+idParentSubject+"']", objForm.xmlStatements, nsArray).iterateNext();
	if (xmlParentAbout){
		var idRelationship = objRelationship.Id;
		var xmlStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idRelationship='"+idRelationship+"' and @uriLinkSubject='"+uriLinkSubject+"']", xmlParentAbout, nsArray).iterateNext();
		if (!xmlStatement){
			xmlStatement = objForm.domForm.createElementNS(nsSHOC,'Statement');
			xmlStatement.setAttribute('idRelationship',idRelationship);
			xmlStatement.setAttribute('uriLinkSubject',uriLinkSubject);
			xmlParentAbout.appendChild(xmlStatement);

			objRelationship.xmlStatement = xmlStatement;

		}
	}
	
};


clsShocFormRelationship.prototype.deleteSubject = function(uriLinkSubject){

	var objRelationship = this;
	var objForm = this.Form;
	

	var idParentSubject = objRelationship.Section.idSubject;
	var xmlParentAbout = new pdXpathNodeList(objForm.domForm.Dom,"shoc:About[@idSubject='"+idParentSubject+"']", objForm.xmlStatements, nsArray).iterateNext();
	if (xmlParentAbout){
		var idRelationship = objRelationship.Id;
		var xmlStatement = new pdXpathNodeList(objForm.domForm.Dom,"shoc:Statement[@idRelationship='"+idRelationship+"' and @uriLinkSubject='"+uriLinkSubject+"']", xmlParentAbout, nsArray).iterateNext();
		if (xmlStatement){
			xmlParentAbout.removeChild(xmlStatement);			
		}
	}
	
};



var clsShocSubjectList = function (tagElement, tagLoading){

	this.objClass = null;
	this.URIs = [];
		
	this.complete = false;
	
	this.tagElement = null;
	this.spanLoading = null;
	
	this.RowsPerPage = 30;	
	this.numRows = 0;
	
	this.addObject = null;
	this.deleteObject = null;
	
	var objList = this;
	
	this.tagElement = (tagElement === undefined) ? null : tagElement;
	this.tagLoading = (tagLoading === undefined) ? this.tagElement : tagLoading;
	
};


clsShocSubjectList.prototype.build = function(){

	var objSubjectList = this;
	
	var url = "api";
	if (objSubjectList.objClass != null){
		url += "/classid/" + objSubjectList.objClass.id;
	}
	url += "/subjects";
	var reqList = getRequest();
	reqList.open("GET",url,true);
	
	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11
	reqList.onreadystatechange = function () {
	
		if (reqList.readyState == 4){
			var strListResponse = reqList.responseText;
			objSubjectList.dom = new pdDom();
			objSubjectList.dom.loadXML(strListResponse);
			
			objSubjectList.objSubjects = new clsShocSubjects();
			objSubjectList.objSubjects.make(objSubjectList.dom);
			
			objSubjectList.render();
		}
	}
	reqList.send(null);

};



clsShocSubjectList.prototype.getURIs = function(){

	var objSubjectList = this;

	objSubjectList.objSubjects = new clsShocSubjects();
	objSubjectList.objSubjects.URIs = objSubjectList.URIs;
	objSubjectList.objSubjects.get();

	objSubjectList.loopSubjects = function(){
		if (objSubjectList.objSubjects.complete === true ){
			objSubjectList.render();
		}
		else
		{
			setTimeout(objSubjectList.loopSubjects,25);				
		}
	};
	objSubjectList.loopSubjects();

	return;
};		



clsShocSubjectList.prototype.render = function(){

	var objSubjectList = this;

//	if (objSubjectList.dom == null){
//		return;
//	}
	
	this.tableList = document.createElement('table');
	objSubjectList.tagElement.appendChild(this.tableList);

	objSubjectList.headings();
	
	var numSubjects = objSubjectList.objSubjects.Items.length;
	for (var i = 0; i < numSubjects; i++) {

		var objSubject = objSubjectList.objSubjects.Items[i];

		var rowSubject = objSubjectList.tableList.insertRow(-1);

		var cellButtons = rowSubject.insertCell(-1);
		
		if (this.addObject != null){			
			var btnAdd = document.createElement('input');
			cellButtons.appendChild(btnAdd);
			btnAdd.type = 'submit';
			btnAdd.value = "+";	
			
			btnAdd.uri = objSubject.uri;
			
			btnAdd.onclick = function(){
				var btnAdd = this;
				objSubjectList.addObject.addSubject(btnAdd.uri);
				objSubjectList.addObject.tabLinks.Select();
				objSubjectList.addObject.LinkExisting();
			};
		}

		if (this.deleteObject != null){			
			var btnDelete = document.createElement('input');
			cellButtons.appendChild(btnDelete);
			btnDelete.type = 'submit';
			btnDelete.value = "x";	
			
			btnDelete.uri = objSubject.uri;
			
			btnDelete.onclick = function(){
				var btnDelete = this;
				objSubjectList.deleteObject.deleteSubject(btnDelete.uri);
				objSubjectList.deleteObject.tabLinks.Select();
				objSubjectList.deleteObject.LinkExisting();
			};
		}

		
		var cellClass = rowSubject.insertCell(-1);		
		cellClass.innerHTML += objSubjectList.objClass.Label;

		
		var numProperties = objSubjectList.objClass.Properties.length;
		for (var p = 0; p < numProperties; p++) {
			objProperty = objSubjectList.objClass.Properties[p];
			var tdAttribute = document.createElement("td");
			
			var numAttributes = objSubject.Attributes.length;
			for (var a = 0; a < numAttributes; a++) {
				objAttribute = objSubject.Attributes[a];
				if (objAttribute.uriProperty == objProperty.uri){
					tdAttribute.innerHTML = objAttribute.Value + "<br/>";
				}
			}
			
		    rowSubject.appendChild(tdAttribute);

		}

		
	}

	
	



};



clsShocSubjectList.prototype.headings = function(){

	var objSubjectList = this;
	
	var thead = objSubjectList.tableList.createTHead();
	var rowHeadings = thead.insertRow(-1);

	var thButtons = document.createElement('th');
	rowHeadings.appendChild(thButtons);
	
    var th = document.createElement('th');
    th.innerHTML = "Class";
    rowHeadings.appendChild(th);
	

	var numProperties = objSubjectList.objClass.Properties.length;
	for (var i = 0; i < numProperties; i++) {

		var objProperty = objSubjectList.objClass.Properties[i];

		var thProperty = document.createElement("th");
		thProperty.innerHTML = objProperty.Label;
	    rowHeadings.appendChild(thProperty);

	}
	
	return;

};

/*
var xclsShocLinkObject = function (ArchRelId, FilterPrefix, ElementId, Action){

	this.ArchRelId = null;
	this.FilterPrefix = null;
	this.ElementId = null;
	
	this.LoadingId = null;

	this.ArchRelId = (ArchRelId === undefined) ? null : ArchRelId;
	this.FilterPrefix = (FilterPrefix === undefined) ? null : FilterPrefix;
	this.ElementId = (ElementId === undefined) ? null : ElementId;
	this.Action = (Action === undefined) ? 'link' : Action;

	
	this.LoadingId = this.ElementId;

	this.tagElement = document.getElementById(ElementId);
	var tagElement = this.tagElement;

	this.tagLoading = document.getElementById(this.LoadingId);
	
	
	this.tabstrip = new pdTabStrip(tagElement);
	this.tabSelect = this.tabstrip.addTab("Select");
	this.tabFilter = this.tabstrip.addTab("Filter");

	this.LoadingImage = new clsShocLoadingImage(this.tagLoading);

	
	this.get();
	
};

xclsShocLinkObject.prototype.get = function(){

	var objLinkObject = this;

	this.tagArchRelId = document.getElementById(objLinkObject.ArchRelId);
	var tagArchRelId = this.tagArchRelId;

	var valArchRelId = getElementValue(tagArchRelId);
	

	objLinkObject.LoadingImage.set(true);
	
	
	var url = "apiSubjectList.php?";
	if (gShoc.SessionId != null){
		url += "sid=" +  gShoc.SessionId + "&";
	}	
	url += "archrelid=" +  valArchRelId;
	url += "&action=" +  this.Action;
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
			objLinkObject.LoadingImage.set(false);
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;	
				objLinkObject.tabSelect.div.innerHTML = strListResponse;
				break;
			case 0:
				break;
			default:
				objLinkObject.tabSelect.div.innerHTML = "Error " + reqList.status;
				break;
			}
			
		}
	};
	
	reqList.send(null);
};
*/


var clsShocMakeLink = function ( fldidElement ){

	this.Action = 'select';
	
	this.fldidSelect = null;

	this.uriActivity = null;
	this.ObjectId = null;
	this.ArchRelId = null;
	this.uriFromSubject = null;
	this.FilterPrefix = null;
	this.uriBox = null;
	
	this.fldidElement = (fldidElement === undefined) ? null : fldidElement;

	this.tagElement = document.getElementById(fldidElement);
	
};

clsShocMakeLink.prototype.get = function(){

	var objMakeLink = this;

	objMakeLink.ObjectId = null;
	objMakeLink.ArchRelId = null;

	
	if (objMakeLink.fldidSelect != null){
		this.tagSelect = document.getElementById(objMakeLink.fldidSelect);
		var tagSelect = this.tagSelect;
		
		var SelectParts = getElementValue(tagSelect).split("_");

		for (var i = 0; i < SelectParts.length; i++) {
			switch ( SelectParts[i]){
			case 'select':
			case 'add':
				objMakeLink.Action = SelectParts[i];
				break;
			case 'archrel':
				objMakeLink.ArchRelId = SelectParts[i+1];
				break;
			case 'object':
				objMakeLink.ObjectId = SelectParts[i+1];
				break;
			}
		}
		
	}
	
	switch (objMakeLink.Action){
	case 'select':
		
		objMakeLink.objSelect = new clsShocListSubject( objMakeLink.fldidElement,'link', objMakeLink.FilterPrefix );
		objMakeLink.objSelect.ArchRelId = objMakeLink.ArchRelId;
		objMakeLink.objSelect.uriActivity = objMakeLink.uriActivity;
		objMakeLink.objSelect.reset();
		
		break;
		
		
	case 'add':
		
		this.tagElement.innerHTML = '';
		this.tagElement.style.display = 'block';
		
		this.divDoc = document.createElement('div');
		this.fldidDoc = objMakeLink.fldidElement + '_document';
		this.divDoc.id = this.fldidDoc;
		this.tagElement.appendChild(this.divDoc);

		this.divForm = document.createElement('form');
		this.tagElement.appendChild(this.divForm);
		this.divForm.method = 'post';
		this.divForm.action = 'doForm.php?mode=new';
		if (gShoc.SessionId != null){
			this.divForm.action += "&sid=" +  gShoc.SessionId;
		}

		var fldidXmlForm = 'xmllinkform'+objMakeLink.ObjectId
		
		var tagXmlTemplate = document.getElementById('xmltemplateform'+objMakeLink.ObjectId);
		if (tagXmlTemplate){
			var xmlTemplate = getElementValue(tagXmlTemplate);
			var tagXmlForm = document.createElement('input');
			tagXmlForm.type = 'hidden';
			tagXmlForm.name = 'xmlform';
			tagXmlForm.id = fldidXmlForm;
			tagXmlForm.value = xmlTemplate;
			this.divForm.appendChild(tagXmlForm);
		}

		var tagUriBox = document.createElement('input');
		tagUriBox.type = 'hidden';
		tagUriBox.name = 'uribox';
		tagUriBox.value = objMakeLink.uriBox;
		this.divForm.appendChild(tagUriBox);
		
		var tagObjectId = document.createElement('input');
		tagObjectId.type = 'hidden';
		tagObjectId.name = 'objectid';
		tagObjectId.value = objMakeLink.ObjectId;
		this.divForm.appendChild(tagObjectId);

		var tagArchRelId = document.createElement('input');
		tagArchRelId.type = 'hidden';
		tagArchRelId.name = 'archrelid';
		tagArchRelId.value = objMakeLink.ArchRelId;
		this.divForm.appendChild(tagArchRelId);

		
		var tagUriFromSubject = document.createElement('input');
		tagUriFromSubject.type = 'hidden';
		tagUriFromSubject.name = 'urifromsubject';
		tagUriFromSubject.value = objMakeLink.uriFromSubject;
		this.divForm.appendChild(tagUriFromSubject);
		
		var tagExtending = document.createElement('input');
		tagExtending.type = 'hidden';
		tagExtending.name = 'extending';
		tagExtending.value = 'true';
		this.divForm.appendChild(tagExtending);

		
		
		var btnSubmit = document.createElement('input');
		btnSubmit.type = 'submit';
		btnSubmit.onclick = function(){			
			objMakeLink.objForm.makeXml();
		};
		btnSubmit.value = 'Create New Revision';
		this.divForm.appendChild(btnSubmit);
		
		
		
		objMakeLink.objForm = new clsShocForm(this.fldidDoc, fldidXmlForm);
		break;

	}

	
	
};




var clsShocListSubject = function ( fldidElement, Action, FilterPrefix){

	this.ObjectId = null;
	this.uriActivity = null;
	this.ArchRelId = null;	
	this.uriLinkSubject = null;
	this.RelId = null;
	this.Inverse = false;
		
	this.FilterPrefix = null;
	this.fldidElement = null;
	this.fldidObjectId = null;
	this.fldidArchRelId = null;
	
	this.fldidLinksRel = null;
		
	this.LoadingId = null;
	this.fldidCount = null;

	this.FilterPrefix = (FilterPrefix === undefined) ? 'filter' : FilterPrefix;
	this.fldidElement = (fldidElement === undefined) ? null : fldidElement;
	this.Action = (Action === undefined) ? null : Action;

	
	this.fldidLoading = this.fldidElement;

	this.tagElement = document.getElementById(fldidElement);
	var tagElement = this.tagElement;
	tagElement.innerHTML = '';

	this.tagLoading = document.getElementById(this.fldidLoading);
	
	this.tagCount = null;
	
	
	this.tabstrip = new pdTabStrip(tagElement);
	this.tabSelect = this.tabstrip.addTab("Select");
	this.tabFilter = this.tabstrip.addTab("Filter");
	this.tabExport = this.tabstrip.addTab("Export");
	
	this.FiltersSet = false;
	this.ExportSet = false;

	this.LoadingImage = new clsShocLoadingImage(this.tagLoading);
	
	this.tagElement.style.display = 'none';

	
};

clsShocListSubject.prototype.reset = function(){
	
	this.FiltersSet = false;
	this.get();
	
};

clsShocListSubject.prototype.showhide = function(){

	var objListSubject = this;

	boolShow = true;
	
	switch (objListSubject.Action){
	case 'subjectlink':
		if (!this.RelId){
			boolShow = false;
		}
		break;
	case 'link':
	case 'boxlink':
		if (!this.ArchRelId){
			boolShow = false;
		}
		break;
	default:
		if (!this.ObjectId){
			boolShow = false;
		}
		break;	
	} 
	

	if (boolShow){
		this.tagElement.style.display = 'block';
	}
	else
	{
		this.tagElement.style.display = 'none';
	}

};


clsShocListSubject.prototype.get = function(Format){
	var objListSubject = this;
	
	Format = (Format === undefined) ? 'html' : Format;
	
	if (objListSubject.fldidObjectId != null){
		this.tagObjectId = document.getElementById(objListSubject.fldidObjectId);
		var tagObjectId = this.tagObjectId;
		objListSubject.ObjectId = getElementValue(tagObjectId);
	}

	if (objListSubject.fldidArchRelId != null){
		this.tagArchRelId = document.getElementById(objListSubject.fldidArchRelId);
		var tagArchRelId = this.tagArchRelId;
		objListSubject.ArchRelId = getElementValue(tagArchRelId);
	}

	
	if (objListSubject.fldidListLinksRel != null){
		
		this.Inverse = false;
		this.RelId = null;
		this.ObjectId = null;
		
		this.tagListLinksRel = document.getElementById(objListSubject.fldidListLinksRel);
		
		var Parts = getElementValue(this.tagListLinksRel).split("_");

		for (var i = 0; i < Parts.length; i++) {
			switch ( Parts[i]){
			case 'relid':
				this.RelId = Parts[i+1];
				break;
			case 'inverse':
				this.Inverse = true;
				break;
			case 'objectid':
				this.ObjectId = Parts[i+1];
				break;
			}
		}	
	}

	
	objListSubject.showhide();
	
	objListSubject.filters();
	var FilterParams = '';		
	var Elements = document.getElementsByTagName('input');
	FilterParams = objListSubject.makeFilterParams(Elements, FilterParams);
	var Elements = document.getElementsByTagName('select');
	FilterParams = objListSubject.makeFilterParams(Elements, FilterParams);

	objListSubject.export();
	
	objListSubject.LoadingImage.set(true);

	
	if (objListSubject.fldidCount){
		objListSubject.tagCount = document.getElementById(objListSubject.fldidCount);		
	}
	
	var url = "apiSubjectList.php?";
	var boolAnd = false;
	if (gShoc.SessionId != null){
		url += "sid=" +  gShoc.SessionId;
		boolAnd = true;
	}
	
	if (objListSubject.uriActivity != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "uriactivity=" +  objListSubject.uriActivity;	
		boolAnd = true;		
	}

	
	if (objListSubject.ObjectId != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "objectid=" +  objListSubject.ObjectId;	
		boolAnd = true;		
	}
	
	if (objListSubject.ArchRelId != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "archrelid=" +  objListSubject.ArchRelId;	
		boolAnd = true;		
	}

	if (objListSubject.Inverse){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "inverse=true";	
		boolAnd = true;		
	}
	
	if ( objListSubject.uriLinkSubject != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "urilinksubject=" + objListSubject.uriLinkSubject;		
		boolAnd = true;	
	}

	
	if (objListSubject.RelId != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "relid=" +  objListSubject.RelId;	
		boolAnd = true;		
	}

	if ( objListSubject.uriBox != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "uribox=" + objListSubject.uriBox;		
		boolAnd = true;	
	}
	
	if (this.Action != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "action=" +  this.Action;
		boolAnd = true;	
	}

	if (FilterParams){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += FilterParams;		
		if (this.FilterPrefix){
			url += "&filterprefix=" +  this.FilterPrefix;
		}
		boolAnd = true;
	}
	if (boolAnd){
		url+= '&';
		boolAnd = false;
	}
	url += "accept=json&format="+Format;
// alert(url);
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
			objListSubject.LoadingImage.set(false);
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;
//alert(reqList.responseText);
				var objResponse = JSON.parse(strListResponse);

				switch (Format){
				case 'html':
					if (objResponse.hasOwnProperty('html')){
						objListSubject.tabSelect.div.innerHTML = objResponse["html"];					
					}

					if (objResponse.hasOwnProperty('count')){
						if (objListSubject.tagCount){
							objListSubject.tagCount.innerHTML = objResponse["count"];					
						}
						else
						{
							objListSubject.tabSelect.tagA.innerHTML = "Select("+objResponse["count"]+")";
						}
					}
					break;
				case 'csv':
					if (objResponse.hasOwnProperty('csv')){
						if (window.navigator.msSaveOrOpenBlob) { // for IE and Edge
							var blob = new Blob([objResponse["csv"]],{type:'text/csv;charset=utf-8;'});
							navigator.msSaveOrOpenBlob(blob,'subjects.csv');
				        } 
						else
						{
							var csv = 'data:text/csv;charset=utf-8,' + objResponse["csv"];
							csvdata = encodeURI(csv);
					        link = document.createElement('a');
					        link.setAttribute('href', csvdata);
					        link.setAttribute('download', 'subjects.csv');
					        link.click();
						}
					}
					
					break;
				}
							
// alert(objListSubject.tagElement.innerHTML);
				
				break;
			case 0:
				break;

			default:
				objLinkObject.tabSelect.div.innerHTML = "Error " + reqList.status;
				break;
			}
			
		}
	};
	
	reqList.send(null);
};


clsShocListSubject.prototype.makeFilterParams = function(Elements, FilterParams){

	var objListSubject = this;

	FilterParams = (FilterParams === undefined) ? '' : FilterParams;

	for (var i = 0; i < Elements.length; i++) {
		if (Elements[i].id.startsWith(objListSubject.FilterPrefix)){
			var value = getElementValue(Elements[i]);
			if (value){
				if (FilterParams){
					FilterParams += '&';
				}
				FilterParams += Elements[i].id + "=" + value;				
			}
		}
	}
	return FilterParams;

}

clsShocListSubject.prototype.filters = function(){

	var objListSubject = this;

	if (objListSubject.FiltersSet){
		return;
	}
	
	objListSubject.FiltersSet = true;
	
	objListSubject.tabFilter.div.innerHTML = '';
	
	var url = "apiSubjectFilters.php?";
	var boolAnd = false;
	if (objListSubject.ObjectId != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "objectid=" +  objListSubject.ObjectId;	
		boolAnd = true;		
	}
	
	if (objListSubject.ArchRelId != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "archrelid=" +  objListSubject.ArchRelId;	
		boolAnd = true;		
	}

	if (objListSubject.FilterPrefix != null){
		if (boolAnd){
			url+= '&';
			boolAnd = false;
		}
		url += "filterprefix=" +  objListSubject.FilterPrefix;	
		boolAnd = true;		
	}
	
//alert(url);	
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;
				
				var divFilters = document.createElement('div');
				objListSubject.tabFilter.div.appendChild(divFilters);
				divFilters.innerHTML = strListResponse;

				var divFilterSubmit = document.createElement('div');
				objListSubject.tabFilter.div.appendChild(divFilterSubmit);

				var btnFilterSubmit = document.createElement('input');
				btnFilterSubmit.type = 'submit';
				btnFilterSubmit.value = 'filter';
				divFilterSubmit.appendChild(btnFilterSubmit);				
				btnFilterSubmit.onclick = function(){					
					objListSubject.get();
				  };

				break;
			case 0:
				break;

			default:
				objLinkObject.tabFilter.div.innerHTML = "Error " + reqList.status;
				break;
			}
			
		}
	};
	
	reqList.send(null);
};



clsShocListSubject.prototype.export = function(){

	var objListSubject = this;

	if (objListSubject.ExportSet){
		return;
	}
	
	objListSubject.ExportSet = true;
	
	var divExport = document.createElement('div');
	objListSubject.tabExport.div.appendChild(divExport);

	var tableExport = document.createElement('table');
	divExport.appendChild(tableExport);
	
	var rowExport = tableExport.insertRow(-1);

	var cellLegend = rowExport.insertCell(-1);
	cellLegend.innerHTML = '<b>Export as</b>';
	
	var cellFormat = rowExport.insertCell(-1);
	objListSubject.selFormat = document.createElement('select');	
	cellFormat.appendChild(objListSubject.selFormat);

	var optFormat = document.createElement('option');	
	objListSubject.selFormat.appendChild(optFormat);

	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'csv';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'odf';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'xlsx';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'xml';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'ttl';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'json';
	objListSubject.selFormat.appendChild(optFormat);
	
	var optFormat = document.createElement('option');
	optFormat.innerHTML = 'sparql';
	objListSubject.selFormat.appendChild(optFormat);
	

	var btnGenerate = document.createElement('input');
	divExport.appendChild(btnGenerate);
	btnGenerate.type = 'submit';
	btnGenerate.value = "Generate";
	btnGenerate.onclick = function(){
		switch (getElementValue(objListSubject.selFormat)){
		case 'csv':
			objListSubject.get('csv');
			break;
		}

	};


};



var clsShocSubjectDot = function (uriSubject, idStyle, idDepth, idFormat, objViz){

	this.uriSubject = (uriSubject === undefined) ? null : uriSubject;
	this.objViz = (objViz === undefined) ? null : objViz;
	this.idStyle = (idStyle === undefined) ? null : idStyle;
	this.idDepth = (idDepth === undefined) ? null : idDepth;
	this.idFormat = (idFormat === undefined) ? null : idFormat;


	this.Style = 1;
	this.depth = 2;
	this.Script = null;
	this.Format = 'image';
	this.Layout = 'dot';
	this.get();
	
};

clsShocSubjectDot.prototype.get = function(){

	var objShocSubjectDot = this;
	
	objShocSubjectDot.objViz.setLoadingImage(true);

	
	if (objShocSubjectDot.idStyle != null){
		objShocSubjectDot.tagStyle = document.getElementById(objShocSubjectDot.idStyle);
		objShocSubjectDot.Style = getElementValue(objShocSubjectDot.tagStyle);
	}

	if (objShocSubjectDot.idDepth != null){
		objShocSubjectDot.tagDepth = document.getElementById(objShocSubjectDot.idDepth);
		objShocSubjectDot.Depth = getElementValue(objShocSubjectDot.tagDepth);
	}


	if (objShocSubjectDot.idFormat != null){
		objShocSubjectDot.tagFormat = document.getElementById(objShocSubjectDot.idFormat);
		objShocSubjectDot.Format = getElementValue(objShocSubjectDot.tagFormat);
	}


	if (objShocSubjectDot.idLayout != null){
		objShocSubjectDot.tagLayout = document.getElementById(objShocSubjectDot.idLayout);
		objShocSubjectDot.Layout = getElementValue(objShocSubjectDot.tagLayout);
	}

	
	var url = "apiSubjectDot.php?";
	if (gShoc.SessionId != null){
		url += "sid=" +  gShoc.SessionId + "&";
	}	
	url += "urisubject=" + this.uriSubject + "&style=" + objShocSubjectDot.Style + "&depth=" + objShocSubjectDot.Depth;
//alert(url);
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
//			objLinkObject.LoadingImage.set(false);
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;	
				objShocSubjectDot.Script = strListResponse;
				
				if (objShocSubjectDot.Layout == 'twopi'){
					objShocSubjectDot.Script = strListResponse.replace("overlap=false;", "overlap=false; ranksep=2.0; ");
				}

				if (objShocSubjectDot.objViz != null){
					objShocSubjectDot.objViz.Dot = objShocSubjectDot.Script;					
					objShocSubjectDot.objViz.show(objShocSubjectDot.Format,objShocSubjectDot.Layout);
				}
				
//				objLinkObject.tabSelect.div.innerHTML = strListResponse;
				break;
			case 0:
				break;

			default:
				alert("Error clsShocSubjectDot " + reqList.status);
//				objLinkObject.tabSelect.div.innerHTML = "Error " + reqList.status;
				break;
			}
			
		}
	};
	
	reqList.send(null);
};


var clsShocLoadingImage = function (tagLoading){

	objLoadingImage = this;
	
	objLoadingImage.boolOn = false;
	objLoadingImage.tagLoading = tagLoading;
	objLoadingImage.spanLoading = null;

};

clsShocLoadingImage.prototype.set = function(boolOn){

	objLoadingImage = this;
	
	boolOn = (boolOn === undefined) ? true : boolOn;
	
	if (boolOn){
		if (objLoadingImage.spanLoading == null){
			objLoadingImage.spanLoading = document.createElement('span');
			objLoadingImage.tagLoading.appendChild(objLoadingImage.spanLoading);
		}
		objLoadingImage.spanLoading.innerHTML = '<img src="images/ajax-loader.gif"/>';		
	}
	else
	{
		if (objLoadingImage.spanLoading !== null){
			objLoadingImage.spanLoading.parentElement.removeChild(objLoadingImage.spanLoading);
			objLoadingImage.spanLoading = null;
		}			
	}
	
	objLoadingImage.boolOn = boolOn;
	
	return;
	
};



//====

var clsShocViewDot = function (idStyle, objViz){

	this.uriActivity = null;
	this.uriBox = null;

	this.objViz = (objViz === undefined) ? null : objViz;
	this.idStyle = (idStyle === undefined) ? null : idStyle;
	this.idLayout = null;
	this.idFormat = null;

	this.Style = 1;
	this.Layout = 'dot';
	this.Format = 'image';
	this.Script = null;
			
};

clsShocViewDot.prototype.get = function(){

	var objShocViewDot = this;
	
	objShocViewDot.objViz.setLoadingImage(true);

	
	if (objShocViewDot.idStyle != null){
		objShocViewDot.tagStyle = document.getElementById(objShocViewDot.idStyle);
		objShocViewDot.Style = getElementValue(objShocViewDot.tagStyle);
	}

	if (objShocViewDot.idFormat != null){
		objShocViewDot.tagFormat = document.getElementById(objShocViewDot.idFormat);
		objShocViewDot.Format = getElementValue(objShocViewDot.tagFormat);
	}

	
	if (objShocViewDot.idLayout != null){
		objShocViewDot.tagLayout = document.getElementById(objShocViewDot.idLayout);
		objShocViewDot.Layout = getElementValue(objShocViewDot.tagLayout);
	}

	var url = "apiViewDot.php?";
	if (gShoc.SessionId != null){
		url += "sid=" +  gShoc.SessionId + "&";
	}	
	url +=  "&style=" + objShocViewDot.Style;
	
	if (objShocViewDot.uriActivity){
		url += "&uriactivity=" + objShocViewDot.uriActivity;		
	}
	if (objShocViewDot.uriBox){
		url += "&uribox=" + objShocViewDot.uriBox;		
	}

//alert(url);
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;	
				objShocViewDot.Script = strListResponse;

				if (objShocViewDot.objViz != null){
					objShocViewDot.objViz.Dot = objShocViewDot.Script;					
//					objShocViewDot.objViz.show();
					objShocViewDot.objViz.show(objShocViewDot.Format,objShocViewDot.Layout);
					
				}
				
				break;
			case 0:
				break;
			default:
				alert("Error clsShocViewDot " + reqList.status);
				break;
			}
			
		}
	};
	
	reqList.send(null);
};

var clsShocBoxDot = function (idStyle, objViz){

	this.uriBox = null;

	this.objViz = (objViz === undefined) ? null : objViz;
	this.idStyle = (idStyle === undefined) ? null : idStyle;

	this.idFormat = null;
	this.idLayout = null;
	
	this.Style = 1;
	this.Script = null;
	this.Format = 'image';
	this.Layout = 'dot';
			
};

clsShocBoxDot.prototype.get = function(){

	var objShocBoxDot = this;
	
	objShocBoxDot.objViz.setLoadingImage(true);

	
	if (objShocBoxDot.idStyle != null){
		objShocBoxDot.tagStyle = document.getElementById(objShocBoxDot.idStyle);
		objShocBoxDot.Style = getElementValue(objShocBoxDot.tagStyle);
	}

	if (objShocBoxDot.idFormat != null){
		objShocBoxDot.tagFormat = document.getElementById(objShocBoxDot.idFormat);
		objShocBoxDot.Format = getElementValue(objShocBoxDot.tagFormat);
	}

	if (objShocBoxDot.idLayout != null){
		objShocBoxDot.tagLayout = document.getElementById(objShocBoxDot.idLayout);
		objShocBoxDot.Layout = getElementValue(objShocBoxDot.tagLayout);
	}

	var url = "apiBoxDot.php?";
	if (gShoc.SessionId != null){
		url += "sid=" +  gShoc.SessionId + "&";
	}	
	url +=  "&style=" + objShocBoxDot.Style;	
	if (objShocBoxDot.uriBox){
		url += "&uribox=" + objShocBoxDot.uriBox;		
	}

//alert(url);
	var reqList = getRequest();
	reqList.open("GET",url,true);

	try {reqList.responseType = "msxml-document";} catch(err) {} // Helping IE11

	reqList.onreadystatechange = function () {

		if (reqList.readyState == 4){
			
			switch (reqList.status){
			case 200:
				var strListResponse = reqList.responseText;	
				objShocBoxDot.Script = strListResponse;

				if (objShocBoxDot.objViz != null){
					objShocBoxDot.objViz.Dot = objShocBoxDot.Script;					
					objShocBoxDot.objViz.show(objShocBoxDot.Format,objShocBoxDot.Layout);
				}
				
				break;
			case 0:
				break;

			default:
				alert("Error clsShocBoxDot " + reqList.status);
				break;
			}
			
		}
	};
	
	reqList.send(null);
};




//====



var clsShocMap = function (arrLayers, ElementId, LoadingId){

	arrLayers = (arrLayers === undefined) ? [] : arrLayers;
	ElementId = (ElementId === undefined) ? null : ElementId;
	LoadingId = (LoadingId === undefined) ? ElementId : LoadingId;

	objMapRenderer = this;

	this.Completed = false;
	this.numPlots = 0;
	
	try{
				
		objMapRenderer.tagElement = document.getElementById(ElementId);
		objMapRenderer.tagLoading = document.getElementById(LoadingId);
		
		objMapRenderer.setLoadingImage(true);
		
		
		objMapRenderer.divContainer = document.createElement('div');
		objMapRenderer.tagElement.appendChild(objMapRenderer.divContainer);
		

		objMapRenderer.objMap = new pdMap(ElementId);

		objMapRenderer.tagMapKey = document.createElement('div');
		objMapRenderer.divContainer.appendChild(objMapRenderer.tagMapKey);

		var numLayers = arrLayers.length;
		for (var i = 0; i < numLayers; i++) {
			var objLayer = arrLayers[i];
			if (objLayer.hasOwnProperty('polygon')){
				var objMapLayer = objMapRenderer.objMap.AddLayer(objMapRenderer.tagMapKey, objLayer.label);
				objMapRenderer.RenderMapArea(objLayer.polygon,objMapLayer);
			}
		}
			
					
		objMapRenderer.objMap.Show();
//		objMapRenderer.objMap.SetUpTab(objMapRenderer.tagLoading);
		objMapRenderer.setLoadingImage(false);
		objMapRenderer.Completed = true;
				
	}
	catch(err){
		alert('Map Renderer:'+err.message);
	}	

};

clsShocMap.prototype.RenderMapPin = function(objSubject,objMapLayer){

	objMapRenderer = this;
		
	var MapLat = null;
	var MapLong = null;
	
	var MapNorthing = null;
	var MapEasting = null;
	
	if (objSubject === null){
		return;
	}

	var numAttributes = objSubject.Attributes.length;
	for (var i = 0; i < numAttributes; i++){
		var objAttribute = objSubject.Attributes[i];
		if (objAttribute.Property.uriProperty == "http://www.w3.org/2003/01/geo/wgs84_pos#lat" ){	
			MapLat = objAttribute.Value;
		}
		if (objAttribute.Property.uriProperty == "http://www.w3.org/2003/01/geo/wgs84_pos#long" ){	
			MapLong = objAttribute.Value;
		}		
		
		
		if (objAttribute.Property.uriProperty == "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/easting" ){
			MapEasting = objAttribute.Value;
		}
		if (objAttribute.Property.uriProperty == "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/northing" ){	
			MapNorthing = objAttribute.Value;
		}

	}

	if (MapLat === null && MapLong === null){
		if (MapEasting != null && MapNorthing != null){
			var os1 = new OsGridRef(MapEasting, MapNorthing);
			var ll1 = OsGridRef.osGridToLatLon(os1);
			MapLong = ll1.lon;
			MapLat = ll1.lat;
		}
	}
	
	if (MapLat === null){
		return;
	}
	if (MapLong === null){
		return;
	}
	
	objMapRenderer.numPlots++;
	objMapLayer.AddMarker(MapLat, MapLong, 'pin');

	return;
	
};


clsShocMap.prototype.RenderMapArea = function(MapPolygon,objMapLayer){
	objMapRenderer = this;
		
	if (MapPolygon === null){
		return;
	}
	
	try {
		var jsonGeo = JSON.parse(MapPolygon);
		switch (jsonGeo.geometry.type){
		case "MultiPolygon":					
			objMapRenderer.numPlots++;
			
			for (var L1 = 0; L1 < jsonGeo.geometry.coordinates.length; L1++){						
				for (var L2 = 0; L2 < jsonGeo.geometry.coordinates[L1].length; L2++){
					var polygon = objMapLayer.AddPolygon();
					for (var L3 = 0; L3 < jsonGeo.geometry.coordinates[L1][L2].length; L3++){
						polygon.AddVertex(parseFloat(jsonGeo.geometry.coordinates[L1][L2][L3][1]), parseFloat(jsonGeo.geometry.coordinates[L1][L2][L3][0]));
					}
				}
			}

			break;
		}
	}
	catch(e)
	{
		null;
	}
			
};


clsShocMap.prototype.setLoadingImage = function(boolOn){
	
	boolOn = (boolOn === undefined) ? true : boolOn;

	var objThis = this;
	
	if (boolOn){
		if (objThis.spanLoading == null){
			objThis.spanLoading = document.createElement('span');
			objThis.tagLoading.appendChild(objThis.spanLoading);
		}
		objThis.spanLoading.innerHTML = '<img src="images/ajax-loader.gif"/>';		
	}
	else
	{
		if (objThis.spanLoading !== null){
			objThis.spanLoading.parentElement.removeChild(objThis.spanLoading);
			objThis.spanLoading = null;
		}
			
	}
	return;

};




var gShoc = new clsShoc();
