<?php
/* a utility page that generates the SQL for the specified entity
 * needs type as GET parameter (e.g. generateSQL.php?type=patient)
 */
 
include_once dirname(__FILE__) . '/../partials/pageCheck.php';
include_once dirname(__FILE__) . '/../classes/core/service.php';
include_once dirname(__FILE__) . '/../classes/core/dataentity.php';
include_once dirname(__FILE__) . '/../classes/application.php';

    
 // must be an super user to access this page
 if ($userID==0 || ($user && !$user->hasRole('superuser',$tenantID))) {
    Log::debug('Non super user (id=' . $userID . ') attempted to access generateSQL.php page', 10);
    header('Location: 403.php');
    die();
    }

$type = Utility::getRequestVariable('type', '');

if (strlen($type)<1) {
    Service::returnError('Please specify a type');
}

    $coretypes = array('tenant','tenantSetting','tenantProperty','category','menuItem','page');
    if(!in_array($type,$coretypes,false) && !in_array($type, Application::$knowntypes,false)) {
        // unrecognized type requested can't do much from here.
        Service::returnError('Unknown type: ' . $type,400,'entityService?type=' .$type);
    }
    
    $classpath = '/../classes/'; 
    if(in_array($type,$coretypes,false)) {
        // core types will be in core subfolder
        $classpath .= 'core/';
    }
    
    // include appropriate dataEntity class & then instantiate it
    $classfile = dirname(__FILE__) . $classpath . $type . '.php';
    if (!file_exists($classfile)) {
        Service::returnError('Internal error. Unable to process entity.',400,'entityService?type=' .$type);
    }
    include_once $classfile;
    $classname = ucfirst($type);    // class names start with uppercase
    $class = new $classname($userID,$tenantID); 

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Generate SQL</title>
        <?php include dirname(__FILE__) . '/../partials/includes.php'; ?>
        <link rel="stylesheet" type="text/css" href="css/core-forms.css" />
        <script type="text/javascript" src="js/validator.js"></script>
        <script type="text/javascript" src="js/jquery.form.min.js"></script>
        <script src="js/modalDialog.js"></script>
        <script src="js/entityPage.js"></script>
        
    </head>
    <body>
        <div class="container">
            <h2>Update</h2>
            <div class="well">
                <code>
                    <?php
                    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                    echo 'CREATE PROCEDURE update' . $class->getName() . '(id int';
                    $fieldarray = $class->getFields();
                    $separator = ", ";
                    foreach ($fieldarray as $field) {
                        echo $separator . $field[0] . ' ';
                        switch ($field[1]) {
                            case "string":
                               if (!$field[2] || $field[2]==0 ) {
                                   $length = 0;
                               }
                               else {
                                   $length = $field[2];
                               } 
                               echo 'varchar(' . $length . ')';
                                break;
                            case "json":
                                break;
                            case "boolean":
                                break;
                            case "number":
                            case "hidden":
                                break;
                            case "date":
                                break;
                            case "picklist":
                                break;
                            case "linkedentity":
                                break;
                            case "linkedentities":
                                break;
                            case "custom":
                                break;
                            }
                        }
                    echo ", tenantid int)<br/>";
                    echo 'BEGIN<br/>';
                    echo '<br/>';
                    echo $tab . 'UPDATE ' . lcfirst($class->getName()) . ' SET<br/>';
                    $separator = $tab . $tab;
                    foreach ($fieldarray as $field) {
                        echo $separator . $field[0] . ' = ' . $field[0];
                        
                        $separator = ',<br/>' . $tab . $tab;
                        }
                    echo '<br/>';
                    echo $tab . 'WHERE';
                    echo '<br/>';
                    echo $tab . $tab . "id=id<br/>";
                    echo $tab . $tab . "AND tenantid=tenantid;<br/>";
                    echo 'END<br/>';
                    ?>
                </code>
            </div>
       </div>  
       <?php include dirname(__FILE__) . '/../partials/footer.php';?>         
    </body>
</html>    
