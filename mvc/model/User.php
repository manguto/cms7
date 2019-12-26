<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\Safety;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Sessions;
use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\Logs;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelTrait;

class User extends Model
{

    use ModelTrait;
    use ModelRepository;

    const SESSION = "User";

    const FORGOT_EMAIL = "UserEmail";

    const adminzoneaccess = [
        0 => 'NÃO',
        1 => 'SIM'
    ]; 

    const devzoneaccess = [
        0 => 'NÃO',
        1 => 'SIM'
    ];

    public function __construct($id = 0)
    {
        // atributos basicos (fundamentais)
        // deb($id,0);
        $this->SetFundamentalAttributes($id);
        // deb($this);

        // definicao dos atributos deste modelo
        $this->defineAttributes();
        // deb($this);

        // carregamento de atributos do banco de dados
        if ($id != 0) {
            $this->load();
            // deb($this);
        }
        // verifica corretude da estrutura dos dados
        parent::checkSetStruct();
    }

    /**
     * !IMPORTANT
     * Função para defniicao do atributos do modelo!
     */
    private function defineAttributes()
    {
        Logs::set(Logs::TYPE_INFO, "Definição dos ATRIBUTOS do modelo <b>" . $this->GetClassName() . "</b>.");

        $attributes_data = [
            'name' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64
            ],
            'login' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 24
            ],
            'password' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 32,
                'encrypted' => true
            ],
            'email' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64,
                'nature' => ModelAttribute::NATURE_EMAIL
            ],
            'phone' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64
            ],
            'adminzoneaccess' => [
                'type' => ModelAttribute::TYPE_BOOLEAN,
                'value' => 0
            ],
            'devzoneaccess' => [
                'type' => ModelAttribute::TYPE_BOOLEAN,
                'value' => 0
            ]
        ];

        parent::SetAttributes($attributes_data);
    }

    static function checkUserLogged(): bool
    {
        return Sessions::isset(User::SESSION);
    }

    static function checkUserLoggedAdmin(): bool
    {
        if (User::checkUserLogged()) {
            $user = self::getSessionUser();
            $return = $user->getAdminzoneaccess();
        } else {
            $return = false;
        }
        return $return;
    }

    static function checkUserLoggedDev(): bool
    {
        if (User::checkUserLogged()) {
            $user = self::getSessionUser();
            $return = $user->getDevzoneaccess();
        } else {
            $return = false;
        }
        return $return;
    }

    static public function initialize()
    {
        Logs::set(Logs::TYPE_INFO, 'Verificação/Definição de usuário administrador.');

        { // usuario padrao fundamental!
          // --------------------------------------------------- set user admin
            $admin = new User();
            $admin->setname('Administrador');
            $admin->setlogin('adm');
            // $admin->setpassword('21232f297a57a5a743894a0e4a801fc3'); // admin
            $admin->setpassword(self::password_crypt('mad')); // admin
            $admin->setemail('adm@suporte.com');
            $admin->setphone('(XX) X.XXXX-XXXX');
            $admin->setadminzoneaccess(1);
            $admin->setdevzoneaccess(1);
        }

        { // obtencao dos usuarios com o login 'admin'
            {
                // $query = 'SELECT * FROM user WHERE login=:login ';
                $query = ' $login="{login}" ';
            }
            $quantUsuarios = ($admin->length($query, $admin->getParameters('login')));
            // deb($quantUsuarios);
        }

        //deb($quantUsuarios);
        if ($quantUsuarios == 0) {
            Logs::set(Logs::TYPE_INFO, 'Criação de usuário administrador necessária...');
            $admin->save();
            Logs::set(Logs::TYPE_INFO, 'Usuário administrador criado com sucesso!');
            // ---------------------------------------------------
        } else {
            Logs::set(Logs::TYPE_INFO, 'Usuário administrador encontrado.');
        }
    }

    static function login($login, $password)
    {
        Logs::set(Logs::TYPE_INFO, 'Validação de login/senha de usuário informados...');
        // deb($login,0); deb($password,0); deb(User::password_crypt($password));

        { // verificacao do repositorio do susuarios
            User::initialize();
        }

        {

            { // usuario existe
                {
                    // $query = ' SELECT * FROM user WHERE login=:login ';
                    $query = " \$login=='$login' ";
                    // deb($query,0);
                }
                $results = (new self())->search($query);
                // deb($results);

                if (count($results) === 0) {
                    throw new Exception("Login não encontrado e/ou senha inválida.");
                } else {
                    Logs::set(Logs::TYPE_INFO, "Login encontrado ($login).");
                }
            }
            { // usuario e senha existem
                {
                    // $query = ' SELECT * FROM user WHERE login=:login AND password=:password ';
                    $query = " \$login=='$login' && \$password=='" . User::password_crypt($password) . "' ";
                    // deb($query,0);
                }
                $results = (new self())->search($query);
                // deb($results);

                if (count($results) === 0) {
                    throw new Exception("Senha inválida e/ou login não encontrado.");
                } else {
                    Logs::set(Logs::TYPE_INFO, "Login/senha encontrados ($login/******).");
                }
            }
        }
        // deb($results);
        $user = array_shift($results);
        // deb($user);
        User::setSessionUser($user);
    }

    static function password_crypt(string $passwordRaw)
    {
        // return password_hash($passwordRaw,PASSWORD_DEFAULT,["cost"=>12]);
        return md5($passwordRaw);
    }

    static function setSessionUser($user)
    {
        self::setMultipleSystemSessionUser($user);

        Sessions::set(User::SESSION, $user);
        Sessions::set(User::SESSION.'_show', "$user");
        Sessions::set(User::SESSION.'_modules', User_module::obterUsuarioModulosPermitidos($user->getId()));
        
        Logs::Start('LOGIN EFETUADO');
        Logs::set(Logs::TYPE_INFO, "Usuário logado e definido na sessão ($user).");
    }

    /**
     * Realizar o registro na sessão para os modulos (sistemas) cadastrados nos quais o usuario pode ter acesso
     *
     * @param User $user
     */
    static private function setMultipleSystemSessionUser($user)
    {
        /*
         * debc($_SESSION,0);
         * deb($user);/*
         */
        $user_id = $user->getId();
        $query = " \$user_id==$user_id ";
        $user_module_array = (new User_module())->search($query);
        //debs($user_module_array);
        
        {//profile get/set
            $profiles = [];
            $modules = [];
            foreach ($user_module_array as $user_module) {
                //deb("$user_module");
                {
                    $module = $user_module->getModule();
                    $nature = $user_module->getNature();
                }
                $modules[] = $module; 
                $profiles[$nature] = $nature; 
            }
            /*deb($modules,0);
            deb($profiles);/**/
        }
        
        foreach ($modules as $module) {

            {//USER PROFILE SET!
                //======================================= DEV
                if(isset($profiles['dev'])){
                    $user->setDevzoneaccess(true);
                }else{
                    $user->setDevzoneaccess(false);
                }
                //======================================= ADMIN
                if(isset($profiles['admin'])){
                    $user->setAdminzoneaccess(true);
                }else{
                    $user->setAdminzoneaccess(false);
                }
                //======================================= USER
                if(!isset($profiles['dev']) && !isset($profiles['admin']) && !isset($profiles['user'])){
                    continue;
                }
            }            
            {//SIS_FOLDERNAME                
                $SIS_FOLDERNAME = $module;
                //deb($SIS_FOLDERNAME);
            }
            Sessions::set(User::SESSION, $user, false, $SIS_FOLDERNAME);
            Sessions::set(User::SESSION.'_show', "$user", false, $SIS_FOLDERNAME);
            Sessions::set(User::SESSION.'_modules', User_module::obterUsuarioModulosPermitidos($user->getId()), false, $SIS_FOLDERNAME);
            
            
        }
    }

    static function getSessionUser()
    {
        return Sessions::get(User::SESSION, false);
    }

    static function getSessionUserDirectAttribute($attribute)
    {
        $user = self::getSessionUser();
        if ($user !== false) {
            $getMethod = 'get' . ucfirst($attribute);
            $attribute = $user->$getMethod();
        } else {
            $attribute = '';
        }
        return $attribute;
    }

    static function logout()
    {
        //Sessions::unset(User::SESSION);
        Sessions::Reset(false);
        Logs::set(Logs::TYPE_INFO, 'Usuário des-logado e indefinido na sessão.');
    }

    /**
     * verifica se o login existe
     *
     * @throws Exception
     * @return bool
     */
    public function checkLoginExist(): bool
    {
        {
            // $query = ' SELECT * FROM user WHERE ( login=:login AND id!=:id ) ';
            $query = ' $login={login} && $id!={id} ';
        }
        $result = User::search($query, $this->getParameters([
            'login',
            'id'
        ]));
        // deb($result);
        if (sizeof($result) == 1) {
            return true;
        } else if (sizeof($result) == 0) {
            return false;
        } else {
            throw new Exception("Existem mais de um usuário com o mesmo login. Contate o administrador.");
        }
    }

    public function checkEmailExist(): bool
    {
        {
            // $query = ' SELECT * FROM user WHERE ( email=:email AND id!=:id ) ';
            $query = ' $email={email} && $id!={id} ';
        }
        $result = User::search($query, $this->getParameters([
            'email',
            'id'
        ]));

        // deb($result);
        if (sizeof($result) == 1) {
            return true;
        } else if (sizeof($result) == 0) {
            return false;
        } else {
            throw new Exception("Existem mais de um usuário com o mesmo login. Contate o administrador.");
        }
    }

    static function getForgot($email, $adminzoneaccess = true)
    {
        // deb($email);
        User::setForgotEmail($email);

        {
            // $query = ' SELECT * FROM user WHERE ( email=:email ) ';
            $query = ' $email={email} ';
        }
        $result = User::search($query, $this->getParameters([
            'email'
        ]));

        // deb($results);

        if (count($results) == 0) {
            throw new Exception("Não foi possível recuperar a sua senha. Contate o administrador informando seu problema e e-mail.");
        } else {

            $user = array_shift($results);
            // deb($user);
            // if(false)$user = new User();
            $userPasswordRecoveries = new UserPasswordRecoveries();
            $userPasswordRecoveries->setid($user->getId());
            $userPasswordRecoveries->setip($_SERVER["REMOTE_ADDR"]);
            $userPasswordRecoveries->setdatetime(time());
            $userPasswordRecoveries->setdeadline(time() + UserPasswordRecoveries::deadline);
            // deb($userPasswordRecoveries);
            $userPasswordRecoveries->save();
            // deb($userPasswordRecoveries);

            // ==========================================================================================================
            // ========================================== cifragem =====================================================
            // ==========================================================================================================
            $recoveryid_encrypted = Safety::encrypt($userPasswordRecoveries->getId());
            // deb($recoveryid_encrypted,0); deb(Safety::decrypt($recoveryid_encrypted));
            // ==========================================================================================================
            // ==========================================================================================================
            // ==========================================================================================================

            $link = SIS_URL . "/forgot/reset?code=" . $recoveryid_encrypted;
            // deb($link);

            /*
             * $mailer = new CMSMailer($user->getemail(), $user->getname(), "Redefinição de senha do(a) " . SIS_NAME, "forgot", array(
             * "name" => $user->getname(),
             * "link" => $link
             * ));
             */
            throw new Exception("Nenhum servidor de e-mail configurado. Contate o Administrador e informe-o desta necessidade.");

            if (! $mailer->send()) {
                throw new Exception("Não foi possível enviar o e-mail de recuperação.<br/>Aguarde alguns instantes e tente novamente.<br/>Caso o problema persista, contate o administrador.");
            }

            return $user->GET_DATA($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
        }
    }

    static function validForgotDecrypt(string $recoveryid_encrypted): Model
    {

        // deb($recoveryid_encrypted);
        // ==========================================================================================================
        // ========================================== decifragem ====================================================
        // ==========================================================================================================
        $recoveryid = Safety::decrypt($recoveryid_encrypted);
        // ==========================================================================================================
        // ==========================================================================================================
        // ==========================================================================================================
        // deb($recoveryid);

        // $results = LocalDatabase::run(" SELECT * FROM userpasswordrecoveries WHERE userpasswordrecoveriesid='$recoveryid' ");
        // $results = Repository::getRepository('userpasswordrecoveries', " \$userpasswordrecoveriesid=='$recoveryid' ");
        // deb($results);
        throw new Exception("");

        if (count($results) === 0) {
            throw new Exception("Recuperação inválida (identificador incorreto ou senha já recuperada).");
        } else {
            $userpasswordrecoveries = array_shift($results);
            if ($userpasswordrecoveries->DeadlineValid()) {
                $id = $userpasswordrecoveries->getid();
                $user = new User($id);
                $user->setrecoveryid($recoveryid);
                $user->save();
            } else {
                throw new Exception("Recuperação inválida (intervalo máximo de tempo ultrapassado).");
            }
        }
        return $user;
    }

    // ------------------------------------------------------------- ForgotEmail
    static function setForgotEmail($email)
    {
        // deb($msg);
        // $__SESSION[SIS_ABREV][User::FORGOT_EMAIL] = $email;
        Sessions::set(User::FORGOT_EMAIL, $email);
    }

    static function getForgotEmail()
    {
        // $cond1 = isset($__SESSION[SIS_ABREV][User::FORGOT_EMAIL]);
        $cond1 = Sessions::isset(User::FORGOT_EMAIL);

        // $cond2 = $__SESSION[SIS_ABREV][User::FORGOT_EMAIL] !== NULL;
        $cond2 = Sessions::get(User::FORGOT_EMAIL) !== NULL;

        if ($cond1 && $cond2) {
            // $msg = $__SESSION[SIS_ABREV][User::FORGOT_EMAIL];
            $msg = Sessions::get(User::FORGOT_EMAIL);
        } else {
            $msg = User::clearForgotEmail();
        }
        return $msg;
    }

    static function clearForgotEmail()
    {
        // deb("CLEAR!",0);
        // unset($__SESSION[SIS_ABREV][User::FORGOT_EMAIL]);
        Sessions::unset(User::FORGOT_EMAIL);
    }

    /**
     * Verifica se os campos informados ($_POST) podem ser utilizados em um usuario para criacao ou atualizacao
     *
     * @throws Exception
     */
    public function verifyFieldsToCreateUpdate()
    {

        // name
        if ($this->getname() == '') {
            throw new Exception("Preencha o seu nome.");
        }
        // email
        if ($this->getemail() == '') {
            throw new Exception("Preencha o seu email.");
        }
        // login
        if ($this->getlogin() == '') {
            throw new Exception("Preencha o seu login.");
        }

        // login exists
        if ($this->checkLoginExist()) {
            throw new Exception("O Login '<b>" . $this->getLogin() . "</b>' já se encontra em uso.<br/> Preencha outro valor e tente novamente.");
        }
        // email exists
        if ($this->checkEmailExist()) {
            throw new Exception("O E-mail '<b>" . $this->getEmail() . "</b>' já se encontra em uso.<br/> Preencha outro valor e tente novamente.");
        }

        // password
        if ($this->getpassword() == '') {
            throw new Exception("Preencha a sua senha.");
        }
    }

    public function verifyPasswordUpdate($current_pass, $new_pass, $new_pass_confirm)
    {
        { // --- ERROR VERIFICATION
            if ($current_pass === '') {
                throw new Exception('Digite a SENHA ATUAL.');
            }

            if ($new_pass === '') {
                throw new Exception('Digite a NOVA SENHA.');
            }

            if ($new_pass_confirm === '') {
                throw new Exception('Digite a CONFIRMAÇÃO da nova senha.');
            }

            if ($new_pass !== $new_pass_confirm) {
                throw new Exception('A CONFIRMAÇÃO da nova senha NÃO CONFERE.');
            }

            if (User::password_crypt($new_pass) === $this->getPassword()) {
                throw new Exception('A sua nova senha deve ser DIFERENTE da atual.');
            }

            if (User::password_crypt($current_pass) !== $this->getPassword()) {
                throw new Exception('A SENHA ATUAL não está correta.');
            }
        }
    }
}



