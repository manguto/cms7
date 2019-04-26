<?php
namespace manguto\cms5\lib\database;

use manguto\cms5\lib\Exception;

class Database
{

    /**
     * obtem a quantidade de registros para um determinado repositorio e condicoes
     *
     * @param string $repositoryname
     * @param string $condition
     * @return int
     */
    static function getTableLength(string $tablename, string $condition = ''): int
    {
        // $repository = self::getRepository($tablename, $condition);
        // deb($repository);
        $repositoryLength = false;
        throw new Exception();
        return $repositoryLength;
    }
}

?>