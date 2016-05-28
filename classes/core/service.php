<?php
/*
 * Service class exposes functions related to RESTful API services
 */
 
 include_once 'log.php';
 
 
class Service{
		
	public static function returnError($errorMessage,$errorCode=400,$service='unspecified') {
	// used to end service and return message to user
		header(' ', true, $errorCode);
        Log::debug('Service error ' . $errorCode . ' (service=' . $service . '): ' . $errorMessage, 9);
		echo $errorMessage;
		die();
	}
	
	public static function returnJSON($json) {
		header('Content-Type: application/json');
		echo $json;
	}
	
}
