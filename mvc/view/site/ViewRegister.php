<?php
namespace manguto\cms5\mvc\view\site;

use manguto\cms5\mvc\view\ViewSite;

class ViewRegister extends ViewSite
{

    static function get_register($registerFormValues)
    {
        $parameters = [];
        $parameters['registerFormValues'] =  $registerFormValues;
        self::PageSite("register", $parameters);
    }
}