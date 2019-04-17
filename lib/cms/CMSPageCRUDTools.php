<?php
namespace manguto\manguto\mvc;

use manguto\manguto\lib\Arquivos;
use manguto\manguto\lib\ProcessResult;
use manguto\manguto\repository\Repository;
use manguto\manguto\repository\RepositoryReferences;

class CMSPageCRUDTools
{

    static function set_structure($modelname)
    {
        {
            // deb($modelname);
            $Modelname = ucfirst($modelname);
        }

        // controler
        self::set_controler_structure($modelname, $Modelname);

        // view
        self::set_view_structure($modelname, $Modelname);

        // templates
        self::set_tpl_structures($modelname, $Modelname);
        self::set_tpl_edit_structures($modelname, $Modelname);
        self::set_tpl_view_structures($modelname, $Modelname);

        return get_defined_vars();
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_controler_structure(string $modelname, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/control/ControlCRUDZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }
        { // replaces
            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $modelname, $content);
            }

            {
                // ...
            }
        }
        { // save
            self::save($modelname, $Modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_view_structure(string $modelname, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/view/ViewCRUDZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $modelname, $content);
            }

            { // filters
                $content = str_replace('#FILTER_PARAMETERS#', self::set_view_structure___FILTERS($modelname), $content);
            }
            
            { // titles
                $content = str_replace('#TITLE_ARRAY#', self::set_view_structure___TITLES($modelname), $content);
            }
            
        }

        { // save
            self::save($modelname, $Modelname, $filename, $content);
        }
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_view_structure___TITLES($modelname)
    {
        $return = [];
        
        { // parameters
            $objectClassname = Repository::getObjectClassname($modelname);
            // deb($objectClassname);
        }

        { // titles
            
            $titles = $objectClassname::get_titles($modelname);
            // deb($titles);
        
            foreach ($titles as $fieldname => $Fieldname) {                
                $return[] = "\$title_array['$fieldname'] =  '$Fieldname';";            
            }
        }
        $return = implode(chr(10) . '            ', $return);
        return $return;
    }
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_view_structure___FILTERS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($modelname);
            // deb($objectClassname);
            $Modelname = ucfirst($modelname);
        }

        {
            $filters = $objectClassname::get_filters($modelname);
            // deb($filters);
        }

        foreach ($filters as $fieldname => $value) {

            if (isset($objectClassname::$$fieldname)) {
                $return[] = "\$filter_" . $fieldname . "_array = $Modelname::$$fieldname;";
            }
            
            if (RepositoryReferences::ehParametroReferencial($fieldname) || RepositoryReferences::ehParametroReferencialMultiplo($fieldname)) {
                
                $repositoryName = RepositoryReferences::getPossibleRepositoryName($fieldname);
                //deb($repositoryName);
                $FieldModelname = Repository::getObjectClassname($repositoryName);
                $return[] = "\$filter_" . $fieldname . "_array = $FieldModelname::getList('', \$returnAsObject=true, \$loadReferences=true);";
            }
            
        }
        $return = implode(chr(10) . '            ', $return);
        return $return;
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_structures(string $modelname, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $modelname, $content);
            }
            {
                $content = str_replace('#FILTER_FIELDS#', self::set_tpl_structures___FILTER_FIELDS($modelname), $content);
            }
            
            {
                $content = str_replace('#FILTER_SCRIPTS#', self::set_tpl_structures___FILTER_SCRIPTS($modelname), $content);
            }
            
        }

        { // save
            self::save($modelname, $Modelname, $filename, $content);
        }
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_tpl_structures___FILTER_SCRIPTS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($modelname);
        }

        {
            $filters = $objectClassname::get_filters($modelname);
            // deb($filters);
            foreach ($filters as $fieldname => $value) {
                
                    $return[] = self::set_tpl_structures___FILTER_SCRIPTS_TYPES($objectClassname, $fieldname);
            }
        }

        $return = implode(chr(10).'    ', $return);
        //debc($return);
        return $return;
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_tpl_structures___FILTER_SCRIPTS_TYPES($objectClassname, $fieldname)
    {
        
        // deb($objectClassname,0);
        // deb($fieldname,0);        
        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // verifica se existe o campo em questao possui um combo fixo (predefinido como constante da classe)
        if (isset($objectClassname::$$fieldname)) {
            // deb($fieldname,0);
            $return = "combo_ordenar('#$fieldname',true,false); ";
            return $return;
        }
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // verifica se existe o campo em questao possui um combo fixo (predefinido como constante da classe)
        if (RepositoryReferences::ehParametroReferencial($fieldname) || RepositoryReferences::ehParametroReferencialMultiplo($fieldname)) {
            // deb($fieldname,0);
            $return = "combo_ordenar('#$fieldname',true,false); ";
            return $return;
        }        
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        return "";
    }
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_tpl_structures___FILTER_FIELDS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($modelname);
        }

        {
            $filters = $objectClassname::get_filters($modelname);
            // deb($filters);
            foreach ($filters as $fieldname => $value) {
                {
                    $col_w = 'col-' . rand(2, 6);
                    $unit = "
            <div class='col $col_w mb-2 campo text-left'>
                <label for='$fieldname' class=''>$value:</label>
                " . self::set_tpl_structures___FILTER_FIELDS_TYPES($objectClassname, $fieldname) . "
			</div>";
                }
                // debc($unit);
                $return[] = $unit;
            }
        }
        $return = implode('', $return);
        return $return;
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_tpl_structures___FILTER_FIELDS_TYPES($objectClassname, $fieldname)
    {

        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // ------------------------------------------------------------------------------------------------------------------------- combo fixo
        // verifica se existe o campo em questao possui um combo fixo (predefinido como constante da classe)
        if (isset($objectClassname::$$fieldname)) {
            // deb($fieldname,0);
            $return = "<select class='form-control' id='$fieldname' name='{$fieldname}[]' multiple='multiple'>
                    {loop=\"\$filter_" . $fieldname . "_array\"}
                    <option value='{\$key}' {if=\"in_array(\$key,GET('$fieldname',[]))\"} selected='selected' {/if}>{\$value}</option>
                    {/loop}
                </select>";
            return $return;
        }
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // ------------------------------------------------------------------------------------------------------------------------- referencia
        // verifica se existe o campo em questao possui um combo fixo (predefinido como constante da classe)
        if (RepositoryReferences::ehParametroReferencial($fieldname) || RepositoryReferences::ehParametroReferencialMultiplo($fieldname)) {
            // deb($fieldname,0);
            $return = "<select class='form-control' id='$fieldname' name='{$fieldname}[]' multiple='multiple'>
                    {loop=\"\$filter_" . $fieldname . "_array\"}                    
                    <option value='{\$key}' {if=\"in_array(\$key,GET('$fieldname',[]))\"} selected='selected' {/if}>{\$value}</option>
                    {/loop}
                </select>";
            return $return;
        }

        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        return "<input type='text' class='form-control' id='$fieldname' name='$fieldname' value='{function=\"GET('$fieldname')\"}' />";
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_edit_structures(string $modelname, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz_edit.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $modelname, $content);
            }

            {
                // ...
            }
        }

        { // save
            self::save($modelname, $Modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_view_structures(string $modelname, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz_view.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $modelname, $content);
            }

            {
                // ...
            }
        }

        { // save
            self::save($modelname, $Modelname, $filename, $content);
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
    static private function save($modelname, $Modelname, $filename, $content)
    {
        { // new content save
            $filename_module = $filename;
            $filename_module = str_replace('Zzz', $Modelname, $filename_module);
            $filename_module = str_replace('zzz', $modelname, $filename_module);
            // deb($filename_new);
        }
        {
            $arquivo_existente = file_exists($filename_module) ? true : false;
            // deb($arquivo_existente);
        }
        { // copia de seguranca
            Arquivos::copiaSeguranca($filename_module, FALSE, '___Ymd-His');
        }
        { // (re)escreve arquivo
            Arquivos::escreverConteudo($filename_module, $content);
        }
        ProcessResult::setSuccess("Arquivo '$filename_module' " . ($arquivo_existente ? 'ATUALIZADO' : 'CRIADO') . " com sucesso.");
    }
}

?>