<?php
namespace manguto\cms7\application\core;

class View
{

    const FRONTEND_TEMPLATE_DIR = APP_TPL_DIR . 'frontend' . DS;

    const BACKEND_TEMPLATE_DIR = APP_TPL_DIR . 'backend' . DS;

    const DEVEND_TEMPLATE_DIR = APP_TPL_DIR . 'devend' . DS;

    const EXTRA_TEMPLATE_DIR = APP_TPL_DIR . '_extra' . DS;

    // ####################################################################################################
    // ######################################################################################### pages run!
    // ####################################################################################################
    //
    static function PageFrontend(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageRun(self::FRONTEND_TEMPLATE_DIR, $tpl_filename, $parameters, $toString);
    }

    static function PageBackend(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageRun(self::BACKEND_TEMPLATE_DIR, $tpl_filename, $parameters, $toString);
    }

    static function PageDevend(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageRun(self::DEVEND_TEMPLATE_DIR, $tpl_filename, $parameters, $toString);
    }

    static function PageExtra(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageRun(self::EXTRA_TEMPLATE_DIR, $tpl_filename, $parameters, $toString);
    }

    // ####################################################################################################
    //
    static function PageFrontendModule(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageFrontend(self::GET_MODULES_DIR_REFERENCE() . $tpl_filename, $parameters, $toString);
    }

    static function PageBackendModule(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageBackend(self::GET_MODULES_DIR_REFERENCE() . $tpl_filename, $parameters, $toString);
    }

    static function PageDevendModule(string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        return self::PageDevend(self::GET_MODULES_DIR_REFERENCE() . $tpl_filename, $parameters, $toString);
    }

    // ####################################################################################################
    // ##################################################################################### STATIC PRIVATE
    // ####################################################################################################
    //
    /**
     * executa o template 
     * @param string $tpl_dir
     * @param string $tpl_filename
     * @param array $parameters
     * @param bool $toString
     * @return string
     */
    static private function PageRun(string $tpl_dir, string $tpl_filename, array $parameters = [], bool $toString = false)
    {
        $page = new Page($tpl_dir, $tpl_filename, $parameters);
        $return = $page->run($toString);
        return $return;
    }

    // ####################################################################################################
    /**
     * obtem o endereço do diretorio dos modulos
     * utilizando como referencia os diretorio
     * dos templates (APP_TPL_DIR)
     *
     * @return string
     */
    static private function GET_MODULES_DIR_REFERENCE(): string
    {
        { // up folders
            {
                $repeat = '..' . DS;
                $mult = sizeof(explode(DS, APP_TPL_DIR));
            }
            $up = str_repeat($repeat, $mult);
        }
        { // path
            $path = str_replace(APP_TPL_DIR, '', APP_MODULES_DIR);
        }

        $return = $up . $path;
        return $return;
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}