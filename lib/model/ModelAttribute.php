<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\Exception;

class ModelAttribute
{

    private $name;

    private $type;

    private $value;
    
    private $unit;

    // ---------------------------------------------------------------------------------------------------------------
    const TYPE_CHAR = 'char';

    const TYPE_VARCHAR = 'varchar';

    const TYPE_TEXT = 'text';

    const TYPE_INT = 'int';

    const TYPE_FLOAT = 'float';

    const TYPE_DATE = 'date';

    const TYPE_DATETIME = 'datetime';

    const TYPE_TIMESTAMP = 'timestamp';

    const TYPE_TIME = 'time';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_REFERENCE_SIMPLE = 'reference_simple';

    const TYPE_REFERENCE_MULTIPLE = 'reference_multiple';

    // ########################################################################################################################
    // ########################################################################################################################
    // ########################################################################################################################
    public function __construct($attributeName, bool $checkAttributeName = true)
    {
        { // nome do atributo
            if ($checkAttributeName) {
                $this->checkAttributeName($attributeName);
            }
            $this->setName($attributeName);
        }
        { // tipo padrao
            $this->type = 'varchar';
        }

        { // valor padrao
            $this->value = '';
        }
    }
    // --------------------------------------------------------------------------------------------------------------
    private function setName($name)
    {           
        $this->name=$name;
    }
    // --------------------------------------------------------------------------------------------------------------
    public function setType($type)
    {
        $constant_name = 'TYPE_'.strtoupper($type);
        //deb($constant_name);
        if(!defined("self::$constant_name")){
            throw new Exception("Definição de tipo de atributo incorreto. Não encontrado ('$type').");
        }        
        $this->type=$type;
    }
    // --------------------------------------------------------------------------------------------------------------
    // magic methods GET & SET
    public function __call(string $methodName, $args)
    {
        // metodo aplicado (solicitado)
        $method_nature = substr($methodName, 0, 3);
        
        // garimpa o nome do parametro
        $attributeName = strtolower(substr($methodName, 3));
        
        if ($method_nature == 'get') {
            
            return $this->$attributeName;
            
        } elseif ($method_nature == 'set') {
            
            $this->$attributeName = $args[0];
            
        } else {
            
            throw new Exception("Método não encontrado ou incorreto ($methodName()).");
        }
    }    

    // --------------------------------------------------------------------------------------------------------------
    public function __toString()
    {
        return "$this->value";
    }

    // ########################################################################################################################
    // ########################################################################################################################
    // ########################################################################################################################

    /**
     * verifica se o nome pode ser utilizado como atributo
     *
     * @param string $attributeName
     * @throws Exception
     */
    private function checkAttributeName(string $attributeName)
    {
        if(in_array($attributeName, Model::fundamentalAttributes)){
            throw new Exception("Foi definido um nome de atributo reservado para um atributo fundamental do modelo ($attributeName). Por favor, escolha outro e tente novamente.");
        }
        
        if (Model_Control::itsAControlParameter($attributeName)) {
            throw new Exception("Foi definido um nome de atributo reservado para um atributo de controle do modelo ($attributeName). Por favor, escolha outro e tente novamente.");
        }
    }
}

?>