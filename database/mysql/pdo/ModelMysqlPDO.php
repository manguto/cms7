<?php
namespace manguto\cms7\database\mysql\pdo;

use manguto\cms7\libraries\Exception;
use manguto\cms7\model\ModelAttribute;
use manguto\cms7\model\Model;
use application\core\Access;


trait ModelMysqlPDO
{

    /**
     * Caso os dados do modelo em questao pertencam
     * a outro banco de dados (externo), SOBRECARREGAR
     * este método na classe do modelo e informar os
     * dados de conexão no mesmo.
     *
     * @return string[]
     */
    private function DatabaseInfo(): array
    {
        return [
            'dbhost' => '',
            'dbuser' => '',
            'dbpass' => '',
            'dbname' => '',
            'charset' => ''
        ];
    }

    /**
     * Conexao com o banco de dados, conforme os dados
     * padrao ou informados no modelo a utiliza-lo
     *
     * @return MysqlPDO
     */
    private function NewMysqlPDO():MysqlPDO
    {
        $databaseInfo = $this->DatabaseInfo();
        // deb($databaseInfo);
        $dbhost = $databaseInfo['dbhost'];
        $dbuser = $databaseInfo['dbuser'];
        $dbpass = $databaseInfo['dbpass'];
        $dbname = $databaseInfo['dbname'];
        $charset = $databaseInfo['charset'];

        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        $mysql_pdo = new MysqlPDO($dbhost, $dbuser, $dbpass, $dbname, $charset);
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

        return $mysql_pdo;
    }

    public function save()
    {
        {
            // validacao de dados (caso necessaria)
            $this->CheckDataIntegrity();
        }
        
        { // verificacao/ajuste antes do salvamento
            $id = $this->getId();
            // deb($id,0);

            if ($id == 0) {
                // atualizacao do datahora da atualizacao
                $this->setins_dtm(date(Model::dtmFormat));

                // atualizacao do usuario autor da atulizacao
                $this->setins_uid(Access::getSessionUserAttribute('id'));
            }

            // atualizacao do datahora da atualizacao
            $this->setchg_dtm(date(Model::dtmFormat));

            // atualizacao do usuario autor da atulizacao
            $this->setchg_uid(Access::getSessionUserAttribute('id'));
        }

        {
            $tablename = $this->GetTablename();
            // deb($tablename);
            $id = $this->getId();
        }
        {
            $attributes = $this->GET_DATA(false, true);
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
                $parameters = $this->getParameters([], 'id');
            } else {
                $query = " UPDATE $tablename SET $column_value_s WHERE id=:id ";
                $parameters = $this->getParameters();
            }
            // deb($query,0); deb($parameters);
        }

        {
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            $mysql_pdo = $this->NewMysqlPDO();
            $mysql_pdo->query($query, $parameters);
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        }
        { // definicao do id do objeto quando da sua criacao
            if ($id == 0) {
                $lastInsertedId = $mysql_pdo->getLastInsertId();
                $this->setId($lastInsertedId);
            }
        }
    }

    public function load()
    {
        { // params
            $tablename = $this->GetTablename();
            // deb($tablename);
        }
        {
            $id = $this->getId();
            $parameters = $this->getParameters('id');
            // deb($parameters);
        }
        // deb($this);
        $object_array = self::search("SELECT * FROM $tablename WHERE id=:id", $parameters);

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

        $ModelAttribute = $object->GET_DATA(true, true);
        // deb($ModelAttribute);

        // definir dados no objeto
        $this->SetAttributes($ModelAttribute, false);
        
        //verificar dados e corretude de sua estrutura
        $this->CheckDataIntegrity();
    }

    public function delete()
    {
        {
            $tablename = $this->GetTablename();
            // deb($tablename);
            // $id = $this->getId();
            // deb($id);
        }
        {
            $mysql_pdo = $this->NewMysqlPDO();
            $parameters = $this->getParameters('id');
            // deb($parameters);
            $mysql_pdo->query("DELETE FROM $tablename WHERE id=:id", $parameters);
        }
    }

    public function search(string $query = '', array $params = []): array
    {
        
        $return = [];

        { // parametros
            $called_class = get_called_class();
            { // instanciacao de um objeto para obtencao do nome da tabela
                $class_sample = new $called_class();
            }
        }

        { // query verificacao
            if ($query == '') {
                { // ajuste da query para retorno de todos os registros
                    $query = "SELECT * FROM " . $class_sample->getTablename() . " WHERE 1 ";
                }
            } else {
                
                if (strpos(strtoupper($query), 'SELECT') === false) {
                    // ajuste da query para complemento inicial do SQL
                    $query = "SELECT * FROM " . $class_sample->getTablename() . " WHERE " . $query;
                }                
            }
        }

        $mysql_pdo = $this->NewMysqlPDO();
        // deb($query,0);
        $register_array = $mysql_pdo->select($query, $params);
        // deb($register_array);

        // Logger::set(Logger::TYPE_NOTICE,"Encontrado(s) '".count($register_array)."' registro(s).");

        foreach ($register_array as $register) {
            { // deb($register);
                $object = new $called_class();
                $object->SET_DATA($register);
                $object->CheckDataIntegrity();
            }
            $return[$object->getId()] = $object;
        }
        return $return;
    }

    public function length(string $query, array $params = []): int
    {
        return MysqlPDO::length($query, $params);
    }

    public function getParameters($attributes = [], $exceptions = []): array
    {
        { // correcao ajuste do formato dos parametros
            {
                if (is_string($attributes)) {
                    if (trim($attributes) != '') {
                        $attributes = [
                            $attributes
                        ];
                    } else {
                        $attributes = [];
                    }
                }
            }
            {
                if (is_string($exceptions)) {
                    if (trim($exceptions) != '') {
                        $exceptions = [
                            $exceptions
                        ];
                    } else {
                        $exceptions = [];
                    }
                }
            }
        }
        // deb($attributes);

        { // verificacao do tipo de operacao solicitada
            {
                $return_attributes = sizeof($attributes);
                $remove_attributes = sizeof($exceptions);
            }

            if (($return_attributes == 0 && $remove_attributes == 0)) {
                $return_attributes = false;
                $remove_attributes = false;
            } else if ($return_attributes > 0 && $remove_attributes == 0) {
                $return_attributes = true;
                $remove_attributes = false;
            } else if ($return_attributes == 0 && $remove_attributes > 0) {
                $return_attributes = false;
                $remove_attributes = true;
            } else {
                throw new Exception("Não é possível a inclusão e exclusão de parâmetros simultaneamente. Corrija a solicitação e tente novamente.");
            }
        }

        {
            // lista de atributos a serem analisados (processados)
            $all_attributes = $this->GET_DATA(false, true);
            // deb($attributes);
        }

        // deb($attributes,0);
        // deb($exceptions,0);

        $return = [];
        foreach ($all_attributes as $attribute) {

            $name = $attribute->getName();
            // deb($name,0);
            // verificacao se algum parametro deve ser removido ou exibido

            if ($return_attributes && ! in_array($name, $attributes)) {
                continue;
            }
            if ($remove_attributes && in_array($name, $exceptions)) {
                continue;
            }

            $return[":$name"]['value'] = $attribute->getValue();
            $return[":$name"]['data_type'] = $this->getParameter___data_type($attribute->getType());
            $return[":$name"]['length'] = $attribute->getLength();
        }
        // deb($return);
        return $return;
    }

    private function getParameter___data_type($modelAttributeType)
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
}

?>