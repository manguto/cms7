<?php

namespace manguto\manguto\lib;



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
    
}


