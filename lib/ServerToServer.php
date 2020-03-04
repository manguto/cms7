<?php
namespace manguto\cms5\lib;

class ServerToServer
{

    private $cURL = false;

    private $login_formActionURL = false;

    private $login_formParameters = false;

    private $loginON = false;

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
    }

    // ========================================================================================================================================
    /**
     * efetua o login no servidor remoto via cURL
     *
     * @throws Exception
     * @return mixed
     */
    public function login(string $login_formActionURL, array $login_formParameters)
    {
        // set
        $this->login_formActionURL = trim($login_formActionURL);

        // set
        $this->login_formParameters = $login_formParameters;

        // efetua a comunicacao com o servidor remoto
        $response = $this->setContent($this->login_formActionURL, $this->login_formParameters);

        // salva o estado da conexao
        $this->loginON = true;

        // return
        return $response;
    }

    // ========================================================================================================================================
    /**
     * realiza o logout no servidor remoto via cURL
     */
    public function logout()
    {
        if ($this->loginON) {

            // Encerra o cURL
            curl_close($this->cURL);
            
            // Inicia a sessao cURL
            $this->cURL = curl_init();
            
            // desabilita a flag de secao
            $this->loginON = false;
            
        }
    }

    // ========================================================================================================================================
    /**
     * obter o conteudo de uma url
     *
     * @param string $URL
     * @return mixed
     */
    public function getContent(string $URL)
    {
        // Define a URL a ser chamada
        curl_setopt($this->cURL, CURLOPT_URL, $URL);

        // Obtem o conteudo
        $content = curl_exec($this->cURL);

        // return
        return $content;
    }

    // ========================================================================================================================================
    public function setContent(string $formActionURL, array $formParameters)
    {
        $formActionURL = trim($formActionURL);

        if ($formActionURL == '' || sizeof($formParameters) == 0) {
            deb($formActionURL,0);
            deb($formParameters,0);
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
        return $response;
    }

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