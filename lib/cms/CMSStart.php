<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\ServerHelp;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Logs;  

class CMSStart
{

    static function Run()
    {   
        if (! defined('VIRTUAL_HOST_ACTIVE')) {
            throw new Exception("A constante 'VIRTUAL_HOST_ACTIVE' não foi definida. Defina-a no arquivo de CONFGURAÇÕES e tente novamente.");
        }

        self::ERROR_HANDLER();

        // --------------------------------------
        
        define('VENDOR_MANGUTO_PRJ_ROOT', CMSStart::VENDOR_MANGUTO_PRJ_ROOT());
        
        // --------------------------------------
        
        define('SIS_FOLDERNAME', CMSStart::SIS_FOLDERNAME());

        define('SIS_URL', CMSStart::SIS_URL());

        // --------------------------------------

        define('ROOT', CMSStart::ROOT());

        define('ROOT_ACTION', CMSStart::ROOT_ACTION());

        define('ROOT_LOCATION', CMSStart::ROOT_LOCATION());

        define('ROOT_TPL', CMSStart::ROOT_TPL());

        define('ROOT_SIS', CMSStart::ROOT_SIS());

        // --------------------------------------

        self::SUBVERSION_CSS();

        self::FOLDERS_PERMISSIONS();

        self::SERVER_PARAMETERS_TUNE();

        self::EXTRA_PARAMETERS();

        self::HTML_AUX();

        

    }

    private static function ERROR_HANDLER()
    {
        register_shutdown_function("fatal_error_handler");
        
        error_reporting(E_ALL);
        
        ini_set("display_errors",1);
        
        ini_set("log_errors",1);
        
        ini_set("error_log",Logs::dir.DIRECTORY_SEPARATOR."error_".date('Ymd_His').".txt");
                
    }

    private static function SIS_FOLDERNAME()
    {

        $dir = __DIR__;
        //deb($dir);
        
        if(strpos($dir,VENDOR_MANGUTO_PRJ_ROOT)===false){
            throw new Exception("Caminho base incompatível ($dir). Contate o administrador.");
        }

        $return_ = explode(VENDOR_MANGUTO_PRJ_ROOT, $dir);
        //deb($return_);
        if(sizeof($return_)!=2){
            deb($return_,0);
            throw new Exception("Caminho incompatível com o padrão necessário.");
        }
        
        $caminho_base = array_shift($return_);
        //deb($caminho_base);
        
        $return_base_array = explode(DIRECTORY_SEPARATOR,$caminho_base);
        //deb($return_base_array);
        
        foreach ($return_base_array as $pasta){
          $pasta = trim($pasta);
          if($pasta=='') continue;
          $return = $pasta;
        }

        //deb($return);
        $return = ServerHelp::fixds($return);
        return $return;
    }

    private static function SIS_URL()
    {
        // sistem url
        $return = SERVER_URL . '/' . SIS_FOLDERNAME;
        $return = ServerHelp::fixds($return);
        return $return;
    }

    private static function SUBVERSION_CSS()
    {
        // development flag (foldername has a digit at its end!)
        define("SUBVERSION", is_numeric(substr(SIS_FOLDERNAME, - 1, 1)) ? true : false);

        if (SUBVERSION) {
            $SUBVERSION_CSS = "<style>header,section,footer {border-top:solid 5px #f00;}</style>";
        } else {
            $SUBVERSION_CSS = '';
        }
        define("SUBVERSION_CSS", $SUBVERSION_CSS);
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
        //deb($request_uri);

        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

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
            // deb($fileperms,0);
            if ($fileperms != '777') {
                if (Arquivos::permissoesAlterar($folder, 0777, false) == false) {
                    throw new Exception("Não foi possível alterar as permissões da pasta '$folder'. Contate o administrador.");
                } else {
                    deb("As permissões da pasta '$folder' foram alteradas com sucesso.", 0);
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
            $return = DIRECTORY_SEPARATOR . SIS_FOLDERNAME;
        }
        // die($return);
        $return = ServerHelp::fixds($return);
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
        $DR = $_SERVER['DOCUMENT_ROOT'];
        // deb($DR);
        if (VIRTUAL_HOST_ACTIVE) {
            $return = $DR . VENDOR_MANGUTO_PRJ_ROOT . 'tpl/';
        } else {
            // deb(SIS_FOLDERNAME);
            $return = '../' . SIS_FOLDERNAME . '/' . VENDOR_MANGUTO_PRJ_ROOT . 'tpl/';
        }
        // deb($return);
        return $return;
    }

    private static function VENDOR_MANGUTO_PRJ_ROOT()
    {
        $thisClassDir = __DIR__;
        // deb($thisClassDir);

        $DIRECTORY_SEPARATOR = DIRECTORY_SEPARATOR;
        // deb($DIRECTORY_SEPARATOR);

        if (strpos($thisClassDir, $DIRECTORY_SEPARATOR) !== false) {
            $thisClassDir_array = explode($DIRECTORY_SEPARATOR, $thisClassDir);
            // deb($thisClassDir_array);
            $path = [];
            $mangutoFolderReached = 0;
            foreach ($thisClassDir_array as $thisClassDir_tmp) {
                // deb($thisClassDir_tmp);
                if (trim($thisClassDir_tmp) == 'vendor' || sizeof($path) > 0) {

                    if ($mangutoFolderReached <= 1) {
                        $path[] = $thisClassDir_tmp;
                    }

                    if (trim($thisClassDir_tmp) == 'manguto' || $mangutoFolderReached > 0) {
                        $mangutoFolderReached ++;
                    }
                }
            }
            // deb($mangutoFolderReached);
            // deb($path);
            $manguto_prj_dir = implode($DIRECTORY_SEPARATOR, $path) . $DIRECTORY_SEPARATOR;
            
            // deb($manguto_prj_dir);
        } else {
            throw new Exception("Não foi possível definir o diretório raiz do projeto no 'vendor'.");
        }
        
        $manguto_prj_dir = ServerHelp::fixds($manguto_prj_dir);
        return $manguto_prj_dir;
    }

    private static function ROOT_SIS()
    {
        if (VIRTUAL_HOST_ACTIVE) {
            $return = $_SERVER['DOCUMENT_ROOT'] . '/sis/';
        } else {
            $return = '../' . SIS_FOLDERNAME . '/sis/';
        }
        // die($return);
        $return = ServerHelp::fixds($return);
        return $return;
    }

    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
}

?>