<?php
namespace manguto\cms7\application\core;

use manguto\cms7\libraries\Strings;
use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\Logger;

class PageReplacer
{

    private $tpl_dir;

    private $content;

    private $parameters;

    private $matches = [];

    // caracteres proibidos
    const blackListChars = [
        '^', // Start of line
        '$', // End of line        
        '.', // Any single character
        '|', // a or b
        '?', // Zero or one of a
        '+' // One or more of a
    ];

    /**
     * -----------------------------------------------------
     * CHEAT SHEET
     * [abc] A single character of: a, b or c
     * [^abc] Any single character except: a, b, or c
     * [a-z] Any single character in the range a-z
     * [a-zA-Z] Any single character in the range a-z or A-Z
     * ^ Start of line
     * $ End of line
     * \A Start of string
     * \z End of string
     * . Any single character
     * \s Any whitespace character
     * \S Any non-whitespace character
     * \d Any digit
     * \D Any non-digit
     * \w Any word character (letter, number, underscore)
     * \W Any non-word character
     * \b Any word boundary
     * (...) Capture everything enclosed
     * (a|b) a or b
     * a? Zero or one of a
     * a* Zero or more of a
     * a+ One or more of a
     * a{3} Exactly 3 of a
     * a{3,} 3 or more of a
     * a{3,6} Between 3 and 6 of a
     * -----------------------------------------------------
     * OPTIONS
     * i case insensitive
     * m treat as multi-line string
     * s dot matches newline
     * x ignore whitespace in regex
     * A matches only at the start of string
     * D matches only at the end of string
     * U non-greedy matching by default
     * -----------------------------------------------------
     */

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################

    /**
     * inicializa a classe de substituicao e consequente transformacao do codigo HTML do template (informado) em um codigo PHP (+ HTML)
     *
     * @param string $tpl_dir
     * @param string $content
     * @param array $parameters
     */
    public function __construct(string $tpl_dir, string $content, array $parameters = [])
    {
        if (! is_string($tpl_dir)) {
            throw new Exception("Formato de parâmetro inadequado (TLP_DIR=>" . gettype($tpl_dir) . ")");
        }

        if (! is_string($content)) {
            throw new Exception("Formato de parâmetro inadequado (CONTENT=>" . gettype($content) . ")");
        }

        if (! is_array($parameters)) {
            throw new Exception("Formato de parâmetro inadequado (PARAMETERS=>" . gettype($parameters) . ")");
        }

        //Logger::info("Substituição de padrões do pseudo-html inicializado (" . implode(',', array_keys($parameters)) . ").");

        $this->tpl_dir = $tpl_dir;
        $this->content = $content;
        $this->parameters = $parameters;
    }

    // ####################################################################################################

    /**
     * 1-procura os padroes para substituicao
     * 2-realiza as substituicoes
     * 3-retorna uma string com codigo HTML + PHP
     *
     * @return string
     */
    public function run(): string
    {
        //Logger::info("Substituições inicializadas...");
        { // realiza eventuais ajustes no conteudo
            $this->fixContent();
            //Logger::info("Ajustes do codigo pseudo-html realizado.");
        }

        { // searchs
            $this->matches = [];
            {
                $this->searchConstants();
                //Logger::info("Constantes ({#CTE#})");

                $this->searchVariables();
                //Logger::info("Variáveis ({\$...})");

                $this->searchIncludes();
                //Logger::info("Includes (include)");

                $this->searchFunctions();
                //Logger::info("Funções (func)");

                $this->searchConditions();
                //Logger::info("Condições (if/else)");

                $this->searchLoop();
                //Logger::info("Laços (loop)");

                $this->searchPHPCode();
                //Logger::info("Código direto {{...}}");
            }
        }
        { // replaces

            // debc($this->matches,0);
            foreach ($this->matches as $search => $replaces) {
                foreach ($replaces as $replace) {
                    $this->content = Strings::str_replace_first($search, $replace, $this->content);
                }
            }
        }
        //Logger::success("...substituições finalizadas!");
        return $this->content;
    }

    // ####################################################################################################

    /**
     * procura o padrao informado e retorna uma lista das strings encontradas
     *
     * @param string $pattern
     * @return array
     */
    private function getPatternMatches(string $pattern): array
    {
        $return = [];
        { // pesquisa pelo padrao informado!
            $match_array = [];
            // deb(trim($pattern),0);
            $result = preg_match_all($pattern, $this->content, $match_array);
            if ($result === false) {
                $error = error_get_last();
                $error = $error['message'];
                throw new Exception("Foi encontrado um erro no padrão da expressão regular ('$pattern'). <br/>ERROR: $error.");
            }
            // deb($match);
        }
        { // depuracao dos dados obtidos
            foreach ($match_array as $real_match_array) {
                // deb($real_match_array);
                foreach ($real_match_array as $v) {
                    $return[] = $v;
                }
            }
            // deb($return,0);
        }
        return $return;
    }

