<?php
namespace manguto\cms5\mvc\control;


class ControlSite extends Control 
{
    static function Executar($app)
    { 
        { // VERIFICA/EXECUTA CLASSES FILHAS
            $classObjectSample = new self();            
            self::ExecutarClassesFilhas($app, $classObjectSample);
        }
    }
}

?>