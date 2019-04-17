<?php
namespace manguto\manguto\mvc\control;


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