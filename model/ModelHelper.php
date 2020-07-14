<?php
namespace manguto\cms7\model;

use manguto\cms7\libraries\Diretorios;
use manguto\cms7\libraries\Files;
use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\Strings;
use manguto\cms7\libraries\Logger;
use manguto\cms7\libraries\Alert;

class ModelHelper
{

    const model_class_folders = [
        APP_MODEL_DIR,
        APP_MODULES_DIR
    ];
    
    const funcoes_padrao = ['__construct','preLoad','posLoad'];
    
    const useModelRepositoryFlag = 'use ModelRepository;';
    
    /**
     * obtem o nome da classe do repositorio informado
     * para carregamento imediato.
     *
     * @param string $repositoryname
     * @return string
     */
    static function getObjectClassname(string $tablename): string
    {
        $tablename = explode('\\', $tablename);
        $tablename = array_pop($tablename);
        $tablename = ucfirst(strtolower($tablename));
        //deb($tablename,0);  
        
        $model_class_folders = self::model_class_folders;
        //deb($model_class_folders,0);
        
        foreach ($model_class_folders as $model_class_folder) {
            
            $php_files = Diretorios::obterArquivosPastas($model_class_folder, true, true, false, [
                'php'
            ]);
            //deb($php_files,0);
            foreach ($php_files as $php_file) {
                $nomeClasse = Files::getBaseName($php_file, false);
                $path = Files::getPath($php_file);
                //deb($nomeClasse,0); deb($tablename,0);  
                if ($nomeClasse == $tablename) {
                    
                    //deb($path);
                    $objectClassname = '\\' . $path . $tablename;
                    $objectClassname = str_replace('/', '\\', $objectClassname);
                    $objectClassname = str_replace('\vendor', '', $objectClassname);
                    // deb($objectClassname,0);
                    return $objectClassname;
                }
            }
        }
        //deb('+++');
        throw new Exception("Classe não encontrada ($tablename).");
    }
    
    
    /**
     * obtem o nome da classe do repositorio informado
     * para carregamento imediato.
     *
     * @param string $repositoryname
     * @return string
     */
    static function getObjectClassName_by_ClassName(string $searchedPathClass_className): string
    {   
        Logger::proc("Busca do nome completo da classe '$searchedPathClass_className' - ");
        Logger::proc("Diretorio(s) com modelo(s): '".implode("','", self::model_class_folders)."' ");
        
        //ciclo pelas pastas de modelos informada 
        foreach (self::model_class_folders as $model_class_folder) {            
            
            //obtencao dos arquivos php da pasta deste ciclo
            $dir_php_files = Diretorios::obterArquivosPastas($model_class_folder, true, true, false, [
                'php'
            ]);
            //percorrimento dos arquivos encontrados no diretorio atual
            foreach ($dir_php_files as $dir_php_file) {
                //nome base do arquivo da classe encontrado
                $ClassBaseFilename_noExt = Files::getBaseName($dir_php_file, false);
                $ClassBasePath = substr($dir_php_file,0, -1*(strlen($ClassBaseFilename_noExt)+4));
                //debc("$ClassBasePath - $ClassBaseFilename_noExt",0);                
                Logger::proc("Arquivo da classe '$dir_php_file'");
                
                Logger::proc("Teste: '$ClassBaseFilename_noExt' == '$searchedPathClass_className' ");
                
                if ($ClassBaseFilename_noExt == $searchedPathClass_className) {
                    
                    $classFullPath = '\\' . $ClassBasePath . $searchedPathClass_className;
                    
                    $classFullPath = str_replace('/', '\\', $classFullPath);
                    
                    //deb($classFullPath);
                    Logger::proc("Classe alvo encontrada! ($classFullPath)");
                    
                    return $classFullPath;
                }
            }
        }
        throw new Exception("A classe '$searchedPathClass_className' não foi encontrada!");
    }
    
    
    
    
    static function get(){
        $files = self::getFiles();
        //deb($files);
        
        sort($files);
        //deb($files);
        
        $models = self::getModels($files);
        //deb($models);
        
        
        self::loadFunctions($models,$files);
        //debc($models);
        
        self::loadParameters($models);
        //debc($models);
        
        self::useModelRepository($models);
        //debc($models);
        
        return $models;
    }
    
    static private function getModels($files){
        $models=[];
        foreach ($files as $file){
            $model_name = Files::getBaseName($file,false);
            $models[$model_name] = [];
        }
        return $models;
    }
    
    static private function loadParameters(array &$models){
        
        foreach ($models as $model_name=>$info){
            //deb($model_name,0); debc($info,0);
            
            //inicializacao do parametro
            $models[$model_name]['parametros'] = [];
            
            foreach ($info['funcao_padrao'] as $funcao_nome=>$funcao_info){
                //deb($funcao_nome,0); debc($funcao_conteudo);
                //$funcao_argumentos = $funcao_info['argumentos'];
                $funcao_conteudo = $funcao_info['conteudo'];
                
                if($funcao_nome=='preLoad'){
                    //debc($funcao_conteudo,0);
                    $funcao_preload_conteudo_ = explode(chr(10), $funcao_conteudo);
                    //debc($funcao_preload_conteudo_);
                    foreach ($funcao_preload_conteudo_ as $funcao_preload_conteudo_linha){
                        if(strpos($funcao_preload_conteudo_linha, '$this->parameters[')){
                            $pline = trim($funcao_preload_conteudo_linha);
                            $pline = str_replace("\$this->parameters['", '', $pline);
                            $pline = str_replace("']", '', $pline);
                            $pline = str_replace("'", "", $pline);
                            //deb($pline);
                            {
                                $pline_ = explode('=', $pline);
                                $pname = trim(array_shift($pline_));
                                //deb($pname);
                                {
                                    $pline_ = explode(';', array_pop($pline_));
                                    $pdef = trim(array_shift($pline_));
                                    //deb($pdef);
                                    $pcoment = trim(array_pop($pline_));
                                    $pcoment = str_replace('//', '', $pcoment);
                                    //deb($pcoment);
                                }
                                {
                                    $pref = substr($pname,-3)=='_id' ? substr($pname, 0,strlen($pname)-3) : '';
                                }
                            }
                            $models[$model_name]['parametros'][$pname]['padrao'] = $pdef;
                            $models[$model_name]['parametros'][$pname]['comentario'] = $pcoment;
                            $models[$model_name]['parametros'][$pname]['reference'] = $pref;
                            
                        }
                    }
                }
            }
        }
    }
    
