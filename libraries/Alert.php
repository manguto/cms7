<?php
namespace manguto\cms7\libraries;

/**
 * classe de controle das mensagens exibidas ao usuario
 *
 * @author Marcos
 *        
 */
class Alert
{

    const key = 'ALERT';

    // tipos de erro que nao devem ser ocultados automaticamente
    const unhideable_types = [
        'error'
    ];

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
    static function Error($expection_or_message): string
    {
        Logger::error('Alerta! ' . chr(10) . $expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ####################################################################################################
    /**
     * registra uma mensagem de alerta
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function Warning($expection_or_message): string
    {
        Logger::warning('Alerta! ' . chr(10) . $expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ####################################################################################################
    /**
     * registra uma mensagem de informacao
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function Info($expection_or_message): string
    {
        Logger::info('Alerta! ' . chr(10) . $expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }

    // ####################################################################################################
    /**
     * registra uma mensagem de sucesso
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function Success($expection_or_message): string
    {
        Logger::success('Alerta! ' . chr(10) . $expection_or_message);
        return self::SET(__FUNCTION__, $expection_or_message);
    }
    // ####################################################################################################
    /**
     * registra uma mensagem de especial com classe configuravel
     * (alteracao cores e ocultacao automatica)
     *
     * @param string|Exception $expection_or_message
     * @return string
     */
    static function SPECIAL($message,string $class='alert-warning',$hide=false): string
    {
        Logger::special('ESPECIAL! ' . chr(10) . $message);
        return self::SET_SPECIAL($message,$class,$hide);
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
                $type_lowercase = strtolower($function);
            }
            { // verifica se o parametro informado eh do tipo exception ou string
              // debc($expection_or_message);
                if (is_object($expection_or_message)) {
                    $msg = $expection_or_message->getMessage();
                } else {
                    $msg = $expection_or_message;
                }
            }
            { // classes a serem utilizadas quando da exibicao
                $class = '';
                { // especificacao da classe da tag do alerta
                    
                    { // excecao! ajuste especifico do tipo 'error' para 'danger'
                        $class_type = $type_lowercase == 'error' ? 'danger' : $type_lowercase;
                    }
                    
                    $class .= " alert-$class_type ";
                }
                { // controle das mensagens que devem ser ocultadas automaticamente
                    if (in_array($type_lowercase, self::unhideable_types)==false) {
                        $class .= ' hide ';
                    }
                }
            }
        }
        Sessions::set(self::key, [
            'type' => $type_lowercase,
            'msg' => $msg,
            'class' => $class
        ], true);

        return $msg;
    }
    // ##########################################################################################################################################

    /**
     * registra o tipo de mensagem special
     *
     * @param string $function
     * @param string|Exception $expection_or_message
     */
    static private function SET_SPECIAL(string $message,$classes='',$hide=false)
    {
        {
            { // obtem o tipo com base no nome da funcao informada
                $type_lowercase = 'special';
            }
            { // classes a serem utilizadas quando da exibicao
                $classes = trim($classes);
                $classes = " $classes ";
                $classes .= ' alert ';                
                { // controle das mensagens que devem ser ocultadas automaticamente
                    if ($hide) {
                        $classes .= ' hide ';
                    }
                }
            }
        }
        Sessions::set(self::key, [
            'type' => $type_lowercase,
            'msg' => $message,
            'class' => $classes
        ], true);

        return $message;
    }

    // ##########################################################################################################################################
    // ##########################################################################################################################################
    // ##########################################################################################################################################
}

?>