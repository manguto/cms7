<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\ServerHelp;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Log;

class CMSHelp
{

    static function Initialization()
    {
        if (defined('VIRTUAL_HOST_ACTIVE')) {

            define('ROOT', CMSHelp::ROOT());

            define('ROOT_ACTION', CMSHelp::ROOT_ACTION());

            define('ROOT_LOCATION', CMSHelp::ROOT_LOCATION());

            define('ROOT_TPL', CMSHelp::ROOT_TPL());
            
            define('ROOT_SIS', CMSHelp::ROOT_SIS());
            
            //--------------------------------------
            
            self::SUBVERSION_CSS();
            
            self::FOLDERS_PERMISSIONS();

            self::SERVER_PARAMETERS_TUNE();

            self::EXTRA_PARAMETERS();
            
            self::HTML_AUX();
            
            self::LOG();
            
            self::DEBUG();
            
            
        } else {
            throw new Exception("A constante 'VIRTUAL_HOST_ACTIVE' não foi definida. Defina-a no arquivo de CONFGURAÇÕES e tente novamente.");
        }
    }
    
    private static function  SUBVERSION_CSS(){
        //development flag (foldername has a digit at its end!)
        define("SUBVERSION", is_numeric(substr(SIS_FOLDERNAME, -1,1)) ? true : false);
        
        if(SUBVERSION){
            $SUBVERSION_CSS = "<style>header,section,footer {border-top:solid 5px #f00;}</style>";
        }else{
            $SUBVERSION_CSS = '';
        }
        define("SUBVERSION_CSS", $SUBVERSION_CSS);        
    }
    
    private static function DEBUG() {
        CMSDebug::Start();
    }
    
    private static function LOG(){        
        Log::Go();        
    }

    private static function EXTRA_PARAMETERS()
    {
        // PRIMARY PARAMETERS <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

        // actualPlatform
        $http_host = $_SERVER['HTTP_HOST'];
        // deb($http_host);

        $php_self = $_SERVER['PHP_SELF'];
        // deb($php_self);

        $request_uri = $_SERVER['REQUEST_URI'];
        // deb($request_uri);
        
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

        { // SIS_PLATFORM - actual platform
            $request_uri_ = explode('/', $request_uri);
            // deb($request_uri_);
            $second_uri_parameter = $request_uri_[2];
            // deb($second_uri_parameter);

            switch ($second_uri_parameter) {
                case 'admin':
                    $SIS_PLATFORM = 'admin';
                    break;
                case 'dev':
                    $SIS_PLATFORM = 'dev';
                    break;
                default:
                    $SIS_PLATFORM = 'site';
                    break;
            }
            // deb($SIS_PLATFORM);
            define('SIS_PLATFORM', $SIS_PLATFORM);
        }
    }
    
    
    private static function HTML_AUX()
    {
        define('HTML_BR', '<br/>');
        define('HTML_HR', '<hr/>');
    }

    private static function FOLDERS_PERMISSIONS()
    {
        $folders777 = [];
        $folders777[] = 'cache';
        $folders777[] = 'data';
        $folders777[] = 'log';        
        $folders777[] = 'repository';
        
        foreach ($folders777 as $folder) {            
            $fileperms = Arquivos::permissoesObter($folder, false);
            //deb($fileperms,0);
            if ($fileperms != '777') {
                if(Arquivos::permissoesAlterar($folder, 0777, false)==false){
                    throw new Exception("Não foi possível alterar as permissões da pasta '$folder'. Contate o administrador.");
                }else{
                    deb("As permissões da pasta '$folder' foram alteradas com sucesso.",0);
                }
            }
        }
    }

