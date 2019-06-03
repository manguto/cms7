<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewDevToolsCRUD;
use manguto\cms5\lib\cms\CMSPageCRUDTools;

   

class ControlDevToolsCRUD extends ControlDevTools
{

    static function RunRouteAnalisys($app)
    {        
        $app->get('/dev/tools/crud', function () {
            self::PrivativeDevZone();
            ViewDevToolsCRUD::crud();
        });        
        
        $app->get('/dev/tools/crud/:tablename', function ($tablename) {
            self::PrivativeDevZone();
            $pars = CMSPageCRUDTools::set_structure($tablename);
            ViewDevToolsCRUD::crud_model($pars);
        });
    }    
}

?>