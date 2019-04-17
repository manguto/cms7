<?php
namespace manguto\cms5\mvc\model;


interface Registravel
{
    public function __construct($id=0);
    
    public function preLoad();
}


class ModelDatabase extends Model implements Registravel
{
    
    public function __construct($id=0) {
        
    }
    
    public function preLoad() {
            
    }
    
    
}

?>