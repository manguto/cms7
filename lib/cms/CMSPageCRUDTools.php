<?php
namespace manguto\cms5\mvc;

use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\repository\Repository;
use manguto\cms5\lib\repository\RepositoryReferences;

class CMSPageCRUDTools
{

    static function set_structure($tablename)
    {
        {
            // deb($tablename);
            $Modelname = ucfirst($tablename);
        }

        // controler
        self::set_controler_structure($tablename, $Modelname);

        // view
        self::set_view_structure($tablename, $Modelname);

        // templates
        self::set_tpl_structures($tablename, $Modelname);
        self::set_tpl_edit_structures($tablename, $Modelname);
        self::set_tpl_view_structures($tablename, $Modelname);

        return get_defined_vars();
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_controler_structure(string $tablename, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/control/ControlCRUDZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }
        { // replaces
            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $tablename, $content);
            }

            {
                // ...
            }
        }
        { // save
            self::save($tablename, $Modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_view_structure(string $tablename, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/view/ViewCRUDZzz.php';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $tablename, $content);
            }

            { // filters
                $content = str_replace('#FILTER_PARAMETERS#', self::set_view_structure___FILTERS($tablename), $content);
            }
            
            { // titles
                $content = str_replace('#TITLE_ARRAY#', self::set_view_structure___TITLES($tablename), $content);
            }
            
        }

        { // save
            self::save($tablename, $Modelname, $filename, $content);
        }
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_view_structure___TITLES($tablename)
    {
        $return = [];
        
        { // parameters
            $objectClassname = Repository::getObjectClassname($tablename);
            // deb($objectClassname);
        }

        { // titles
            
            $titles = $objectClassname::get_titles($tablename);
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
    static private function set_view_structure___FILTERS($tablename)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($tablename);
            // deb($objectClassname);
            $Modelname = ucfirst($tablename);
        }

        {
            $filters = $objectClassname::get_filters($tablename);
            // deb($filters);
        }

        foreach ($filters as $fieldname => $value) {

            if (isset($objectClassname::$$fieldname)) {
                $return[] = "\$filter_" . $fieldname . "_array = $Modelname::$$fieldname;";
            }
            
            if (RepositoryReferences::itsReferenceAttributeSimple($fieldname) || RepositoryReferences::itsReferenceAttributeMultiple($fieldname)) {
                
                $repositoryName = RepositoryReferences::getReferencedModelName($fieldname);
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
    static private function set_tpl_structures(string $tablename, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces
            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $tablename, $content);
            }
            {
                $content = str_replace('#FILTER_FIELDS#', self::set_tpl_structures___FILTER_FIELDS($tablename), $content);
            }
            
            {
                $content = str_replace('#FILTER_SCRIPTS#', self::set_tpl_structures___FILTER_SCRIPTS($tablename), $content);
            }
            
        }

        { // save
            self::save($tablename, $Modelname, $filename, $content);
        }
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function set_tpl_structures___FILTER_SCRIPTS($tablename)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($tablename);
        }

        {
            $filters = $objectClassname::get_filters($tablename);
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
        if (RepositoryReferences::itsReferenceAttributeSimple($fieldname) || RepositoryReferences::itsReferenceAttributeMultiple($fieldname)) {
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
    static private function set_tpl_structures___FILTER_FIELDS($tablename)
    {
        $return = [];
        { // parameters
            $objectClassname = Repository::getObjectClassname($tablename);
        }

        {
            $filters = $objectClassname::get_filters($tablename);
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
        if (RepositoryReferences::itsReferenceAttributeSimple($fieldname) || RepositoryReferences::itsReferenceAttributeMultiple($fieldname)) {
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
    static private function set_tpl_edit_structures(string $tablename, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz_edit.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $tablename, $content);
            }

            {
                // ...
            }
        }

        { // save
            self::save($tablename, $Modelname, $filename, $content);
        }
    }

    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    // ############################################################################################################################################# PRIVATE
    static private function set_tpl_view_structures(string $tablename, string $Modelname)
    {
        { // file - name & content
            $filename = 'sis/tpl/crud_zzz_view.html';
            $content = Arquivos::obterConteudo($filename);
            // debc($content);
        }

        { // replaces

            { // basic
                $content = str_replace('Zzz', $Modelname, $content);
                $content = str_replace('zzz', $tablename, $content);
            }

            {
                // ...
            }
        }

        { // save
            self::save($tablename, $Modelname, $filename, $content);
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
    static private function save($tablename, $Modelname, $filename, $content)
    {
        { // new content save
            $filename_module = $filename;
            $filename_module = str_replace('Zzz', $Modelname, $filename_module);
            $filename_module = str_replace('zzz', $tablename, $filename_module);
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