    // ####################################################################################################
    /**
     * adiciona um registro aa lista de substuituicao com as devidas verificacoes.
     *
     * @param string $search
     * @param string $replace
     */
    private function addMatch(string $search, string $replace)
    {
        $this->matches[$search][] = $replace;
    }

    // ####################################################################################################
    /**
     * devolve o codigo informado, envolvido (iniciado e finalizado) pelas tags do PHP
     *
     * @return string
     */
    private static function phpWrap($code): string
    {
        $return = '<?php ';
        $return .= $code;
        $return .= ' ?>';
        return $return;
    }

    // ####################################################################################################

    /**
     * correcao de eventuais indentacoes indevidas no codigo entre outros
     *
     * @return mixed
     */
    private function fixContent()
    {
        { // fixes
            { // Wrong Automatic Identation (CRTL + F on Eclipse)
                $this->content = str_replace('" }', '"}', $this->content);
            }
        }
    }

    // ####################################################################################################
    /**
     * obtem (verificando) o padrao a ser utilizado
     *
     * @param string $left
     * @param string $pattern
     * @param string $right
     * @throws Exception
     * @return string
     */
    private function GetPattern(string $left, string $center, string $right, string $FUNCTION): string
    {
        $FUNCTION = strtoupper(str_replace('search', '', $FUNCTION));

        $bcl = $this->CheckPatternBoundErrors($left);        
        if ($bcl !== '') {
            throw new Exception("Caractere(s) proibido(s) encontrado(s) na delimitação esquerda no padrão de interpretação de templates. $FUNCTION LEFT => $bcl.");
        }
        $bcl = $this->CheckPatternBoundErrors($right);
        if ($bcl !== '') {
            throw new Exception("Caractere(s) proibido(s) encontrado(s) na delimitação direita no padrão de interpretação de templates. $FUNCTION RIGHT => $bcl.");
        }
        return '/' . $left . $center . $right . '/';
    }

    // ####################################################################################################
    /**
     * verifica se algum dos caracteres
     * da lista proibida foi encontrado
     * e retorna-os concatenados
     * em uma string
     *
     * @param string $string
     * @return string
     */
    private function CheckPatternBoundErrors(string $string): string
    {
        $return = [];
        //$stringRaw=$string;
        { // remove eventuais caracteres ja mascarados (padronizados)
            foreach (self::blackListChars as $bc) {
                $search = preg_quote($bc, '/');
                //deb("$string => $search",0);
                $string = str_replace($search, '', $string);
            }
            //debc("$stringRaw (-$search) => $string",0);
        }
        // procura por caracteres que deviam estar mascarados
        foreach (self::blackListChars as $bc) {
            if (preg_match('/[' . preg_quote($bc, '/') . ']/', $string)) {
                $return[] = $bc;
            }
        }
        return sizeof($return) > 0 ? '"' . implode('", "', $return) . '"' : '';
    }

