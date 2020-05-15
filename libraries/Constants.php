<?php
namespace manguto\cms7\libraries;

class Constants
{

    /**
     * verifica se a constante encontra-se definida
     *
     * @param string $constantName
     * @return bool
     */
    static function isset(string $constantName, bool $throwException = false): bool
    {
        if (defined($constantName)) {
            return true;
        } else {
            if ($throwException) {
                throw new Exception("A constante '$constantName' não foi encontrada (definida).");
            }
            return false;
        }
    }

    /**
     * verifica se a constante esta definida e caso contrario 
     * dispara uma excecao
     * @param string $constantName
     * @param bool $throwException
     * @param mixed $default
     * @return string
     */
    static function isset_get(string $constantName,bool $throwException = TRUE, $default = NULL)
    {   
        if (self::isset($constantName, $throwException)) {
            return constant($constantName);
        }else{
            if($default!=NULL){
                return $default;
            }else{
                throw new Exception("A constante '$constantName' não foi encontrada, e o valor padrão para retorno ('default') não definido.");
            }   
        }
    }

}


