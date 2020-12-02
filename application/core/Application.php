<?php
namespace manguto\cms7\application\core;

use manguto\cms7\libraries\Logger;

class Application
{
    
    private $logger;
    
    private $configuration;

    private $controller;
    
    public function __construct()
    {   
        Logger::info(str_repeat('=', Logger::lineLen-10).' START!');
        Logger::info('Aplicação inicializacao');
        // ######################################################     
        {   
            $this->configuration = new Configuration();
            $this->controller = new Controller();
        }
        // ######################################################
        Logger::info(str_repeat('-', Logger::lineLen-10).' ');
    }

    public function Run()
    {
        Logger::info('Aplicação execucao');
        // ######################################################
        {
            $this->controller->Run();
        }
        // ######################################################
        Logger::info(str_repeat('=', Logger::lineLen-10) . '   END!');
        
    }
}

?>