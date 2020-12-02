<?php
namespace manguto\cms7\application\core;

use manguto\cms7\libraries\ServerHelp;
use manguto\cms7\libraries\Strings;
use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\Logger;

class Route
{

    private $method;

    private $raw_url;

    private $platform;

    private $url;

    private $parameters;
    
    public $route_found = false;

    public function __construct()
    {
        Logger::info('Controle de Rota inicializado');

        // obtem o metodo solicitado pela rota
        $this->method = strtoupper(ServerHelp::getRequestMethod());

        // obtem a url informada
        $this->raw_url = ServerHelp::getURL();

        // transforma a url em uma rota
        //$this->url = self::fixURL($this->raw_url);
        $this->url = $this->fixURLDomain();

        // carrega os parametros
        $this->parameters = self::explodeParameters($this->url);

        /*{//log
            $vars = Arrays::arrayShowSingleLine(get_object_vars($this),'',' | ',true);
            //deb($vars);
            Logger::info("Parametros da Rota => $vars");
        }/**/
        
    }

    // ####################################################################################################
    /**
     * obter a URL jah ajustada
     * @return string
     */
    public function getURL():string{
        return $this->url;
    }
    // ####################################################################################################
    /**
     * remove caracteres indevidos 
     * @example /admin/log//today/ => admin/log/today
     * @param string $url
     * @return string
     */
    static function fixURL(string $url): string
    {
        { // remocao de barras repetidas            
            $url = Strings::removeRepeatedOccurrences('/', $url);            
        }
        return $url;
    }
    // ####################################################################################################
    
    private function fixURLDomain(): string
    {
        {
            $url = $this->raw_url;
            //Logger::proc("URL Domain Fix (fix-A) '$url'");
        }        
        {
            $url = self::fixURL($url);
            //Logger::proc("URL Domain Fix (fix-B) '$url'");
        }
        { // ajusta o dominio do (url) 
            $url = substr($url, strlen(APP_URL_ROOT) - 1);            
            //Logger::proc("URL Domain Fix (fix-C) '$url'");
        }
        //deb($url);
        return $url;
    }

    // ####################################################################################################
    /**
     * explode a url em parametros (delimitador: '/')
     *
     * @param string $url
     * @return array
     */
    private static function explodeParameters(string $url): array
    {
        $return = explode('/', trim($url));
        if (trim($return[sizeof($return) - 1]) == '') {
            array_pop($return);
        }
        return $return;
    }

    // ####################################################################################################

    /**
     * realiza os testes quanto a rota solicitada e a mascara do controlador em questao,
     * retornando as variaveis necessarias, e em caso contrario, FALSE.
     *
     * @param string $method
     * @return boolean|string
     */
    private function checkRoute(string $method, string $controller_route_masked)
    {   
        { // parameters
            $controller_route_parameters = self::explodeParameters($controller_route_masked);
        }
        // ######################################################################
        // ################################### teste 1 - metodo (GET, POST, etc.)
        // ######################################################################
        if (strtolower($this->method) != strtolower($method)) {
            return false;
        }
        // ######################################################################
        // ######################## teste 2 - quantidade de parametros envolvidos
        // ######################################################################
        if (sizeof($this->parameters) != sizeof($controller_route_parameters)) {
            return false;
        }
        // ######################################################################
        // ########################## teste 3 - conferencia de parametros (fixos)
        // ######################################################################
        $variables = [];
        foreach ($controller_route_parameters as $controller_route_parameter_key => $controller_route_parameter_name) {
            
            //parametro (titulo ou valor)
            $route_parameter_name = $this->parameters[$controller_route_parameter_key];

            //parametro estatico?
            if (substr($controller_route_parameter_name, 0, 1) != ':') {                
                //parametro estatico!
                //parametro estatico!
                //parametro estatico!                
                //parametro estatico eh igual ao da rota em questao?
                if ($controller_route_parameter_name != $route_parameter_name) {
                    //a rota solicitada nao pertence ao objeto em questao
                    return false;
                } else {
                    // parametro estah conforme a mascara da rota (staticPar1/:variablePar1/staticPar2)
                    // continua o loop...                    
                }
            } else {
                //parametro variavel!
                //parametro variavel!
                //parametro variavel!
                $variables[] = $route_parameter_name;
            }
        }
        // ######################################################################
        //Logger::success("Rota encontrada com sucesso! [$controller_route_masked]",$variables);
        $this->route_found = true;
        // ######################################################################
        return $variables;
    }

    // ####################################################################################################
    public function get($url_masc, $function)
    {
        $method = 'get';
        $url_masc = self::fixURL($url_masc);
        //Logger::info("Verificação da rota '$url_masc'");
        $parameters = $this->checkRoute($method, $url_masc);
        if ($parameters !== false) {
            { // extrai as variaveis (``,$p_0, $p_1, $p_2) e obtem como retorno a quant. de parametros declarados
                $parametersLength = extract($parameters, EXTR_PREFIX_ALL, 'p');
            }
            { // teste e execucao da funcao
                if ($parametersLength == 0) {
                    $function();
                } else if ($parametersLength == 1) {
                    $function($p_0);
                } else if ($parametersLength == 2) {
                    $function($p_0, $p_1);
                } else if ($parametersLength == 3) {
                    $function($p_0, $p_1, $p_2);
                } else if ($parametersLength == 4) {
                    $function($p_0, $p_1, $p_2, $p_3);
                } else if ($parametersLength == 5) {
                    $function($p_0, $p_1, $p_2, $p_3, $p_4);
                } else {
                    throw new Exception("Foram definidos mais parâmetros do que o programado. Contate o administrador para acréscimo na implementação.");
                }
            }
        }
    }

    // ####################################################################################################
    public function post($url_masc, $function)
    {
        $method = 'post';
        $url_masc = self::fixURL($url_masc);
        //Logger::info("Verificação da rota '$url_masc'");
        $parameters = $this->checkRoute($method, $url_masc);
        if ($parameters !== false) {
        	{ // extrai as variaveis (``,$p_0, $p_1, $p_2) e obtem como retorno a quant. de parametros declarados
        		$parametersLength = extract($parameters, EXTR_PREFIX_ALL, 'p');
        	}
        	{ // teste e execucao da funcao
        		if ($parametersLength == 0) {
        			$function();
        		} else if ($parametersLength == 1) {
        			$function($p_0);
        		} else if ($parametersLength == 2) {
        			$function($p_0, $p_1);
        		} else if ($parametersLength == 3) {
        			$function($p_0, $p_1, $p_2);
        		} else if ($parametersLength == 4) {
        			$function($p_0, $p_1, $p_2, $p_3);
        		} else if ($parametersLength == 5) {
        			$function($p_0, $p_1, $p_2, $p_3, $p_4);
        		} else {
        			throw new Exception("Foram definidos mais parâmetros do que o programado. Contate o administrador para acréscimo na implementação.");
        		}
        	}
        }
    }

    // ####################################################################################################
    /**
     * retorna a url bruta solicitada
     *
     * @return string
     */
    public function getRawURL(): string
    {
        return $this->raw_url;
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}