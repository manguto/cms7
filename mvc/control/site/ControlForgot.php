<?php
namespace manguto\cms5\mvc\control\site;
 
use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception; 
use manguto\cms5\mvc\model\UserPasswordRecoveries;
use manguto\cms5\lib\cms\CMSPage;
use manguto\cms5\mvc\view\site\ViewForgot;
use manguto\cms5\mvc\control\ControlSite;
    

class ControlForgot extends ControlSite
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/forgot', function () {
            ControlForgot::get_forgot();
        });
        $app->post('/forgot', function () {
            ControlForgot::post_forgot();
        });
        $app->get('/forgot/sent', function () {
            ControlForgot::get_forgot_sent();
        });
        $app->get('/forgot/reset', function () {
            ControlForgot::get_forgot_reset();
        });
        $app->post('/forgot/reset', function () {
            ControlForgot::post_forgot_reset();
        });
    }

    static function get_forgot()
    {   
        ViewForgot::get_forgot();
    }

    static function post_forgot()
    {
        //deb($_POST);
        try {
            User::getForgot(trim($_POST['email']), false);
            headerLocation('/forgot/sent');
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/forgot');
            exit();
        }
    }

    static function get_forgot_sent()
    {
        {
            $email = User::getForgotEmail();
            $emailInfo = explode('@', $email);
            $emailUrl = $emailInfo[1];
            $emailInfo2 = explode('.', $emailUrl);
            $emailName = ucfirst($emailInfo2[0]);
        }
        ViewForgot::get_forgot_sent($email, $emailUrl, $emailName);
    }

    static function get_forgot_reset()
    {
        // deb($_GET,0);
        $code = $_GET['code'];

        try {
            $user = User::validForgotDecrypt($code);
            $page = new CMSPage();
            $page->setTpl("forgot-reset", [
                'form_action' => '/forgot/reset',
                'name' => $user->getname(),
                'code' => $code
            ]);
        } catch (Exception $e) {
            // deb($e);
            ProcessResult::setError($e);
            headerLocation('/forgot');
            exit();
        }
    }

    static function post_forgot_reset()
    {
        $code = $_POST['code'];

        try {
            $user = User::validForgotDecrypt($code);
            UserPasswordRecoveries::setForgotUsed($user->getrecoveryid());
            $user->setPassword(User::password_crypt($_POST['password']));
            $user->save();
            ViewForgot::post_forgot_reset();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/forgot');
            exit();
        }
    }
}

?>