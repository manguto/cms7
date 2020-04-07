<?php
namespace manguto\cms7\lib\cms;

use cms\control\Control;
use manguto\cms7\lib\Exception;
use manguto\cms7\lib\Sessions;
use manguto\cms7\lib\Arquivos;
use cms\model\Manutencao;
use manguto\cms7\lib\ProcessResult;
use manguto\cms7\lib\ServerHelp;

class CMS
{

    // constantes fundamentais
    const fundamental_constants = [
        'SIS_FOLDERNAME',
        // => string(3) "new"
        'SIS_NAME',
        // => string(7) "Sistema"
        'SIS_NAME_FULL',
        // => string(16) "Sistema Auxiliar"
        'SIS_ABREV',
        // => string(3) "SIS"
        'SIS_EMAIL',
        // => string(15) "contato@sis.com"
        'SIS_EMAIL_ADMIN',
        // => string(13) "admin@sis.com"
        'SIS_URL',
        // => string(16) "http: //l.sis.com"
        'SIS_VIRTUAL_HOST',
        // => bool(false)
        'SIS_ITERACTION_ID',
        // => string(24) "20200324_170937_0.239302"
        'SIS_PATH',
        // => string(32) "C:\xampp\htdocs\manguto.com\new\"
        'SIS_HTML_PATH',
        // => string(0) ""
        'SIS_VENDOR_MANGUTO_PATH',
        // => string(47) "C:\xampp\htdocs\manguto.com\new\vendor\manguto\"
        'SIS_VENDOR_MANGUTO_LIB_NAME',
        // => string(4) "cms5"
        'SIS_VENDOR_MANGUTO_REPOSITORY_PATH',
        // => string(52) "C:\xampp\htdocs\manguto.com\new\vendor\manguto\cms7\"
        'SIS_LOG_PATH'
        // => string(4) "log\"
    ];

    // ############################################################################################################################################
    // pastas necessarias na raiz e suas devidas permissoes
    const root_folder_permission_array = [
        '777' => 'cache',
        '777' => 'data',
        '777' => 'log',
        '777' => 'repository'
    ];

