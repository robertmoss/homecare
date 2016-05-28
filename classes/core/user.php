<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';
include_once 'cache.php';

class User extends DataEntity {
	
	public $id = 0; 
	public $name = null;
	public $email = null;
	public $twitterHandle = null;
    public $bio = null;
    	
	function __construct($id,$tenantid) {
            
        $this->tenantid = $tenantid;
        if ($id>0) {
            $this->id = $id;
            $this->loadUser($id);
        }
        else {
            $this->id = 0;
        }
        
	}
    
    private function loadUser($id) {
        
        // retrieve user from database
        $query = 'SELECT id, name, email, twitterHandle,coalesce(bio,\'\') as bio FROM user where id=' . Database::queryNumber($id);
        Utility::debug('Creating user object for id=' . $id, 5);
        $result = Database::executeQuery($query);
        $row = mysqli_fetch_assoc($result);
        if (is_null($row)) {
            throw new Exception("User not found.");
        }
        else {

            $this->name = $row["name"];
            $this->email = $row["email"];
            $this->twitterHandle = $row["twitterHandle"];
            $this->bio = $row["bio"];
            Utility::debug('User object instantiated for user id ' .$id, 1);
        }
    }
	
	public function getName() {
			return "User";
		}
	
	public function getFields() {
		$fields = array(
			array("name","string"),
			array("email","string"),
			array("password","string"),
			array("bio","string")
		);
		
		return $fields;
	}
	
	public function isRequiredField($fieldName) {
		// note: password is not required — for an update, you can change name, etc. w/o changing password
		// passwords cannot be set: they can only be reset to a tempoarry value
		return ($fieldName=='id'||$fieldName=='name'||$fieldName=='email');
	}
		
	public function getEntity($id) {
		
		//loadUser();
		$entity	= array(
			"id" => $this->id,
			"name" =>$this->name,
			"email" =>$this->email,
			"twitterHandle" => $this->twitterHandle,
			"bio" => $this->bio
			);
			
		return $entity;
	}
	
	public static function getUserDetails($username) {
		
		$query = 'SELECT id,name,email,password, twitterHandle, active FROM user where email=' . Database::queryString($username);
		
		$result = Database::executeQuery($query);
		$row = mysqli_fetch_assoc($result);
		
		return $row;
	}
	
	
	public function addEntity($data) {
            
	    Log::debug('Adding new user for tenant ' + $this->tenantid, 5);
        
		// before save: salt & hash password and perform user-specific validation
		
		// ensure email not already in use
		$query = "select count(*) from user where email=" . Database::queryString($data->{"email"}) . ";";
        $result = Database::executeQuery($query);
        while ($arr = mysqli_fetch_row($result)) {
            if ($arr[0]>0) {
                throw new Exception("That email address is already in use. Please select another.");
            }
        }
		
		$pass = Utility::generateHash($data->{"password"});
		$data->{"password"}=$pass;
		
		$newid=parent::addEntity($data);
        
        // by default, a newly created user gets assigned to the current tenant
        $query = "call addTenantUserRole(" . Database::queryNumber($newid) . "," .
                                             Database::queryNumber($this->tenantid) . "," .
                                             Database::queryString('standard')  
                                                 . ");";
        Database::executeQuery($query);
        
		return $newid;
	}
	

