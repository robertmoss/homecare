<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';

class TenantSetting extends DataEntity {
       
      public function getName() {
            return "TenantSetting";
        }
        
      public function getFriendlyName() {
            return "tenant setting";
        }
    
    public function getFields() {
        $fields = array(
            array("tenantid","hidden"),
            array("name","string",100),
            array("value","string",10000)
        );      
        return $fields;
    }
    
     public function isParentId($fieldName) {
        return ($fieldName=='tenantid'); 
     }
   
}