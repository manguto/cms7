<?php
namespace manguto\cms7\lib\cms;

use Rain\Tpl;
use manguto\cms7\lib\Exception;
use manguto\cms7\lib\ServerHelp;
use manguto\cms7\lib\Logs;

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
            "tpl_dir" => SIS_CMS_TPL_PATH . $tpl_dir,
            "cache_dir" => "cache/",
            "debug" => false // set to false to improve the speed
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
     * obtem o nome completo do arquivo do template solicitado
     * @param string $filename
     * @throws Exception
     * @return 
     */
    private function getFullTemplatePath(string $filename):string
    {
        $return = $filename;
        { // verifica se o arquivo tpl esta na pasta padrao (tpl) ou na pasta dos modelos.
            
            $filename_test = ServerHelp::fixds(SIS_CMS_TPL_PATH . $this->tpl_dir . $filename . '.html');
            //deb($filename_test, 0);
            
            if (file_exists($filename_test)) {
                $msg = "Template '$filename_test' encontrado com sucesso.";
                Logs::set('info',$msg);
                return $filename;                
            } else {
                $msg = "Template '$filename_test' NÃO encontrado!";
                Logs::set('info',$msg);
                throw new Exception($msg);
            }
        }
    }
    
    public function setTpl($filename, $data = array(), bool $toString = false)
    {   
        {//atribuicao dos dados informados para processamento na procudao do html
            $this->assignDataArray($data);
        }
        {//obtencao do html em questao
            $html = $this->tpl->draw($this->getFullTemplatePath($filename), true);
            
        }
        {//ajustes idenficados localmente
            $html = CMSPageTools::TplReferencesFix($html);
        }
                
        if ($toString) {
            return $html;
        }
        
        print $html;        
    }

    public function __destruct()
    {
        // ...
    }
}

?>