	public function validateUser($username,$password) {
		
		Utility::debug('Validating user ' .$username . ', tenantID=' . $this->tenantid, 9);
		
		if (strlen($username)==0 || strlen($password)==0)
			{
				throw new Exception("Invalid username or password.");
			}
		
		$userDetails = User::getUserDetails($username);
		if ($userDetails["id"]>0 && $userDetails["active"]==0) {
			throw new Exception("This user account is inactive. Please check your email for activation instructions.");
			}
		else {
			$saltedPassword = Utility::saltAndHash($password,$userDetails["password"]);
			//echo 'salted:' . $saltedPassword;
			//echo Utility::saltAndHash($password);
			$query = 'call validateUser(' . Database::queryString($username);
			$query .= ',' . Database::queryString($saltedPassword);
			$query .= ',' . Database::queryNumber($this->tenantid) . ');';
					
			$result = Database::executeQuery($query);
			if (!$result) {
			    Utility::debug('User ' .$name . ' failed validation.', 9);
				throw new Exception('Unable to validate that username/password combination.');
				}
			else {
				$userid=0;
				while($o = mysqli_fetch_object($result)) {
					$userid = $o->userid;
					$name = $o->name;
                    $passwordExpires = $o->passwordExpires;
                    $this->userid=$userid;
                    $resetPassword = $o->resetPassword;
					}
				if ($userid>0) {
				    if ($resetPassword) {
				        throw new Exception("Your password has been reset.",2); 
				    }
				    if (strtotime($passwordExpires)) {
				        $now = new DateTime("now");
                        $passwordExpires=new DateTime($passwordExpires);
                        Log::debug($now->format('Y-m-d H:i:s') . '>' . $passwordExpires->format('Y-m-d H:i:s'), 5);    
				        if ($now>$passwordExpires) {
                            throw new Exception("Your password has expired.",1); 
				        }
				    }
					$this->id = $userid;
					$this->name = $name;
					Utility::debug('User ' .$name . 'validated.', 9);
					Log::setSessionUserId(session_id(), $userid);
					}
				else {
					throw new Exception("Unable to validate that username/password combination.");
					}
			}
		}
	}

    private function validatePassword($password,$username, $userid) {
        // returns true if password if correct for specified user
        
        $userDetails = User::getUserDetails($username);
        if ($password!="reset") {
            $saltedPassword = Utility::saltAndHash($password,$userDetails["password"]);
        }
        else {
            $saltedPassword = 'reset';
        }
       
        $query = 'call validateUser(' . Database::queryString($username);
            $query .= ',' . Database::queryString($saltedPassword);
            $query .= ',' . Database::queryNumber($this->tenantid) . ');';
                    
        $result = Database::executeQuery($query);
        if (!$result) {
                Utility::debug('User ' .$name . ' failed password validation.', 9);
                return false;
            }
        else {
            $matchedid=0;
            while($o = mysqli_fetch_object($result)) {
                    $matchedid = $o->userid;
                   }
                  Utility::debug($matchedid . '- ' . $userid, 9);
            return ($userid==$matchedid); 
         }
    }

    public function resetPassword() {
        $query = 'update user set password="reset" where id=' . $this->id;
        Database::executeQuery($query);
    }

