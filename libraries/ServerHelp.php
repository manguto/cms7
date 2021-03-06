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
     * FIX DIRECTORY SEPARATOR - Ajusta o caminho informado com o DIRECTORY_SEPARATOR do servidor ou outro caso informado.
     *
     * @param string $path
     * @return string $DIRECTORY_SEPARATOR
     */
    static function fixURLseparator(string $url): string
    {   
        $url_separator = '/';
        $url = str_replace('\\', $url_separator, $url);
        while (strpos($url, $url_separator . $url_separator) !== false) {
            $url = str_replace($url_separator . $url_separator, $url_separator, $url);
        }
        if(substr($url,-1,1)=='/'){
            $url = substr($url,0,strlen($url)-1);
        }
        return $url;
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
     * retorna o ip do usuario atual no padrao DDD_DDD_DDD_DDD
     * onde o separador (spacer) pode ser alterado
     * @param string $spacer
     * @return string
     */
    static function getStandardizedIP(string $spacer='_'):string{
        $ip = self::getIp();
        $ip_ = explode('.', $ip);
        $return = [];
        foreach ($ip_ as $ip_piece){
            $return[] = str_pad($ip_piece, 3,'0',STR_PAD_LEFT);            
        }
        return implode($spacer, $return);
    }
    
    
    // ####################################################################################################

    /**
     * Obtem a rota solicitada via URL
     *
     * @return string
     */
    static function getURL(): string
    {   
        $REQUEST_URI = $_SERVER['REQUEST_URI'] ?? false;        
        if ($REQUEST_URI!==false) {
            $return = $REQUEST_URI;
        } else {            
            $return = Variables::filter_input(INPUT_SERVER,'REQUEST_URI');                        
        }        
        return $return;
    }
    
    
    // ####################################################################################################

    /**
     * Obtem o methodo de solicitacao realizado
     *
     * @return string
     */
    static function getRequestMethod(): string
    {   
        return $_SERVER['REQUEST_METHOD'];        
    }
    
    
    
}

?>