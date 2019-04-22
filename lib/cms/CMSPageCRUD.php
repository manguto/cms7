<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\repository\Repository;
use manguto\cms5\lib\repository\RepositoryReferences;

class CMSPageCRUD extends CMSPage
{

    public function __construct($opts = array(), $tpl_dir = 'crud/')
    {
        parent::__construct($opts, $tpl_dir);
    }

    static function List___get_register_array(string $tablename,string $conditions='',array $title_array)
    {
        $ObjectClassname = Repository::getObjectClassname($tablename);
        
        { // full content
            $obj_array = $ObjectClassname::getList($conditions, $returnAsObject = true, $loadReferences = true);
            // deb($zzz_array);
        }

        {
            $row_array = [];

            foreach ($obj_array as $id => $obj) {
                foreach (array_keys($title_array) as $fieldname) {

                    { // caso seja um campo referencial
                        if (RepositoryReferences::ehParametroReferencial($fieldname) || RepositoryReferences::ehParametroReferencialMultiplo($fieldname)) {
                            $fieldame_call = RepositoryReferences::getPossibleRepositoryName($fieldname);
                        } else {
                            $fieldame_call = $fieldname;
                        }
                    }

                    $method = 'get' . $fieldame_call;
                    $item = $obj->$method();
                    if (is_array($item)) {
                        $item_array = $item;
                        $item_show = [];
                        foreach ($item_array as $item) {
                            $item_show[] = "$item";
                        }
                        $item_show = implode(', ', $item_show);
                    } else {

                        if (isset($ObjectClassname::$$fieldname)) {
                            $fieldname_array = $ObjectClassname::$$fieldname;
                            // deb($fieldname_array);
                            $item_show = $fieldname_array[$item];
                        } else {
                            $item_show = "$item";
                        }
                    }
                    $row_array[$id][$fieldname] = $item_show;
                }
            }
        }
        return $row_array;
    }
}

?>