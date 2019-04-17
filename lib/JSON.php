<?php
namespace manguto\manguto\lib;

class JSON
{

    static function encode($data,$setHeaderPrintExit=false)
    {
        $json = json_encode($data);
        if ($json == false) {
            throw new Exception(json_last_error_msg());
        } else {
            if($setHeaderPrintExit){
                self::setHeader();
                print $json;
                exit();
            }else{
                return $json;
            }
        }
    }

    static function setHeader()
    {
        header('Content-Type: application/json; charset=utf-8');                
    }
         
}

