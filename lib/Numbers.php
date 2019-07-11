<?php

namespace manguto\cms5\lib;



class Numbers
{
    static function str_pad_left($numero,$digitos=2){
        return str_pad($numero, $digitos,'0',STR_PAD_LEFT);
        
    }
    
    static function isIntOrFloat(string $number):bool{        
        $number = str_replace(' ', '', $number);
        $number = str_replace('.', '', $number);
        $number = str_replace(',', '', $number);
        $number = str_replace('-', '', $number);
        if(ctype_digit($number)){
            return true;
        }else{
            return false;
        }
    }
    
    static function isInt(string $number):bool{
        $number = str_replace(' ', '', $number);
        $number = str_replace('-', '', $number);
        if(ctype_digit($number)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * verifica se um valor (string, int, float) é a representacao de um INTEIRO
     * @param $text
     * @return bool
     */
    static function is_numeric_int($text):bool{        
        if(is_numeric($text) && (intval($text)==floatval($text))){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * transforma um valor em float BR em float US (())
     * @param string $value
     * @return mixed
     */
    static function stringBRToFloat(string $value){
        //$hasPoint = strpos($value, '.');
        //$hasComma = strpos($value, ',');        
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return $value;
    }
    
}


