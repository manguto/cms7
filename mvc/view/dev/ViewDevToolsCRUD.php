<?php
namespace manguto\cms5\mvc\view\dev;

use manguto\cms5\mvc\model\Model_Helper;

class ViewDevToolsCRUD extends ViewDevTools
{    
    static function crud() {
        {
            $model_array = Model_Helper::get_repository_extended_tablenames();
            //deb($model_array);
        }
        parent::load('tools_crud',get_defined_vars());
    }
    
    
    static function crud_model($parameters) {
        //extract parameters
        extract($parameters);
        
        {//tablename            
            $models = Model_Helper::get_repository_extended_tablenames();            
            $tablename_show = $models[$tablename];
        }
        
        parent::load('tools_crud_model',get_defined_vars());
    }
    
    
}