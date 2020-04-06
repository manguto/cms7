<?php
namespace manguto\cms7\lib\cms;

class CMSPageCRUDToolsControler
{

    static function ContentReplaces(string $modelname, string $content)
    {
        {
            $Modelname = ucfirst($modelname);
        }
        { // basic
            $content = str_replace('Zzz', $Modelname, $content);
            $content = str_replace('zzz', $modelname, $content);
        }

        {
            // ...
        }
        return $content;
    }
}

?>