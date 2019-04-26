<?php
namespace manguto\cms5\mvc\view\admin;

use manguto\cms5\mvc\view\View;

class ViewAdmin extends View
{

    static function load(string $tplName, array $parameters = [], bool $toString = false)
    {
        
        return self::PageAdmin($tplName, $parameters, $toString);
    }
}