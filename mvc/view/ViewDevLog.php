<?php
namespace manguto\cms5\mvc\view;

class ViewDevLog extends ViewDev
{

    static function get_dev_log($logFiles)
    {   
        self::PageDev("log");
    }
    
    
    static function get_dev_log_data($data,$logs)
    {   
        self::PageDev("log_data",['data'=>$data, 'logs'=>$logs]);
    }
    
    
    static function get_dev_log_completo($logFiles)
    {   
        self::PageDev("log_completo",['logFiles'=>$logFiles]);
    }
    
    
}