    // ####################################################################################################
    // ####################################################################################################
    // ######################################################################################### CONSTANTES
    // ####################################################################################################
    // ####################################################################################################
    private function searchConstants()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{#';
                {
                    $center = '\w*';
                }
                $right = '#}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }
        $matches = $this->getPatternMatches($pattern);
        foreach ($matches as $rawMatch) {
            { // nome da cte
                $match = trim($rawMatch);
                $match = str_replace('{#', '', $match);
                $match = str_replace('#}', '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // codigo para substituicao
                    $replace = "echo $match;";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ########################################################################################## VARIÁVEIS
    // ####################################################################################################
    // ####################################################################################################
    private function searchVariables()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{\$';
                {
                    $center = '[';
                    $center .= '\w'; // Any word character (letter, number, underscore)
                    $center .= "'";
                    $center .= "\$";
                    $center .= '-';
                    $center .= '>';
                    $center .= '\(';
                    $center .= '\)';
                    $center .= '\[';
                    $center .= '\]';
                    $center .= ']+'; // 1 vez ou mais
                }
                $right = '}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }
        $matches = $this->getPatternMatches($pattern);
        // deb($matches);
        foreach ($matches as $rawMatch) {
            { // nome da cte
                $match = trim($rawMatch);
                $match = str_replace(str_replace('\\', '', $left), '', $match);
                $match = str_replace($right, '', $match);
                // deb($varName);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "echo $$match;";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ########################################################################################### INCLUDES
    // ####################################################################################################
    // ####################################################################################################
    private function searchIncludes()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{include="';
                {
                    $center = '[';
                    $center .= '\w'; // Any word character (letter, number, underscore)
                    $center .= '\.';
                    $center .= '\/';
                    $center .= ']+'; // 1 vez ou mais
                }
                $right = '"}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }
        $matches = $this->getPatternMatches($pattern);
        // deb($matches,0);
        foreach ($matches as $rawMatch) {
            { // nome do template a ser incluido
                $match = trim($rawMatch);
                $match = str_replace($left, '', $match);
                $match = str_replace($right, '', $match);
            }
            { // substituicao (pseudo-php => php)
              // replace
                $Page = new Page($this->tpl_dir, $match, $this->parameters);
                // addMatch
                $this->addMatch($rawMatch, $Page->getCacheContent());
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ################################################################################# FUNCOES ou METODOS
    // ####################################################################################################
    // ####################################################################################################
    private function searchFunctions()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{func="';
                {
                    $center = '[^}]+';
                }
                $right = '"}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }

        $matches = $this->getPatternMatches($pattern);
        // deb($matches,0);
        foreach ($matches as $rawMatch) {
            { // apara as rebarbas para obter apenas o conteudo de interesse
                $match = trim($rawMatch);
                $match = str_replace($left, '', $match);
                $match = str_replace($right, '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "echo $match;";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ########################################################################################## FREE CODE
    // ####################################################################################################
    // ####################################################################################################
    private function searchPHPCode()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{%';
                {
                    $center = '[^}]+';
                }
                $right = '%}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }

        $matches = $this->getPatternMatches($pattern);
        // deb($matches,0);
        foreach ($matches as $rawMatch) {
            { // apara as rebarbas para obter apenas o conteudo de interesse
                $match = trim($rawMatch);
                $match = str_replace($left, '', $match);
                $match = str_replace($right, '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = " $match; ";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ################################################################################ CONDICOES (IF/ELSE)
    // ####################################################################################################
    // ####################################################################################################
    private function searchConditions()
    {
        $this->searchConditions_if();
        $this->searchConditions_else();
        $this->searchConditions_if_end();
    }

    // ##################################################################################
    private function searchConditions_if()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{if="';
                {
                    $center = '[^}]+';
                }
                $right = '"}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }
        $matches = $this->getPatternMatches($pattern);
        // deb($matches);
        foreach ($matches as $rawMatch) {
            { // condicao
                $match = trim($rawMatch);
                $match = str_replace($left, '', $match);
                $match = str_replace($right, '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = " if($match){ ";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ##################################################################################
    private function searchConditions_else()
    {
        { // regex
            $matches = $this->getPatternMatches('/{else}/');
        }
        foreach ($matches as $rawMatch) {
            { // condicao
                $match = trim($rawMatch);
                $match = str_replace('{else}"', '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "}else{";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ##################################################################################
    private function searchConditions_if_end()
    {
        { // regex
            $matches = $this->getPatternMatches('/{\/if}/');
        }
        foreach ($matches as $rawMatch) {
            { // condicao
                $match = trim($rawMatch);
                $match = str_replace('{/if}', '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "}";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ####################################################################################################
    // ####################################################################################################
    // ##################################################################################### LOOP (foreach)
    // ####################################################################################################
    // ####################################################################################################
    private function searchLoop()
    {
        $this->searchLoopOpen();
        $this->searchLoopClose();
    }

    // ##################################################################################

    //
    private function searchLoopOpen()
    {
        { // regex pattern
            { // limites esq e dir
                $left = '{loop="';
                {
                    $center = '[^}]+';
                }
                $right = '"}';
            }
            $pattern = $this->GetPattern($left, $center, $right, __FUNCTION__);
            // deb($pattern);
        }
        $matches = $this->getPatternMatches($pattern);
        // deb($matches,0);

        // percorrendo todos os loops
        foreach ($matches as $index => $rawMatch) {
            { //
                $match = trim($rawMatch);
                $match = str_replace($left, '', $match);
                $match = str_replace($right, '', $match);
            }
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "foreach($match as \$key{$index}=>\$value{$index}){ \$key = \$key{$index}; \$value = \$value{$index}; ";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }

    // ##################################################################################
    private function searchLoopClose()
    {
        { // regex
            $matches = $this->getPatternMatches('/{\/loop}/');
            // deb($matches,0);
        }
        foreach ($matches as $rawMatch) {
            { // substituicao (pseudo-php => php)
                { // replace
                    $replace = "}";
                    $replace = self::phpWrap($replace);
                }
                // addMatch
                $this->addMatch($rawMatch, $replace);
            }
        }
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>