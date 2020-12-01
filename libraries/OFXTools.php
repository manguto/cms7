<?php

namespace manguto\cms7\libraries;

/**
 *
 * @author MAGT
 *        
 */
class OFXTools {
	// cabecalho ofx - delimitador entre o nome do attributo e o seu valor
	const header_attributes_delimiter = ':';
	// ####################################################################################################
	// ####################################################################################################
	// ####################################################################################################
	/**
	 * retorna estrutura formatada do arquivo OFX
	 *
	 * @param string $ofx_filename
	 * @param bool $utf8_encode
	 * @return array
	 */
	static function GetOFXFormattedStruct(string $ofx_filename): array{
		return self::GetOFXFormattedContent(File::getContent($ofx_filename));
	}
	// ####################################################################################################
	/**
	 * formata o conteudo enviado e retorna-o como cabecalho e corpo(xml)
	 *
	 * @param string $ofx_content
	 *        	- utf8
	 * @return array
	 */
	static function GetOFXFormattedContent(string $ofx_content): array{
		return self::GetFormattedContent($ofx_content);
	}
	// ####################################################################################################
	/**
	 * verifica, ajusta caso necessario e retorna conteudo ofx
	 *
	 * @param string $ofx_content
	 * @throws Exception
	 * @return string
	 */
	private static function CheckFixOFXStructure(string $ofx_content): string{
		// tag inicilaizacao
		if(strpos($ofx_content, OFXConfig::ofx_needle_start) !== false){
			// tag finalizacao
			if(strpos($ofx_content, OFXConfig::ofx_needle_end) === false){
				throw new Exception("Não foi possível encontrar o marcador final ('" . OFXConfig::ofx_needle_end . "') no arquivo OFX informado.");
			}
			// conteudo (linha a linha)
			$ofx_content_array = explode(OFXConfig::ofx_needle_start, $ofx_content);
			if(sizeof($ofx_content_array) == 2){
				$ofx_content_header = $ofx_content_array[0];
				$ofx_content_body = OFXConfig::ofx_needle_start . chr(10) . chr(10) . $ofx_content_array[1];
				$lines = explode(chr(10), $ofx_content_body);
				// ###############################################
				for($i = 0; $i < sizeof($lines); $i++){
					$lines[$i] = trim($lines[$i]);
					if($lines[$i] == ''){
						continue;
					}
					// PARAMETROS!
					$startTagFound = substr($lines[$i], 0, 1) === '<';
					$endTagFound = substr($lines[$i], - 1, 1) === '>';
					{ // verificacao de linha sem tag de abertura
						if(! $startTagFound){
							$lines[$i] = $lines[$i - 1] . $lines[$i];
							$lines[$i - 1] = '';
						}
					}
					{ // verificacao de linha sem tag de fechamento
						if(! $endTagFound){
							$lines[$i + 1] = $lines[$i] . $lines[$i + 1];
							$lines[$i] = '';
						}
					}
				}
				// ################################################
				// montagem do corpo
				$ofx_content_body_array = [];
				foreach($lines as $line){
					if(trim($line) == ''){
						continue;
					}
					$ofx_content_body_array[] = $line;
				}
				$ofx_content_body = implode(chr(10), $ofx_content_body_array);
				{ // montagem ofx completo
					$ofx_content = $ofx_content_header . chr(10) . $ofx_content_body;
				}
			}else{
				throw new Exception("Foram encontrados mais de um marcadores iniciais ('" . OFXConfig::ofx_needle_start . "') para o arquivo OFX informado.");
			}
		}else{
			throw new Exception("Não foi possível encontrar o marcador inicial ('" . OFXConfig::ofx_needle_start . "') no arquivo OFX informado.");
		}
		return $ofx_content;
	}
	// ####################################################################################################
	/**
	 * retorna estrutura formatada (header e body) do arquivo OFX
	 *
	 * @param string $ofx_raw_content
	 * @return array
	 */
	private static function GetFormattedContent(string $ofx_content): array{
		{ // verifica e ajusta estrutura informada
			$ofx_content = self::CheckFixOFXStructure($ofx_content);
		}
		{ // obtencao dos conteudos
			$ofx_header = trim(substr($ofx_content, 0, strpos($ofx_content, OFXConfig::ofx_needle_start) - 1));
			$ofx_body = trim(substr($ofx_content, strpos($ofx_content, OFXConfig::ofx_needle_start) - 1));
		}
		{ // HEADER => ARRAY
			{
				$delimiter = strpos($ofx_header, chr(10)) !== false ? chr(10) : chr(13);
			}
			$header_array = explode($delimiter, $ofx_header);
			$header = [];
			foreach($header_array as $line){
				if(trim($line) == ''){
					continue;
				}else{
					$line_array = explode(self::header_attributes_delimiter, $line);
					if(sizeof($line_array) != 2){
						throw new Exception("Linha do cabeçalho em formato desconhecido ($line).");
					}else{
						$attrName = $line_array[0];
						$attrValue = $line_array[1];
						$header[$attrName] = trim($attrValue);
					}
				}
			}
			// deb($header);
			{
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
				{
					$CHARSET = strtoupper($header['CHARSET']);
					if($CHARSET != 'UTF-8' && $CHARSET != 'UTF8'){
						$utf8_encode_needed = true;
					}else{
						$utf8_encode_needed = false;
					}
				}
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
				// >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO >>> CHARSET INFO
			}
		}
		{ // BODY => utilizacao da classe DOMDocument para formatacao do conteudo XML
		  //
			{ // especificacoes DOM Document
				$doc = new \DOMDocument();
				$doc->recover = true;
				$doc->preserveWhiteSpace = false;
				$doc->formatOutput = true;
				$save = libxml_use_internal_errors(true);
			}
			{ // insercao e tratamento do conteudo
				{//verificacao de necessidade de conversao utf8 
					if($utf8_encode_needed){
						$ofx_body = utf8_encode($ofx_body);
					}
				}				
				$doc->loadXML($ofx_body);
				libxml_use_internal_errors($save);
				$body = $doc->saveXML();
				$body = trim($body);
			}
			//
		}
		return [
				$header,
				$body
		];
	}
	// ####################################################################################################
	/**
	 * obtem o tipo do extrato informado
	 *
	 * @param OFX $OFX
	 * @throws Exception
	 * @return string
	 */
	static function getOFXType(OFX $OFX): string{
		$array = $OFX->getContentAsArray();
		// deb(Arrays::multiToSingleDimension($array,'',']['));
		if(isset($array['CREDITCARDMSGSRSV1'])){
			return 'CREDITCARD';
		}
		if(isset($array['BANKMSGSRSV1'])){
			$ACCTTYPE = $array['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKACCTFROM']['ACCTTYPE'] ?? false;
			{
				if($ACCTTYPE == 'CHECKING'){
					return 'CHECKING';
				}
			}
			{
				if($ACCTTYPE == 'SAVINGS'){
					return 'SAVINGS';
				}
			}
		}
		throw new Exception("Não foi possível identificar o tipo de extrato informado.");
	}
	// ####################################################################################################
	/**
	 * obtem o tipo de extrato para exibicao (show)
	 *
	 * @param string $OFXType
	 * @throws Exception
	 * @return string
	 */
	static function getOFXTypeShow(string $OFXType): string{
		$return = OFXConfig::OFXType[$OFXType] ?? false;
		if($return == false){
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























