<?php
namespace manguto\cms7\libraries;

class Sessions
{

    // define que as variaveis salvas precisam ser serializadas
    const serialize = false;

    // quantidade de níveis da localizacao deste arquivo ate a pasta principal da aplicacao
    const levels = 4;

    /**
     * Aloca variáveis/parametros na sessao atual do sistema em questao
     *
     * @param string $key
     * @param string $value
     * @param bool $increment
     *            - define se é uma definicao ou um incremento de um array(array[])
     * @throws Exception
     */
    static function set(string $key, $value, bool $arrayIncrement = false)
    {
        if ($arrayIncrement == false) {
            $APP_BASENAME = self::GET_APP_BASENAME();
            $_SESSION[$APP_BASENAME][$key] = self::serialize ? serialize($value) : $value;
        } else {
            if (! self::isset($key)) {
                $variable = [];
            } else {
                $variable = self::get($key);
                if (! is_array($variable)) {
                    throw new Exception("Não foi possível atualizar a variável '$key' da 'SESSION', pois esta não se encontra no formato de array!");
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
     * @throws Exception
     * @return mixed|boolean
     */
    static function get(string $key, bool $throwException = true, bool $unset = false)
    {
        if (self::isset($key)) {
            $APP_BASENAME = self::GET_APP_BASENAME();
            $return = $_SESSION[$APP_BASENAME][$key];
            $return = self::serialize ? unserialize($return) : $return;

            if ($unset) {
                self::unset($key, $throwException, $APP_BASENAME);
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
    static function unset(string $key, $throwException = false)
    {
        if (self::isset($key)) {
            $APP_BASENAME = self::GET_APP_BASENAME();
            unset($_SESSION[$APP_BASENAME][$key]);
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
    static function isset(string $key): bool
    {
        $APP_BASENAME = self::GET_APP_BASENAME();
        return isset($_SESSION[$APP_BASENAME][$key]);
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
    static function Reset(bool $multipleSessionsData = false): bool
    {
        
        //deb($APP_BASENAME);
        if ($multipleSessionsData == true) {
            unset($_SESSION);
            Alert::setSuccess('Sessão reinicializada (completamente) com sucesso!');            
        } else {
            $APP_BASENAME = self::GET_APP_BASENAME();
            if (isset($_SESSION[$APP_BASENAME])) {
                unset($_SESSION[$APP_BASENAME]);                
                Alert::setSuccess("Sessão da aplicação '$APP_BASENAME' reinicializada com sucesso!");
            } else {
                Alert::setWarning("Não foi encontrada uma sessão para a aplicação '$APP_BASENAME'. Procedimento desnecessário!");
            }
        }
        return true;
    }

    static private function GET_APP_BASENAME()
    {
        $dir = __DIR__;
        $dir_main = dirname($dir, self::levels);
        $basename = basename($dir_main);
        // deb("$dir => $dir_main => $basename");
        return $basename;
    }
}

?>