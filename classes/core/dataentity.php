<?php

interface iDataEntity {
	
	public function getName();
	public function getPluralName();
    public function getFriendlyName();
	public function getDataServiceURL();
	public function getFields();
	public function getEntity($id);
	public function getEntities($filters, $return, $offset);
	public function validateData($data);
	public function addEntity($data);
	public function updateEntity($id,$data);
    public function updateFields($id,$data);
    public function deleteEntity($id);
	public function renderView($entity,$returnurl);
	public function getJavaScript();
	public function getCustomValue($fieldname,$currentvalue,$operationtype);
	public function getCustomEditControl($fieldname,$currentvalue);
	public function getCustomFormControl($fieldname, $entity);
	public function getEntityCount($filters);
	public function hasProperties();
	public function hasOwner();
	public function getPropertyKeys();
	
}

abstract class DataEntity implements iDataEntity {
		
		public $userid;
		public $tenantid;
				
		public function __construct($userid,$tenantid) {
			$this->userid = $userid;
			$this->tenantid = $tenantid;
		}
        
				
		public abstract function getName();
		
        // override if you want your entity to be displayed with a different name than underlies the data store
        public function getFriendlyName() {
            return $this->getName();
        }
        
		// should return an array defining available fields on the object
		public abstract function getFields();
		/* returns array with this format
		 * 	[0] name: name of the field
		 *  [1] field type: string, number, date, viewonly, picklist, linkedentity, childentities, custom
		 * 			string: regular varchar/text field
		 * 			number: integer or decimal number
		 * 			date: date
		 *       boolean: a true/false value (1=true/0=false)
		 * 			viewonly: won't be rendered on edit forms or submitted to database on insert/update
         *            hidden: won't be displayed on forms but will be submitted to database on insert/update and 
         *                      included in get service requests. Assumed to be numeric: use for parents and other keys
		 * 			picklist: pick from a static list of values, using Utility object's getList method
		 * 		linkedentity: represents a many-to-one linkage - this field links the current entity to one (and only one) other entity 
         *                      (e.g. order linked to a specified customer, a customer may have multiple orders but an order only has one customer)
         *    linkedentities: represents a many-to-many linkage - this entity has a collection of sub-entities (potential many sub-entities) linked to it, 
         *                      and those sub-entities may be linked to other entities, too (e.g. order linked to multiple products, and those products can be 
         *                      used on other orders) 
		 * 	    childentities: represents one-to-many relationship: a collection of sub-entities to this entity, and those sub-entities are used
         *                      only on this particular entity (e.g. the line items on an order)
		 * 			  custom: handling of field is deferred to the entity subclass for special treatment 
		 *  		   image: an image file that gets uploaded to content server with url stored in database
		 *        properties: a dummy placeholder that lets you specify where on forms to place user-defined properties
		 * 			  custom: core classes don't know what to do with this, so must be handled custom by child
		 * 
		 *  [2] max length: maxium length of the field (in characters for text, digits for numbers)
		 *         0 or not set indictes no max
		 *  [3+] info varies by field type:
		 * 		picklist: [3] - name of list to choose values from (as found in Utility::getList method )
		 * 				  [4] - (optional) boolean indicating whether users can add new picklist itmes (e.g. to show an "add new button" 
         *                      next to pick list to add new items)
		 * 		linkedentity:
		 * 				  [3] - same as picklist #3 
		 * 				  [4] - same as picklist #4
		 * 				  [5] - (optional) the class name of the linked entity
		 * 				  [6] - (optional) index of the hidden or viewonly field in the field array to be used to as label for the linked entity 
		 * 		linkedentities
		 * 				  [2] - name of the linked entity
		 * 				  [3] - true/false: whether user should be allowed to dynamically create new entities to be linked (if false, user can only select from a defined list of existing entities)
		 * 				  [4] - true/false: whether user should be able to delete a linked entities or just de-link the entity from parent 
         *      childentities
         *                [2] - name of the childentity
		 */

