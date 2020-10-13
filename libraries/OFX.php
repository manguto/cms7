<?php
namespace manguto\cms7\libraries;

use SimpleXMLElement;

/**
 * classe para gerenciamendo de dados financeiros
 * atraves da manipulacao de arquivos/conteudos
 * no formato OFX (XML)
 *
 * @author Marcos Torres
 *        
 */
class OFX
{

    /*
     * public $bankTranList;
     *
     * public $dtStart;
     *
     * public $dtEnd;
     *
     * public $bankId;
     *
     * public $acctId;
     *
     * public $org;
     */
    private $SXML;

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    public function __construct(string $filename = '')
    {
        if ($filename != '') {
            $this->loadFromFile($filename);
        }
    }

    // ####################################################################################################
    public function loadFromFile(string $ofx_filename)
    {
        $xml_content = OFXTools::ConvertFileContentToXML($ofx_filename);
        $this->SXML = new \SimpleXMLElement($xml_content);
    }

    // ####################################################################################################
    public function loadFromContent(string $ofx_content)
    {
        $xml_content = OFXTools::ConvertContentToXML($ofx_content);
        $this->SXML = new \SimpleXMLElement($xml_content);
    }

    // ####################################################################################################
    /**
     * retorna um array com os dados informados pelo conteudo ofx
     *
     * @return array
     */
    public function getContentAsArray(): array
    {
        return json_decode(json_encode($this->SXML), TRUE);
    }

    // ####################################################################################################

    /**
     * carrega estrutura dos dados informados conforme o tipo
     */
    public function loadData()
    {
        $type = OFXTools::getOFXType($this);
        // deb($type);

        $array = $this->getContentAsArray();
        // deb($array);

        foreach (OFXConfig::OFXTypeParameters[$type] as $parameterName => $parameterKeys) {

            //evita parametros desnecessarios (sem nome)
            if(trim($parameterName)==''){
                continue;
            }
            
            { // parameters
                $parameterKeys = explode(',', $parameterKeys);
                $implodedKeys = implode("']['", $parameterKeys);
                $implodedKeys = "['" . $implodedKeys . "']";
                $eval = " \$parameterValue = \$array$implodedKeys ?? false; ";
                // deb($eval);
            }

            eval($eval);
            $this->$parameterName = $parameterValue;
            $this->SXML = null;
            
            /*if ($parameterValue !== false) {
                $this->$parameterName = $parameterValue;
            } else {
                throw new Exception("Não foi possível obter o parâmetro '$parameterName' no OFX informado.");
            }*/
        }
        
    }

    // ####################################################################################################

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>