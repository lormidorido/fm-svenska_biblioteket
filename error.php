<?php

	/**
	* FileMaker PHP Site Assistant Generated File
	*/

	 require_once 'FileMaker.php';
	 
	 function ExitOnError($result) {
	 
		$errorMessage = NULL;
		
		if (FileMaker :: isError($result)) {
			$errorCode = $result->getCode();
			$errorMessage = "<p>Fel: " . $errorCode . " - " . $result->getErrorString() . "<br></p>";
			
			if ((is_null($errorCode) === false && (($errorCode >= 208 && $errorCode <= 214)) || $errorCode == 22) || is_null($errorCode)) {
				Authenticate($errorMessage);
			} else {
				DisplayError($errorMessage);
			}
			
			exit;
		} else if ($result === NULL) {
			$errorMessage = "<p>Fel: felresultat är lika med NOLL!</p>";
			DisplayError($errorMessage);
			exit;
		}
	}
	
	function DisplayErrorandExit($message) {
		global $errormessage;
		$errormessage = $message . "<br>";
		include "errorpage.php";
		exit;
	}
	
	function DisplayError($message) {
		global $errormessage;
		$errormessage = $message . "<br>";
		include "errorpage.php";
	}

	function Authenticate($message) {
		global $errormessage;
		$errormessage = "";
		include "authentication.php";
	}
?>