		public function getFieldType($fieldName) {
		    // returns the type of the specified field
		    $fields = $this->getFields();
		    for ($i=0;$i<count($fields); $i++) {
		        if ($fields[$i][0]==$fieldName) {
		            return $fields[$i][1];
                    break;
		        }
		    }
            return "unknown fieldName";
		}
        
        public function hasField($fieldName) {
            // returns true if the specified entity has the named field; false otherwise
            $fields = $this->getFields();
            $hasField = false;
            for ($i=0;$i<count($fields); $i++) {
                if ($fields[$i][0]==$fieldName) {
                    $hasField = true;
                    break;
                }
            }
            return $hasField;
        }
		
		public function getPluralName() {
			// overrride for classes with funky plurals
			return $this->getName() . 's';
		}
		
		
		public function getDataServiceURL() {
			return Config::$service_path . "/entityService.php?type=" . lcfirst($this->getName());
		}
		
		public function getJavaScript() {
			// if your class needs custom javascript to handle view elements, override and return a script tag containing the reference to it
			// by default, no script tags are rendered
			
			return '';
		}
		
		public function isRequiredField($fieldName) {
			// override to specify what fields are required for the child object
			// by default, id is only required field
			return ($fieldName=='id');
		}
        
       public function isUpdatableField($fieldName) {
            // a non-updatable field can be set on a new entity but after that cannot be updated through the API
            // override and return false for any field needing such handling
            return true;
        }
       
       public function isParentId($fieldName) {
           // override and return true for fields that are Parent IDs for the entity - that is, links to the entity's parent
           // doing so drives the automatic linking of subentities to parent entities in forms, etc.
           // Note: parentids are used for 1 to N parent relationships
           //       childentity data types are used for N to N parent relationships
           return false;
       }
       
        public function fieldAllowsSingleUpdates($fieldName) {
            // By default, you cannot update an individual field on an entity; you must update the entire entity with all
            // field values via the updateEntity method. Override this method to return true for fields that you wish the entityService
            // to be updated in isolation from the rest of the entity fields. Useful, for example, to let a service call set a "status" field on
            // an entity
            return false;
        }
		
		public function isClickableUrl($fieldName) {
			// override if you wish a field to be 'clickable' - opening in new window w/ field value as url 
			return false;
		}
		
		public function friendlyName($fieldName) {
			// override if you want to to have a more human-readble name for one or more fields
			return ucfirst($fieldName);
		}
		
		public function getEntity($id) {
			// returns an object graph representing the entity
			
			$query = $this->getEntityQuery($id);
			
			$data = Database::executeQuery($query);
			$entity = '';
			
			//echo '<p>count= ' . $data->field_count .'</p>';
			
			if ($data->num_rows==0)	{
				//no match found.
				throw new Exception($this->getName() . ' not found.');
				//return array();
			}
			
			while ($r = mysqli_fetch_assoc($data))
				{
				$entity = $r;
				}
			
			// add user-defined properties, if supported
			if ($this->hasProperties()) {
				$query = $this->getPropertyQuery($id);			
				$data = Database::executeQuery($query);
				$properties = array();
				while ($r = mysqli_fetch_assoc($data))
					{
					$properties[] = $r;
					}
				if (count($properties)>0) {
					$entity["properties"] = $properties;
				}
			}
				
			// query child entities, if any exist
			$fieldarray = $this->getFields();
			$separator = "";
			foreach ($fieldarray as $field) {
				if ($field[1]=='childentities'||$field[1]=='linkedentities') {
					// add .
					$query = "call get" . ucfirst($field[0]) . "By" . $this->getName() . "ID(" . $id . "," . $this->tenantid . "," . $this->userid . ")";
					$data = Database::executeQuery($query);
					if ($data->num_rows>0) {
						$subs = array();
						while ($r = mysqli_fetch_assoc($data)) {
							$subs[] = $r;
						}				
						if (count($subs>0)) {
							$entity[$field[0]] = $subs;
							}
					}		
				}
			} 
			
			
			
			return $entity;			 
		}

