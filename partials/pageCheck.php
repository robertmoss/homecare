<?php

/*
 * pageCheck is an essential routine. It should be included on every page in the application. 
 * It verifies we have a session, sets the tenantid & userid in memory 
 */

	ini_set('display_errors', 'On'); // switch to off for production deployment

	include_once dirname(__FILE__) . '/../classes/core/user.php';
    include_once dirname(__FILE__) . '/../classes/core/context.php';
	include_once dirname(__FILE__) . '/../classes/core/utility.php';

	
	error_reporting(E_ALL | E_STRICT);
    session_start();
    Utility::debug('pageCheck executing for ' . $_SERVER["SCRIPT_FILENAME"] . ' - sessionid=' . session_id(), 1);
    date_default_timezone_set('America/New_York');
	$user = null;
    $newsession = false;
    $applicationID = 1; // not using for anything right now, but conceivable could be used in future if multiple application share same core and database
    if (!isset($_SESSION['userID'])) {
        $newsession = true;
    }

    // TO DO: look at URL to see if it is custom one for a tenant
    // TO DO: as we add more custom URLs, need to look in DB or elsewhere vs. hardcoding
    if ($_SERVER['SERVER_NAME']=='[URL HERE]') {
        $_SESSION['tenantID'] = 1;
    }
    elseif ($_SERVER['SERVER_NAME']=='[URL HERE]') {
        $_SESSION['tenantID'] = 5;
    }
    // ETC.

	// set tenant for this application. Will default to 1
	if (!isset($_SESSION['tenantID'])) {
		$_SESSION['tenantID'] = 0;
		// look to see if tenant specified on query string
        if (isset($_GET["tenant"])) {
			$_SESSION['tenantID'] = $_GET["tenant"]; 
			}
		else {
			// for now defaulting to 1: need to update to handle in future
			$_SESSION['tenantID'] = 1;
			}
		}
	$tenantID = $_SESSION['tenantID'];
    Context::$tenantid = $tenantID;
	
    if (!isset($_SESSION['userID'])) {
		// set ID to 0 to indicate unauthenticated user
	   $_SESSION['userID']=0;
       $userID = 0;
    }
    else {
        $userID=$_SESSION['userID'];
    }
    
    try {
    	$user = new User($userID,$tenantID);
        Context::$currentUser = $user;}
    catch (Exception $ex) {
        
    }
    
    if ($newsession) {
        Log::startSession(session_id(),$tenantID,$userID);
    }
	
	if ($userID>0 && !$user->canAccessTenant($tenantID)) {
	    Log::debug('Unauthorized user attempted to access tenant page. (user=' . $userID . ', tenant=' . $tenantID . ')', 9);
		header('HTTP/1.0 403 Forbidden');
		echo '<p>You are not allowed to access this resource.</p>';
		exit();
	}
    elseif ($userID==0) {
		// TO DO: check whether tenant allows anonymous access
		// for now, assume that they all do not
		$allowAnon = Utility::getTenantProperty($applicationID, $tenantID, $userID, 'allowAnonAccess');
		if (!$allowAnon && strtolower(basename($_SERVER['PHP_SELF']))!='login.php') {
		    //echo strtolower(basename($_SERVER['PHP_SELF']));
		    Log::debug('Unauthenticated user attempted to access tenant page. Redirecting to login. (tenant=' . $tenantID . ')', 9);
		    header('Location: Login.php?context=loginRequired');
            die();
		}
	}
    Utility::debug('pageCheck complete.  (user=' . $userID . ', tenant=' . $tenantID . ')', 1);
	

	
	
	