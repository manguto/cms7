<?php
namespace manguto\cms7\database\mysql\mysqli;

use manguto\cms7\libraries\Exception;

trait ModelMysqli
{
    
    public function save(&$model)
    {
        {
            $tablename = $model->GetTablename();
            // deb($tablename);
            $id = $model->getId();
            //deb($id);
        }
        {
            $attributes = $model->GET_DATA(false, true);
            {
                $columns=[];
                $values=[];
                $column_value_s=[];
                foreach ($attributes as $attribute){
                    {
                        $name = $attribute->getName();
                        {
                            $value = $attribute->getValue();
                            $value = $attribute->checkQuotesWrap() ? "'$value'" : $value;
                        }
                        $type = $attribute->getType();
                    }
                    {
                        if($name=='id'){
                            continue;
                        }
                    }
                    //----------------------------------------------------------------- insert
                    $columns[] = "$name";
                    $values[] = "$value";
                    //----------------------------------------------------------------- update
                    $column_value_s[] = "$name=$value";
                }
                
                $columns = implode(', ', $columns);
                $values = implode(', ', $values);
                $column_value_s = implode(', ', $column_value_s);
            }
        }
        {
            if($id==0){
                $query = " INSERT INTO $tablename ($columns) VALUES ($values)";
            }else{
                $query = " UPDATE $tablename SET $column_value_s WHERE id=$id ";
            }
            
            //deb($mysqli);
        }
        {
            $mysqli = new Mysqli();
            $mysqli->query($query);
            if($id==0){
                $model->setId($mysqli->getInsertedId());
            }
        }
    }
    
    public function load(&$model)       
    {
        {
            $tablename = $model->GetTablename();
            // deb($tablename);
            $id = $model->getId();
            //deb($id);
        }
        {
            $query = "SELECT * FROM $tablename WHERE id=$id";            
        }
        {
            $mysqli = new Mysqli();
            $mysqli->query($query);
            $data_array = $mysqli->fetchAll();
            $registerAmount = sizeof($data_array);
            if($registerAmount==0){
                throw new Exception("Não foi encontrado nenhum registro para identificador ($id) na tabela '$tablename'.");
            }elseif ($registerAmount>1){
                throw new Exception("Forma encontrados mais de um registro com o mesmo identificador ($id) na tabela '$tablename'.");
            }
            {
                $data = array_shift($data_array);
                //deb($data);
                $model->SET_DATA($data);
            }
        }    
    }
        
    public function delete(&$model)    
    {
        {
            $tablename = $model->GetTablename();
            // deb($tablename);
            $id = $model->getId();
            //deb($id);
        }
        {
            $query = "DELETE FROM $tablename WHERE id=$id";
        }
        {
            $mysqli = new Mysqli();
            $mysqli->query($query);            
        }    
    }

    public function search(string $query = '', array $parameters = []): array
    {  
    
    }

    public function length(string $query, array $params = []): int
    {
        
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
        
        //deb($attributes,0);
        //deb($exceptions,0);
        
        $return = [];
        foreach ($all_attributes as $attribute) {
            
            $name = $attribute->getName();
            //deb($name,0);
            // verificacao se algum parametro deve ser removido ou exibido
            
            if ($return_attributes && ! in_array($name, $attributes)) {
                continue;
            }
            if ($remove_attributes && in_array($name, $exceptions)) {
                continue;
            }
            
            $return[$name]['value'] = $attribute->getValue();
            $return[$name]['data_type'] = $attribute->getType();
            $return[$name]['length'] = $attribute->getLength();
        }
        //deb($return);
        return $return;
    }
    
    
}

?>