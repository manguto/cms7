<?php
namespace manguto\cms7\libraries;

/**
 * Logger ultra basico (temporario)
 *
 * @author Marcos
 *        
 */
class Logger
{

    const neededConstants = [
        'APP_LOG_DIR',
        'APP_USER_IP_MASKED',
        'APP_UNIQID',
        'APP_ITERATION'
    ];

    const folderDateFormat = 'Y-m-d';

    const lineTimeFormat = 'H:i:s-#';

    const lineChar = '=';

    const parametersBoxChar = '|';

    const lineSpacer = '_';

    const lineLen = 100;

    const SessionKey = 'Logger';

    // ####################################################################################################
    // #################################################################################### STATIC / PUBLIC
    // ####################################################################################################
    //
    //
    static function info(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    // ######################################################################
    static function warning(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    // ######################################################################
    static function error(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    // ######################################################################
    static function exception(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    // ######################################################################
    static function proc(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    // ######################################################################
    static function success(string $msg, array $parameters = [])
    {
        self::save(self::getData(__FUNCTION__, $msg, $parameters));
    }

    //
    //
    // ####################################################################################################
    // ############################################################################################ PRIVATE
    // ####################################################################################################
    /**
     * verifica se todas as constantes necessarias foram definidas
     *
     * @throws Exception
     */
    static private function checkConstants()
    {
        foreach (self::neededConstants as $cteName) {
            if (! defined($cteName)) {
                return false;
            }
        }
        return true;
    }

    // ####################################################################################################
    /**
     * retorna o nome do arquivo de log (atual)
     *
     * @return string
     */
    static private function getFilename(): string
    {
        // ==============================================================
        {
            $dir = APP_LOG_DIR;
            {
                $APP_USER_IP_MASKED = APP_USER_IP_MASKED;
                $APP_USER_IP_MASKED = str_replace('_', '-', $APP_USER_IP_MASKED);
            }
            $folder = date(self::folderDateFormat) . DIRECTORY_SEPARATOR . $APP_USER_IP_MASKED . DIRECTORY_SEPARATOR;
            $filename = APP_ITERATION . '.txt';
        }
        // ==============================================================
        return $dir . $folder . $filename;
    }

    // ####################################################################################################
    /**
     * salva todos as notas previamente 'cacheadas'
     */
    static private function save_cached_logs()
    {
        // registro de dados previamente 'cacheados'
        if (Sessions::isset(self::SessionKey)) {
            $cached_log_array = Sessions::get(self::SessionKey, true, true);
            if (is_array($cached_log_array) && sizeof($cached_log_array) > 0) {
                foreach ($cached_log_array as $cached_log) {
                    self::write($cached_log);
                }
            }
        }
    }

    // ####################################################################################################
    /**
     * guarda informacoes de 'logs' para escrita futura
     * (quando todas as constantes necessarias estiverem definidas)
     *
     * @param string $data
     */
    static private function cache(string $data)
    {
        // inicializa parametro como array (caso necessario)
        if (! Sessions::isset(self::SessionKey)) {
            Sessions::set(self::SessionKey, []);
        }
        Sessions::set(self::SessionKey, $data, true);
    }

    // ####################################################################################################
    /**
     * tenta salvar as informacoes passadas no arquivo de log
     * e caso contrario, registra na sessao para escrita futura
     * quando possivel.
     *
     * @param string $data
     */
    static private function save(string $data)
    {
        if (self::checkConstants()) {
            self::save_cached_logs();
            self::write($data);
        } else {
            self::cache($data);
        }
    }

    // ####################################################################################################
    /**
     * escreve arquivo na pasta informada para insercao de logs
     *
     * @param string $data
     * @return boolean
     */
    static private function write(string $data)
    {
        return File::writeContent(self::getFilename(), utf8_decode($data), FILE_APPEND);
    }

    // ####################################################################################################
    static private function getLineInfo(string $type = '')
    {
        {
            $time = str_replace('#', Datas::getMicrotime(), date(self::lineTimeFormat));
            $type = str_pad(strtoupper($type), 10, '_', STR_PAD_RIGHT);
        }
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "$time | $type";
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===

        return $return;
    }

    // ####################################################################################################
    static private function getCallInfo()
    {
        {
            $backtrace = debug_backtrace();
            // deb($backtrace);
            { // verificacao do passo da chamada
                $caller = $backtrace[3];
                { // verificacao do arquivo de origem. Caso seja a classe Exception o passo precisa ser antecipado
                    if (strpos($caller['file'], 'Exception') !== false) {
                        $caller = $backtrace[4];
                    }
                }
            }

            {
                $file = $caller['file'];
                $function = $caller['function'];
                {
                    $file = str_replace(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR, '', $file);
                    $file = str_replace(DIRECTORY_SEPARATOR, '.', $file);
                    $file = str_replace('.php', '', $file);
                }
                $line = $caller['line'];
            }
        }
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "{$file} ({$line}) # {$function}";
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        { // espacamento linha

            $lineLenTemp = strlen($return);
            if ($lineLenTemp < self::lineLen) {
                $multiplier = self::lineLen - $lineLenTemp;
                $shift = str_repeat(self::lineSpacer, $multiplier);
            } else {
                $shift = '';
            }
        }
        $return = str_replace('#', $shift, $return);

        return $return;
    }

    // ####################################################################################################
    /*
     * static private function getParametersInfo(array $parameters)
     * {
     * if (sizeof($parameters) > 0) {
     * $parameters_show = [];
     * {
     * $parameters_show[] = ':';
     * $parameters_show[] = ' ';
     * $parameters_show[] = Arrays::arrayShow($parameters);
     * $parameters_show[] = ' ';
     * }
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * $parameters_show = ' | ' . str_replace(chr(10) . ' ', chr(10) . self::parametersBoxChar . ' ', implode(chr(10), $parameters_show));
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
     * } else {
     * $parameters_show = '';
     * }
     * return $parameters_show;
     * }/*
     */

    // ####################################################################################################
    /**
     * formata a mensagem para uma melhor exibicao no log quanto a quebras de linha
     * @param string $message
     * @return string
     */
    private static function formatMessage(string $message):string
    {
        {//remocao tags html
            {//replace quebras de linha em HTML para nao perde-las
                $message = str_replace('<br>', chr(10), $message);
                $message = str_replace('<br/>', chr(10), $message);
                $message = str_replace('<br />', chr(10), $message);                
                $message = str_replace('<BR>', chr(10), $message);
                $message = str_replace('<BR/>', chr(10), $message);
                $message = str_replace('<BR />', chr(10), $message);                
            }
            $message = Strings::RemoverTagsHTML($message);
        }
        {
            $test = '#@#';
            $nl = chr(10).str_repeat(' ', 3);
            $pre_extra_lines = 2;
            $pos_extra_lines = 5;
        }
        
        {//teste
            $message_test = Strings::RemoverQuebrasDeLinha($message,$test);
        }
        
        //existem quebras de linha na mensagem?
        if(strlen($message)!=strlen($message_test)){            
            
            {//quebras de linha encontradas!
                $message = '...';
                $message .= str_repeat($nl,$pre_extra_lines);
                $message .= str_replace($test, $nl, $message_test);
                $message .= str_repeat($nl,$pos_extra_lines);
            }               
        }
        
        return $message;
    }

    // ####################################################################################################
    /**
     * monta o conteudo a ser registrado (logado)
     *
     * @param string $method
     * @param string $msg
     * @param array $parameters
     * @return string
     */
    static private function getData(string $method, string $msg, array $parameters): string
    {
        {
            $lineInfo = self::getLineInfo($method);
            $callInfo = self::getCallInfo();
            $msg = self::formatMessage($msg);
            {
                $parameters = sizeof($parameters) > 0 ? self::formatMessage(json_encode($parameters)) : '';
            }
        }
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "{$lineInfo} | {$callInfo} | {$msg} {$parameters} " . chr(10);
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        // <===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===

        return $return;
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>