		public function getEntities($filters, $return, $offset) {
			// in the future, may split out the "collection" class from the core class
			// but don't want to over-OO this thing. Right now the idea is that the entity
			// class knows everything about its entity and how to manipulate it, including
			// data (singular and plural) as well as forms/rendering (UI)
			// want to keep it lightweight and not load up a bunch of classes with data when
			// all we really need is to return an object graph back to calling coding 
			
			// base class doesn't know enough handle filters in a sophisticated way
			// must override if you want filter capability or to return child entities
			// base class simply does an unfiltered query with start and offset
			// 
			// $return:	number of entities to return; defaults to 50
			// offset:	number to start at (i.e. skip)
			if (is_null($return)||$return<=0) {
				$return = 50;
			}
			$query = $this->getEntitiesQuery($filters,$return,$offset);
			
						
			$data = Database::executeQuery($query);
			$entity = '';
			
			
			if ($data->num_rows==0)	{
				//no match found.
				//throw new Exception($this->getName() . ' not found.');
				return array();
			}
			
			while ($r = mysqli_fetch_assoc($data))
				{
				$entities[] = $r;
				}
			
			return $entities;		
		}

		protected function getEntityQuery($id) {
			// returns the SQL query used to retrieve multiple entities.
			// by default, all data entities should have a GET stored procedure named get<Entity>ById with params id, userid and tenantid
			// Override if you wish to have a non-standard stored proc or query
			$query = 'call get' . $this->getName() . 'ById(' . Database::queryNumber($id) . ', ' . Database::queryNumber($this->tenantid). ', ' . Database::queryNumber($this->userid) . ');';
			return $query;
		}
		
		protected function getPropertyQuery($id) {
			$query = 'call getPropertiesBy' . $this->getName() . 'Id(' . Database::queryNumber($id) . ', ' . Database::queryNumber($this->tenantid). ');';
			return $query;
		}

		protected function getEntitiesQuery($filters, $return, $offset) {
			// returns the SQL query used to retrieve multiple entities.
			// Override if you wish to have a non-standard stored proc or query
			$query = 'call get' . $this->getPluralName() . '(' . Database::queryNumber($this->userid) . ', ' . Database::queryNumber($return). ', ' . Database::queryNumber($offset) . ', ' . Database::queryNumber($this->tenantid) . ');';				
			return $query;
		}
		
		public function getEntityCount($filters) {
			// returns the total number of entities matching specified filter
			// assumes table has same name as entity and has a tenantid column
			// currently, the base class isn't smart enough to know how to filter entities, so it returns a count of all entities
			// override if you need different/smarter behavior (or just overwrite getEntityCountQuery method)
			
			// 
			$query=$this->getEntityCountQuery($filters);
			$data = Database::executeQuery($query);
			
			if ($data->num_rows==0)	{
				//no match found.
				//throw new Exception($this->getName() . ' not found.');
				return 0;
			}
			else {
				$r = mysqli_fetch_row($data);
				return $r[0];
			}				
		}
		
		protected function getEntityCountQuery($filters) {
			$query = 'select count(*) from ' . strtolower($this->getName()) . ' where tenantid=' . $this->tenantid;
			return $query;
		}
		
		public function validateData($data) {
			// takes an object graph as input
			// override to add your own validation.
			
			// evaluate required fields
			
			Utility::debug('dataentity.validateData called',1);
			
			$fieldarray = $this->getFields();	
			foreach ($fieldarray as $field) {
				Utility::debug('Validating ' . $field[0],1);
				if (!property_exists($data,$field[0])||$data->{$field[0]}=='') {
					if ($this->isRequiredField($field[0])) {
						throw new Exception($field[0] . ' is required.');
						}
					}
			}
			Utility::debug('dataentity.validateData validated successfully.',1);
			return true;
		}
		
