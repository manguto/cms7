<?php
namespace manguto\cms5\mvc\control\dev;


use manguto\cms5\mvc\control\ControlDev;
use manguto\cms5\mvc\view\dev\ViewTools;
class ControlTools extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/tools', function () {
            self::PrivativeDevZone();
            ViewTools::load('tools');
        });
    }
}

?>