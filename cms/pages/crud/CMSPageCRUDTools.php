<?php
namespace manguto\cms7\lib\cms;

use manguto\cms7\lib\Arquivos;
use manguto\cms7\lib\ProcessResult;
use manguto\cms7\lib\Arrays;
use manguto\cms7\lib\Exception;

class CMSPageCRUDTools
{

    const models_dir = 'sis/';

    static function set_structure($modelname)
    {
        
        throw new Exception("Processo em desenvolvimento..."); 
        
        $return = [];
        // controler
        $return[] = self::set_controler_structure($modelname);
        
        // view
        $return[] = self::set_view_structure($modelname);

        // templates
        $return[] = self::set_tpl_list_structures($modelname);
        $return[] = self::set_tpl_edit_structures($modelname);
        $return[] = self::set_tpl_view_structures($modelname);

        // merge results
        $return = Arrays::arrayMultiNivelParaSimples($return);
        
        return $return;
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_controler_structure(string $modelname)
    {
        { // file - name & content
            $filename = self::models_dir . 'control/crud/ControlZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }
        { // replaces
            $content = CMSPageCRUDToolsControler::ContentReplaces($modelname, $content);
        }
        { // save
            return self::save($modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_view_structure(string $modelname)
    {
        { // file - name & content
            $filename = self::models_dir . 'view/crud/ViewZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            $content = CMSPageCRUDToolsView::ContentReplaces($modelname, $content);
        }

        { // save
            return self::save($modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_list_structures(string $modelname)
    {
        { // file - name & content
            $filename = self::models_dir . 'tpl/crud/crud_zzz.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            $content = CMSPageCRUDToolsTpl_list::ContentReplaces($modelname, $content);
        }

        { // save
            return self::save($modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_edit_structures(string $modelname)
    {
        { // file - name & content
            $filename = self::models_dir . 'tpl/crud/crud_zzz_edit.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            $content = CMSPageCRUDToolsTpl_edit::ContentReplaces($modelname, $content);
        }

        { // save
            return self::save($modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_view_structures(string $modelname)
    {
        { // file - name & content
            $filename = self::models_dir . 'tpl/crud/crud_zzz_view.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            $content = CMSPageCRUDToolsTpl_view::ContentReplaces($modelname, $content);
        }

        { // save
            return self::save($modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    // ############################################################################################################################################# PRIVATE GENERAL
    static private function save($modelname, $filename, $content)
    {
        { // new content save
            $filename_module = $filename;
            $filename_module = str_replace('Zzz', ucfirst($modelname), $filename_module);
            $filename_module = str_replace('zzz', $modelname, $filename_module);
            // deb($filename_new);
        }
        { // copia de seguranca
            Arquivos::copiaSeguranca($filename_module);
        }
        { // (re)escreve arquivo
            Arquivos::escreverConteudo($filename_module, $content);
        }
        return ProcessResult::setSuccess("Arquivo '$filename_module' " . (file_exists($filename_module) ? 'ATUALIZADO' : 'CRIADO') . " com sucesso.");
    }
}

?>