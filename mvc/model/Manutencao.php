<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelTrait;

class Manutencao extends Model
{

    use ModelTrait;
    use ModelRepository;

    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        // ---------------------------------------------------
        $a = new ModelAttribute('motivo');
        $a->setType(ModelAttribute::TYPE_TEXT);
        $a->setValue('Manutenção Preventiva Padrão.');
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('mensagem');
        $a->setType(ModelAttribute::TYPE_TEXT);
        $a->setValue('MANUTENÇÃO em ANDAMENTO! <br/>Por favor, aguarde ou contate o administrador do sistema. <br/><br/>Atenciosamente, <br/>O Administrador');
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('status');
        $a->setValue('ativa');
        $this->SetAttribute($a);
        // ---------------------------------------------------
    }
    
    /**
     * Verifica se há alguma manutenção ativa e a retorna ou FALSE.
     * @return boolean|mixed
     */
    static function EmFuncionamento() {
        $manutencao_array = (new self())->search(" \$status=='ativa' ");
        if(sizeof($manutencao_array)>0){
            $manutencao = $manutencao_array;
        }else{
            $manutencao = [];
        }
        return $manutencao;
    }
}



