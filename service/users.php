<?php

include dirname(__FILE__) . '/../partials/pageCheck.php';
include_once dirname(__FILE__) . '/../classes/core/database.php';
include_once dirname(__FILE__) . '/../classes/core/utility.php';


//session_start();

$tenantID = $_SESSION['tenantID'];
if ($_SERVER['REQUEST_METHOD']=="GET") {
		
	$numToReturn = Utility::getRequestVariable('return', 10);
	if ($numToReturn>100) {
		$numToReturn=100; // let's not get crazy, people.
	}
	$offset = Utility::getRequestVariable('offset', 0);
	$totalUsers=0;
	
	$query = "call getUsersCount(" . $userID . ")";
	$data = Database::executeQuery($query);
	if ($r = mysqli_fetch_row($data))
			{
			$totalUsers=$r[0];
			}
	
	$query = "call getUsers(" . $userID . "," . $numToReturn . "," . $offset . ")";
	$data = Database::executeQuery($query);
    $users=array();
	while ($r = mysqli_fetch_assoc($data))
			{
			$users[] = $r;
			}
	
	$set = "{\"totalUsers\":" . $totalUsers . ", \"users\":" . json_encode($users) . "}";
	
	header("Access-Control-Allow-Origin: *");	
	header('Content-Type: application/json');

	echo $set;
				
	}

elseif ($_SERVER['REQUEST_METHOD']=="POST")
	{
	echo "Unsupported HTTP method.";
	header(' ', true, 400);
	die();
	}
	
elseif ($_SERVER['REQUEST_METHOD']=="PUT")
	{
	echo "Unsupported HTTP method.";
	header(' ', true, 400);
	die();
	}

elseif ($_SERVER['REQUEST_METHOD']=="DELETE") 
	{
	echo "Unsupported HTTP method.";
	header(' ', true, 400);
	die();
	} 
else
	{
	echo "Unsupported HTTP method.";
	header(' ', true, 400);
	die();
	}
