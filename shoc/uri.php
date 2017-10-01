<?php

require_once("path.php");

require_once("class/clsSystem.php");

require_once("class/clsPage.php");
require_once("class/clsModel.php");
require_once("class/clsShocData.php");
require_once("class/clsUri.php");

	
require_once("function/utils.inc");

	define('PAGE_NAME', 'uri');

	session_start();

	$System = new clsSystem();
	if (isset($System->Config->Vars['instance']['host'])){
		$System->path = $System->Config->Vars['instance']['host'];
	}
	
	$Shoc = new clsShoc();
	$Models = new clsModels();
	$Archetypes = new clsArchetypes($Models);
	

	$Uri = null;
	$Type = 'subject';

	$Format = 'rdf/xml';
	$ContentType = 'application/rdf+xml';

	$Format = 'ttl';
	$ContentType = 'application/ttl';
	

	if (isset($_SERVER['HTTP_ACCEPT'])){
		$Accept = $_SERVER['HTTP_ACCEPT'];
		if (substr($Accept,0,9) == 'text/html'){
			$ContentType = 'text/html';
		}
	}

	if (isset($_SERVER['REDIRECT_URL'])){
		$RedirectFrom = $_SERVER['REDIRECT_URL'];
		if (strpos( $RedirectFrom , '/id/' ) !== false){
			
			$RedirectTo =  preg_replace('/id/', 'doc' , $RedirectFrom, 1);
			
			header ("HTTP/1.1 303 See Other");
			header ("Location: $RedirectTo");		
			exit;
		}
	
		if (strpos( $RedirectFrom , '/doc/' ) !== false){			
			$Uri = 'http://'.$_SERVER['HTTP_HOST'].preg_replace('/doc/', 'id' , $RedirectFrom, 1);
		}
	}

	if (is_null($Uri)){
		if (isset($_REQUEST['uri'])){
			$Uri = $_REQUEST['uri'];
		}
	}

	
	if (isset($_REQUEST['type'])){
		$Type = $_REQUEST['type'];
	}
	
	
	if (is_null($Uri)){
		exit;
	}

	
	try {
	
		$Content = '';
		
		switch ($Type){
			case 'subject':
	
				$objUri = new clsUri();
				$objUri->forUri($Uri);
		
				if ($ContentType == 'text/html'){
					$Content = '<pre>'.htmlentities($objUri->Dereference($Format)).'</pre>';
				}
				else
				{
					$Content = $objUri->Doc;
				}
				break;
				
			case 'box':
				
				$objBox = $Shoc->getBox($Uri);
				foreach ($objBox->Subjects as $objSubject){

					$objUri = new clsUri();
					$objUri->forSubject($objSubject);
		
					$Content .= $objUri->Dereference($Format);
					
				}
				$Content = '<pre>'.htmlentities($Content).'</pre>';
				break;
				
		}
		
		header("Content-Type: $ContentType");
		echo $Content;
		 
	}
	catch(Exception $e)  {
		echo $e->getMessage();
	}
 
	exit;

?>