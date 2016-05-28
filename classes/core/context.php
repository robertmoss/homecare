<?php

class Context{
 
    public static $currentUser;
    public static $tenantid;
    
    public static function getUser($userid) {

        // returns user object for specified user; right now, we just cache the currentUser object
        // if that's what caller is looking for, we return it; otherwise instantiate new user object
            
        if ($currentUser && $currentUser->id=$userid) {
            return $currentUser;
        }
        else {
            return new User($userid,self::$tenantid);
        }
    }
        
}
    