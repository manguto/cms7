<?php
namespace manguto\cms5\lib\model;

class Model_Control
{
    
    // indicador de referencia a outro objeto
    const ctrl_parameter_ini = '@';
        
    /**
     * verifica se o parametro eh de controle
     *
     * @param string $parameterName
     * @return bool
     */
    static function itsAControlParameter(string $parameterName): bool
    {
        { // parametro eh de controle?
            $parameterNameInitialPart = substr($parameterName, 0, strlen(self::ctrl_parameter_ini));
            if ($parameterNameInitialPart == self::ctrl_parameter_ini) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * remove parametros de controle de um array
     * @param array $array
     */
    static function RemoveControlAttributes(array $array){
        foreach (array_keys($array) as $attributeName){
            if(Model_Control::itsAControlParameter($attributeName)){
                unset($array[$attributeName]);
            }
        }
        return $array;
    }
    
    
    
}

?>