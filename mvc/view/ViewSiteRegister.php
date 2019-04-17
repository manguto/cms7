<?php
namespace manguto\manguto\mvc\view;

class ViewSiteRegister extends ViewSite
{

    static function get_register($registerFormValues)
    {
        $parameters = [];
        $parameters['registerFormValues'] =  $registerFormValues;
        self::PageSite("register", $parameters);
    }
}