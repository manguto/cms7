<?php
namespace manguto\cms5\mvc\view\admin;

class ViewAdminUsers extends ViewAdmin
{

    static function get_admin_users($users)
    {   
        self::PageAdmin("users", [
            'users' => $users
        ]);
    }
    
    static function get_admin_users_create()
    {
        self::PageAdmin("users-create", [
            'temp' => 'usuario' . date("is")
        ]);
    }
    static function get_admin_user($user)
    {   
        self::PageAdmin("users-view", [
            'user' => $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false)
        ]);        
    }
    
    static function get_admin_user_edit($user)
    {   
        self::PageAdmin("users-update", [
            'user' => $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false)
        ]);
    }
}