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
    
    const lineLen = 80;

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
                // throw new Exception("Constante necessária não definida ($cteName).");
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
     * registra temporariamente o registro para insercao no
     * log quando das definicoes das constantes necessarias
     * ou
     * salva todos as notas previamente registradas
     *
     * @param string|bool $data
     */
    static private function saveOrCheckCache($data)
    {
        if ($data !== true) {
            // no caso de uma mensagem
            if (! isset($_SESSION[__DIR__]['logger'])) {
                $_SESSION[__DIR__]['logger'] = [];
            }
            $_SESSION[__DIR__]['logger'][] = $data;
        } else {
            // no caso do salvamento efetivo
            if (isset($_SESSION[__DIR__]['logger']) && sizeof($_SESSION[__DIR__]['logger']) > 0) {
                $logsTemp = $_SESSION[__DIR__]['logger'];
                unset($_SESSION[__DIR__]['logger']);
                foreach ($logsTemp as $dataTemp) {
                    self::save($dataTemp);
                }
            }
        }
    }

    // ####################################################################################################
    /**
     * salva os dados informados no arquivo de log
     *
     * @param string $data
     */
    static private function save(string $data)
    {
        if (self::checkConstants()) {
            self::saveOrCheckCache(true);
            Files::escreverConteudo(self::getFilename(), $data, FILE_APPEND);
        } else {
            self::saveOrCheckCache($data);
        }
    }
    
    // ####################################################################################################
    static private function getLineInfo(string $type = '')
    {
        {
            $time = str_replace('#', Datas::getMicrotime(), date(self::lineTimeFormat));
            $type = str_pad(strtoupper($type), 10, '_', STR_PAD_RIGHT);
        }
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "$time | $type";
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        
        return $return;
    }
    
    // ####################################################################################################
    static private function getCallInfo()
    {
        {
            $backtrace = debug_backtrace();
            //deb($backtrace);
            {//verificacao do passo da chamada
                $caller = $backtrace[3];
                {//verificacao do arquivo de origem. Caso seja a classe Exception o passo precisa ser antecipado
                    if(strpos($caller['file'],'Exception')!==false){
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
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "{$file} ({$line}) # {$function}";
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        {//espacamento linha
            
            $lineLenTemp = strlen($return);
            if($lineLenTemp < self::lineLen){
                $multiplier = self::lineLen - $lineLenTemp;
                $shift = str_repeat(self::lineSpacer, $multiplier);
            }else{
                $shift = '';   
            }
        }
        $return = str_replace('#', $shift, $return);

        return $return;
    }

    // ####################################################################################################
    static private function getParametersInfo(array $parameters)
    {
        if (sizeof($parameters) > 0) {
            $parameters_show = [];
            {
                $parameters_show[] = ':';
                $parameters_show[] = ' ';
                $parameters_show[] = Arrays::arrayShow($parameters);
                $parameters_show[] = ' ';
            }
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
            $parameters_show = ' | ' . str_replace(chr(10) . ' ', chr(10) . self::parametersBoxChar . ' ', implode(chr(10), $parameters_show));
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
            //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        } else {
            $parameters_show = '';
        }
        return $parameters_show;
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
            $parametersInfo = self::getParametersInfo($parameters);
        }        
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        $return = "{$lineInfo} | {$callInfo} | {$msg} {$parametersInfo} " . chr(10);
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===
        //<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===<===

        return $return;
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>