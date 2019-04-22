<?php
use manguto\cms5\lib\Javascript;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Strings;   

class FileViewer
{

    static function MountStructure()
    {
        try {
            { // configuracoes
                $filename = 'repository/testes.csv';
                $timeout = 1000; // 60seg
                $extArray = [
                    'csv',
                    'txt'
                ];
            }
            
            if (isset($_GET['filename']) && trim($_GET['filename']) != '') {
                $filename = $_GET['filename'];
            }
            if (isset($_GET['filename']) && trim($_GET['timeout']) != '') {
                $timeout = $_GET['timeout'];
            }
            
            { // PRINT
                { // js
                    print Javascript::TimeoutDocumentLocation("?filename=$filename&timeout=$timeout", $timeout);
                }
                { // text content
                    $ext = strtolower(Arquivos::obterExtensao($filename));
                    if (! in_array($ext, $extArray)) {
                        throw new Exception("Extensão não permitida!");
                    }
                    $content = Arquivos::obterConteudo($filename);
                    $content = utf8_encode($content);
                    if ($ext == 'csv') {
                        $content = Strings::showCSV($content);
                    }
                    print "<pre>";
                    print $content;
                    print "</pre>";
                    exit();
                }
            }
        } catch (\Throwable $e) {
            $echo = '<pre><br/>';
            $echo .= '<b>' . nl2br($e->getMessage()) . '</b><br/><br/>';
            $echo .= $e->getFile() . ' (' . $e->getLine() . ')<br/><br/><br/>';
            $echo .= nl2br($e->getTraceAsString()) . '<br/><br/>';
            echo $echo;
        }
    }
}

?>