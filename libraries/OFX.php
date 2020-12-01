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
class OFX {
	
	public $filename;
	
	public $IDENTIFIER;
	
	public $HEADER;
	
	public $BODY;
	
	const ofx_identifier_parameters =[			
			'FID',
			'ACCTID',
			'DTSERVER',
			'DTASOF',			
			//'BALAMT'
	];
	
	// ####################################################################################################
	// ####################################################################################################
	// ####################################################################################################
	public function __construct(string $filename = '', bool $loadData = true, bool $setExtraAttributes= true){
		$this->filename = $filename;
		if($filename!='' && $loadData){
			$this->loadFromFile();
			if($setExtraAttributes){
				$this->setExtraAttributes();
				$this->setIdentifier();
			}
		}
	}
	// ####################################################################################################
	public function loadFromFile(){
		$ofx_content = File::getContent($this->filename);
		$return = $this->loadFromContent($ofx_content);
		return $return;
	}
	// ####################################################################################################
	public function loadFromContent(string $ofx_content){
		{
			[
					$header,
					$body
			] = OFXTools::GetOFXFormattedContent($ofx_content);
		}
		$this->HEADER = $header;
		$this->BODY = new \SimpleXMLElement($body);		
	}
	// ####################################################################################################
	/**
	 * define um identificador para o extrato (evitar recarregamento etc.) 
	 * @throws Exception
	 * @return string
	 */
	public function setIdentifier(){
		{
			$identifier = [];
			foreach (self::ofx_identifier_parameters as $PNAME){
				$P = $this->$PNAME ?? false;
				if($P===false){
					deb($this,0);
					throw new Exception("Um dos atributos necessários para a criação do identificador, não foi encontrado ($PNAME).");
				}else{
					{//ajuste de parametros datados (20200831120000[-3:BRT] => 20200831120000)
						if(strpos($P, '[')!==false){
							$P = substr($P,0,14);
						}
					}
					$identifier[] = $P;
				}				
			}
			//deb($return);
			$identifier = implode('_', $identifier);
			$this->IDENTIFIER = $identifier;
		}
	}
	// ####################################################################################################
	/**
	 * retorna um array com os dados informados pelo conteudo ofx
	 *
	 * @return array
	 */
	public function getContentAsArray(): array{
		return json_decode(json_encode($this->BODY), TRUE);
	}
	// ####################################################################################################
	/**
	 * define atribuitos com base no tipo do extrato
	 */
	public function setExtraAttributes(){
		$array = $this->getContentAsArray();
		// deb($array);
		$type = OFXTools::getOFXType($this);
		// deb($type);
		foreach(OFXConfig::OFXTypeParameters[$type] as $parameterName => $parameterKeys){
			// evita parametros desnecessarios (sem nome)
			if(trim($parameterName) == ''){
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
			/*if(is_string($parameterValue)){
				$parameterValue = trim(Strings::RemoverEspacamentosRepetidos($parameterValue));
			}/**/
			$this->$parameterName = $parameterValue;
		}
	}
	// ####################################################################################################
	public function __toString(){
		$return = [];
		// debc($this);
		$vars = get_object_vars($this);
		// debc($vars);
		foreach($vars as $varName => $varValue){
			$varType = gettype($varValue);
			if($varType == 'string'){
				$return[] = "$varName=" . trim($varValue);
			}else if($varType == 'array'){
				$return[] = "$varName ($varType (" . sizeof($varValue) . "))";
			}else if($varType == 'object'){
				$return[] = "$varName ($varType (" . get_class($varValue) . "))";
			}else{
				$return[] = "$varName ($varType)";
			}
		}
		return implode('|', $return);
	}
	// ####################################################################################################
	// ####################################################################################################
	// ####################################################################################################
}
?>