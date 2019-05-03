<?php
namespace manguto\cms5\lib;

class Strings
{

    const textosNaoAcronimaveis = [
        'da',
        'de',
        'do'
    ];

    const textosNaoAbreviaveis = [
        'da',
        'de',
        'do'
    ];

    static function RemoverCaracteresDeControle($texto, $permitirQuebradeLinha = true)
    {
        if ($permitirQuebradeLinha) {
            $texto = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $texto);
        } else {
            $texto = preg_replace('/[\x00-\x1F\x7F]/', '', $texto);
        }
        return $texto;
    }
    
    static function RemoverCaracteresEspeciais($string)
    {
        // Replaces all spaces with hyphens.
        // $string = str_replace(' ', '-', $string);
        // Removes special chars.
        $string = preg_replace('/[^A-Za-z0-9\- ]/', '', $string);
        return $string;
    }
    
    static function RemoverQuebrasDeLinha($string,$replace=''){        
        $return = preg_replace('/[\x00-\x1F\x7F]/', $replace, $string);
        return $return;
    }
    

    static function acronimo($string)
    {
        $string = trim($string);
        $string_array = explode(' ', $string);
        $return = '';
        foreach ($string_array as $s) {
            if (in_array(strtolower($s), self::textosNaoAcronimaveis))
                continue;
            $return .= substr($s, 0, 1);
        }
        return $return;
    }

    static function abreviacao($strString, $intLength = NULL)
    {
        $defaultAbbrevLength = 8; // Default abbreviation length if none is specified

        // Set up the string for processing
        $strString = preg_replace("/[^A-Za-z0-9]/", '', $strString); // Remove non-alphanumeric characters
        $strString = ucfirst($strString); // Capitalize the first character (helps with abbreviation calcs)
        $stringIndex = 0;
        // Figure out everything we need to know about the resulting abbreviation string
        $uppercaseCount = preg_match_all('/[A-Z]/', $strString, $uppercaseLetters, PREG_OFFSET_CAPTURE); // Record occurences of uppercase letters and their indecies in the $uppercaseLetters array, take note of how many there are
        $targetLength = isset($intLength) ? intval($intLength) : $defaultAbbrevLength; // Maximum length of the abbreviation
        $uppercaseCount = $uppercaseCount > $targetLength ? $targetLength : $uppercaseCount; // If there are more uppercase letters than the target length, adjust uppercaseCount to ignore overflow
        $targetWordLength = round($targetLength / intval($uppercaseCount)); // How many characters need to be taken from each uppercase-designated "word" in order to best meet the target length?
        $abbrevLength = 0; // How long the abbreviation currently is
        $abbreviation = ''; // The actual abbreviation
                            // Create respective arrays for the occurence indecies and the actual characters of uppercase characters within the string
        for ($i = 0; $i < $uppercaseCount; $i ++) {
            // $ucIndicies[] = $uppercaseLetters[1]; //Not actually used. Could be used to calculate abbreviations more efficiently than the routine below by strictly considering indecies
            $ucLetters[] = $uppercaseLetters[0][$i][0];
        }
        $characterDeficit = 0; // Gets incremented when an uppercase letter is encountered before $targetCharsPerWord characters have been collected since the last UC char.
        $wordIndex = $targetWordLength; // HACK: keeps track of how many characters have been carried into the abbreviation since the last UC char
        while ($stringIndex < strlen($strString)) { // Process the whole input string...
            if ($abbrevLength >= $targetLength) // ...unless the abbreviation has hit the target length cap
                break;
            $currentChar = $strString[$stringIndex ++]; // Grab a character from the string, advance the string cursor
            if (in_array($currentChar, $ucLetters)) { // If handling a UC char, consider it a new word
                $characterDeficit += $targetWordLength - $wordIndex; // If UC chars are closer together than targetWordLength, keeps track of how many extra characters are required to fit the target length of the abbreviation
                $wordIndex = 0; // Set the wordIndex to reflect a new word
            } else if ($wordIndex >= $targetWordLength) {
                if ($characterDeficit == 0) // If the word is full and we're not short any characters, ignore the character
                    continue;
                else
                    $characterDeficit --; // If we are short some characters, decrement the defecit and carry on with adding the character to the abbreviation
            }
            $abbreviation .= $currentChar; // Add the character to the abbreviation
            $abbrevLength ++; // Increment abbreviation length
            $wordIndex ++; // Increment the number of characters for this word
        }
        return $abbreviation;
    }

    static function RemoverAcentosECaracteresLinguisticos($string,$strtolower=false)
    {
        $table = [
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ŕ' => 'R',
            'ŕ' => 'r'
        ];
        $return = strtr($string, $table);
        if($strtolower){
            $return = strtolower($return);
        }
        return $return;
    }

    static function RemoverAcentosECaracteresLinguisticos20181221($string)
    {
        $return = preg_replace(array(
            "/(ç)/",
            "/(Ç)/",
            "/(á|à|ã|â|ä)/",
            "/(Á|À|Ã|Â|Ä)/",
            "/(é|è|ê|ë)/",
            "/(É|È|Ê|Ë)/",
            "/(í|ì|î|ï)/",
            "/(Í|Ì|Î|Ï)/",
            "/(ó|ò|õ|ô|ö)/",
            "/(Ó|Ò|Õ|Ô|Ö)/",
            "/(ú|ù|û|ü)/",
            "/(Ú|Ù|Û|Ü)/",
            "/(ñ)/",
            "/(Ñ)/"
        ), explode(" ", "c C a A e E i I o O u U n N"), $string);
        return $return;
    }

    static function RemoverEspacamentosRepetidos($string)
    {
        // xxx($string,0);
        $return = preg_replace('/[ ]([ ])+/', ' ', $string);
        // xxx($return);
        return $return;
        /*
         * while(substr_count($string, ' ')>0){
         * $string = str_replace(' ', ' ', $string);
         * }
         * return $string;
         */
    }

    /**
     * retorna um HTML com cada caractere 'titulado' pelo seu numero na tabela ascii
     * @param string $string
     * @return string
     */
    static function ObterASCIICodes(string $string):string
    {
        $return = array();
        for ($c = 0; $c < strlen($string); $c ++) {

            $caracter = $string[$c];
            $ascii = ord($caracter);

            $return[] = "<a href='#' title='$caracter($ascii)'>$caracter</a>";
        }
        return implode('', $return);
    }

    static function RemoverCaracteresInvisiveis($string)
    {
        $string_ = explode(' ', $string);
        foreach ($string_ as $k => $s) {
            $string_[$k] = trim($s);
        }
        return implode(' ', $string_);
    }

    /**
     * retorna um caractere aleatorio dentre a faixa definida com relacao aa tabela ascii
     *
     * @param number $ascii_in
     * @param number $ascii_out
     * @return string
     */
    static function AleatorioCaractere($ascii_in = 97, $ascii_out = 122): string
    {
        return chr(rand($ascii_in, $ascii_out));
    }

    /**
     * retorna uma string aleatoria com a quantidade de caracteres definida, dentre a faixa informada (ascii)
     *
     * @param number $quantCaracteres
     * @param number $ascii_in
     * @param number $ascii_out
     * @return string
     */
    static function AleatoriaString($quantCaracteres = 1, $ascii_in = 97, $ascii_out = 122): string
    {
        $quantCaracteres = intval($quantCaracteres);
        $return = '';
        for ($i = 0; $i < $quantCaracteres; $i ++) {
            $return .= self::AleatorioCaractere($ascii_in, $ascii_out);
        }
        return $return;
    }

    /**
     * retorna uma string aleatoria de NUMEROS com a quantidade de caracteres definida
     *
     * @param number $quantCaracteres
     * @param number $ascii_in
     * @param number $ascii_out
     * @return string
     */
    static function AleatoriosNumeros($quantCaracteres = 1): string
    {
        $quantCaracteres = intval($quantCaracteres);
        // 0-9
        $ascii_in = 48;
        $ascii_out = 57;
        $return = '';
        for ($i = 0; $i < $quantCaracteres; $i ++) {
            $return .= self::AleatorioCaractere($ascii_in, $ascii_out);
        }
        return $return;
    }

    /**
     * gera uma sequencia similar a de um cpf sem validacao
     *
     * @return string
     */
    static function AleatorioCPF(): string
    {
        return self::AleatoriosNumeros(3) . '.' . self::AleatoriosNumeros(3) . '.' . self::AleatoriosNumeros(3) . '-' . self::AleatoriosNumeros(2);
    }

    /**
     * gera uma sequencia similar a de um celular
     *
     * @return string
     */
    static function AleatorioCelular($ddd = 87): string
    {
        return "(87) 9." . rand(8, 9) . self::AleatoriosNumeros(3) . '-' . self::AleatoriosNumeros(4);
    }

    /**
     * gera uma sequencia similar a de um nome de pessoa sem padrao definido
     *
     * @return string
     */
    static function AleatorioNomePessoa(): string
    {
        $return = [];
        // quantidade de palavras do nome da pessoa
        $quantp = rand(2, 4);
        for ($p = 1; $p <= $quantp; $p ++) {
            $pmaxlen = 10;
            if ($p == 1 || $p == $quantp) {
                $pminlen = 5;
            } else {
                $pminlen = 1;
            }
            $ptmp = Strings::AleatoriaString(rand($pminlen, $pmaxlen));
            if (strlen($ptmp) > 2) {
                $ptmp = ucfirst($ptmp);
            }
            $return[] = $ptmp;
        }
        $return = implode(' ', $return);
        return $return;
    }

    static function unwrap(string $string, string $delimiterLeft, string $delimiterRight): string
    {
        $string_ = explode($delimiterLeft, $string);
        $sizeof = sizeof($string_);
        if ($sizeof == 2) {
            $string = $string_[1];
            $string_ = explode($delimiterRight, $string);
            $sizeof = sizeof($string_);
            if ($sizeof == 2) {
                $string = $string_[0];
            } else {
                throw new Exception("Foram encontrados mais de um ($sizeof) delimitador direito na conteúdo informado (Delimitar Direito: '$delimiterRight').");
            }
        } else {
            throw new Exception("Foram encontrados mais de um ($sizeof) delimitador esquerdo na conteúdo informado (Delimitar Esquerdo: '$delimiterLeft').");
        }
        return $string;
    }

    static function showCSV(string $text): string
    {
        // ajuste correcional de caracteres especiais
        $text = Strings::RemoverAcentosECaracteresLinguisticos($text);
        $csvLinhas = explode(chr(10), $text);
        { // levantamento de informacoes
            $quantMaxCaracteresColuna = [];
            foreach ($csvLinhas as $csvLinha) {
                $csvLinha = trim($csvLinha);
                if ($csvLinha == '')
                    continue;
                $csvLinhaConteudos = explode(';', $csvLinha);
                foreach ($csvLinhaConteudos as $coluna => $csvLinhaConteudo) {
                    $csvLinhaConteudo = trim($csvLinhaConteudo);
                    $quantCaracteresConteudo = strlen($csvLinhaConteudo);
                    if (! isset($quantMaxCaracteresColuna[$coluna]) || $quantCaracteresConteudo > $quantMaxCaracteresColuna[$coluna]) {
                        $quantMaxCaracteresColuna[$coluna] = $quantCaracteresConteudo;
                    }
                }
            }
        }
        $return = [];
        { // ajuste dos espacamentos e montagem do retorno
          // $csvLinhas = explode(chr(10),$text);
            foreach ($csvLinhas as $csvLinha) {
                $csvLinha = trim($csvLinha);
                if ($csvLinha == '')
                    continue;
                $csvLinhaConteudos = explode(';', $csvLinha);
                foreach ($csvLinhaConteudos as $coluna => &$csvLinhaConteudo) {

                    $csvLinhaConteudo = trim($csvLinhaConteudo);

                    $csvLinhaConteudo = ' ' . str_pad($csvLinhaConteudo, $quantMaxCaracteresColuna[$coluna] + 1, ' ', STR_PAD_RIGHT);
                }
                $return[] = '|' . implode('|', $csvLinhaConteudos) . '|';
            }
        }
        // deb($quantMaxCaracteresColuna,0);
        $return = implode(chr(10), $return);
        return $return;
    }

    static function SubstituirConteudoEntreTextos(string $searchIni, string $searchEnd, string $replace, string $subject, bool $removeSearch = true)
    {
        { // VERIFICACOES INICIAIS
            $searchIniAmount = substr_count($subject, $searchIni);
            $searchEndAmount = substr_count($subject, $searchEnd);
            { // testes
                if ($searchIniAmount != 1) {
                    throw new \Exception("Quantidade incorreta ($searchIniAmount!=1) de parâmetro de inicialização para balizamento de substituição.");
                }
                if ($searchEndAmount != 1) {
                    throw new \Exception("Quantidade incorreta ($searchEndAmount!=1) de parâmetro de finalização para balizamento de substituição.");
                }
            }
        }
        { // SEPARACAO DAS PARTES ( A | B | C )
            $data = explode($searchIni, $subject);

            if (sizeof($data) == 2) {
                $A = $data[0];
                $BC = $data[0];
            } else {
                $A = '';
                $BC = $data[0];
            }

            $data = explode($searchEnd, $BC);
            if (sizeof($BC) == 2) {
                $B = $data[0];
                $C = $data[1];
            } else {
                $B = $data[0];
                $C = '';
            }
        }
        { // JUNCAO DAS PARTES PARA RETORNO
            $return = $A . $replace . $C;
        }
        return $return;
    }

    static function str_inverter($string)
    {

        // deb(chr(65));

        // 0-255
        for ($c = 0; $c < strlen($string); $c ++) {

            { // rotation

                { // actual
                    $chr = $string[$c];
                    $ord = ord($chr);
                }
                { // new
                    $ordNew = 256 - $ord;
                    $chrNew = chr($ordNew);
                }
                // deb("'$chr'[$ord] => '$chrNew'[$ordNew] ",0);
            }

            $string[$c] = $chrNew;
        }
        return $string;
    }
}

?>
