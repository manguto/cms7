<?php
namespace manguto\cms7\libraries;

/**
 * Classe de suporte para
 * tratamento de arquivos
 * enviados via formulario.
 *
 * @author Marcos Torres
 */
class ArquivosEnviados
{

    public function __construct()
    {}

    static function ObterArquivosEnviados()
    {
        // deb($_FILES);
        $arquivos = [];
        foreach ($_FILES as $id => $files_info) {
            // deb($files_info);
            foreach ($files_info as $file_info_key => $files_info_value) {
                foreach ($files_info_value as $key => $file_info_value) {
                    $arquivos[$key][$file_info_key] = $file_info_value;
                }
            }
        }
        return $arquivos;
    }
    
    /**
     * Realiza o salvamento de um arquivo enviado atraves de um formulario com base nos parametros solicitados, e retorna o nome do arquivo salvo
     * @param string $fieldName
     * @param string $targetDir
     * @param bool $randomizeName
     * @param int $filesize
     * @param bool $throwException
     * @throws Exception
     * @return boolean|string
     */
    static function SalvarArquivo(string $fieldName,string $targetDir='data',bool $randomName=true,int $filesize=500000,bool $throwException=true)
    {
        if(isset($_FILES[$fieldName])){
            
            {//filename
                $filename = basename($_FILES[$fieldName]["name"]);                
                if($randomName){
                    $ext = Files::getExtension($filename);
                    $filename = uniqid('arquivo_').'.'.$ext;
                }
            }            
            
            $target_file = $targetDir . $filename;
            
            //cotrole parameter
            $uploadOk = true;
            
            // Check if file already exists
            if (file_exists($target_file)) {
                $msg = "Não foi possível salvar o arquivo. Já existe outro arquivo com o mesmo nome no sistema ($target_file).";
                $uploadOk = false;
            }
            // Check file size
            $fileUploadedSize =$_FILES[$fieldName]["size"];
            if ($fileUploadedSize > $filesize) {
                $msg = "Não foi possível salvar o arquivo. O tamanho excede o permitido ($fileUploadedSize > $filesize).";
                $uploadOk = false;
            }
            
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk === true) {
                if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], $target_file) !== true) {
                    $msg = "Sorry, there was an error uploading your file.";
                    $saveok = false;
                }else{
                    $saveok = true;
                }
            }
        }else{
            $saveok = false;
            $msg = "Parâmetro não encontrado na listagem de arquivos enviados! => \$_FILES['$fieldName']";
        }
        
        if($saveok == false){
            if($throwException){
                throw new Exception($msg);
            }else{
                return false;
            }            
        }else{
            return $target_file;
        }
    }
}

?>