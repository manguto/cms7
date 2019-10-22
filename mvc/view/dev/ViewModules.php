<?php
namespace manguto\cms5\mvc\view\dev;

use manguto\cms5\mvc\view\ViewDev;
use manguto\cms5\mvc\model\Module;
use manguto\cms5\mvc\model\User;
use manguto\cms5\mvc\model\User_module;

class ViewModules extends ViewDev
{

    static function modules()
    {
        $modules = (new Module())->search();
        // deb($modules);
        $users = (new User())->search();
        // deb($users);
        $profiles = [
            'user' => [
                'show' => 'Usuário',
                'class' => 'usuario'
            ],
            'admin' => [
                'show' => 'Administrador',
                'class' => 'admin'
            ],
            'dev' => [
                'show' => 'Desenvolvedor',
                'class' => 'dev'
            ]
        ];
        // deb($profiles);
        {
            $user_module_set = [];
            $user_module_array = (new User_module())->search();
            // deb($user_module_array);
            foreach ($user_module_array as $user_module) {
                $user_id = $user_module->getUser_id();
                $module_id = $user_module->getModule_id();
                $nature = $user_module->getNature();
                $user_module_set[$user_id][$module_id][$nature] = true;
            }
        }
        // deb($user_module_set);

        self::load('modules', get_defined_vars());
    }
}