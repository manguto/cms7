<?php
namespace manguto\cms5\lib\database\mysql\pdo;

use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\Logs;
use manguto\cms5\lib\model\ModelAttribute;

trait ModelMysqlPDO
{

    private function load()
    {
        { // params
            $tablename = $this->GetTablename();
            // deb($tablename);
            $id = $this->getId();
            // deb($id);
        }

        $object_array = self::search("SELECT * FROM $tablename WHERE id=:ID", [
            ':ID' => $id
        ]);

        $registerAmount = sizeof($object_array);
        // deb($registerAmount);
        if ($registerAmount == 0) {
            throw new Exception("Não foi encontrado nenhum registro para identificador ($id) na tabela '$tablename'.");
        } elseif ($registerAmount > 1) {
            throw new Exception("Forma encontrados mais de um registro ($registerAmount) com o mesmo identificador ($id) na tabela '$tablename'.");
        }

        // obter o primeiro registro obtido
        $object = array_shift($object_array);
        // deb($object);

        $ModelAttribute = $object->GetData(true, true);
        // deb($ModelAttribute);

        // definir dados no objeto
        // $this->SetData($data);
        $this->SetAttributes($ModelAttribute, false); /* */
    }

    /**
     * procedimentos a serem realizados
     * antes do salvamento propriamente
     * dito (update info, etc.)
     */
    private function save_prepareTo()
    {

        // atualizacao do datahora da atualizacao
        $this->setUpdate___datetime(date('Y-m-d H:i:s'));

        // atualizacao do usuario autor da atulizacao
        $this->setUpdate___user_id(User::getSessionUserDirectAttribute('id'));
    }

    public function save()
    {
        { // verificacao/ajuste antes do salvamento
            $this->save_prepareTo();
        }

        {
            $tablename = $this->GetTablename();
            // deb($tablename);
            $id = $this->getId();
        }
        {
            $attributes = $this->GetData(false, true);
            {
                $columns = [];
                $values = [];
                $column_value_s = [];
                foreach ($attributes as $attribute) {
                    {
                        $name = $attribute->getName();
                        {
                            $value = $attribute->getValue();
                            $value = $attribute->checkQuotesWrap() ? "'$value'" : $value;
                        }
                    }
                    // ----------------------------------------------------------------- general
                    {
                        if ($name == 'id') {
                            continue;
                        }
                    }
                    // ----------------------------------------------------------------- insert
                    $columns[] = "$name";
                    $values[] = ":$name";
                    // ----------------------------------------------------------------- update
                    $column_value_s[] = "$name=:$name";
                }

                $columns = implode(', ', $columns);
                $values = implode(', ', $values);
                $column_value_s = implode(', ', $column_value_s);
            }
        }
        {
            if ($id == 0) {
                $query = " INSERT INTO $tablename ($columns) VALUES ($values)";
                $parameters = $this->getMysqlPDOParameters(['id']);
            } else {
                $query = " UPDATE $tablename SET $column_value_s WHERE id=:id ";
                $parameters = $this->getMysqlPDOParameters();
            }
            //deb($query,0); deb($parameters);
        }
        
        {
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            $mysql_pdo = new MysqlPDO();
            $mysql_pdo->query($query, $parameters);
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        }
        {//definicao do id do objeto quando da sua criacao
            if ($id == 0) {
                $lastInsertedId = $mysql_pdo->getLastInsertedId();
                $this->setId($lastInsertedId);
            }
        }
    }

    /**
     * Retorna uma listagem com os dados do atributos (parametros para o PDO) no formato exigido pelo metodo PDO->bindParam();
     * @param array $exceptions - nomes dos parametros que nao devem estar presentes na listagem
     * @return array
     */
    protected function getMysqlPDOParameters(array $exceptions=[],bool $exclude=true):array
    {
        $parameters = [];
        $attributes = $this->GetData(false, true);
        foreach ($attributes as $attribute) {

            $name = $attribute->getName();
            
            //verificacao se algum parametro deve ser removido ou exibido
            if($exclude){
                if(in_array($name, $exceptions)){
                    continue;
                }   
            }else{
                if(!in_array($name, $exceptions)){
                    continue;
                }
            }
                     
            $parameters[":$name"]['value'] = $attribute->getValue();
            $parameters[":$name"]['data_type'] = $this->getMysqlPDOParameter__data_type($attribute->getType());
            $parameters[":$name"]['length'] = $attribute->getLength();
        }
        return $parameters;
    }

