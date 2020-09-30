<?php
namespace manguto\cms7\libraries;

/**
 * Envio de mensagens de e-mail principalmente por via direta e secundariamente por via alternativa (manguto sendmail service) 
 * @author Marcos
 *
 */
class Email
{

    /**
     * Envia mensagem de email com as informacoes fornecidas.
     * @param string $from
     * @param string $to
     * @param string $cc
     * @param string $cco
     * @param string $subject
     * @param string $content
     * @param boolean $password - manguto sendmail service password
     * @return string|bool
     */
    static function Enviar(string $from, string $to, string $cc, string $cco, string $subject, string $content, bool $password = false)
    {
        Logger::proc("Envio de E-mail => " . __METHOD__);
        
        if (self::CheckServerMailService()) {
            
            //SERVICO DE EMAIL OFICIAL (SERVER)
            //SERVICO DE EMAIL OFICIAL (SERVER)
            //SERVICO DE EMAIL OFICIAL (SERVER)
            
            { // parameters
                $headers = "From: $from \r\n";
                $headers .= "Reply-To: $from \r\n";
                $headers .= $cc != "" ? "CC: $cc\r\n" : "";
                $headers .= $cco != "" ? "CCO: $cco\r\n" : "";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            }
            Logger::proc("Tentativa de envio pelo servidor próprio (to:$to/subject:$subject).");            
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            $return = mail($to, $subject, $content, $headers);
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            if ($return===true) {               
                Logger::success("E-mail enviado com sucesso!");               
            } else {
                $return = "Não foi possível o envio da mensagem de e-mail. Tente novamente em alguns instantes => ".error_get_last()['message'];
                Logger::error($return);  
            }
            
        } else {
             
            //SERVICO DE EMAIL ALTERNATIVO!
            //SERVICO DE EMAIL ALTERNATIVO!
            //SERVICO DE EMAIL ALTERNATIVO!
            
            Logger::proc("Tentativa de envio através de servidor alternativo (to:$to/subject:$subject).");
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            $emailAlternativeServer = new EmailMSS($from, $to, $cc, $cco, $subject, $content, $password);
            $return = $emailAlternativeServer->send();
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            if ($return===true) {                
                Logger::success("E-mail enviado com sucesso! (to:$to)");
            } else {
                $return = strval($return); //Não foi possível o envio da mensagem de e-mail. Tente novamente em alguns instantes => 
                Logger::error($return);
            }
        }
        
        return $return;
    }
    
    // ####################################################################################################
    

    /**
     * Verifica se o servido de envio de email
     * esta configurado no servidor.
     */
    static function CheckServerMailService()
    {
        Logger::proc("Verificação de status de serviço de disparo de E-mails =>" . __METHOD__);
        $all = ini_get_all();
        $sendmail_path = $all['sendmail_path'];
        $global_value = $sendmail_path['global_value'];
        $local_value = $sendmail_path['local_value'];
        // deb($global_value,0); deb($local_value);
        if ($global_value == NULL && $local_value == NULL) {
            Logger::error("Serviço de disparo de e-mails DESABILITADO.");
            return false;
        } else {
            Logger::success("Serviço de disparo de e-mails HABILITADO.");
            return true;
        }
    }
}

?>