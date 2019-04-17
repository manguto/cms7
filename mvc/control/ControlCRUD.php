<?php
namespace manguto\manguto\mvc\control;

class ControlCRUD extends Control 
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