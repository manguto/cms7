<?php
namespace manguto\cms7\libraries;

class EmailAlternative
{

    //servico alternativo disparo email url    
    const form_action_url = 'http://manguto.com/mail/send.php';

    private $from;

    private $to;

    private $cc = '';

    private $cco = '';

    private $subject;

    private $content;

    private $password;

    // critico

    // ####################################################################################################
    public function __construct(string $from, string $to, string $cc, string $cco, string $subject, string $content, string $password)
    {
        Logger::proc("Inicilizacao da classe de controle para disparo de e-mails alternativos =>" . __METHOD__);

        $this->from = $from;
        $this->to = $to;
        $this->cc = $cc;
        $this->cco = $cco;
        $this->subject = $subject;
        $this->content = $content;
        $this->password = $password;
    }

    // ####################################################################################################
        
    /**
     * Tenta realizar o envio de e-mail com base nas informacoes informadas
     * e retorna TRUE em caso de sucesso e uma STRING com o erro no caso contrario.
     * 
     * @return boolean|string
     */
    public function send()
    {     
        if(EmailAlternative::checkAlternativeMailService()){
            Logger::proc("Tentativa de disparo de e-mail (assunto: $this->subject).");
            //=============================================================================
            {
                $s2s = new ServerToServer();
                {
                    $form_parameters = [
                        'from' => $this->from,
                        'to' => $this->to,
                        'cc' => $this->cc,
                        'cco' => $this->cco,
                        'subject' => $this->subject,
                        'content' => $this->content,
                        'password' => $this->password
                    ];
                }
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                $result = $s2s->setContent(self::form_action_url, $form_parameters);                
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            }                        
            //debc($result,0);
            if($result!==false && trim($result)==''){
                Logger::success("Envio de e-mail realizado com sucesso ($this->subject | from: $this->from | to:$this->to).");
                $result = true;
            }else{
                Logger::error("Não foi possível o envio do e-mail. Retorno obtido: '$result'");                
            }
        }else{
            $result = "O servidor alternativo de envio de mensagens de e-mail encontra-se inativo. Aguarde alguns instantes e tente novamente.";
            Logger::error($result);            
        }       
        return $result;
    }

    // ####################################################################################################

    /**
     * Verifica se o servido de envio de email alternativo esta operacional.
     */
    static function checkAlternativeMailService()
    {
        Logger::proc("Verificação de status de serviço de disparo de e-mails alternativo =>" . __METHOD__);
        {
            //implementar verificacao........
            $alternativeMailServiceTest = true;
        }

        if ($alternativeMailServiceTest) {
            Logger::success("Serviço alternativo de disparo de e-mails ATIVO.");
            return true;
        } else {
            Logger::error("Serviço alternativo de disparo de e-mails DESABILITADO.");
            return false;            
        }
    }
}

?>