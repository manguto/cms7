<?php
namespace manguto\cms5\mvc\control\site;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\site\ViewSiteRegister;
use manguto\cms5\lib\Exception; 
use manguto\cms5\lib\Sessions;
use manguto\cms5\mvc\control\admin\ControlAdminProfile;

class ControlSiteRegister extends ControlSite
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/register', function () {
            ControlSiteRegister::get_register();
        });
        $app->post('/register', function () {
            ControlSiteRegister::post_register();
        });
    }

    static function get_register()
    {
        if (Sessions::isset(ControlAdminProfile::key)) {
            $registerFormValues = Sessions::get(ControlAdminProfile::key);
            Sessions::unset(ControlAdminProfile::key);
        } else {
            $registerFormValues = [
                'name' => '',
                'email' => '',
                'phone' => ''
            ];
        }
        ViewSiteRegister::get_register($registerFormValues);
    }

    static function post_register()
    {
        throw new Exception("A criação de novos usuários está desabilitada até segunda ordem. Obrigado!");

        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // -------------montagem do usuario
        $user = new User();

        $user->SetData([
            'adminzoneaccess' => 0,
            'name' => $_POST['name'],
            'login' => $_POST['email'],
            'email' => $_POST['email'],
            'password' => User::password_crypt($_POST['password']),
            'phone' => $_POST['phone']
        ]);
        // deb($user,0);
        // ------------- verificacao de parametros enviados
        try {
            $user->verifyFieldsToCreateUpdate();
            $user->save();
            ProcessResult::setSuccess("Cadastro realizado com sucesso!<br/>Seja bem vindo(a) à nossa plataforma!!");
            User::login($_POST['email'], $_POST['password']);
            headerLocation('/');
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/login');
            exit();
        }
    }
}

?>