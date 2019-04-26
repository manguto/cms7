<?php
namespace manguto\cms5\mvc\control\site;

use manguto\cms5\mvc\view\site\ViewSiteHome;

class ControlSiteHome extends ControlSite
{

    static function RunRouteAnalisys($app)
    {
        { // ROTAS
            $app->get('/', function () {
                ViewSiteHome::load("index");
            });
        }
    }
}

?>