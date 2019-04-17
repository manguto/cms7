<?php
namespace manguto\manguto\lib\html;

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
}

?>