<?php

namespace manguto\cms7\lib\database;

interface Database{

    function getLastInsertId():int;
    
    function select(string $rawQuery='',array $parameters=[]):array;
    
    
}