		public function addEntity($data) {
			
			// this does a very basic add based upon common pattern
			// override to add custom save functionality
			$this->validateData($data);
			
			$newID = 0;
			$query = "call add" . $this->getName() . "(";
			$fieldarray = $this->getFields();
			$followOnQueries = array();
			$separator = "";
			foreach ($fieldarray as $field) {
				$value = '';
				if (!property_exists($data,$field[0])) {
					if ($this->isRequiredField($field[0])) {
						throw new Exception($field[0] . 'is required.');
						}
					}
				else {
					$value = $data->{$field[0]}; 		
					}
				switch ($field[1]) {
					case "string":
						$query .= $separator . Database::queryString($value);
						break;
					case "json":
						$query .= $separator . Database::queryJSON($value);
						break;
					case "date":
						$query .= $separator . Database::queryDate($value);
						break;
					case "number":
                    case "hidden":
						$query .= $separator . Database::queryNumber($value);
						break;
					case "boolean":
						$query .= $separator . Database::queryBoolean($value);
						break;
					case "picklist":
						$query .= $separator . Database::queryString($value);
						break;
					case "linkedentity":
						$query .= $separator . Database::queryNumber($value);
						break;
					case "linkedentities":
						if (is_array($data->{$field[0]})) {
							$children = $data->{$field[0]};
	 						foreach ($children as $c) {
								$procname = $this->getAddChildProcName($field[2]);
								array_push($followOnQueries,'call ' . $procname . '([[ID]],' . $c->id . ',' . $this->tenantid . ');');
							}
						}
						break;
					
					case "custom":
						$query .= $separator . $this->getCustomValue($field[0],$data->{$field[0]},'add');
					}
					$separator = ", ";
					}
			// assume tenantid is always needed
			$query .= $separator . Database::queryNumber($this->tenantid);
			$separator = ", ";
			
			// add userid if object hasOwner 
			if ($this->hasOwner()) {
				$query .= $separator . Database::queryNumber($this->userid);
			}
			$query .= ')';
			
			
			$result = Database::executeQuery($query);
			
			if (!$result) {
				return false;
			}
			else 
			{
				while ($r = mysqli_fetch_array($result))
					{
					$newID=$r[0];
					}
			}
			
			// next, handle user-defined properties
			if ($this->hasProperties()) {
				// get array of properties configured for this entity & tenant
				$keys = $this->getPropertyKeys();
				foreach($keys as $key) {
					if (property_exists($data,'PROP-' . $key[0])) {
							// only save if not empty - that's the MO for now
							$val =  $data->{'PROP-'.$key[0]};
							if (strlen($val)>0) {
								$this->saveProperty($newID, $key[0], $data->{'PROP-'.$key[0]});
							}
						}
				}
			}
			
			// finally, execute follow-on queries to add child entities		
			foreach($followOnQueries as $q) {
					// replace ID placeholder with new ID now that entity is saved
					$q2 = str_replace('[[ID]]',$newID,$q);
					$result = Database::executeQuery($q2);
				}
			
			return $newID;
		
		}
		
