<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\Exception;  

class Model_Reference
{

    // indicador de referencia a outro objeto
    const simple_reference_indicator_end = '_id';

    // indicador de referencia multipla a outros objetos
    const multiple_reference_indicator_end = '_ids';

    // separador de apelido de objeto referenciado quando da criacao do nome da coluna para o repositorio
    const reference_nick_splitter = '___';

    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    
    /**
     * 
     * @param Model $model_object
     */
    
    /**
     * Carregar as possiveis referencias do modelo informado.
     * @param Model $model_object
     * @param bool $inArray - referencias devem ser inseridas em uma array mesmo que unicas
     */
    static function Load(Model &$model_object,bool $inArray=true)
    {                   
        //$attributes = $model_object->GET_DATA($attribute_extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = false, $singleLevelArray = false);
        $attributes = $model_object->GET_DATA();

        //deb($attributes);
        foreach ($attributes as $attributeName => $attributeValue_possible_id_or_ids) {
            //deb($attributeName,0); deb($attributeValue_possible_id_or_ids);
                        
            // caso o array nao possua nenhum conteudo FALSE, ou seja, é um parametro referencial (ex.: pessoa_id, responsavel__pessoa_id, categoria_id)
            $itsReferenceAttributeSingle = self::itsReferenceAttributeSingle($attributeName);            
            $itsReferenceAttributeMultiple = self::itsReferenceAttributeMultiple($attributeName);
            // deb($ehParametroReferencial,0); deb($itsReferenceAttributeMultiple,0);
            
            if ($itsReferenceAttributeSingle || $itsReferenceAttributeMultiple) {
                
                // obtem todos os objetos referenciados                
                $referencedObjec_array = self::getReferencedObjects($attributeName, $attributeValue_possible_id_or_ids);                
                //debc($referencedObjec_array);
                    
                //estoque dos objetos referenciados
                $referencedObjectTemp_array = [];
                
                //caso tenha encontrado algum objeto referenciado
                if(sizeof($referencedObjec_array)>0){
                    
                    
                    // percorre cada um dos objetos referenciados
                    foreach ($referencedObjec_array as $referencedObjectTemp_id=>$referencedObjectTemp) {
                        
                        // LOAD REFERENCES
                        $referencedObjectTemp->loadReferences();
                        // deb($referencedObjectTemp,0);
                        
                        //SAVE ON TEMP ARRAY
                        $referencedObjectTemp_array[$referencedObjectTemp_id] = $referencedObjectTemp;
                        //deb($repositoryObjectParameter);
                        
                    }
                    //deb($referencedObjectTemp_array,0);
                    //deb(gettype(array_shift($referencedObjectTemp_array)),0);
                    
                    //FORMATO DE INSERCAO NO OBJETO
                    if($inArray==false){
                        if(sizeof($referencedObjectTemp_array)==1){
                            /**
                             * caso o objeto em questao faca referencia a apenas um objeto
                             * este sera disponibilizado diretamento no parametro criado
                             * e NAO EM FORMA de array!
                             */
                            $referencedObjectTemp_array = array_shift($referencedObjectTemp_array);
                        }
                    }                    
                    
                    
                }
                
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< SET VALUE
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< SET VALUE
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< SET VALUE
                {
                    {// method name
                        $set_method = "set" . ucfirst(strtolower(self::getReferencedModelName($attributeName)));                     
                    }
                    $model_object->$set_method($referencedObjectTemp_array, true);
                }
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            }
        }
        
    }

    /**
     * obtem o(s) objeto(s) referenciado(s)
     *
     * @param string $attributeName
     * @param string $attributePossible_id
     * @return array
     */
    static private function getReferencedObjects($attributeName, $attributeValue_possible_id_or_ids): array
    {
        $attributeValue_possible_id_or_ids = strval($attributeValue_possible_id_or_ids);
        //deb($attributeName,0); deb($attributeValue_possible_id_or_ids);
        
        $referencedObject_array = [];

        // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:user)
        $attributeName = self::removeNickname($attributeName);
        //deb($attributeName,0);

        $possibleModelName = self::getReferencedModelName($attributeName);
        //deb($possibleModelName,0);

        $modelPossibleModelName = Model_Helper::getObjectClassname($possibleModelName);        
        //deb($modelPossibleModelName);

        if (self::itsReferenceAttributeMultiple($attributeName)) {
            $attributeValue_id_array = explode(',', $attributeValue_possible_id_or_ids);
        } else {
            $attributeValue_id_array = [
                $attributeValue_possible_id_or_ids
            ];
        }
        //deb($attributeValue_id_array);

        foreach ($attributeValue_id_array as $attributeValue_id) {
            if(intval($attributeValue_id)==0) {
                continue;
            }
            //deb($attributeValue_id,0);
            $referencedObjectTemp = new $modelPossibleModelName($attributeValue_id);
            //$referencedObjectTemp->loadReferences();             
            $referencedObject_array[$attributeValue_id] = $referencedObjectTemp;
            // deb($repositoryObject,0); debc($referencedObjectTemp,0);
        }
        //deb($referencedObjectTemp);
        return $referencedObject_array;
    }
    
