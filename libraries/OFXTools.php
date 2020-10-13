<?php
namespace manguto\cms7\libraries;

/**
 *
 * @author MAGT
 *        
 */
class OFXTools
{

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################

    /**
     * retorna o conteudo do arquivo em XML (string)
     *
     * @param string $ofx_filename
     * @param bool $utf8_encode
     * @return string
     */
    static function ConvertFileContentToXML(string $ofx_filename, bool $utf8_encode = true): string
    {
        {
            $ofx_content = File::getContent($ofx_filename);
            if ($utf8_encode) {
                $ofx_content = utf8_encode($ofx_content);
            }
        }
        return self::ConvertContentToXML($ofx_content);
    }

    // ####################################################################################################

    /**
     * retorna o conteudo em XML (string)
     *
     * @param string $ofx_content
     *            - utf8
     * @return string
     */
    static function ConvertContentToXML(string $ofx_content): string
    {
        {
            $xml_content = self::GetXMLContent($ofx_content);
        }
        return $xml_content;
    }

    // ####################################################################################################

    /**
     * verifica, ajusta caso necessario e retorna conteudo ofx
     *
     * @param string $ofx_content
     * @throws Exception
     * @return string
     */
    private static function CheckFixOFXStructure(string $ofx_content): string
    {
        // tag inicilaizacao
        if (strpos($ofx_content, OFXConfig::ofx_needle_start) !== false) {

            // tag finalizacao
            if (strpos($ofx_content, OFXConfig::ofx_needle_end) === false) {
                throw new Exception("Não foi possível encontrar o marcador final ('" . OFXConfig::ofx_needle_end . "') no arquivo OFX informado.");
            }

            // conteudo (linha a linha)
            $ofx_content_array = explode(OFXConfig::ofx_needle_start, $ofx_content);

            if (sizeof($ofx_content_array) == 2) {
                $ofx_content_header = $ofx_content_array[0];
                $ofx_content_body = OFXConfig::ofx_needle_start . chr(10) . chr(10) . $ofx_content_array[1];
                $lines = explode(chr(10), $ofx_content_body);
                // ###############################################
                for ($i = 0; $i < sizeof($lines); $i ++) {
                    $lines[$i] = trim($lines[$i]);
                    if ($lines[$i] == '') {
                        continue;
                    }

                    // PARAMETROS!
                    $startTagFound = substr($lines[$i], 0, 1) === '<';
                    $endTagFound = substr($lines[$i], - 1, 1) === '>';

                    { // verificacao de linha sem tag de abertura
                        if (! $startTagFound) {
                            $lines[$i] = $lines[$i - 1] . $lines[$i];
                            $lines[$i - 1] = '';
                        }
                    }

                    { // verificacao de linha sem tag de fechamento
                        if (! $endTagFound) {
                            $lines[$i + 1] = $lines[$i] . $lines[$i + 1];
                            $lines[$i] = '';
                        }
                    }
                }
                // ################################################
                // montagem do corpo
                $ofx_content_body_array = [];
                foreach ($lines as $line) {
                    if (trim($line) == '') {
                        continue;
                    }
                    $ofx_content_body_array[] = $line;
                }
                $ofx_content_body = implode(chr(10), $ofx_content_body_array);

                { // montagem ofx completo
                    $ofx_content = $ofx_content_header . chr(10) . $ofx_content_body;
                }
            } else {
                throw new Exception("Foram encontrados mais de um marcadores iniciais ('" . OFXConfig::ofx_needle_start . "') para o arquivo OFX informado.");
            }
        } else {
            throw new Exception("Não foi possível encontrar o marcador inicial ('" . OFXConfig::ofx_needle_start . "') no arquivo OFX informado.");
        }
        return $ofx_content;
    }

    // ####################################################################################################

    /**
     * retorna conteudo XML
     *
     * @param string $ofx_raw_content
     * @return string
     */
    private static function GetXMLContent(string $ofx_raw_content): string
    {
        { // verifica e ajusta estrutura informada
            $ofx_content = self::CheckFixOFXStructure($ofx_raw_content);
        }

        { // obtencao de conteudo principal
            $ofx_content = trim(substr($ofx_raw_content, strpos($ofx_raw_content, OFXConfig::ofx_needle_start) - 1));
        }

        { // use DOMDocument with non-standard recover mode
            $doc = new \DOMDocument();
            $doc->recover = true;
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            $save = libxml_use_internal_errors(true);
            $doc->loadXML($ofx_content);
            libxml_use_internal_errors($save);
            $xml_content = $doc->saveXML();
            $xml_content = trim($xml_content);
        }

        return $xml_content;
    }

    // ####################################################################################################
    /**
     * obtem o tipo do extrato informado
     * @param OFX $OFX
     * @throws Exception
     * @return string
     */
    static function getOFXType(OFX $OFX):string
    {
        $array = $OFX->getContentAsArray();
        //deb(Arrays::multiToSingleDimension($array,'',']['));

        if (isset($array['CREDITCARDMSGSRSV1'])) {
            return 'cartao_credito';
        }
        if (isset($array['BANKMSGSRSV1'])) {
            $ACCTTYPE = $array['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKACCTFROM']['ACCTTYPE'] ?? false;

            {
                if ($ACCTTYPE == 'CHECKING') {
                    return 'conta_corrente';
                }
            }
            {
                if ($ACCTTYPE == 'SAVINGS') {
                    return 'poupanca';
                }
            }
        }
        throw new Exception("Não foi possível identificar o tipo de extrato informado.");
    }
    // ####################################################################################################
    /**
     * obtem o tipo de extrato para exibicao (show)
     * @param string $OFXType
     * @throws Exception
     * @return string
     */
    static function getOFXTypeShow(string $OFXType):string{
        $return = OFXConfig::OFXType[$OFXType] ?? false;        
        if($return==false){
            throw new Exception("Não foi possível identificar o tipo de extrato informado ($OFXType).");
        }else{
            return $return;
        }
    }
    // ####################################################################################################
    
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}