    static private function getFiles(){
        $modelFiles = [];
        foreach (self::model_class_folders as $model_dir){
            $files = Diretorios::obterArquivosPastas($model_dir, false, true, false,['php']);
            //deb($files,0);
            foreach ($files as $file){
                if(strpos($file, 'Zzz')) continue;
                $modelFiles[] = $file;
            }
        }
        return $modelFiles;
    }
    
    static private function useModelRepository(array &$models){
        //deb($models);
        foreach ($models as $key=>&$model){
            //deb($key,0);
            $conteudo = $model['conteudo'];
            //debc($conteudo);
            if(strpos($conteudo, self::useModelRepositoryFlag)!==false){
                $model['useModelRepository'] = true;
            }else{
                $model['useModelRepository'] = false;
            }
        }
        
    }
    
    static private function loadFunctions(array &$models, array $filename_array){
        $funcoes = [];
        foreach ($filename_array as $filename){
            
            $conteudo = Files::obterConteudo($filename);
            //debc($conteudo);
            
            $model_name = Files::getBaseName($filename,false);
            //deb($model_name,0);
            
            {//parameters
                
                //separa o conteudo pelas funcoes
                $funcao_conteudo_ = explode(' function ', $conteudo);
                
                $models[$model_name]['funcao_padrao'] = [];
                $models[$model_name]['funcao_adicional'] = [];
                $models[$model_name]['conteudo'] = $conteudo;
                
                if(sizeof($funcao_conteudo_)>1){
                    //remove conteudo desnecessario
                    array_shift($funcao_conteudo_);
                    //debc($funcoes);
                    
                    foreach ($funcao_conteudo_ as $f){
                        //debc($f);
                        //nome da funcao
                        $f_ = explode('(', $f);
                        //debc($f_);
                        $funcao_nome = array_shift($f_);
                        //deb($funcao_nome,0);
                        //deb($f_);
                        {
                            $funcao_conteudo = implode('(', $f_);
                            //debc($funcao_conteudo);
                            {//obter argumentos da funcao
                                $funcao_conteudo_ = explode(')', $funcao_conteudo);
                                //remove parametros
                                $funcao_argumentos = array_shift($funcao_conteudo_);
                                $funcao_argumentos = str_replace(',', ', ', $funcao_argumentos);
                                $funcao_argumentos = str_replace(' = ', '=', $funcao_argumentos);
                                $funcao_argumentos = Strings::RemoverEspacamentosRepetidos($funcao_argumentos);
                                
                                //deb($funcao_parametros);
                                $funcao_conteudo = implode(')', $funcao_conteudo_);
                                //debc($funcao_conteudo);
                            }
                            {//remove inicio outra funcao
                                $funcao_conteudo_ = explode('}', $funcao_conteudo);
                                //deb($funcao_conteudo_);
                                //remove atributos da funcao seguinte
                                array_pop($funcao_conteudo_);
                                $funcao_conteudo = implode('}', $funcao_conteudo_).'}';
                                //debc($funcao_conteudo);
                            }
                            //debc($funcao_conteudo);
                        }
                        {
                            $tipo =  in_array($funcao_nome, self::funcoes_padrao) ? 'funcao_padrao' : 'funcao_adicional';
                        }
                        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                        $models[$model_name][$tipo][$funcao_nome]['argumentos']=$funcao_argumentos;
                        $models[$model_name][$tipo][$funcao_nome]['conteudo']=$funcao_conteudo;
                        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    }
                }
            }
        }
    }
    
    static function Initializer()
    {
        Alert::setWarning("Inicialização de Modelos solicitada.");
        $models = Diretorios::obterArquivosPastas(APP_MODEL_DIR, false, true, false, [
            'php'
        ]);
        // deb($models);
        $msg = "Modelos encontrados: <b>".sizeof($models)."</b>";
        Logger::info($msg);
        Alert::setWarning($msg);
        foreach ($models as $model) {
            $modelClassName = Files::getBaseName($model, false);
            if ($modelClassName == 'Zzz' || substr($modelClassName, 0, 1) == '_') {
                continue;
            }
            $modelClassNamePath = ModelHelper::getObjectClassName_by_ClassName($modelClassName);
            $modelClassNamePath::initialize();
            $msg = "Modelo <b>$modelClassName</b> inicializado.";
            Logger::success($msg);
            Alert::setWarning($msg);
        }
    }
    
    /*static function get_repository_extended_tablenames(){
        $model_array = ModelHelper::get();
        //deb($model_array);
        foreach ($model_array as $tablename=>$model_information){
            //deb($model_information);
            $er = $model_information['useModelRepository'];
            //deb($tablename,0); deb($er,0);
            if($er){
                $tablename_show = explode('_',$tablename);
                $tablename_show = array_map('ucfirst',$tablename_show);
                $tablename_show = implode(' ', $tablename_show);
                
                $model_array[strtolower($tablename)] = $tablename_show;
            }
            unset($model_array[$tablename]);
        }
        //deb($model_array);
        return $model_array;
    }/**/
   
}

?>