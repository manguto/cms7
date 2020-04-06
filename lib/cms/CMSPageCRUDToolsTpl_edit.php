<?php
namespace manguto\cms7\lib\cms;

class CMSPageCRUDToolsTpl_edit
{

    static function ContentReplaces(string $modelname, string $content)
    {
        
        { // basic
            $content = str_replace('Zzz', ucfirst($modelname), $content);
            $content = str_replace('zzz', $modelname, $content);
        }       
        
        return $content;
    }    
    
}

?>