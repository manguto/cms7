<?php
namespace sis\model;

use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\database\ModelDatabase;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\model\ModelTrait;
use manguto\cms5\lib\Strings;

class Xxx extends Model implements ModelDatabase
{
    
    use ModelTrait; 
    use ModelRepository;

    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        { // nome
            $a = new ModelAttribute('nome');
            $this->SetAttribute($a);
        }
        { // idade
            $a = new ModelAttribute('idade');
            $a->setType(ModelAttribute::TYPE_INT);
            $this->SetAttribute($a);
        }
        
        { // peso
            $a = new ModelAttribute('peso');
            $a->setType(ModelAttribute::TYPE_FLOAT);
            $a->setUnit('Kg');
            $this->SetAttribute($a);
        }
    }
    
    
    
    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // /////////////////////////////////// CRUD => create, retrieve, update and delete ////////////////////////////////////////////////////
    // \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    static function TESTS(){
        self::TEST_CREATE();
        self::TEST_CREATE();
        self::TEST_CREATE();
        self::TEST_RETRIEVE();
        self::TEST_UPDATE();
        self::TEST_DELETE();
    }
    
    static function TEST_CREATE()
    {
        $c = new self();
        $c->setNome(Strings::AleatorioNomePessoa());
        $c->setPeso(rand(50, 190));
        $c->save();
        deb('CREATED: '.strval($c),0);
        return $c;
    }
    
    static function TEST_RETRIEVE()
    {
        $c = new self();
        //$c->setPeso('54');
        
        $list = $c->search();
        //deb($list);
        
        $id_list = array_keys($list);
        //deb($id_list);
        $key = rand(0, sizeof($id_list)-1);
        $id_test = $id_list[$key];
        //deb($id_test);
        $c = new self($id_test);
        //deb($c);
        deb('RETRIEVED: '.strval($c),0);
        return $c;
    }
    
    static function TEST_UPDATE()
    {
        $c = self::TEST_RETRIEVE();
        $c->setNome('Xxxxxxxxxxxxx xx Xxxxx');
        $c->setPeso('999');
        $c->save();
        deb('UPDATED: '.strval($c),0);
        return $c;
    }
    
    static function TEST_DELETE()
    {
        $c = self::TEST_RETRIEVE();
        $c->delete();
        deb('DELETED: '.strval($c),0);
        return $c;
    }
}

?>