<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewDevCMSPageCRUDTools;
use manguto\cms5\mvc\CMSPageCRUDTools;
   

class ControlDevCMSPageCRUDTools extends ControlDevTools
{

    static function Executar($app)
    {        
        $app->get('/dev/tools/crud', function () {
            self::PrivativeDevZone();
            ViewDevCMSPageCRUDTools::crud();
        });        
        
        $app->get('/dev/tools/crud/:tablename', function ($tablename) {
            self::PrivativeDevZone();
            $pars = CMSPageCRUDTools::set_structure($tablename);
            ViewDevCMSPageCRUDTools::crud_model($pars);
        });
    }    
}

?>