<?php
namespace manguto\cms5\mvc\view\site;

use manguto\cms5\mvc\view\ViewSite;

class ViewLogin extends ViewSite
{
    
    static function get_login()
    {   
        self::PageSite("login");
    }
}