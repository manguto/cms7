# Manguto - Content Management System
> Compact Content Management System - Help on developing simple personal solutions & stuff. 

----
## composer.json
    {
	"require" : {
		"manguto/cms7" : "*"
	},
	"autoload" : {
		"psr-4" : {
			"mvc\\" : "mvc/"
		}
	},
	"minimum-stability" : "dev"
	}

## Git Bash
composer install

## setup.php
    <?php    
    session_start();
    session_unset();    
    use manguto\cms7\lib\cms\CMSSetup;
    require_once "vendor/autoload.php";
    CMSSetup::Run('cms7');        
    ?>

## Done!


[Markdown - Help](http://markdownlivepreview.com)
