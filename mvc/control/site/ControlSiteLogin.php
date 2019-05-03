<?php
namespace manguto\cms5\mvc\control\site;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\site\ViewSiteLogin;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Logs;

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
        Logs::set("Exibição da tela de login");
        ViewSiteLogin::get_login();
    }

    static function post_login()
    {
        Logs::set("Parametros informados para o LOGIN recebidos...");

        try {
            
            User::login($_POST['login'], $_POST['password']);
            Logs::set('Login autorizado! Redirecionamento para página principal solicitado...');
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
        Logs::set('Logout realizado');
        headerLocation("/");
        exit();
    }
}

?>