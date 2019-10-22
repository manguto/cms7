<?php
namespace manguto\cms5\mvc\view\dev;


use manguto\cms5\mvc\model\User;
use manguto\cms5\mvc\view\ViewDev;

class ViewUsers extends ViewDev
{

    static function get_dev_users($users)
    {   
        self::PageDev("users", [
            'users' => $users
        ]);
    }
    
    static function get_dev_users_create()
    {
        $adminzoneaccess = User::adminzoneaccess;
        $devzoneaccess = User::devzoneaccess;
        $temp = 'usuario' . date("is");        
        self::PageDev("users-create", get_defined_vars());
    }
    static function get_dev_user($user)
    {   
        self::PageDev("users-view", get_defined_vars());        
    } 
    
    static function get_dev_user_edit($user)
    {   
        $adminzoneaccess = User::adminzoneaccess;
        $devzoneaccess = User::devzoneaccess;
        self::PageDev("users-update", get_defined_vars());
    }
}