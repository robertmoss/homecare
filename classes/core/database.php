<?php

include_once 'utility.php';
include_once 'log.php';
class Database {
	
	public static function queryString($value) {
		$value = str_replace("'","''",$value);
		return "'" . $value . "'";
	}
	
	public static function queryJSON($value) {
		// still a work in progress
		if (strlen($value)==0) {
			return 'null';
		}
		else {
			return "'" . $value . "'";			
		}

	}
	
	public static function queryDate($value) {
		
		
		if (is_null($value)||$value=='') {
				return "null";
			} 
		else {
		    if (is_a($value,"DateTime")) {
		        $value = date_format($value, 'Y-m-d H:i:s');
		    }
			// format string as MySQL compliant date
			$time = strtotime($value);
			$newformat = date('Y-m-d',$time);
	
			return "'" . $newformat . "'";
		}
	}
	
	public static function queryNumber($value) {
				
		/*if ($value=='') {Utility::debug('value is empty string ',9);}
		if ($value==0) {Utility::debug('value equals 0',9);}
		if (is_null($value)) {Utility::debug('value is null',9);}*/
				
		if (is_null($value) || $value=='') {  
			return 'null';
		}
		else {		
			return $value;
		}
	}
	
	public static function queryBoolean($value) {
		// booleans are stored as bits in database, so convert to 1 if true, 0 otherwise
        if (!$value) {
            return 0;
        }
        else {
            // gotta parse all the allowable values that could be 'true'
            $value = strtolower(strval($value));
            if ($value=='1'||$value=='true'||$value=='yes'||$value=='youbetcha') {
                return 1;
            }		
		    else {
			     return 0;
    		}
        }
    }
	
	public static function executeQuery($query)
	{
		// connect to database
		Log::debug('Database::executeQuery() called. Server=' . Config::$server . ', user=' .  Config::$user, 1);
		$con = mysqli_connect(Config::$server,Config::$user,Config::$password,Config::$database);
		if (!$con) {
				Utility::debug('Error connecting to database: ' . mysql_error(), 1);										
				Utility::errorRedirect('Error connecting to database: ' . mysql_error());											
				}
		else {
			//Utility::debug('Connected.', 9);	
		}	
		
		Log::debug('executing query [' . $query . ']', 5);
		
		$data = mysqli_query($con,$query);

		if (!$data) {
			Log::debug('Error executing query:' . mysqli_error($con),9);
			//Utility::errorRedirect('Error connecting to database: ' . mysqli_error());
			throw new Exception(mysqli_error($con));
			}
		else {
			Log::debug('Query executed successfully', 1);
			return $data;
			}
	}
    
    // accepts an array of queries and executes them within a transaction
    // if any query fails, the entire transaction will be rolled back
    public static function executeQueriesInTransaction($queries) {
         Log::debug('Database::executeQueriesInTransaction() called. Server=' . Config::$server . ', user=' .  Config::$user, 1);
         $con = mysqli_connect(Config::$server,Config::$user,Config::$password,Config::$database);
         if (!$con) {
                Utility::debug('Error connecting to database: ' . mysql_error(), 9);                                        
                Utility::errorRedirect('Error connecting to database: ' . mysql_error());                                           
                }
         Log::debug('Starting transaction.', 5);
          if (!mysqli_autocommit($con, FALSE)) {
              Log::debug('Unable to set autocommit off: ' . mysqli_error($con), 9);
              mysqli_rollback($con);
              throw new Exception(mysqli_error($con));
          }
          Log::debug('Transaction started.', 1);
         $success = true;
         foreach($queries as $query) {
                Log::debug('executing query [' . $query . ']', 5); 
               $data = mysqli_query($con,$query);
                if (!$data) {
                    $success=false;
                    Log::debug('Error executing query:' . mysqli_error($con),9);
                    break;
                }    
         }
        if (!$success) {
            Log::debug('Rolling back transaction.', 9);
            $err = mysqli_error($con);
            mysqli_rollback($con);
            mysqli_close($con);
            throw new Exception($err);
        }
        else {
            Log::debug('Committing transaction.', 5);
            mysqli_commit($con);
        }
        mysqli_close($con);
                  
    }	
	 
}
	
