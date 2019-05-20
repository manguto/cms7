<?php
namespace manguto\cms5\lib\database\repository;

use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\model\Model_Helper;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\database\Database; 
use manguto\cms5\lib\Logs;

class Repository implements Database
{

    // pasta onde serao disponibilizados os arquivos de dados
    private const dir = 'repository';

    private $tablename;

    private $filename;

    private $table = [];

    private $length = false;

    private $lastInsertId = false;

    public function __construct($tablename)
    {
        $this->tablename = $tablename;
        $this->filename = $this->getFilename();
        $this->table = $this->getTable();
        $this->length = $this->length();
        $this->lastInsertId = $this->getLastInsertId();
        //deb($this);
    }

    public function save(array $parameters = [])
    {
        //deb($parameters);
        { // definicao de id no caso de novos registros
            {//id do modelo
                $id = intval($parameters['id']['value']);
                //deb($id);
            }
            {//caso uma insercao definicao valor novo
                if ($id == 0) {
                    {//INSERCAO
                        $id = $this->lastInsertId + 1;
                        $parameters['id']['value'] = $id;
                    }
                    
                }else if($id<0){
                    {//DELECAO
                        unset($this->table[abs($id)]);
                    }
                }
                //deb($id,0);
            }
        }
        { // definicao dos atributos na linha da tabela
            foreach ($parameters as $parameterName => $parameterInfo) {
                $this->table[$id][$parameterName] = strval($parameterInfo['value']);
            }
        }
        { // salvamento
            $this->setTable();
        }
        // deb($this->table);
    }

    public function length(): int
    {
        $length = 0;
        foreach ($this->table as $row){
            $id = intval($row['id']);
            if($id>0){
                $length++;
            }
        }
        return $length;
    }
    
    /**
     * substituicao dos parametros na query
     * @param string $rawQuery
     * @param array $parameters
     * @return string
     */
    private function select_getConditions(string $rawQuery, array $parameters):string{
        $query = $rawQuery;
        //deb($parameters);
        foreach ($parameters as $key=>$info){
            //deb($key,0); deb($info);
            {
                $value = $info['value'];
                //$data_type = $info['data_type'];
                //$length = $info['length'];
            }
            $query = str_replace('{'.$key.'}', "\"$value\"", $query);
        }
        return $query;
    }
    
    /**
     * ajustes em alguns operadores da "query"
     * @param string $conditions
     * @return string
     */
    private function select_conditionsFix(string $conditions):string
    {
        
        // troca de aspas simples por duplas
        {
            $conditions = str_replace("'", '"', $conditions);
            $conditions = str_replace('""', '"', $conditions);
        }        
                
        { // excecao quando da utilizacao de "=" ao inves de "=="
            $conditions = str_replace('==', '=', $conditions);
            $conditions = str_replace('=', '==', $conditions);
            $conditions = str_replace('<==', '<=', $conditions);
            $conditions = str_replace('>==', '>=', $conditions); /* */
        }
        
        // deb($conditions);
        return $conditions;
    }
    
    /**
     * estruturacao do formato final da condicao
     * @param string $conditions
     * @return string
     */
    private function select_conditionsStruct(string $conditions):string
    {   
        if ($conditions == '') {
            $conditions = "\$approved = \$id>0;";
        } else {
            $conditions = "\$approved = \$id>0 && ( $conditions );";
        }
        // deb($conditions);
        return $conditions;
    }

    public function select(string $rawQuery='', array $parameters = []): array
    {   
        //query final
        $conditions = $this->select_getConditions($rawQuery, $parameters);        
        $conditions = $this->select_conditionsFix($conditions);        
        $conditions = $this->select_conditionsStruct($conditions);
        //deb($conditions,0);
        
        Logs::set('info','Query: '.$conditions,$parameters);
        
        $table = $this->getTable();
        //deb($table);
        
        foreach ($table as &$row) {
            //deb($row);            
            extract($row);
            
            //<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<
            $approved = false;
            {
                eval($conditions);            
            }
            //<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<
            //<<<<<<<<<<<<<<<<<<<<<<<
            //deb($approved,0);
            if($approved==false){
                unset($table[abs($id)]);
            }
        }
        //deb($table);
        return $table;
    }