		public function updateEntity($id,$data) {
			
			// this does a very basic update based upon common pattern
			// override to add custom save functionality
						
			Utility::debug('dataentity.updateEntity called',5);
			$queries = array();
			$this->validateData($data);
			//$queries = array();
			$newID = 0;
			$query = "call update" . $this->getName() . "(" . $id;
			$followOnQueries = array();
			$fieldarray = $this->getFields();
			$separator = ",";
			foreach ($fieldarray as $field) {
			     if (!property_exists($data,$field[0])) {
			        // assume all required fields already validated, so do what?
			        $data->{$field[0]} = null;
			    }
				switch ($field[1]) {
					case "string":
						$query .= $separator . Database::queryString($data->{$field[0]});
						break;
					case "json":
						$query .= $separator . Database::queryJSON($data->{$field[0]});
						break;
                    case "boolean":
                        $query .= $separator . Database::queryBoolean($data->{$field[0]});
                        break;
					case "number":
                    case "hidden":
						$query .= $separator . Database::queryNumber($data->{$field[0]});
						break;
					case "date":
						$query .= $separator . Database::queryDate($data->{$field[0]});
						break;
					case "picklist":
						$query .= $separator . Database::queryString($data->{$field[0]});
						break;
					case "linkedentity":
						$query .= $separator . Database::queryNumber($data->{$field[0]});
						break;
					case "linkedentities":
                        // a little extra overhead here, but due to sort/sequence keys, etc., don't want to blow away and replace unless we have to
                        // first, determine whether linkedentities list is different
                        $peekquery = "call get" . ucfirst($field[0]) . "By" . $this->getName() . 'Id(' . Database::queryNumber($id) . ',' . Database::queryNumber($this->tenantid) . ',' . Database::queryNumber($this->userid) .');';
                        $results = Database::executeQuery($peekquery); 
                        $currentSet = array();
                        $debug = '';
                        while ($row = $results->fetch_assoc()) {
                            array_push($currentSet,intval($row["id"]));
                            $debug .= $row["id"] . '-';   
                        }
                        $newSet = array();
                        $debug .=  '|';
                        if (is_array($data->{$field[0]})) {
                            $children = $data->{$field[0]};
                            foreach ($children as $c) {
                                array_push($newSet,$c->id);
                                $debug .= $c->id . '-';
                            }
                        }
                        Log::debug('SETS: ' . $debug, 5);
                        // first, determine whether we need to remove children
                        for($i=0;$i<count($currentSet);$i++) {
                            if (!in_array($currentSet[$i],$newSet)) {
                                // one of the old children is not in new set; for now, we'll remove all    
                                $procname = $this->getRemoveChildrenProcName($field[0]);
                                array_push($followOnQueries,'call ' . $procname . '('. $id . ',' . $this->tenantid . ');');
                                // blank current set so all children get re-added
                                $currentSet = array();
                                break;                                                                                                    
                            }           
                        }
                        
                        // now, determine which children need to be added
						if (is_array($data->{$field[0]})) {
							$children = $data->{$field[0]};
	 						foreach ($children as $c) {
	 						    if (!in_array($c->id,$currentSet)) {
	 						        // this child isn't present. Will need to add
                                    $procname = $this->getAddChildProcName($field[2]);
                                    array_push($followOnQueries,'call ' . $procname . '('. $id . ',' . $c->id . ',' . $this->tenantid . ');');
	 						    }
							}
						}
						break;
					case "custom":
						$query .= $separator . $this->getCustomValue($field[0],$data->{$field[0]},'update');
					}
					$separator = ", ";
					}
			// assume tenantid is always needed and is last parameter (or 2nd to last if user required)
			$query .= $separator . Database::queryNumber($this->tenantid);
			$separator = ", ";
			
			// add userid if object hasOwner 
			if ($this->hasOwner()) {
				$query .= $separator . Database::queryNumber($this->userid);
			}
			
			$query .= ')';
			array_push($queries,$query);
			
			// handle user-defined properties
			if ($this->hasProperties()) {
				// remove all properties for object - if not specified in the data, assume it's not longer a valid property
				array_push($queries,$this->getDeletePropertiesSQL($id));
					
				// get array of properties allowed for this entity & tenant
				$keys = $this->getPropertyKeys();
				foreach($keys as $key) {
					// determine whether data contains a value for this key - field will be prepended with PROP
					if (property_exists($data,'PROP-' . $key[0])) {
						// only save if not empty - that's the MO for now
						$val =  $data->{'PROP-'.$key[0]};
						if (strlen($val)>0) {
							array_push($queries,$this->getSavePropertySQL($id, $key[0], $data->{'PROP-'.$key[0]}));
						  } 
    					}	
    				}
    			}
    					
			// add follow-one queries for child entities
			foreach($followOnQueries as $q) {
			     array_push($queries,$q);
				}
            
            Database::executeQueriesInTransaction($queries);
            return true;
        }

        private function saveProperty($id, $key, $value) {
            $query = $this->getSavePropertySQL($id, $key, $value);
            Database::executeQuery($query);
        }
        
		public function getRemoveChildrenProcName($childentityname) {
			// override if your class has a different name for the proc that removes all linked child entities
			$proc = 'remove' . ucfirst($this->getName()) . ucfirst($childentityname); 
			return $proc;
		}
		
		public function getAddChildProcName($childsinglename) {
			// override if your class has a different name for the proc that adds a linked child entity
			$proc = 'add' . ucfirst($this->getName()) . ucfirst($childsinglename); 
			return $proc;
		}
        
