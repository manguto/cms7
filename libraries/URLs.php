<?php
namespace manguto\cms7\libraries;

class URLs
{

    /**
     * embelezar url para exibicao
     * @param string $url
     * @param bool $HTML_CONTENT
     */
    static function PRETTY_PRINT(string $url,bool $HTML_CONTENT=true) {
        $return = urldecode($url);
        $return = str_replace('?', '?'.chr(10), $return);
        $return = str_replace('&', chr(10).'&', $return);
        $return = str_replace('=', ' = ', $return);        
        {
            $return = str_replace('//', '@@@', $return);
            {
                $return = str_replace('/', '/'.chr(10), $return);
            }
            $return = str_replace('@@@', '//', $return);
        }
        $return = nl2br($return);
        
        return $return;
    }
}

?>
