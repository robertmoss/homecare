<?php
/*
 * This is a placeholder wrapper for now for using a resource model to support localization/language
 * will update in the future to be more canonical
 */

class Resource {
    
    public static function getString($key) {
        
        switch ($key) {
            case 'ApplicationName':
                return('Homecare');
                break;
            case 'LogOut':
                return('Logout');
                break;
            default:
                return $key;
        }
        
    }
}
