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
                }
                break;
        }
        
        return $template;           
    }
    
}
