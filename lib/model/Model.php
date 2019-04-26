<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\Exception;


/**
 * esta classe tera como intuito representar os objetor que precisam ser salvos de alguma forma (banco de dados, xml, json, etc.)
 *
 * @author Marcos
 *        
 */
abstract class Model
{

    private $attributes = [];

    private $attributes_extra = [];
        
    const fundamentalAttributes = ['id'];
    
    public function __construct(int $id = 0)
    {           
        //atributos basicos (fundamentais)
        $this->DefineAttributeFundamentals($id);
                
        //atributos definidos
        $this->CheckAttributesSetted();
        
    }
    
    /**
     * define um atributo para o 
     * @param ModelAttribute $attribute
     */
    protected function DefineAttribute(ModelAttribute $attribute){
        $this->attributes[$attribute->getName()] = $attribute;
    }
    
    /**
     * insere no modelo os atributos fundamentais
     * @param int $id
     */
    private function DefineAttributeFundamentals(int $id){
        
        {//id <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<            
            $attribute = new ModelAttribute('id',false);
            $attribute->setType(ModelAttribute::TYPE_INT);
            $attribute->setvalue($id);
            $this->DefineAttribute($attribute);
        }//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<            
    }    
        
    
    private function CheckAttributesSetted(){
        $attributesSetted = false;
        //deb(self::fundamentalAttributes,0);
        
        $thisAttributes = $this->attributes;
        //deb($thisAttributes);
        
        foreach ($thisAttributes as $attribute){
            //deb($attribute,0);
            if(!in_array($attribute->getName(), self::fundamentalAttributes)){
                $attributesSetted = true;
            }
        }
        //deb($attributesSetted);
        if($attributesSetted==false){
            $class =  get_class($this);
            //deb($class);
            throw new Exception("Os atributos do modelo '$class' não foram definidos. Defina-os e tente novamente!");
        }
    }
   
    /**
     * retorna o modelo em forma de string
     *
     * @return string
     */
    public function __toString()
    {
        $return = array();

        { // attributes

            // atributos do modelo
            $attribute_array = $this->attributes;

            // remocao dos atributos de controle
            $attribute_array = Model_Control::RemoveControlAttributes($attribute_array);

            //percorre todos os atributos para expo-los
            foreach ($attribute_array as $attrName => $attrValue) {

                { // verifica se o atributo eh referencial (ex.: categoria_id, modalidade_ids) para definicao do(s) valor(es) __toString() das referencias
                    
                    {
                        $itsReferenceAttributeSimple = Model_Reference::itsReferenceAttributeSimple($attrName);
                        $itsReferenceAttributeMultiple = Model_Reference::itsReferenceAttributeMultiple($attrName);
                    }
                    
                    if ($itsReferenceAttributeSimple || $itsReferenceAttributeMultiple) {

                        $referencedModelName = Model_Reference::getReferencedModelName($attrName);

                        if (isset($this->attributes_extra[$referencedModelName])) {

                            if ($itsReferenceAttributeSimple) {
                                $attrValue = $this->attributes_extra[$referencedModelName];
                            }
                            
                            if($itsReferenceAttributeMultiple){
                                $attrValueArray = [];
                                foreach ($this->attributes_extra[$referencedModelName] as $referencedModel){
                                    $attrValueArray[] = "$referencedModel";
                                }                    
                                $attrValue = implode(', ', $attrValueArray);
                            }
                        }
                    }
                }

                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                $return[] = "<span title='$attrName'>$attrValue</span>";
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            }
        }
        $return = implode(' | ', $return);
        return $return;
    }

    // magic methods GET & SET
    public function __call(string $methodName, $args)
    {
        // metodo aplicado (solicitado)
        $method_nature = substr($methodName, 0, 3);

        // garimpa o nome do parametro
        $attributeName = strtolower(substr($methodName, 3));

        if ($method_nature == 'get') {

            $this->GET($attributeName);
            
        } elseif ($method_nature == 'set') {

            $this->SET($attributeName, $args);
            
        } else {

            throw new Exception("Método não encontrado ou incorreto ($methodName()).");
        }
    }

