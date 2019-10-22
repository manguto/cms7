<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\control\ControlDev;
use manguto\cms5\mvc\view\dev\ViewHome;

class ControlHome extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev', function () {
            self::PrivativeDevZone();
            ViewHome::load('index');
        });
    }
}

?>