        public function updateFields($id,$data) {
            // updates just the specified field on the specified entity (fields not included in $data object graph will be left
            // unchanged in the database )
            // by default, individual fields can't be updated unless the underlying class allows it
            if (!$id>0) {
                throw new Exception('Invalid ID: ' . $id. '.');
            }
            
            $fields = get_object_vars($data);
            $updateString = '';
            $separator = '';
            while ($field = current($fields)) {
                $fieldname = key($fields);
                if ($fieldname !='id') {   
                    if (!$this->fieldAllowsSingleUpdates($fieldname)) {
                        throw new Exception('Single updates not allowed for ' . $fieldname . '.');
                        }
                    $updateString .= $separator . $fieldname . '=' . $this->getFieldUpdateString($fieldname, $field); 
                    $separator = ', ';
                }
                next($fields);   
                }
            
           if (strlen($updateString)==0) {
               throw new Exception('No valid values available for updating.');
           }
           $query = 'update ' . lcfirst($this->getName()) . ' set ';
           $query .= $updateString;
           $query .= ' where id = ' . Database::queryNumber($id) . ';';
           
           return Database::executeQuery($query);
            
        }

        protected function getFieldUpdateString($fieldname,$value) {
            $fieldType = $this->getFieldType($fieldname);
            $output = '';
            switch ($fieldType) {
                    case "string":
                        $output = Database::queryString($value);
                        break;
                    case "json":
                        $output = Database::queryJSON($value);
                        break;
                    case "boolean":
                        $output = Database::queryBoolean($value);
                        break;
                    case "number":
                    case "hidden":
                        $output = Database::queryNumber($value);
                        break;
                    case "date":
                        $output = Database::queryDate($value);
                        break;
                    case "picklist":
                        $output = Database::queryString($value);
                        break;
            }
            return $output;
        }


		public function deleteEntity($id) {
			
			// this does a very basic delete based upon common pattern
			// override to add custom delete functionality
			
			$query = "call delete" . $this->getName() . "(" . $id;
			// assume tenantid and userid are always needed and are last parameters
			$query .= ',' . Database::queryNumber($this->tenantid);
			$query .= ',' . Database::queryNumber($this->userid);
			$query .= ')';
			
			$result = Database::executeQuery($query);
			
			if (!$result) {
				return false;
			}
			else 
			{
				return true;				
			}
		
		}

		public function getCustomValue($fieldname, $currentvalue, $operationtype) {
			// operationtype is add, update, etc.
			// override to tell the dataentity to use a value for this field 
			// other than that submitted to the web service
			return $currentvalue;
		}
		
		public function getAvailableChildren($fieldname) {
			// for childentities type fields, override to return a list of eligible child entities that can be linked to this object
			// if no list is returned (i.e. empty array), controlling code will assume children cannot be added
			// no need to override if you don't want to specify a specific set of allowable children
			// should return id in first position, name in second (e.g. "1","Testing")
			return array(); 
		}
		
		
		// produces a very basic display of an entity's fields. Override to create your
		// own stylized views
		public function renderView ($entity,$returnurl) {
						
			$fieldarray = $this->getFields();
			$entityid = $entity["id"];
			$name = 'entity';
			
			foreach ($fieldarray as $field) {
				if ($field[0]=="name") {
					$name = $entity[$field[0]];
					echo "<h1>" . $name . "</h1>";
				}
				elseif ($field[1]=="linkedentity") {
					// do nothing. supress linkedentities: these are ids, and view json should show viewonly labels 
				}
				elseif ($field[1]=="childentities") {
					echo '<div class="subentity">';	
					echo "<h2>" . ucfirst($field[0]) . ": </h2>";
					if (isset($entity[$field[0]])) {
						$childarray = $entity[$field[0]];
						foreach ($childarray as $child) {
							echo '<p><a href="entityPage.php?type=' . $field[2] . '&mode=view&id=' . $child["id"] . '">' . $child["name"] . '</a></p>';
						}
					}
					if (isset($field[3]) && $field[3]) {
						echo '<p><input type="button" class="btn" value="Add New ' . ucfirst($field[2]) . '" onclick="document.location=\'entityPage.php?type=' . $field[2] . '&mode=edit&id=0&parentid=' . $entityid  . '\';"></p>';	
						}
					echo '</div>';
				}
				else {
					echo "<p>" . $this->friendlyName($field[0]) . ": " . $entity[$field[0]] . "</p>";
				}
			}
		}


