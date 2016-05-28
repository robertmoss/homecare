<?php

include_once 'database.php';
include_once 'dataentity.php';
include_once 'utility.php';
include_once 'cache.php';

class Tenant extends DataEntity {
		
	public $id = 0; 
	public $name = null;
	
	public function getName() {
			return "Tenant";
		}
	
	public function getFields() {
		$fields = array(
			array("name","string",100),
			array("title","string",100),
			array("welcome","string",10000),
			array("css","string",200),
			array("allowAnonAccess","boolean"),
			array("settings","childentities","tenantSetting",true,true),
			array("properties","childentities","tenantProperty",true,true),
			array("categories","childentities","category",true,true),
			array("menuItems",'childentities','menuItem',true,true)
		);		
		return $fields;
	}
	
	public function isRequiredField($fieldName) {
		return ($fieldName=='name');
	}	
	
	// Overrides to parent methods
	
	public function friendlyName($fieldName) {
            $return =  ucfirst($fieldName);   
            switch ($fieldName) {
                case 'css':
                    $return="CSS";
                    break;
                case 'allowAnonAccess':
                    $return = 'Allow Anonymous Access';
                    break; 
            }
            return $return;
        }
	
	protected function getEntitiesQuery($filters, $return, $offset) {
		// override default since we don't need tenantID on this one.
				
			$query = 'call getTenants(' . Database::queryNumber($this->userid) . ', ' . Database::queryNumber($return). ', ' . Database::queryNumber($offset) . ');';				
			return $query;	
	}
	
	protected function getEntityCountQuery($filters) {
			$query = 'select count(*) from ' . strtolower($this->getName());
			return $query;
		}
    	
}