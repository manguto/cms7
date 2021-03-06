<?php
namespace manguto\cms7\libraries;

class File
{

    // mascara para copia de reserva
    const backupCopy_dateMasc = 'Y-m-d_His';

    // define quantas iteracoes sao consideradas normais para a tentativa de escrita controlada
    const wait_loop_limit = 3;

    // ####################################################################################################
    // ############################################################################################ PUBLIC
    // ####################################################################################################
    public function __construct()
    {}

    // ####################################################################################################
    // ############################################################################################ STATIC
    // ####################################################################################################

    /**
     * obtem a extensao do arquivo informado
     *
     * @param string $path
     * @return mixed
     */
    static function getExtension(string $path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    // ####################################################################################################
    /**
     * obtem o tamanho do arquivo informado
     *
     * @param string $path
     * @return number
     */
    static function getFileSize(string $path)
    {
        return filesize($path);
    }

    // ####################################################################################################
    /**
     * obterm o caminho onde se encontra o arquivo ou caminho informado
     *
     * @param string $path
     * @return string
     */
    static function getPath(string $path)
    {
        return pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
    }

    // ####################################################################################################
    /**
     * obtem o nome do arquivo
     *
     * @param string $path
     * @param bool $withExtension
     * @return mixed
     */
    static function getBaseName(string $path, bool $withExtension = true)
    {
        if ($withExtension) {
            return pathinfo($path, PATHINFO_BASENAME);
        } else {
            return pathinfo($path, PATHINFO_FILENAME);
        }
    }

    // ####################################################################################################
    /**
     * optem o nome especifico da pasta
     *
     * @param string $path
     * @return string
     */
    static function getFolderName(string $path): string
    {
        $return = basename(dirname($path));
        return $return;
        /*
         * $path = Diretorios::fixDirectorySeparator($path);
         * $path = explode(DIRECTORY_SEPARATOR, $path);
         * // debug($filepath);
         * { // ---verificacao se parametro eh de uma pasta ou de um arquivo
         * if (strpos($path[sizeof($path) - 1], '.') !== false) {
         * $arquivo = true;
         * } else {
         * $arquivo = false;
         * }
         * }
         * if ($arquivo) {
         * array_pop($path);
         * }
         *
         * $path = array_pop($path);
         * return $path;
         */
    }

    // ####################################################################################################
    /**
     * obtem o conteudo (string) de um arquivo
     *
     * @param string $filename
     * @param boolean $throwException
     * @throws Exception
     * @return string
     */
    static function getContent(string $filename, $throwException = true): string
    {
        // config
        {
            // extensoes de arquivos nao retornaveis
            $extBlocked = [
                'rar',
                'zip'
            ];
        }
        { // verificacao de tipo de arquivo
            $ext = strtolower(self::getExtension($filename));
            if (in_array($ext, $extBlocked)) {
                return 'Conteúdo não renderizável.';
            }
        }

        // ############ log::open(__METHOD__,"Obtém o conteúdo do arquivo '$filename'.");
        if (file_exists($filename)) {
            // ############ log::add("Arquivo encontrado.");
            $return = file_get_contents($filename);
            if ($return !== false) {
                // ############ log::add("Conteúdo do arquivo obtido com sucesso.");
                return $return;
            } else {
                if ($throwException === true) {
                    throw new Exception("Não foi possível realizar a leitura do arquivo solicitado ('$filename').");
                } else {
                    // ############ log::add("Não foi possível realizar a leitura do arquivo solicitado ('$filename').");
                    return '';
                }
            }
        } else {
            if ($throwException === true) {
                throw new Exception("Arquivo (e consequentemente seu conteúdo) não encontrado. ('$filename').");
            } else {
                // ############ log::add("Arquivo (e consequentemente seu conteúdo) não encontrado. ('$filename').");
                return '';
            }
        }
    }

    // ####################################################################################################
    /**
     * Salva um texto ($data) em um arquivo ($filename)
     * e caso o parametro $flags esteja especificado como
     * FILE_APPEND, apenas incrementa o conteudo do mesmo
     * ao inves de sobescrever.
     *
     * @param string $filename
     * @param string $data
     * @param
     *            $flags
     * @throws Exception
     * @return bool
     */
    static function writeContent(string $filename, string $data, $flags = NULL, $throwException = true): bool
    {
        // ---verificar diretorio
        $caminho = self::getPath($filename);
        if(Diretorios::mkdir($caminho)){
            // ---verificar conteudo
            $data = $data == '' ? ' ' . chr(10) : $data;
            // ---salvar o arquivo
            if (! file_put_contents($filename, $data, $flags)) {
                if ($throwException) {
                    // echo exec('whoami');
                    $error = implode('<br/>', error_get_last());
                    throw new Exception("Não foi possível salvar o arquivo '$filename'.<hr/>$error");
                } else {
                    return false;
                }
            }
            chmod($filename, 0755);
            return true;
        }else{
            if ($throwException) {
                // echo exec('whoami');
                $error = implode('<br/>', error_get_last());
                throw new Exception("Não foi possível criar o diretório '$caminho'.<hr/>$error");
            } else {
                return false;
            }
        }

        
    }

    // ####################################################################################################
    /**
     * clona um arquivo
     *
     * @param string $filenameOrigem
     * @param string $filenameDestino
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    static function copy(string $filenameSource, string $filenameDestination, $throwException = true)
    {
        if (! file_exists($filenameDestination)) {
            self::writeContent($filenameDestination, ' ' . chr(10));
        }

        if (copy($filenameSource, $filenameDestination) == false) {
            if ($throwException) {
                throw new Exception("Não foi possível copiar o arquivo ($filenameDestination).");
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    // ####################################################################################################
    /**
     * exclui um determinado arquivo
     *
     * @param string $filename
     * @param bool $throwException
     * @throws Exception
     * @return boolean
     */
    static function delete(string $filename, bool $throwException = true): bool
    {
        // ---verificar diretorio
        if (file_exists($filename)) {
            if (! unlink($filename)) {
                if ($throwException) {
                    throw new Exception("Não foi possível excluir o arquivo solicitado ($filename).");
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            if ($throwException) {
                throw new Exception("Arquivo não encontrado ($filename).");
            } else {
                return false;
            }
        }
        return true;
    }

    // ####################################################################################################
    /**
     * verifica se os arquivos são identicos
     *
     * @param string $filename_a
     * @param string $filename_b
     * @return boolean
     */
    static function verificarArquivosIdenticos(string $filename_a, string $filename_b)
    {
        // Check if filesize is different
        if (filesize($filename_a) !== filesize($filename_b)) {
            return false;
        }

        // Check if content is different
        $ah = fopen($filename_a, 'rb');
        $bh = fopen($filename_b, 'rb');

        $result = true;
        while (! feof($ah)) {
            if (fread($ah, 8192) != fread($bh, 8192)) {
                $result = false;
                break;
            }
        }

        fclose($ah);
        fclose($bh);

        return $result;
    }

    // ####################################################################################################
    /**
     * verifica se um arquivo ou pasta existem
     *
     * @param string $fileOrFolderName
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    static function verificarArquivoOuPastaExiste(string $fileOrFolderName, bool $throwException = true)
    {
        if (file_exists($fileOrFolderName) == false) {
            if ($throwException) {
                throw new Exception("Arquivo ou pasta não encontrado(a) ($fileOrFolderName).");
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    // ####################################################################################################
    /**
     * obtem as permissoes atuais de um arquivo ou pasta
     *
     * @param string $fileOrFolderName
     * @param boolean $throwException
     * @throws Exception
     * @return boolean|int
     */
    static function permissoesObter(string $fileOrFolder_name, bool $throwException = true)
    {
        $return = false;
        if (self::verificarArquivoOuPastaExiste($fileOrFolder_name, $throwException)) {
            $return = decoct(fileperms($fileOrFolder_name) & 0777);
            $return = intval($return);
        }
        return $return;
    }

    // ####################################################################################################
    /**
     * altera as permissoes de uma determinado arquivo ou pasta
     *
     * @param string $fileOrFolderName
     * @param int $permissoesNovas
     * @param boolean $throwException
     * @throws Exception
     */
    static function permissoesAlterar(string $fileOrFolderName, int $newPermissions, bool $throwException = true)
    {
        if (File::verificarArquivoOuPastaExiste($fileOrFolderName, $throwException)) {
            if (chmod($fileOrFolderName, $newPermissions) == false) {
                if ($throwException) {
                    $permissionsActual = self::permissoesObter($fileOrFolderName, $throwException);
                    throw new Exception("Não foi possível alterar as permissões do arquivo ou pasta solicitado ($fileOrFolderName ['$permissionsActual']) para '$newPermissions'.");
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    // ####################################################################################################

    /**
     * realiza uma copia de seguranca do arquivo
     * reproduzindo-o em uma pasta e
     * utilizando uma mascara de tempo
     *
     * @param string $filename
     * @param string $dateMasc
     * @param bool $throwException
     * @throws Exception
     * @return bool
     */
    static function copiaSeguranca(string $filename, bool $dateMascSideRight = true, string $dateMasc = '', string $backupFolderName = '', bool $throwException = false): bool
    {
        if (file_exists($filename)) {

            { // backup filepath
                { // datemasc
                    $dateMasc = $dateMasc == '' ? self::backupCopy_dateMasc : $dateMasc;
                    $date = date($dateMasc);
                }
                if ($dateMascSideRight) {
                    $date = "_{$date}";
                    $filename_new = str_replace('.', $date . '.', $filename);
                } else {
                    $date = "{$date}_";
                    $filename_ext = self::getBaseName($filename);
                    $filename_new = str_replace($filename_ext, $date . $filename_ext, $filename);
                }
                { // folder stuff
                    {
                        $backupFolderName = trim($backupFolderName);
                        $backupFolderName = $backupFolderName != '' ? $backupFolderName . DIRECTORY_SEPARATOR : '';
                    }
                    $filename_new = $backupFolderName . $filename_new;
                }
                { // file type change (rename extension ex.: php)
                    $filename_new = str_replace('.php', '.php_', $filename_new);
                }
            }

            self::copiarArquivo($filename, $filename_new, true);
            return true;
        } else {
            if ($throwException) {
                throw new Exception("Não foi possível realizar a cópia do arquivo '$filename', pois este não foi encontrado.");
            }
            return false;
        }
    }

    // ###################################################################################################################################################
    // ############################################################################################################################## CONTROLE DE ESCRITA
    // ###################################################################################################################################################

    /**
     * escreve um arquivo tendo o cuidado de que este não esteja sendo escrito por outro processo.
     *
     * @param string $filename
     * @param string $data
     * @param
     *            $flags
     * @param boolean $throwException
     * @param int $loop
     *            - NÃO UTILIZAR (VARIAVEL DE CONTROLE)
     * @throws Exception
     * @return bool
     */
    static function escreverConteudoControlado(string $filename, string $data, $flags = NULL, $throwException = true, int $loop = 0): bool
    {
        if (self::arquivoEstaTravado($filename) == false) {
            self::arquivoTravar($filename);
            $return = self::writeContent($filename, $data, $flags, $throwException);
            self::arquivoDestravar($filename);
        } else {
            // aguarda alguns milisegundos
            sleep(1);
            // quantidade de 'loops' em espera foi atingido?
            if ($loop < self::wait_loop_limit) {
                // tenta escrever novamente
                $return = self::escreverConteudoControlado($filename, $data, $flags, $throwException, ($loop + 1));
            } else {
                throw new Exception("Não foi possível salvar o conteúdo do arquivo controlado: '$filename'. Tente novamente em breve!");
            }
        }
        return $return;
    }

    static private function arquivoTravar($filename)
    {
        $filename_temp = $filename . '_';
        self::writeContent($filename_temp, "TRAVA CONTRA ESCRITA DO ARQUIVO: $filename");
    }

    static private function arquivoEstaTravado($filename)
    {
        $filename_temp = $filename . '_';
        if (file_exists($filename_temp)) {
            return true;
        } else {
            return false;
        }
    }

    static private function arquivoDestravar($filename)
    {
        $filename_temp = $filename . '_';
        if (self::arquivoEstaTravado($filename)) {
            self::delete($filename_temp);
        }
    }

    // ###################################################################################################################################################

    // ###################################################################################################################################################
    // ###################################################################################################################################################
    // ###################################################################################################################################################
}

?>