<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Safety;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Session;
use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\model\ModelInterface;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\database\mysql\Mysql;
use manguto\cms5\lib\database\sql\Sql;
use manguto\cms5\lib\model\ModelSql;

class User extends ModelSql implements ModelInterface
{

    const SESSION = "User";

    const FORGOT_EMAIL = "UserEmail";

    public function __construct($id = 0)
    {
        // definicao dos atributos deste modelo
        $this->DefineAttributes();

        // construct
        parent::__construct($id);
       
    }

    // definicao dos atributos deste modelo
    private function DefineAttributes()
    {
        $attributes = [
            'name' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64,
            ],
            'login' =>[
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 24,
            ],
            'password' =>[
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 32,
                'encrypted' => true,
            ],
            'email' =>[
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64,
                'nature' => ModelAttribute::NATURE_EMAIL,
            ],
            'phone' =>[
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => '',
                'length' => 64,                
            ],
            'adminzoneaccess' =>[
                'type' => ModelAttribute::TYPE_BOOLEAN,
                'value' => 0,                                
            ],
            'devzoneaccess' =>[
                'type' => ModelAttribute::TYPE_BOOLEAN,
                'value' => 0,
            ],
            
        ];
        
        $this->SetAttributes($attributes);
    }

    // const FORGOT_SECRET_KEY = "1234567890123456";
    static function checkUserLogged(): bool
    {
        $return = false;
        if (Session::isset(User::SESSION)) {
            $user = Session::get(User::SESSION);
            if (isset($user['id'])) {
                $id = intval($user['id']);
                if ($id > 0) {
                    $return = true;
                }
            }
        }
        return $return;
    }

    static function checkUserLoggedAdmin(): bool
    {
        if (User::checkUserLogged()) {

            $user = self::getSessionUser();
            $user = $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
            if (isset($user['adminzoneaccess']) && ((bool) $user['adminzoneaccess'] == true)) {
                $return = true;
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    static function checkUserLoggedDev(): bool
    {
        if (User::checkUserLogged()) {

            $user = self::getSessionUser();
            // deb($user);
            $user = $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);

            if (isset($user['devzoneaccess']) && ((bool) $user['devzoneaccess'] == true)) {
                $return = true;
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    static public function initialize()
    {
        $quantUsuarios = Sql::getTableLength('SELECT * FROM user WHERE 1');
        // deb($quantUsuarios);
        if ($quantUsuarios == 0) {            
            // --------------------------------------------------- set user admin
            $admin = new User();
            $admin->setname('Administrador');
            $admin->setlogin('admin');
            $admin->setpassword('7f002aed5bd47189aae03deaf5149292');
            $admin->setemail('admin@admin.com');
            $admin->setphone('(XX) X.XXXX-XXXX');
            $admin->setadminzoneaccess(1);
            $admin->setdevzoneaccess(1);
            $admin->save();
            // ---------------------------------------------------
        }
    }

    static function login($login, $password)
    {
        // deb($login,0); deb($password,0); deb(User::password_crypt($password));
        // cifragem de password para comparacao
        $password = User::password_crypt($password);
        // veirifcacao do repositorio do susuarios
        User::initialize();
        // die('++');
        {
            { // usuario existe
                $mysql = new Mysql();
                $mysql->query(" SELECT * FROM user WHERE login='$login' ");                
                $results = $mysql->fetchArray();
                // deb($results);
                if (count($results) === 0) {
                    throw new Exception("Login não encontrado e/ou senha inválida.");
                }
            }
            { // usuario e senha existem                
                $mysql = new Mysql();
                $mysql->query(" SELECT * FROM user WHERE login='$login' && password='$password' ");
                $results = $mysql->fetchArray();
                // deb($results);

                // $results = Repository::getRepository('user', $conditions);
                throw new Exception("");

                // deb($results);
                if (count($results) === 0) {
                    throw new Exception("Senha inválida e/ou login não encontrado.");
                }
            }
            // ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES
            // ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES
            // ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES ### TESTES
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

    static function setSessionUser(Model $user)
    {
        // deb($user);
        // $__SESSION[SIS_ABREV][User::SESSION] = $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
        $user = $user->GetData($extraIncluded = false);
        Session::set(User::SESSION, $user);
    }

    static function getSessionUser()
    {
        $user = false;

        if (User::checkUserLogged()) {
            // $user_array = $__SESSION[SIS_ABREV][User::SESSION];
            $user = Session::get(User::SESSION);
            // deb($user_array);

            try {
                $user = new User($user['id']);
                // deb($user);
            } catch (Exception $e) {
                ProcessResult::setError($e);
                User::logout();
                headerLocation('/login');
                exit();
            }
        }
        return $user;
    }

    static function getSessionUserDirectParameter($parameter)
    {
        if (User::checkUserLogged()) {
            // $user_array = $__SESSION[SIS_ABREV][User::SESSION];
            $user_array = Session::get(User::SESSION);

            // deb($user_array);
            if (isset($user_array[$parameter])) {
                return $user_array[$parameter];
            } else {
                return 'indefinido';
            }
        } else {
            return 'indefinido2';
        }
    }

    static function logout()
    {
        // unset($__SESSION[SIS_ABREV][User::SESSION]);
        Session::unset(User::SESSION);
    }

    public function checkLoginExist(): bool
    {
        // $result = LocalDatabase::run(" SELECT * FROM user WHERE login='".$this->getlogin()."' ");
        // $result = Repository::getRepository('user', " \$login=='" . $this->getlogin() . "' ");
        throw new Exception("");

        // deb($result);
        if (sizeof($result) == 1) {
            $user = array_shift($result);
            if ($this->getId() == $user->getId()) {
                return false;
            } else {
                return true;
            }
        } else if (sizeof($result) == 0) {
            return false;
        } else {
            throw new Exception("Existem mais de um usuário com o mesmo login. Contate o administrador.");
        }
    }

    public function checkEmailExist(): bool
    {
        // $result = LocalDatabase::run(" SELECT * FROM user WHERE email='".$this->getemail()."' ");
        // $result = Repository::getRepository('user', " \$email=='" . $this->getemail() . "' ");
        throw new Exception("");

        // deb($result);
        if (sizeof($result) == 1) {
            $user = array_shift($result);
            if ($this->getId() == $user->getId()) {
                return false;
            } else {
                return true;
            }
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

        // $results = LocalDatabase::run(" SELECT * FROM user WHERE email='$email' ");
        // $results = Repository::getRepository('user', " \$email=='$email' ");
        throw new Exception("");
        // deb($results);

        if (count($results) == 0) {
            throw new Exception("Não foi possível recuperar a sua senha.");
        } else {

            $user = array_shift($results);
            // deb($user);
            // if(false)$user = new User();
            $userPasswordRecoveries = new UserPasswordRecoveries();
            $userPasswordRecoveries->setid($user->getId());
            $userPasswordRecoveries->setip($_SERVER["REMOTE_ADDR"]);
            $userPasswordRecoveries->setdeadline(time() + UserPasswordRecoveries::deadline);
            $userPasswordRecoveries->setdatetime(time());
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

            /*
             * if ($adminzoneaccess === true) {
             * $link = SIS_URL . "/admin/forgot/reset?code=" . $recoveryid_encrypted;
             * } else {
             * $link = SIS_URL . "/forgot/reset?code=" . $recoveryid_encrypted;
             * }
             */
            $link = SIS_URL . "/forgot/reset?code=" . $recoveryid_encrypted;
            // deb($link);

            /*
             * $mailer = new CMSMailer($user->getemail(), $user->getname(), "Redefinição de senha do(a) " . SIS_NAME, "forgot", array(
             * "name" => $user->getname(),
             * "link" => $link
             * ));
             */
            throw new Exception("");

            if (! $mailer->send()) {
                throw new Exception("Não foi possível enviar o e-mail de recuperação.<br/>Aguarde alguns instantes e tente novamente.<br/>Caso o problema persista, contate o administrador.");
            }

            return $user->GetData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
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

    static function setForgotUsed($userpasswordrecoveriesid)
    {
        $upr = new UserPasswordRecoveries($userpasswordrecoveriesid);
        // deb($upr,0);
        $upr->setdeadline(time());
        // deb($upr,0);
        $upr->save();
        // deb($upr);
    }

    // ------------------------------------------------------------- ForgotEmail
    static function setForgotEmail($email)
    {
        // deb($msg);
        // $__SESSION[SIS_ABREV][User::FORGOT_EMAIL] = $email;
        Session::set(User::FORGOT_EMAIL, $email);
    }

    static function getForgotEmail()
    {
        // $cond1 = isset($__SESSION[SIS_ABREV][User::FORGOT_EMAIL]);
        $cond1 = Session::isset(User::FORGOT_EMAIL);

        // $cond2 = $__SESSION[SIS_ABREV][User::FORGOT_EMAIL] !== NULL;
        $cond2 = Session::get(User::FORGOT_EMAIL) !== NULL;

        if ($cond1 && $cond2) {
            // $msg = $__SESSION[SIS_ABREV][User::FORGOT_EMAIL];
            $msg = Session::get(User::FORGOT_EMAIL);
        } else {
            $msg = User::clearForgotEmail();
        }
        return $msg;
    }

    static function clearForgotEmail()
    {
        // deb("CLEAR!",0);
        // unset($__SESSION[SIS_ABREV][User::FORGOT_EMAIL]);
        Session::unset(User::FORGOT_EMAIL);
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



