<?php
namespace manguto\cms5\mvc\control\site;

use manguto\cms5\mvc\control\Control;

class ControlSite extends Control 
{
    static function RunRouteAnalisys($app)
    { 
        
        { // VERIFICA/EXECUTA CLASSES FILHAS
            $classObjectSample = new self();            
            self::RunChilds($app, $classObjectSample);
        }
    }
}

?>