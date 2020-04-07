<?php
namespace manguto\cms7\lib\cms\dev;

use manguto\cms7\lib\Diretorios;
use manguto\cms7\lib\Arquivos;
use manguto\cms7\lib\Exception;
use manguto\cms7\lib\ServerHelp;
use manguto\cms7\lib\ProcessResult;

class CMSSync
{

    // diretorio dos arquivos base para replicacao
    // const base_dir = SIS_VENDOR_MANGUTO_REPOSITORY_PATH . 'res/cms/';
    const base_dir = SIS_VENDOR_MANGUTO_REPOSITORY_PATH . 'struct/';

    // diretorio com os arquivos em producao
    // const prod_dir = '../' . SIS_FOLDERNAME . '/';
    const prod_dir = SIS_PATH . '/';

    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
    static function getCMSFileInfo_array($type = '')
    {
        $return = [];
        $cmsFileInfo_array = CMSSyncInfo::cmsFileInfo_array;
        foreach ($cmsFileInfo_array as $cmsFilename => $cmsFileType) {
            if ($type == '') {
                $return[] = $cmsFilename;
            } else {
                if ($cmsFileType == $type) {
                    $return[] = $cmsFilename;
                }
            }
        }
        return $return;
    }

    // #########################################################################################################################################
    static function get_files()
    {
        $return = [];
        {
            { // ######################################################################################################## prod
                $set = [];
                $set['res'] = Diretorios::obterArquivosPastas(SIS_RES_PATH, true, true, false);
                $set['cms'] = Diretorios::obterArquivosPastas(SIS_CMS_PATH, true, true, false);
                $prods = [];
                foreach ($set as $fs) {
                    foreach ($fs as $filename) {
                        $prods[$filename] = $filename;
                    }
                }
                ksort($prods);
                // deb($prods);
            }
            { // ######################################################################################################## base
                $set = [];
                //$path = SIS_VENDOR_MANGUTO_REPOSITORY_PATH . 'res' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
                $path = ServerHelp::fixds(self::base_dir);
                $set['res'] = Diretorios::obterArquivosPastas($path . SIS_RES_PATH, true, true, false);
                $set['cms'] = Diretorios::obterArquivosPastas($path . SIS_CMS_PATH, true, true, false);
                $bases = [];
                foreach ($set as $fs) {
                    foreach ($fs as $filename) {
                        $key = str_replace('.php_', '.php', $filename);
                        $key = str_replace($path, '', $key);
                        $bases[$key] = $filename;
                    }
                }
                ksort($bases);
                // deb($bases);
            }
            { // ######################################################################################################## all
                $alls_pre = [];
                $critic_array = CMSSync::getCMSFileInfo_array('critic');
                $generic_array = CMSSync::getCMSFileInfo_array('generic');

                foreach ($prods as $filename => $path) {
                    $f_test = str_replace('\\', '/', $filename);
                    {
                        if (in_array($f_test, $critic_array)) {
                            $type = 'critic';
                        } else if (in_array($f_test, $generic_array)) {
                            $type = 'generic';
                        } else {
                            $type = 'new';
                        }
                    }
                    $alls_pre[$filename] = [
                        'file' => $f_test,
                        'type' => $type
                    ];
                }
                // ########################################################################################################
                // ############################################################################################# ordenacao
                // ########################################################################################################
                foreach ($bases as $filename => $path) {
                    if (! isset($alls_pre[$filename])) {
                        $f_test = str_replace('\\', '/', $filename);
                        {
                            if (in_array($f_test, $critic_array)) {
                                $type = 'critic';
                            } else if (in_array($f_test, $generic_array)) {
                                $type = 'generic';
                            } else {
                                $type = 'new';
                            }
                        }
                        $alls_pre[$filename] = [
                            'file' => $f_test,
                            'type' => $type
                        ];
                    }
                }
                ksort($alls_pre);
                // deb($alls_pre);
                // ########################################################################################################
                // ######################################################################################### categorizacao
                // ########################################################################################################
                $alls = [];
                { // separacao por pasta (hierarquia)
                    foreach ($alls_pre as $data) {
                        $filename = $data['file'];
                        $folder = ServerHelp::fixds(Arquivos::obterCaminho($filename), '/');
                        $folder = str_replace('/', ' > ', $folder);
                        $key = str_repeat('=====================', substr_count($filename, '/')) . " " . $folder;

                        // <<<<<<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<<<<<
                        $alls[$key][$filename] = $data;
                        // <<<<<<<<<<<<<<<<<<<<<<<<
                        // <<<<<<<<<<<<<<<<<<<<<<<<
                    }
                }
                // deb($alls);
            }
        }
        $return = [
            'prods' => $prods,
            'bases' => $bases,
            'alls' => $alls
        ];

        // deb($return);
        return $return;
    }

