<?php
namespace manguto\cms7\libraries;

class Alert
{

    const key = 'ALERT';
    
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################

    /**
     * Obtem um array com as mensagens registradas (em html)
     *
     * @param bool $unset_all
     * @return array
     */
    static function GET(bool $unset = false): array
    {
        $alerts = Sessions::get(self::key, false, $unset);
        if ($alerts !== false) {
            $return = $alerts;
        } else {
            $return = [];
        }
        return $return;
    }
   
    
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    /**
     * registra uma mensagem de perigo ou erro (danger)
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function setDanger($expection_or_message): string
    {   
        Logger::error('Alerta! '.chr(10).$expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ####################################################################################################
    /**
     * registra uma mensagem de alerta
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function setWarning($expection_or_message): string
    {
        Logger::warning('Alerta! '.chr(10).$expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ####################################################################################################
    /**
     * registra uma mensagem de informacao
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function setInfo($expection_or_message): string
    {
        Logger::info('Alerta! '.chr(10).$expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }
    // ####################################################################################################
    /**
     * registra uma mensagem de sucesso
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function setSuccess($expection_or_message): string
    {
        Logger::success('Alerta! '.chr(10).$expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ##########################################################################################################################################
    // ########################################################################################################################### STATIC PRIVATE
    // ##########################################################################################################################################

    /**
     * registra o tipo de mensagem e a propria
     *
     * @param string $function
     * @param string|Exception $expection_or_message
     */
    static private function SET(string $function, $expection_or_message)
    {   
        {
            { // obtem o tipo com base no nome da funcao informada
                $type = strtolower($function);
                $type = substr($type, 3);
            }
            { // verifica se o parametro informado eh do tipo exception ou string
                //debc($expection_or_message);
                if (is_object($expection_or_message)) {
                    $msg = $expection_or_message->getMessage();
                } else {
                    $msg = $expection_or_message;
                }
            }
            { // classes a serem utilizadas quando da exibicao
                $class = '';
                { // classe ajuste
                    $class .= " alert-$type ";
                }
                { // ocultacao automatica de todas mensagens exceto 'dangers'
                    if ($type != 'danger') {
                        $class .= ' hide ';
                    }
                }
            }
        }
        Sessions::set(self::key, [
            'type' => $type,
            'msg' => $msg,
            'class' => $class
        ], true);

        return $msg;
    }

    // ##########################################################################################################################################
    // ##########################################################################################################################################
    // ##########################################################################################################################################
}

?>