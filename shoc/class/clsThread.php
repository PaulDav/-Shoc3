<?php

require_once('class/clsSystem.php');
require_once('class/clsShocData.php');

class clsThread {

	private $uriActivity = null;
	private $uriGroup = null;
	private $uriBox = null;
	private $uriDocument = null;
	private $uriSubject = null;

	private $Sid = null;

	private $System = null;
	
	public function __construct(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;

		if (is_null($System->Session->Sid)){
			return;
		}
		$this->Sid = $System->Session->Sid;
	}
	
	public function __get($name){
		
		
		if (is_null($this->Sid)){
			return null;
		}
		
		
		switch ($name){
			case 'uriActivity':
				if (isset($_SESSION['sid'][$this->Sid]['thread']['uriActivity'])){
					return $_SESSION['sid'][$this->Sid]['thread']['uriActivity'];
				}
				break;
				
			case 'uriGroup':
				if (isset($_SESSION['sid'][$this->Sid]['thread']['uriGroup'])){
					return $_SESSION['sid'][$this->Sid]['thread']['uriGroup'];
				}
				break;
				
			case 'uriBox':
				if (isset($_SESSION['sid'][$this->Sid]['thread']['uriBox'])){
					return $_SESSION['sid'][$this->Sid]['thread']['uriBox'];
				}
				break;
				
			case 'uriDocument':
				if (isset($_SESSION['sid'][$this->Sid]['thread']['uriDocument'])){
					return $_SESSION['sid'][$this->Sid]['thread']['uriDocument'];
				}
				break;
				
			case 'uriSubject':
				if (isset($_SESSION['sid'][$this->Sid]['thread']['uriSubject'])){
					return $_SESSION['sid'][$this->Sid]['thread']['uriSubject'];
				}
				break;
				
		}
		return null;
		
	}		


	public function __set($name,$value){

		if (is_null($this->Sid)){
			return;
		}
		
		switch ($name){
			case 'uriActivity':
				$_SESSION['sid'][$this->Sid]['thread']['uriActivity'] = $value;
				break;
				
			case 'uriGroup':
				$_SESSION['sid'][$this->Sid]['thread']['uriGroup'] = $value;
				break;
				
			case 'uriBox':
				$_SESSION['sid'][$this->Sid]['thread']['uriBox'] = $value;
				break;
				
			case 'uriDocument':
				$_SESSION['sid'][$this->Sid]['thread']['uriDocument'] = $value;
				break;

			case 'uriSubject':
				$_SESSION['sid'][$this->Sid]['thread']['uriSubject'] = $value;
				break;
				
		}
		
		
	}
	
	public  function Clear(){

		if (is_null($this->Sid)){
			return;
		}

		unset($_SESSION['sid'][$this->Sid]['thread']);

		return;
	}	
		
}

?>