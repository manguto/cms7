<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\view\dev\ViewDevUsers;

class ControlDevUsers extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/users', function () {
            self::PrivativeDevZone();
            ControlDevUsers::get_dev_users();
        });

        $app->get('/dev/users/create', function () {
            self::PrivativeDevZone();
            ControlDevUsers::get_dev_users_create();
        });

        $app->post('/dev/users/create', function () {
            self::PrivativeDevZone();
            ControlDevUsers::post_dev_users_create();
        });

        $app->get('/dev/users/:id', function ($id) {
            self::PrivativeDevZone();
            ControlDevUsers::get_dev_user($id);
        });

        $app->get('/dev/users/:id/delete', function ($id) {
            self::PrivativeDevZone();
            ControlDevUsers::get_dev_user_delete($id);
        });

        $app->get('/dev/users/:id/edit', function ($id) {
            self::PrivativeDevZone();
            ControlDevUsers::get_dev_user_edit($id);
        });

        $app->post('/dev/users/:id/edit', function ($id) {
            self::PrivativeDevZone();
            ControlDevUsers::post_dev_user_edit($id);
        });
    }

    static function get_dev_users()
    {   
        $users = User::search();
        ViewDevUsers::get_dev_users($users);
    }

    static function get_dev_users_create()
    {   
        ViewDevUsers::get_dev_users_create();
    }

    static function post_dev_users_create()
    {
        // deb($_POST,0);        
        // fix - form devzoneaccess (checkbox)
        $_POST['devzoneaccess'] = ! isset($_POST['devzoneaccess']) ? 0 : 1;
        // password crypt
        $_POST['password'] = User::password_crypt($_POST['password']);
        // deb($_POST);

        try {
            $user = new User();
            $user->SetData($_POST);
            $user->verifyFieldsToCreateUpdate();
            // deb($user);
            $user->save();
            ProcessResult::setSuccess("Usuário salvo com sucesso!");
            headerLocation("/dev/users");
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation("/dev/users/create");
            exit();
        }
    }

    static function get_dev_user($id)
    {
        $user = new User($id);
        ViewDevUsers::get_dev_user($user);  
    }

    static function get_dev_user_edit($id)
    {
        $user = new User($id);
        //deb($user);
        ViewDevUsers::get_dev_user_edit($user);
    }

    static function post_dev_user_edit($id)
    {
        // checkbox stuff fix        
        $_POST['adminzoneaccess'] = ! isset($_POST['adminzoneaccess']) ? 0 : 1;
        $_POST['devzoneaccess'] = ! isset($_POST['devzoneaccess']) ? 0 : 1;
        
        //deb($_POST);
        try {
            $user = new User($id);
            $user->SetData($_POST);
            //deb($user);
            $user->verifyFieldsToCreateUpdate();
            //deb($user);
            $user->save();
            ProcessResult::setSuccess("Usuário atualizado com sucesso!");
            headerLocation("/dev/users");
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation("/dev/users/create");
            exit();
        }
    }

    static function get_dev_user_delete($id)
    {
        $user = new User($id);
        $user->delete();
        ProcessResult::setSuccess("Usuário removido com sucesso!");
        headerLocation("/dev/users");
        exit();
    }
}

?>