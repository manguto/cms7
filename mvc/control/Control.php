<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use Slim\Slim;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\model\User;
use manguto\cms5\mvc\model\Manutencao;
use manguto\cms5\lib\Sessions;
use manguto\cms5\lib\ServerHelp;

class Control
{

    static function Start()
    {
        // ====================================================================================================
        { // SLIM PLATAFORM ANALISYS - ROUTES
            $app = new Slim();
            $app->config('debug', true);
            self::PlataformRouteAnalisys($app);
            $app->run();
        }
        // ====================================================================================================
    }

    private static function PlataformRouteAnalisys($app)
    {

        // ====================================================================================================
        self::CheckUnderMaintenance();
        // ====================================================================================================
        { // SITE - Front End
            ControlSite::RunRouteAnalisys($app);
        }
        // ====================================================================================================
        { // SITE - Back End
            ControlCRUD::RunRouteAnalisys($app);
        }
        // ====================================================================================================
        { // SITE - Back End
            ControlAdmin::RunRouteAnalisys($app);
        }
        // ====================================================================================================
        { // SITE - Dev End
            ControlDev::RunRouteAnalisys($app);
        }
        // ====================================================================================================
    }

    // ============================================================================================ CONTROLE DE ACESSO
    // ============================================================================================ CONTROLE DE ACESSO
    // ============================================================================================ CONTROLE DE ACESSO
    /**
     * Realiza o controle de acesso ao conteudo seguinte apenas a usuarios logados
     */
    protected static function PrivativeZone()
    {    
        if (! User::checkUserLogged()) {
            ProcessResult::setError("Permissão de acesso negada. Contate o administrador.");
            headerLocation('/');
            exit();
        }
    }

    /**
     * Realiza o controle de acesso ao conteudo seguinte apenas a usuarios logados e Administradores
     */
    protected static function PrivativeAdminZone()
    {
        if (! User::checkUserLoggedAdmin() && ! User::checkUserLoggedDev()) {
            ProcessResult::setError("Permissão de acesso negada. Contate o administrador.");
            headerLocation('/');
            exit();
        }
    }

    /**
     * Realiza o controle de acesso ao conteudo seguinte apenas a usuarios logados e Administradores
     */
    protected static function PrivativeDevZone()
    { 
        if (! User::checkUserLoggedDev()) {
            ProcessResult::setError("Permissão de acesso negada. Contate o administrador.");
            headerLocation('/');
            exit();
        }
    }

