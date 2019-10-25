<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\database\ModelDatabase;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\model\ModelTrait;

class User_module extends Model implements ModelDatabase
{

    use ModelTrait;
    use ModelRepository;

    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        // -------------------------------------------------
        $a = new ModelAttribute('user_id');
        $a->setType(ModelAttribute::TYPE_INT);
        $this->SetAttribute($a);
        // -------------------------------------------------
        $a = new ModelAttribute('module_id'); 
        $a->setType(ModelAttribute::TYPE_INT);
        $this->SetAttribute($a);
        // -------------------------------------------------
        $a = new ModelAttribute('nature');
        $this->SetAttribute($a);
        // -------------------------------------------------
    }

    //=================================================================================
    
    static function obterUsuarioModulosPermitidos($user_id):array{
        $user_modules = (new self())->search(" \$user_id==$user_id ");
        $return = [];
        foreach ($user_modules as $user_module){
            $return[] = new Module($user_module->getModule_id());
        }
        {
            $self =  new Module();
            $self->setNome(SIS_NAME);
            $self->setPasta(SIS_FOLDERNAME);
            $return[] = $self;
        }
        return $return; 
    }
}

?>