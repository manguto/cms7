<?php
namespace manguto\cms5\mvc\view;

use manguto\cms5\lib\cms\CMSPageSite;
use manguto\cms5\lib\cms\CMSPageAdmin;
use manguto\cms5\lib\cms\CMSPageCRUD;
use manguto\cms5\lib\cms\CMSPageDev;
use manguto\cms5\lib\cms\CMSPageOther;
use manguto\cms5\lib\Logs;

class View
{    
    
    static protected function PageSite(string $templateFilename,array $parameters=[],bool $toString=false){
        Logs::set(Logs::TYPE_INFO,"Visualização do template: <b>site/$templateFilename</b>");
        $page = new CMSPageSite();        
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }    
    
    static protected function PageAdmin(string $templateFilename,array $parameters=[],bool $toString=false){
        Logs::set(Logs::TYPE_INFO,"Visualização do template: <b>admin/$templateFilename</b>");
        $page = new CMSPageAdmin();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static protected function PageCRUD(string $templateFilename,array $parameters=[],bool $toString=false){
        Logs::set(Logs::TYPE_INFO,"Visualização do template: <b>crud/$templateFilename</b>");
        $page = new CMSPageCRUD();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static protected function PageDev(string $templateFilename,array $parameters=[],bool $toString=false){
        Logs::set(Logs::TYPE_INFO,"Visualização do template: <b>dev/$templateFilename</b>");
        $page = new CMSPageDev();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    static function PageOther(string $templateFilename,array $parameters=[],bool $toString=false){
        Logs::set(Logs::TYPE_INFO,"Visualização do template: <b>other/$templateFilename</b>");
        $page = new CMSPageOther();
        $return = $page->setTpl($templateFilename,$parameters,$toString,$page);
        return $return;
    }
    
    
}