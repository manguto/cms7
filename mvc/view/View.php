<?php
namespace manguto\manguto\mvc\view;


use manguto\manguto\cms\CMSPageSite;
use manguto\manguto\cms\CMSPageAdmin;
use manguto\manguto\cms\CMSPageDev;
use manguto\manguto\cms\CMSPageOther;
use manguto\manguto\repository\Repository;
use manguto\manguto\lib\Arrays;
use manguto\manguto\cms\CMSPageCRUD;

class View
{    
    static protected function PageSite(string $templateFilename,array $parameters=[],bool $toString=false){
        $page = new CMSPageSite();        
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }    
    
    static protected function PageAdmin(string $templateFilename,array $parameters=[],bool $toString=false){
        $page = new CMSPageAdmin();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static protected function PageCRUD(string $templateFilename,array $parameters=[],bool $toString=false){
        $page = new CMSPageCRUD();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static protected function PageDev(string $templateFilename,array $parameters=[],bool $toString=false){
        $page = new CMSPageDev();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static function PageOther(string $templateFilename,array $parameters=[],bool $toString=false){
        $page = new CMSPageOther();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    

    //================================================================================================================ EXTRA / AUX
    //================================================================================================================ EXTRA / AUX
    //================================================================================================================ EXTRA / AUX

    
    static function HTML_Combo($idSelected='',$modelname,$showFiels=[],$glue=' | '){
        $return = [];
        $rep = Repository::getRepository($modelname,'',false,true,false,true);        
        //deb($rep);
        
        $return[] = "<select name='{$modelname}_id' id='{$modelname}_id' class='form-control'>";
        $return[] = "<option value=''>Selecione uma opção...</option>";
        foreach ($rep as $r){
            $r = Arrays::arrayMultiNivelParaSimples($r);
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