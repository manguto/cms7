<?php
namespace manguto\cms5\mvc\view;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\repository\Repository;
use manguto\cms5\lib\repository\RepositoryReferences;

class ViewDevRepository extends ViewDev
{

    static function repository()
    {
        {
            $repositorios = Diretorios::obterArquivosPastas('repository', false, true, false, [
                'csv'
            ]);
            sort($repositorios);
            //deb($repositorios);
            foreach ($repositorios as $key => $repositorio) {
                // deb($repositorio);
                
                $repositoryname = Arquivos::obterNomeArquivo($repositorio, false);
                
                unset($repositorios[$key]);
                $repositorios[$repositoryname] = [
                    'filename' => $repositorio,
                    'show' => strtoupper($repositoryname)
                ];
            }
        }
        // deb($repositorios);
        self::load('repository', get_defined_vars());
    }

    static function repository_view($repository)
    {
        
        // deb($repository);
        {
            $repositoryNameShow = ucfirst($repository);
        }
        {//colunas
            $cols = [];
            $repositoryNameCall = Repository::getObjectClassname($repository);
            // deb($repositoryNameCall);
            $temp = new $repositoryNameCall();
            // deb($temp);
            $cols = array_keys($temp->getData($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false));
            // deb($colunas,0);
            foreach ($cols as $k => $coluna) {                
                $cols[$k] = [
                    'name' => $coluna,
                    'nameShow' => strtoupper($coluna)
                ];
            }
            // deb($colunas,0);
        }
        {//registros
            $registers = Repository::getRepository($repository, '', true, true, false);
            //deb($repository);
            //deb($registers);
            foreach ($registers as &$register){
                $register->replaceReferences();
                //deb($register);                
                $register = $register->getData($extraIncluded = FALSE, $ctrlParametersIncluded = false, $referencesIncluded = false, $singleLevelArray = false);
                //deb($register);
                //$register = Arrays::arrayMultiNivelParaSimples($register);
            }
            //deb($registers);
        }
        
        self::load('repository_view', get_defined_vars());
    }

    static function repository_register_view($repository, $register)
    {   //deb($repository,0); deb($register);
        {
            $repositoryNameShow = ucfirst($repository);
        }
        
        self::load('repository_register_view', get_defined_vars());
    }

    static function repository_register_edit($repository, $register)
    {
        {
            $repositoryNameShow = ucfirst($repository);
        }
        {
            foreach ($register as $k=>$id){
                if(RepositoryReferences::ehParametroReferencial($k)){
                    $tablename = substr($k, 0,-3);
                    //deb($tablename,0);
                    $register[$k] = self::HTML_Combo($id,$tablename);
                }
            }
            //debc($register);
        }
        self::load('repository_register_edit', get_defined_vars());
    }
    
    
    //..............................................................................
    static function repository_sheet_view($repository)
    {
        
        // deb($repository);
        {
            $repositoryNameShow = ucfirst($repository);
        }
        {//colunas
            $cols = [];
            $repositoryNameCall = Repository::getObjectClassname($repository);
            // deb($repositoryNameCall);
            $temp = new $repositoryNameCall();
            // deb($temp);
            $cols = array_keys($temp->getData($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false));
            // deb($colunas,0);
            foreach ($cols as $k => $coluna) {
                $cols[$k] = [
                    'name' => $coluna,
                    'nameShow' => strtoupper($coluna)
                ];
            }
            // deb($colunas,0);
        }
        {//registros
            $registers = Repository::getRepository($repository, '', false, false, false);            
        }
        
        self::load('repository_sheet_view', get_defined_vars());
    }
    
    
    
    
}