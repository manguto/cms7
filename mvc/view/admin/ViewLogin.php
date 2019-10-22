<?php
namespace manguto\cms5\mvc\view\admin;

use manguto\cms5\mvc\view\ViewAdmin;

class ViewLogin extends ViewAdmin
{    
    static function get_admin_login()
    {
        self::PageAdmin("login");
    }
}