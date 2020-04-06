<?php
namespace manguto\cms7\lib\cms;

use cms\model\User;
use manguto\cms7\lib\Sessions;

 
class CMSAccessManagement
{

    // ############################################################################################################################################
    // ############################################################################################################################ LOGIN / LOGOUT
    // ############################################################################################################################################
    /**
     * verifica se as credenciais de usuario informadas sao validas
     *
     * @param string $login
     * @param string $password
     * @return bool
     */
    static function checkUserCredentials(string $login, string $password): bool
    {
        $user = User::checkUserCredentials($login, $password);
        if ($user !== false) {
            CMSAccessManagement::setSessionUser($user);
            return true;
        }
        return false;
    }

    // ############################################################################################################################################
    
    /**
     * verifica se existe um usuario logado no sistema
     *
     * @return bool
     */
    static function checkUserLogged(): bool
    {
        return Sessions::isset(User::SESSION);
    }

    // ############################################################################################################################################
    static function checkUserLoggedAdmin(): bool
    {
        if (CMSAccessManagement::checkUserLogged()) {
            $user = CMSAccessManagement::getSessionUser();
            $return = $user->checkProfile('admin',false);
        } else {
            $return = false;
        }
        return $return;
    }

    // ############################################################################################################################################
    static function checkUserLoggedDev(): bool
    {
        if (CMSAccessManagement::checkUserLogged()) {
            $user = CMSAccessManagement::getSessionUser();
            $return = $user->checkProfile('dev',false);
        } else {
            $return = false;
        }
        return $return;
    }

    // ############################################################################################################################################
    static function setSessionUser(User $user)
    {   
        Sessions::set(User::SESSION, $user);
        //Sessions::set(User::SESSION . '_show', "$user");
        //Sessions::set(User::SESSION . '_modules', User_module::getUserModules($user->getId()));        
        //CMSAccessManagement::setMultipleSystemSessionUser($user);
    }

    // ###########################################################################################################################################
    // ########################################################################################################################## MULTIPLE SYSTEM
    // ###########################################################################################################################################
    
    /**
     * Realizar o registro na sessão para os modulos (sistemas)
     * cadastrados nos quais o usuario pode ter acesso
     *
     * @param User $user
     */
    /*static private function setMultipleSystemSessionUser($user)
    {
        $user_module_array = (new User_module())->search(" \$user_id=={$user->getId()} ");
        // deb($user_module_array);
        { // profile get/set
            $profiles = [];
            $SIS_FOLDERNAME_array = [];
            foreach ($user_module_array as $user_module) {
                {
                    $SIS_FOLDERNAME_temp = $user_module->getModule();
                    $nature = $user_module->getNature();
                }
                $SIS_FOLDERNAME_array[] = $SIS_FOLDERNAME_temp;
                $profiles[] = $nature;
            }
            $user->setProfiles($profiles,true);
            //deb($user);
        }
        
        foreach ($SIS_FOLDERNAME_array as $SIS_FOLDERNAME_temp) {
            
            Sessions::set(User::SESSION, $user, false, $SIS_FOLDERNAME_temp);
            //Sessions::set(User::SESSION . '_show', "$user", false, $SIS_FOLDERNAME_temp);
            //Sessions::set(User::SESSION . '_modules', User_module::getUserModules($user->getId()), false, $SIS_FOLDERNAME_temp);
        }
    }*/

    // ############################################################################################################################################
    static function getSessionUser()
    {
        return Sessions::get(User::SESSION, false);
    }

    // ############################################################################################################################################
    static function getSessionUserDirectAttribute($attribute)
    {
        $user = CMSAccessManagement::getSessionUser();
        if ($user !== false) {
            $getMethod = 'get' . ucfirst($attribute);
            $attribute = $user->$getMethod();
        } else {
            $attribute = '';
        }
        return $attribute;
    }

    // ############################################################################################################################################
    static function clearSessionUser()
    {
        Sessions::unset(User::SESSION);
    }
    
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
}

?>