    // ############################################################################################################################################
    /**
     * define constantes fundamentais
     */
    private function setCMSCtes()
    {
        // ----------------------------------------------------------------------------------------------------
        define('SIS_CMS_RES_FOLDERNAME', 'res');
        define('SIS_CMS_FOLDERNAME', 'cms');
        define('SIS_CMS_CONTROL_FOLDERNAME', 'control');
        define('SIS_CMS_MODEL_FOLDERNAME', 'model');
        define('SIS_CMS_VIEW_FOLDERNAME', 'view');
        define('SIS_CMS_TPL_FOLDERNAME', 'tpl');
        define('SIS_CMS_LIB_FOLDERNAME', 'lib');
        // ----------------------------------------------------------------------------------------------------
        define('SIS_RES_PATH', SIS_PATH . SIS_CMS_RES_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_PATH', SIS_PATH . SIS_CMS_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_CONTROL_PATH', SIS_CMS_PATH . SIS_CMS_CONTROL_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_MODEL_PATH', SIS_CMS_PATH . SIS_CMS_MODEL_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_VIEW_PATH', SIS_CMS_PATH . SIS_CMS_VIEW_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_TPL_PATH', SIS_CMS_PATH . SIS_CMS_TPL_FOLDERNAME . DIRECTORY_SEPARATOR);
        define('SIS_CMS_LIB_PATH', SIS_CMS_PATH . SIS_CMS_LIB_FOLDERNAME . DIRECTORY_SEPARATOR);
        // ----------------------------------------------------------------------------------------------------
        // definicao de constantes secundarias
        define('SIS_CMS_ACTUAL_PLATFORM', $this->sis_cms_actual_platform());
        // ----------------------------------------------------------------------------------------------------
    }

    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################
    public function __construct()
    {
        // ----------------------------------------------------------------------------------------------------
        // verificacao de parametros fundamentais
        $this->checkCteDefined();
        // ----------------------------------------------------------------------------------------------------
        // definicao de constantes fundamentais
        $this->setCMSCtes();
        // ----------------------------------------------------------------------------------------------------
        // verifica as pastas necessarias e suas permissoes
        $this->checkDirectoriesAndPermissions();
        // ----------------------------------------------------------------------------------------------------
        // sessions reset check!
        Sessions::checkResetRequest();
        // ----------------------------------------------------------------------------------------------------
        // verifica se o cms nao se encontra em manutencao!
        $this->checkUnderMaintenance();
        // ----------------------------------------------------------------------------------------------------
        // debc(get_defined_constants());
        // ----------------------------------------------------------------------------------------------------
    }

    // ############################################################################################################################################
    public function Initialize()
    {
        Control::Initialize();
    }

    // ############################################################################################################################################
    // ############################################################################################################################################
    // ############################################################################################################################################

    /**
     * Verifica se todas os parametros necessarios (constantes, parametros globais, etc.) foram definidos
     *
     * @throws Exception
     */
    private function checkCteDefined()
    {
        // deb(get_defined_constants());
        $error = [];
        foreach (self::fundamental_constants as $cte) {
            if (! defined($cte)) {
                $error[] = "A constante fundamental '$cte' não foi definida. Defina-a e tente novamente ('/configuracoes.php').";
            }
        }
        if (sizeof($error) > 0) {
            throw new Exception(implode('<br/>' . chr(10) . chr(13), $error));
        }
    }

    // ############################################################################################################################################

    // ############################################################################################################################################
    /**
     * obtem o nome da plataforma requisitada na iteracao
     *
     * @return string
     */
    private function sis_cms_actual_platform()
    {
        { // parameters
            $request_uri_ = explode('/', $_SERVER['REQUEST_URI']);
            $second_uri_parameter = isset($request_uri_[2]) ? $request_uri_[2] : 'site';
        }

        switch ($second_uri_parameter) {
            case 'admin':
                $return = 'admin';
                break;
            case 'dev':
                $return = 'dev';
                break;
            default:
                $return = 'site';
                break;
        }

        return $return;
    }

    // ############################################################################################################################################
    /**
     * verifica a existencia de determinadas pastas e suas permissoes
     */
    private function checkDirectoriesAndPermissions()
    {
        foreach (self::root_folder_permission_array as $permission => $folder) {
            $permissionActual = Arquivos::permissoesObter($folder, false);
            // deb($fileperms,0);
            if ($permissionActual != $permission) {
                Arquivos::permissoesAlterar($folder, 0777);
            }
        }
    }

    // ############################################################################################################################################
    /**
     * verifica se os sistema foi colocado em manutencao
     */
    private function checkUnderMaintenance()
    {
        if (CMSAccessManagement::checkUserLoggedDev()) {
            // caso o usuario seja um DESENVOLVEDOR, não faz mais nada! pode liberar o acesso!
        } else {
            // caso seja qualquer outro tipo de usuario (nao logado, logado ou adm) é feito o teste
            $manutencao_array = Manutencao::EmFuncionamento();
            // deb($manutencao_array);
            if (sizeof($manutencao_array) > 0) {

                foreach ($manutencao_array as $manutencao) {
                    ProcessResult::setWarning($manutencao->getMensagem());
                    ProcessResult::setError($manutencao->getMotivo());
                }
                // limpa a sessao e redireciona para a tela principal com os detalhes para exibicao!
                CMSAccessManagement::clearSessionUser();
                $route = ServerHelp::getRoute();
                // deb($route);
                $route_home = '/' . SIS_FOLDERNAME . '/';
                $route_login = '/' . SIS_FOLDERNAME . '/login';
                if ($route != $route_home && $route != $route_login) {
                    // redirecionamento para a home
                    CMS::headerLocation('/');
                    exit();
                }
            } else {
                // tudo certo! nenhuma manutencao em andamento!
            }
        }
    }

    // ####################################################################################################

    // #####################################################################################################
    // ################################################################################### REDIRECIONAMENTOS
    // #####################################################################################################
    static function headerLocation(string $path, $die = true)
    {
        { // verifica se o caminho informado (path) eh direto ou precisa de uma barra ('/')
            $firstChar = substr($path, 0, 1);
            if ($firstChar == '?' || $firstChar == '/') {
                $separator = '';
            } else {
                $separator = '/';
            }
        }
        header('Location: ' . SIS_HTML_PATH . $separator . $path);
        if ($die) {
            exit();
        }
    }

    // #####################################################################################################
    /**
     * realiza um redirecionamento via submicao de um formulario com o metodo POST
     * com os parametros informados
     *
     * @param string $path
     * @param array $variables
     */
    static function headerLocationPost(string $path, array $parameter_array = [])
    {
        $url = SIS_HTML_PATH . $path;

        $inputs = '';
        foreach ($parameter_array as $key => $value) {

            // ajuste no caso de parametros informados em array (checkboxes...)
            if (! is_array($value)) {
                $inputs .= "$key: <input type='text' name='$key' value='$value' class='form-control mb-2' style='display:none;'>";
            } else {
                $key = $key . '[]';
                foreach ($value as $v) {
                    $inputs .= "$key: <input type='text' name='$key' value='$v' class='form-control mb-2' style='display:none;'>";
                }
            }
        }

        $html = "<!DOCTYPE html>
                <html>
                    <head>
                        <title>REDIRECTION...</title>
                    </head>
                    <body>
                        <section>
                        	<div class='container'>
                        		<form method='post' action='$url' id='postRedirect' style='display:none;'>
                                    $inputs
                        			<input type='submit' value='CLIQUE AQUI PARA CONTINUAR...' style='display:none;'>
                        		</form>
                        	</div>
                        </section>
                    </body>
                </html>
                <script type='text/javascript'>
                    (function() {
                        document.getElementById('postRedirect').submit();
                    })();
                </script>";
        echo $html;
    }
}

?>