<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewAdminHome;
  

class ControlAdminHome extends ControlAdmin 
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/admin', function () {
            self::PrivativeAdminZone();
            ViewAdminHome::load('index');  
        });
    }
}

?>