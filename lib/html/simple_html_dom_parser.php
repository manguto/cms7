<?php
namespace manguto\cms7\lib\html;

use manguto\cms7\lib\Exception;
use manguto\cms7\lib\Arquivos;
use manguto\cms7\lib\Strings;

/**
 * http://simplehtmldom.sourceforge.net/
 *
 * // Create DOM from URL or file
 * $html = file_get_html('http://www.google.com/');
 *
 * // Find all images
 * foreach($html->find('img') as $element){ echo $element->src . '<br>'; }
 *
 * // Find all links
 * foreach($html->find('a') as $element) { echo $element->href . '<br>'; }
 *
 * // Create DOM from string
 * $html = str_get_html('<div id="hello">Hello</div><div id="world">World</div>');
 *
 * $html->find('div', 1)->class = 'bar';
 *
 * $html->find('div[id=hello]', 0)->innertext = 'foo';
 *
 * echo $html; // Output: <div id="hello">foo</div><div id="world" class="bar">World</div>
 *
 * // Dump contents (without tags) from HTML
 * echo file_get_html('http://www.google.com/')->plaintext;
 *
 *
 * // Create DOM from URL
 * $html = file_get_html('http://slashdot.org/');
 *
 * // Find all article blocks
 * foreach($html->find('div.article') as $article) {
 * $item['title'] = $article->find('div.title', 0)->plaintext;
 * $item['intro'] = $article->find('div.intro', 0)->plaintext;
 * $item['details'] = $article->find('div.details', 0)->plaintext;
 * $articles[] = $item;
 * }
 *
 * print_r($articles);
 *
 *
 *
 *
 * Website: http://sourceforge.net/projects/simplehtmldom/
 * Additional projects that may be used: http://sourceforge.net/projects/debugobject/
 * Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
 * Contributions by:
 * Yousuke Kumakura (Attribute filters)
 * Vadim Voituk (Negative indexes supports of "find" method)
 * Antcs (Constructor with automatically load contents either text or file/url)
 *
 * all affected sections have comments starting with "PaperG"
 *
 * Paperg - Added case insensitive testing of the value of the selector.
 * Paperg - Added tag_start for the starting index of tags - NOTE: This works but not accurately.
 * This tag_start gets counted AFTER \r\n have been crushed out, and after the remove_noice calls so it will not reflect the REAL position of the tag in the source,
 * it will almost always be smaller by some amount.
 * We use this to determine how far into the file the tag in question is. This "percentage will never be accurate as the $dom->size is the "real" number of bytes the dom was created from.
 * but for most purposes, it's a really good estimation.
 * Paperg - Added the forceTagsClosed to the dom constructor. Forcing tags closed is great for malformed html, but it CAN lead to parsing errors.
 * Allow the user to tell us how much they trust the html.
 * Paperg add the text and plaintext to the selectors for the find syntax. plaintext implies text in the innertext of a node. text implies that the tag is a text node.
 * This allows for us to find tags based on the text they contain.
 * Create find_ancestor_tag to see if a tag is - at any level - inside of another specific tag.
 * Paperg: added parse_charset so that we know about the character set of the source document.
 * NOTE: If the user's system has a routine called get_last_retrieve_url_contents_content_type availalbe, we will assume it's returning the content-type header from the
 * last transfer or curl_exec, and we will parse that and use it in preference to any other method of charset detection.
 *
 * Found infinite loop in the case of broken html in restore_noise. Rewrote to protect from that.
 * PaperG (John Schlick) Added get_display_size for "IMG" tags.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author S.C. Chen <me578022@gmail.com>
 * @author John Schlick
 * @author Rus Carroll
 * @version 1.5 ($Rev: 210 $)
 * @package PlaceLocalInclude
 * @subpackage simple_html_dom
 */

/**
 * All of the Defines for the classes below.
 *
 * @author S.C. Chen <me578022@gmail.com>
 */
define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT', 3);
define('HDOM_TYPE_ENDTAG', 4);
define('HDOM_TYPE_ROOT', 5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO', 3);
define('HDOM_INFO_BEGIN', 0);
define('HDOM_INFO_END', 1);
define('HDOM_INFO_QUOTE', 2);
define('HDOM_INFO_SPACE', 3);
define('HDOM_INFO_TEXT', 4);
define('HDOM_INFO_INNER', 5);
define('HDOM_INFO_OUTER', 6);
define('HDOM_INFO_ENDSPACE', 7);
define('DEFAULT_TARGET_CHARSET', 'UTF-8');
define('DEFAULT_BR_TEXT', "\r\n");
define('DEFAULT_SPAN_TEXT', " ");
define('MAX_FILE_SIZE', 600000);

class simple_html_dom_parser
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
            if (strpos($url, 'http://') == false) {
                $url = 'http://' . $url;
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
     *
     * @param string $selector
     * @throws Exception
     * @return simple_html_dom
     */
    public function getTag(string $selector): simple_html_dom
    {
        $dom = simple_html_dom_parser::load_str($this->html_content);

        $return = $dom->find($selector);

        if ($return == false) {
            throw new Exception("Elementro não encontrado no HTML informado ($selector).");
        }

        return $return;
    }

    // #################################################################################################################################
    // ########################################################################################################################## STATIC
    // #################################################################################################################################

    // get html dom from file
    // $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
    //static function simple_html_dom_file($url, $use_include_path = false, $context = null, $offset = - 1, $maxLen = - 1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    static function load_file($url, $use_include_path = false, $context = null, $offset = - 1, $maxLen = - 1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    {
        //simple_html_dom_file( => simple_html_dom_parser::load_file(
        // We DO force the tags to be terminated.
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        $contents = file_get_contents($url, $use_include_path, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        // $contents = retrieve_url_contents($url);
        if (empty($contents) || strlen($contents) > MAX_FILE_SIZE) {
            return false;
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);
        return $dom;
    }

    // #################################################################################################################################

    // get html dom from string
    //static function simple_html_dom_parser::load_str($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    static function load_str($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT)
    {
        //simple_html_dom_parser::load_str( => simple_html_dom_parser::load_str(
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
            if (strlen($str) > MAX_FILE_SIZE) {
                throw new Exception("Tamanho máximo do arquivo HTML atingido. Reduza-o removendo partes desnecessárias e tente novamente.");
            }

            if (empty($str)) {
                throw new Exception("Arquivo HTML vazio.");
            }

            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }
}

?>