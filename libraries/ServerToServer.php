<?php
namespace manguto\cms7\libraries;

class ServerToServer
{

    private $cURL;

    // ========================================================================================================================================
    public function __construct()
    {
        // Inicia a sessao cURL
        $this->cURL = curl_init();
        // Permitir o redirecionamento de pagina
        $this->curl_setopt(CURLOPT_FOLLOWLOCATION, TRUE);
        // Define o maior numero de redirecionamentos
        $this->curl_setopt(CURLOPT_MAXREDIRS, 5);
        // Imita o comportamento patrão dos navegadores: manipular cookies
        $this->curl_setopt(CURLOPT_COOKIEJAR, 'cookie.txt');
        // Define o tipo de transferência (Padrão: 1)
        $this->curl_setopt(CURLOPT_RETURNTRANSFER, TRUE);
        // force the use of a new connection instead of a cached one.
        $this->curl_setopt(CURLOPT_FRESH_CONNECT, TRUE);
        { // ssl security off
            $this->curl_setopt(CURLOPT_SSL_VERIFYPEER, FALSE);
            $this->curl_setopt(CURLOPT_SSL_VERIFYHOST, FALSE);
            // $this->curl_setopt( CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        }
    }

    // ========================================================================================================================================
    /**
     * padronizacao da definicao dos parametros do cURL 
     * @param mixed $parameterName
     * @param mixed $parameterValue
     * @throws Exception
     */
    private function curl_setopt($parameterName, $parameterValue)
    {        
        $result = curl_setopt($this->cURL, $parameterName, $parameterValue);
        if ($result == false) {            
            $error = "cURL - Não foi possível configurar o parâmetro '$parameterName' => ".error_get_last()['message'];
            Logger::error($error);
            throw new Exception($error);
        }        
        Logger::proc("cURL - Parâmetro '$parameterName' definido com sucesso!");        
    }

    // ========================================================================================================================================
    
    /**
     * define a opcao 'HTTP HEADER' com o parametro informado
     * ex.: $value='Content-Type: application/x-www-form-urlencoded' 
     * @param mixed $value
     */
    public function setCURL_CURLOPT_HTTPHEADER($value)
    {
        if (is_string($value)) {
            $value = [
                $value
            ];
        }
        $this->curl_setopt(CURLOPT_HTTPHEADER, $value);
    }

    // ========================================================================================================================================
    /**
     * efetua o login no servidor remoto via cURL
     *
     * @throws Exception
     * @return mixed
     */
    public function loginTry(string $server_URL, string $login_form_URL, array $login_form_personalData = [])
    {
        { // obter pagina com o formulario
            $response_href = $this->getContent($login_form_URL);
            $html = File::getContent($response_href);
            // debc($html);
        }
        { // resumir html
            $needle = '<form';
            $form_quant = substr_count($html, $needle);
            if ($form_quant != 1) {
                if ($form_quant == 0) {
                    throw new Exception("Nenhum formulário encontrado na página informada ('$login_form_URL').");
                } else {
                    throw new Exception("Mais de um formulário foram encontrados na página informada ('$login_form_URL').");
                }
            } else {
                $html = Strings::RemoverConteudoAnteriorEPosteriorA('<form', '/form>', $html);
                // debc($html);
            }
        }
        { // verificar/obter parametros do formulario
            { // html to dom
                $html_dom = simple_html_dom_parser::load_str($html);
            }
            { // form as dom
                $form_dom = $html_dom->find('form', 0);
            }
            // ACTION
            $form_action = $server_URL . '/' . $form_dom->action;
        }
        { // obter campos do formulario
            $form_fields = [];
            foreach ($form_dom->find('input') as $input) {
                $form_fields[$input->name] = $input->value;
            }
            foreach ($form_dom->find('textarea') as $textarea) {
                $form_fields[$textarea->name] = $textarea->innertext;
            }
            // deb($form_fields);
        }
        { // insercao dados informados
            foreach ($login_form_personalData as $k => $v) {
                if (! isset($form_fields[$k])) {
                    throw new Exception("O campo '$k' não existe no formulário padrão.");
                }
                $form_fields[$k] = $v;
            }
            // deb($form_fields);
        }

        // login
        return $this->setContent($form_action, $form_fields);
    }

    // ========================================================================================================================================
    /**
     * realiza o logout no servidor remoto via cURL
     */
    public function logout()
    {
        // Encerra o cURL
        curl_close($this->cURL);

        // Inicia a sessao cURL
        $this->cURL = curl_init();
    }

    // ========================================================================================================================================
    /**
     * obter o conteudo de uma url
     *
     * @param string $URL
     * @return mixed
     */
    public function getContent(string $URL, $POST_Array = false, $saveAndReturnFilename = true)
    {
        // Define a URL a ser chamada
        $this->curl_setopt(CURLOPT_URL, $URL);

        // Definicao de protocolo e cia
        if (is_array($POST_Array)) {
            // Abilita o protocolo POST
            $this->curl_setopt(CURLOPT_POST, true);
            $this->curl_setopt(CURLOPT_POSTFIELDS, $POST_Array);
            $this->curl_setopt(CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        } else {
            // Desabilita o protocolo POST
            $this->curl_setopt(CURLOPT_POST, false);
            $this->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
        }

        // Obtem o conteudo
        $response = curl_exec($this->cURL);

        // return
        if ($saveAndReturnFilename) {
            return $this->saveResponse($response, __FUNCTION__);
        } else {
            return $response;
        }
    }

    // ========================================================================================================================================
    /**
     * Envia os parametros para o URL informado e em caso de sucesso
     * retorna o resultado obtido e caso contrario FALSE.
     *
     * @param string $formActionURL
     * @param array $formParameters
     * @throws Exception
     * @return string|bool
     */
    public function setContent(string $formActionURL, array $formParameters)
    {
        { // verificacoes basicas...
            if (trim($formActionURL) == '' || sizeof($formParameters) == 0) {                
                $msg = "Não foi possível enviar as informações solicitadas (URL ou parâmetros não definidos). Contate o administrador.";
                Logger::error($msg);
                throw new Exception($msg);
            }
        }

        // Define a URL original (do formulário de login)
        $this->curl_setopt(CURLOPT_URL, $formActionURL);

        // Habilita o protocolo POST
        $this->curl_setopt(CURLOPT_POST, TRUE);
                
        // define o content-type
        $this->curl_setopt(CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        //$this->curl_setopt(CURLOPT_HEADER, true);
        
        //definicao forcada do metodo
        $this->curl_setopt(CURLOPT_CUSTOMREQUEST, 'POST');
        
        // Define os parâmetros que serão enviados (usuário e password por exemplo)        
        $this->curl_setopt(CURLOPT_POSTFIELDS, http_build_query($formParameters));

        // Executa a requisição
        $response = curl_exec($this->cURL);

        // close connection
        $this->logout();

        // return
        return $response;
    }

    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    private function saveResponse(string $response, string $function_name = 'xxx')
    {
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        $now = $now->format("Ymd_His-u");
        $filename = "data/temp/serverToServerResponse_{$now}_{$function_name}.html";
        File::writeContent($filename, $response);
        return $filename;
    }

    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    /**
     * transforma um array em uma string no formato definido para url
     *
     * @param array $fields
     * @return string
     */
    static function formatTocURLOptPostfield(array $fields): string
    {
        $return = [];
        foreach ($fields as $k => $v) {
            $return[] = "$k=$v";
        }
        $return = implode('&', $return);
        // deb($return);
        return $return;
    }
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
}

?>