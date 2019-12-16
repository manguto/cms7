<?php
namespace manguto\cms5\mvc\view\dev;

use manguto\cms5\mvc\view\ViewDev;
use manguto\cms5\mvc\model\User;
use manguto\cms5\mvc\model\User_module;
use manguto\cms5\lib\Diretorios;

class ViewModules extends ViewDev
{

    static function modules()
    {
        
        $diretorios = Diretorios::obterArquivosPastas('../', false, false, true);
        //deb($diretorios);
        $modules=[];
        foreach ($diretorios as $dir){
            $dir = str_replace('../', '', $dir);
            $dir = str_replace('/', '', $dir);
            $esq = SIS_FOLDERNAME.'_';
            $dir_esq = substr($dir, 0,strlen($esq));
            if($dir_esq==$esq){
                $modules[] = $dir;
            }
        }
        //deb($modules);  
        
        //$modules = (new Module())->search();
        //deb($modules);
        
        $users = (new User())->search();
        // deb($users);
        $profiles = [
            'user' => [
                'show' => 'USER',
                'title' => 'Usuário',
                'class' => 'usuario'
            ],
            'admin' => [
                'show' => 'ADM',
                'title' => 'Administrador',
                'class' => 'admin'
            ],
            'dev' => [
                'show' => 'DEV',
                'title' => 'Desenvolvedor',
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
                $module = $user_module->getModule();
                $nature = $user_module->getNature();
                $user_module_set[$user_id][$module][$nature] = true;
            }
        }
        // deb($user_module_set);

        self::load('modules', get_defined_vars());
    }
}