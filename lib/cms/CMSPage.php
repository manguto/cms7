<?php
namespace manguto\cms5\lib\cms;

use Rain\Tpl;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\ServerHelp;

/**
 * Documentation for web designers:
 * https://github.com/feulf/raintpl/wiki/Documentation-for-web-designers *
 *
 * @author Marcos Torres *
 */
class CMSPage
{

    private $tpl;

    private $tpl_dir;

    const tpl_inclusive_folders = ['general']; 
    
    private $options = [];

    private $optionsDefault = [
        "data" => []
    ];
    
    

    public function __construct($opts = array(), $tpl_dir = '')
    {
        $this->options = array_merge($this->optionsDefault, $opts);
        // deb($this->options);
        
        // tpl_dir
        $this->tpl_dir = $tpl_dir;
        
        // config
        $config = array(
            "tpl_dir" => ROOT_TPL . $tpl_dir,
            "cache_dir" => "cache/",
            "debug" => true // set to false to improve the speed
        );
        
        Tpl::configure($config);
        
        // create the Tpl object
        $this->tpl = new Tpl();
        
        $this->assignDataArray($this->options['data']);
    }

    private function assignDataArray($data = array())
    {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    /**
     * verifica se o arquivo template estah na pasta padrao (ROOT_TPL)
     * ou se pertence a algum modulos disponivel em ROOT_SIS
     * @param string $filename
     *  @param CMSPage $classObjectSample
     */    
    private function CheckAndOrFixTemplatePath(string $filename,CMSPage $classObjectSample)
    {
        $return = $filename;
        
        { // obtencao do nome da plataforma com base no nome da classe
            { // class name
                $className = get_class($classObjectSample);
                $className = Diretorios::fixDirectorySeparator($className);
                $thisclassname_ = explode(DIRECTORY_SEPARATOR, $className);
                $className = array_pop($thisclassname_);
                // deb($className,0);
            }
            { // platform
                $platform = strtolower($className);
                $platform = str_replace('cmspage', '', $platform);
                // deb($platform,0);
            }
        }
        
        { // verifica se o arquivo tpl esta na pasta padrao (tpl) ou na pasta dos modelos.
            //deb(ROOT_TPL);
            $filename_test = ROOT_TPL . $platform . '/' . $filename . '.html';
            $filename_test = ServerHelp::fixds($filename_test);
            //deb($filename_test, 0);
            //deb(getcwd(),0); deb($filename_test,0);
            if (file_exists($filename_test)) {
                //deb("Arquivo encontrado na biblioteca",0);
                // <<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<                
                $return = $filename;
                // <<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<
                // <<<<<<<<<<<<<<<<<<<<
            } else {                
                //deb("Arquivo NÃO encontrado na biblioteca",0);
                { // apenas os templates da plataforma em questao serao analisados                    
                    $sis_tpl_path_array = Diretorios::obterArquivosPastas(ROOT_SIS, true, true, false, [
                        'html'
                    ]);
                    //deb($sis_tpl_path_array,0);
                }       
                $return = [];
                foreach ($sis_tpl_path_array as $sis_tpl_path) {
                    
                    if ($filename == Arquivos::obterNomeArquivo($sis_tpl_path, false)) {
                        
                        //tpl auto loading only on 'tpl' folder
                        if(strpos($sis_tpl_path,'tpl')===false){
                            continue;
                        }
                        
                        //extension remove
                        $filename_full = str_replace('.html', '', $sis_tpl_path);
                        //bridge inserction
                        $filename_full = self::checkFixTplPathBridge().$filename_full;
                        //fix directory separator
                        $filename_full = str_replace('\\', '/', $filename_full);
                        
                        // <<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<
                        $return[] = $filename_full;
                        // <<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<
                    }
                }
                if(sizeof($return)>1){
                    throw new Exception("Foram encontrados mais de um template com o mesmo nome (".implode(', ' , $return).").");
                }else if(sizeof($return)==1){
                    $return = array_shift($return);
                }else{
                    throw new Exception("O template solicitado não foi encontrado ($filename).");
                }
            }
        }
        //deb($return,0);
        return $return;
    }
    
    /**
     * retorna o endereço completo para um "{include='xxxxxx'}"
     * baseado no diretorio dos templates (ROOT_TPL) e 
     * realizado diretamento no arquivo HTML
     * @param string $filename
     * @throws Exception
     * @return string
     *
    static function include_tpl(string $filename):string
    {
        $path = '';
        
        $limite = 5;
        
        $filename_tmp = '';
        
        while ($filename_tmp=='' || ! file_exists($filename_tmp)) {
            
            //path serah incrementado apenas após primeira iteracao
            if($filename_tmp!=''){
                $path .= '..' . DIRECTORY_SEPARATOR;
            }
            
            $filename_tmp = $path . ROOT_TPL . $filename . '.html';
            
            if ($limite -- < 0)
                throw new Exception("Número máximo de tentativas atingida na busca do template '$filename'.");
        }
        $return = $path . $filename;
        deb($return,0);
        return $return;
    }*/

    static private function checkFixTplPathBridge(){
        
        $ROOT_TPL_ = explode(DIRECTORY_SEPARATOR, Diretorios::fixDirectorySeparator(ROOT_TPL));        
        //deb($ROOT_TPL_);
        $jumps = '';
        foreach ($ROOT_TPL_ as $dir){
            //deb($dir,0);
            if($dir=='..' || trim($dir)==''){
                continue;
            }
            $jumps .= '..'.DIRECTORY_SEPARATOR;
        }
        //deb($jumps);
        return $jumps;
    }
    
    public function setTpl($filename, $data = array(), bool $toString = false, $classObjectSample)
    {
        /*{//define include variables needes
            $include_vars = self::getIncludeVars();  
            $data = array_merge($data,$include_vars);  
        }/**/
        
        $this->assignDataArray($data);
        
        $filename = $this->CheckAndOrFixTemplatePath($filename, $classObjectSample);
        //deb($filename);
        $html = $this->tpl->draw($filename, true);
        
        $html = CMSPage_Tools::TplReferencesFix($html);
        
        if ($toString) {
            return $html;
        } else {
            echo $html;
        }
    }

    public function __destruct()
    {
        // ...
    }
}

?>