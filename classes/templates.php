<?php 

class Template {
    
    public static function getTemplate($entity,$key) {
        $template="";    
        switch ($entity) {
            case "patient":
                if ($key=="compact") {
                    $template = "<p>{{firstName}} {{middleName}} {{lastName}}</p>";
                }
                else {
                    $template = "<h1>{{lastName}}</b>, {{firstName}} {{middleName}} </h1>";
                    $template .='{{#addresses}}<p>{{addressLine1}}</p>';
                    $template .= '  {{#addressLine2}}<p>{{addressLine2}}</p>{{/addressLine2}}';
                    $template .= '<p>{{city}}, {{stateOrProvince}} {{postalCode}}</p>';
                    $template .= '{{/addresses}}';
                    
                }
                break;
        }
        
        return $template;           
    }
    
}
