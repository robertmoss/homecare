<?php

    /*
     * The form service renders a form for the specified entity. It returns HTML with just the form markup
     * Should be used client-side to render a form within a DIV on a page
     * GET parameters:
     *      type:   the type of entity to retrieve the form for
     *      id:     the id of the entity to populate the form with data for; if 0 or unspecified, will create an unfilled form (i.e. for a new entity)  
     */ 
	include dirname(__FILE__) . '/../partials/pageCheck.php';
	include_once dirname(__FILE__) . '/../classes/core/database.php';
	include_once dirname(__FILE__) . '/../classes/core/utility.php';
    include_once dirname(__FILE__) . '/../classes/core/forms.php';
    include_once dirname(__FILE__) . '/../classes/core/service.php';

	if (isset($_GET["type"])) {
		$type=$_GET["type"];
	}
	else {
		header(' ', true, 400);
		echo 'Type is required.';
		die();
	}
	
	Utility::debug('Form service invoked for type:' . $type . ', method=' . $_SERVER['REQUEST_METHOD'], 5);
	
    // in the future: can remove hardcoded array and query DB or config file to allow dynamic entities
	$knowntypes = array('location','link','media','tenant','tenantSetting','tenantProperty','category','menuItem',"page");
	if(!in_array($type,$knowntypes,false)) {
		// unrecognized type requested can't do much from here.
		Service::returnError('Unknown type: ' . $type);
	}
	
	$classpath = '/../classes/'; 
	$coretypes = array('tenant','tenantSetting','tenantProperty','category','menuItem','page');
	if(in_array($type,$coretypes,false)) {
		// core types will be in core subfolder
		$classpath .= 'core/';
	}
	
	// include appropriate dataEntity class & then instantiate it
	$classfile = dirname(__FILE__) . $classpath . $type . '.php';
	if (!file_exists($classfile)) {
		header(' ', true, 500);
		Utility::debug('Unable to instantiate class for ' . $type . ' Classfile does not exist.', 9);
		echo 'Internal error. Unable to process entity.';
		die();
	}
	include_once $classfile;
	$classname = ucfirst($type); 	// class names start with uppercase
	$class = new $classname($userID,$tenantID);
	
	$id=0; 
	if (isset($_GET["id"])) {
		$id = $_GET["id"];
	}
    $parentid=Utility::getRequestVariable('parentid', 0);
	
	$entity='';
	if ($id>0) {
	    try {
    		$entity = $class->getEntity($id,$tenantID,$userID);
        }
        catch (Exception $ex) {
            Service::returnError($ex->getMessage());
        }
	 }
?>
	<form id="<?php echo $type; ?>Form" class="form-horizontal" action="<?php echo $class->getDataServiceURL(); ?>" method="post" role="form">
		<div class="edit">
			<input type="hidden" id="<?php echo $type; ?>id" name="id" value="<?php echo $id; ?>"/>
			<input type="hidden" name="tenantid" value="<?php echo $tenantID; ?>"/>
			<input type="hidden" id="type" name="type" value="<?php echo $type; ?>"/>
			<?php
			Forms::renderForm($class, $entity, $id, $tenantID, $parentid);
			?>	        			
		</div>
	</form>
