<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';
include_once 'cache.php';

class Page extends DataEntity {
    
    
    public function getName() {
            return "Page";
        }
    
    public function getFields() {
        $fields = array(
            array("name","string",300),
            array("shortdesc","string",5000),
            array("url","string",1000),
            array("imgurl","string",1000),
            array("roles","string",500),
            array("pageCollections","linkedentities","pageCollection",false,false)
        );      
        return $fields;
    }
    
    public function isRequiredField($fieldName) {
        return ($fieldName=='name'||$fieldName=="url"||$fieldName=="shortdesc");
    }
    
    public function friendlyName($fieldName) {
        if ($fieldName=="pageCollections") {
            return "Page Collections";
        }
        else {
            return parent::friendlyName($fieldName);
        }
        
    }
    
    public function getAvailableChildren($fieldname) {

        if ($fieldname=="pageCollections") {            
            $query = "select id, name from pageCollection where tenantid=" . $this->tenantid;
            $results = Database::executeQuery($query);
            
            if ($results->num_rows==0) {
                    return array();
                    }
                else {
                    $entities = array();
                    while ($r = mysqli_fetch_assoc($results))
                        {
                        $entities[] = $r;
                        }
                    return $entities;
                }
            
            return $results->fetch;
            }
        else {
            return parent::getAvailableChildren($fieldname);
        } 
    }
          
    
}
    