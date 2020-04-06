<?php
namespace manguto\cms7\lib\html;

use manguto\cms7\lib\Arrays;

class HTMLForm extends HTML
{

    static function getSelect(string $id, string $name,array $options,$actualValue='',bool $required=false,string $class='',string $style='',string $extraTags=''):string
    {
        $required = $required ? " required='required' " : '';
        
        $html = "";
        $html .= "<select class='form-control' id='$id' name='$name' style='$style' class='$class' $required $extraTags>";
        foreach ($options as $key=>$value){
            if($key==$actualValue){
                $selected = 'selected';
            }else{
                $selected = '';
            }
            $html .= "<option value='$key' $selected>$value</option>";
        }        
        $html .= "</select>";

        return $html;
    }
    
    
    
    
    static function HTML_Combo($idSelected='',$tablename,$showFiels=[],$glue=' | '){
        $return = [];
        //$rep = Repository::getRepository($tablename,'',false,true,false,true);
        //throw new Exception("...");
        //deb($rep);
        
        $return[] = "<select name='{$tablename}_id' id='{$tablename}_id' class='form-control'>";
        $return[] = "<option value=''>Selecione uma opção...</option>";
        foreach ($rep as $r){
            $r = Arrays::multiToSingleDimension($r);
            {
                $selected = $r['id']==$idSelected ? 'selected' : '';
            }
            {
                $value = [];
                if(sizeof($showFiels)>0){
                    foreach ($showFiels as $showField){
                        $value[] = $r[$showField];
                    }
                }else{
                    foreach ($r as $field=>$fieldValue){
                        if(substr($field, -3)=='_id'){
                            continue;
                        }
                        $value[] = $fieldValue;
                    }
                }
                //deb($value);
                $value = implode($glue, $value);
            }
            $return[] = "<option value='".$r['id']."' $selected>$value</option>";
        }
        $return[] = "</select>";
        $return = implode('', $return);
        return $return;
    }
}

?>