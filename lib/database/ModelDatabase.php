<?php
namespace manguto\cms5\lib\database;

/**
 * Responsavel pela definicao dos metodos fundamentais aos TRAITS que fazem a interface com as bases de dados (ModelMysqlPDO, ModelRepository, etc.),
 * mas que como não podem ser interfaceados pelos TRAITS, devem ser pelos MODELOS.
 *
 * @author MAGT
 *        
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
    function length(string $query, array $params = []): int;
    
    /**
     * Retorna os parametros do registro no formato exigido pelo banco de dados em questao.
     * Obs.: os parametros da funcao sao filtros. utilizar apenas um deles 
     * ou deixar ambos vazios ("[]") para obtencao de todos os atributos 
     * do registro em questao. 
     *
     * @param array $attributes
     *            - nomes dos parametros a serem exibidos, ou
     * @param array $exceptions
     *            - nomes dos parametros a serem ocultados
     * @return array
     */
    function getParameters($attributes = [], $exceptions = []): array;

   

   
}

?>