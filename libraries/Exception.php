<?php
namespace manguto\cms7\libraries;

use application\core\Controller;

class Exception extends \Exception
{

    // ####################################################################################################
    // ########################################################################################## construct
    // ####################################################################################################
    public function __construct($message = null, $code = null, $previous = null)
    {
        { // log
            Logger::exception($message);
        }
        { // e-mail
            self::sendEmail($message);
        }
        parent::__construct($message, $code, $previous);
    }

    // ####################################################################################################
    /**
     * envia um email para o administrador da aplicacao
     * @param string $message
     */
    static private function sendEmail(string $message){
        $email = new Email();
        {
            $from = APP_EMAIL;
            $to = APP_EMAIL_ADMIN;
            $cc = '';
            $cco = '';
            $subject = APP_SHORT_NAME. " - EXCEPTION";
            $content = 'MENSAGEM DA EXCEÇÃO: <hr/>'.$message;
            $password = APP_EMAIL_MSS_PASSWORD;
        }
        $emailResult = $email->Enviar($from, $to, $cc, $cco, $subject, $content, $password);
        if($emailResult===true){
            Logger::success("'Exception' e-mail enviado com sucesso!");
        }else{
            Logger::error("Não foi possível enviar 'Exception' e-mail. Resultado obtido: ".$emailResult);
        }
    }

    // ####################################################################################################
    // ############################################################################################# static
    // ####################################################################################################
    /**
     * imprime o caminho percorrido ate o local quantas vezes o codigo passar pelo mesmo
     *
     * @param string $msg
     * @throws Exception
     */
    static function deb($msg = '')
    {
        try {
            throw new Exception($msg);
        } catch (Exception $e) {
            Exception::static_show($e, true);
        }
    }

    // ####################################################################################################
    // ############################################################################################ private
    // ####################################################################################################

    /**
     * exibe a excecao informada
     *
     * @param Exception $e
     * @param boolean $echo
     * @return string
     */
    private static function static_show(Exception $e, $echo = false)
    {
        $type = gettype($e);
        $return = "<pre title='$type'><br/>";
        $return .= '<b>' . nl2br($e->getMessage()) . '</b><br/><br/>';
        $return .= $e->getFile() . ' (' . $e->getLine() . ')<br/><br/>';
        $return .= nl2br($e->getTraceAsString()) . '<br/><br/>';
        if ($echo) {
            echo $return;
        } else {
            return $return;
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    /**
     * realiza o manipulacao de eventos indevidos do sistema (Error, Throwable, Exception)
     *
     * @param mixed $e
     */
    static function handleEvent($e,$redirect=true)
    {
        
        // tipo do evento (classe)
        $event_class = get_class($e);

        if (strpos($event_class, 'Exception') !== false) {
            { // EXCEPTION                
                $message = $e->getMessage();
            }
        } else if (strpos($event_class, 'Error') !== false) {
            { // ERROR
                $message = "$e";                
            }
        } else {
            $message = "Evento ($event_class): ".strval($e);            
        }
        
        //Cria os alertas!
        //Alert::setDanger("ATENÇÃO! Ocorreu um ERRO CRÍTICO no sistema. <br/>Por favor, tente novamente e caso a inconssistência persista, <br/>anote as informações descritas abaixo e contate o Administrador através do e-mail: ".APP_EMAIL_ADMIN.".<br/>Obrigado!");
        
        Logger::error("Exception!!! ".chr(10).$message);
        Alert::setDanger($message);
        
        //redireciona para a tela principal
        if($redirect){
            Controller::HeaderLocation('/');
        }        
    }

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    /**
     * Retorna um array com os caminhos percorridos na iteracao atual.
     *
     * @return array
     */
    private function getTraceAsArray(): array
    {
        { // conf
            $lineDelimiter = '#';
        }
        $return = [];
        $traceAsString = $this->getTraceAsString();
        // deb($traceAsString);

        $trace_line_array = explode($lineDelimiter, $traceAsString);
        // deb($trace_line_array);

        foreach ($trace_line_array as $trace_line) {
            $trace_line = Strings::RemoverQuebrasDeLinha($trace_line);
            $trace_line = trim($trace_line);

            // pula linhas vazias
            if ($trace_line == '') {
                continue;
            }

            // remove o indice à esquerda ("34 Xxxxxxxx xxxxxxxx")
            $trace_line = substr($trace_line, strpos($trace_line, ' '));

            // remoce o registro referente ao index
            if (strpos($trace_line, '{main}')) {
                $trace_line = getcwd() . DIRECTORY_SEPARATOR . 'index.php';
            }
            $return[] = trim($trace_line);
        }
        // remove ultimo registro referente a chamada a esta mesma funcao
        array_shift($return);

        // inverte a ordem do array
        rsort($return);
        return $return;
    }

    // ####################################################################################################
    /**
     * parseia a string do trace em parametros
     *
     * @param string $trace_line
     * @return array
     */
    private function getTraceAsArray_line(string $trace_line): array
    {
        $trace_line_info_array = explode(' ', $trace_line);
        // deb($trace_line_info_array);
        {
            $index = array_shift($trace_line_info_array);
            {
                $file_line = array_shift($trace_line_info_array);
                $file_line_s = explode('(', $file_line);
                $file = array_shift($file_line_s);
                $file = trim($file);
                $file_ = explode(' ', $file);
                $file = array_pop($file_);
                // deb($file);
                {
                    $lineNumber = array_shift($file_line_s);
                    $lineNumber = str_replace('):', '', $lineNumber);
                    $lineNumber = trim($lineNumber);
                    // deb($fileNumber);
                }
            }
            {
                $class_method_function = array_shift($trace_line_info_array);
            }
        }
        $return = [
            'index' => $index,
            'file' => $file,
            'line' => $lineNumber,
            'function' => $class_method_function
        ];

        return $return;
    }

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>