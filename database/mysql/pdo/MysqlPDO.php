<?php
namespace manguto\cms7\lib\database\mysql\pdo;

use manguto\cms7\lib\Exception;
use manguto\cms7\lib\database\Database;

class MysqlPDO extends \PDO implements Database
{

    protected $conn;

    public function __construct($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $charset = 'utf8')
    {
        if ($dbhost == '' && $dbuser == '' && $dbpass == '' && $dbname == '') {
            $dbhost = APP_DATABASE_HOST;
            $dbuser = APP_DATABASE_USER;
            $dbpass = APP_DATABASE_PASS;
            $dbname = APP_DATABASE_NAME;
            $charset = APP_DATABASE_CHARTSET;
        }

        $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=$charset";

        $this->conn = new \PDO($dsn, $dbuser, $dbpass);
    }

    private function setParams(\PDOStatement $statement, $parameters = [])
    {
        foreach ($parameters as $key => $parameter_info) {

            $value = $parameter_info['value'] ?? false;
            $data_type = $parameter_info['data_type'] ?? false;
            $length = $parameter_info['length'] ?? null;

            if ($data_type === false) {                
                throw new Exception("Os parâmetros informados não estão no formato correto (KEY='$key(".gettype($key).")', VALUE='$value'(".gettype($value)."), DATA_TYPE='$data_type'(".gettype($data_type)."), LENGTH='$length'(".gettype($length).") | Query='$statement->queryString').");
            }

            $this->setParam($statement, $key, $value, $data_type, $length);
        }
    }

    private function OFF_setParams($statement, $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->setParam($statement, $key, $value);
        }
    }

    /**
     *
     * @param \PDOStatement $statement
     * @param string $key
     * @param
     *            $value
     * @param int $data_type
     *            - Explicit data type for the parameter using the PDO::PARAM_&#42; constants
     * @param int $length
     *            - Length of the data type. To indicate that a parameter is an OUT parameter from a stored procedure, you must explicitly set the length.
     */
    private function setParam(\PDOStatement $statement, string $key, $value, $data_type = null, $length = null)
    {
        $statement->bindParam($key, $value, $data_type, $length);
    }

    public function query(string $rawQuery,array $parameters = []): \PDOStatement
    {
        $statement = $this->conn->prepare($rawQuery);

        if (sizeof($parameters) > 0) {
            // deb($parameters);
            $this->setParams($statement, $parameters);
        }
        // deb($statement,0);
        // deb($parameters,0);

        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        if(!$statement->execute()){
            $errorInfo = $statement->errorInfo();
            $sqlstate_errorCode = $errorInfo[0];
            $driver_errorCode = $errorInfo[1];
            $driver_errorMsg = $errorInfo[2];
            throw new Exception("$driver_errorMsg | Driver:$driver_errorCode | SqlState: $sqlstate_errorCode.");
        }        
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        return $statement;
    }

    public function select(string $rawQuery='', array $parameters = []): array
    {
        $stmt = $this->query($rawQuery, $parameters);
        // deb($stmt);

        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // deb($return);

        return $return;
    }

    public function getLastInsertId():int
    {
        return $this->conn->lastInsertId();
    }

    // ############################################################################
    /**
     * obter a quantidade de itens de uma determinada tabela com base em uma query eventualmente parametrizada
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    static function length(string $query='', array $params = []): int
    {
        $sql = new MysqlPDO();
        $result = $sql->select($query, $params);
        return sizeof($result);
    }
}

?>