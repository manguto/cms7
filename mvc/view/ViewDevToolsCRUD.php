<?php
namespace manguto\manguto\mvc\view;

use manguto\manguto\mvc\model\Models;

class ViewDevCMSPageCRUDTools extends ViewDevTools
{    
    static function crud() {
        {
            $model_array = Models::get_repository_extended_modelnames();
            //deb($model_array);
        }
        parent::load('tools_crud',get_defined_vars());
    }
    
    
    static function crud_model($parameters) {
        //extract parameters
        extract($parameters);
        
        {//modelname            
            $models = Models::get_repository_extended_modelnames();            
            $modelname_show = $models[$modelname];
        }
        
        parent::load('tools_crud_model',get_defined_vars());
    }
    
    
}