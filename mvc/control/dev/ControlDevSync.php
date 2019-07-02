<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\dev\ViewDevSync;

class ControlDevSync extends ControlDev
{

    // #########################################################################################################################################
    // PASTAS OU ARQUIVOS ONDE OS MESMOS PRECISAM ESTAR SEMPRE IGUAIS com a BASE
    //#########################################################################################################################################
    const diretorioOuArquivo_criticos = [
        // ============================================================================= res
        'res/css/style.css',
        'res/css/print.css',
        'res/fonts/index.php',
        'res/js/scripts.js',
        'res/js/scripts_form.js',
        // ============================================================================= control
        'sis/control/admin/ControlAdminZzz.php',
        'sis/control/site/ControlSiteZzz.php',
        'sis/control/site/ControlSiteModelagem.php',
        'sis/control/crud/ControlCRUDZzz.php',
        // ============================================================================= model
        'sis/model/Zzz.php',
        // ============================================================================= lib
        'sis/lib/Modelagem.php',
        // ============================================================================= view        
        'sis/view/admin/ViewAdminZzz.php',
        'sis/view/site/ViewSiteZzz.php',
        'sis/view/site/ViewSiteModelagem.php', 
        'sis/view/crud/ViewCRUDZzz.php',
        // ============================================================================= tpl
        'sis/tpl/admin/admin_zzz.html',
        'sis/tpl/site/site_zzz.html',
        'sis/tpl/site/site_modelagem.html',      
        'sis/tpl/site/modelagem/telas/administrativas/01_database.html',
        'sis/tpl/crud/crud_zzz.html',
        'sis/tpl/crud/crud_zzz_edit.html',
        'sis/tpl/crud/crud_zzz_view.html' 
    ];

    // #########################################################################################################################################
    // PASTAS OU ARQUIVOS RESULTADO DE MODIFICACOES IMPLEMENTACIONAIS
    //#########################################################################################################################################
    const diretorioOuArquivo_implementacoes = [
        // ============================================================================= basic
        //'index.php',
        'configurations.php',
        'functions.php',
        // ============================================================================= control
        'sis/control/admin/ControlAdminHome.php',
        'sis/control/site/ControlSiteHome.php',
        // ============================================================================= model
        // ============================================================================= lib
        'sis/lib/ModelagemExtra.php',
        // ============================================================================= view        
        // ============================================================================= tpl
        'sis/tpl/general/_footer_content.html',
        'sis/tpl/general/_header_title.html',
        'sis/tpl/general/_menu',
        'sis/tpl/site/_menu.html',
        'sis/tpl/site/site_home.html',
        //---------------------------------------------------- modelagem
        'sis/lib/Modelagem.php',
        //-------------------------------------------------------------!        
        'sis/tpl/site/modelagem/telas/principais/01_bem_vindo.html',
        'sis/tpl/site/modelagem/telas/principais/info.html',
        //-------------------------------------------------------------!
        'sis/tpl/admin/_menu.html',
        'sis/tpl/admin/admin_home.html'        
    ];

