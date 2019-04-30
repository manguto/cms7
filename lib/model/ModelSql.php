<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\database\sql\Sql;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\model\User;

class ModelSql extends Model
{

    public function __construct(int $id = 0)
    {
        parent::__construct($id);

        if ($id != 0) {
            $this->load();
        }
    }

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
        if ($registerAmount == 0) {
            throw new Exception("Não foi encontrado nenhum registro para identificador ($id) na tabela '$tablename'.");
        } elseif ($registerAmount > 1) {
            throw new Exception("Forma encontrados mais de um registro ($registerAmount) com o mesmo identificador ($id) na tabela '$tablename'.");
        }

        // obter o primeiro registro obtido
        $object = array_shift($object_array);
        // deb($data);

        // definir dados no objeto
        $this->SetData($object->GetData(true, true));
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
        $this->setUpdate___user_id(User::getSessionUserDirectParameter('id'));
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
            $sql = new Sql();
            
            $sql->query($query,$params);
            
            if ($id == 0) {
                $lastInsertedId = $sql->getLastInsertedId();
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
            $sql = new Sql();
            $sql->query("DELETE FROM $tablename WHERE id=:ID",[':ID'=>$id]);
        }
    }

    public static function search($query = '', $params = [])
    {
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

        $sql = new Sql();
        $register_array = $sql->select($query, $params);
        foreach ($register_array as $register) {
            { // deb($register,0);
                $object = new $called_class();
                $object->SetData($register);
            }
            $return[$object->getId()] = $object;
        }
        return $return;
    }

    public function LoadReferences()
    {}
}

?>