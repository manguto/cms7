<?php
namespace manguto\cms5\lib;

use manguto\cms5\mvc\model\User; 

class Log
{

    const dir = 'log';

    const formato_datahora = 'Y-m-d H:i:s';

    const formato_data_arquivo = 'Ymd';

    static $parameters = [
        'datahora',
        'user_login',
        'user_id',
        'ip',
        'rota',
        'type',
        'subtype',
        'msg'
    ];

    static function Go($msg = '', $type = '', $subType = '')
    {
        { // parameters
            $values = [];
            $values['datahora'] = date(self::formato_datahora);
            $values['ip'] = ServerHelp::getIp();
            $values['rota'] = ServerHelp::getURLRoute();
            { // usuario
                $user = User::getSessionUser();
                if($user!=false){
                    $values['user_id'] = $user->getId();
                    $values['user_login'] = $user->getLogin();
                }else{
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
            foreach (Log::$parameters as $parameter) {
                if (! isset($values[$parameter])) {
                    throw new Exception("Parâmetro não definido ($parameter).");
                } else {
                    $parametes[$parameter] = $values[$parameter];
                }
            }
        }
        
        { // verifica se todos os valores definidos constam na listagem de parametros para registro
            foreach ($values as $parameter => $value) {
                if (! isset($parametes[$parameter])) {
                    throw new Exception("Valor não alocado nas definições do parametros ($parameter).");
                }
            }
        }
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        self::save($parametes);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    }

    static function ProcessResult($msg = '', $type = '')
    {
        self::Go($msg, 'ProcessResult', $type);
    }

    // ============================================================================================
    // ============================================================================================
    // ============================================================================================
    static private function checkDir()
    {
        $pathname = self::dir;
        if (! file_exists($pathname)) {
            Diretorios::mkdir($pathname);
        }
    }

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
        //deb($filename, 0); debc($data);
        Arquivos::escreverConteudo($filename, $data, FILE_APPEND);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<
    }

    static private function checkFile($datahora)
    {
        $filename = self::get_filename($datahora);
        if (! file_exists($filename)) {
            $data = implode(';', self::$parameters) . chr(10);
            Arquivos::escreverConteudo($filename, $data);
        }
    }

    static public function get_filename($datahora)
    {
        $data = new Datas($datahora, self::formato_datahora);
        $data_arquivo = $data->getDate(self::formato_data_arquivo);
        $filename = self::dir . DIRECTORY_SEPARATOR . $data_arquivo . '.csv';
        return $filename;
    }
}

?>