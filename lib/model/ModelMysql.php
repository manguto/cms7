<?php
namespace manguto\cms5\lib\model;

use manguto\cms5\lib\database\mysql\DatabaseMysql;

class ModelMysql extends Model
{
    
    public function __construct(int $id = 0)
    {
        parent::__construct($id);
        
        $this->load();
    }
    
    
    
    private function load()
    {
        DatabaseMysql::load($this);
    }
    
    public function save()
    {
        DatabaseMysql::save($this);
    }
    
    public function delete()
    {
        DatabaseMysql::delete($this);
    }
    
    public function LoadReferences(){
        
    }
    
}

?>