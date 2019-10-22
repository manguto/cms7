<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\database\ModelDatabase;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\model\ModelTrait;

class Module extends Model implements ModelDatabase
{

    use ModelTrait;
    use ModelRepository;

    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        // -------------------------------------------------
        $a = new ModelAttribute('nome');
        $this->SetAttribute($a);
        // -------------------------------------------------
        $a = new ModelAttribute('pasta');        
        $this->SetAttribute($a);
        // -------------------------------------------------
    }


}

?>