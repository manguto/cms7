<?php
namespace manguto\cms5\mvc\control\admin;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\view\admin\ViewAdminUsers;
 

class ControlAdminUsers extends ControlAdmin
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/admin/users', function () {
            self::PrivativeAdminZone();
            ControlAdminUsers::get_admin_users();
        });

        $app->get('/admin/users/create', function () {
            self::PrivativeAdminZone();
            ControlAdminUsers::get_admin_users_create();
        });

        $app->post('/admin/users/create', function () {
            self::PrivativeAdminZone();
            ControlAdminUsers::post_admin_users_create();
        });

        $app->get('/admin/users/:id', function ($id) {
            self::PrivativeAdminZone();
            self::PrivateCrudPermission('view',$id);
            ControlAdminUsers::get_admin_user($id);
        });

        $app->get('/admin/users/:id/delete', function ($id) {
            self::PrivativeAdminZone();
            self::PrivateCrudPermission('delete',$id);
            ControlAdminUsers::get_admin_user_delete($id);
        });

        $app->get('/admin/users/:id/edit', function ($id) {
            self::PrivativeAdminZone();
            self::PrivateCrudPermission('edit',$id);
            ControlAdminUsers::get_admin_user_edit($id);
        });

        $app->post('/admin/users/:id/edit', function ($id) {
            self::PrivativeAdminZone();
            User::PrivateCrudPermission('edit',$id);
            ControlAdminUsers::post_admin_user_edit($id);
        });
    }

    static function get_admin_users()
    {   
        $users = User::getList('',false);        
        
        ViewAdminUsers::get_admin_users($users);
    }

    static function get_admin_users_create()
    {   
        ViewAdminUsers::get_admin_users_create();
    }

    static function post_admin_users_create()
    {
        // deb($_POST,0);        
        // fix - form adminzoneaccess (checkbox)
        $_POST['adminzoneaccess'] = ! isset($_POST['adminzoneaccess']) ? 0 : 1;
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
            headerLocation("/admin/users");
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation("/admin/users/create");
            exit();
        }
    }

    static function get_admin_user($id)
    {
        $user = new User($id);
        ViewAdminUsers::get_admin_user($user);
    }

    static function get_admin_user_edit($id)
    {
        $user = new User($id);
        ViewAdminUsers::get_admin_user_edit($user);
    }

    static function post_admin_user_edit($id)
    {
        // fix - form adminzoneaccess (checkbox)
        $_POST['adminzoneaccess'] = ! isset($_POST['adminzoneaccess']) ? 0 : 1;
        try {
            $user = new User($id);
            $user->SetData($_POST);
            $user->verifyFieldsToCreateUpdate();
            $user->save();
            ProcessResult::setSuccess("Usuário atualizado com sucesso!");
            headerLocation("/admin/users");
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation("/admin/users/create");
            exit();
        }
    }

    static function get_admin_user_delete($id)
    {
        $user = new User($id);
        $user->delete();
        ProcessResult::setSuccess("Usuário removido com sucesso!");
        headerLocation("/admin/users");
        exit();
    }
}

?>