<?php
namespace manguto\cms5\mvc\view;


use manguto\cms5\cms\CMSPageSite;
use manguto\cms5\cms\CMSPageAdmin;
use manguto\cms5\cms\CMSPageDev;
use manguto\cms5\cms\CMSPageOther;
use manguto\cms5\repository\Repository;
use manguto\cms5\lib\Arrays;
use manguto\cms5\cms\CMSPageCRUD;

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

    
    static function HTML_Combo($idSelected='',$tablename,$showFiels=[],$glue=' | '){
        $return = [];
        $rep = Repository::getRepository($tablename,'',false,true,false,true);        
        //deb($rep);
        
        $return[] = "<select name='{$tablename}_id' id='{$tablename}_id' class='form-control'>";
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