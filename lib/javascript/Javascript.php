<?php

namespace manguto\manguto\lib\javascript;

class Javascript
{
    static function TimeoutDocumentLocation($location,$timeout=100){
        $conteudo_js = "
          setTimeout(function(){
            document.location='$location';
           },$timeout);  
        ";
        $return = self::Tags($conteudo_js);
        return $return;
    }
    
    static function Tags($conteudo_js){
        $return = '<script type="text/javascript">';
        $return .= $conteudo_js;
        $return .= '</script>';
        return $return;
    }
}

