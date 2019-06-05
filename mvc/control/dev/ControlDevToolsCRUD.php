<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\lib\cms\CMSPageCRUDTools;
use manguto\cms5\mvc\view\dev\ViewDev;
use manguto\cms5\lib\model\Model_Helper;

   

class ControlDevToolsCRUD extends ControlDevTools
{

    static function RunRouteAnalisys($app)
    {        
        $app->get('/dev/tools/crud', function () {
            self::PrivativeDevZone();
            {
                $model_array = Model_Helper::get_repository_extended_tablenames();
                //deb($model_array);
            }
            ViewDev::load('tools_crud',get_defined_vars());
        });        
        
        $app->get('/dev/tools/crud/:tablename', function ($tablename) {
            self::PrivativeDevZone();            
            {
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                $results = CMSPageCRUDTools::set_structure($tablename);
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            }
            {   
                $tablename_show = $models[$tablename];
            }            
            ViewDev::load('tools_crud_model',get_defined_vars());
        });
    }    
}

?>