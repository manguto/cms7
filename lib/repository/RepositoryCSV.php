<?php

namespace manguto\cms5\lib\repository;

use manguto\cms5\lib\Exception;

class RepositoryCSV{
    
    const parameterSeparator = ';';
    const reservedCaracters = [';','
'];        
    
    /**
     * Converte um ARRAY para uma string no formato CSV
     * @param array $array
     * @throws Exception
     * @return string
     */
    static function ArrayToCSV(array $array):string{

        $lines = [];
        if(sizeof($array)>0){
            
            {//HEADER                 
                $columnNameArray = [];
                //percorre todos os registros
                foreach ($array as $line){
                    //obtem os nomes das colunas do registro
                    $entry_columnNameArray = array_keys($line);
                    //percorre cada nome de coluna
                    foreach ($entry_columnNameArray as $columnName) {
                        //verifica se ja foi registrado e caso contrario o registra
                        if(!in_array($columnName, $columnNameArray)){
                            $columnNameArray[] = $columnName;                            
                        }
                    }
                }
                //insere no topo do futuro arquivo csv o cabecalho das colunas 
                $lines[] = implode(RepositoryCSV::parameterSeparator, $columnNameArray);
            }
            {//BODY

                foreach ($array as $lineValues) {

                    if(is_array($lineValues)){                        
                        //pula as linhas vazias
                        if(trim(implode('', $lineValues))==''){
                            continue;
                        }
                        //variavel para armazenamento do conteudo da linha para posterior string'alizacao
                        $line = [];
                        //percorre cada coluna registrada
                        foreach ($columnNameArray as $columnName){
                            if(isset($lineValues[$columnName])){
                                //$line[] = $lineValues[$columnName]; ERRO/ESQUECIMENTO TERMINAL!
                                $line[] = self::mascString($lineValues[$columnName]);
                            }else{
                                $line[] = '';
                            }
                        }
                        $lines[]=implode(RepositoryCSV::parameterSeparator, $line);
                                             
                    }else{
                        throw new Exception("Não foi possível converter o array para uma string (CSV).");
                    }
                }
            }
        }
        $lineBreak = chr(10); 
        $csv=implode($lineBreak, $lines).$lineBreak;        
        return $csv;
        
    }
    /**
     * Converte um string no formato CSV para um ARRAY
     * @param array $array
     * @throws Exception
     * @return string
     */
    static function CSVToArray(string $csv){
        
        //deb($csv);
        $array=[];
        $lineBreak = chr(10); 
        $csvLineArray = explode($lineBreak, $csv);
        //deb($csvLineArray,0);
        if(sizeof($csvLineArray)>1){
            
            {// montagem / analise do cabecalho
                
                //retirada da primeira linha (em csv) que seria o cabecalho das colunas (titulos)
                $csvHeaderLineCSV = array_shift($csvLineArray);

                //transformacao em array
                $csvHeaderLine_ = explode(RepositoryCSV::parameterSeparator, $csvHeaderLineCSV);
                
                $headerLineArray = [];
                
                foreach ($csvHeaderLine_ as $key=>$p){                    
                    $p = RepositoryCSV::unmascString($p);                    
                    $headerLineArray[$key] = $p;                    
                    if($p=='id'){
                        $idKey = $key;
                    }
                }
                if(!isset($idKey)){
                    throw new Exception("Não foi encontrada nenhum parâmetro (".implode(', ',$headerLineArray).") que coincida com a chave para indexação informada 'id'.");
                }
                //deb($csvHeaderLine,0);
            }
            
            {//body
                //deb($csvLines);
                foreach ($csvLineArray as $csvLine) {
                    //pula linhas vazias 
                    if(trim($csvLine)==""){
                        continue;
                    }
                    
                    $csvBodyLine_ = explode(RepositoryCSV::parameterSeparator, $csvLine);                        
                    $id = $csvBodyLine_[$idKey];
                    foreach ($csvBodyLine_ as $key=>$p){
                        {
                            $columnName = trim($headerLineArray[$key]);
                            $columnValue = trim(RepositoryCSV::unmascString($p));
                        }                        
                        $array[$id][$columnName] = $columnValue;
                    }                    
                }
            }
        }
        //deb($array);
        return $array;        
    }
    
    //------------------------------------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------------------------------
    //---------------------------------  FUNCOES AUXILIARES INTERNAS ---------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------------------------------
    
    /**
     * aplica mascara a uma string para evitar conflito de 'parseamento'
     * @param string $string
     * @return string
     */
    static private function mascString(string $string):string{
        foreach (RepositoryCSV::reservedCaracters as $key=>$rc) {
            {
                $search = $rc;
                $replace = "<$key>";
            }
            $string = str_replace($search, $replace, $string);
        }
        return $string;
        
        
    }
    /**
     * remove a mascara de string, colocada para evitar conflito de 'parseamento'
     * @param string $string
     * @return string
     */
    static private function unmascString(string $string):string{
        foreach (RepositoryCSV::reservedCaracters as $key=>$rc) {
            {   
                $search = "<$key>";
                $replace = $rc;
            }
            $string = str_replace($search, $replace, $string);
        }
        return $string;
    }
    
    
}