    private static function SERVER_PARAMETERS_TUNE()
    {
        { // reques_uri
            $_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);
            // deb($_SERVER);
        }
    }

    private static function ROOT()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $uri_levels = explode('/', $_SERVER['REQUEST_URI']);
            $return = str_repeat('..' . DIRECTORY_SEPARATOR, sizeof($uri_levels) - 1);
        } else {
            $return = '/' . SIS_FOLDERNAME;
        }
        // die($return);
        return $return;
    }

    private static function ROOT_ACTION()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $return = '';
        } else {
            $return = '/' . SIS_FOLDERNAME;
        }
        // die($return);
        return $return;
    }

    private static function ROOT_LOCATION()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $return = '';
        } else {
            $return = '/' . SIS_FOLDERNAME;
        }
        // die($return);
        return $return;
    }

    private static function ROOT_TPL()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $return = $_SERVER['DOCUMENT_ROOT'] . '/vendor/manguto/manguto/tpl/';
        } else {
            $return = '../' . SIS_FOLDERNAME . '/vendor/manguto/manguto/tpl/';
        }
        // die($return);
        return $return;
    }

    private static function ROOT_SIS()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $return = $_SERVER['DOCUMENT_ROOT'] . '/sis/';
        } else {
            $return = '../' . SIS_FOLDERNAME . '/sis/';
        }
        // die($return);
        return $return;
    }

    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
    static function Setup($echo = true)
    {
        try {

            $relat = [];
            $relat[] = "<hr/>";
            $relat[] = "<h1>SETUP</h1>";
            $relat[] = "<h2>Procedimento de instalação do General Managemente System (CMS) inicializado</h2>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            // config
            $originFilesPath = ServerHelp::fixds('vendor/manguto/manguto/cms/files');

            // get folders/files structure to reply
            $originFiles = Diretorios::obterArquivosPastas($originFilesPath, true, true, true);
            $relat[] = "Foram encontrados '" . sizeof($originFiles) . "' pastas/arquivos.";
            // deb($foldersFiles);
            // criacao de pastas e arquivos
            $relat[] = "<ol>";
            foreach ($originFiles as $originFile) {
                $relat[] = "<li>$originFile";
                $destinationFilePath = str_replace($originFilesPath . DIRECTORY_SEPARATOR, '', $originFile);

                if (is_dir($originFile)) {
                    if (! file_exists($destinationFilePath)) {
                        Diretorios::mkdir($destinationFilePath);
                        $relat[] = " - Diretório '$destinationFilePath' criado com sucesso!";
                    }
                } else if (is_file($originFile)) {
                    { // tratamento deviso a extensao "php_"
                        $ext = Arquivos::obterExtensao($originFile);
                        if ($ext == 'php_') {
                            $destinationFilePath = str_replace('php_', 'php', $destinationFilePath);
                        }
                    }

                    if (! file_exists($destinationFilePath)) {
                        Arquivos::copiarArquivo($originFile, '.' . DIRECTORY_SEPARATOR . $destinationFilePath);
                        $relat[] = " - <b>Arquivo '$destinationFilePath' criado com sucesso!</b>";
                    } else {
                        $relat[] = " - Arquivo '$destinationFilePath' já existente (NOP).";
                    }
                } else {
                    throw new Exception("Arquivo de tipo inadequado/desconhecido (?).");
                }
                $relat[] = "</li>";
            }
            
            $relat[] = "</ol>";
            $relat[] = "<h3>Procedimento de SETUP finalizado com sucesso!</h3>";
            $relat[] = "<hr/>";
            $relat[] = "<h2>CLIQUE <a href='index.php' title='Clique aqui para acessar a nova plataforma.'>AQUI</a> PARA ACESSAR A NOVA PLATAFORMA</h2>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<hr/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            //$relat[] = Javascript::TimeoutDocumentLocation('index.php');
            
            {//RENAME/REPLACE INDEX
                self::SetupReplaceIndexes();
            }
            
            { // relat
                $relat = implode(chr(10), $relat);
                if ($echo) {
                    echo $relat;
                } else {
                    return $relat;
                }
            }
        } catch (Exception $e) {
            echo $e->show();
        }
    }
    
    private static function SetupReplaceIndexes(){
        
        $index_old_filename = 'index.php';
        $index_old_bkp_filename = 'index_old.php';
        $index_cms_filename = 'index_cms.php';
        $index_new_filename = 'index.php';
        
        {//backup arquivo index atual
            if(file_exists($index_old_filename)){
                $index_old_content = file_get_contents($index_old_filename);
                if($index_old_content===false){
                    throw new \Exception("Não foi possível obter o conteúdo do arquivo de indexação antigo (index). Contate o administrador!");
                }else{
                    if(!file_put_contents($index_old_bkp_filename, $index_old_content)){
                        throw new \Exception("Não foi possível copiar o conteúdo do arquivo de indexação antigo (index -> index_old). Contate o administrador!");
                    }
                }
            }
        }
        
        {//atualizacao do arquivo de indexacao para acesso ao cms instalado
            if(!file_exists($index_cms_filename)){
                throw new \Exception("Arquivo de indexação do Content Management System (CMS) não encontrado (index_cms). Contate o administrador!");
            }else{
                $index_cms_content = file_get_contents($index_cms_filename);                
                if($index_cms_content===false){
                    throw new \Exception("Não foi possível obter o conteúdo do arquivo de indexação do cms (index_cms). Contate o administrador!");
                }else{
                    if(!file_put_contents($index_new_filename, $index_cms_content)){
                        throw new \Exception("Não foi possível atualizar o conteúdo do arquivo de indexação atual (index_cms -> index). Contate o administrador!");
                    }
                }                
            }
        }
        
    }

    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
}

?>