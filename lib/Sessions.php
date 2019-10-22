<?php
namespace manguto\cms5\lib;

class Sessions
{

    /**
     * Aloca variáveis/parametros na sessao atual do sistema em questao
     * @param string $key
     * @param string $value
     * @param bool $increment - define se é uma definicao ou um incremento de um array(array[])
     * @param string $SIS_FOLDERNAME - pasta do sistema em questão ('' => sistema atual)
     * @throws Exception
     */
    static function set(string $key, $value, bool $arrayIncrement=false, string $SIS_FOLDERNAME='')
    {        
        $SIS_FOLDERNAME = $SIS_FOLDERNAME=='' ? SIS_FOLDERNAME : $SIS_FOLDERNAME;
        
        if($arrayIncrement==false){
            
            $_SESSION[$SIS_FOLDERNAME][$key] = self::wrapValue($value);            
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
    static function get(string $key,$throwException=true,string $SIS_FOLDERNAME='')
    {
        $SIS_FOLDERNAME = $SIS_FOLDERNAME=='' ? SIS_FOLDERNAME : $SIS_FOLDERNAME;
        
        if(self::isset($key)){            
            return self::wrapValue($_SESSION[$SIS_FOLDERNAME][$key],true);
            
        }else{
            if($throwException){
                throw new Exception("A variável solicitada ('$key') não foi encontrada na sessão.");
            }else{
                return false;
            }
        }
    }

    /**
     * codifica/decodifica valor a ser guardado na sessao 
     * @param mixed $value
     * @param bool $unwrap
     * @return mixed
     */
    static private function wrapValue($value, bool $unwrap=false){
        if($unwrap){
            return unserialize($value);
        }else{
            return serialize($value);
        }
    }
    
    /**
     * remocao de um parametro da sessao
     *
     * @param string $key
     * @param bool $throwException
     * @throws Exception
     */
    static function unset(string $key = '',$throwException=false,string $SIS_FOLDERNAME='')
    {   
        $SIS_FOLDERNAME = $SIS_FOLDERNAME=='' ? SIS_FOLDERNAME : $SIS_FOLDERNAME;
        
        if(self::isset($key)){
            unset($_SESSION[$SIS_FOLDERNAME][$key]);
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
    static function isset(string $key = '',string $SIS_FOLDERNAME=''): bool
    {
        $SIS_FOLDERNAME = $SIS_FOLDERNAME=='' ? SIS_FOLDERNAME : $SIS_FOLDERNAME;
        return isset($_SESSION[$SIS_FOLDERNAME][$key]);
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
    static function Reset($redirecionar=true){        
        session_destroy();
        session_start();        
        if($redirecionar){
            ProcessResult::setSuccess('Sessão reinicializada com sucesso!');
            headerLocation('/');
            exit();
        }        
    }
   
    
}

?>