<?php
namespace manguto\cms7\libraries;

/**
 * Controle e Gerenciamento de eventos improprio/indevidos do sistema
 *
 * @author Manguto
 *        
 */
class Exception extends \Exception
{

    /**
     * string que especifica que o evento segue o padrao dos 3 campos em negrito.
     *
     * @var string
     */
    const bold3PatternFlag = 'bold3Pattern';

    /**
     * informacoes para tratamento de eventos
     *
     * @var array
     */
    const Events = [
        'Notice' => [
            'needleStart' => '<b>Notice</b>:',
            'needleEnd' => self::bold3PatternFlag,
            'title' => 'Notificação'
        ],
        'Warning' => [
            'needleStart' => '<b>Warning</b>:',
            'needleEnd' => self::bold3PatternFlag,
            'title' => 'Aviso'
        ],
        'FatalError' => [
            'needleStart' => 'Fatal error:',
            'needleEnd' => self::bold3PatternFlag,
            'title' => 'Erro Fatal'
        ],
        'ParseError' => [
            'needleStart' => 'ParserError:',
            'needleEnd' => '{main}',
            'title' => 'Erro de Interpretação'
        ]
    ];

    //ajuste para exibicao da linha correta do template quando do problema (error, excep...) 
    const templateLineFinetune = 116;
    
    // ####################################################################################################
    // ########################################################################################## construct
    // ####################################################################################################
    public function __construct($message = null, $code = null, $previous = null)
    {
        // construct
        parent::__construct($message, $code, $previous);
        // log extra
        // Logger::EXTRA($message, $this->getFile(), $this->getLine(), APP_USER_IP,'',APP_LOG_DIR . '_exceptions'.DS);
        {
            $filename = '';
            $dir = Logger::dir . Logger::foldernameException;
            $title = 'EXCEPTION!';
        }
        
        {// adicao de informacoes da excecao (arquivo e linha)
        	$file = $this->getFile();
        	$line = intval($this->getLine());
        	{//extra template error help!
        		$extra = strpos($message, 'templates');
        		if($extra!==false){
        			$info = explode(' on line ',substr($message, $extra));        			
        			$extra_template_line = intval(array_pop($info)) - self::templateLineFinetune;
        			$extra_template_line = " (Tpl: $extra_template_line)";
        		}else{
        			$extra_template_line = '';
        		}
        	}
        	$message .= " $extra_template_line | $file ({$line})" . chr(10) . '<hr/>' . implode('<br/>' . chr(10), $this->getTraceAsArray()) . chr(10) . '<br/><br/><br/>';
        }
        

        $this->message = $message;
        // log
        Logger::exception($message);
        // log extra
        Logger::EXTRA($message, $filename, $dir, $title);
    }

    // ####################################################################################################

    /**
     * retorna a mensagem de acordo com o tipo do evento    
     * @param mixed $e
     * @return string
     */
    static function getEventMessage($e):string
    {
        if(is_object($e)){
            { // tipo do evento (classe)
                $event_class = get_class($e);
            }
            { // formatacao da mensagem de acordo com o tipo do evento
                if (strpos($event_class, 'Exception') !== false) {
                    { // EXCEPTION
                        $message = $e->getMessage();
                    }
                } else if (strpos($event_class, 'ParserError') !== false) {
                    { // PARSER ERROR
                        $message = "$e";
                    }
                } else if (strpos($event_class, 'Error') !== false) {
                    { // ERROR
                        $message = "$e";
                    }
                } else {
                    $message = strval($e);
                }
            }
        }else{
            $message = strval($e);
        }        
        return self::FixMessageContent($message);
    }

    // ###############################################################################################

    /**
     * realiza uma verificacao no texto enviado
     * quanto a existencia de um padrao de texto
     * que representa a ocorrencia de eventos indevidos
     * (warning, notice...)
     * e
     * caso os encontre,
     * retorna as suas mensagens
     *
     * @param string $string
     * @throws Exception
     * @return array
     */
    static function checkGetThrownEventMsg(string $string): array
    {
        $return = [];
        foreach (self::Events as $eventName => $eventINFO) {

            $eventStartNeedle = $eventINFO['needleStart'];
            $eventEndNeedle = $eventINFO['needleEnd'];
            $eventStartNeedlePoint = strpos($string, $eventStartNeedle);

            if ($eventStartNeedlePoint !== false) {
                // deb($eventINFO,0);
                if ($eventEndNeedle == self::bold3PatternFlag) {

                    // tratamento das mensagens com o padrao de 3 negritos (warning, notice, .?.)
                    $position = $eventStartNeedlePoint;
                    for ($i = 0; $i < 3; $i ++) {
                        $position = strpos($string, "</b>", $position) + 1;
                    }
                    $eventEndPoint = $position;
                    $eventLength = $eventEndPoint + 3 - $eventStartNeedlePoint;
                } else {

                    // tratamento de excecoes da classe e demais que nao seguem o B3P
                    $eventEndPoint = strpos($string, $eventEndNeedle);
                    if ($eventEndPoint === false) {
                        // caso nao encontre o padrao de finalizacao do conteudo, retorna apenas o nome do evento
                        $eventEndPoint = $eventStartNeedlePoint + strlen($eventStartNeedle) + 1;
                    }
                    $eventLength = $eventEndPoint + strlen($eventEndNeedle) - $eventStartNeedlePoint;
                }
                $eventMessage = substr($string, $eventStartNeedlePoint, $eventLength);
                $eventMessage = Exception::FixMessageContent($eventMessage);
                $eventMessage = strip_tags($eventMessage);
                $return[$eventName] = $eventMessage;
            }
        }
        return $return;
    }

    /* */

    // ###############################################################################################
    // ####################################################################################### private
    // ###############################################################################################

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
    /**
     * realiza ajustes no texto da mensagem
     *
     * @param string $message
     * @return string
     */
    static private function FixMessageContent(string $message):string
    {
        $return = $message;
        {
            // ocultacao de caminho base do servidor
            $return = str_replace(APP_PATH, '', $return);
        }
        return $return;
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>