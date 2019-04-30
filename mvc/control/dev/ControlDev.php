<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\control\Control;
use manguto\cms5\lib\Logs;

class ControlDev extends Control
{

    static function RunRouteAnalisys($app)
    {
        Logs::CheckPoint();
        { // VERIFICA/EXECUTA CLASSES FILHAS
            $classObjectSample = new self();
            self::RunChilds($app, $classObjectSample);
        }
    }
}

?>