<?php
namespace manguto\cms5\lib;

use manguto\cms5\mvc\model\User;

class Logs
{

    const dir = 'log';

    const formato_datahora = 'Y-m-d H_i_s';

    const formato_data_arquivo = 'Ymd_H';

    const parameters = [
        'datahora',
        'user_login',
        'user_id',
        'ip',
        'rota',
        'type',
        'subtype',
        'msg'
    ];

    private $trace;

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT
    static function Start()
    {
        $log = new self();
        self::write('Log inicializado.', 'START', '#############');
        Session::set('log', $log);
    }

    private function __construct()
    {
        $this->trace = [];
    }

    private function saveTrace($msg)
    {
        if (! $this->issetTrace($msg)) {
            $this->trace[] = $msg;
        }
    }

    private function issetTrace($msg)
    {
        return in_array($msg, $this->trace);
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    static private function GetLogObject()
    {
        return Session::get('log');
    }

    static private function SetLogObject(Logs $log)
    {
        Session::set('log', $log);
    }

    /**
     * verifica se um 'trace' existe e caso contrario registra-o,
     * retornando true ou false, conforme o caso.
     *
     * @param string $trace
     * @return boolean
     */
    static private function CheckPointTrace_checkSet(string $trace): bool
    {
        $log = self::GetLogObject();
        if ($log->issetTrace($trace)) {
            return true;
        } else {
            $log->saveTrace($trace);
            return false;
        }
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    static public function CheckPoint($msg = '')
    {
        if (trim($msg) == '') {
            $msg = 'Ponto de Verificação!';
        }

        $traceAsArray = self::getTraceAsArray();
        // deb($traceAsArray);

        foreach ($traceAsArray as $trace) {
            
            // evita o mesmo registro duas vezes por conta dos multiplos pontos de verificação!
            /*if (self::CheckPointTrace_checkSet($trace) == false) {
                self::write($trace, 'Checkpoint');
            }/**/
            
            self::write($trace, 'Checkpoint');
        }
    }

    static private function getTraceAsArray()
    {
        { // conf
            $lineDelimiter = '#';
        }
        $return = [];
        $traceAsString = get_backtrace();
        // deb($traceAsString);
        
        $trace_line_array = explode($lineDelimiter, $traceAsString);
        // deb($trace_line_array);
        
        foreach ($trace_line_array as $trace_line) {
            $trace_line = Strings::RemoverQuebrasDeLinha($trace_line);
            $trace_line = trim($trace_line);
            
            // pula linhas vazias
            if ($trace_line == ''){
                continue;
            }
            
            //remove o indice à esquerda ("34 Xxxxxxxx xxxxxxxx")
            //$trace_line = substr($trace_line, strpos($trace_line, ' '));
            
            //remoce o registro referente ao index
            if(strpos($trace_line, '{main}')){
                $trace_line = getcwd().DIRECTORY_SEPARATOR.'index.php';
            }
            $return[] = trim($trace_line);
            
        }
        //remove registros referente as chamadas desta classe
        array_pop($return);
        array_pop($return);
        
        //inverte a ordem do array
        //rsort($return);
        return $return;
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    static private function write($msg = '', $type = 'Default', $subType = 'Default')
    {
        { // parameters
            $values = [];
            $values['datahora'] = date(self::formato_datahora);
            $values['ip'] = ServerHelp::getIp();
            $values['rota'] = ServerHelp::getURLRoute();
            { // usuario
                $user = User::getSessionUser();
                if ($user != false) {
                    $values['user_id'] = $user->getId();
                    $values['user_login'] = $user->getLogin();
                } else {
                    $values['user_id'] = '0';
                    $values['user_login'] = 'visitante';
                }
            }
            $values['type'] = $type;
            $values['subtype'] = $subType;
            $values['msg'] = $msg;
        }

        { // define os parametros (e seus valores) a serem registrados
            $parametes = [];
            foreach (Logs::parameters as $parameter) {
                if (! isset($values[$parameter])) {
                    throw new Exception("Parâmetro não definido ($parameter).");
                } else {
                    $parametes[$parameter] = $values[$parameter];
                }
            }
        }

        { // verifica se todos os valores definidos constam na listagem de parametros para registro
            foreach (array_keys($values) as $parameter) {
                if (! isset($parametes[$parameter])) {
                    throw new Exception("Valor não alocado nas definições do parametros ($parameter).");
                }
            }
        }
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        self::save($parametes);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< PRIVATE
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< PRIVATE
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< PRIVATE
    static private function checkDir()
    {
        $pathname = self::dir;
        if (! file_exists($pathname)) {
            Diretorios::mkdir($pathname);
        }
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
    static private function save($parameters)
    {
        { // basic parameters
            $datahora = $parameters['datahora'];
        }
        { // checks
          // dir
            self::checkDir();
            // file
            self::checkFile($datahora);
        }
        { // filename
            $filename = self::get_filename($datahora);
        }
        { // data
            $data = utf8_decode(implode(';', $parameters) . chr(10));
        }
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // deb($filename, 0); debc($data);
        Arquivos::escreverConteudo($filename, $data, FILE_APPEND);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
    static private function checkFile($datahora)
    {
        $filename = self::get_filename($datahora);
        if (! file_exists($filename)) {
            $data = implode(';', self::parameters) . chr(10);
            Arquivos::escreverConteudo($filename, $data);
        }
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
    static private function get_filename($datahora)
    {
        $data = new Datas($datahora, self::formato_datahora);
        $data_arquivo = $data->getDate(self::formato_data_arquivo);
        $filename = self::dir . DIRECTORY_SEPARATOR . $data_arquivo . '.csv';
        return $filename;
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
    /*
     * static function ReflectionClassAnalisys($class_name, $class_method, $arguments=[])
     * {
     *
     * $rc = new \ReflectionClass($class_name);
     * //deb($rc);
     * $docComment = $rc->getDocComment();
     *
     * $rcm = new \ReflectionMethod($class_method);
     * deb($rcm->getDocComment());
     * }/*
     */
    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
}

?>