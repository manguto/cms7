<?php
namespace manguto\cms5\lib;

class Sessions_DEPRECATED
{

    
    /**
     * aloca parametros na sessao atual do sistema em questao
     *
     * @param string $key
     * @param $value
     */
    static function set(string $key = '', $value = '')
    {
        if ($key == '' && $value == '') {            
            session_start();
        } else {
            $args = func_get_args();
            // deb($args);
            { // value
                $value = array_pop($args);
            }
            {
                $var = self::get_var($args);
            }
            {
                // deb($var,0); deb($value,0);
                $eval = "$var = \$value;";
                // deb($eval);
            }
            // <<<<<<<<<<<<<<<<<<<<<<
            eval($eval);
            // <<<<<<<<<<<<<<<<<<<<<<
        }
    }

    /**
     * obtem parametro previamente alocado na sessao do sistema em questao
     *
     * @param string $key
     * @throws Exception
     * @return
     */
    static function get(string $key = '')
    {
        {
            $args = func_get_args();
            //deb($args);
            $var = self::get_var($args);
            //deb($var);
        }
        {
            $eval = '$return = '.$var.';';
            //deb($eval,0);
        }
        //<<<<<<<<<<<<<<<<<<
        eval($eval);
        //<<<<<<<<<<<<<<<<<<
        
        //deb($return,0);
        return $return;
    }

    /**
     * remocao de um parametro da sessao
     *
     * @param string $key
     * @param bool $throwException
     * @throws Exception
     */
    static function unset(string $key = '')
    {
        {
            $args = func_get_args();
            // deb($args);
            $var = self::get_var($args);
        }
        {
            $eval = "
            if(isset($var)){
                unset($var);
            }else{
                throw new Exception(\"Parâmetro não definido na sessão para possível indefinição ('$key').\");
            }";
            //deb($eval);
        }
        //<<<<<<<<<<<<<<<<<<
        eval($eval);
        //<<<<<<<<<<<<<<<<<<
        
    }

    /**
     * verifica se o parametro esta definido na sessao
     *
     * @param string $key
     * @return bool
     */
    static function isset(string $key = ''): bool
    {
        {
            $args = func_get_args();
            // deb($args);
            $var = self::get_var($args);            
        }
        {
            $eval = '$return = isset('.$var.');';
            //deb($eval,0);            
        }
        
        //Exception::deb($eval);        
        
        //<<<<<<<<<<<<<<<<<<
        eval($eval);
        //<<<<<<<<<<<<<<<<<<
                
        //deb($return,0);
        return $return;
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
    
    
    // =============================================================================================================================
    // =============================================================================================================================
    // =============================================================================================================================
    // =============================================================================================================================
    /**
     * caso o valor não seja um inteiro,
     * coloca "'" antes e depois de cada parametro (já que nao sao ctes para correta utilizacao no array)
     *
     * @param array $args
     * @return array
     */
    static private function args_wrapping(array $args): array
    {
        foreach ($args as &$arg) {
            // verifica se eh um numero (ou nao)
            if (! Numbers::is_numeric_int($arg) && trim($arg) != '') {
                $arg = "'" . $arg . "'";
            }
        }
        return $args;
    }

    /**
     * retorno o nome da variavel a partir dos argumetos informados 
     * @param array $args
     * @return string
     */
    static private function get_var(array $args): string
    {
        //deb($args,0); throw new Exception();
        
        $args = self::args_wrapping($args);
        deb($args);

        $variable = '$_SESSION[SIS_ABREV][SIS_FOLDERNAME]'; 
        foreach ($args as $arg) { 
            $variable .= "[$arg]"; 
        }
        deb($variable);
        return $variable;
    }
    
}

?>