<?php
namespace manguto\cms5\lib\html;

use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Strings;

class HTMLParser
{

    private $name = false;

    private $url = false;

    private $dir = 'temp/html/';

    private $html_content = false;

    const fileDateFormat = 'Ymd';

    /**
     * contrucao do objeto de controle de parseamento do html para obtencao de alguma tag do mesmo
     *
     * @param string $name
     *            - parametros de identificacao para o arquivo temporario
     * @param string $url
     */
    public function __construct(string $name, string $url = '')
    {
        $this->name = Strings::RemoverAcentosECaracteresLinguisticos($name);

        if ($url != '') {
            $this->loadHTMLContent_fromUrl($url);
        }
    }

    /**
     * atualizar o local temporario
     *
     * @param string $dir
     */
    public function setTempDir(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * carrega o conteudo html a partir de uma URL
     *
     * @param string $url
     */
    public function loadHTMLContent_fromUrl(string $url)
    {
        {
            if(strpos($url,'http://')==false){
                $url = 'http://'.$url;
            }
        }
        
        $this->url = $url;

        $filename = $this->getFilename();

        if (! Arquivos::verificarArquivoOuPastaExiste($filename, false)) {
            $html_content = file_get_contents($this->url);
            Arquivos::escreverConteudo($filename, $html_content);
        } else {
            $html_content = Arquivos::obterConteudo($filename);
        }
        $this->loadHTMLContent_fromString($html_content);
    }

    /**
     * carrega o conteudo html a partir de uma string
     *
     * @param string $html_string
     */
    public function loadHTMLContent_fromString(string $html_string)
    {
        $this->html_content = $html_string;
    }

    /**
     * obtem o nome do arquivo temporario (utilizado como cache)
     *
     * @return string
     */
    private function getFilename()
    {
        return $this->dir . $this->name . '_' . date(self::fileDateFormat) . '.html';
    }

    /**
     * obtem o conteudo entre os limites esquerdo e direito
     *
     * @param string $leftBound
     * @param string $rightBound
     * @throws Exception
     * @return string
     */
    public function getWrappedContent(string $leftBound, string $rightBound)
    {

        /*
         * html_content => a | b | c
         * | => leftBound / rightBound
         * b => alvo!
         */
        $abc = $this->html_content;

        { // obter apenas conteudo principal
            $abc_array = explode($leftBound, $abc);
            // debc($conteudo);
            if (sizeof($abc_array) != 2) {
                throw new Exception("Código fonte da página modificado (limite esquerdo não encontrado). Nova análise necessária!");
            }

            $bc = array_pop($abc_array);

            $pos = strpos($bc, $rightBound);
            // deb($pos);

            if ($pos === false) {
                throw new Exception("Código fonte da página modificado (limite direito não encontrado). Nova análise necessária!");
            }

            $return = trim(substr($bc, 0, $pos));
            // debc($conteudo);
        }
        return $return;
    }

   /**
    * 
    * @param string $selector
    * @throws Exception
    * @return array|NULL|mixed|NULL[]
    */
    
    /**
     * obtem o objeto (tag) do html informado
     * @param string $selector
     * @throws Exception
     * @return simple_html_dom
     */
    public function getTag(string $selector):simple_html_dom
    {
        $dom = simple_html_dom_str($this->html_content);
        
        $return = $dom->find($selector);

        if($return==false){
            throw new Exception("Elementro não encontrado no HTML informado ($selector).");
        }
        
        return $return;
    }
}

?>