		public function getCustomEditControl($fieldname, $currentvalue) {
			// type is add, update, etc.
			// override to tell the dataentity the value to use for this field
			return '<p>Custom edit field for ' . $fieldname . ' not defined. Field value=' . $currentvalue . '</p>';
		}
		
		public function getCustomFormControl($fieldname, $entity) {
			// by default does nothing
			// if you need a custom control for you entity (e.g. the Google Places lookup for Locations)
			// override this method and return the markup for the control, which will be rendered immediately after the default form fields
			return '';
		}
		
		public function hasProperties() {
			// override and return true if you wish your object to support user-definable properties
			// if you do, it is assumed there is a table called [entityname]Property with columns id, [entityname], key, value
			// to hold your properties
			// default is false
			return false;
		}
		
		public function getPropertyKeys() {
			// return an array of the user-defined property keys allowed for this tenant for this entity
			// by default will assume we can query based on entity name; override if you need special handling
			$query = 'call getTenantPropertiesByEntity(' . $this->tenantid . ',' . Database::queryString($this->getName()) . ')';
			$result = Database::executeQuery($query);
			
			$keys=array();
			while ($r = mysqli_fetch_row($result))
				{
				$keys[] = $r;
				}
			return $keys;
			}
		
		protected function getDeletePropertiesSQL($id) {
			// assumes a pattern to property tables; only override if your object stores properties differently
			$tablename = lcfirst($this->getName()) . 'Property';

			$query = 'delete from ' . $tablename . ' where id in (';
			$query .= ' select * from (select distinct T.id from ' . $tablename . ' T where';
			$query .= ' T.' . lcfirst($this->getName()) . 'id=' . Database::queryNumber($id) . ') as list);';

			return $query;

		}
		
		function getSavePropertySQL($id,$key,$value) {
	
			$tablename = lcfirst($this->getName()) . 'Property';
			$idname = lcfirst($this->getName()) . 'id';

			// key is a reserved word, making this a bit of a pain (hence appendeding table name)
			$query = 'insert into ' . $tablename . ' (' . $idname . ',' . $tablename . '.key,value)';
			$query .= ' values (' . Database::queryNumber($id);
			$query .= ', ' . Database::queryString($key);
			$query .= ', ' . Database::queryString($value) . ');';

			return $query;	
		}
		
		public function hasOwner() {
			// override and return true if you wish your object to be ownable by a user
			// if true, userid will be passed to all add/update/get statements
			// default is false
			return false;
		}
        
    public function userCanRead($id,$user) {
        // by default, any user read/view any entity
        return true;
    }
    
    public function userCanEdit($id,$user) {

       if ($user->id==0) {
           // must be authenticated user to edit a typical entity
            return false;
        }
       elseif ($user->id==1) {
           return true;
       }
       else {
            // simple rule for most entities: to edit an entity within a tenant, you must be an admin within that tenant
            return $user->hasRole('admin',$this->tenantid);
        }
    }
    
    public function userCanAdd($user) {
        // rules by default: must be authenticated user to add and must be an admin within the tenant (or superuser)
        Log::debug('checking user perms ' . $user->id,1);
        if ($user->id==0) {
            return false;
        }
        elseif ($user->id==1){
            // superuser can do anything!
            return true;
        }
        else {
            return ($user->hasRole('admin',$this->tenantid));
        }
    }
        
    public function userCanDelete($id,$user) {
        
        // by default, only admin user can delete entities
        if ($user->id==0) {
            return false;
        }
        elseif ($user->id==1 ){
            // superuser can do anything!
            return true;
        }
        else {
            return ($user->hasRole('admin',$this->tenantid));
        }
    }

}