	public function getAPIKey() {
		// generates a one-time API Key for user
		// to do: explore more secure ways to do this
        $chars = str_split("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
		$key = "";
        for ($i = 0; $i < 32; $i++) {
            $randNum = rand(0, 61);
            $key .= $chars[$randNum];
        }
        return $key;
	}
	
	//Update the user's password
	public function updatepassword($pass,$expirationDate) {
		
     	$secure_pass = Utility::generateHash($pass);
		$query = "UPDATE user SET password = " . Database::queryString($secure_pass) . ", passwordExpires=" . Database::queryDate($expirationDate) . " WHERE id = " . Database::queryNumber($this->id);
		return (Database::executeQuery($query));
	}
    
    // this function does all the logic to verify a current password and update a new one
    // data elements should be:
    //   original: the original password for the user
    //   new1: the new password to be set
    //   new2: the new password to be repeated (client side should check this, be we do it again just to be safe)
    public function changePassword($data) {
        
        $userid = $data->{'id'};
        $username = $data->{'username'};
       
       if (!property_exists($data,"original")) {
           // if no password was submitted, the assumption must be that it is a reset request.
           $data->{'original'} = 'reset';
       }
       
       $expirationDays = Utility::getTenantProperty(1, $this->tenantid, $this->id, 'passwordExpires');
       $expirationDate = null;
       if ($expirationDays) {
           $interval = 'P' . $expirationDays . 'D';
           $expirationDate = new DateTime("now");
           $expirationDate = $expirationDate->add(new DateInterval($interval));
       }
       
       
        // check change password rules
        if ($data->{'original'}==$data->{'new1'}) {
            throw new Exception('The new password cannot be the same as your current one.');
        }
        elseif ($data->{'new2'}!=$data->{'new1'}) {
            throw new Exception('The two versions of the new password do not match');
        }
        elseif (strlen($data->{'new1'})<8) {
            throw new Exception('The new password must be at least 8 characters long.');
        }
        
        // validate original password
        if (!$this->validatePassword($data->{'original'}, $username, $userid)) {
            
            throw new Exception('Original password is incorrect');
        }
        
        return $this->updatepassword($data->{'new1'},$expirationDate);
    }
	
	
	
	public function canAccessTenant($tenantID) {
		
        if ($this->id==1) {
            return true; // superuser can access anything
        }
        
        // first, check cache for roles
        $rolekey = "UTR:" . $this->id . ':' . $tenantID;
        $roles = Cache::getValue($rolekey);
        if (!is_null($roles)) {
            // user has roles, so can access
            return true;
        }
        else {
              // nothing in cache: check database     
	          $query = 'select count(*) from tenantUser where userid=' . $this->id . ' and tenantid=' . $this->tenantid;
	          $result = Database::executeQuery($query);
	          if ($arr = mysqli_fetch_array($result)) {
		         return ($arr[0]>0);
        		}
	      else {		
		     return false;
    		}
       }
    }
    
    // returns an array containing the roles (as strings) for which the use has 
    // been enabled for the specified tenant; empty array if no roles enabled for that tenant 
    public function getTenantRoles($tenantid) {
        $roles = array();

        if ($this->id>0) {
            $rolekey = "UTR:" . $this->id . ':' . $tenantid;
            // first check cache
            $roles = Cache::getValue($rolekey);
            if (is_null($roles)) {
                // not cached: try database
                $roles = array();
                $query = "call getTenantRolesByUserId(" . Database::queryNumber($this->id) .
                    "," . Database::queryNumber($tenantid) . ");";
                 $results = Database::executeQuery($query);
                 while ($arr=mysqli_fetch_array($results)) {
                     array_push($roles,$arr[1]);
                 }
                 Cache::putValue($rolekey, $roles);
            }
        }    
        
        return $roles;
    }
    
    // returns true if user is assigned to specified role for specified tenant, false otherwise
    public function hasRole($role,$tenantid) {
        $hasRole = false; 
        if ($this->id>0) {
            $roles = $this->getTenantRoles($tenantid);
            if ($this->id==1) {
                // super user should be admin for any tenant
                array_push($roles,'admin');
                array_push($roles,'superuser');
            }
            $hasRole = in_array($role,$roles,false);                
            }
        return $hasRole;
    }
    
    // overrides standard version in dataentity; 
    public function userCanEdit($id,$user) {

       if ($user->id==0) {
           // must be authenticated user to edit a typical entity
            return false;
        }
       elseif ($user->id==1) {
           return true;
       }
       else {
            // to edit a user, you must be either that user or an admin
            return ($user->hasRole('admin',$this->tenantid)||$id==$user->id);
        }
    }
	
    // returns all the tenant access and roles the user has
    public function getTenants() {
        $tenants = array();
        if ($this->id>0) {
            $query = "call getRolesByUserId(" . Database::queryNumber($this->id) . ");";
            $results = Database::executeQuery($query);
            $i=0;
            while ($arr=mysqli_fetch_assoc($results)) {
                $arr["index"]=$i;
                $i++;
                 array_push($tenants,$arr);
                 }
        }
        return $tenants;
    }
    
    public function setTenantAccess($data) {
        // TO DO: 1. remove all exiting tenants
        //        2. cycle through $data and add access to each tenant specified
        
        $queries = array("call removeTenantUsers(" . $this->id . ");");
        $tenants = $data->{'tenants'};
        foreach($tenants as $tenant) {
            $query = "call addTenantUserRole(" . Database::queryNumber($this->id) . "," .
                                                 Database::queryNumber($tenant->{'tenantid'}) . "," .
                                                 Database::queryString($tenant->{'role'})  
                                                 . ");";
            array_push($queries,$query);
        }
        
        Database::executeQueriesInTransaction($queries);
                
    }
	
	
}
