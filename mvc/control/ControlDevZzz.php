<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewDevZzz;

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