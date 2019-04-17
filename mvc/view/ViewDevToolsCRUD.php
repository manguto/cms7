<?php
namespace manguto\cms5\mvc\view;

use manguto\cms5\mvc\model\Models;

class ViewDevCMSPageCRUDTools extends ViewDevTools
{    
    static function crud() {
        {
            $model_array = Models::get_repository_extended_tablenames();
            //deb($model_array);
        }
        parent::load('tools_crud',get_defined_vars());
    }
    
    
    static function crud_model($parameters) {
        //extract parameters
        extract($parameters);
        
        {//tablename            
            $models = Models::get_repository_extended_tablenames();            
            $tablename_show = $models[$tablename];
        }
        
        parent::load('tools_crud_model',get_defined_vars());
    }
    
    
}