    /**
     * obtem o nome do possivel repositorio (sem _id, _ids, etc.)
     * @param string $attributeName
     * @throws Exception
     * @return string
     */
    static function getReferencedModelName(string $attributeName, bool $throwException = true):string{
        
        if(self::itsReferenceAttributeSingle($attributeName)){
            // obtem o possivel nome do repositorio
            $possibleModelName = ucfirst(str_replace(self::simple_reference_indicator_end, '', $attributeName));
            // deb($possibleModelName);
        }else if(self::itsReferenceAttributeMultiple($attributeName)){
            // obtem o possivel nome do repositorio
            $possibleModelName = ucfirst(str_replace(self::multiple_reference_indicator_end, '', $attributeName));
            // deb($possibleModelName);
        }else{
            if($throwException){
                throw new Exception("Atributo não referencial informado ('$attributeName').");
            }else{
                $possibleModelName = false;
            }            
        }
        return $possibleModelName;
    }
    
    /**
     * verifica se o nome do atributo informado possui num apelido
     * @param string $attributeName
     * @return bool
     */
    static function itsNickedAttribute(string $attributeName):bool{
        if (strpos($attributeName, self::reference_nick_splitter) !== false) {
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * retornando o nome do modelo referenciado sem o apelido
     * ex.: pedreiro__usuario_id => usuario_id
     *
     * @param string $attributeName
     */
    static function removeNickname(string $attributeName)
    {
        { // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
            if (self::itsNickedAttribute($attributeName)) {
                $attributeName = explode(self::reference_nick_splitter, $attributeName);
                if (sizeof($attributeName) > 2) {
                    throw new Exception("Definição incorreta para parâmetro de objeto. Este não pode conter mais do que 1 'reference_nick_splitters' (" . self::reference_nick_splitter . ").");
                } else {
                    // apelido do campo (ex.:pedreiro)
                    // $nick = $key[0];
                    // chave padrao (ex.:user_id)
                    $attributeName = $attributeName[1];
                }
            }
        }
        return $attributeName;
    }
    
    
    /**
     * retornando o apelido do atributo referenciado
     * ex.: pedreiro__usuario_id => pedreiro
     *
     * @param string $attributeName
     */
    static function getNickname(string $attributeName)
    {
        { // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
            if (self::itsNickedAttribute($attributeName)) {
                $attributeName = explode(self::reference_nick_splitter, $attributeName);
                if (sizeof($attributeName) > 2) {
                    throw new Exception("Definição incorreta para parâmetro de objeto. Este não pode conter mais do que 1 'reference_nick_splitters' (" . self::reference_nick_splitter . ").");
                } else {
                    // apelido do campo (ex.:pedreiro)
                    // $nick = $key[0];
                    // chave padrao (ex.:user_id)
                    $attributeName = $attributeName[0];
                }
            }
        }
        return $attributeName;
    }
    

    /**
     * verifica se o nome do parametro indica que seja um campo que faca referencia a outro objeto do sistema
     *
     * @param string $attributeName
     * @return bool
     */
    static function itsReferenceAttribute(string $attributeName): bool
    {
        $return = (self::itsReferenceAttributeSingle($attributeName) || self::itsReferenceAttributeMultiple($attributeName));
        
        return $return;
    }

    /**
     * verifica se o nome do parametro indica que seja um campo que faca referencia a outro objeto do sistema
     *
     * @param string $attributeName
     * @return bool
     */
    static function itsReferenceAttributeSingle(string $attributeName): bool
    {
        { // parametro eh uma referencia?
            $attributeNameFinalPart = substr($attributeName, (- 1) * strlen(self::simple_reference_indicator_end));
            if ($attributeNameFinalPart == self::simple_reference_indicator_end) {
                return true;
            }
        }
        return false;
    }

    /**
     * verifica se o nome do parametro indica que seja um campo que faca referencia multiplo a outros objetos (do mesmo tipo) do sistema
     *
     * @param string $attributeName
     * @return bool
     */
    static function itsReferenceAttributeMultiple(string $attributeName): bool
    {
        { // parametro eh uma referencia multiplo?
            $attributeNameFinalPart = substr($attributeName, (- 1) * strlen(self::multiple_reference_indicator_end));
            if ($attributeNameFinalPart == self::multiple_reference_indicator_end) {
                return true;
            }
        }
        return false;
    }


}

?>