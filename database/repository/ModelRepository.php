<?php
namespace manguto\cms7\lib\database\repository;

use manguto\cms7\lib\Exception;
use manguto\cms7\lib\cms\CMSAccessManagement;

trait ModelRepository
{

    public function getDatabaseName()
    {
        return 'Repository';
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
                $this->setInsert___datetime(date('Y-m-d H:i:s'));
                
                // atualizacao do usuario autor da atulizacao
                $this->setInsert___user_id(CMSAccessManagement::getSessionUserDirectAttribute('id'));
            }
            
            // atualizacao do datahora da atualizacao
            $this->setUpdate___datetime(date('Y-m-d H:i:s'));
            
            // atualizacao do usuario autor da atulizacao
            $this->setUpdate___user_id(CMSAccessManagement::getSessionUserDirectAttribute('id'));
        }
        {
            // $tablename = $this->GetTablename();
            $id = $this->getId();
        }
        {
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            $repository = new Repository($this->GetClassName());
            // deb($repository);
            $parameters = $this->getParameters();
            // deb($parameters);
            $repository->save($parameters);
            // deb($repository);
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        }
        { // definicao do id do objeto quando da sua criacao
            if ($id == 0) {
                $lastInsertedId = $repository->getLastInsertId();
                // deb($lastInsertedId);
                $this->setId($lastInsertedId);
            }
        }
    }

    /**
     * carrega o objeto com base no ID informado
     *
     * @throws Exception
     */
    public function load()
    {
        { // params
            $tablename = $this->GetTablename();
            // deb($tablename);
        }
        { // identificador
            $id = $this->getId();
            // deb($id);
        }
        // carrega algum objeto caso o id do mesmo tiver sido informado
        if ($id != 0) {
            // deb($this);
            $object_array = self::search(' $id={id} ', $this->getParameters('id'));
            // deb($object_array);
            
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
            
            // verificar dados e corretude de sua estrutura
            $this->CheckDataIntegrity();
        }
    }

    public function delete()
    {
        $id = $this->getId() * - 1;
        $this->setId($id);
        $this->save();
    }

    public function search(string $query = '', array $parameters = []): array
    {
        $return = [];
        //=======================================================================================
        { // analises/verificacoes da query informada
            //deb($query,0);
            [$query,$order_by] = Repository::search_query_parse($query);
            //deb($query,0);
        }        
        //=======================================================================================
        {// obtencao dos registros no respectivo repositorio
            $className = $this->GetClassName(true);
            $repository = new Repository($className);
            $table = $repository->select($query, $parameters);                     
        }
        //=======================================================================================
        {//criacao de cada objeto e insercao no array de retorno            
            foreach ($table as $row) {
                $modelClassName = $this->getClass();
                $registro = new $modelClassName();
                $registro->SET_DATA($row);
                $registro->CheckDataIntegrity();
                $return[$registro->getId()] = $registro;
            }
        }
        //=======================================================================================
        {//ordenacao dos resultados informados            
            $return = Repository::result_order_by($return,$order_by);            
        }
        //=======================================================================================
        return $return;
    }


    public function length(string $query = ''): int
    {
        $return = $this->search($query);
        return sizeof($return);
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
            
            $return[$name]['value'] = $attribute->getValue();
            $return[$name]['data_type'] = $attribute->getType();
            $return[$name]['length'] = $attribute->getLength();
        }
        // deb($return);
        return $return;
    }
}

?>