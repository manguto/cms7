<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewDevTools;

class ControlDevTools extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/tools', function () {
            self::PrivativeDevZone();
            ViewDevTools::load('tools');
        });
    }
}

?>