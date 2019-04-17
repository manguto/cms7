<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewSiteHome;

class ControlSiteHome extends ControlSite   
{
    
    static function Executar($app)
    {
        { // ROTAS            
            $app->get('/', function () {                
                ViewSiteHome::load("index");
            });
        }        
    }

    
}

?>