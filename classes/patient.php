<?php

include_once 'core/database.php';
include_once 'core/dataentity.php';
include_once 'core/utility.php';
include_once 'core/cache.php';

class Patient extends DataEntity {
    
    
    public function getName() {
            return "Patient";
        }
    
    public function getFields() {
        $fields = array(
            array("firstName","string",100),
            array("middleName","string",100),
            array("lastName","string",100)
        );      
        return $fields;
    }
    
    public function isRequiredField($fieldName) {
        return ($fieldName=='lastName');
    }
    
    public function friendlyName($fieldName) {
        if ($fieldName=="firstName") {
            return "First Name";
        }
        if ($fieldName=="lastName") {
            return "Last Name";
        }
        if ($fieldName=="middleName") {
            return "Middle Name";
        }
        else {
            return parent::friendlyName($fieldName);
        }
        
    }
    
    public function renderView ($entity,$returnurl) {
        return '<p>Hi!</p>';
    }
}