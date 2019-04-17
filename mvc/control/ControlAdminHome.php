<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewAdminHome;
  

class ControlAdminHome extends ControlAdmin 
{

    static function Executar($app)
    {
        $app->get('/admin', function () {
            self::PrivativeAdminZone();
            ViewAdminHome::load('index');  
        });
    }
}

?>