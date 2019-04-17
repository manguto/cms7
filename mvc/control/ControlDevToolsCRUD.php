<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewDevCMSPageCRUDTools;
use manguto\manguto\mvc\CMSPageCRUDTools;
   

class ControlDevCMSPageCRUDTools extends ControlDevTools
{

    static function Executar($app)
    {        
        $app->get('/dev/tools/crud', function () {
            self::PrivativeDevZone();
            ViewDevCMSPageCRUDTools::crud();
        });        
        
        $app->get('/dev/tools/crud/:modelname', function ($modelname) {
            self::PrivativeDevZone();
            $pars = CMSPageCRUDTools::set_structure($modelname);
            ViewDevCMSPageCRUDTools::crud_model($pars);
        });
    }    
}

?>