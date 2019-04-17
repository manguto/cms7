<?php
namespace manguto\manguto\mvc\model;

use manguto\manguto\lib\ServerHelp;
use manguto\manguto\lib\Exception;
use manguto\manguto\lib\Diretorios;
use manguto\manguto\lib\Arquivos;
use manguto\manguto\repository\RepositoryReferences;
use manguto\manguto\lib\Arrays;

class Model
{

    protected $values = [];

    protected $extra = [];

    protected $references = [];
    
    public function __construct(int $id = 0)
    {
        { // PARAMETROS BASE

            { // parametro de identificacao
                $this->values['id'] = $id;
            }
        }
    }

    // magic methods GET & SET
    public function __call(string $name, $args)
    {
        // metodo aplicado (solicitado)
        $method = strtolower(substr($name, 0, 3));

        // garimpa o nome do parametro
        $fieldname = strtolower(substr($name, 3, strlen($name)));
        
        
        if ($method == 'get') {

            $this->GET($fieldname);
            
        } elseif ($method == 'set') {

            $this->SET($fieldname, $args);
            
        } else {
            
            throw new Exception("Método não encontrado ou incorreto ($name()).");
            
        }
    }

    /**
     * GET
     * @param string $fieldname
     * @return mixed|boolean
     */
    private function GET(string $fieldname){
        
        if (isset($this->values[$fieldname])) {
            
            return $this->values[$fieldname];
            
        } else if (isset($this->extra[$fieldname])) {
            
            return $this->extra[$fieldname];
            
        } else if (isset($this->references[$fieldname])) {
            
            $return  = $this->references[$fieldname];
            
            if(is_array($return) && sizeof($return)==1){
                return array_shift($return);
            }else{
                return $return;
            }
            
        } else {
            throw new Exception("Parâmetro não encontrado/definido ($fieldname).");            
        }
        
    }
    
    private function SET(string $fieldname, array $args){
        
        if (! isset($args[0]))
            throw new Exception("Chamada do método de definição sem informação do valor a ser definido (set".ucfirst($fieldname)."('?')).");
            
            {
                { // VALUE <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    $value = $args[0];
                }
                
                { // TYPE of SET <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    
                    if (! isset($args[1])) {
                        $setNature = 'default';
                    } else {
                        if ($args[1] == true) {
                            $setNature = 'extra';
                        } else {
                            $setNature = 'reference';
                        }
                    }
                }
            }
            
            if ($setNature == 'default') {
                
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                if (isset($this->values[$fieldname])) {
                    $this->values[$fieldname] = $value;
                } else {
                    throw new Exception("Parâmetro '$fieldname' não definido. Defina-o no modelo do objeto '" . $this->getModelname() . "' e tente novamente, ou utilize-o como um valor extraordinário.");
                }
            } else if ($setNature == 'extra') {
                
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                $this->extra[$fieldname] = $value;
            } else {
                
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< REFERENCE
                if(!is_array($value)){
                    throw new Exception("Lista de Objetos informada para inserção como referência precisa estar no formato de array de objetos. '".gettype($value)."' informado.");
                }else{
                    //deb($value,0);
                    if(!isset($this->references[$fieldname])){
                        $this->references[$fieldname] = [];
                    }
                    
                    foreach ($value as $v){
                        if(!is_object($v)){
                            throw new Exception("Parâmetro da lista de objetos informado para inserção como referência precisa estar no formato de objetos. '".gettype($value)."' informado.");
                        }
                        $id = $v->getId();
                        $this->references[$fieldname][$id] = $v;
                    }
                    
                }
                
            }
    }
    
    
    /**
     *
     * @param array $data
     */

