<?php
namespace manguto\cms5\mvc\control\site;
 
use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\view\ViewSiteForgot;
use manguto\cms5\mvc\model\UserPasswordRecoveries;
use manguto\cms5\lib\cms\CMSPage;

class ControlSiteForgot extends ControlSite
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/forgot', function () {
            ControlSiteForgot::get_forgot();
        });
        $app->post("/forgot", function () {
            ControlSiteForgot::post_forgot();
        });
        $app->get('/forgot/sent', function () {
            ControlSiteForgot::get_forgot_sent();
        });
        $app->get('/forgot/reset', function () {
            ControlSiteForgot::get_forgot_reset();
        });
        $app->post('/forgot/reset', function () {
            ControlSiteForgot::post_forgot_reset();
        });
    }

    static function get_forgot()
    {
        ViewSiteForgot::get_forgot();
    }

    static function post_forgot()
    {
        // deb($_POST);
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
        ViewSiteForgot::get_forgot_sent($email, $emailUrl, $emailName);
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
            ViewSiteForgot::post_forgot_reset();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/forgot');
            exit();
        }
    }
}

?>