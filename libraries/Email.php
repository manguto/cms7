<?php
namespace manguto\cms7\libraries;

/**
 * envio de mensagens de email
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
     * @param boolean $alternative_manguto_sendmail_password
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
            $mailResult = mail($to, $subject, $content, $headers);
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            if ($mailResult===true) {               
                Logger::success($mailResult);               
            } else {
                $mailResult = "Não foi possível o envio da mensagem de e-mail. Tente novamente em alguns instantes => ".error_get_last()['message'];
                Logger::error($mailResult);  
            }
            
        } else {
             
            //SERVICO DE EMAIL ALTERNATIVO!
            //SERVICO DE EMAIL ALTERNATIVO!
            //SERVICO DE EMAIL ALTERNATIVO!
            
            Logger::proc("Tentativa de envio através de servidor alternativo (to:$to/subject:$subject).");
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            $emailAlternativeServer = new EmailAlternative($from, $to, $cc, $cco, $subject, $content, $password);
            $mailResult = $emailAlternativeServer->send();
            //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            if ($mailResult===true) {                
                Logger::success($mailResult);
            } else {
                $mailResult = "$mailResult"; //Não foi possível o envio da mensagem de e-mail. Tente novamente em alguns instantes => 
                Logger::error($mailResult);
            }
        }
        
        return $mailResult;
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