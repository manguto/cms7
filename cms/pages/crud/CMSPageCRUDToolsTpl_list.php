<?php
namespace manguto\cms7\lib\cms;

use manguto\cms7\lib\model\ModelHelper;
use manguto\cms7\lib\model\ModelReference;

class CMSPageCRUDToolsTpl_list
{

    static function ContentReplaces(string $modelname, string $content)
    {
        
        { // basic
            $content = str_replace('Zzz', ucfirst($modelname), $content);
            $content = str_replace('zzz', $modelname, $content);
        }
        {
            $FILTER_FIELDS = self::FILTER_FIELDS($modelname);
            $content = str_replace('#FILTER_FIELDS#', $FILTER_FIELDS, $content);
        }
        
        {
            $FILTER_SCRIPTS = self::FILTER_SCRIPTS($modelname);
            $content = str_replace('#FILTER_SCRIPTS#', $FILTER_SCRIPTS, $content);
        }
        
        return $content;
    }
    
    
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function FILTER_FIELDS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = ModelHelper::getObjectClassname($modelname);
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
                " . self::FILTER_FIELDS_TYPES($objectClassname, $fieldname) . "
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
    static private function FILTER_FIELDS_TYPES($objectClassname, $fieldname)
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
        if (ModelReference::itsReferenceAttributeSingle($fieldname) || ModelReference::itsReferenceAttributeMultiple($fieldname)) {
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
    
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function FILTER_SCRIPTS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = ModelHelper::getObjectClassname($modelname);
        }
        
        {
            $filters = $objectClassname::get_filters($modelname);
            // deb($filters);
            foreach ($filters as $fieldname => $value) {
                
                $return[] = self::FILTER_SCRIPTS_TYPES($objectClassname, $fieldname);
            }
        }
        
        $return = implode(chr(10) . '    ', $return);
        // debc($return);
        return $return;
    }
    
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function FILTER_SCRIPTS_TYPES($objectClassname, $fieldname)
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
        if (ModelReference::itsReferenceAttributeSingle($fieldname) || ModelReference::itsReferenceAttributeMultiple($fieldname)) {
            // deb($fieldname,0);
            $return = "combo_ordenar('#$fieldname',true,false); ";
            return $return;
        }
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        // ---------------------------------------------------------------------------------------------------------------------------- padrao
        return "";
    }
}

?>