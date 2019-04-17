<?php
namespace manguto\manguto\lib\html;
use manguto\manguto\lib\Exception;

/**
 *
 * @author Marcos Torres
 *         What is HTTP?
 *         The Hypertext Transfer Protocol (HTTP) is designed to enable communications between clients and servers.
 *         HTTP works as a request-response protocol between a client and server.
 *         A web browser may be the client, and an application on a computer that hosts a web site may be the server.
 *         Example: A client (browser) submits an HTTP request to the server; then the server returns a response to the client. The response contains status information about the request and may also contain the requested content.
 *        
 *         HTTP Methods: GET * POST * PUT * HEAD * DELETE * PATCH * OPTIONS
 *        
 */
class HTTPMethods
{

    // #########################################################################################################
    // ############################################ POST #####################################################
    // #########################################################################################################
    
    /**
     * Retorna uma variavel POST conforme os parametros solicitados
     *
     * @param string $varName
     * @param string $default
     * @param bool $allowUndefined
     * @param bool $allowEmpty
     * @throws Exception
     * @return string
     */
    static function POSTVariable(string $varName, $default = '', bool $allowUndefined = false, bool $allowEmpty = false)
    {
        $return = $default;
        
        if (isset($_POST[$varName])) {
            $return = $_POST[$varName];
            if ($return == '' && ! $allowEmpty) {
                throw new Exception("Parâmetro sem valor encontrado (\$_POST[$varName]='').");
            }
        } else {
            if (! $allowUndefined) {
                throw new Exception("Parâmetro indefinido (\$_POST[$varName]).");
            }
        }
        
        return $return;
    }
    
    static function POSTSetGet(array $parameters=[]){
        $return = [];
        foreach ($parameters as $key=>$defaultValue) {            
            if(!isset($_POST[$key])){
                $return[$key] = $defaultValue;
            }else{
                $return[$key] = $_POST[$key];
            }
        }
        return $return;
    }

    // #########################################################################################################
    // ############################################ GET ######################################################
    // #########################################################################################################
    
    /**
     * Retorna uma variavel GET conforme os parametros solicitados
     *
     * @param string $varName
     * @param string $default
     * @param bool $allowUndefined
     * @param bool $allowEmpty
     * @throws Exception
     * @return string
     */
    static function GETVariable(string $varName, $default = '', bool $allowUndefined = false, bool $allowEmpty = false)
    {
        $return = $default;
        
        if (isset($_GET[$varName])) {
            $return = trim($_GET[$varName]);
            if ($return == '' && ! $allowEmpty) {
                throw new Exception("Parâmetro sem valor encontrado (\$_GET[$varName]='').");
            }
        } else {
            if (! $allowUndefined) {
                throw new Exception("Parâmetro indefinido (\$_GET[$varName]).");
            }
        }
        
        return $return;
    }

    /**
     * retorna a URL atual com os parametros GET alinhados
     *
     * @param array $removeGetVars
     * @return array|string
     */
    static function GET2String(array $removeGetVars = array())
    {
        if (sizeof($_GET) > 0) {
            $return = array();
            foreach ($_GET as $k => $v) {
                if (in_array($k, $removeGetVars)) {
                    continue;
                }
                if ($v != '') {
                    $return[] = "$k=$v";
                } else {
                    $return[] = "$k";
                }
            }
            $return = '?' . implode('&', $return);
        } else {
            $return = '';
        }
        return $return;
    }

    static function URLString2Array(String $urlString)
    {
        $return = array();
        
        { // separacao - parte 1 (arquivo.php[?]) e parte 2 (par=1[&]par=3)
            $urlArrayCompleto = explode('?', $urlString);
            if (sizeof($urlArrayCompleto) == 2) {
                $urlPath = $urlArrayCompleto[0];
                $urlParameteres = $urlArrayCompleto[1];
            } else if (sizeof($urlArrayCompleto) == 1) {
                $urlPath = '';
                $urlParameteres = $urlArrayCompleto[0];
            } else {
                throw new Exception("URL com formato incorreto, ou seja, mais de um caractere '?' ($urlString).");
            }
            // debug($urlPath,0); debug($urlParameteres);
        }
        // return 01
        $return['path'] = $urlPath;
        
        { // tratamento parte 2
            
            $urlArrayP2 = explode('&', $urlParameteres);
            foreach ($urlArrayP2 as $keyVal) {
                $keyValArray = explode('=', $keyVal);
                if (sizeof($keyValArray) == 1) {
                    $key = $keyValArray[0];
                    $val = null;
                } else if (sizeof($keyValArray) == 2) {
                    $key = $keyValArray[0];
                    $val = $keyValArray[1];
                } else {
                    throw new Exception("URL com formacao parametrial incorreta ($keyVal).");
                }
                // tratamento possivel 'url encode'
                $val = urldecode($val);
                // return 02
                $return[$key] = $val;
            }
            // debug($parametros);
        }
        // debug($return);
        return $return;
    }

    /**
     * returna uma string com a url correspondente ao array (padrao do sistema) informado
     *
     * @param array $urlArray
     * @throws Exception
     * @return string
     */
    static function URLArray2String(Array $urlArray)
    {
        $return = '';
        { // ----------------------------------------------------------------------------------- path
            if (! isset($urlArray['path'])) {
                throw new Exception('Nao foi encontrado o parametro "path" no array informado.');
            }
            $path = $urlArray['path'];
            unset($urlArray['path']);
            $return = $path . '?';
        }
        { // ----------------------------------------------------------------------------------- parameters
            $par = array();
            foreach ($urlArray as $key => $val) {
                if ($val != null) {
                    $val = urlencode($val);
                    $par[] = "$key=$val";
                } else {
                    $par[] = "$key=";
                }
            }
            $return .= implode('&', $par);
        }
        return $return;
    }
}