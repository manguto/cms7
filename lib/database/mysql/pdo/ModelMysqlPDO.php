<?php
namespace manguto\cms5\lib\database\mysql\pdo;

use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\Logs;

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
        //deb($registerAmount);
        if ($registerAmount == 0) {
            throw new Exception("Não foi encontrado nenhum registro para identificador ($id) na tabela '$tablename'.");
        } elseif ($registerAmount > 1) {
            throw new Exception("Forma encontrados mais de um registro ($registerAmount) com o mesmo identificador ($id) na tabela '$tablename'.");
        }

        // obter o primeiro registro obtido
        $object = array_shift($object_array);
        //deb($object);
        
        $ModelAttribute = $object->GetData(true,true);
        //deb($ModelAttribute);
        
        // definir dados no objeto
        //$this->SetData($data);
        $this->SetAttributes($ModelAttribute,false);/**/
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
            
            $mysql_pdo->query($query,$params);
            
            if ($id == 0) {
                $lastInsertedId = $mysql_pdo->getLastInsertedId();
                //deb($lastInsertedId,0);
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
            $mysql_pdo->query("DELETE FROM $tablename WHERE id=:ID",[':ID'=>$id]);
        }
    }

    /**
     * obtem uma lista de objetos do modelo em questao
     * @param string $query
     * @param array $params
     * @return array
     */
    public static function search(string $query = '', array $params = []):array
    {
        Logs::set($query.' - '.implode(',',$params));        
        
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
        //deb($query,0);        
        $register_array = $mysql->select($query, $params);
        //deb($register_array);
        
        foreach ($register_array as $register) {
            {  //deb($register);
                $object = new $called_class();
                $object->SetData($register);
            }
            $return[$object->getId()] = $object;
        }
        return $return;
    }


    /**
     * obter a quantidade de itens de uma determinada tabela com base em uma query eventualmente parametrizada
     * @param string $query
     * @param array $params
     * @return int
     */
    static function getTableLength(string $query,array $params=[]): int
    {        
        return MysqlPDO::getTableLength($query,$params);        
    }
    
    public function LoadReferences()
    {}
}

?>