<?php
namespace manguto\cms7\lib\database;

/**
 * Metodos fundamentais dos TRAITS que fazem a interface 
 * com as bases de dados (ModelMysqlPDO, ModelRepository, etc.)
 * que devem ser interfaceados pelos MODELOS já que 
 * não podem ser interfaceados pelos TRAITS. *
 * @author MAGT        
 */
interface ModelDatabase
{

    /**
     * salva/atualiza o registro na base de dados
     */
    function save();
    
    /**
     * carrega o registro com as informacoes da base de dados
     */
    function load();
    
    /**
     * remove o registro da base de dados
     */
    function delete();

    
    /**
     * obtem uma lista dos registros da base de dados, conforme query e parametros informados
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    function search(string $query = '', array $params = []): array;
    
    /**
     * obtem a quantidade de registros da base de dados, conforme query e parametros informados
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    function length(string $query): int;
    
    /**
     * Retorna os parametros do registro no formato exigido pelo banco de dados em questao.
     * Obs.: Os parametros desta funcao sao filtros. Utilize apenas um deles ou deixe ambos vazios
     * (para obtencao de todos os atributos do registro em questao). 
     *
     * @param array $attributes | nomes dos parametros a serem exibidos
     * @param array $exceptions | nomes dos parametros a serem ocultados
     * @return array
     */
    function getParameters($attributes = [], $exceptions = []): array;

   

   
}

?>