<?php
namespace manguto\cms7\lib;

class CSSs
{
    static function randomColor($levelMin=0,$levelMax=255){
        return self::randomColorPart($levelMin, $levelMax) . self::randomColorPart($levelMin, $levelMax) . self::randomColorPart($levelMin, $levelMax);
    }    
    static function randomColorPart($levelMin=0,$levelMax=255) {
        return str_pad( dechex( mt_rand( $levelMin, $levelMax ) ), 2, '0', STR_PAD_LEFT);
    }    
}
?>