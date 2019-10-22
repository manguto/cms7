<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewZzz;
use manguto\cms5\mvc\control\ControlDev;

class ControlZzz extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/zzz', function () {
            self::PrivativeDevZone();
            ViewZzz::load('zzz');
        });
    }
}

?>