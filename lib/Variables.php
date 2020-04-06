<?php

namespace manguto\cms7\lib;

class Variables
{
    static function isset($variableName) {
        global $$variableName;
        
        if(isset($variableName)){
            return true;
        }else{
            return false;
        }        
    }
    
    static function issetAndNotEmpty($variableName) {
        global $$variableName;
        
        if(self::isset($variableName) && trim($variableName)!=''){
            return true;
        }else{
            return false;
        }
    }
    
    static function isntset_set(&$variableName,$value) {
        global $$variableName;
        
        if(!self::isset($variableName)){
            return $value;
        }        
    }
    
    static function GET(string $varname,$default='',bool $throwException=false){
        if(isset($_GET[$varname])){            
            $return = $_GET[$varname];
        }else{
            if($throwException){
                throw new Exception("Não foi possível obter o conteúdo da variável \$_GET[$varname]. Variável não definida.");
            }else{
                $return = $default;
            }
        }
        return $return;
    }
    
    static function POST(string $varname,$default='',bool $throwException=false){
        
        if(isset($_POST[$varname])){
            $return = $_POST[$varname];
        }else{
            if($throwException){
                throw new Exception("Não foi possível obter o conteúdo da variável \$_POST[$varname]. Variável não definida.");
            }else{
                $return = $default;
            }
        }        
        return $return;
    }
    
}


