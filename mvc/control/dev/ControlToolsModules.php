<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\ViewDev;
use manguto\cms5\mvc\model\DevToolsModules;

   

class ControlToolsModules extends ControlTools
{

    static function RunRouteAnalisys($app)
    {        
        $app->get('/dev/tools/modules', function () {
            self::PrivativeDevZone();
            ViewDev::load('tools_modules',get_defined_vars());
        });        
        
        $app->post('/dev/tools/modules', function () {
            self::PrivativeDevZone(); 
            //deb($_POST);
            {
                $platform = $_POST['platform'];
                $ModuleName = ucfirst($_POST['modulename']);
            }
            {
                $results = DevToolsModules::GenerateFile($platform, $ModuleName);
            }
            ViewDev::load('tools_modules_result',get_defined_vars());
        });
    }    
}
 
?>