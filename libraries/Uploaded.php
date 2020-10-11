<?php
namespace manguto\cms7\libraries;

/**
 * Classe de suporte para tratamento de arquivos enviados.
 *
 * @author Marcos Torres
 */
class Uploaded
{

    private $fieldName;

    private $uploaded_file_array = [];

    private $path = false;

    private $random_name = false;

    private $random_name_prefix = '';

    private $file_size_max;

    private $logs = [];

    const LOG_TYPE_SUCCESS = 'success';

    const LOG_TYPE_ERROR = 'error';

    const units = [
        'KB' => 1000, // kilo bytes
        'MB' => 1000000, // mega bytes
        'GB' => 1000000000 // giga bytes
    ];

    const status = [
        'started', // objeto inicializado com sucesso
        'saved', // arquivos salvos com sucesso
        'error' // erros
    ];

    /**
     * parametros extra de controle de cada arquivo
     *
     * @var array
     */
    const extra_parameters = [
        'filename' => '',
        'full_filename' => '',
        'saved' => ''
    ];

    // file upload common errors
    const error_description = [
        UPLOAD_ERR_OK => "Não houve erro, o upload foi bem sucedido", // 0

        UPLOAD_ERR_INI_SIZE => "O arquivo enviado excede o limite definido na diretiva upload_max_filesize do php.ini", // 1

        UPLOAD_ERR_FORM_SIZE => "O arquivo excede o limite definido em MAX_FILE_SIZE no formulário HTML", // 2

        UPLOAD_ERR_PARTIAL => "O upload do arquivo foi feito parcialmente", // 3

        UPLOAD_ERR_NO_FILE => "Nenhum arquivo foi enviado", // 4

        UPLOAD_ERR_NO_TMP_DIR => "Pasta temporária ausente. Introduzido no PHP 5.0.3", // 6

        UPLOAD_ERR_CANT_WRITE => "Falha em escrever o arquivo em disco. Introduzido no PHP 5.1.0", // 7

        UPLOAD_ERR_EXTENSION => "Uma extensão do PHP interrompeu o upload do arquivo. O PHP não fornece uma maneira de determinar qual extensão causou a interrupção. Examinar a lista das extensões carregadas com o phpinfo() pode ajudar. Introduzido no PHP 5.2.0" // 8
    ];

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    //
    public function __construct(string $fieldName, string $path = __DIR__)
    {
        $this->fieldName = $fieldName;
        $this->path = $path;
        $this->setContent();
        $this->setContentExtraParameters();
        $this->setContentFilenames();
        $this->setLog(self::LOG_TYPE_SUCCESS, 'Objeto construído (inicializado)!');
    }

    // ####################################################################################################
    /**
     * registra os eventos ocorridos na instancia da classe
     *
     * @param string $LOG_TYPE
     * @param string $message
     * @throws Exception
     */
    private function setLog(string $LOG_TYPE, string $message)
    {
        switch ($LOG_TYPE) {
            case self::LOG_TYPE_SUCCESS:
                $this->logs[][self::LOG_TYPE_SUCCESS] = trim($message);
                break;
            case self::LOG_TYPE_ERROR:
                $this->logs[][self::LOG_TYPE_ERROR] = trim($message);
                break;
            default:
                throw new Exception("Não foi possível registrar o evento solicitado. Tipo de registro desconhecido ('$LOG_TYPE').");
                break;
        }
    }

    // ####################################################################################################

    /**
     * obtem os eventos registrados nesta instancia
     *
     * @param string $LOG_TYPE
     * @throws Exception
     * @return string
     */
    public function getLog(string $LOG_TYPE = ''): string
    {
        $return = [];
        foreach ($this->logs as $i=>$log){
            foreach ($log as $type=>$msg){
                if($LOG_TYPE==$type){
                    $return[] = $msg;
                }
            }
        }
        return implode(chr(10), $return);
        
        /* switch ($LOG_TYPE) {
            
            case self::LOG_TYPE_SUCCESS:
                return implode(chr(10), $this->logs[self::LOG_TYPE_SUCCESS]);
                break;
            case self::LOG_TYPE_ERROR:
                return implode(chr(10), $this->logs[self::LOG_TYPE_ERROR]);
                break;
            case '':
                
                break;
            default:
                throw new Exception("Não foi possível obter os registros dos eventos solicitados. Tipo de registro desconhecido ('$LOG_TYPE').");
                break;
        } */
    }

