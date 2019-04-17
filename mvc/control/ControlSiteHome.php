<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewSiteHome;

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