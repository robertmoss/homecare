<?php

include_once 'core/database.php';
include_once 'core/dataentity.php';
include_once 'core/utility.php';
include_once 'core/cache.php';

class Address extends DataEntity {
    
    
    public function getName() {
            return "Address";
        }
    
    public function getFields() {
        $fields = array(
            array("addressLine1","string",200),
            array("addressLine2","string",200),
            array("city","string",50),
            array("stateOrProvince","picklist",2,"states"),
            array("postalCode","string",20)
        );      
        return $fields;
    }
    
    public function isRequiredField($fieldName) {
        return ($fieldName=='addressLine1');
    }
    
    public function friendlyName($fieldName) {
        if ($fieldName=="addressLine1") {
            return "Address Line 1";
        }
        if ($fieldName=="addressLine2") {
            return "Address Line 2";
        }
        if ($fieldName=="stateOrProvince") {
            return "State/Province";
        }
        else {
            return parent::friendlyName($fieldName);
        }
        
    }
}