<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';

class TenantProperty extends DataEntity {
       
      public function getName() {
            return "TenantProperty";
        }
        
      public function getFriendlyName() {
            return "tenant property";
        }
    
    public function getFields() {
        $fields = array(
            array("tenantid","hidden"),
            array("entity","picklist",100,"entities",false),
            array("name","string",255)
        );      
        return $fields;
    }
    
     public function isParentId($fieldName) {
        return ($fieldName=='tenantid'); 
     }
   
}