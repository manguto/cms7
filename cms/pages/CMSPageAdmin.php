<?php

namespace manguto\cms7\lib\cms;

class CMSPageAdmin extends CMSPage
{

    public function __construct($opts = array(), $tpl_dir = 'admin/')
    {   
        parent::__construct($opts,$tpl_dir);
    }
}

?>