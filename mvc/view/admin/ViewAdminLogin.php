<?php
namespace manguto\cms5\mvc\view\admin;

class ViewAdminLogin extends ViewAdmin
{    
    static function get_admin_login()
    {
        self::PageAdmin("login");
    }
}