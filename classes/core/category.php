<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';

class Category extends DataEntity {
       
      public function getName() {
            return "Category";
        }
  
    public function getFields() {
        $fields = array(
            array("tenantid","hidden"),
            array("name","string",100),
            array("categorytypeid","linkedentity",100,"categorytypes","false"),
            array("seq","number",1000),
            array("icon","string",255)
        );      
        return $fields;
    }
    
    public function friendlyName($fieldName) {
            // override if you want to to have a more human-readble name for one or more fields
            if ($fieldName=='categorytypeid') {
                return 'Category Type';
            }
            elseif ($fieldName=='seq') {
                return 'Sequence';
            }
            else {
                return parent::friendlyName($fieldName);    
            }
            
        }
    
     public function isParentId($fieldName) {
        return ($fieldName=='tenantid'); 
     }
   
}