    // ####################################################################################################
    /**
     * obtem os nomes completos dos arquivos recentemente salvos
     *
     * @return array
     */
    public function getSavedFilenames(): array
    {
        $return = [];

        foreach ($this->uploaded_file_array as $file) {
            if ($file['saved'] === true) {
                $return[] = $file['full_filename'];
            }
        }
        $this->setLog(self::LOG_TYPE_SUCCESS, "Obtenção da lista de arquivos salvos (quant.: " . sizeof($return) . ").");
        return $return;
    }

    // ####################################################################################################

    /**
     * define um estado atual do objeto
     *
     * @param string $status
     * @throws Exception
     */
    /*
     * private function setStatus(string $status, string $msg = '')
     * {
     * if (! in_array($status, self::status)) {
     * throw new Exception("Estado não permitido ('$status').");
     * }
     * $this->status[] = [
     * 'title' => $status,
     * 'msg' => $msg
     * ];
     * }
     */

    // ####################################################################################################

    /**
     * definicao de parametros extraordinários para os diversos procedimentos necessarios
     */
    /*
     * public function setExtraParameters()
     * {
     * foreach ($this->uploaded_file_array as &$file) {
     * $this->setFullFilename($file);
     * }
     * }
     */

    // ####################################################################################################

    /**
     * define o tamanho maximo de cada arquivo enviado
     *
     * @param string|int $file_size_max
     */
    public function setFileSizeMax($file_size_max)
    {
        // ajustes de tipo e formato
        $file_size_max = strtoupper(strval($file_size_max));

        { // verificacao de utilizacao de unidades
            $unit_passed = substr($file_size_max, - 2);
            foreach (self::units as $unit_temp => $mult) {
                if ($unit_temp == $unit_passed) {
                    $file_size_max = floatval(substr($file_size_max, 0, - 2)) * $mult;
                    $this->file_size_max = $file_size_max;
                    return true;
                }
            }
        }
        $this->file_size_max = intval($file_size_max);
        $this->setLog(self::LOG_TYPE_SUCCESS, "Definição de tamanho máximo para o arquivo enviado realizada ($file_size_max).");
    }

    /**
     * define uma nomenclatura aleatoria para o nova arquivo para o salvamento
     *
     * @param string $prefix
     */
    public function setRandomName(string $prefix = 'file_')
    {
        $this->random_name = true;
        $this->random_name_prefix = $prefix;
        $this->setLog(self::LOG_TYPE_SUCCESS, "Definição de nomenclatura aleatória realizada (prefixo: $prefix).");
    }

    /**
     * define o conteudo enviado
     */
    private function setContent()
    {
        $rawContent = $_FILES[$this->fieldName] ?? false;
        // deb($rawContent);

        if ($rawContent !== false) {
            foreach ($rawContent as $parameterName => $parameterValue_array) {
                foreach ($parameterValue_array as $index => $parameterValue) {
                    $this->uploaded_file_array[$index][$parameterName] = $parameterValue;
                }
            }
            $this->setLog(self::LOG_TYPE_SUCCESS, "Arquivos obtidos com sucesso (quant.: " . sizeof($this->uploaded_file_array) . ").");
        } else {
            $this->setLog(self::LOG_TYPE_ERROR, "Não foi encontrado nenhum arquivo para a chave '{$this->fieldName}'.");
        }
    }

    // ####################################################################################################
    /**
     * inicializacao dos parametros extras de cada arquivo
     */
    private function setContentExtraParameters()
    {
        foreach ($this->uploaded_file_array as &$file) {
            foreach (self::extra_parameters as $k => $v) {
                $file[$k] = $v;
            }
        }
    }

    // ####################################################################################################
    /**
     * verifica os tamanhos dos arquivos enviados
     * quanto ao limite permitido
     *
     * @return boolean
     */
    private function checkFilesSizes(): bool
    {
        $return = true;

        if (isset($this->file_size_max)) {
            foreach ($this->uploaded_file_array as $file) {
                $fsize = floatval($file['size']);
                $fname = $file['name'];
                if ($fsize > $this->file_size_max) {
                    $this->setLog(self::LOG_TYPE_ERROR, "O arquivo '$fname' ($fsize bytes) é maior do que o limite permitido ({$this->file_size_max} bytes).");
                    $return = false;
                } else {
                    $this->setLog(self::LOG_TYPE_SUCCESS, "Tamanho do arquivo '$fname' dentro do permitido ($fsize bytes).");
                }
            }
        } else {
            $this->setLog(self::LOG_TYPE_SUCCESS, "Tamanho máximo não definido. Nenhum procedimento realizado!");
        }
        return $return;
    }

