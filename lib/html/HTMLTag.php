<?php
namespace manguto\cms7\lib\html;

class HTMLTag extends HTML
{

    //-------------------------------------------------------------------------- form
    static function form_open($id='',$method='',$action="")
    {
        {
            $id = $id=='' ? '' : "id='$id'";
            $method = $method=='' ? '' : "method='$method'";
            $action = $action=='' ? '' : "action='$action'";
        }
        return "<form $id $method $action>";
    }
    static function form_close()
    {
        return "</form>";
    }
    //-------------------------------------------------------------------------- input
    static function input($id='',$label,$value='',$type='text',$placeholder='',$help='')
    {
        {   
            //label
            $label = $label=='' ? '' : "<label for='$id'>$label:</label> ";
            
            //help
            $help = $help=='' ? '' : "<small class='form-text text-muted'>$help</small>";
            
            //...
            $name = $id=='' ? '' : "name='$id'";
            $id = $id=='' ? '' : "id='$id'";            
            $type = $type=='' ? '' : "type='$type'";            
            $value = $value=='' ? '' : "value='$value'";
            $placeholder = $placeholder=='' ? '' : "placeholder='$placeholder'";
            
            //input
            $input = "<input $type $id $name $value $placeholder class='form-control'/>";
        }
        $return = "<div class='form-group'>$label $input $help </div>";
 
        return $return;
    }
    //-------------------------------------------------------------------------- button
    static function button($text,$type='submit',$id='',$class='')
    {
        {   
            {   
                $type = $type=='' ? '' : "type='$type'";
                $id = $id=='' ? '' : "id='$id'";                
                $class = $class=='' ? "class='btn btn-primary'" : "class='$class'";
            }            
            //button
            $return = "<button $id $type $class>$text</button>";
        }
 
        return $return;
    }
    
    
    //------------------------------------------------------------------------- select
    /*
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
    }*/
}

?>