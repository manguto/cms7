<?php
namespace manguto\cms7\database\repository;

use manguto\cms7\libraries\Exception;  

class _OFF_RepositoryReferences
{

    // indicador de referencia a outro objeto
    const ctrl_parameter_ini = '@';

    // indicador de referencia a outro objeto
    const reference_indicator_end = '_id';

    // indicador de referencia multipla a outros objetos
    const multiple_reference_indicator_end = '_ids';

    // separador de apelido de objeto referenciado quando da criacao do nome da coluna para o repositorio
    const reference_nick_splitter = '__';


    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    // ###########################################################################################################################################################################################################################################
    static function Load(Repository $repositoryObject): Repository
    {
        //deb($tablename=$repositoryObject->getModelname(),0);
                        
        $parameters = $repositoryObject->GET_DATA($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = false, $singleLevelArray = false);

        //deb($parameters,0);
        foreach ($parameters as $parameterName => $parameterValue_possible_id_or_ids) {

            // caso o array nao possua nenhum conteudo FALSE, ou seja, é um parametro referencial (ex.: pessoa_id, responsavel__pessoa_id, categoria_id)
            $ehParametroReferencial = self::itsReferenceAttributeSingle($parameterName);
            $itsReferenceAttributeMultiple = self::itsReferenceAttributeMultiple($parameterName);
            // deb($ehParametroReferencial,0); deb($itsReferenceAttributeMultiple,0);
            
            if ($ehParametroReferencial || $itsReferenceAttributeMultiple) {

                // obtem todos os objetos referenciados
                $referencedObjec_array = self::getReferencedObjects($parameterName, $parameterValue_possible_id_or_ids);                
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
                    $set_method = "set" . ucfirst(strtolower(self::getReferencedModelName($parameterName)));
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
     * @param string $parameterName
     * @param string $parameterPossible_id
     * @return array
     */
    static private function getReferencedObjects($parameterName, $parameterValue_possible_id_or_ids): array
    {
        //deb($parameterName,0); deb($parameterValue_possible_id_or_ids,0);
        
        $referencedObject_array = [];

        // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
        $parameterName = self::removeNickname($parameterName);
        // deb($parameterName,0);

        $possibleRepositoryName = self::getReferencedModelName($parameterName);
        //deb($possibleRepositoryName,0);

        $modelPossibleRepositoryName = Repository::getObjectClassname($possibleRepositoryName);
        // deb($modelPossibleRepositoryName,0);

        if (self::itsReferenceAttributeMultiple($parameterName)) {
            $parameterValue_id_array = explode(',', $parameterValue_possible_id_or_ids);
        } else {
            $parameterValue_id_array = [
                $parameterValue_possible_id_or_ids
            ];
        }
        //deb($parameterValue_id_array,0);

        foreach ($parameterValue_id_array as $parameterValue_id) {
            if(intval($parameterValue_id)==0) {
                continue;
            }
            //deb($parameterValue_id,0);
            $referencedObjectTemp = new $modelPossibleRepositoryName($parameterValue_id);
            $referencedObjectTemp->loadReferences();             
            $referencedObject_array[$parameterValue_id] = $referencedObjectTemp;
            // deb($repositoryObject,0); debc($referencedObjectTemp,0);
        }
        //deb($referencedObjectTemp,0);
        return $referencedObject_array;
    }
    
    /**
     * obtem o nome do possivel repositorio (sem _id, _ids, etc.)
     * @param string $parameterName
     * @throws Exception
     * @return string
     */
    static function getReferencedModelName(string $parameterName):string{
        if(self::itsReferenceAttributeSingle($parameterName)){
            // obtem o possivel nome do repositorio
            $possibleRepositoryName = ucfirst(str_replace(self::simple_reference_indicator_end, '', $parameterName));
            // deb($possibleRepositoryName);
        }else if(self::itsReferenceAttributeMultiple($parameterName)){
            // obtem o possivel nome do repositorio
            $possibleRepositoryName = ucfirst(str_replace(self::multiple_reference_indicator_end, '', $parameterName));
            // deb($possibleRepositoryName);
        }else{
            throw new Exception("Parâmetro não referencial informado ('$parameterName').");
        }
        return $possibleRepositoryName;
    }
    
    /**
     * verifica se o nome do parametro eh composto por um apelido,
     * retornando apenas o nome do modelo referenciado sem o apelido
     * ex.: pedreiro__usuario_id => usuario_id
     *
     * @param string $parameterName
     */
    static private function removeNickname(string $parameterName)
    {
        { // verificacao de apelido para campo referencial (pedreiro__user_id => apelido:pedreiro, objeto:usuario)
            if (strpos($parameterName, self::reference_nick_splitter) !== false) {
                $parameterName = explode(self::reference_nick_splitter, $parameterName);
                if (sizeof($parameterName) > 2) {
                    throw new Exception("Definição incorreta para parâmetro de objeto. Este não pode conter mais do que 1 'reference_nick_splitters' (" . self::reference_nick_splitter . ").");
                } else {
                    // apelido do campo (ex.:pedreiro)
                    // $nick = $key[0];
                    // chave padrao (ex.:user_id)
                    $parameterName = $parameterName[1];
                }
            }
        }
        return $parameterName;
    }

    

    /**
     * verifica se o nome do parametro indica que seja um campo que faca referencia multiplo a outros objetos (do mesmo tipo) do sistema
     *
     * @param string $parameterName
     * @return bool
     */
    static function itsReferenceAttributeMultiple(string $parameterName): bool
    {
        { // parametro eh uma referencia multiplo?
            $parameterNameFinalPart = substr($parameterName, (- 1) * strlen(self::multiple_reference_indicator_end));
            if ($parameterNameFinalPart == self::multiple_reference_indicator_end) {
                return true;
            }
        }
        return false;
    }


}

?>