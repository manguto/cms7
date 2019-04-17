<?php

namespace manguto\manguto\lib\cms;

class CMSPageDev extends CMSPage
{

    public function __construct($opts = array(), $tpl_dir = 'dev/')
    {   
        parent::__construct($opts,$tpl_dir);
    }
}

?>