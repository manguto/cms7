<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewDevTools;

class ControlDevTools extends ControlDev
{

    static function Executar($app)
    {
        $app->get('/dev/tools', function () {
            self::PrivativeDevZone();
            ViewDevTools::load('tools');
        });        
        
    }
    
}

?>