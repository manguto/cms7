<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\ViewSiteLogin;
use manguto\cms5\lib\Exception;

class ControlSiteLogin extends ControlSite
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/login', function () {
            ControlSiteLogin::get_login();
        });
        $app->post('/login', function () {
            ControlSiteLogin::post_login();
        });
        $app->get('/logout', function () {
            ControlSiteLogin::get_logout();
        });
    }

    static function get_login()
    {
        ViewSiteLogin::get_login();
    }

    static function post_login()
    {
        // deb($_POST);
        try {
            User::login($_POST['login'], $_POST['password']);
            headerLocation('/');
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/login');
            exit();
        }
    }

    static function get_logout()
    {
        User::logout();
        headerLocation("/");
        exit();
    }
}

?>