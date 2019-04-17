<?php
namespace manguto\cms5\lib\cms;

class CMSDebug
{

    static function Start() {
     //check if debug mode ON...   
    }
    
    
    static function AddClassMethod($class_name,$class_method,$arguments) {
        
        //$rc = new \ReflectionClass($class_name);
        $rcm = new \ReflectionMethod($class_method);
        deb($rcm->getDocComment(),0); 
    }
    
    //...
    
}

?>