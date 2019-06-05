<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\model\Model_Helper;
use manguto\cms5\lib\model\Model_Reference;

class CMSPageCRUDToolsView
{

    static function ContentReplaces(string $modelname, string $content)
    {
        {
            $Modelname = ucfirst($modelname);
        }
        { // basic
            $content = str_replace('Zzz', $Modelname, $content);
            $content = str_replace('zzz', $modelname, $content);
        }
        
        { // filters
            $FILTER_PARAMETERS = self::FILTER_PARAMETERS($modelname);
            $content = str_replace('#FILTER_PARAMETERS#', $FILTER_PARAMETERS, $content);
        }
        
        { // titles
            $TITLE_ARRAY = self::TITLE_ARRAY($modelname);
            $content = str_replace('#TITLE_ARRAY#', $TITLE_ARRAY, $content);
        }
        return $content;
    }

    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function FILTER_PARAMETERS($modelname)
    {
        $return = [];
        { // parameters
            $objectClassname = Model_Helper::getObjectClassname($modelname);
            // deb($objectClassname);
            $Modelname = ucfirst($modelname);
        }
        
        {
            //$filters = $objectClassname::get_filters($modelname);
            $filters = Model_Helper::get_filters($modelname);
            // deb($filters);
        }
        
        foreach ($filters as $fieldname => $value) {
            
            if (isset($objectClassname::$$fieldname)) {
                $return[] = "\$filter_" . $fieldname . "_array = $Modelname::$$fieldname;";
            }
            
            if (Model_Reference::itsReferenceAttributeSimple($fieldname) || Model_Reference::itsReferenceAttributeMultiple($fieldname)) {
                
                $repositoryName = Model_Reference::getReferencedModelName($fieldname);
                // deb($repositoryName);
                $FieldModelname = Model_Helper::getObjectClassname($repositoryName);
                $return[] = "\$filter_" . $fieldname . "_array = $FieldModelname::getList('', \$returnAsObject=true, \$loadReferences=true);";
            }
        }
        $return = implode(chr(10) . '            ', $return);
        return $return;
    }
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    // ============================================================================================================================================== PRIVATE
    static private function TITLE_ARRAY($modelname)
    {
        $return = [];
        
        { // parameters
            $objectClassname = Model_Helper::getObjectClassname($modelname);
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
}

?>