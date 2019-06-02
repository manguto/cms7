<?php
namespace manguto\cms5\lib\model;

trait ModelTrait
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
}

?>