# Manguto - Library & Content Management System

> Library & Mini Content Management System - Little help on developing simple personal solutions. 

----
## composer.json
    {
	"require" : {
		"manguto/cms5" : "*"
	},
	"autoload" : {
		"psr-4" : {
			"sis\\" : "sis/"
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
    use manguto\cms5\lib\cms\CMSSetup;
    require_once "vendor/autoload.php";
    CMSSetup::Run('cms5');        
    ?>

## Done!


[Markdown - Help](http://markdownlivepreview.com)