    private function getMysqlPDOParameter__data_type($modelAttributeType)
    {

        /**
         * +++++++++++++++++++++++++++++++++++++++++++++++ ModelAttribute::
         * const TYPE_CHAR = 'char';
         * const TYPE_VARCHAR = 'varchar';
         * const TYPE_TEXT = 'text';
         * const TYPE_INT = 'int';
         * const TYPE_FLOAT = 'float';
         * const TYPE_DATE = 'date';
         * const TYPE_DATETIME = 'datetime';
         * const TYPE_TIMESTAMP = 'timestamp';
         * const TYPE_TIME = 'time';
         * const TYPE_BOOLEAN = 'boolean';
         */

        /**
         * ++++++++++++++++++++++++++++++++++++++++++++++++ PDO::
         * const PARAM_BOOL = 5;
         * const PARAM_NULL = 0;
         * const PARAM_INT = 1;
         * const PARAM_STR = 2;
         * const PARAM_LOB = 3;
         * const PARAM_STMT = 4;
         * const PARAM_INPUT_OUTPUT = 2147483648;
         * const PARAM_EVT_ALLOC = 0;
         * const PARAM_EVT_FREE = 1;
         * const PARAM_EVT_EXEC_PRE = 2;
         * const PARAM_EVT_EXEC_POST = 3;
         * const PARAM_EVT_FETCH_PRE = 4;
         * const PARAM_EVT_FETCH_POST = 5;
         * const PARAM_EVT_NORMALIZE = 6;
         */
        
        switch ($modelAttributeType) {
            case ModelAttribute::TYPE_BOOLEAN:
                $data_type = \PDO::PARAM_BOOL;
                break;

            case ModelAttribute::TYPE_INT:
                $data_type = \PDO::PARAM_INT;
                break;

            default:
                $data_type = \PDO::PARAM_STR;
                break;
        }
        return $data_type;
    }

    public function OFF_save()
    {
        { // verificacao/ajuste antes do salvamento
            $this->save_prepareTo();
        }

        {
            $tablename = $this->GetTablename();
            // deb($tablename);
            $id = $this->getId();
        }
        {
            $attributes = $this->GetData(false, true);
            {
                $columns = [];
                $values = [];
                $column_value_s = [];
                foreach ($attributes as $attribute) {
                    {
                        $name = $attribute->getName();
                        {
                            $value = $attribute->getValue();
                            $value = $attribute->checkQuotesWrap() ? "'$value'" : $value;
                        }
                    }
                    {
                        if ($name == 'id') {
                            continue;
                        }
                    }
                    // ----------------------------------------------------------------- insert
                    $columns[] = "$name";
                    $values[] = "$value";
                    // ----------------------------------------------------------------- update
                    $column_value_s[] = "$name=$value";
                }

                $columns = implode(', ', $columns);
                $values = implode(', ', $values);
                $column_value_s = implode(', ', $column_value_s);
            }
        }
        {
            if ($id == 0) {
                $query = " INSERT INTO $tablename ($columns) VALUES ($values)";
                $params = [];
            } else {
                $query = " UPDATE $tablename SET $column_value_s WHERE id=:ID ";
                $params = [
                    ':ID' => $id
                ];
            }
            // deb($query,0);
        }

        {
            $mysql_pdo = new MysqlPDO();

            $mysql_pdo->query($query, $params);

            if ($id == 0) {
                $lastInsertedId = $mysql_pdo->getLastInsertedId();
                // deb($lastInsertedId,0);
                $this->setId($lastInsertedId);
            }
        }
    }

    public function delete()
    {
        {
            $tablename = $this->GetTablename();
            // deb($tablename);
            $id = $this->getId();
            // deb($id);
        }
        {
            $mysql_pdo = new MysqlPDO();
            $parameters = $this->getMysqlPDOParameters(['id'],false);
            //deb($parameters);
            $mysql_pdo->query("DELETE FROM $tablename WHERE id=:id", $parameters);
        }
    }

    /**
     * obtem uma lista de objetos do modelo em questao
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public static function search(string $query = '', array $params = []): array
    {
        Logs::set(Logs::TYPE_NOTICE, $query, $params);

        $return = [];

        { // parametros
            $called_class = get_called_class();
        }

        { // query verificacao
            if ($query == '') {
                { // instanciacao de um objeto para obtencao do nome da tabela
                    $class_sample = new $called_class();
                }
                $query = "SELECT * FROM " . $class_sample->getTablename() . " WHERE 1";
            }
        }

        $mysql = new MysqlPDO();
        // deb($query,0);
        $register_array = $mysql->select($query, $params);
        // deb($register_array);

        // Logs::set(Logs::TYPE_NOTICE,"Encontrado(s) '".count($register_array)."' registro(s).");

        foreach ($register_array as $register) {
            { // deb($register);
                $object = new $called_class();
                $object->SetData($register);
            }
            $return[$object->getId()] = $object;
        }
        return $return;
    }

    /**
     * obter a quantidade de itens de uma determinada tabela com base em uma query eventualmente parametrizada
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    static function getTableLength(string $query, array $params = []): int
    {
        return MysqlPDO::getTableLength($query, $params);
    }

    public function LoadReferences()
    {}
}

?>