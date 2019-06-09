<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\dev\ViewDevSync;

class ControlDevSync extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/sync', function () {
            self::PrivativeDevZone();
            //ControlDevSync::get_dev_sync();
            headerLocation('/dev/sync/go');
            exit();
        });

        $app->get('/dev/sync/go', function () {
            self::PrivativeDevZone();
            ControlDevSync::get_dev_sync_go();
        });

        $app->post('/dev/sync/analyse', function () {
            self::PrivativeDevZone();
            ControlDevSync::get_dev_sync_analyse();
        });

        $app->post('/dev/sync/go', function () {
            self::PrivativeDevZone();
            ControlDevSync::get_dev_sync_go_save();
        });
    }

    // ##########################################################################################################################
    static function get_dev_sync()
    {
        ViewDevSync::get_dev_sync();
    }

    // ##########################################################################################################################
    static function get_dev_sync_go()
    {
        $analisys = self::get_dev_sync_go_analisys();
        ViewDevSync::get_dev_sync_go($analisys);
    }

    // ##########################################################################################################################
    static function get_dev_sync_analyse()
    {
        // deb($_POST);
        $info = explode(';', urldecode($_POST['paths']));
        $prod_path = $info[0];
        $base_path = $info[1];
        // deb($prod_path,0); deb($base_path);

        // deb($_POST);
        $filename = $prod_path;
        $prod_content = htmlentities(Arquivos::obterConteudo($prod_path, false));   
        $base_content = htmlentities(Arquivos::obterConteudo($base_path, false));

        { // replaces
            { // tab => ' '
                $prod_content = str_replace(chr(9), ' ', $prod_content);
                $base_content = str_replace(chr(9), ' ', $base_content);
            }
        }

        {
            { // explode
                $prod_content_array = explode(chr(10), $prod_content);
                $base_content_array = explode(chr(10), $base_content);
            }

            { // wraps

                { // prod
                    $prod_content_html = [];
                    foreach ($prod_content_array as $prod_content_line) {
                        if (trim($prod_content_line) == '')
                            continue;

                        if (in_array($prod_content_line, $base_content_array)) {
                            $pre_tag = "<span class='linha encontrada'>";
                            $pos_tag = "</span>";
                        } else {
                            $pre_tag = "<span class='linha nao-encontrada'>";
                            $pos_tag = "</span>";
                        }
                        $prod_content_html[] = $pre_tag . $prod_content_line . $pos_tag;
                    }
                    $prod_content_html = implode('', $prod_content_html);
                }

                { // base
                    $base_content_html = [];
                    foreach ($base_content_array as $base_content_line) {
                        if (trim($base_content_line) == '')
                            continue;

                        if (in_array($base_content_line, $prod_content_array)) {
                            $pre_tag = "<span class='linha encontrada'>";
                            $pos_tag = "</span>";
                        } else {
                            $pre_tag = "<span class='linha nao-encontrada'>";
                            $pos_tag = "</span>";
                        }
                        $base_content_html[] = $pre_tag . $base_content_line . $pos_tag;
                    }
                    $base_content_html = implode('', $base_content_html);
                }
            }
        }

        ViewDevSync::post_dev_sync_analyse($filename, $prod_content_html, $base_content_html);
    }

    // ##########################################################################################################################
    static function get_dev_sync_go_save()
    {
        // deb($_POST);
        try {
            foreach ($_POST['arquivo'] as $info) {
                if (trim($info) == '')
                    continue;
                $info = explode(';', $info);
                
                
                if (sizeof($info) == 2) {
                    // ----------------------------------------------
                    //CRIACAO OU ATUALIZACAO DE ARQUIVO
                    // ----------------------------------------------
                    $origin_path = $info[0];
                    $destin_path = $info[1];
                    Arquivos::copiarArquivo($origin_path, $destin_path);
                    ProcessResult::setSuccess("Arquivo criado/atualizado com sucesso! <br>$origin_path => $destin_path");
                    // ----------------------------------------------------------------------------------------------

                }else if(sizeof($info) == 1) {
                    // ----------------------------------------------
                    //REMOCAO DE ARQUIVO
                    // ----------------------------------------------
                    $remove_file_path = $info[0];
                    Arquivos::excluir($remove_file_path);
                    ProcessResult::setSuccess("Arquivo removido com sucesso! <br>[$remove_file_path]");
                    // ----------------------------------------------------------------------------------------------
                    
                }else{
                    throw new Exception("Quanditade de parâmetros inadequada a necessária ($info).");
                }
                
            }
        } catch (Exception $e) {
            ProcessResult::setError($e);
            headerLocation('/dev/sync/go');
            exit();
        }
        headerLocation('/dev/sync/go');
        exit();
    }

    // #
    // #
    // #
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ################################################## PRIVATE ###############################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // #
    // #
    // #
    static private function get_dev_sync_go_analisys()
    {
        { // CONFIGURATIONS - configuracoes fundamentais

            { // diretorios
                { // producao
                    $prod_dir = '../' . SIS_FOLDERNAME . '/';
                    $prod_dir = Diretorios::fixDirectorySeparator($prod_dir);
                }
                { // base
                    $base_dir = VENDOR_MANGUTO_PRJ_ROOT.'res/cms/';
                    $base_dir = Diretorios::fixDirectorySeparator($base_dir);
                }
            }
            { // sub-pastas fundamentais
                $target_folders = [
                    'sis',                    
                    'res'
                ];
            }
        }
        { // ALL FILES - OBTENCAO DE TODOS OS ARQUIVOS ALVO ENCONTRADOS
            $all_files = self::get_all_files_envolved($prod_dir, $base_dir, $target_folders);
            //deb($all_files);
        }
        { // ANALISES - ANALISE QUANTO A TOMADA DE ACAO
            $analisys = self::get_all_files_analisys($all_files, $base_dir);
            //deb($analisys);
        }        

        return $analisys;
    }

    /**
     * obtem todos os arquivos da producao (root,tpl,res,lib)
     *
     * @param string $prod_dir
     * @param string $target_folders
     * @return mixed[]
     */
    static private function get_production_files(string $prod_dir, array $target_folders)
    {
        // PRODUCTION FILES
        $prod_files = [];

        // root
        $prod_files_tmp = Diretorios::obterArquivosPastas($prod_dir, false, true, false, [
            'php'
        ]);
        foreach ($prod_files_tmp as $prod_file) {
            $key = str_replace($prod_dir, '', $prod_file);
            $prod_file = str_replace($prod_dir, '', $prod_file);
            $prod_files[$key] = $prod_file;
        }

        foreach ($target_folders as $target_folder) {
            $prod_files_tmp = Diretorios::obterArquivosPastas($prod_dir . $target_folder, true, true, true);
            foreach ($prod_files_tmp as $prod_file) {

                $key = str_replace($prod_dir, '', $prod_file);
                $prod_file = str_replace($prod_dir, '', $prod_file);
                $prod_files[$key] = $prod_file;
            }
        }
        // deb($prod_files);
        return $prod_files;
    }

    /**
     * obtem todos os arquivos da base (VENDOR_MANGUTO_PRJ_ROOT/cms/files)
     *
     * @param string $base_dir
     * @param string $target_folders
     * @return string[][]|mixed[][]|array[]
     */
    static private function get_base_files(string $base_dir, array $target_folders)
    {
        $base_files = [];

        // root
        $base_files_tmp = Diretorios::obterArquivosPastas($base_dir, false, true, false, [
            'php_'
        ]);
        foreach ($base_files_tmp as $base_file) {
            $key = str_replace($base_dir, '', $base_file);
            $key = str_replace('php_', 'php', $key);
            $base_files[$key] = $base_file;
        }
        // deb($base_files);
        foreach ($target_folders as $target_folder) {
            $base_files_tmp = Diretorios::obterArquivosPastas($base_dir . $target_folder, true, true, true);
            foreach ($base_files_tmp as $base_file) {
                $key = str_replace($base_dir, '', $base_file);
                $key = str_replace('php_', 'php', $key);
                $base_files[$key] = $base_file;
            }
        }
        // deb($base_files);
        return $base_files;
    }

    static private function get_all_files_envolved(string $prod_dir, string $base_dir, array $target_folders)
    {
        { // ARQUIVOS PRODUCAO / BASE - LEVANTAMENTO

            { // PRODUCTION FILES
                $prod_files = self::get_production_files($prod_dir, $target_folders);
                // deb($prod_files);
            }

            { // BASE FILES
                $base_files = self::get_base_files($base_dir, $target_folders);
                // deb($base_files);
            }
        }

        { // ALL FILES - REGISTRO DE TODOS OS ARQUIVOS ENCONTRADOS
            $all_files = [];
            foreach ($prod_files as $key => $prod_file) {
                if (is_dir($prod_file))
                    continue;
                $all_files[$key]['prod'] = $prod_file;
            }

            foreach ($base_files as $key => $base_file) {
                if (is_dir($base_file))
                    continue;
                $all_files[$key]['base'] = $base_file;
            }
            // deb($all_files);
            ksort($all_files);
        }
        return $all_files;
    }

    static private function get_all_files_analisys(array $all_files, string $base_dir)
    {
        $comparison = [];
        $filters = [];

        { // CONFIGURATIONS

            $conf = [];

            { // PASTAS OU ARQUIVOS ONDE OS MESMOS PRECISAM ESTAR SEMPRE IGUAIS com a BASE
                $conf['diretorioOuArquivo_critico'] = [];                                            
                //============================================================================= res
                $conf['diretorioOuArquivo_critico'][] = 'res/css/style.css';
                $conf['diretorioOuArquivo_critico'][] = 'res/css/print.css';
                
                $conf['diretorioOuArquivo_critico'][] = 'res/fonts/index.php';
                
                $conf['diretorioOuArquivo_critico'][] = 'res/js/scripts.js';
                $conf['diretorioOuArquivo_critico'][] = 'res/js/scripts_form.js';
                //============================================================================= control
                $conf['diretorioOuArquivo_critico'][] = 'sis/control/admin/ControlAdminZzz.php';
                $conf['diretorioOuArquivo_critico'][] = 'sis/control/site/ControlSiteZzz.php';
                $conf['diretorioOuArquivo_critico'][] = 'sis/control/site/ControlSiteModelagem.php';                
                $conf['diretorioOuArquivo_critico'][] = 'sis/control/crud/ControlCRUDZzz.php';
                //============================================================================= model
                $conf['diretorioOuArquivo_critico'][] = 'sis/model/Zzz.php';
                //============================================================================= view
                $conf['diretorioOuArquivo_critico'][] = 'sis/view/admin/ViewAdminZzz.php';
                $conf['diretorioOuArquivo_critico'][] = 'sis/view/site/ViewSiteModelagem.php';
                $conf['diretorioOuArquivo_critico'][] = 'sis/view/site/ViewSiteZzz.php';
                
                $conf['diretorioOuArquivo_critico'][] = 'sis/view/crud/ViewCRUDZzz.php';
                
                //============================================================================= tpl
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/admin/admin_zzz.html';
                
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/site/site_zzz.html';
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/site/site_modelagem.html';
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/site/modelagem';
                
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/crud/crud_zzz.html';
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/crud/crud_zzz_edit.html';
                $conf['diretorioOuArquivo_critico'][] = 'sis/tpl/crud/crud_zzz_view.html';
                               
                
                // ...
            }

            { // PASTAS OU ARQUIVOS RESULTADO DE MODIFICACOES IMPLEMENTACIONAIS
                $conf['diretorioOuArquivo_implementacao'] = [];
                $conf['diretorioOuArquivo_implementacao'][] = 'index.php';
                $conf['diretorioOuArquivo_implementacao'][] = 'configurations.php';
                $conf['diretorioOuArquivo_implementacao'][] = 'functions.php';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/control/admin/ControlAdminHome.php';
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/control/site/ControlSiteHome.php';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/general/_footer_content.html';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/general/_header_title.html';                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/general/_menu_site.html';               
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/general/_menu_admin.html';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/site/_menu.html';
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/site/site_home.html';
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/site/site_modelagem_top.html';
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/site/site_modelagem.html';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/admin/_menu.html';                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/tpl/admin/admin_home.html';
                
                $conf['diretorioOuArquivo_implementacao'][] = 'sis/_footer_content.html';
                // ...
            }

            { // pastas ou arquivos desnecessarios
                $conf['diretorioOuArquivo_lixo'] = [];
                $conf['diretorioOuArquivo_lixo'][] = 'cache';
                // ...
            }
        }

        { // fix directory separator
            foreach ($conf as $k => $v) {
                foreach ($v as $kk => $vv) {
                    $conf[$k][$kk] = Diretorios::fixDirectorySeparator($vv);
                }
            }
        }

        $i = 0;
        foreach ($all_files as $file => $info) {
            // deb($file);

            { // trash folders files jump
                $folder = Arquivos::obterCaminho($file);
                // deb($folder,0);
                if (in_array($folder, $conf['diretorioOuArquivo_lixo'])) {
                    continue;
                }
            }

            { // parametros necessarios para avaliacao

                { // producao
                    $prod_found = isset($info['prod']);
                    $prod_path = $prod_found ? $info['prod'] : $file;
                    $prod_path_show = $prod_found ? $prod_path : '...';
                }

                { // base
                    $base_found = isset($info['base']);
                    $base_path = $base_found ? $info['base'] : $base_dir . str_replace('.php', '.php_', $file);
                    $base_path_show = $base_found ? $base_path : '...';
                }

                { // paths (para enviar para analysis)
                    $paths = urlencode($prod_path . ';' . $base_path);
                }
                {//
                    $pathname = $prod_path;
                }
            }

            { // avaliacao das informacoes do arquivo
              // STATUS
              // OPCOES
              // MSG

                { // ============================================================================================= ANALISE e LEVANTAMENTO de PARAMETROS
                    if ($prod_found && ! $base_found) {

                        // ======================================================================================== faltando na base (>>)
                        $status_show = 'FALTANDO NA BASE';
                        $status_title = 'Arquivos encontrados na produção e faltando na base.';
                        $status = 'novo_faltando-na-base';
                        $class = 'novo faltando alvo-base';
                        $msg = 'Arquivo faltando na BASE!';
                        $sugestao = '.';
                        $opcoes = [
                            '.',
                            '>>'
                        ];
                    } else if (! $prod_found && $base_found) {

                        // ======================================================================================== faltando na prod (<<)
                        $status_show = 'FALTANDO NA PRODUÇÃO';
                        $status_title = 'Arquivos encontrados na base e faltando na produção.';
                        $status = 'novo_faltando-na-prod';
                        $class = 'novo faltando alvo-prod verificar critico';
                        $msg = 'Arquivo faltando na PRODUÇÃO!';
                        $sugestao = '<<';
                        $opcoes = [
                            '<<',
                            '.'
                        ];
                    } else if ($prod_found && $base_found) {

                        // ========================================================================================= encontrado em ambos

                        if (Arquivos::verificarArquivosIdenticos($prod_path, $base_path)) {

                            // =============================================================== encontrado em ambos - identicos
                            $status_show = 'IGUAIS';
                            $status_title = 'Arquivos idênticos. Nenhum procedimento a ser realizado.';
                            $status = 'existente_identico';
                            $class = 'existente identico';
                            $msg = 'Arquivos identicos.';
                            $sugestao = '';
                            $opcoes = [];
                        } else {

                            // =============================================================== encontrado em ambos - diferentes
                            
                            if(in_array($pathname, $conf['diretorioOuArquivo_implementacao'])){
                                
                                // ======================================  provenientes da implementacao do projeto em questao (manter como estah)
                                $status_show = 'ALTERAÇÕES de PROJETO';
                                $status_title = 'Arquivos com diferenças encontradas em decorrência do desenvolvimento do projeto.';
                                $status = 'existente_diferente_implementacao';
                                $class = 'existente diferente implementacao';
                                $msg = 'Arquivos implementacionais.';
                                $sugestao = '.';
                                $opcoes = [
                                    '<',
                                    '.',
                                    '>'
                                ];
                                
                            }else{
                            
                                // ======================================  diferentes para analise
                                $status_show = 'DIFERENÇAS ENCONTRADAS';
                                $status_title = 'Arquivos com diferenças detectadas! Verifique a melhor opção a ser tomada.';
                                $status = 'existente_diferente';
                                $class = 'existente diferente verificar';
                                $msg = 'Diferênças encontradas!';
                                $sugestao = '.';
                                $opcoes = [
                                    '<',
                                    '.',
                                    '>'
                                ];
                            }
                            
                            
                        }
                    } else {

                        throw new Exception("Nenhum dos arquivos listados foi encontrado (inconssitência).");
                    }
                }
                { // ============================================================================================= VERIFICACOES EXCEPCIONAIS (ADICIONAIS)!

                    { // ARQUIVOS CRITICOS - UMA DECISAO PRECISA SER TOMADA!

                        // ARQUIVO OU ARQUIVOS DE PASTA EVENTUALMENTE MODIFICADOS
                        foreach ($conf['diretorioOuArquivo_critico'] as $dac) {

                            { // montagem das condicoes
                                { // parmetros necessarios                                    
                                    { // file_part = caminho ou nome completo do arquivo
                                        if (is_dir($dac)) {
                                            //se o item no loop quem questao for um diretorio, entao obtera apenas a parte inicial do conteudo de acordo
                                            //com a quantidade de caracteres do caminho informado (dfc)
                                            $diretorioParte_ou_nomeDoArquivoCompleto = substr($pathname, 0, strlen($dac));
                                        } else if (is_file($dac)) {
                                            $diretorioParte_ou_nomeDoArquivoCompleto = $pathname;
                                        } else {
                                            throw new Exception("Tipo de arquivo definido na lista de 'Diretorio ou Arquivo Critico' não permitido (Tipo:" . gettype($dac) . " | Path: $dac).");
                                        }
                                        // deb($file_part);
                                    }
                                }
                                { // condicoes propriamente ditas
                                    $condicoes = [
                                        $status != 'existente_identico',
                                        $dac == $diretorioParte_ou_nomeDoArquivoCompleto
                                    ];
                                }
                            }
                            { // verificacao das condicoes
                                if (! in_array(false, $condicoes)) {

                                    { // parametros a serem modificados
                                        { // class
                                            $class .= ' critico ';
                                        }

                                        { // sugestao
                                            if ($status == 'novo_faltando-na-base') {
                                                $sugestao = '>>';
                                            } else if ($status == 'novo_faltando-na-prod') {
                                                $sugestao = '<<';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    {
                        // ...
                    }
                }
            }

            { // COMPARISONS RESULT
                $comparison[$i]['file'] = $file;
                $comparison[$i]['base'] = $base_path;
                $comparison[$i]['base_show'] = $base_path_show;
                $comparison[$i]['prod'] = $prod_path;
                $comparison[$i]['prod_show'] = $prod_path_show;
                $comparison[$i]['status'] = $status;
                $comparison[$i]['status_show'] = $status_show;
                $comparison[$i]['status_title'] = $status_title;
                $comparison[$i]['class'] = $class;
                $comparison[$i]['sugestao'] = $sugestao;
                $comparison[$i]['msg'] = $msg;
                $comparison[$i]['opcoes'] = self::get_dev_sync_go_options_html($file, $opcoes, $sugestao, $prod_path, $base_path);
                $comparison[$i]['paths'] = $paths;
                $comparison[$i]['lookslike'] = false;
            }

            { // FILTERS BTN DATA

                { // parameters
                    { // quant
                        if (isset($filters[$status]['quant'])) {
                            $btn_quant = $filters[$status]['quant'] + 1;
                        } else {
                            $btn_quant = 1;
                        }
                    }
                }

                $filters[$status]['quant'] = $btn_quant;
                $filters[$status]['title'] = $status_title;
                $filters[$status]['show'] = $status_show;
                $filters[$status]['class'] = $class;
            }

            $i ++;
        }

        { // return
          // ###################################################################################################################################################
          // ###################################################################################################################################################
            $return = [];
            $return['filters'] = $filters;
            $return['comparison'] = $comparison;
            // ###################################################################################################################################################
            // ###################################################################################################################################################
        }

        return $return;
    }

    static private function get_dev_sync_go_options_html($file, $opcoes, $sugestao, $prod_path, $base_path)
    {
        if (sizeof($opcoes) == 0) {
            $return = "Nenhum procedimento necessário.";
        } else {

            $return = "<select name='arquivo[]'>";

            foreach ($opcoes as $opcao) {

                { // selected ? (com base na sugestao)
                    if ($opcao == $sugestao) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                }

                switch ($opcao) {
                    // ==========================================================================================================
                    case '<<':
                        $return .= "<option value='$base_path;$prod_path' $selected>&#9978; Criar arquivo na PRODUÇÃO &#9664;&#9664;&#9664;</option>";
                        $return .= "<option value='$base_path'> &#9940; REMOVER arquivo da BASE &#9658;&#9658;&#9658; </option>";
                        break;
                    // ==========================================================================================================
                    case '<':
                        $return .= "<option value='$base_path;$prod_path' $selected>&#9889; Atualizar PRODUÇÃO &#9664;&#9664;&#9664;</option>";
                        break;
                    // ==========================================================================================================
                    case '.':                        
                        $return .= "<option value='' $selected>&#9749; NÃO FAZER NADA!</option>"; //&#9723;
                        break;
                    // ==========================================================================================================
                    case '>':
                        $return .= "<option value='$prod_path;$base_path' $selected>&#9889; Atualizar BASE &#9658;&#9658;&#9658;</option>";
                        break;
                    // ==========================================================================================================
                    case '>>':
                        $return .= "<option value='$prod_path;$base_path' $selected>&#9978; Criar arquivo na BASE &#9658;&#9658;&#9658;  </option>";
                        $return .= "<option value='$prod_path'>&#9940; REMOVER arquivo da PRODUÇÃO &#9664;&#9664;&#9664;</option>";
                        break;
                    // ==========================================================================================================
                    default:
                        throw new Exception("Opção inválida ($opcao).");
                        break;
                }
            }
            $return .= "</select>";
        }

        return $return;
    }

    // ##########################################################################################################################
}

?>