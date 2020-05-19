<?php
namespace manguto\cms7\libraries;

class Arrays
{

    // ######################################################################################################################################
    /**
     * ordena um array atraves de suas chaves de maneira recursiva
     *
     * @param
     *            $array
     * @param string $sort_flags
     * @return boolean
     */
    static function ksortRecursive(array &$array, string $sort_flags = SORT_REGULAR)
    {
        ksort($array, $sort_flags);
        foreach ($array as &$arr) {
            if (is_array($arr)) {
                ksortRecursive($arr, $sort_flags);
            }
        }
        return true;
    }

    // ######################################################################################################################################
    /**
     * Retorna um array com uma unica dimensao
     *
     * @param array $array
     * @param string $chave_base
     * @param string $delimitador
     * @return array
     */
    static function multiToSingleDimension(array $array, string $chave_base = '', string $delimitador = '__'): array
    {
        $return = array();
        foreach ($array as $chave => $item) {

            if (is_array($item)) {

                $array2 = self::multiToSingleDimension($item, $chave_base . $delimitador, $delimitador);

                foreach ($array2 as $chave2 => $item2) {

                    $return[$chave . $delimitador . $chave2] = $item2;
                }
            } else {

                $return[$chave] = $item;
            }
        }
        return $return;
    }

    // ######################################################################################################################################
    
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
     *
     * @param string $functionName
     * @param array $array
     * @return array
     */
    /*
     * static function __call($functionName, array $array):array{
     * if(function_exists($functionName)){
     * foreach ($array as &$v) {
     * if(is_array($v)){
     * $v = self::$functionName($v);
     * }else{
     * $v = $functionName($v);
     * }
     * }
     * }
     * return $array;
     * }/*
     */
    static function strtolower($array)
    {
        foreach ($array as &$v) {
            if (is_array($v)) {
                $v = self::strtolower($v);
            } else {
                $v = strtolower($v);
            }
        }
        return $array;
    }

    static function utf8_encode($array)
    {
        foreach ($array as &$v) {
            if (is_array($v)) {
                $v = self::utf8_encode($v);
            } else {
                $v = utf8_encode($v);
            }
        }
        return $array;
    }

    static function utf8_decode($array)
    {
        foreach ($array as &$v) {
            if (is_array($v)) {
                $v = self::utf8_decode($v);
            } else {
                $v = utf8_decode($v);
            }
        }
        return $array;
    }

    static function merge(array $array1, array $array2): array
    {
        $return = [];
        $args = func_get_args();
        // deb($args);
        foreach ($args as $array) {

            foreach ($array as $argKey => $argValue) {
                if (isset($return[$argKey])) {
                    $previousSavedValue = $return[$argKey];
                    $actualValue = $argValue;
                    if ($previousSavedValue != $actualValue) {
                        throw new Exception("Ocorreu uma inconssistência na mesclagem de arrays. Existem conteúdos diferentes para a mesma chave ($argKey => '$previousSavedValue' | '$actualValue' ).");
                    }
                }
                $return[$argKey] = $argValue;
            }
        }
        return $return;
    }
}