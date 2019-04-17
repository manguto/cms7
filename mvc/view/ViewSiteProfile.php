<?php
namespace manguto\cms5\mvc\view;

class ViewSiteProfile extends ViewSite
{

    static function get_profile($user)
    {
        self::PageSite("profile", [
            'user' => $user->getData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false),
            'form_action' => '/profile',
            'link_change_password' => '/profile/change-password'
        ]);
    }

    static function get_profile_change_password()
    {   
        self::PageSite("profile-change-password", [
            'form_action' => '/profile/change-password'
        ]);
    }
}