<?php
namespace manguto\cms5\mvc\control\admin;

use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Sessions;
use manguto\cms5\mvc\control\Control;
use manguto\cms5\mvc\view\admin\ViewProfile;
use manguto\cms5\mvc\control\ControlAdmin;
 
class ControlProfile extends ControlAdmin
{

    const key = 'form_temp_values';
    
    static function RunRouteAnalisys($app)
    {     
        $app->get('/admin/profile', function () {
            Control::PrivativeAdminZone();
            ControlProfile::get_admin_profile();
        });
        $app->post('/admin/profile', function () {
            Control::PrivativeAdminZone();
            ControlProfile::post_admin_profile();
        });
        $app->get('/admin/profile/change-password', function () {
            Control::PrivativeAdminZone();
            ControlProfile::get_admin_profile_change_password();
        });
        $app->post('/admin/profile/change-password', function () {
            Control::PrivativeAdminZone();
            ControlProfile::post_admin_profile_change_password();
        });
    }

    static function get_admin_profile()
    {        
        $user = User::getSessionUser();
        ViewProfile::get_admin_profile($user);
    }

    static function post_admin_profile()
    {

        $user = User::getSessionUser();
        { // --- PARAMETERS VERIFICATION & CERTIFICATION
            $_POST['adminzoneaccess'] = $user->getadminzoneaccess();
            $_POST['password'] = $user->getPassword();
            if (checkUserLoggedAdmin() === false) {
                $_POST['login'] = $_POST['email'];
            }
        }
        $user->SET_DATA($_POST);
        try {
            $user->verifyFieldsToCreateUpdate();
            $user->save();
            ProcessResult::setSuccess('Usuário salvo com sucesso!');
            headerLocation('/admin/profile');
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);            
            Sessions::set(ControlProfile::key,$_POST);            
            headerLocation('/admin/profile');
            exit();
        }
    }

    static function get_admin_profile_change_password()
    {   
        ViewProfile::get_admin_profile_change_password();
    }

    static function post_admin_profile_change_password()
    {
        $user = User::getSessionUser();
        try {
            $current_pass = isset($_POST['current_pass']) ? $_POST['current_pass'] : '';
            $new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : '';
            $new_pass_confirm = isset($_POST['new_pass_confirm']) ? $_POST['new_pass_confirm'] : '';
            $user->verifyPasswordUpdate($current_pass, $new_pass, $new_pass_confirm);
            $user->setPassword(User::password_crypt($new_pass));
            $user->save();
            ProcessResult::setSuccess('Senha alterada com sucesso!!!');
            headerLocation('/admin/profile');
            exit();
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/admin/profile/change-password');
            exit();
        }
    }
}

?>