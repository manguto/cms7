<?php
namespace manguto\manguto\mvc\view;

class ViewDevUsers extends ViewDev
{

    static function get_dev_users($users)
    {   
        self::PageDev("users", [
            'users' => $users
        ]);
    }
    
    static function get_dev_users_create()
    {
        self::PageDev("users-create", [
            'temp' => 'usuario' . date("is")
        ]);
    }
    static function get_dev_user($user)
    {   
        self::PageDev("users-view", [
            'user' => $user->getData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false)
        ]);        
    }
    
    static function get_dev_user_edit($user)
    {   
        self::PageDev("users-update", [
            'user' => $user->getData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false)
        ]);
    }
}