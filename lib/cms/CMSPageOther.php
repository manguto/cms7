<?php

namespace manguto\cms5\lib\cms;

use Rain\Tpl;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;


/**
 * Documentation for web designers:
 * https://github.com/feulf/raintpl/wiki/Documentation-for-web-designers * 
 * @author Marcos Torres *
 */
class CMSPageOther{

	private $tpl;
	private $tpl_dir;
	private $options = [];
	private $optionsDefault = [
		"data"=>[]
	];
	
	public function __construct($opts=array(),$tpl_dir='other/')
	{	    
		$this->options = array_merge($this->optionsDefault,$opts);		
		//deb($this->options);
		
		//tpl_dir
		$this->tpl_dir = $tpl_dir;
		
		// config
		$config = array(
			"tpl_dir"       => ROOT_TPL . $tpl_dir,			
		    "cache_dir"     => "cache/",
			"debug"         => true  // set to false to improve the speed
		);

		Tpl::configure( $config );
		
		// create the Tpl object
		$this->tpl = new Tpl;
		
		$this->assignDataArray($this->options['data']);
		
				
	}
	
	private function assignDataArray($data=array())
	{
		foreach ($data as $key=>$value){		    
			$this->tpl->assign($key,$value);
		}
	}
	
	/**
	 * Ajusta/corrige template (HTML) decorrentes do espacamento 
	 * colocado antes das chaves ("}") realizado automaticamente 
	 * pelo Eclipse quando do CTRL+F
	 * @param string $filename
	 */
	private function fixTpl(string $filename){
	    //ajuste nome comleto arquivo
	    $filename = ROOT_TPL.$this->tpl_dir.$filename.'.html';
	    //obtem conteudo
	    $data = Arquivos::obterConteudo($filename);
	    //debCode($data);
	    //realiza correcoes se necessario
	    if(strpos($data, ' }')!==false){
	        $data = str_replace(' }', '}', $data);
	        //reescreve conteudo
	        Arquivos::escreverConteudo($filename, $data);
	    }else{
	        //nenhum ajuste necessario
	    }
	}
	
	public function setTpl($filename,$data=array(),bool $toString=false)

	{
		$this->assignDataArray($data);
		
		//$this->fixTpl($filename);
		
		$html = $this->tpl->draw($filename,true);
		
		$html = CMSPage_Tools::TplReferencesFix($html);
		
		if($toString){
		    return $html;
        } else {
            echo $html;
        }
    }

	
	public function __destruct()
	{
	   //...
	}	

}



?>