    protected static function PrivateCrudPermission($operation, $target_user_id)
    {
        // deb($operation,0); deb($target_user_id);
        {
            // logged user
            $user = User::getSessionUser();
            $user = $user->GET_DATA($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
            // deb($user);
            $adm = intval($user['adminzoneaccess']);
            // deb($adm);
            $dev = intval($user['devzoneaccess']);
            // deb($dev);
            $level_user = 1 + $adm + $dev;
        }
        // deb($userLevel);

        { // target user
            $user = new User($target_user_id);
            $user = $user->GET_DATA($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
            // deb($user);
            $adm = intval($user['adminzoneaccess']);
            // deb($adm);
            $dev = intval($user['devzoneaccess']);
            // deb($dev);
            $level_user_target = 1 + $adm + $dev;
        }

        try {
            // deb($tagetUserLevel);
            if ($operation == 'view') {
                // deb($level_user_target,0); deb($level_user,0);
                if ($level_user_target <= $level_user) {
                    // ok
                } else {
                    throw new Exception("Permissão negada. Contate o administrador.");
                }
            } else if ($operation == 'delete' || $operation == 'edit') {
                if ($level_user_target < $level_user) {
                    // ok
                } else {
                    throw new Exception("Permissão negada. Contate o administrador.");
                }
            }
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/');
            exit();
        }
    }

    // ============================================================================================ FUNCOES AUXILIARES
    // ============================================================================================ FUNCOES AUXILIARES
    // ============================================================================================ FUNCOES AUXILIARES

    /**
     * Executa as classes filhas conforme a sua existencia
     *
     * @param Slim $app
     * @param object $classObjectSample
     */
    static protected function RunChilds(Slim $app, $classObjectSample)
    {
        { // obtencao do nome desta classe e do local onde ela se encontra
            $thisclassname = get_class($classObjectSample);
            // deb($thisclassname,0);
            $thisclassname = Diretorios::fixDirectorySeparator($thisclassname);
            $thisclassname_path_array = explode(DIRECTORY_SEPARATOR, $thisclassname);
            // deb($thisclassname_);
            $thisclassname = array_pop($thisclassname_path_array);
            // deb($thisclassname,0);
            { // caminho onde estao todas as classes desta categoria (MVC)
                $sub_folder = strtolower(str_replace('Control', '', $thisclassname));
                $path_vendor = 'vendor' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $thisclassname_path_array) . DIRECTORY_SEPARATOR . $sub_folder . DIRECTORY_SEPARATOR;                
                $path_sis = 'sis' . DIRECTORY_SEPARATOR . 'control' . DIRECTORY_SEPARATOR . $sub_folder . DIRECTORY_SEPARATOR;
            }
            // deb($thisclassname,0); deb($path,0);
        }
        // deb($thisclassname,0);

        { // obtencao de todos os arquivos das pastas em questao
            { // vendor / sis

                //deb($path_vendor,0);
                if(file_exists($path_vendor)){
                    $control_vendor_files = Diretorios::obterArquivosPastas($path_vendor, false, true, false, [
                        'php'
                    ]);
                }else{
                    $control_vendor_files = [];
                }
                //deb($control_vendor_files);

                if(file_exists($path_sis)){
                    $control_sis_files = Diretorios::obterArquivosPastas($path_sis, false, true, false, [
                        'php'
                    ]);
                }else{
                    $control_sis_files = [];
                }                
                //deb($control_sis_files);

            }
            {//mix
                $arquivos_classes_filhas = [];
                foreach ($control_vendor_files as $control_vendor_file) {
                    $arquivos_classes_filhas[] = $control_vendor_file;
                }
                
                foreach ($control_sis_files as $control_sis_file) {
                    $arquivos_classes_filhas[] = $control_sis_file;
                }
                //deb($arquivos_classes_filhas,0);
            }            
            
        }
        
        // deb($classesFilhas,0);
        { // acionamento das classes filhas desta atraves do metodo "RUN()"
            foreach ($arquivos_classes_filhas as $classeFilha) {
                $classeFilha = str_replace('.php', '', $classeFilha);
                $classeFilha = str_replace('/', '\\', $classeFilha);
                $classeFilha = str_replace('vendor', '', $classeFilha);
                // deb('CLASSE FILHA >>> '.$classeFilha,0);
                $classeFilha::RunRouteAnalisys($app);
            }
        }

        {
            {
                {
                    {
                        self::RunChildsModules($app, $classObjectSample);
                    }
                }
            }
        }
    }

    /**
     * Executa as classes filhas modulares conforme a sua existencia
     *
     * @param Slim $app
     * @param object $classObjectSample
     */
    static protected function RunChildsModules(Slim $app, $classObjectSample)
    {
        { // obtencao do nome desta classe e do local onde ela se encontra
            $thisclassname = get_class($classObjectSample);
            $thisclassname = Diretorios::fixDirectorySeparator($thisclassname);
            $thisclassname_ = explode(DIRECTORY_SEPARATOR, $thisclassname);
            $thisclassname = array_pop($thisclassname_);
            // deb($thisclassname,0);
        }
        { // diretorio base dos controles dos modulos
          // deb(ROOT_SIS);
          // deb(get_defined_constants());
            $files = Diretorios::obterArquivosPastas(ROOT_SIS, true, true, false, [
                'php'
            ]);
            // deb($files,0);
            $classesFilhas = [];
            foreach ($files as $key => $file) {
                { // remove arquivos que nao sao filhos da classe em questao
                  // deb($file,0); deb($thisclassname,0);
                    if (! strpos($file, $thisclassname)) {
                        continue;
                    }
                }
                { // ajusta nome do arquivo da classe para um formato chamável
                    $search = '..' . DIRECTORY_SEPARATOR . SIS_FOLDERNAME . DIRECTORY_SEPARATOR;
                    $file = str_replace($search, '', $file);
                    $file = str_replace('.php', '', $file);
                    $classesFilhas[] = $file;
                }
            }
            // deb($classesFilhas);
        }
        // deb($classesFilhas);
        { // acionamento das classes filhas desta atraves do metodo "RUN()"
            foreach ($classesFilhas as $classeFilha) {
                $classeFilha = str_replace('/', '\\', $classeFilha);
                // deb('CLASSE FILHA MODULAR >>> '.$classeFilha,0);
                $classeFilha::RunRouteAnalisys($app);
            }
        }
    }
    
    static private function CheckUnderMaintenance() {
        
        if(User::checkUserLoggedDev()){
            //caso o usuario seja um DESENVOLVEDOR, não faz mais nada! pode liberar o acesso!
            //caso o usuario seja um DESENVOLVEDOR, não faz mais nada! pode liberar o acesso!
            //caso o usuario seja um DESENVOLVEDOR, não faz mais nada! pode liberar o acesso!
            
        }else{
            //caso seja qualquer outro tipo de usuario (nao logado, logado ou adm) é feito o teste
            //caso seja qualquer outro tipo de usuario (nao logado, logado ou adm) é feito o teste
            //caso seja qualquer outro tipo de usuario (nao logado, logado ou adm) é feito o teste
            $manutencao_array = Manutencao::EmFuncionamento();
            //deb($manutencao_array);
            if(sizeof($manutencao_array)>0){
                
                foreach ($manutencao_array as $manutencao){
                    ProcessResult::setWarning($manutencao->getMensagem());
                    ProcessResult::setError($manutencao->getMotivo());
                }
                //limpa a sessao e redireciona para a tela principal com os detalhes para exibicao!
                //limpa a sessao e redireciona para a tela principal com os detalhes para exibicao!
                //limpa a sessao e redireciona para a tela principal com os detalhes para exibicao!
                User::logout();
                $route = ServerHelp::getURLRoute();
                //deb($route);
                $route_home = '/'.SIS_FOLDERNAME.'/';
                $route_login = '/'.SIS_FOLDERNAME.'/login';
                if($route != $route_home && $route != $route_login){
                    //redirecionamento para a home
                    //redirecionamento para a home
                    //redirecionamento para a home
                    headerLocation('/');
                    exit();
                }
                
            }else{
                //tudo certo! nenhuma manutencao em andamento!
                //tudo certo! nenhuma manutencao em andamento!
                //tudo certo! nenhuma manutencao em andamento!
            }
        }        
    }
}

?>