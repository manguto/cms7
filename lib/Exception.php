<?php
namespace manguto\cms5\lib;

class Exception extends \Exception
{

    /**
     * Exibicao de alguma excessao ou mensagem de erro 
     * @param boolean $echo
     * @return string
     */
    public function show($echo = false)
    {
        return self::static_show($this,$echo);
    }
    
    static function static_show($e,$echo=false){
        $type = gettype($e);
        $return = "<pre title='$type'><br/>";
        $return .= '<b>' . nl2br($e->getMessage()) . '</b><br/><br/>';
        $return .= $e->getFile() . ' (' . $e->getLine() . ')<br/><br/>';
        $return .= nl2br($e->getTraceAsString()) . '<br/><br/>';
        if ($echo) {
            echo $return;
        } else {
            return $return;
        }
    }
}

?>