    /**
     * define os parametros ou atributos do modelo atraves de um array passado
     * caso estes existam no objeto atual (por segurancao, definir no __construct deste),
     * ou sejam parametros extraordinários (de outro objeto, temporarios, para calculos, etc.).
     *
     * @param array $data
     * @param bool $valueExtra
     */
    public function SetData(array $data = array())
    {
        // deb($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            // evita o carregamento de parametros que nao pertencam ao objeto (outros parametros inseridos no <form> p.ex.)
            if (isset($this->attributes[$key])) {
                $this->attributes[$key]->setValue($value);
            }
        }
    }

    /**
     * obtem o conteudo do modelo em forma de array
     *
     * @param bool $extraIncluded
     * @param bool $ctrlParametersIncluded
     * @param bool $referencesIncluded
     * @param bool $singleLevelArray
     * @return array
     */
    public function GetData(bool $attributes_extra_included, bool $attributes_control_included = false): array
    {
        $data = $this->attributes;

        if ($attributes_extra_included) {

            $attributes_extra = $this->attributes_extra;

            foreach ($attributes_extra as $attrName => $attrValue) {

                if (isset($data[$attrName])) {
                    throw new Exception("Foi encontrado um atributo extra com o mesmo nome de um atributo padrão do modelo ($attrName).");
                }

                $data[$attrName] = $attrValue;
            }
        }

        if (! $attributes_control_included) {
            $data = Model_Control::RemoveControlAttributes($data);
        }

        return $data;
    }

    /**
     * obtem o nome completo da classe deste objeto
     * @return string
     */
    public function GetClass() {
        return get_class($this);        
    }
    /**
     * obtem especificamente o nome da classe deste objeto
     */
    public function GetClassName() {
        $class = $this->GetClass();
        $className = explode(DIRECTORY_SEPARATOR, $class);
        $className = array_pop($className);
        return $className;
    }
    
    public function GetTablename(){        
        $className = $this->GetClassName();        
        $tablename = strtolower($className);
        return $tablename;        
    }
    
    // ##################################################################################################################################
    // ##################################################################################################################################
    // ######################################################## PRIVATE #################################################################
    // ##################################################################################################################################
    // ##################################################################################################################################

    /**
     * GET
     *
     * @param string $fieldname
     * @return mixed|boolean
     */
    private function GET(string $fieldname)
    {
        if (isset($this->attributes[$fieldname])) {

            return $this->attributes[$fieldname];
            
        } else if (isset($this->attributes_extra[$fieldname])) {

            return $this->attributes_extra[$fieldname];
            
        } else {
            throw new Exception("Parâmetro não encontrado/definido ($fieldname).");
        }
    }

    /**
     * SET
     *
     * @param string $fieldname
     * @param array $args
     * @throws Exception
     */
    private function SET(string $fieldname, array $arguments)
    {
        if (isset($arguments[0])) {

            { // obter o tipo de atrubuto a ser definido
                { // valor a ser definido <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    $value = $arguments[0];
                }

                { // tipo de atributo

                    if (! isset($arguments[1])) {
                        $setNature = 'default';
                    } else if (isset($arguments[1]) && $arguments[1] == true) {
                        $setNature = 'extra';
                    } else {
                        throw new Exception("Definição de atributo de modelo incorreta. Tipo não identificado.");
                    }
                }
            }

            { // definicao do atribuito conforme o seu tipo
                if ($setNature == 'default') {

                    if (isset($this->attributes[$fieldname])) {
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        $this->attributes[$fieldname]->setValue($value);
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                    } else {
                        throw new Exception("Parâmetro '$fieldname' não definido para o modelo '" . $this->getModelname() . "'. Defina-o ou utilize-o como um atributo extraordinário.");
                    }
                } else {
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                    $this->attributes_extra[$fieldname] = $value;
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                }
            }
        } else {
            throw new Exception("Ocorreu uma tentativa de definição de atributo de um modelo, onde não foi informado o valor a ser definido (set" . ucfirst($fieldname) . "(?)).");
        }
    }
}

?>