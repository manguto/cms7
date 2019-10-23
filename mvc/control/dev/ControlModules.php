<?php
namespace manguto\cms5\mvc\control\dev;


use manguto\cms5\mvc\control\ControlDev;
use manguto\cms5\mvc\view\dev\ViewModules;
use manguto\cms5\mvc\model\User_module;

class ControlModules extends ControlDev
{
    
    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/modules', function () {
            self::PrivativeDevZone();
            ViewModules::modules();
        });
        
        $app->get('/dev/modules/:action/:key', function ($action,$key) {
            self::PrivativeDevZone();
            /*deb($action,0);
            deb($key);/**/
            {
                $key_array = explode('_', $key);
                $user_id = $key_array[0];
                $module_id = $key_array[1];
                $nature = $key_array[2];
            }
            
            {//remocao de todos os perfis do usuario para um determinado modulo
                $query = " \$user_id==$user_id && \$module_id==$module_id ";
                $um_array = (new User_module())->search($query);
                if(sizeof($um_array)>0){
                    foreach ($um_array as $um){
                        $um->delete();
                    }
                }
            }
            
            {//insercao do perfil informado
                if($action=='set'){
                    $um = new User_module();
                    $um->setUser_id($user_id);
                    $um->setModule_id($module_id);
                    $um->setNature($nature);
                    $um->save();                 
                }
            }                                    

            headerLocation('/dev/modules');
        });
        
    }
}

?>