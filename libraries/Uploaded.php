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

    private $masc_name = false;

    private $masc_name_model = 'temp_{$uniqid}_{$filename}';

    const masc_name_parameters = [
        '{$uniqid}' => 'Conjunto de caracteres únicos.',
        '{$basename}' => 'Nome do original do arquivo adaptado.'
    ];

    private $file_size_max;

    private $logs = [];

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
        $this->setLog('info', 'Inicializando...');
        $this->fieldName = $fieldName;
        $this->path = $path;

        if ($this->setContent()) {
            $this->setContentExtraParameters();
            $this->setContentFilenames();
        } else {
            throw new Exception($this->getLog('error'));
        }
    }

    // ####################################################################################################
    /**
     * registra os eventos ocorridos na instancia da classe
     *
     * @param string $LOG_TYPE
     * @param string $message
     * @throws Exception
     */
    private function setLog(string $type, string $message)
    {
        $this->logs[] = [
            'type' => $type,
            'message' => trim($message)
        ];
    }

    // ####################################################################################################

    /**
     * obtem os eventos registrados nesta instancia
     *
     * @param string $LOG_TYPE
     * @throws Exception
     * @return string
     */
    public function getLog(string $type = ''): string
    {
        $return = [];
        $logs = $this->logs;
        asort($logs);

        foreach ($this->logs as $i) {
            $type_temp = $i['type'];
            $message = $i['message'];
            if ($type_temp == $type || $type == '') {
                $return[] = "$message";
            }
        }

        return implode(chr(10), $return);
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
        $this->setLog('info', "Obtenção dos nomes dos arquivos salvos...");
        foreach ($this->uploaded_file_array as $file) {
            if ($file['saved'] === true) {
                $return[] = $file['full_filename'];
            }
        }
        $this->setLog('success', "Arquivos salvos com sucesso: " . sizeof($return) . ".");
        return $return;
    }

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
        $this->setLog('success', "Definição de tamanho máximo para o arquivo enviado realizada ($file_size_max).");
    }

    /**
     * define uma nomenclatura aleatoria para o nova arquivo para o salvamento
     * OBS.: a mascara pode possuir os termos dinamicos definidos pela contante 'masc_file_model'
     *
     * @param string $filename_masc
     */
    public function setRandomName(string $filename_masc = 'file_{$uniqid}_{$filename}')
    {
        $this->masc_name = true;
        $this->masc_name_model = $filename_masc;
        $this->setLog('success', "Definição de nomenclatura aleatória realizada (modelo: $filename_masc).");
        $this->setContentFilenames();
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
            $this->setLog('success', "Arquivos obtidos com sucesso (quant.: " . sizeof($this->uploaded_file_array) . ").");
            return true;
        } else {
            $this->setLog('error', "Não foi encontrado nenhum arquivo para a chave informada: '{$this->fieldName}'.");
            return false;
        }
    }

    // ####################################################################################################
    /**
     * inicializacao dos parametros extras de cada arquivo
     */
    private function setContentExtraParameters()
    {
        $this->setLog('info', "Definição de parametros extraordinário iniciada...");
        foreach ($this->uploaded_file_array as &$file) {
            foreach (self::extra_parameters as $k => $v) {
                if (! isset($file[$k])) {
                    $file[$k] = $v;
                }
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
        $this->setLog('info', "Verificação de tamanho máximo de arquivos iniciada...");
        if (isset($this->file_size_max)) {
            foreach ($this->uploaded_file_array as $file) {
                $fsize = floatval($file['size']);
                $fname = $file['name'];
                if ($fsize > $this->file_size_max) {
                    $this->setLog('error', "O arquivo '$fname' ($fsize bytes) é maior do que o limite permitido ({$this->file_size_max} bytes).");
                    $return = false;
                } else {
                    $this->setLog('success', "Tamanho do arquivo '$fname' dentro do permitido ($fsize bytes).");
                }
            }
        } else {
            $this->setLog('success', "Tamanho máximo não definido. Nenhum procedimento realizado!");
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
            $basename = File::getBaseName($name, false); // normalizado
            $extension = File::getExtension($name);
            $uniqid = uniqid();
        }
        if ($this->masc_name === true) {
            $fileModelName = $this->masc_name_model;
            // debc($fileModelName);
            // verifica se algumas dos parametros dinamicos foi utilizado
            foreach (array_keys(self::masc_name_parameters) as $key) {
                {
                    $replace = Strings::Slugify($key);
                    $replace = $$replace ?? '_';
                }
                $fileModelName = str_replace($key, $replace, $fileModelName);
            }
        } else {
            $fileModelName = $basename;
        }
        {
            // debc($fileModelName);
            $filename = $fileModelName . '.' . $extension;
            $filename = Strings::Slugify($filename);
        }
        $file['filename'] = $filename;
        $this->setLog('success', "Novo nome para o arquivo realizado! '$tmp_name' => '{$file['filename']}'.");
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
        $this->setLog('info', "Definição de nome completo iniciada...");
        $return = true;
        foreach ($this->uploaded_file_array as &$file) {
            if ($this->setFilename($file)) {
                $file['full_filename'] = $this->path . DIRECTORY_SEPARATOR . $file['filename'];
                $this->setLog('success', "Nome completo do arquivo '{$file['name']}' definido: " . $file['full_filename']);
            } else {
                $return = false;
            }
        }
        return $return;
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
        $this->setLog('info', "Salvamento do arquivo '$name' iniciada...");

        // Check if file already exists and try to save it!
        if (file_exists($full_filename)) {
            $this->setLog('error', "Não foi possível salvar o arquivo. Já existe um arquivo com o mesmo nome no diretório informado ('$full_filename').");
            return false;
        } else {
            
            
            if (File::copy($file["tmp_name"], $full_filename,false)) {
                $file['saved'] = true;
                $this->setLog('success', "Arquivo '{$full_filename}' salvo com sucesso.");
                return true;
            } else {
                $file['saved'] = false;
                $this->setLog('error', "Não foi possível salva o arquivo solicitado ($name => $full_filename). Error: " . $file["error"]);
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
        $this->setLog('info', "Verificação geral iniciada...");
        $return = true;
        { // verificacoes gerais!

            { // uploaded files error
                foreach ($this->uploaded_file_array as $file) {
                    $error_id = intval($file['error']);
                    if ($error_id != 0) {
                        $this->setLog('success', self::getErrorMessage($error_id));
                        $return = false;
                    }
                }
            }

            { // file size max
                if (! $this->checkFilesSizes()) {
                    $return = false;
                }
            }
        }
        return $return;
    }

    // ####################################################################################################
    /**
     * obtem a descricao do erro com base no codigo informado
     *
     * @param int $error_id
     * @return string
     */
    private static function getErrorMessage(int $error_id)
    {
        return self::error_description[$error_id] ?? "Erro #$error_id";
    }

    // ####################################################################################################
    /**
     * realiza o salvamento dos arquivos enviados em questao
     *
     * @param string $path
     * @param boolean $file_size_max
     * @param bool $mascName
     * @param string $mascNamePrefix
     * @return boolean
     */
    public function save(): bool
    {
        $this->setLog('info', "Salvamento geral iniciado...");
        $return = true;

        if (! $this->check()) {
            return false;
        } else {
            $uploaded_file_array = $this->uploaded_file_array;
            foreach ($uploaded_file_array as &$file) {
                if (! $this->saveFile($file)) {
                    $return = false;
                }
            }
            $this->uploaded_file_array = $uploaded_file_array;
        }

        return $return;
    }

    // ####################################################################################################
    // ############################################################################################# STATIC
    // ####################################################################################################
    /**
     * verifica se o campo informado possui algum arquivo enviado (uploaded)
     * @param string $fieldname
     * @return boolean
     */
    static function CheckUploadedFiles(string $fieldname)
    {
        { // all uploaded files info
            $UFI = self::GetUploadedFilesInfo();
        }
        if (isset($UFI[$fieldname]) && sizeof($UFI[$fieldname]) > 0) {
            return true;
        } else {
            return false;
        }
    }

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
            // deb($files_info,0);
            // quantidade de arquivos encontrados para o parametro atual
            $q = sizeof($files_info['name']);

            //percorre array de dados
            for ($i = 0; $i < $q; $i ++) {
                $error = intval($files_info['error'][$i]);
                if ($error == 0) {
                    $return[$name][$i]['error'] = $error;
                    $return[$name][$i]['name'] = $files_info['name'][$i];
                    $return[$name][$i]['type'] = $files_info['type'][$i];
                    $return[$name][$i]['tmp_name'] = $files_info['tmp_name'][$i];
                    $return[$name][$i]['size'] = $files_info['size'][$i];
                } else if ($error == 4) {
                    // nenhum arquivo enviado
                    continue;
                } else {
                    throw new Exception(self::getErrorMessage($error));
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