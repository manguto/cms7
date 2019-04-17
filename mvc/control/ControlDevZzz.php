<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewDevZzz;

class ControlDevZzz extends ControlDev
{

    static function Executar($app)
    {
        $app->get('/dev/zzz', function () {
            self::PrivativeDevZone();
            ViewDevZzz::load('zzz');
        });
    }
}

?>