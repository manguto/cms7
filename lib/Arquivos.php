<?php
namespace manguto\cms5\lib;

class Arquivos
{
    
    const backupCopy_dateMasc = 'Y-m-d_His';

    static function obterExtensao($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    static function obterTamanho($path)
    {
        return filesize($path);
    }

    static function obterCaminho($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;        
    }

    static function obterNomeArquivo($path, $withExtension = true)
    {
        
        if($withExtension){
            return pathinfo($path, PATHINFO_BASENAME);
        }else{
            return pathinfo($path, PATHINFO_FILENAME);
        }
        
        /*{//verificacao de delimitador utilizado (separador de pastas e arquivos)
            $teste = strpos($filenamePath, chr(47)); // '/'
            if ($teste !== false) {
                $delimitador = chr(47); // '/'
            } else {
                $delimitador = chr(92); // '\';
            }
        }        

        {//obtencao do nome do arquivo completo (sem o caminho)
            $filenamePath_parts = explode($delimitador, $filenamePath);
            $filename = array_pop($filenamePath_parts);
        }        

        { // se solicitado, remocao da extensao
            if ($withExtension === false) {
                $ext = self::obterExtensao($filename);
                $filename = str_replace('.' . $ext, '', $filename);
            }
        }

        return $filename;*/
    }

    static function obterNomePasta($path)
    {

        $path = Diretorios::fixDirectorySeparator($path);
        $path = explode(DIRECTORY_SEPARATOR, $path);
        // debug($filepath);
        { // ---verificacao se parametro eh de uma pasta ou de um arquivo
            if (strpos($path[sizeof($path) - 1], '.') !== false) {
                $arquivo = true;
            } else {
                $arquivo = false;
            }
        }
        if ($arquivo) {
            array_pop($path);
        }

        $path = array_pop($path);
        return $path;
    }

    /**
     * OBTEM O CONTEUDO (STRING) DE UM ARQUIVO
     * E CASO NÃO O ENCONTRE, SOLTA UMA EXCESSÃO.
     *
     * @throws Exception
     */
    static function obterConteudo($filename, $throwException = true)
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
            $ext = strtolower(self::obterExtensao($filename));
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
        // ############ log::close();
    }

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
    static function escreverConteudo(string $filename, string $data, $flags = NULL, $throwException = true)
    {
        // ---verificar diretorio
        $caminho = self::obterCaminho($filename);
        Diretorios::mkdir($caminho);
        // ---verificar conteudo
        $data = $data == '' ? ' ' . chr(10) : $data;
        // ---salvar o arquivo
        if (! file_put_contents($filename, $data, $flags)) {
            if ($throwException) {
                throw new Exception("Não foi possível salvar o arquivo solicitado ($filename).");
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * clona um arquivo
     *
     * @param string $filenameOrigem
     * @param string $filenameDestino
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    static function copiarArquivo(string $filenameSource, string $filenameDestination, $throwException = true)
    {
        if(!file_exists($filenameDestination)){
            self::escreverConteudo($filenameDestination, ' '.chr(10));
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

    /**
     * exclui um determinado arquivo
     *
     * @param string $filename
     * @param bool $throwException
     * @throws Exception
     * @return boolean
     */
    static function excluir(string $filename, bool $throwException = true)
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

    /**
     * verifica se os arquivos são identicos
     * @param string $filename_a
     * @param string $filename_b
     * @return boolean
     */
    static function verificarArquivosIdenticos(string $filename_a,string $filename_b)
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

    /**
     * verifica se um arquivo ou pasta existem
     *
     * @param string $filename_or_foldername
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    static function verificarArquivoOuPastaExiste(string $filename_or_foldername, bool $throwException = true)
    {
        if (file_exists($filename_or_foldername) == false) {
            if ($throwException) {
                throw new Exception("Arquivo ou pasta não encontrado(a) ($filename_or_foldername).");
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * obtem as permissoes atuais de um arquivo ou pasta,
     * onde caso seja uma pasta, cria-a caso esta não exista.
     *
     * @param string $filename_or_foldername
     * @param boolean $throwException
     * @throws Exception
     * @return boolean|string
     */
    static function permissoesObter(string $filename_or_foldername, bool $throwException = true)
    {
        $ArquivoOuPastaExiste = Arquivos::verificarArquivoOuPastaExiste($filename_or_foldername,$throwException);
        
        if ($ArquivoOuPastaExiste==TRUE) {            
            $fileperms = decoct(fileperms($filename_or_foldername) & 0777);
            // deb($fileperms);
            return $fileperms;
        }else{
            
            //se nao for um arquivo...            
            $eh_diretorio = self::obterExtensao($filename_or_foldername)=='' ? true : false;                        
            
            if($eh_diretorio){
                Diretorios::mkdir($filename_or_foldername,true);
                return self::permissoesObter($filename_or_foldername,$throwException);
            }
        }
    }

    /**
     * altera as permissoes de uma determinado arquivo ou pasta
     * @param string $filename_or_foldername
     * @param int $permissoesNovas
     * @param boolean $throwException
     * @throws Exception
     */
    static function permissoesAlterar(string $filename_or_foldername,int $permissoesNovas, $throwException = true)
    {   
        if (Arquivos::verificarArquivoOuPastaExiste($filename_or_foldername,$throwException)) {
            if(chmod($filename_or_foldername, $permissoesNovas)==false){
                if($throwException){
                    throw new Exception("Não foi possível alterar as permissões do arquivo ou pasta solicitado ($filename_or_foldername).");
                }else{
                    return false;
                }                
            }else{
                return true;
            }
        }
    }
    
    /**
     * realiza uma copia de seguranca do arquivo
     * reproduzindo-o em uma pasta e  
     * utilizando uma mascara de tempo
     * @param string $filename
     * @param string $dateMasc
     * @param bool $throwException
     * @throws Exception
     * @return bool
     */
    static function copiaSeguranca(string $filename,bool $dateMascSideRight=true,string $dateMasc='',string $backupFolderName = '',bool $throwException=false):bool{
        
        if(file_exists($filename)){
            
            {//backup filepath
                {//datemasc                    
                    $dateMasc = $dateMasc=='' ? self::backupCopy_dateMasc : $dateMasc;                    
                    $date = date($dateMasc);
                }
                if($dateMascSideRight){
                    $date = "_{$date}";
                    $filename_new = str_replace('.', $date.'.', $filename);                    
                }else{
                    $date = "{$date}_";
                    $filename_ext = self::obterNomeArquivo($filename);
                    $filename_new = str_replace($filename_ext, $date.$filename_ext,$filename);
                }
                {//folder stuff
                    {
                        $backupFolderName = trim($backupFolderName);
                        $backupFolderName = $backupFolderName!='' ? $backupFolderName.DIRECTORY_SEPARATOR : '';
                    }                     
                    $filename_new = $backupFolderName.$filename_new;
                }
                {//file type change (rename extension ex.: php) 
                    $filename_new = str_replace('.php', '.php_', $filename_new);
                }
            }
            
            self::copiarArquivo($filename, $filename_new,true);
            return true;
        }else{
            if($throwException){
                throw new Exception("Não foi possível realizar a cópia do arquivo '$filename', pois este não foi encontrado.");
            }
            return false;
        }
    }
    
    //###################################################################################################################################################
    //###################################################################################################################################################
    //###################################################################################################################################################
    
    /**
     * escreve um arquivo tendo o cuidado de que este não seja escrito durante o processo.
     * @param string $filename
     * @param string $data
     * @param $flags
     * @param boolean $throwException
     */
    static function escreverConteudoControlado(string $filename, string $data, $flags = NULL, $throwException = true)
    {
        if(self::arquivoEstaTravado($filename)==false){            
            self::arquivoTravar($filename);
            self::escreverConteudo($filename, $data,$flags,$throwException);
            self::arquivoDestravar($filename);            
        }else{            
            sleep(1);            
            self::escreverConteudoControlado($filename, $data,$flags,$throwException);
        }       
    }
    
    static private function arquivoTravar($filename){
        $filename_temp = $filename.'_';
        self::escreverConteudo($filename_temp, "TRAVA CONTRA ESCRITA DO ARQUIVO: $filename");
    }
    
    static private function arquivoEstaTravado($filename){
        $filename_temp = $filename.'_';
        if(file_exists($filename_temp)){
            return true;
        }else{
            return false;
        }
    }
    
    static private function arquivoDestravar($filename){
        $filename_temp = $filename.'_';
        if(self::arquivoEstaTravado($filename)){
            self::excluir($filename_temp);
        }        
    }
    
    
    //###################################################################################################################################################
    //###################################################################################################################################################
    //###################################################################################################################################################
}

?>