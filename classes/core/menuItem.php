<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';

class MenuItem extends DataEntity {
       
      public function getName() {
            return "MenuItem";
        }
        
      public function getFriendlyName() {
            return "menu item";
        }
    
    public function getFields() {
        $fields = array(
            array("name","string",100),
            array("link","string",500),
            array("newWindow","boolean"),
            array("tenantid","hidden"),
            array("seq","number",1000),
            array("roles","string",250),
        );      
        return $fields;
    }
    
     public function isParentId($fieldName) {
        return ($fieldName=='tenantid'); 
     }
     
    public function friendlyName($fieldName) {
        if ($fieldName=='seq') {
            return 'Sequence';
        }
        elseif ($fieldName=='newWindow') {
            return 'Open Target Page in New Window';
        }
        else {
            return ucfirst($fieldName);
            }
    }
   
}