<?php
namespace manguto\cms5\mvc\model;

class ModelHelper
{

    const model_class_folders = [
        'sis/',
        'vendor/manguto/manguto/mvc/model/'
    ]; 
    
    
    /**
     * verifica se o parametro eh de controle
     *
     * @param string $parameterName
     * @return bool
     */
    static function ehParametroDeControle(string $parameterName): bool
    {
        { // parametro eh de controle?
            $parameterNameInitialPart = substr($parameterName, 0, strlen(self::ctrl_parameter_ini));
            if ($parameterNameInitialPart == self::ctrl_parameter_ini) {
                return true;
            }
        }
        
        return false;
    }
}

?>