    // #########################################################################################################################################

    /**
     *
     * @param string $content
     * @return boolean
     */
    static function updateCMSSyncInfo(string $content)
    {
        $filename = SIS_VENDOR_MANGUTO_REPOSITORY_PATH . 'lib/cms/dev/CMSSyncInfo.php';
        $filename = ServerHelp::fixds($filename);
        /*
         * deb($filename,0);
         * deb(file_exists($filename));/*
         */
        if (Arquivos::copiaSeguranca($filename)) {
            ProcessResult::setSuccess("Cópia de segurança realizada com sucesso ($filename).");
            Arquivos::escreverConteudo($filename, $content);
            ProcessResult::setSuccess("A listagem de arquivos envolvidos na estrutura básica do CMS foi atualizada com sucesso ($filename)!");
        }
        return true;
    }

    // #########################################################################################################################################
    // #########################################################################################################################################
    // #########################################################################################################################################
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

        $return = [
            'filename' => $filename,
            'prod_content_html' => $prod_content_html,
            'base_content_html' => $base_content_html
        ];
        // ViewSync::post_dev_sync_analyse($filename, $prod_content_html, $base_content_html);
        return $return;
    }

    // ##########################################################################################################################
    // ##########################################################################################################################
    // ################################################## PRIVATE ###############################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    static function get_dev_sync_go_analisys()
    {
        { // CONFIGURATIONS - configuracoes fundamentais

            { // diretorios
              // producao
                $prod_dir = Diretorios::fixDirectorySeparator(CMSSync::prod_dir);

                // base
                $base_dir = Diretorios::fixDirectorySeparator(CMSSync::base_dir);
            }
            { // sub-pastas fundamentais
                $target_folders = [
                    'mvc',
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
    static function get_production_files(string $prod_dir, array $target_folders)
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
     * obtem todos os arquivos da base (SIS_VENDOR_MANGUTO_REPOSITORY_PATH/cms/files)
     *
     * @param string $base_dir
     * @param string $target_folders
     * @return string[][]|mixed[][]|array[]
     */
    static function get_base_files(string $base_dir, array $target_folders)
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

    static function get_all_files_envolved(string $prod_dir, string $base_dir, array $target_folders)
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

    static function get_all_files_analisys(array $all_files, string $base_dir)
    {
        $comparison = [];
        $filters = [];

        { // CONFIGURATIONS

            $conf = [];

            { // PASTAS OU ARQUIVOS ONDE OS MESMOS PRECISAM ESTAR SEMPRE IGUAIS com a BASE
                $conf['diretorioOuArquivo_critico'] = [];
                foreach (CMSSync::getCMSFileInfo_array('critic') as $diretorioOuArquivo) {
                    $conf['diretorioOuArquivo_critico'][] = $diretorioOuArquivo;
                }
            }

            { // PASTAS OU ARQUIVOS RESULTADO DE MODIFICACOES IMPLEMENTACIONAIS
                $conf['diretorioOuArquivo_implementacao'] = [];
                foreach (CMSSync::getCMSFileInfo_array('generic') as $diretorioOuArquivo) {
                    $conf['diretorioOuArquivo_implementacao'][] = $diretorioOuArquivo;
                }
            }

            { // PASTAS OU ARQUIVOS DESNECESSARIOS
                $conf['diretorioOuArquivo_lixo'] = [];
                foreach (CMSSync::getCMSFileInfo_array('trash') as $diretorioOuArquivo) {
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

                        { // OBSERVACOES!
                            if (in_array($relative_filename_path, $conf['diretorioOuArquivo_implementacao'])) {
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
                                        // deb($dac,0); deb($ext);

                                        if (trim($ext) == '') {
                                            // se o item no loop em questao for um diretorio, entao obtera apenas a parte inicial do conteudo de acordo
                                            // com a quantidade de caracteres do caminho informado (dfc)
                                            $diretorioParte_ou_nomeDoArquivoCompleto = substr($pathname, 0, strlen($dac));
                                        } else { // if (is_file($dac))
                                                 // se o item no loop em questao for um arquivo, entao obtera o nome completo do arquivo da base
                                            $diretorioParte_ou_nomeDoArquivoCompleto = $pathname;
                                        }

                                        /*
                                         * else {
                                         * throw new Exception("Arquivo da lista de 'Diretorio ou Arquivo Critico' não encontrado: '$dac'.");
                                         * }
                                         */
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

    static function get_dev_sync_go_options_html($file, $opcoes, $sugestao, $prod_path, $base_path)
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
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
    // ##########################################################################################################################
}

?>