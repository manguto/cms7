<?php
namespace manguto\cms5\mvc\control\site;

use manguto\cms5\mvc\control\Control;
use manguto\cms5\lib\Logs;

class ControlSite extends Control 
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