    // #########################################################################################################################################
    // PASTAS OU ARQUIVOS DESNECESSARIOS (LIXOS)
    //#########################################################################################################################################
    const diretorioOuArquivo_lixos = [
        'cache'
    ];

    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/sync', function () {
            self::PrivativeDevZone();
            // ControlDevSync::get_dev_sync();
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
                    // CRIACAO OU ATUALIZACAO DE ARQUIVO
                    // ----------------------------------------------
                    $origin_path = $info[0];
                    $destin_path = $info[1];
                    Arquivos::copiarArquivo($origin_path, $destin_path);
                    ProcessResult::setSuccess("Arquivo criado/atualizado com sucesso! <br>$origin_path => $destin_path");
                    // ----------------------------------------------------------------------------------------------
                } else if (sizeof($info) == 1) {
                    // ----------------------------------------------
                    // REMOCAO DE ARQUIVO
                    // ----------------------------------------------
                    $remove_file_path = $info[0];
                    Arquivos::excluir($remove_file_path);
                    ProcessResult::setSuccess("Arquivo removido com sucesso! <br>[$remove_file_path]");
                    // ----------------------------------------------------------------------------------------------
                } else {
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
                    $base_dir = VENDOR_MANGUTO_PRJ_ROOT . 'res/cms/';
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
            // deb($all_files);
        }
        { // ANALISES - ANALISE QUANTO A TOMADA DE ACAO
            $analisys = self::get_all_files_analisys($all_files, $base_dir);
            // deb($analisys);
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
            $relative_filepath = str_replace($prod_dir, '', $prod_file);
            $prod_file = str_replace($prod_dir, '', $prod_file);
            $prod_files[$relative_filepath] = $prod_file;
        }

        foreach ($target_folders as $target_folder) {
            $prod_files_tmp = Diretorios::obterArquivosPastas($prod_dir . $target_folder, true, true, true);
            foreach ($prod_files_tmp as $prod_file) {

                $relative_filepath = str_replace($prod_dir, '', $prod_file);
                $prod_file = str_replace($prod_dir, '', $prod_file);
                $prod_files[$relative_filepath] = $prod_file;
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
            foreach ($prod_files as $relative_filename_path => $prod_filename_path) {
                if (is_dir($prod_filename_path))
                    continue;
                $all_files[$relative_filename_path]['prod'] = $prod_filename_path;
            }
            foreach ($base_files as $relative_filename_path => $base_filename_path) {
                if (is_dir($base_filename_path))
                    continue;
                $all_files[$relative_filename_path]['base'] = $base_filename_path;
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
                foreach (self::diretorioOuArquivo_criticos as $diretorioOuArquivo) {
                    $conf['diretorioOuArquivo_critico'][] = $diretorioOuArquivo;
                }
            }

            { // PASTAS OU ARQUIVOS RESULTADO DE MODIFICACOES IMPLEMENTACIONAIS
                $conf['diretorioOuArquivo_implementacao'] = [];
                foreach (self::diretorioOuArquivo_implementacoes as $diretorioOuArquivo) {
                    $conf['diretorioOuArquivo_implementacao'][] = $diretorioOuArquivo;
                }
            }

            { // PASTAS OU ARQUIVOS DESNECESSARIOS
                $conf['diretorioOuArquivo_lixo'] = [];
                foreach (self::diretorioOuArquivo_lixos as $diretorioOuArquivo) {
                    $conf['diretorioOuArquivo_lixo'][] = $diretorioOuArquivo;
                }
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
        foreach ($all_files as $relative_filename_path => $info) {
            // deb($file);

            { // trash folders files jump
                $folder = Arquivos::obterCaminho($relative_filename_path);
                // deb($folder,0);
                if (in_array($folder, $conf['diretorioOuArquivo_lixo'])) {
                    continue;
                }
            }

            { // parametros necessarios para avaliacao

                { // producao
                    $found_at_the_production = isset($info['prod']);
                    $production_path = $found_at_the_production ? $info['prod'] : $relative_filename_path;
                    $production_path_show = $found_at_the_production ? $production_path : '...';
                }

                { // base
                    $found_at_the_base = isset($info['base']);
                    $base_path = $found_at_the_base ? $info['base'] : $base_dir . str_replace('.php', '.php_', $relative_filename_path);
                    $base_path_show = $found_at_the_base ? $base_path : '...';
                }

                { // paths (para enviar para analysis)
                    $paths = urlencode($production_path . ';' . $base_path);
                }
                { //
                    $pathname = $production_path;
                }
            }

            { // avaliacao das informacoes do arquivo
              // STATUS
              // OPCOES
              // MSG

                { // ============================================================================================= ANALISE e LEVANTAMENTO de PARAMETROS
                    if ($found_at_the_production && ! $found_at_the_base) {
                        
                        
                        
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
                        
                        {//OBSERVACOES! 
                            if(in_array($relative_filename_path, $conf['diretorioOuArquivo_implementacao'])){
                                $class .= ' critico';
                                $sugestao = '>>';
                            }
                        }
                        
                        
                    } else if (! $found_at_the_production && $found_at_the_base) {

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
                    } else if ($found_at_the_production && $found_at_the_base) {

                        // ========================================================================================= encontrado em ambos

                        if (Arquivos::verificarArquivosIdenticos($production_path, $base_path)) {

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

                            if (in_array($pathname, $conf['diretorioOuArquivo_implementacao'])) {

                                // ====================================== provenientes da implementacao do projeto em questao (manter como estah)
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
                            } else {

                                // ====================================== diferentes para analise
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
                                        $ext = Arquivos::obterExtensao($dac);                                        
                                        //deb($dac,0); deb($ext);
                                        
                                        if (trim($ext)=='') {
                                            // se o item no loop em questao for um diretorio, entao obtera apenas a parte inicial do conteudo de acordo
                                            // com a quantidade de caracteres do caminho informado (dfc)
                                            $diretorioParte_ou_nomeDoArquivoCompleto = substr($pathname, 0, strlen($dac));
                                        } else { // if (is_file($dac))
                                            // se o item no loop em questao for um arquivo, entao obtera o nome completo do arquivo da base 
                                            $diretorioParte_ou_nomeDoArquivoCompleto = $pathname;
                                        }
                                        
                                        /*else {
                                            throw new Exception("Arquivo da lista de 'Diretorio ou Arquivo Critico' não encontrado: '$dac'.");
                                        }*/
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
                $comparison[$i]['file'] = $relative_filename_path;
                $comparison[$i]['base'] = $base_path;
                $comparison[$i]['base_show'] = $base_path_show;
                $comparison[$i]['prod'] = $production_path;
                $comparison[$i]['prod_show'] = $production_path_show;
                $comparison[$i]['status'] = $status;
                $comparison[$i]['status_show'] = $status_show;
                $comparison[$i]['status_title'] = $status_title;
                $comparison[$i]['class'] = $class;
                $comparison[$i]['sugestao'] = $sugestao;
                $comparison[$i]['msg'] = $msg;
                $comparison[$i]['opcoes'] = self::get_dev_sync_go_options_html($relative_filename_path, $opcoes, $sugestao, $production_path, $base_path);
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
                        $return .= "<option value='' $selected>&#9749; NÃO FAZER NADA!</option>"; // &#9723;
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