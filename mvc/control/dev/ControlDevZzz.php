<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewDevZzz;

class ControlDevZzz extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/zzz', function () {
            self::PrivativeDevZone();
            ViewDevZzz::load('zzz');
        });
    }
}

?>