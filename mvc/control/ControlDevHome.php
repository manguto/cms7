<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewDevHome;

class ControlDevHome extends ControlDev
{
    static function RunRouteAnalisys($app)
    {   
        
            $app->get('/dev', function () {
                self::PrivativeDevZone();
                ViewDevHome::load('index');                
            });
                
    }
    
}

?>