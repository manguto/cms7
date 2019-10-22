<?php
namespace manguto\cms5\mvc\control;

class ControlSite extends Control 
{
    static function RunRouteAnalisys($app)
    { 
        
        { // VERIFICA/EXECUTA CLASSES FILHAS
            $classObjectSample = new self();
            //deb($classObjectSample);
            self::RunChilds($app, $classObjectSample);
        }
    }
}

?>