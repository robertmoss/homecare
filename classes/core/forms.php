<?php

include_once dirname(__FILE__) . '/database.php';
include_once dirname(__FILE__) . '/../config.php';
include_once dirname(__FILE__) . '/log.php';
include_once dirname(__FILE__) . '/cache.php';
include_once dirname(__FILE__) . '/tenant.php';

/* 
 * a utility class for managing and rendering forms from data entities
 */

class Forms {
     
     public static function renderForm($class, $entity, $id, $tenantID, $parentid) {
        
            $fieldarray = $class->getFields();
            $hasImage = false;
            foreach ($fieldarray as $field) {
                    $value='';
                    if ($id>0 && isset($entity[$field[0]])) {$value = $entity[$field[0]];}
                    $required = $class->isRequiredField($field[0]) ? 'required' : '';
                    $readonly='';
                    if (!$class->isUpdatableField($field[0])) {
                        $readonly = ' readonly';
                    } 
                    
                    $default_label = '<label class="col-sm-3 control-label" for="txt' . $field[0] . '">' . $class->friendlyName($field[0]) .':</label>';
                    if ($class->isClickableUrl($field[0])) {
                        // add link to label
                        $url = 'getElementById(\'txt' . $class->getName() . ucfirst($field[0]) . '\').value';
                        $default_label ='<a onclick="window.open('. $url . ');" target="_blank">' . $default_label . '</a>';                        
                    }
                    
                    switch ($field[1]) {
                        case "string":  
                            echo '<div class="form-group">';
                            $maxlen = '';
                            $collength = 6; 
                            if (count($field)>2) {
                                // add a max-length validator
                                $maxlen = 'maxlength="' . $field[2] . '"';
                                if ($field[2]<50) {
                                    $collength = 2;
                                }
                            }
                            echo $default_label;
                            echo '  <div class="col-sm-' .$collength .'">';
                            if (count($field)>2 && $field[2]>200) {
                                echo '     <textarea rows="4" cols="100" id="txt' . $class->getName() . ucfirst($field[0]) . '" name="' . $field[0] . '"  class="form-control" placeholder="'. $class->friendlyName($field[0]) .'" ' . $maxlen . ' ' . $required . '>';
                                echo $value . '</textarea>';
                                }
                            else {
                                echo '     <input id="txt' . $class->getName() . ucfirst($field[0]) . '" name="' . $field[0] . '" type="text" class="form-control" placeholder="'. $class->friendlyName($field[0])  .'" value="' . $value . '" ' . $maxlen . ' ' . $required .  $readonly . '/>';
                                if ($class->isRequiredField($field[0])) {
                                    echo '<span class="glyphicon form-control-feedback glyphicon-asterisk" aria-hidden="true"></span>';
                                }
                             }
                                
                            echo '  </div>';
                            echo '  <div class="help-block with-errors"></div>';
                            echo '</div>';
                            break;
                        case "date":    
                            echo '<div class="form-group">';
                            echo $default_label;
                            echo '  <div class="col-sm-6"><input id="txt' . $field[0] . '" name="' . $field[0] . '" type="text" class="form-control" placeholder="'. $field[0] .'" value="' . $value . '"/></div>';
                            echo '  <div class="help-block with-errors"></div>';
                            echo '</div>';
                            break;
                        case "number":
                            echo '<div class="form-group">';
                            echo $default_label;
                            $css_class = $field[2] < 10 ? 'col-sm-1' : 'col-sm-2';
                            echo '  <div class="' . $css_class . '"><input id="txt' . $class->getName() . ucfirst($field[0]) . '" name="' . $field[0] . '" type="text" class="form-control" placeholder="'. $field[0] . '" value="' . $value . '"/></div>';
                            echo '  <div class="help-block with-errors"></div>';
                            echo '</div>';
                            break;
                        case "boolean":
                            echo '<div class="form-group">';
                            echo $default_label;
                            $css_class = 'col-sm-1';
                            $checked = ($value) ? 'checked' : '';
                            echo '  <div class="' . $css_class . '"><input id="txt' . $class->getName() . ucfirst($field[0]) . '" name="' . $field[0] . '" type="checkbox" class="form-control" ' . $checked . '/></div>';
                            echo '  <div class="help-block with-errors"></div>';
                            echo '</div>';
                            break;
                        case "picklist":
                            echo '<div class="form-group">';
                            echo $default_label;
                            $size = $field[2]<4 ? 'col-sm-2' : 'col-sm-6';
                            echo '<div class="' . $size . '"><select id="txt' . $class->getName() . ucfirst($field[0]) . '" name="' . $field[0] . '" class="form-control">';
                            $list = Utility::getList($field[3],$tenantID,0);
                            foreach ($list as $r) {
                                $selected = "";
                                if ($id>0 && $r[0]==$entity[$field[0]]) {
                                    $selected = "selected";
                                    }
                                echo '<option value="' . $r[0].'"' . $selected . '>' . $r[1] . '</option>';
                                }
                            echo '</select></div>';
                            echo '  <div class="help-block with-errors"></div>';
                            echo '</div>';
                            break;
                        case "linkedentity":
                            echo '<div class="form-group">';
                            echo $default_label;
                            echo '<div class="col-sm-6"><select id="' . $field[0] . '" name="' . $field[0] . '" class="form-control">';
                            $list = Utility::getList($field[3],$tenantID);
                            foreach ($list as $r) {
                                $selected = "";
                                if (($id>0 && $r[0]==$entity[$field[0]]) || ($id==0 && $r[0]==$parentid)) {
                                    $selected = "selected";
                                    }
                                echo '<option value="' . $r[0].'"' . $selected . '>' . $r[1] . '</option>';
                                }
                            echo '</select></div>';
                            if (isset($field[4])) {
                                echo '<a href="#add' . $field[0]  . '" onclick="addSubEntity(\'add' . $field[0]  . '\');">Add New</a>'; 
                                }
                            echo '</span></div>';
                            break;
                        case "image":
                            //echo '<div class="row">';
                            //echo '<span class="label">' . $field[0] . ':</span>';
                            //echo '<span class="input"><input id="file' . $field[0] . '" name="file" type="file" placeholder="'. $field[0] .'" value="' . $value . '" readonly></span>';
                            echo '<input id="txt' . $field[0] . '" name="' . $field[0] . '" type="hidden" value=""/>';
                            //echo '</div>';
                            $hasImage = true;
                            break;  
                        case "viewonly":
                            echo '<div class="form-group">';
                            echo $default_label;
                            echo '  <span class="">' . $value . '</span>';
                            echo '</div>';
                            break;
                        case "hidden":
                            if ($class->isParentId($field[0])) {
                                $value = $parentid;
                            }
                            echo '<input type="hidden" id="txt' . $field[0] . '" name="' . $field[0] . '" value="' . $value . '"/>';
                            break;
                        case "childentities":
                            echo '<div class="panel panel-info">';
                            echo '   <div class="panel-heading"><div class="col-sm-2">'. ucfirst($field[0]) . '</div>';
                            $subform='';
                            echo '&nbsp;<button type="button" class="btn btn-default" onclick="createChildEntity(\''. $field[2] . '\', \'' . Config::$service_path . '\');">';
                            echo '<span title="add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add</button>';  
                            echo '</div>';
                            echo '   <div class="panel-body">';
                            //$options='';
                            $rows='';
                            if ($entity && array_key_exists($field[0],$entity)) {
                                foreach($entity[$field[0]] as $child) {
                                    // this assumes all child entities have an id and a name - safe assumption?
                                    //$options .= '<option value=' . $child['id'] . ' selected>' . $child['name'] . '</option>';
                                    $rows .= '<tr>
                                                   <td><div class="user"><span class="description">' . $child['name'] . '</span></div></td>
                                                   <td><div class="btn-group btn-group-sm" role="group" aria-label="...">
                                                           <button type="button" class="btn btn-default" onclick="editChildEntity(\'' . $field[2] .  '\',' . $child['id'] . ');"><span class="glyphicon glyphicon-pencil"></span>&nbsp;</button>
                                                           <button type="button" class="btn btn-default" onclick="deleteChildEntity(\'' . $field[2] .  '\',' . $child['id'] . ');"><span class="glyphicon glyphicon-remove"></span>&nbsp;</button>
                                                           <div id="workingDelete'.$child['id']. '" class="hidden"></div> 
                                                       </div></td></tr>'; 
                                }
                            }
                            echo '<table class="table table-striped table-hover table-responsive">';
                            //echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead>';
                            echo '<tbody>' . $rows;
                            echo '</tbody></table>';

                            echo '   </div>';
                            echo '</div>';
                            break;
                        case "linkedentities":
                            // need to render special handling for linked entities, differing depending on whether user can add or not when editing parent entity
                            echo '<div id="linkedentities' . ucfirst($field[0]) . '" class="panel panel-info">';
                            echo '   <div class="panel-heading"><div class="col-sm-4">'. $class->friendlyName($field[0]) . '</div>';
                            $subform='';
                            if (!$field[3]) {
                                $child_array = $class->getAvailableChildren($field[0],$tenantID);
                                $selectName = '';
                                $options='';
                                foreach($child_array as $c) {
                                    $options .= '<option value="'. $c['id'] .'">'. $c['name'] .'</option>';
                                    }
                                $selectName = 'add' . $field[0];
                                echo '<div class="col-sm-3"><select id="' . $selectName . '" name="' . $selectName . '" class="form-control">' . $options . '</select></div>';
                                echo '&nbsp;<button type="button" class="btn btn-default" onclick="addChildEntity('. $selectName .','. $field[0] .');">';
                                echo '<span title="add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add</button>';                          
                                }
                            else {
                                    // add model form to allow user to create new child entities
                                    //$subform = Utility::renderChildModal($field[2]);
                                    // This will be handled afterwords so forms don't get nested
                                    echo '&nbsp;<button type="button" class="btn btn-default" onclick="createChildEntity(\''. $field[2] .'\');">';
                                    echo '<span title="add" class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add</button>';  
                            }   
                            echo '</div>';
                            echo '   <div class="panel-body">';
                            $options='';
                            if ($entity && array_key_exists($field[0],$entity)) {
                                foreach($entity[$field[0]] as $child) {
                                    // this assumes all child entities have an id and a name - safe assumption?
                                    $options .= '<option value=' . $child['id'] . ' selected>' . $child['name'] . '</option>';
                                }
                            }
                            echo '      <select id="' . $field[2] . 'Select" name="' . $field[0] . '" class="form-control" multiple>' . $options . '</select>';
                            echo '   </div>';
                            echo '</div>';
                            break;
                        case "custom":
                            // call into entity class for its edit field
                            echo $class->getCustomEditControl($field[0],$entity[$field[0]],$tenantID);
                            break;
                        case "properties":
                            $prop = array();
                            if ($entity && array_key_exists("properties", $entity)) {
                                $prop = $entity["properties"];
                            }                            
                            foreach($class->getPropertyKeys($tenantID) as $key) {
                                // find property value matching key
                                $value='';
                                foreach($prop as $p) {
                                    if ($p['key']==$key[0]) {
                                        $value=$p['value'];
                                        break;
                                    }
                                }
                                
                                echo '<div class="form-group">';
                                echo '<label class="col-sm-4 control-label" for="txtPROP-' . $key[0] . '">' . $key[0] .':</label>';
                                echo '  <div class="col-sm-6">';
                                echo '     <input id="txtPROP-' . $key['0'] . '" name="PROP-' . $key[0] . '" type="text" class="form-control" placeholder="'. $key[0] .'" value="' . $value . '"/>';
                                echo '  </div>';
                                echo '  <div class="help-block with-errors"></div>';
                                echo '</div>';
                            }
                            break;
                        default:
                            echo '<p>Unknown field type:' . $field[1];
                            }
                        echo $class->getCustomFormControl($field[0],$tenantID,$entity);
                        }


        }

