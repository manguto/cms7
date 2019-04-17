<?php
namespace manguto\manguto\lib\cms;

use Rain\Tpl;


class CMSMailer
{

    private $USERNAME;
    private $PASSWORD;
    private $NAME_FROM;
    private $EMAIL_FROM;
    private $mail;

    
    public function __construct(string $toAddress, string $toName, string $subject, string $tplName, array $data = array(), string $GMAIL_USERNAME = '', string $GMAIL_PASSWORD = '', string $NAME_FROM = '', string $EMAIL_FROM = '')
    {
        $this->USERNAME = $GMAIL_USERNAME == '' ? GMAIL_USERNAME : $GMAIL_USERNAME;
        $this->PASSWORD = $GMAIL_PASSWORD == '' ? GMAIL_PASSWORD : $GMAIL_PASSWORD;
        $this->NAME_FROM = $NAME_FROM == "" ? SIS_NAME : $NAME_FROM;
        $this->EMAIL_FROM = $EMAIL_FROM == "" ? SIS_EMAIL : $EMAIL_FROM;
        
        // Create a new PHPMailer instance
        //$this->mail = new PHPMailer();
        throw new \Exception("Classe PHPMailer foi descontinuada e não encontra-se funcional. Analisar outra possibilidade.");
        
        // Tell PHPMailer to use SMTP
        $this->mail->isSMTP();
        
        // ########################################## CORRECAO DE ERRO DESCONHECIDO
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        // ########################################################################
        
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = 2;
        
        // Set the hostname of the mail server
        $this->mail->Host = 'smtp.gmail.com';
        // use above code, if your network does not support SMTP over IPv6
        // $this->mail->Host = gethostbyname('smtp.gmail.com');
        
        // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 587;
        
        // Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = 'tls';
        
        // Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;
        
        // Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = $this->USERNAME;
        
        // Password to use for SMTP authentication
        $this->mail->Password = $this->PASSWORD;
        
        // Set who the message is to be sent from
        $this->mail->setFrom($this->EMAIL_FROM, $this->NAME_FROM);
        
        // Set an alternative reply-to address
        $this->mail->addReplyTo($this->EMAIL_FROM, $this->NAME_FROM);
        
        // Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);
        
        // Set the subject line
        $this->mail->Subject = utf8_decode($subject);
        
        // Read an HTML message body from an external file, convert referenced images to embedded,
        // convert HTML into a basic plain-text alternative body
        $content = $this->contentTpl($tplName,$data);
        $this->mail->msgHTML($content);
        
        // Replace the plain text body with one created manually
        //$this->mail->AltBody = 'ERRO no HTML ---> This is a plain-text message body';
        
        // Attach an image file
        // $this->mail->addAttachment('anexo.png');
    }

    public function contentTpl($tplName,$data)
    {
        $config = array(
            "tpl_dir" => ROOT_TPL . "email/",
            "cache_dir" => "cache/",
            "debug" => true // set to false to improve speed
        );
        
        Tpl::configure($config);
        
        // create the Tpl object
        $tpl = new Tpl();
        
        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }
        
        $html = $tpl->draw($tplName, true);
        
        $html = utf8_decode($html);
        
        return $html;
    }

    public function send()
    {
        return $this->mail->send();
    }    
}

?>