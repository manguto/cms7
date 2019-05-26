<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\ServerHelp;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;

class CMSSetup
{

    /**
     * informe o nome do projeto do repositorio manguto em questao
     * ex.: 'manguto','cms3','cms5'
     * @param string $manguto_prj_name
     */
    static function Run($manguto_prj_name = '')
    {
        
        $vendor_manguto_prj_root='vendor/manguto/'.$manguto_prj_name.'/';
        
        try {
            echo "<h1>SETUP</h1>";
            echo "<hr />";
            // config
            $originFilesPath = ServerHelp::fixds($vendor_manguto_prj_root . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR);
            // informacoes iniciais
            $originFiles = self::Initialize($originFilesPath);
            // deb($originFiles);
            echo "<hr />";
            // criacao de pastas e arquivos
            self::FileFolderAnalisys($originFilesPath, $originFiles);
            echo "<hr />";
            // finalizacao
            self::finalization();
            echo "<hr />";
            // renomear e criar copia de determinados arquivos
            self::SetupReplaceIndexes();
        } catch (Exception $e) {
            echo $e->show();
        }
    }

    private static function Initialize($originFilesPath)
    {
        
        echo "<h2>Procedimento de instalação inicializado...</h2>";

        // deb($originFilesPath);
        echo "Caminho para obtenção dos pastas/arquivos base:<br />";
        echo "<b>$originFilesPath</b><br />";
        echo "<br />";

        // get folders/files structure to reply
        $originFiles = Diretorios::obterArquivosPastas($originFilesPath, true, true, true);
        // deb($originFiles);

        echo "<b>" . sizeof($originFiles) . "</b> pastas/arquivos encontrados <br />";
        echo "<br />";
        // deb($foldersFiles);
        foreach ($originFiles as $originFile) {
            echo "- " . str_replace($originFilesPath, '', $originFile) . " <br />";
        }
        return $originFiles;
    }

    private static function FileFolderAnalisys(string $originFilesPath, array $originFiles)
    {
        echo "<h2>Procedimento de criação de arquivos/pastas inicializado...</h2>";

        {
            $ds = "<div style='width:100%; text-align:right; background:#afa;'>";
            $dn = "<div style='width:100%; text-align:left; background:#faa;'>";
        }

        $dir_n = 0;

        echo "<div style='padding-bottom:5px;'>";

        foreach ($originFiles as $originFile) {

            {
                // deb($originFilesPath,0);
                $destinationFilePath = str_replace($originFilesPath, '', $originFile);
                // deb($destinationFilePath);

                if (is_dir($originFile)) {

                    echo "</div>";

                    echo "<div style='padding-bottom:5px;'>";

                    $dir_n ++;

                    if (! file_exists($destinationFilePath)) {
                        Diretorios::mkdir($destinationFilePath);
                        echo $ds . "Diretório '$destinationFilePath' criado com sucesso! &#8592; </div>";
                    } else {
                        echo $dn . " &#8594; Diretório '$destinationFilePath' já existente. Nenhum procedimento realizado.</div>";
                    }
                } else if (is_file($originFile)) {

                    { // tratamento deviso a extensao "php_"
                        if (Arquivos::obterExtensao($originFile) == 'php_') {
                            $destinationFilePath = str_replace('php_', 'php', $destinationFilePath);
                        }
                    }

                    if (! file_exists($destinationFilePath)) {
                        Arquivos::copiarArquivo($originFile, '.' . DIRECTORY_SEPARATOR . $destinationFilePath);
                        echo $ds . "  Arquivo '$destinationFilePath' criado com sucesso! &#8592; </div>";
                    } else {
                        echo $dn . " &#8594; Arquivo '$destinationFilePath' já existente. Nenhum procedimento realizado.</div>";
                    }
                } else {
                    throw new Exception("Arquivo de tipo inadequado/desconhecido (?).");
                }
            }
        }
    }

    private static function finalization()
    {
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<h2>SETUP finalizado com sucesso!</h2>";
        echo "Acesse a nova plataforma clicando <a href='index.php'>AQUI</a>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        // echo Javascript::TimeoutDocumentLocation('index.php');
    }

    private static function SetupReplaceIndexes()
    {
        $index_old_filename = 'index.php';
        $index_old_bkp_filename = 'index_old.php';
        $index_cms_filename = 'index_cms.php';
        $index_new_filename = 'index.php';

        { // backup arquivo index atual
            if (file_exists($index_old_filename)) {
                $index_old_content = file_get_contents($index_old_filename);
                if ($index_old_content === false) {
                    throw new \Exception("Não foi possível obter o conteúdo do arquivo de indexação antigo (index). Contate o administrador!");
                } else {
                    if (! file_put_contents($index_old_bkp_filename, $index_old_content)) {
                        throw new \Exception("Não foi possível copiar o conteúdo do arquivo de indexação antigo (index -> index_old). Contate o administrador!");
                    }
                }
            }
        }

        { // atualizacao do arquivo de indexacao para acesso ao cms instalado
            if (! file_exists($index_cms_filename)) {
                throw new \Exception("Arquivo de indexação do Content Management System (CMS) não encontrado (index_cms). Contate o administrador!");
            } else {
                $index_cms_content = file_get_contents($index_cms_filename);
                if ($index_cms_content === false) {
                    throw new \Exception("Não foi possível obter o conteúdo do arquivo de indexação do cms (index_cms). Contate o administrador!");
                } else {
                    if (! file_put_contents($index_new_filename, $index_cms_content)) {
                        throw new \Exception("Não foi possível atualizar o conteúdo do arquivo de indexação atual (index_cms -> index). Contate o administrador!");
                    }
                }
            }
        }
    }

    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
    // ##################################################################################################################################################################
}

?>