        public static function renderMultifileUpload($url,$prompt,$buttonText) {
                
            echo '<form action="' . $url .'" method="post" enctype="multipart/form-data">';
            echo $prompt;
            echo '<input name="userfile[]" type="file" multiple/><br />';
            echo '<input type="submit" value="' . $buttonText . '" />';
            echo '</form>';
            
        }

        public static function renderChildModal($class) {
            
            // determines whether class requires a modal for creating childentities fields and, if so, renders it
            $fieldarray = $class->getFields();
            $requiresModal = false;
            foreach ($fieldarray as $field) {
                if ($field[1]=="childentities" && $field[3]) {
                    $requiresModal = true;
                    break;
                }
            }
            
            if ($requiresModal) {
                $entityName = "child";
                $markup = '<div id="' . $entityName . 'EditModal" class="modal fade" role="dialog">';
                $markup .= '  <div class="modal-dialog modal-lg">';
                $markup .= '    <div class="modal-content">';
                $markup .= '        <div class="modal-header">';
                $markup .= '         <button type="button" class="close" data-dismiss="modal">&times;</button>;';
                $markup .= '         <h4 id="' . $entityName . 'EditHeader" class="modal-title">Modal Header</h4>';
                $markup .= '      </div>';
                $markup .= '      <div id="' . $entityName . 'EditBody" class="modal-body">';
                $markup .= '        <div id="' . $entityName . 'EditLoading" class="ajaxLoading">';
                $markup .= '        </div>';
                $markup .= '        <div id="' . $entityName . 'Container" class="container-fluid">';
                $markup .= '            <p>Loading information...</p>';
                $markup .= '        </div>';
                $markup .= '      </div>';
                $markup .= '      <div class="modal-footer">';
                $markup .= '        <div id="' . $entityName . 'MessageDiv" class="message hidden">';
                $markup .= '            <span id="' . $entityName . 'MessageSpan"><p>Your message here!</p></span>';
                $markup .= '        </div>';
                $markup .= '        <button type="button" class="btn btn-default" onclick="cancelChild();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel</button>';
                $markup .= '        <button id="' . $entityName . 'EditSaveButton" type="button" class="btn btn-primary" onclick="saveLocation();" disabled>';
                $markup .= '            <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Save';
                $markup .= '        </button>';
                $markup .= '      </div>';
                $markup .= '    </div>';            
                $markup .= '  </div>';
                $markup .= '</div>';
            
                echo $markup;
                }
                
        }   
        
}

    