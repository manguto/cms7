<?php
namespace manguto\cms5\lib;

class Sessions
{

    
    /**
     * Aloca variáveis/parametros na sessao atual do sistema em questao
     * @param string $key
     * @param string $value
     * @param bool $increment - define se é uma definicao ou um incremento de um array(array[])
     */
    static function set(string $key, $value,bool $arrayIncrement=false)
    {
        if($arrayIncrement==false){
            $_SESSION[SIS_FOLDERNAME][$key] = serialize($value);
        }else{
            if(!self::isset($key)){
                $variable = [];
            }else{
                $variable = self::get($key);
                if(!is_array($variable)){
                    throw new Exception("Variável da session previsa estar no formato de uma lista (array) para permitir o incremento solicitado! Contate o Administrador.");
                }
            }                        
            $variable[] = $value;
            self::set($key, $variable);
        }
        
    }

    /**
     * obtem parametro previamente alocado na sessao do sistema em questao
     *
     * @param string $key
     * @throws Exception
     * @return
     */
    static function get(string $key,$throwException=true)
    {
        
        if(self::isset($key)){
            return unserialize($_SESSION[SIS_FOLDERNAME][$key]);
        }else{
            if($throwException){
                throw new Exception("A variável solicitada ('$key') não foi encontrada na sessão.");
            }else{
                return false;
            }
        }
    }

    /**
     * remocao de um parametro da sessao
     *
     * @param string $key
     * @param bool $throwException
     * @throws Exception
     */
    static function unset(string $key = '',$throwException=false)
    {
        if(self::isset($key)){
            unset($_SESSION[SIS_FOLDERNAME][$key]);
        }else{
            if($throwException){
                throw new Exception("Foi solicitada a limpeza de uma variável da sessão, mas esta não foi encontrada ('$key').");
            }
        }
    }

    /**
     * verifica se o parametro esta definido na sessao
     *
     * @param string $key
     * @return bool
     */
    static function isset(string $key = ''): bool
    {
        return isset($_SESSION[SIS_FOLDERNAME][$key]);
    }
    
    /**
     * verifica se foi solicitada um reset da 
     * sessao e caso afirmativo realiza-o
     */
    static function checkResetRequest(){
                
        if(isset($_GET['reset'])){
            self::Reset();
        }        
    }
    
    /**
     * realiza um reset na sessao
     */
    static function Reset(){        
        session_destroy();
        session_start();
        ProcessResult::setSuccess('Sessão reinicializada com sucesso!');
        headerLocation('/');
        exit();
    }
   
    
}

?>