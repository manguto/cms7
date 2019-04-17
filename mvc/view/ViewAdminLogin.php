<?php
namespace manguto\cms5\mvc\view;

class ViewAdminLogin extends ViewAdmin
{    
    static function get_admin_login()
    {
        self::PageAdmin("login");
    }
}