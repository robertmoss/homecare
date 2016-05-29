<?php
/* a utility page that generates the SQL for the specified entity
 * needs type as GET parameter (e.g. generateSQL.php?type=patient)
 */
 
include_once dirname(__FILE__) . '/../partials/pageCheck.php';
include_once dirname(__FILE__) . '/../classes/core/service.php';
include_once dirname(__FILE__) . '/../classes/core/dataentity.php';
include_once dirname(__FILE__) . '/../classes/application.php';
include_once dirname(__FILE__) . '/../classes/config.php';
    
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
    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';

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
            <h2>SQL for <?php echo $class->getName() ?></h2>
            <div class="well">
                <code>
                    <?php
                    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                    echo '/* Stored Procedures for ' . $class->getName() . '*/<BR/><BR/>';
                    
                     /* GET proc */
                    echo 'USE `' . Config::$database . '`;<br/>';
                    echo 'DROP procedure IF EXISTS `get' . $class->getName() . 'ById`;<br/><br/>';
                    echo 'DELIMITER $$<br/>';
                    echo 'USE `' . Config::$database . '`$$<br/><br/>';
                    echo 'CREATE PROCEDURE get' . $class->getName() . 'ById(id int, tenant int, userid int)<br/>';
                    echo 'BEGIN<br/><br/>';
                    echo $tab . 'SELECT id,<br/>';
                    $fieldarray = $class->getFields();
                    $separator = $tab . $tab . "";
                    foreach ($fieldarray as $field) {
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0];
                            $separator = ',<br/>' . $tab . $tab;
                        }
                    }
                    echo '<br/>' . $tab . 'FROM<BR/>'. $tab . $tab . $class->getName() . '<BR/>';
                    echo $tab . ' WHERE<br/>' . $tab . $tab . "id=id AND tenantid=tenantid;<br/><br/>";
                    echo 'END$$<br/>DELIMITER ;';
                    echo '<br/><br/>';
                    
                    /* GET procs and table for any linked entities */
                    foreach ($fieldarray as $field) {
                        if ($field[1]=="linkedentities") {
                             
                             $tablename = lcfirst($class->getName()) . ucfirst($field[2]);
                             echo 'CREATE TABLE IF NOT EXISTS `' . Config::$database . '`.`' . $tablename . '` (<br/>';
                             echo $tab . 'id INT NOT NULL AUTO_INCREMENT,</br/>';
                             echo $tab . lcfirst($class->getName()) . 'Id INT NOT NULL, <br/>';
                             echo $tab . lcfirst($field[2]) . 'Id INT NOT NULL, <br/>';
                             echo $tab . 'PRIMARY KEY (`id`),<br/>';
                             echo $tab . 'INDEX `fk_' . $tablename . '_' . lcfirst($class->getName()) . '_idx` (`' . lcfirst($class->getName()) . 'Id` ASC),<br/>';
                             echo $tab . 'INDEX `fk_' . $tablename . '_' . lcfirst($field[2]) . '_idx` (`' . lcfirst($field[2]) . 'Id` ASC),<br/>';
                             echo $tab . 'CONSTRAINT `fk_' . $tablename . '_' . lcfirst($class->getName()) . '` FOREIGN KEY (`' . lcfirst($class->getName()) . 'Id`)<br/>';
                             echo $tab . $tab . 'REFERENCES `' . Config::$database . '`.`' . lcfirst($class->getName()) . '` (`id`)<br/>';
                             echo $tab . $tab . 'ON DELETE CASCADE<br/>';
                             echo $tab . $tab . 'ON UPDATE NO ACTION,<br/>';
                             echo $tab . 'CONSTRAINT `fk_' . $tablename . '_' . lcfirst($field[2]) . '` FOREIGN KEY (`' . lcfirst($field[2]) . 'Id`)<br/>';
                             echo $tab . $tab . 'REFERENCES `' . Config::$database . '`.`' . lcfirst($field[2]) . '` (`id`)<br/>';
                             echo $tab . $tab . 'ON DELETE CASCADE<br/>';
                             echo $tab . $tab . 'ON UPDATE NO ACTION);<br/>';               
                             echo $tab . '<br/>';
                            
                             $procname = 'get' . ucfirst($field[0])  . 'By' . $class->getName(). 'Id';
                             $classname = ucfirst($field[2]);    // class names start with uppercase
                             $classpath = '/../classes/'; 
                             $classfile = dirname(__FILE__) . $classpath . lcfirst($classname) . '.php';
                             include_once $classfile;
                             $subclass = new $classname($userID,$tenantID);
                             $subfieldarray = $subclass->getFields();
                             
                             echo 'USE `' . Config::$database . '`;<br/>';
                             echo 'DROP procedure IF EXISTS `' . $procname . '`;<br/><br/>';
                             echo 'DELIMITER $$<br/>';
                             echo 'USE `' . Config::$database . '`$$<br/><br/>';
                             echo 'CREATE PROCEDURE ' . $procname . '(_id int, _tenantid int, userid int)<br/>';
                             echo 'BEGIN<br/><br/>';
                             echo $tab . 'SELECT<br/>';
                             echo $tab . $tab . 'T1.id';
                             foreach ($subfieldarray as $subfield) {
                                 echo ',<br/>' . $tab . $tab . 'T1.' . $subfield[0];
                             }
                             echo $tab . '<br/>FROM<br/>';
                             echo $tab . $tab . lcfirst($field[2]) . ' T1<br/>';
                             echo $tab . $tab . 'INNER JOIN ' .  $tablename . ' T2 ON T1.id=T2.'.lcfirst($field[2]) . 'Id<br/>' ;
                             echo $tab . 'WHERE<br/>';
                             echo $tab . $tab . 'T2.' . lcfirst($class->getName()) . 'Id=_id<br/>';
                             echo $tab . $tab . 'and T1.tenantid=_tenantid;<br/><br/>';
                             echo 'END$$<br/>DELIMITER ;';
                             echo '<br/><br/>';
                             
                             
                             
                        }
                    }
                    
                    /* ADD proc */
                    echo 'USE `' . Config::$database . '`;<br/>';
                    echo 'DROP procedure IF EXISTS `add' . $class->getName() . '`;<br/><br/>';
                    echo 'DELIMITER $$<br/>';
                    echo 'USE `' . Config::$database . '`$$<br/><br/>';
                    echo 'CREATE PROCEDURE add' . $class->getName() . '(';
                    $fieldarray = $class->getFields();
                    $separator = "";
                    foreach ($fieldarray as $field) {
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0] . ' ';
                            $separator = ", ";
                        }
                        switch ($field[1]) {
                            case "string":
                            case "picklist":
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
                            case "linkedentity":
                                echo 'int';
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
                    echo $tab . 'INSERT INTO ' . lcfirst($class->getName()) . ' (<br/>';
                    $separator = $tab . $tab;
                    foreach ($fieldarray as $field) {
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0];
                            $separator = ',<br/>' . $tab . $tab;
                            }
                        }
                    echo ', tenantid)<br/>' . $tab . 'VALUES (';
                    $separator="";
                    foreach ($fieldarray as $field) {
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0] ;
                            $separator = ',<br/>' . $tab . $tab;
                            }
                        }
                    echo ', tenantid);';
                    echo '<br/><br/>';
                    echo $tab . 'SELECT Last_Insert_ID() as newID;';
                    echo '<br/><br/>';
                    echo 'END$$<br/>DELIMITER ;';
 
                    echo '<br/><br/>';
 
                    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                     echo 'USE `' . Config::$database . '`;<br/>';
                    echo 'DROP procedure IF EXISTS `update' . $class->getName() . '`;<br/><br/>';
                    echo 'DELIMITER $$<br/>';
                    echo 'USE `' . Config::$database . '`$$<br/><br/>';
                    echo 'CREATE PROCEDURE update' . $class->getName() . '(id int';
                    $fieldarray = $class->getFields();
                    $separator = ", ";
                    foreach ($fieldarray as $field) {
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0] . ' ';
                        }
                        switch ($field[1]) {
                            case "string":
                            case "picklist":
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
                            case "linkedentity":
                                echo 'int';
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
                        if ($field[1]!="linkedentities") {
                            echo $separator . $field[0] . ' = ' . $field[0];
                            $separator = ',<br/>' . $tab . $tab;
                            }
                        }
                    echo '<br/>';
                    echo $tab . 'WHERE';
                    echo '<br/>';
                    echo $tab . $tab . "id=id<br/>";
                    echo $tab . $tab . "AND tenantid=tenantid;<br/>";
                    echo 'END$$<br/>DELIMITER ;';
                    echo '<BR/></BR>/* End ' . $class->getName() . ' stored procs */<BR/><BR/>';
                    
                    ?>
                </code>
            </div>
       </div>  
       <?php include dirname(__FILE__) . '/../partials/footer.php';?>         
    </body>
</html>    
