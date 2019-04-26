<?php
namespace manguto\cms5\lib\database\mysql;

use manguto\cms5\lib\database\Database;

class DatabaseMysql extends Database
{

    static function load(&$model)
    {}

    static function save(&$model)
    {
        {
            $tablename = $model->GetTablename();
            //deb($tablename);
        }
        $sql = " INSERT INTO $tablename (column-names) VALUES (values)";
        deb($sql);
        
    }

    static function delete(&$model)
    {}
}

?>