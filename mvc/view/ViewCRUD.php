<?php
namespace manguto\cms5\mvc\view;

class ViewCRUD extends View
{

    static function load(string $tplName, array $parameters = [], bool $toString = false)
    {
        
        return self::PageCRUD($tplName, $parameters, $toString);
    }
}