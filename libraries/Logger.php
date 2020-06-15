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
        'APP_ITERATION'
    ];

    const folderDateFormat = 'Y-m-d';

    const lineTimeFormat = 'H:i:s:# d-m-Y';

    // # => microtime
    const lineChar = '=';

    const parametersBoxChar = '|';

    const lineLengthLevel1 = '100';

    const lineLengthLevel2 = '75';

    const lineLengthLevel3 = '50';

    const lineLengthLevel4 = '25';

    // ####################################################################################################
    // #################################################################################### STATIC / PUBLIC
    // ####################################################################################################

    // ####################################################################################################
    static function info(string $msg, array $parameters = [])
    {
        { // confeccao do conteudo do registro
            $data = self::getData(__FUNCTION__, $msg, $parameters);
        }
        self::save($data);
    }

    // ####################################################################################################
    static function error(string $msg, array $parameters = [])
    {
        if (self::checkConstants()) {
            {
                $data = self::getData(__FUNCTION__, $msg, $parameters);
            }
            self::save($data);
        }
    }

    // ####################################################################################################
    static function proc(string $msg, array $parameters = [])
    {
        if (self::checkConstants()) {
            {
                $data = self::getData(__FUNCTION__, $msg, $parameters);
            }
            self::save($data);
        }
    }

    // ####################################################################################################
    static function success(string $msg, array $parameters = [])
    {
        if (self::checkConstants()) {
            {
                $data = self::getData(__FUNCTION__, $msg, $parameters);
            }
            self::save($data);
        }
    }

    // ####################################################################################################
    /**
     * obtem uma sequencia de caracteres (delimitacao ou separacao de textos) de 1ª ordem
     *
     * @return string
     */
    static private function getLineLevel1(string $char = self::lineChar): string
    {
        return self::getLine($char, self::lineLengthLevel1);
    }

    // ####################################################################################################
    /**
     * obtem uma sequencia de caracteres (delimitacao ou separacao de textos) de 2ª ordem
     *
     * @return string
     */
    static private function getLineLevel2(string $char = self::lineChar): string
    {
        return self::getLine($char, self::lineLengthLevel2);
    }

    // ####################################################################################################
    /**
     * obtem uma sequencia de caracteres (delimitacao ou separacao de textos) de 3ª ordem
     *
     * @return string
     */
    static private function getLineLevel3(string $char = self::lineChar): string
    {
        return self::getLine($char, self::lineLengthLevel3);
    }

    // ####################################################################################################
    /**
     * obtem uma sequencia de caracteres (delimitacao ou separacao de textos) de 4ª ordem
     *
     * @return string
     */
    static private function getLineLevel4(string $char = self::lineChar): string
    {
        return self::getLine($char, self::lineLengthLevel4);
    }

    // ####################################################################################################
    // ############################################################################################ PRIVATE
    // ####################################################################################################

    /**
     * obtem uma sequencia de caracteres (delimitacao ou separacao de textos)
     *
     * @param int $multiplier
     * @return string
     */
    static private function getLine(string $char = self::lineChar, int $multiplier): string
    {
        return str_repeat($char, $multiplier);
    }

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
        {
            $dir = APP_LOG_DIR;
            $folder = date(self::folderDateFormat) . DIRECTORY_SEPARATOR;
            $filename = APP_USER_IP_MASKED . '__' . APP_ITERATION . '.txt';
            // $filename = APP_USER_IP_MASKED . '.txt';
        }
        // ==============================================================
        return $dir . $folder . $filename;
    }

    // ####################################################################################################
    /**
     * registra temporariamente o registro para insercao no log quando das definicoes das constantes necessarias
     * ou
     * salva todos os registros previamente anotados 
     *
     * @param string|bool $data
     */
    static private function saveOrCheckCache($data)
    {
        if($data!==true){
            //no caso de uma mensagem
            if (! isset($_SESSION[__DIR__]['logger'])) {
                $_SESSION[__DIR__]['logger'] = [];
            }
            $_SESSION[__DIR__]['logger'][] = $data;
        }else{
            //no caso do salvamento efetivo
            if(isset($_SESSION[__DIR__]['logger']) && sizeof($_SESSION[__DIR__]['logger'])>0){
                $logsTemp = $_SESSION[__DIR__]['logger'];
                unset($_SESSION[__DIR__]['logger']);
                foreach ($logsTemp as $dataTemp){
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
            $dateTime = date(self::lineTimeFormat);
            $dateTime = str_replace('#', Datas::getMicrotime(), $dateTime);
            $type = strtoupper($type);
        }
        return "[$dateTime][$type]";
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
        { // line info
            $lineInfo = self::getLineInfo($method);
        }
        { // parameters
            if (sizeof($parameters) > 0) {
                $parameters_show = [];
                {
                    $parameters_show[] = ':';
                    $parameters_show[] = ' ';
                    $parameters_show[] = Arrays::arrayShow($parameters);
                    $parameters_show[] = ' ';
                }
                $parameters_show = str_replace(chr(10) . ' ', chr(10) . self::parametersBoxChar . ' ', implode(chr(10), $parameters_show));
            } else {
                $parameters_show = '';
            }
        }
        // =======================================
        return $lineInfo . ' ' . $msg . ' ' . $parameters_show . chr(10);
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>