    /**
     * define os parametros ou atributos do modelo atraves de um array passado
     * caso estes existam no objeto atual (por segurancao, definir no __construct deste),
     * ou sejam parametros extraordinários (de outro objeto, temporarios, para calculos, etc.).
     *
     * @param array $data
     * @param bool $valueExtra
     */
    public function setData(array $data = array())
    {
        // deb($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            
            //evita o carregamento de parametros que nao pertencam ao objeto (outros parametros inseridos no <form> p.ex.)
            if(isset($this->values[$key])){
                $this->{"set" . $key}($value);
            }            
        }
    }
    
    
    /**
     * obtem o conteudo do modelo em forma de array
     * @param bool $extraIncluded
     * @param bool $ctrlParametersIncluded
     * @param bool $referencesIncluded
     * @param bool $singleLevelArray
     * @return array
     */
    public function getData(bool $extraIncluded, bool $ctrlParametersIncluded, bool $referencesIncluded, bool $singleLevelArray): array
    {
        
        
        $data = $this->values;
        
        if(!$ctrlParametersIncluded){
            foreach (array_keys($data) as $parameterName){
                if(RepositoryReferences::ehParametroDeControle($parameterName)){
                    unset($data[$parameterName]);
                }
            }
        }        
        
        if ($extraIncluded) {
            $data = array_merge($data, $this->extra);
        }
        if ($referencesIncluded) {
            $references = $this->references;
            //debc($this->getModelname(),0); deb($references,0);
            foreach ($references as $parameterNameToTheSet=>$referencedObject_array){
                //deb($parameterNameToTheSet,0);
                foreach ($referencedObject_array as $referencedObject){
                    if(!is_object($referencedObject)){
                        $modelname = $this->getModelname();
                        throw new Exception("Parâmetro deve estar como objeto. Array encontrado ($modelname).");
                    }
                    $references[$parameterNameToTheSet][$referencedObject->getId()] = $referencedObject->getData($extraIncluded, $ctrlParametersIncluded, $referencesIncluded, $singleLevelArray);
                }                
            }
            $data = array_merge($data, $references);
        }   
        //deb($singleLevelArray?'sim':'nao',0);
        if($singleLevelArray){           
            $data = Arrays::arrayMultiNivelParaSimples($data);
            //debc($data,0);
        }
        return $data;
    }

    /**
     * retorna o modelo em forma de string
     *
     * @return string
     */
    public function __toString()
    {
        $return = array();

        { // values
            foreach ($this->values as $c => $v) {
                if(RepositoryReferences::ehParametroDeControle($c)) continue;
                $return[] = "<span title='$c'>$v</span>";
            }
        }
        $return = implode(' | ', $return);
        return $return;
    }

    // --------------------------------------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------------------------------------
    /**
     * define o nome do modelo do objeto
     */
    private function setModelname(): void
    {
        $modelname = get_class($this);
        $modelname = ServerHelp::fixds($modelname);
        $modelname = explode(DIRECTORY_SEPARATOR, $modelname);
        $modelname = array_pop($modelname);
        $this->modelname = $modelname;
    }

    /**
     * obtem o nome da classe do repositorio informado
     * para carregamento imediato.
     *
     * @param string $repositoryname
     * @return string
     */
    static function getObjectClassname(string $modelname): string
    {
        $modelname = ucfirst(strtolower($modelname));
        //deb($modelname,0);

        foreach (self::model_class_folders as $model_class_folder) {

            $php_files = Diretorios::obterArquivosPastas($model_class_folder, true, true, false, [
                'php'
            ]);
            // deb($php_files,0);
            foreach ($php_files as $php_file) {
                $nomeClasse = Arquivos::obterNomeArquivo($php_file, false);
                $path = Arquivos::obterCaminho($php_file);
                // deb($nomeClasse,0); deb($modelname);
                if ($nomeClasse == $modelname) {

                    // deb($path);
                    $objectClassname = '\\' . $path . $modelname;
                    $objectClassname = str_replace('/', '\\', $objectClassname);
                    $objectClassname = str_replace('\vendor', '', $objectClassname);
                    // deb($objectClassname,0);
                    return $objectClassname;
                }
            }
        }

        throw new Exception("Classe não encontrada ($modelname).");
    }

    // ==================================================================================================================================================
    // =========================================================================================================================================== START
    // ==================================================================================================================================================
    static function inicializar()
    {
        {
            // ...
        }
    }
}

?>