    public function OFF_select(string $rawQuery='', array $parameters = []): array
    {
        $table = $this->table;
        foreach ($table as $id => $row) {
            foreach ($parameters as $parameterName => $parameterINFO) {
                $parameterValue = $parameterINFO['value'];
                if (isset($row[$parameterName])) {
                    if (strval($row[$parameterName]) !== strval($parameterValue)) {
                        unset($table[$id]);
                    }
                } else {
                    throw new Exception("Não foi possível realizar a busca solicitada. O parâmetro '$parameterName' não encontrado na tabela '$this->tablename'. Verifique e tente novamente.");
                }
            }
        }
        return $table;
    }

    public function getLastInsertId(): int
    {
        $table = $this->table;

        if (is_array($table) && sizeof($this->table) > 0) {
            $table_last = array_pop($table);
            // deb($table_last);
            $lastInsertId = abs($table_last['id']);
        } else {
            $lastInsertId = 0;
        }

        return $lastInsertId;
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function getFilename()
    {
        return self::dir . DIRECTORY_SEPARATOR . "$this->tablename.csv";
    }

    /**
     * obter o conteudo da tabela em csv
     *
     * @param string $repositoryname
     * @return string
     */
    private function getTable(): array
    {
        // obtencao do conteudo
        $repositoryCSV = Arquivos::obterConteudo($this->filename, false);
        // debc($repositoryCSV);

        // obtencao ou inicializacao
        $repositoryCSV = $repositoryCSV == false ? $this->tableInit() : $repositoryCSV;
        // debc($repositoryCSV);

        // transformar codificacao do texto
        $repositoryCSV = utf8_encode($repositoryCSV);
        // debc($repositoryCSV);

        // conversao do conteudo em csv para array
        $table = RepositoryCSV::CSVToArray($repositoryCSV);
        // debc($repository);

        //ordenacao pelo id virtual
        $table = $this->sortTable($table);   
        //deb($table);

        return $table;
    }
    
    /**
     * ordenaca o array informado pelo valor abs(id)
     * de forma que os registros deletados permanecam 
     * no mesmo local de quando estavam ativos
     * @param array $table
     * @return array
     */
    private function sortTable(array $table):array{
        $sortedTable = [];
        {//ajuste ordenacao e filtragem de registros removidos virtualmente
            foreach ($table as $line){
                $sortedTable[abs($line['id'])] = $line;
            }
        }
        ksort($sortedTable);
        return $sortedTable;
    }
    
    
    /**
     * cria arquivo CSV caso nao exista
     */
    private function tableInit()
    {
        $className = Model_Helper::getObjectClassname($this->tablename);
        $tempObject = new $className();
        $data = $tempObject->getData(false, true);
        $data = array_keys($data);
        // deb($data);
        $titles = implode(RepositoryCSV::valuesDelimiter, $data) . chr(10);
        //deb($titles);
        Arquivos::escreverConteudo($this->filename, $titles);
    }

    /**
     * converte o array em csv e o salva
     *
     * @param string $repositoryname
     * @param string $repositoryARRAY
     */
    private function setTable()
    {
        //array sort
        $table = $this->sortTable($this->table);
        
        // array - csv
        $repositoryCSV = RepositoryCSV::ArrayToCSV($table);
        // deb($repositoryCSV);

        // utf8 decode
        $repositoryCSV = utf8_decode($repositoryCSV);
        // deb($repositoryCSV);

        // salvar arquivo
        Arquivos::escreverConteudo($this->getFilename(), $repositoryCSV);
    }
}

?>