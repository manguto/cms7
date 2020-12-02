<?php
namespace manguto\cms7\application\core;

use manguto\cms7\libraries\Sessions;
use application\models\User;
use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\Logger;
use manguto\cms7\libraries\Alert;

class Access
{

    public function __construct()
    {
        Logger::info('Controle de Acesso inicializado');
    }

    // ############################################################################################################################################
    // ############################################################################################################################ LOGIN / LOGOUT
    // ############################################################################################################################################
    /**
     * verifica se as credenciais de usuario informadas sao validas
     * e retorna o id do usuario em caso afirmativo
     * @param string $email
     * @param string $password
     * @return bool|integer
     */
    static function checkUserCredentials(string $email, string $password)
    {
        Logger::info("Verificação de credenciais para o login/senha ($email/**********).");
        $user = User::checkUserCredentials($email, $password);
        //deb($user);
        if ($user !== false) {            
            Logger::success("Verificação de credenciais realizada com sucesso! ($email)");
            $user_id = $user->getId();
            return $user_id;
        }else{            
            Logger::error("As credenciais informadas são inválidas (login: $email)!!!");
            return false;
        }        
    }

    // ############################################################################################################################################
    /**
     * PORTEIRO - verifica se o usuario possui 
     * o perfil informado para liberacao do acesso
     * ou redireciona-o aa pagina de login. 
     * @param array|string $profile [admin,dev,user]
     */
    static function Concierge($profiles){
        if(is_string($profiles)){
            $profiles = [$profiles];
        }else if(!is_array($profiles)){
            throw new Exception("O parâmetro da função ".__METHOD__." deve ser STRING ou ARRAY."); 
        }
        
        Logger::info("CONCIERGE");
        if(self::checkUserProfiles($profiles) == true){            
            Logger::success("ACESSO PERMITIDO");            
        }else{
            Logger::warning("ACESSO NEGADO. O USUÁRIO NÃO POSSUI O PERFIL NECESSÁRIO!!!");
            Alert::Warning('Acesso negado! Contate o Administrador!');
            Controller::HeaderLocation('/');          
        }
    }
    // ############################################################################################################################################
    
    /**
     * verifica se o usuario atual possui um dos perfis
     * especificados pela lista de 'apelidos' informada
     * @param array $required_profile_nickname_array
     * @return bool
     */
    static function checkUserProfiles(array $required_profile_nickname_array):bool
    {
        Logger::info("Verificacao dos perfis do usuario quanto ao(s) perfil(ís): '".implode("', '", $required_profile_nickname_array)."' => ");
        if (sizeof($required_profile_nickname_array) == 0) {
            Logger::warning("Nenhum perfil informado para verificação. Acesso liberado (público).");
            return true;
        } else {
            if (self::checkUserLogged()) {
                foreach ($required_profile_nickname_array as $required_profile_nickname) {
                    $user = self::getSessionUser();                    
                    if($user->checkProfile($required_profile_nickname)===true){
                        Logger::success("Perfil '$required_profile_nickname' encontrado com sucesso no usuario atual!");
                        return true;
                    }
                }
            } else {
                Logger::warning("Nenhum usuário logado no momento.");
                return false;
            }
        }
        Logger::warning("O usuário atual não possui nenhum dos perfis solicitados.");
        return false;
    }

    // ############################################################################################################################################

    /**
     * verifica se existe alguum usuario logado
     *
     * @return bool
     */
    static function checkUserLogged(): bool
    {
        return Sessions::isset(User::SESSION);
    }

    // ############################################################################################################################################
    
    /**
     * define o usuario atualmente logado 
     * @param User $user
     */
    static function setSessionUser_id($user_id)
    {
        Sessions::set(User::SESSION, $user_id);
    }

    // ############################################################################################################################################
    
    /**
     * obtem o usuario logado ou levanta um excecao
     * @return User
     * @throws Exception
     */
    static function getSessionUser():User
    {
        $user_id = Sessions::get(User::SESSION,false);
        if($user_id!==false){
            $user = new User($user_id);
        }else{
            $user = new User();
            $user->setName('Visitante');
        }        
        return $user;
    }

    // ############################################################################################################################################
    
    /**
     * obtem um parametro do usuario atual
     * @param string $attributeName
     * @return mixed|string
     */
    static function getSessionUserAttribute(string $attributeName)
    {
        $user = self::getSessionUser();
        if ($user !== false) {
            $getMethod = 'get' . ucfirst($attributeName);
            return $user->$getMethod();
        } else {
            return 'Nenhum usuário logado';
        }        
    }

    // ############################################################################################################################################
    /*
     * remove registro de usuario logado no sistema
     */
    static function unsetSessionUser()
    {
        Logger::info("Limpeza do usuario realizada (LOGOFF).");
        Sessions::unset(User::SESSION);        
    }

    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
}