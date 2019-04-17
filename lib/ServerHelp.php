<?php

namespace manguto\cms5\lib;

class ServerHelp{
    
    /**
     * Ajusta o caminho informado com o DIRECTORY SEPARATOR correto do sitema *
     * @param string $path
     * @return string
     */
    static function fixds(string $path): string
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        return $path;
    }
  
    
    /**
     * Get IP
     * @return string
     */
    static function getIp()
    {
        //whether ip is from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from remote address
        else
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        return $ip_address;
    }
    
    /**
     * Obtem a 'rota' solicitada
     * @return string
     */
    static function getURLRoute()
    {        
        if(isset($_SERVER['REQUEST_URI'])){
            return $_SERVER['REQUEST_URI'];
        }else{
            return '...';
        }
    }
    
    
}


?>