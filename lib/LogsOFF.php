<?php
namespace manguto\cms5\lib;

use manguto\cms5\mvc\model\User;

class LogsOFF
{

    const dir = 'log';

    const formato_datahora = 'Y-m-d H_i_s';

    const formato_data_arquivo = 'Ymd_H';

    const parameters = [
        'datahora',
        'ip',
        'user_id',
        'user_login',
        'rota',
        'type',
        'msg',
        'trace'
    ];

    const trace_base_parameters = [
        'file',
        'line',
        'function',
        'class',
        'type',
        'args'
    ];

    const trace_hide_terms = [
        'slim\slim'
    ];

    const traceDelimiter = '<br/>';

    // <<<<<<<<<<<<<<<<<< <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT
    // <<<<<<<<<<<<<<<<<<< <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT
    // <<<<<<<<<<<<<<<<<<<< <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< CONSTRUCT

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    static public function set($msg = 'Ponto de Verificação!', $type = '...')
    {
        {//destaque por tipo
            if(strpos(strtolower($type),'exc')!==false){
                $msg_style = "color:#f00";
                $type_style = "color:#f00";
            }else{
                $msg_style = "";
                $type_style = "";
            }
        }
        {//encapsulamento
            $msg = "<span style='$msg_style'>$msg</span>";
            $type = "<span style='$type_style'>$type</span>";
        }
        self::write($msg, $type);
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< ADD
    static private function getBackTrace()
    {
        $return = [];
        $backtrace_array = debug_backtrace();
        // deb($backtrace_array);

        // $backtrace_last = array_shift($backtrace_array);
        // deb($backtrace_last);

        // ---------------------------------- remove traces desta classe (desnecessarios)
        if (sizeof($backtrace_array) > 1) {
            array_shift($backtrace_array);
            array_shift($backtrace_array);
        } /* */

        foreach ($backtrace_array as $backtrace) {
            // deb($backtrace,0);

            { // garimpagem de parametros
                $title = [];
                foreach (self::trace_base_parameters as $key) {
                    if (isset($backtrace[$key]) && (is_string($backtrace[$key]) || is_int($backtrace[$key]))) {
                        $$key = trim($backtrace[$key]);
                    } else {
                        $$key = '';
                    }
                    $title[] = "$key=" . $$key;
                }
                $title = implode(' #', $title);
            }

            { // verifica se o trace deve ser ocultado
                $hide = false;
                foreach (self::trace_hide_terms as $term) {
                    if (strpos($file, $term) !== false) {
                        $hide = true;
                    }
                }
                if ($hide) {
                    continue;
                }
            }

            $return[] = "<span title='$title'>$file ($line)</span>";
        } /* */

        // inverter a ordem
        rsort($return);

        $return = implode(self::traceDelimiter, $return);
        return $return;
    }

    static private function write($msg = '', $type = 'Default')
    {
        { // parameters
            $values = [];
            $values['datahora'] = date(self::formato_datahora);
            $values['ip'] = ServerHelp::getIp();
            $values['rota'] = ServerHelp::getURLRoute();
            { // usuario
                $user = User::getSessionUser();
                // deb($user);
                if ($user != false) {
                    $values['user_id'] = $user->getId();
                    $values['user_login'] = $user->getLogin();
                } else {
                    $values['user_id'] = '0';
                    $values['user_login'] = 'visitante';
                }
            }
            $values['type'] = $type;
            $values['msg'] = $msg;
            $values['trace'] = self::getBackTrace();
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
            { // extra datahora (microsegundos)
                $microtime = round(microtime(false), 4);
                $microtime = str_pad($microtime, 5, STR_PAD_RIGHT);
                $parameters['datahora'] .= ' ' . $microtime;
            }
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
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        Arquivos::escreverConteudo($filename, $data, FILE_APPEND);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
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
    static function getLastLogFileHTML($sortByField = 'datahora', $sortDesc = true, $deleteFile = false)
    {
        $logs = Diretorios::obterArquivosPastas(self::dir, $recursive = false, $filesAllowed = true, $foldersAllowed = false, [
            'csv'
        ]);
        if (sizeof($logs) > 0) {
            $lastLog = array_pop($logs);
            $return = utf8_encode(CSV::CSVToHTML(Arquivos::obterConteudo($lastLog), [
                'class' => 'log'
            ], true, true, false));
            if ($deleteFile) {
                Arquivos::excluir($lastLog);
            }
        } else {
            $return = 'Nenhum arquivo de log encontrado.';
        }
        { // html
            $return = "<br/><br/><div class='container_large log'>$return</div><br/><br/><br/><br/><br/><br/>";
        }
        { // js
            $return .= "<script>";
            $return .= "  $(document).ready(function(){";
            $return .= "      $('th[data-field=\"msg\"]').css('width','300px');";
            $return .= "      $('th[data-field=\"$sortByField\"] div.th-inner').click();";
            // deb($sortDesc,0);
            if ($sortDesc) {
                $return .= "        $('th[data-field=\"$sortByField\"] div.th-inner').click();";
            }
            $return .= " $('div.container_large.log *').css({'font-size':'12px'});";
            $return .= " $('table.log th,td').css({'padding':'0px 5px 1px 5px','vertical-align':'top'});";
            $return .= "  });";
            $return .= "</script>";
        }
        return $return;
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
}

?>