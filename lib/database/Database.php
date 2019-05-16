<?php

namespace manguto\cms5\lib\database;

interface Database{

    function getLastInsertId():int;
    
    function select(string $rawQuery='',array $parameters=[]):array;
    
    
}