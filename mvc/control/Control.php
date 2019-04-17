<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos; 
use Slim\Slim;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\model\User;
use manguto\cms5\lib\Session;
use manguto\cms5\lib\cms\CMSHelp;

class Control
{
    const control_dir = 'sis/control';
    static function Inicializar()
    {   
        // ====================================================================================================
        {// SLIM FRAMEWORK START!
            $app = new Slim();
            $app->config('debug', true);
            
            // SESSION RESET REQUEST?
            Session::checkResetRequest();
        }
        // ====================================================================================================
        { // PAGE STRUCTURAL DATA INITIALIZATION
            CMSHelp::Initialization();
        }
        // ====================================================================================================
        {// PLATAFORMS CALL ANALISYS
            self::ExecutePlataforms($app);            
        }
        // ====================================================================================================
        $app->run();
    }

    private static function ExecutePlataforms($app){
        // ====================================================================================================
        { // SITE - Front End
            ControlSite::Executar($app);
        }
        // ====================================================================================================
        { // SITE - Back End
            ControlCRUD::Executar($app);
        }
        // ====================================================================================================
        { // SITE - Back End
            ControlAdmin::Executar($app);
        }
        // ====================================================================================================
        { // SITE - Dev End
            ControlDev::Executar($app);
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
        if (! User::checkUserLoggedAdmin()) {
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
            $user = $user->getData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
            // deb($user);
            $adm = intval($user['adminzoneaccess']);
            // deb($adm);
            $dev = intval($user['devzoneaccess']);
            // deb($dev);
            $level_user = 1 + $adm + $dev;
        }
        //deb($userLevel);
        
        {//target user
            $user = new User($target_user_id);
            $user = $user->getData($extraIncluded = true, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
            // deb($user);
            $adm = intval($user['adminzoneaccess']);
            //deb($adm);
            $dev = intval($user['devzoneaccess']);
            //deb($dev);
            $level_user_target = 1 + $adm + $dev;
        }
        
        try {
            //deb($tagetUserLevel);
            if($operation=='view'){
                //deb($level_user_target,0); deb($level_user,0);
                if($level_user_target <= $level_user){
                    //ok
                }else{
                    throw new Exception("Permissão negada. Contate o administrador.");
                }
            }else if($operation=='delete' || $operation=='edit'){
                if($level_user_target < $level_user){
                    //ok
                }else{
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
     * @param Slim $app
     * @param object $classObjectSample
     */
    static protected function ExecutarClassesFilhas(Slim $app, $classObjectSample)
    {
        { // obtencao do nome desta classe e do local onde ela se encontra
            $thisclassname = get_class($classObjectSample);
            $thisclassname = Diretorios::fixDirectorySeparator($thisclassname);
            $thisclassname_ = explode(DIRECTORY_SEPARATOR, $thisclassname);
            $thisclassname = array_pop($thisclassname_);            
            { // caminho onde estao todas as classes desta categoria (MVC)
                $path = implode(DIRECTORY_SEPARATOR, $thisclassname_) . DIRECTORY_SEPARATOR;
                $path = 'vendor' . DIRECTORY_SEPARATOR . $path;
            }
            //deb($thisclassname);
            //deb($path);
        }
        //deb($thisclassname,0);

        { // obtencao de todos os arquivos das pastas em questao
            {//sis
                 
                $control_files = Diretorios::obterArquivosPastas($path, false, true, false, [
                    'php'
                ]);
                //deb($control_files,0);
            }
            {//cria uma lista apenas com os arquivos de controle da classe em questao
                $arquivos = [];
                foreach ($control_files as $arquivo){
                    $arquivo_temp = str_replace($path, '', $arquivo);
                    //deb($arquivo_temp);
                    $left = substr($arquivo_temp, 0,strlen($thisclassname));
                    //deb($path,0); deb($left,0); deb($thisclassname,0); deb('<hr/>',0);
                    if($left == $thisclassname && strpos($arquivo, 'Zzz')===false){
                        $arquivos[] = $arquivo;
                    }
                }
                //deb($arquivos,0);
            }
        }

        //deb($arquivos,0);
        { // obtencao das classes filhas
            $classesFilhas = [];
            foreach ($arquivos as $arquivo) {
                $classname = Arquivos::obterNomeArquivo($arquivo, false);
                $path_tmp = Arquivos::obterCaminho($arquivo);
                //deb($arquivo,0); deb($path_tmp,0); deb($classname);
                if (substr($classname, 0, strlen($thisclassname)) == $thisclassname && strlen($classname) != strlen($thisclassname)) {
                    $classesFilhas[] = $path_tmp . $classname;
                }
            }
            //deb($classesFilhas);
        }
        //deb($classesFilhas,0);
        { // acionamento das classes filhas desta atraves do metodo "RUN()"
            foreach ($classesFilhas as $classeFilha) {                
                $classeFilha = str_replace('/','\\',$classeFilha);
                $classeFilha = str_replace('vendor','',$classeFilha);                
                //deb('CLASSE FILHA >>> '.$classeFilha,0);
                $classeFilha::Executar($app);
            }
        }
        
        {
            {
                {
                    {
                        self::ExecutarClassesFilhasModulares($app, $classObjectSample);           
                    }
                }
            }
        }
    }
    
    /**
     * Executa as classes filhas modulares conforme a sua existencia
     * @param Slim $app
     * @param object $classObjectSample
     */
    static protected function ExecutarClassesFilhasModulares(Slim $app, $classObjectSample)
    {
        { // obtencao do nome desta classe e do local onde ela se encontra
            $thisclassname = get_class($classObjectSample);
            $thisclassname = Diretorios::fixDirectorySeparator($thisclassname);
            $thisclassname_ = explode(DIRECTORY_SEPARATOR, $thisclassname);
            $thisclassname = array_pop($thisclassname_);
            //deb($thisclassname,0);
        }
        {//diretorio base dos controles dos modulos    
            //deb(ROOT_SIS);
            //deb(get_defined_constants());            
            $files = Diretorios::obterArquivosPastas(ROOT_SIS, true, true, false,['php']);
            //deb($files,0);
            $classesFilhas = [];
            foreach ($files as $key=>$file){
                {//remove arquivos que nao sao filhos da classe em questao
                    //deb($file,0); deb($thisclassname,0);
                    if(!strpos($file, $thisclassname)){                        
                        continue;
                    }
                }           
                {//ajusta nome do arquivo da classe para um formato chamável
                    $search = '..'.DIRECTORY_SEPARATOR.SIS_FOLDERNAME.DIRECTORY_SEPARATOR;
                    $file = str_replace($search, '', $file);
                    $file = str_replace('.php', '', $file);
                    $classesFilhas[] = $file;   
                }
                
            }
            //deb($classesFilhas);
        }
        //deb($classesFilhas);
        { // acionamento das classes filhas desta atraves do metodo "RUN()"
            foreach ($classesFilhas as $classeFilha) {                
                $classeFilha = str_replace('/','\\',$classeFilha);
                //deb('CLASSE FILHA MODULAR >>> '.$classeFilha,0);                
                $classeFilha::Executar($app);
            }
        }
    }
}

?>