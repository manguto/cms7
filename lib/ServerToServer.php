<?php
namespace manguto\cms5\lib;


class ServerToServer
{

    private $cURL = false;

    // ========================================================================================================================================
    
    public function __construct()
    {
        // Inicia a sessao cURL
        $this->cURL = curl_init();
        // Permitir o redirecionamento de pagina
        curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, true);
        // Define o maior numero de redirecionamentos
        curl_setopt($this->cURL, CURLOPT_MAXREDIRS, 5);
        // Imita o comportamento patrão dos navegadores: manipular cookies
        curl_setopt($this->cURL, CURLOPT_COOKIEJAR, 'cookie.txt');
        // Define o tipo de transferência (Padrão: 1)
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, 1);
        //force the use of a new connection instead of a cached one. 
        curl_setopt($this->cURL, CURLOPT_FRESH_CONNECT, TRUE);
        //??
        curl_setopt($this->cURL, CURLOPT_SSL_VERIFYPEER, false);
        //??
        curl_setopt($this->cURL, CURLOPT_SSL_VERIFYHOST, 0);
                
        
    }

    // ========================================================================================================================================

    /**
     * define a opcao 'HTTP HEADER' com o parametro informado
     * ex.: $value='Content-Type: application/x-www-form-urlencoded'
     *
     * @param
     *            $value
     */
    public function setCURL_CURLOPT_HTTPHEADER($value)
    {
        if (is_string($value)) {
            $value = [
                $value
            ];
        }
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, $value);
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
        {//obter pagina com o formulario
            $response_href = $this->getContent($login_form_URL);
            $html = Arquivos::obterConteudo($response_href);
            //debc($html);
        }
        {//resumir html
            $needle = '<form';
            $form_quant = substr_count($html, $needle);
            if($form_quant!=1){
                if($form_quant==0){
                    throw new Exception("Nenhum formulário encontrado na página informada ('$login_form_URL').");
                }else{
                    throw new Exception("Mais de um formulário foram encontrados na página informada ('$login_form_URL').");
                }                
            }else{
                $html = Strings::RemoverConteudoAnteriorEPosteriorA('<form', '/form>', $html);
                // debc($html);
            }            
        }
        {//verificar/obter parametros do formulario
            {//html to dom
                $html_dom = simple_html_dom_str($html);
            }
            {//form as dom
                $form_dom = $html_dom->find('form', 0);                
            }
            //ACTION
            $form_action = $server_URL .'/'. $form_dom->action;
        }
        {//obter campos do formulario
            $form_fields = [];
            foreach ($form_dom->find('input') as $input){
                $form_fields[$input->name] = $input->value;
            }
            foreach ($form_dom->find('textarea') as $textarea){
                $form_fields[$textarea->name] = $textarea->innertext;
            }
            //deb($form_fields);
        }
        {//insercao dados informados
            foreach ($login_form_personalData as $k=>$v){
                if(!isset($form_fields[$k])){
                    throw new Exception("O campo '$k' não existe no formulário padrão.");
                }
                $form_fields[$k] = $v;
            }
            //deb($form_fields);
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
    public function getContent(string $URL,$POST_Array=false,$saveAndReturnFilename=true)
    {
        // Define a URL a ser chamada
        curl_setopt($this->cURL, CURLOPT_URL, $URL);

        // Definicao de protocolo e cia
        if(is_array($POST_Array)) {
            // Abilita o protocolo POST
            curl_setopt($this->cURL, CURLOPT_POST, true);
            curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $POST_Array);
        }else{
            // Desabilita o protocolo POST
            curl_setopt($this->cURL, CURLOPT_POST, false);
            curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, 'GET');                        
        }
        
        // Obtem o conteudo
        $response = curl_exec($this->cURL);

        // return
        if($saveAndReturnFilename){
            return $this->saveResponse($response,__FUNCTION__);
        }else{
            return $response;   
        }        
    }

    // ========================================================================================================================================
    public function setContent(string $formActionURL, array $formParameters)
    {
        $formActionURL = trim($formActionURL);

        if ($formActionURL == '' || sizeof($formParameters) == 0) {
            deb($formActionURL, 0);
            deb($formParameters, 0);
            throw new Exception("Não foi possível enviar as informações solicitadas (HTTP/POST). Contate o administrador.");
        }

        // Define a URL original (do formulário de login)
        curl_setopt($this->cURL, CURLOPT_URL, $formActionURL);

        // Habilita o protocolo POST
        curl_setopt($this->cURL, CURLOPT_POST, 1);

        // Define os parâmetros que serão enviados (usuário e password por exemplo)
        curl_setopt($this->cURL, CURLOPT_POSTFIELDS, self::formatTocURLOptPostfield($formParameters));

        // Executa a requisição
        $response = curl_exec($this->cURL);

        // return
        return $this->saveResponse($response,__FUNCTION__);
    }

    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    private function saveResponse(string $response,string $function_name='xxx') {        
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        $now = $now->format("Ymd_His-u");        
        $filename = "data/temp/serverToServerResponse_{$now}_{$function_name}.html";
        Arquivos::escreverConteudo($filename, $response);
        return $filename;
    }
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    // ========================================================================================================================================
    /**
     * transforma um array em string em um formato definido (estilo url)
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