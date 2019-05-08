<?php
namespace manguto\cms5\lib\database\mysql\pdo;

class MysqlPDO extends \PDO
{

    protected $conn;

    public function __construct($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $charset = 'utf8')
    {
        if ($dbhost == '' && $dbuser == '' && $dbpass == '' && $dbname == '') {
            $dbhost = DATABASE_HOST;
            $dbuser = DATABASE_USER;
            $dbpass = DATABASE_PASS;
            $dbname = DATABASE_NAME;
            $charset = DATABASE_CHARTSET;
        }

        $dsn = "mysql:host=$dbhost;dbname=$dbname";

        $this->conn = new \PDO($dsn, $dbuser, $dbpass);
    }
    
    private function setParams($statement, $parameters = [])
    {
        foreach ($parameters as $key => $value) {            
            $this->setParam($statement, $key, $value);
        }
    }    
    
    private function setParam($statement, $key, $value)
    {
        $statement->bindParam($key, $value);
    }
        
    public function query($rawQuery, $parameters = [])
    {
        $stmt = $this->conn->prepare($rawQuery);
        
        if(sizeof($parameters)>0){
            $this->setParams($stmt, $parameters);
        }        
                
        $stmt->execute();
        
        return $stmt;
        
    }
    

    public function select($rawQuery,$parameters=[]):array {
        
        $stmt = $this->query($rawQuery,$parameters);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getLastInsertedId(){
        return $this->conn->lastInsertId();
    }
    
    //############################################################################
    /**
     * obter a quantidade de itens de uma determinada tabela com base em uma query eventualmente parametrizada
     * @param string $query
     * @param array $params
     * @return int
     */
    static function getTableLength(string $query,array $params=[]): int
    {
        $sql = new MysqlPDO();
        $result = $sql->select($query,$params);        
        return sizeof($result);
    }
    
}

?>