<?php
namespace manguto\cms7\libraries;

class ServerHelp
{

    /**
     * FIX DIRECTORY SEPARATOR - Ajusta o caminho informado com o DIRECTORY_SEPARATOR do servidor ou outro caso informado.
     *
     * @param string $path
     * @return string $DIRECTORY_SEPARATOR
     */
    static function fixds(string $path, string $DIRECTORY_SEPARATOR = DIRECTORY_SEPARATOR): string
    {
        $path = str_replace('/', $DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\', $DIRECTORY_SEPARATOR, $path);
        while (strpos($path, $DIRECTORY_SEPARATOR . $DIRECTORY_SEPARATOR) !== false) {
            $path = str_replace($DIRECTORY_SEPARATOR . $DIRECTORY_SEPARATOR, $DIRECTORY_SEPARATOR, $path);
        }
        return $path;
    }

    // ####################################################################################################

    /**
     * obtem o IP do usuario
     *
     * @return string
     */
    static function getIp()
    {
        // whether ip is from share internet
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } // whether ip is from proxy
        elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } // whether ip is from remote address
        else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        return $ip_address;
    }

    // ####################################################################################################

    /**
     * Obtem a rota solicitada via URL
     *
     * @return string
     */
    static function getURL(): string
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $return = $_SERVER['REQUEST_URI'];
        } else {            
            $return = Variables::filter_input(INPUT_SERVER,'REQUEST_URI');                        
        }
        return $return;
    }
}

?>