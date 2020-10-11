<?php
namespace manguto\cms7\libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Envio de mensagens de e-mail via SMTP
 *
 * @author Marcos
 *        
 */
class PersonalPHPMailer
{

    /**
     *
     * @var PHPMailer
     */
    private $mail;

    private $host;

    private $username;

    private $port;

    private $email_on_production = false;

    private $from_username_show;

    private $to;

    private $subject;

    private $cc;

    private $bcc;

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################

    /**
     * construtor classe envio emails
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $port
     */
    public function __construct(string $host, string $username, string $password, int $port = 465)
    {
        
        Logger::info("Inicialização!");
        
        $this->host = $host;

        $this->username = $username;

        $this->port = $port;

        // Instantiation and passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        // Server settings
        {
            { // credentials

                // Set the SMTP server to send through
                $this->mail->Host = $this->host;

                // SMTP username
                $this->mail->Username = $this->username;

                // SMTP password
                $this->mail->Password = $password;
            }
            { // debug
                $this->mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output
            }
            { // safety

                // Send using SMTP
                $this->mail->isSMTP();

                // Enable SMTP authentication
                $this->mail->SMTPAuth = true;

                // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

                // TCP port to connect to (use 465 for `PHPMailer::ENCRYPTION_SMTPS` or 587 for PHPMailer::ENCRYPTION_STARTTLS)
                $this->mail->Port = $this->port;
            }
        }
    }

    // ####################################################################################################
    public function createEmailMessage(string $from_username_show, string $to, string $subject, string $body, string $altbody = '', string $cc = '', string $bcc = '')
    {
        Logger::info("Produção da mensagem de e-mail (Para: $to | Assunto: $subject)");
        
        $this->email_on_production = true;
        {
            $this->from_username_show = $from_username_show;
            $this->to = $to;
            $this->subject = $subject;
            $this->body = $body;
        }

        // From email address and name
        $this->mail->setFrom($this->username, $from_username_show);

        // Recipent reply address
        $this->mail->addReplyTo($this->username, $from_username_show);

        { // To email addresss (recipient)
            $this->addRecipients('to', trim($to));
        }

        { // cc
            $this->addRecipients('cc', trim($cc));
        }

        { // bcc
            $this->addRecipients('bcc', trim($bcc));
        }

        { // subject
            $this->mail->Subject = $this->subject;
        }

        { // Content
          // Set email format to HTML
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
        }
    }

    // ####################################################################################################

    /**
     * anexar um arquivo
     *
     * @param string $filename
     * @param string $showname
     * @return bool
     */
    public function addAttachments(string $filename, string $showname = NULL): bool
    {
        Logger::info("Adicionando anexo ($filename)");
        
        if ($this->email_on_production) {
            $return = $this->mail->addAttachment($filename, $showname ?? $filename);
            Logger::info("Anexo ($filename) adicionado.");
            return $return;
        } else {
            throw new Exception("Não foi possível anexar o arquivo ($filename)! Nenhuma mensagem de e-mail em produção no momento.");
        }
    }

    // ####################################################################################################
    /**
     * envia a mensagem de email
     *
     * @throws Exception
     * @return bool
     */
    public function sendEmailMessage(): bool
    {
        try {
            Logger::info("Tentativa de envio de e-mail iniciada");
            if ($this->email_on_production) {
                $this->mail->send();
                Logger::success("E-mail enviado com sucesso!");
            } else {
                throw new Exception("Não foi possível enviar a mensagem! Não foi encontrada nenhuma mensagem de e-mail em produção no momento.");
            }
        } catch (Exception $e) {
            $msg = "Não foi possível enviar a mensagem solicitada. Em breve tente novamente.";
            $msg_error = "MAIL ERROR INFO: {$this->mail->ErrorInfo}";
            throw new Exception($msg . ' ' . $msg_error);
        }
        return true;
    }

    // ####################################################################################################
    // ########################################################################################### private
    // ####################################################################################################
    /**
     * adiciona o_s destinatario_s conforme o_s seu_s tipo_s.
     * (ex.: aaa@mail.com, bbb@mail.com, ...)
     *
     * @param string $type
     * @param string $recipients
     * @throws Exception
     */
    private function addRecipients(string $type, string $recipients)
    {
        $recipients = trim($recipients);

        if ($recipients != '') {

            $recipient_array = [];
            { // configuracao array com destinatarios
                if (strpos($recipients, ',') !== false) {
                    $recipient_array = explode(',', $recipients);
                } else {
                    $recipient_array[] = trim($recipients);
                }
            }

            { // adicao dos destinatarios
                { // definicao do metodo a ser utilizado
                    switch ($type) {
                        case 'to':
                            $method = 'addAddress';
                            break;
                        case 'cc':
                            $method = 'addCC';
                            break;
                        case 'bcc':
                            $method = 'addBCC';
                            break;
                        default:
                            throw new Exception("Não foi possível tratar o tipo de destinatário informado ($type).");
                            break;
                    }
                }
                foreach ($recipient_array as $recipient_temp) {
                    $this->mail->$method(trim($recipient_temp));
                }
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    static function test()
    {
        $email = new PersonalPHPMailer('smtp.gmail.com', 'nti.uast.suporte@gmail.com', 'rapadura');
        $email->createEmailMessage('Suporte NTI', 'marcosagtorres@gmail.com', 'Teste ' . date('d/m/Y H:i:s'), 'Teste <b>Bold</b> <i>Italic</i> <hr/>' . date('d/m/Y H:i:s'));
        $email->addAttachments('public/img/back.png');
        $result = $email->sendEmailMessage();
        return $result;
    }
}

?>