<?php
namespace manguto\cms5\lib\database\mysql;

use manguto\cms5\lib\database\Database;
use manguto\cms5\lib\Exception;

class DatabaseMysql extends Database
{

    static function load(&$model)
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
            $mysql = new Mysql();
            $mysql->query($query);
            $data_array = $mysql->fetchAll();
            $registerAmount = sizeof($data_array);
            if($registerAmount==0){
                throw new Exception("Não foi encontrado nenhum registro para identificador ($id) na tabela '$tablename'.");
            }elseif ($registerAmount>1){
                throw new Exception("Forma encontrados mais de um registro com o mesmo identificador ($id) na tabela '$tablename'.");
            }
            {
                $data = array_shift($data_array);
                //deb($data);
                $model->SetData($data);
            }
        }    
    }

    static function save(&$model)
    {   
        {
            $tablename = $model->GetTablename();
            // deb($tablename);
            $id = $model->getId();
            //deb($id);
        }
        {
            $attributes = $model->GetData(false, true);            
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
            
            //deb($sql);
        }
        {
            $mysql = new Mysql();
            $mysql->query($query);
            if($id==0){
                $model->setId($mysql->getInsertedId());
            }
        }        
    }

    static function delete(&$model)
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
            $mysql = new Mysql();
            $mysql->query($query);            
        }    
    }
}

?>