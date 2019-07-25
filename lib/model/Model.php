<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Logs;

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

    const fundamentalAttributes = [
        'id',
        'insert___datetime',
        'insert___user_id',
        'update___datetime',
        'update___user_id'
    ];

    protected function checkSetStruct()
    {
        // verifica se 1 ou mais atributos foram definidos na classe filha
        $this->CheckAttributesSetted();
        
        // validacao de dados (caso necessaria)
        $this->VerifyDataAndStructure();

        // ordernar atributos
        $this->SetAttributesOrder();
    }

    /**
     * recebe um array de parametros de atributos e os define para o modelo atual
     *
     * @param array $attributes
     */
    protected function SetAttributes(array $attributes_data = [], bool $checkAttributeName = true)
    {
        // deb($attributes_data);
        Logs::set(Logs::TYPE_INFO, "Definição de ATRIBUTOS: " . implode(', ', array_keys($attributes_data)) . " (verificação do nome do atributo: $checkAttributeName).");

        // recebe uma lista de paramertros e transforma uma lista de model attributes e verifica (caso solicitado) se é permitido
        $attribute_list = ModelAttribute::Convert_ParameterDataArray_to_ModelAttributeArray($attributes_data, $checkAttributeName);

        foreach ($attribute_list as $attribute) {
            // salva atributo no modelo
            $this->SetAttribute($attribute);
        }
    }

    /**
     * obtem um array de parametros de atributos e os define para o modelo atual
     *
     * @param array $attributes
     */
    protected function GetAttributes()
    {
        // deb($attributes_data);
        Logs::set(Logs::TYPE_INFO, "Obtenção de ATRIBUTOS do modelo " . $this->GetClassName());

        $attributes = $this->attributes;

        return $attributes;
    }

    /**
     * obtem o atributo solicitado
     *
     * @param array $attributes
     */
    protected function GetAttribute(string $attributeName)
    {
        // deb($attributes_data);
        Logs::set(Logs::TYPE_INFO, "Obtenção do ATRIBUTO '$attributeName' do modelo " . $this->GetClassName());

        if (isset($this->attributes[$attributeName])) {
            $attribute = $this->attributes[$attributeName];
        } else {
            throw new Exception("Atributo '$attributeName' não definido para o modelo " . $this->GetClassName());
        }

        return $attribute;
    }

    /**
     * insere no modelo os atributos fundamentais
     *
     * @param int $id
     */
    protected function SetFundamentalAttributes(int $id)
    {
        Logs::set(Logs::TYPE_INFO, "Definição dos ATRIBUTOS FUNDAMENTAIS do modelo <b>" . $this->GetClassName() . "</b>.");

        $attributes = [
            'id' => [
                'type' => ModelAttribute::TYPE_INT,
                'value' => $id
            ],
            'insert___datetime' => [
                'type' => ModelAttribute::TYPE_DATETIME,
                'value' => null
            ],
            'insert___user_id' => [
                'type' => ModelAttribute::TYPE_INT,
                'nature' => ModelAttribute::NATURE_REFERENCE_SINGLE,
                'value' => null
            ],
            'update___datetime' => [
                'type' => ModelAttribute::TYPE_DATETIME,
                'value' => null
            ],
            'update___user_id' => [
                'type' => ModelAttribute::TYPE_INT,
                'nature' => ModelAttribute::NATURE_REFERENCE_SINGLE,
                'value' => null
            ]
        ];

        // deb($attributes);
        $this->SetAttributes($attributes, false);
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
    public function __call(string $methodName, $args)
    {
        // metodo aplicado (solicitado)
        $method_nature = substr($methodName, 0, 3);

        // garimpa o nome do parametro
        $attributeName = strtolower(substr($methodName, 3));

        if ($method_nature == 'get') {

            return $this->GET($attributeName);
        } elseif ($method_nature == 'set') {

            $this->SET($attributeName, $args);
        } else {

            throw new Exception("Método não encontrado ou incorreto ($methodName()).");
        }
    }

        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GET & SET
    /**
     * define os parametros ou atributos do modelo atraves de um array passado
     * caso estes existam no objeto atual (por segurancao, definir no __construct deste),
     * ou sejam parametros extraordinários (de outro objeto, temporarios, para calculos, etc.).
     *
     * @param array $data
     * @param bool $dataEmptyValueSet
     *            - utilizar o valor informado mesmo que vazio?
     */
    public function SetData(array $data, bool $dataEmptyValueSet = true)
    {
        // deb($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            // evita o carregamento de parametros que nao pertencam ao objeto (outros parametros inseridos no <form> p.ex.)
            if (isset($this->attributes[$key])) {                
                //define o valor informado quando este for diferente de vazio ou se definido independentemente
                if($value!='' || $dataEmptyValueSet==true){
                    // chamada do metodo de definicao de cada atributo (generico ou especifico se definido)
                    $this->{'set' . ucfirst($key)}($value);
                }                
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
    public function GetData(bool $extra_attributes_included = false, bool $control_attributes_included = false): array
    {
        // ====================================================================================
        $data = $this->attributes;
        // ====================================================================================
        if ($control_attributes_included == false) {
            foreach (array_keys($data) as $attributeName) {
                if (in_array($attributeName, self::fundamentalAttributes)) {
                    unset($data[$attributeName]);
                }
            }
        }

        if ($extra_attributes_included == true) {
            $attributes_extra = $this->attributes_extra;
            foreach ($attributes_extra as $attrName => $attrValue) {
                if (isset($data[$attrName])) {
                    throw new Exception("Foi encontrado um ATRIBUTO EXTRAORDINÁRIO (AE) com o mesmo nome de um ATRIBUTO PADRÃO (AP) do modelo: '$attrName'. Altere o nome do AE e tente novamente.");
                }
                $data[$attrName] = $attrValue;
            }
        }

        return $data;
    }

    // ##################################################################################################################################
    // ##################################################################################################################################
    // ##################################################################################################################################

    /**
     * obtem o nome completo da classe deste objeto
     *
     * @return string
     */
    public function GetClass(): string
    {
        return get_class($this);
    }

    /**
     * obtem especificamente o nome (shortName) da classe deste objeto
     *
     * @return string
     */
    public function GetClassName(): string
    {
        $class = $this->GetClass();
        $className = explode(DIRECTORY_SEPARATOR, $class);
        $className = array_pop($className);
        return $className;
    }

    /**
     * obtem o titulo da estrutura que mantera os dados deste objeto
     *
     * @return string
     */
    public function GetTablename(): string
    {
        $className = $this->GetClassName();
        $tablename = strtolower($className);
        return $tablename;
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

            // percorre todos os atributos para expo-los
            foreach ($attribute_array as $attrName => $attrValue) {

                { // ocultacao de parametros fundamentais (de controle)                    
                    if(in_array($attrName, self::fundamentalAttributes)){
                        continue;
                    }                    
                }
                { // ocultacao de parametros vazios                    
                    if(trim($attrValue)==''){
                        continue;
                    }                    
                }

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
                            if ($itsReferenceAttributeMultiple) {
                                $attrValueArray = [];
                                foreach ($this->attributes_extra[$referencedModelName] as $referencedModel) {
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
                // $return[] = "<span title='$attrName'>$attrValue</span>";
                $return[] = "$attrValue";
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            }
        }
        $return = implode(' | ', $return);
        return $return;
    }

    // ##################################################################################################################################
    // ######################################################## PRIVATE #################################################################
    // ##################################################################################################################################
    // ##################################################################################################################################

    /**
     * define um atributo para o
     *
     * @param ModelAttribute $attribute
     */
    protected function SetAttribute(ModelAttribute $attribute)
    {
        // deb($attribute->getName(),0);
        $this->attributes[$attribute->getName()] = $attribute;
    }

    /**
     * verifica se na classe filha os atributos deste foram definidos!
     *
     * @throws Exception
     */
    private function CheckAttributesSetted()
    {
        Logs::set(Logs::TYPE_INFO, "Verificação quanto a atribuição de todos os atributos do modelo.");

        $attributesSetted = false;
        // deb(self::fundamentalAttributes,0);

        $thisAttributes = $this->attributes;
        // deb($thisAttributes);

        foreach ($thisAttributes as $attribute) {
            // deb($attribute,0);
            if (! in_array($attribute->getName(), self::fundamentalAttributes)) {
                $attributesSetted = true;
            }
        }
        // deb($attributesSetted);
        if ($attributesSetted == false) {
            $class = get_class($this);
            // deb($class);
            throw new Exception("Os atributos do modelo '$class' não foram definidos. Defina-os e tente novamente!");
        }
    }
    
    /*protected function VerifyDataAndStructure(){
        Logs::set(Logs::TYPE_INFO, "Validação de dados e estrutura entre atributos do modelo ($this).");
    }*/

    /**
     * ordernar atributos do objeto de maneira
     * que os atributos fundamentais esteja no
     * inicio desta listagem
     */
    private function SetAttributesOrder()
    {
        Logs::set(Logs::TYPE_INFO, "Ordenação dos atributos do modelo.");

        $attributesOrdered = [];
        foreach (self::fundamentalAttributes as $attributeFundamental) {
            if (isset($this->attributes[$attributeFundamental])) {
                $attributesOrdered[$attributeFundamental] = $this->attributes[$attributeFundamental];
            }
        }

        foreach ($this->attributes as $attributeName => $attribute) {
            if (! isset($attributesOrdered[$attributeName])) {
                $attributesOrdered[$attributeName] = $attribute;
            }
        }
        $this->attributes = $attributesOrdered;
    }

    // ###########################################################################################################################
    /**
     * GET
     *
     * @param string $fieldname
     * @return mixed|boolean
     */
    private function GET(string $attributeName)
    {
        if (isset($this->attributes[$attributeName])) {

            $return = $this->attributes[$attributeName]->getValue();
        } else if (isset($this->attributes_extra[$attributeName])) {

            $return = $this->attributes_extra[$attributeName];
        } else {
            throw new Exception("Parâmetro não encontrado/definido ($attributeName).");
        }

        return $return;
    }

    /**
     * SET
     *
     * @param string $fieldname
     * @param array $args
     * @throws Exception
     */
    private function SET(string $attributeName, array $arguments)
    {
        if (isset($arguments[0])) {

            { // obter o tipo de atributo a ser definido
                { // valor a ser definido <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    $value = $arguments[0];
                }

                { // tipo de atributo

                    if (! isset($arguments[1])) {
                        $setNature = 'default';
                    } else if (isset($arguments[1]) && $arguments[1] == true) {
                        $setNature = 'extra';
                    } else {
                        throw new Exception("Definição de atributo de modelo incorreta ($attributeName). Tipo não identificado.");
                    }
                }
            }

            { // definicao do atribuito conforme o seu tipo
                if ($setNature == 'default') {

                    if (isset($this->attributes[$attributeName])) {
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                        $this->attributes[$attributeName]->setValue($value);
                        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< DEFAULT
                    } else {
                        throw new Exception("Parâmetro '$attributeName' não definido para o modelo '" . $this->GetClass(). "'. Defina-o ou utilize-o como um atributo extraordinário.");
                    }
                } else {
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA                    
                    $this->attributes_extra[$attributeName] = $value;
                    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< EXTRA
                }
            }
        } else {
            throw new Exception("Ocorreu uma tentativa de definição de atributo de um modelo, onde não foi informado o valor a ser definido (set" . ucfirst($attributeName) . "(?)).");
        }
    }

    // ##################################################################################################################################
    // ##################################################################################################################################
    // ##################################################################################################################################
    public function loadReferences()
    {
        Model_Reference::Load($this);
        // deb($this);
    }
    // ##################################################################################################################################
    // ##################################################################################################################################
    // ##################################################################################################################################
}

?>