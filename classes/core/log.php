<?php 

/*
 * Implements error, debug and trace log functions
 * 
 */
 include_once dirname(__FILE__) . '/../config.php';
 
 class Log {
 	public static function debug($message,$level) {
		// for now, we just inserting into a debug database. May update this to be more sophisticated in the future

		if ($level >= Config::$debugLevel) {
			$message = str_replace("'","''",$message);
			// $message = $message . ' [' . __FILE__ . ']';
			if (Config::$log_mode=='file'||Config::$log_mode=='both') {
				 Log::logToFile($message);
			}
			$query = "insert into debug.debug (message,level) values ('". $message . "'," . $level .")";
			try {
				$con = mysqli_connect(Config::$server,Config::$user,Config::$password, Config::$database);
			}
			catch(Exception $e) {
				// do what on an error? Just eat debug?
				Log::logToFile('unable to connect to database for debug:' . $e->getMessage());
			}
			if ($con) {
				mysqli_query($con,$query);
			}
			else 
				{
				Log::logToFile('unable to connect to database for debug: no connection returned.');
				}
		}		
	}
    
    public static function startSession($sessionid,$tenantid,$userid) {
        
        $session_info = "";
        if (array_key_exists('HTTP_HOST', $_SERVER)) {
            $session_info .= "HTTP_Host: " . $_SERVER['HTTP_HOST'];
        }
        if (array_key_exists('HTTP_REFERRER', $_SERVER)) {
            $session_info .= "; HTTP_Referrer: " . $_SERVER['HTTP_REFERRER'];
        }
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $session_info .= "; Remote_Addr: " . $_SERVER['REMOTE_ADDR'];
        }
        if (array_key_exists('REMOTE_HOST', $_SERVER)) {
            $session_info .= "; Remote_Host: " . $_SERVER['REMOTE_HOST'];
        }
         if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $session_info .= "; Request_Uri: " . $_SERVER['REQUEST_URI'];
        }
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $session_info .= "; User_Agent: " . $_SERVER['HTTP_USER_AGENT'];
        }        
        $query = "insert into session(sessionid,startDateTime,tenantid,userid,info)
                     values ('". $sessionid. "', now(), " . $tenantid . ", " . $userid . ", " . Database::queryString($session_info) . ")";
                     
       Log::debug('query=' . $query,1);                     
        try {
            $con = mysqli_connect(Config::$server,Config::$user,Config::$password, Config::$database);
        }
        catch(Exception $e) {
           Log::debug('unable to write to session table: ' . $e->getMessage(),10);
        }
        if ($con) {
            mysqli_query($con,$query);
        }
        else 
            {
            $this->debug('unable to connect to database for debug: no connection returned.',10);
            }
    }    
    
    public static function endSession($sessionid) {
        Log::debug('ending session ' . $sessionid,1);
        $query = "update session set endDateTime=now() where sessionid='" . $sessionid . "'";
        try {
            $con = mysqli_connect(Config::$server,Config::$user,Config::$password, Config::$database);
        }
        catch(Exception $e) {
            $this->debug('unable to write to session table: ' . $e->getMessage(),10);
        }
        if ($con) {
            mysqli_query($con,$query);
        }
        else 
            {
            $this->debug('unable to connect to database for debug: no connection returned.',10);
            }
    } 
    
    public static function setSessionUserId($sessionid,$userid) {
        Log::debug('updating user id ' . $userid . ' on session record ' . $sessionid,1);
        $query = "update session set userid= " .$userid . " where sessionid='" . $sessionid . "'";
        try {
            $con = mysqli_connect(Config::$server,Config::$user,Config::$password, Config::$database);
        }
        catch(Exception $e) {
            $this->debug('unable to write to session table: ' . $e->getMessage(),10);
        }
        if ($con) {
            mysqli_query($con,$query);
        }
        else 
            {
            $this->debug('unable to connect to database for debug: no connection returned.',10);
            }
    }     
    
	
	private static function logToFile($message) {
		// may make this more sophisticated in the future; for now, just dump to file
		date_default_timezone_set('UTC');
		file_put_contents(Config::$debug_filename, date('Y-m-d h:i:sa') . ' ' . $message . "\n", FILE_APPEND);
	}
}