    // ####################################################################################################

    /**
     * define o nome do arquivo informado para o salvamento
     *
     * @param string $name
     * @return bool
     */
    private function setFilename(array &$file): bool
    {
        { // parametros principais
            $name = $file['name'];
            $tmp_name = $file['tmp_name'];
        }
        if ($this->random_name === true) {
            $file['filename'] = uniqid($this->random_name_prefix) . '.' . File::getExtension($name);
        } else {
            $file['filename'] = $name;
        }

        //deb($tmp_name, 0); deb($file['filename']);
        $this->setLog(self::LOG_TYPE_SUCCESS, "Nome do arquivo '$tmp_name' gerado com sucesso: '{$file['filename']}'.");
        return true;
    }

    // ####################################################################################################
    /**
     * define o nome completo dos arquivos enviados obtidos
     *
     * @param array $file
     * @return boolean
     */
    private function setContentFilenames()
    {
        foreach ($this->uploaded_file_array as &$file) {
            if ($this->setFilename($file)) {
                $file['full_filename'] = $this->path . DIRECTORY_SEPARATOR . $file['filename'];
                $this->setLog(self::LOG_TYPE_SUCCESS, "Nome completo do arquivo '{$file['name']}' definido: " . $file['full_filename']);
                return true;
            } else {
                return false;
            }
        }
    }

    // ####################################################################################################
    /**
     * salva os arquivos individualmente
     *
     * @param array $file
     * @return boolean
     */
    private function saveFile(array &$file): bool
    {
        { // parametros principais
            $name = $file['name'];
            $full_filename = $file['full_filename'];
        }

        // Check if file already exists and try to save it!
        if (file_exists($full_filename)) {
            $this->setLog(self::LOG_TYPE_ERROR, "Não foi possível salvar o arquivo. Já existe um arquivo com o mesmo nome no diretório informado ('$full_filename').");
            return false;
        } else {
            if (move_uploaded_file($file["tmp_name"], $full_filename)) {
                $this->setLog(self::LOG_TYPE_SUCCESS, "Arquivo '{$full_filename}' salvo com sucesso.");
                return true;
            } else {
                $this->setLog(self::LOG_TYPE_ERROR, "Não foi possível salva o arquivo solicitado ($name). Error: " . $file["error"]);
                return false;
            }
        }
    }

    // ####################################################################################################
    /**
     * realiza uma verificacao geral
     * com base nos parametros definidos
     *
     * @return boolean
     */
    public function check(): bool
    {
        $return = true;
        { // verificacoes gerais!

            { // uploaded files error
                foreach ($this->uploaded_file_array as $file) {
                    $error_id = intval($file['error']);
                    if ($error_id != 0) {
                        $this->setLog(self::LOG_TYPE_SUCCESS, self::error_description[$error_id] ?? "Erro #$error_id ");
                        $return = false;
                    }
                }
            }

            { // file size max
                if ($this->checkFilesSizes()) {
                    $return = false;
                }
            }
        }
        return $return;
    }

    // ####################################################################################################
    /**
     * realiza o salvamento dos arquivos enviados em questao
     *
     * @param string $path
     * @param boolean $file_size_max
     * @param bool $randomName
     * @param string $randomNamePrefix
     * @return boolean
     */
    public function save(): bool
    {
        { // verificacao geral
            if (! $this->check()) {
                return false;
            }
        }
        $return = true;
        { // salvamento propriamente dito
            foreach ($this->uploaded_file_array as &$file) {
                if (! $this->saveFile($file)) {
                    $return = false;
                }
            }
        }
        return $return;
    }

    // ####################################################################################################
    // ############################################################################################# STATIC
    // ####################################################################################################

    /**
     * obtem um array com as informacoes de todos os arquivos enviados
     *
     * @return array
     */
    static function GetUploadedFilesInfo(): array
    {
        $return = [];
        foreach ($_FILES as $name => $files_info) {
            foreach ($files_info as $file_info_key => $files_info_value) {
                foreach ($files_info_value as $key => $file_info_value) {
                    $return[$name][$key][$file_info_key] = $file_info_value;
                }
            }
        }
        return $return;
    }

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>