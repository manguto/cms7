<?php
namespace manguto\cms5\mvc\view;

class ViewSite extends View
{

    static function load(string $tplName, array $parameters = [], bool $toString = false)
    {
        return self::PageSite($tplName, $parameters, $toString);
    }
}





