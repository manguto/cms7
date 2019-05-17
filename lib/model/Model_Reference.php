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
    const reference_nick_splitter = '__';

    // separador para insercao na chave do valor de objeto referenciado quando retornado de uma chamada à funcao LOAD()
    const reference_key_splitter = '___';

    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    static function Load($repositoryObject)
    {
        //deb($tablename=$repositoryObject->getModelname(),0);
                        
        $attributes = $repositoryObject->GetData($attribute_extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = false, $singleLevelArray = false);

        //deb($attributes,0);
        foreach ($attributes as $attributeName => $attributeValue_possible_id_or_ids) {

            // caso o array nao possua nenhum conteudo FALSE, ou seja, é um parametro referencial (ex.: pessoa_id, responsavel__pessoa_id, categoria_id)
            $ehParametroReferencial = self::itsReferenceAttributeSimple($attributeName);
            $itsReferenceAttributeMultiple = self::itsReferenceAttributeMultiple($attributeName);
            // deb($ehParametroReferencial,0); deb($itsReferenceAttributeMultiple,0);
            
            if ($ehParametroReferencial || $itsReferenceAttributeMultiple) {

                // obtem todos os objetos referenciados
                $referencedObjec_array = self::getReferencedObjects($attributeName, $attributeValue_possible_id_or_ids);                
                //debc($referencedObjec_array);
                // percorre cada um dos objetos referenciados

                
                if(sizeof($referencedObjec_array)>0){
                    $referencedObjectTemp_array = [];
                    
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
                    
                    // METHOD NAME
                    $set_method = "set" . ucfirst(strtolower(self::getReferencedModelName($attributeName)));
                    //deb($set_method,0);
                    
                    //SET VALUES
                    $repositoryObject->$set_method($referencedObjectTemp_array, false);
                }else{
                    
                    //nada!
                    
                }
                
                
            }
        }
        // deb($repositoryObject,0);
        return $repositoryObject;
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
        //deb($attributeName,0); deb($attributeValue_possible_id_or_ids,0);
        
        $referencedObject_array = [];

        // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
        $attributeName = self::removerApelidoSe($attributeName);
        // deb($attributeName,0);

        $possibleRepositoryName = self::getReferencedModelName($attributeName);
        //deb($possibleRepositoryName,0);

        //$modelPossibleRepositoryName = Repository::getObjectClassname($possibleRepositoryName);
        throw new Exception("");
        // deb($modelPossibleRepositoryName,0);

        if (self::itsReferenceAttributeMultiple($attributeName)) {
            $attributeValue_id_array = explode(',', $attributeValue_possible_id_or_ids);
        } else {
            $attributeValue_id_array = [
                $attributeValue_possible_id_or_ids
            ];
        }
        //deb($attributeValue_id_array,0);

        foreach ($attributeValue_id_array as $attributeValue_id) {
            if(intval($attributeValue_id)==0) {
                continue;
            }
            //deb($attributeValue_id,0);
            $referencedObjectTemp = new $modelPossibleRepositoryName($attributeValue_id);
            $referencedObjectTemp->loadReferences();             
            $referencedObject_array[$attributeValue_id] = $referencedObjectTemp;
            // deb($repositoryObject,0); debc($referencedObjectTemp,0);
        }
        //deb($referencedObjectTemp,0);
        return $referencedObject_array;
    }
    
    /**
     * obtem o nome do possivel repositorio (sem _id, _ids, etc.)
     * @param string $attributeName
     * @throws Exception
     * @return string
     */
    static function getReferencedModelName(string $attributeName, bool $throwException = true):string{
        
        if(self::itsReferenceAttributeSimple($attributeName)){
            // obtem o possivel nome do repositorio
            $possibleRepositoryName = ucfirst(str_replace(self::simple_reference_indicator_end, '', $attributeName));
            // deb($possibleRepositoryName);
        }else if(self::itsReferenceAttributeMultiple($attributeName)){
            // obtem o possivel nome do repositorio
            $possibleRepositoryName = ucfirst(str_replace(self::multiple_reference_indicator_end, '', $attributeName));
            // deb($possibleRepositoryName);
        }else{
            if($throwException){
                throw new Exception("Parâmetro não referencial informado ('$attributeName').");
            }else{
                $possibleRepositoryName = false;
            }            
        }
        return $possibleRepositoryName;
    }
    
    /**
     * verifica se o nome do parametro eh composto por um apelido,
     * retornando apenas o nome do modelo referenciado sem o apelido
     * ex.: pedreiro__usuario_id => usuario_id
     *
     * @param string $attributeName
     */
    static private function removerApelidoSe(string $attributeName)
    {
        { // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
            if (strpos($attributeName, self::reference_nick_splitter) !== false) {
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
     * verifica se o nome do parametro indica que seja um campo que faca referencia a outro objeto do sistema
     *
     * @param string $attributeName
     * @return bool
     */
    static function ehParametroReferencial(string $attributeName): bool
    {
        $return = (self::itsReferenceAttributeSimple($attributeName) || self::itsReferenceAttributeMultiple($attributeName));
        
        return $return;
    }

    /**
     * verifica se o nome do parametro indica que seja um campo que faca referencia a outro objeto do sistema
     *
     * @param string $attributeName
     * @return bool
     */
    static function itsReferenceAttributeSimple(string $attributeName): bool
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