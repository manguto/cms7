<?php
namespace manguto\cms7\lib\model;

use manguto\cms7\lib\Exception;
use manguto\cms7\lib\ProcessResult;

trait ModelStart
{

    public function __construct($id = 0)
    {
        // atributos basicos (fundamentais)
        $this->SetFundamentalAttributes($id);
        // deb($this);

        // definicao dos atributos deste modelo
        $this->defineAttributes();
        // deb($this);

        // carregamento de atributos do banco de dados
        if ($id != 0) {
            $this->load();
            // deb($this);
        }
        // verifica corretude da estrutura dos dados
        parent::checkSetStruct();
    }

    /**
     * inicializa o repositorio caso defindos os registros base (constante 'default').
     * @throws Exception
     */
    static function initialize()
    {   
        if(defined('self::default') && sizeof(self::default)>0){            
            $n = (new self())->length();
            if ($n == 0) {
                ProcessResult::setWarning("Processo de inicialização do repositório '".__CLASS__."' inicializada.");
                foreach (self::default as $data){
                    $new = new self();
                    $new->SET_DATA($data);
                    $new->save();
                    ProcessResult::setWarning("Registro da classe '".__CLASS__."' inserido com sucesso ({$new->getId()})!");
                }
            }
        }        
    }
    
    /**
     * Verificacao da integridade do objeto (estrutura dos dados) 
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    public function CheckDataIntegrity($throwException = true)
    {
        $errors=[];
        if ($this->getId() != 0) {
            {
                //...
            }
        }
        if(sizeof($errors)>0){
            if($throwException){
                throw new Exception(implode('<hr/>', $errors));
            }else{
                return false;
            }
        }else{
            return true;
        }        
    }
}

?>