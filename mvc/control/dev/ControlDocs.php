<?php
namespace manguto\cms5\mvc\control\dev;


use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\mvc\view\dev\ViewDocs;
use manguto\cms5\mvc\control\ControlDev;

class ControlDocs extends ControlDev
{
    const folder = ROOT_SIS . 'docs';

    static function RunRouteAnalisys($app)
    {
        
            $app->get('/dev/docs', function () {
                self::PrivativeDevZone();
                ViewDocs::load('docs', self::docs_parameters());
            });
            
            
            $app->get('/dev/docs/:page', function ($page) {
                self::PrivativeDevZone();
                ViewDocs::load('docs_page', self::docs_page_parameters($page));
            });
            
            
               
    }

    // =======================================================================================================================================
    // =======================================================================================================================================
    // =======================================================================================================================================
    static private function docs_parameters()
    {
        $folder = self::folder;
        $samples = [];
        // deb($folder);
        if (file_exists($folder)) {
            $htmls = Diretorios::obterArquivosPastas($folder, false, true, false, [
                'html'
            ]);

            foreach ($htmls as $html) {
                $filename = Arquivos::obterNomeArquivo($html, false);
                $samples[$filename] = ucfirst($filename);
            }
        }
        // deb($samples);

        return get_defined_vars();
    }
    
    static private function docs_page_parameters($page)
    {
        $folder = self::folder;
        {
            $pageTitle = ucfirst($page);    
        }
        {
            //$pageContent = Arquivos::obterConteudo(ROOT_SIS.'docs/'.$page.'.html');
            $include = "../../../../../$folder/$page";
        }
        return get_defined_vars();
    }
    
    
    
    
    
    
    
    
    
}

?>