<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewDevHome;

class ControlDevHome extends ControlDev
{
    static function Executar($app)
    {   
        
            $app->get('/dev', function () {
                self::PrivativeDevZone();
                ViewDevHome::load('index');                
            });
                
    }
    
}

?>