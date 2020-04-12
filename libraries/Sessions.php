<?php
namespace manguto\cms7\libraries;
 
class Sessions
{

    /**
     * Aloca variáveis/parametros na sessao atual do sistema em questao
     *
     * @param string $key
     * @param string $value
     * @param bool $increment
     *            - define se é uma definicao ou um incremento de um array(array[])
     * @param string $APP_FOLDERNAME
     *            - pasta do sistema em questão ('' => sistema atual)
     * @throws Exception
     */
    static function set(string $key, $value, bool $arrayIncrement = false, string $APP_FOLDERNAME = '')
    {
        $APP_FOLDERNAME = $APP_FOLDERNAME == '' ? APP_FOLDERNAME : $APP_FOLDERNAME;

        if ($arrayIncrement == false) {

            $_SESSION[$APP_FOLDERNAME][$key] = serialize($value);
        } else {
            if (! self::isset($key)) {
                $variable = [];
            } else {
                $variable = self::get($key);
                if (! is_array($variable)) {
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
     * @param bool $throwException
     * @param bool $unset
     * @param string $APP_FOLDERNAME
     * @throws Exception
     * @return mixed|boolean
     */
    static function get(string $key, bool $throwException = true, bool $unset = false, string $APP_FOLDERNAME = '')
    {
        $APP_FOLDERNAME = $APP_FOLDERNAME == '' ? APP_FOLDERNAME : $APP_FOLDERNAME;

        if (self::isset($key)) {
            $return = unserialize($_SESSION[$APP_FOLDERNAME][$key]);
            if($unset){
                self::unset($key,$throwException,$APP_FOLDERNAME);
            }
            
        } else {
            if ($throwException) {
                throw new Exception("A variável solicitada ('$key') não foi encontrada na sessão.");
            } else {
                $return = false;
            }
        }
        return $return;
    }

    /**
     * remocao de um parametro da sessao
     *
     * @param string $key
     * @param bool $throwException
     * @throws Exception
     */
    static function unset(string $key = '', $throwException = false, string $APP_FOLDERNAME = '')
    {
        $APP_FOLDERNAME = $APP_FOLDERNAME == '' ? APP_FOLDERNAME : $APP_FOLDERNAME;

        if (self::isset($key)) {
            unset($_SESSION[$APP_FOLDERNAME][$key]);
        } else {
            if ($throwException) {
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
    static function isset(string $key = '', string $APP_FOLDERNAME = ''): bool
    {
        $APP_FOLDERNAME = $APP_FOLDERNAME == '' ? APP_FOLDERNAME : $APP_FOLDERNAME;
        return isset($_SESSION[$APP_FOLDERNAME][$key]);
    }

    /**
     * Verifica se foi solicitada o reset da sessao, e caso afirmativo realiza-o.
     */
    static function checkResetRequest()
    {
        if (isset($_GET['reset'])) {
            self::Reset();
        }
    }

    /**
     * realiza um reset na sessao
     */
    static function Reset()
    {
        session_destroy();
        session_start();
        ProcessResult::setSuccess('Sessão reinicializada com sucesso!');
        return true;
    }
}

?>