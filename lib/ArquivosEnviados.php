<?php
namespace manguto\cms5\lib;

/**
 * Classe de suporte para
 * tratamento de arquivos
 * enviados via formulario.
 *
 * @author Marcos Torres
 */
class ArquivosEnviados
{
    
    public function __construct(){
        
    }
    
    static function ObterArquivosEnviados(){
        //deb($_FILES);
        $arquivos = [];
        foreach ($_FILES as $id=>$files_info){
            //deb($files_info);
            foreach ($files_info as $file_info_key=>$files_info_value){
                foreach ($files_info_value as $key=>$file_info_value){
                    $arquivos[$key][$file_info_key] = $file_info_value;
                }
            }             
        }
        return $arquivos;
    }
    
}

?>