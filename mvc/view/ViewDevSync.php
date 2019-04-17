<?php
namespace manguto\manguto\mvc\view;

class ViewDevSync extends ViewDev
{

    static function get_dev_sync()
    {
        self::PageDev("sync");
    }
    
    static function get_dev_sync_cms_checkbasefiles($comparison)
    {
        //deb($comparison);
        self::PageDev("sync_cms_checkbasefiles",[
            'comparison'=>$comparison
        ]);
    }
    
    static function get_dev_sync_go($parameters)
    {   
        self::PageDev("sync_go",$parameters);
    }
    
    static function post_dev_sync_analyse($filename,$prod_content,$base_content)
    {   
        self::PageDev("sync_analyse",[
            'filename'=>$filename,
            'prod_content'=>$prod_content,
            'base_content'=>$base_content
        ]);
    }
    
    static function post_dev_sync_verificar($a,$b)
    {   
        self::PageDev("sync_verificar",[
            'a'=>$a,
            'b'=>$b
        ]);
    }
    
    
    
    

}