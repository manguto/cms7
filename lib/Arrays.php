<?php
namespace manguto\cms5\lib;

class Arrays
{
    
    /**
     * Retorna um array com um unico nivel, atraves de outro multi-nivel cujas chaves serao as chaves
     * @param array $arquivoArray
     * @return array
     */
    static function arrayMultiNivelParaSimples(array $array,$chave_base='',$delimitador='___'):array
    {
        $return = array();
        foreach ($array as $chave=>$item) {
            
            if (is_array($item)) {
                
                $array2 = self::arrayMultiNivelParaSimples($item,$chave_base.$delimitador,$delimitador);
                
                foreach ($array2 as $chave2=>$item2) {
                    
                    $return[$chave.$delimitador.$chave2] = $item2;
                    
                }
                
            } else {
                
                $return[$chave] = $item;
                
            }
        }
        return $return;
    }

    /**
     * retorna um item de um array multinível com base em uma chave multipla no formato de string, parametrizada por um delimitador comum
     * @param array $array
     * @param string $multipleKeyString
     * @param string $delimiter
     * @return array
     */
    static function getValue_by_multipleReferenceKeyString(array $array,string $multipleKeyString,string $delimiter):array{
        
        $eval = "\$return = \$array['".implode("']['",explode($delimiter,$multipleKeyString))."'];";
        //deb($eval);
        eval($eval);
        return $return;
    }
    
    static function arrayShow($array, string $arrayName = '', string $continuacao = '', int $level = 1)
    {
        $return = [];
        
        // array name
        if ($arrayName != '' && $level == 1) {
            $pre = "\$$arrayName [";
            $pos = "]";
        } else {
            $pre = "$continuacao [";
            $pos = "]";
        }
        
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                // deb($v,0);
                $termo = $pre . $k . $pos;
                $return[] = self::arrayShow($v, $k, $termo, ++ $level);
            }
        } else {
            // $termo = $pre.$arrayName.$pos;
            return "$continuacao = \"$array\"<br/>";
        }
        
        return implode(chr(10), $return);
    }
    
    /**
     * Aplica a funcao informada em todas as celulas do array
     * @param string $functionName
     * @param array $array
     * @return array
     */
    /*static function __call($functionName, array $array):array{
        if(function_exists($functionName)){
            foreach ($array as &$v) {
                if(is_array($v)){
                    $v = self::$functionName($v);
                }else{
                    $v = $functionName($v);
                }
            }
        }        
        return $array;
    }/**/
    
    static function strtolower($array)
    {
        foreach ($array as &$v) {
            if(is_array($v)){
                $v = self::strtolower($v);
            }else{
                $v = strtolower($v);
            }
            
        }
        return $array;
    }
    static function utf8_encode($array)
    {
        foreach ($array as &$v) {
            if(is_array($v)){
                $v = self::utf8_encode($v);
            }else{
                $v = utf8_encode($v);
            }
            
        }
        return $array;
    }
    static function utf8_decode($array)
    {
        foreach ($array as &$v) {
            if(is_array($v)){
                $v = self::utf8_decode($v);
            }else{
                $v = utf8_decode($v);
            }
            
        }
        return $array;
    }
    
    static function merge(array $array1, array $array2):array{
        
        $return =  [];
        $args = func_get_args();   
        //deb($args);
        foreach ($args as $array){
            
            foreach ($array as $argKey=>$argValue){
                if(isset($return[$argKey])){
                    $previousSavedValue = $return[$argKey];
                    $actualValue = $argValue;
                    if($previousSavedValue!=$actualValue){
                        throw new Exception("Ocorreu uma inconssistência na mesclagem de arrays. Existem conteúdos diferentes para a mesma chave ($argKey => '$previousSavedValue' | '$actualValue' ).");
                    }
                }
                $return[$argKey]=$argValue;
            }            
        }
        return $return;
    }

}