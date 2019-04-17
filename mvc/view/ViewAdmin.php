<?php
namespace manguto\manguto\mvc\view;

class ViewAdmin extends View
{

    static function load(string $tplName, array $parameters = [], bool $toString = false)
    {
        
        return self::PageAdmin($tplName, $parameters, $toString);
    }
}