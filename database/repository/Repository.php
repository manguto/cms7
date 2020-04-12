<?php
namespace manguto\cms7\database\repository;

use manguto\cms7\libraries\Arquivos;
use manguto\cms7\model\ModelHelper; 
use manguto\cms7\libraries\Exception; 
use manguto\cms7\database\Database; 
use manguto\cms7\libraries\Diretorios;
use manguto\cms7\libraries\Strings;

class Repository implements Database
{

    // pasta onde serao disponibilizados os arquivos de dados
    const dir = 'repository';

    private $ClassName;

    private $tablename;

    private $filename;

    private $table = [];

    private $length = false;

    private $lastInsertId = false;

    const search_query_keywords = [
        'order_by' => [
            'keyword' => ' ORDER BY ',
            'separator' => ',',
            'ascendent_order' => 'ASC',
            'descendent_order' => 'DESC'
        ]
    ];

    public function __construct($ClassName)
    {
        $this->ClassName = $ClassName;
        $this->tablename = strtolower($ClassName);
        $this->filename = $this->getFilename();
        $this->table = $this->getTable();
        $this->length = $this->length();
        $this->lastInsertId = $this->getLastInsertId();
        // deb($this);
    }

    public function save(array $parameters = [])
    {
        // deb($parameters);
        { // definicao de id no caso de novos registros
            { // id do modelo
                $id = intval($parameters['id']['value']);
                // deb($id);
            }
            { // caso uma insercao definicao valor novo
                if ($id == 0) {
                    { // INSERCAO
                        $id = $this->lastInsertId + 1;
                        $parameters['id']['value'] = $id;
                    }
                } else if ($id < 0) {
                    { // DELECAO
                        unset($this->table[abs($id)]);
                    }
                }
                // deb($id,0);
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
        foreach ($this->table as $row) {
            $id = intval($row['id']);
            if ($id > 0) {
                $length ++;
            }
        }
        return $length;
    }

    /**
     * substituicao dos parametros na query
     *
     * @param string $rawQuery
     * @param array $parameters
     * @return string
     */
    private function select_getConditions(string $rawQuery, array $parameters): string
    {
        $query = $rawQuery;
        // deb($parameters);
        foreach ($parameters as $key => $info) {
            // deb($key,0); deb($info);
            {
                $value = $info['value'];
                // $data_type = $info['data_type'];
                // $length = $info['length'];
            }
            $query = str_replace('{' . $key . '}', "\"$value\"", $query);
        }
        return $query;
    }

    /**
     * ajustes em alguns operadores da "query"
     *
     * @param string $conditions
     * @return string
     */
    private function select_conditionsFix(string $conditions): string
    {
        
        // troca de aspas simples por duplas
        {
            $conditions = str_replace("'", '"', $conditions);
            // $conditions = str_replace('""', '"', $conditions); ERRO!!! (idade=="" => idade=")
        }
        
        { // excecao quando da utilizacao de "=" ao inves de "=="
            $conditions = str_replace('!=', '<>', $conditions);
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
     *
     * @param string $conditions
     * @return string
     */
    private function select_conditionsStruct(string $conditions): string
    {
        if ($conditions == '') {
            $conditions = "\$approved = \$id>0;";
        } else {
            $conditions = "\$approved = \$id>0 && ( $conditions );";
        }
        // deb($conditions);
        return $conditions;
    }

    public function select(string $rawQuery = '', array $parameters = []): array
    {
        // query final
        // deb($rawQuery,0);
        $conditions = $this->select_getConditions($rawQuery, $parameters);
        // deb($conditions,0);
        $conditions = $this->select_conditionsFix($conditions);
        // deb($conditions);
        $conditions = $this->select_conditionsStruct($conditions);
        // deb($conditions,0);
        
        $table = $this->getTable();
        // deb($table);
        
        foreach ($table as &$row) {
            // deb($row,0);
            extract($row);
            
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            $approved = false;
            {
                eval($conditions);
            }
            // deb($approved,0);
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            // deb($approved,0);
            if ($approved == false) {
                unset($table[abs($id)]);
            }
        }
        // deb($table);
        return $table;
    }

    public function OFF_select(string $rawQuery = '', array $parameters = []): array
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
        $tablename = $this->tablename;
        $filename = strtolower($tablename);
        return self::dir . DIRECTORY_SEPARATOR . "$filename.csv";
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
        
        // obtencao ou inicializacao e caso ainda nao exista, cria-o!
        if ($repositoryCSV == false) {
            $repositoryCSV = $this->tableInit();
            // debc($repositoryCSV);
        }
        
        // transformar codificacao do texto
        $repositoryCSV = utf8_encode($repositoryCSV);
        // debc($repositoryCSV);
        
        // conversao do conteudo em csv para array
        $table = RepositoryCSV::CSVToArray($repositoryCSV);
        // debc($repository);
        
        // ordenacao pelo id virtual
        $table = $this->sortTable($table);
        // deb($table);
        
        return $table;
    }

    /**
     * ordenaca o array informado pelo valor abs(id)
     * de forma que os registros deletados permanecam
     * no mesmo local de quando estavam ativos
     *
     * @param array $table
     * @return array
     */
    private function sortTable(array $table): array
    {
        $sortedTable = [];
        { // ajuste ordenacao e filtragem de registros removidos virtualmente
            foreach ($table as $line) {
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
        // deb($this);
        $ClassNameFull = ModelHelper::getObjectClassName_by_ClassName($this->ClassName);
        // deb($ClassNameFull);
        $tempObject = new $ClassNameFull();
        $data = $tempObject->GET_DATA(false, true);
        $data = array_keys($data);
        // deb($data);
        $titles = implode(RepositoryCSV::valuesDelimiter, $data) . chr(10);
        // deb($titles);
        Arquivos::escreverConteudoControlado($this->filename, $titles);
    }

    /**
     * converte o array em csv e o salva
     *
     * @param string $repositoryname
     * @param string $repositoryARRAY
     */
    private function setTable()
    {
        // array sort
        $table = $this->sortTable($this->table);
        
        // array - csv
        $repositoryCSV = RepositoryCSV::ArrayToCSV($table);
        // deb($repositoryCSV);
        
        // utf8 decode
        $repositoryCSV = utf8_decode($repositoryCSV);
        // deb($repositoryCSV);
        
        // salvar arquivo
        Arquivos::escreverConteudoControlado($this->getFilename(), $repositoryCSV);
    }

    /**
     * Analisa / Verifica a query informada quanto as suas funcionalidades,
     * e retorna os eventuais parametros envolvidos / necessarios
     *
     * @param string $query
     * @return array
     */
    static function search_query_parse(string $query): array
    {
        // ---------------------------------------------------------------------
        { // verificacao de palavara chave de ordenacao (order by)
            [
                $query,
                $order_by
            ] = Repository::search_query_parse_orderby($query);
        }
        // ---------------------------------------------------------------------
        $return = [
            $query,
            $order_by
        ];
        // ---------------------------------------------------------------------
        return $return;
    }

    /**
     * Verifica se a query possui uma solicitacao de ordenacao
     *
     * @param string $query
     * @throws Exception
     * @return array
     */
    private static function search_query_parse_orderby(string $query): array
    {
        $order_by = '';
        // ================================================================================
        { // analise do formato da palavra chave utilizado (ORDER BY / order by)
            $search = Repository::search_query_keywords['order_by']['keyword'];
            $search_keyword_uppercase = strpos($query, strtoupper($search)) !== false;
            $search_keyword_lowercase = strpos($query, strtolower($search)) !== false;
            if ($search_keyword_lowercase && $search_keyword_uppercase) {
                throw new Exception("Foram encontrados múltiplos parâmetros de ordenação e um query ('$query').");
            } else if ($search_keyword_uppercase) {
                $search = strtoupper($search);
            } else if ($search_keyword_lowercase) {
                $search = strtolower($search);
            } else {
                $search = false;
            }
            // deb($search,0);
        }
        // ================================================================================
        { // continuacao do processo caso a palavra chave tenha sido encontrada
            if ($search !== false) {
                $query_explode = explode($search, $query);
                $parts = sizeof($query_explode);
                if ($parts != 2) {
                    throw new Exception("Não foi possível verificar os parâmetros para ordenação de uma query ('$query').");
                }
                $query = array_shift($query_explode);
                $order_by = array_pop($query_explode);
            }
        }
        // ================================================================================
        return [
            $query,
            $order_by
        ];
    }

    /**
     * ordena um array de objetos (Models)
     * de acordo com as condicoes informadas
     *
     * @param array $array_messy
     * @param string $conditions
     * @throws Exception
     * @return array
     */
    static function result_order_by(array $array_messy, string $order_by = ''): array
    {
        $order_by = trim($order_by);
        $ASC = Repository::search_query_keywords['order_by']['ascendent_order'];
        $DESC = Repository::search_query_keywords['order_by']['descendent_order'];
        // ======================================================================================================
        if ($order_by != '') {
            // ======================================================================================================
            { // condition analysis                
                $order_by_array = explode(',', $order_by);
                $parameter_info_array = [];
                $sortKeyFormer = [];
                foreach ($order_by_array as $term) {
                    $term = trim($term);
                    $term_array = explode(' ', $term);                    
                    if (sizeof($term_array) == 1) {
                        $parameterName = trim($term_array[0]);
                        $parameterOrder = $ASC;
                    } else if (sizeof($term_array) == 2) {
                        $parameterName = trim($term_array[0]);
                        $parameterOrder = strtoupper(trim($term_array[1]));
                    } else {
                        throw new Exception("Quantidade incorreta de termos para indexação ('$order_by').");
                    }                    
                    if ($parameterOrder != $ASC && $parameterOrder != $DESC) {
                        throw new Exception("Parâmetro da ordem de indexação incorreto ('$parameterOrder' != '$ASC' != '$DESC').");
                    }                    
                    $parameter_info_array[$parameterName] = $parameterOrder;
                }
            }
            // ======================================================================================================
            $return = Repository::result_order_by_indexation($array_messy, $parameter_info_array);
            // ======================================================================================================
            
        }else{
            $return = $array_messy;
        }
        // ======================================================================================================
        
        return $return;
    }

    /**
     * ordena o array informado com base nos parametros informados
     *
     * @param array $array_messy
     * @param array $ordination
     * @return array
     */
    static private function result_order_by_indexation(array $array_messy, array $parameter_info_array): array
    {
        $return = [];
        $DESC = Repository::search_query_keywords['order_by']['descendent_order'];
        // ==================================================================================================
        foreach ($array_messy as $register) {
            { // criacao da chave (que pode possuir multiplos parametros)
                $key = [];
                foreach ($parameter_info_array as $parameterName => $sortType) {
                    {//obtencao do valor com base no tipo do objeto informado
                        if (! is_object($register)) {
                            $parameterValue = $register[$parameterName];
                        } else {
                            $parameterValue = $register->{'get' . $parameterName}();
                        }
                    }                    
                    { // caso a ordenacao do parametro atual seja DECRESCENTE, realiza a inversao dos valores de cada caractere
                        if (strtoupper($sortType) == strtoupper($DESC)) {                            
                            $parameterValue = Strings::str_inverter($parameterValue);
                        }
                    }
                    $key[] = $parameterValue;
                }
                $key = implode('.', $key);
                //deb($key,0);
            }
            $return[$key] = $register;
        }
        // ==================================================================================================
        { // (finalmente) ordena o array com base na chave criada
            ksort($return);
        }
        // ==================================================================================================
        { // substituicao do indice do array para cada registro ('chave' => 'id')            
            foreach ($return as $orderKey => $register) {
                // remove o registro correspondente e desordenado
                unset($return[$orderKey]);
                // insere o registro na ordem correta
                if (! is_object($register)) {
                    $id = $register['id'];
                } else {
                    $id = $register->getId();
                }
                $return[$id] = $register;
            }
            // deb($array);
        }
        // ==================================================================================================
        return $return;
    }

    static function InitializeRepositories($dir = 'sis/model')
    {
        $modelFiles = Diretorios::obterArquivosPastas($dir, false, true, false, [
            'php'
        ]);
        foreach ($modelFiles as $modelFile) {
            // deb($modelFile);
            $conteudo = Arquivos::obterConteudo($modelFile);
            
            $modelRepositoryFile = strpos($conteudo, 'use ModelRepository') !== false;
            // deb($modelFile,0); deb($modelRepositoryFile,0);
            
            if ($modelRepositoryFile) {
                $tablename = strtolower(Arquivos::obterNomeArquivo($modelFile, false));
                // deb($tablename);
                
                // inicializa o modelo repositorial
                $repository = new Repository($tablename);
            }
        }
    }
}

?>