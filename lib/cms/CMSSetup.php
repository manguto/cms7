<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\ServerHelp;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Exception;

class CMSSetup
{

    static function Run($echo = true)
    {
        try {

            $relat = [];
            $relat[] = "<hr/>";
            $relat[] = "<h1>SETUP</h1>";
            $relat[] = "<h2>Procedimento de instalação do General Managemente System (CMS) inicializado</h2>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            // config
            $originFilesPath = ServerHelp::fixds('vendor/manguto/manguto/cms/files');

            // get folders/files structure to reply
            $originFiles = Diretorios::obterArquivosPastas($originFilesPath, true, true, true);
            $relat[] = "Foram encontrados '" . sizeof($originFiles) . "' pastas/arquivos.";
            // deb($foldersFiles);
            // criacao de pastas e arquivos
            $relat[] = "<ol>";
            foreach ($originFiles as $originFile) {
                $relat[] = "<li>$originFile";
                $destinationFilePath = str_replace($originFilesPath . DIRECTORY_SEPARATOR, '', $originFile);

                if (is_dir($originFile)) {
                    if (! file_exists($destinationFilePath)) {
                        Diretorios::mkdir($destinationFilePath);
                        $relat[] = " - Diretório '$destinationFilePath' criado com sucesso!";
                    }
                } else if (is_file($originFile)) {
                    { // tratamento deviso a extensao "php_"
                        $ext = Arquivos::obterExtensao($originFile);
                        if ($ext == 'php_') {
                            $destinationFilePath = str_replace('php_', 'php', $destinationFilePath);
                        }
                    }

                    if (! file_exists($destinationFilePath)) {
                        Arquivos::copiarArquivo($originFile, '.' . DIRECTORY_SEPARATOR . $destinationFilePath);
                        $relat[] = " - <b>Arquivo '$destinationFilePath' criado com sucesso!</b>";
                    } else {
                        $relat[] = " - Arquivo '$destinationFilePath' já existente (NOP).";
                    }
                } else {
                    throw new Exception("Arquivo de tipo inadequado/desconhecido (?).");
                }
                $relat[] = "</li>";
            }

            $relat[] = "</ol>";
            $relat[] = "<h3>Procedimento de SETUP finalizado com sucesso!</h3>";
            $relat[] = "<hr/>";
            $relat[] = "<h2>CLIQUE <a href='index.php' title='Clique aqui para acessar a nova plataforma.'>AQUI</a> PARA ACESSAR A NOVA PLATAFORMA</h2>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<hr/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            $relat[] = "<br/>";
            // $relat[] = Javascript::TimeoutDocumentLocation('index.php');

            { // RENAME/REPLACE INDEX
                self::SetupReplaceIndexes();
            }

            { // relat
                $relat = implode(chr(10), $relat);
                if ($echo) {
                    echo $relat;
                } else {
                    return $relat;
                }
            }
        } catch (Exception $e) {
            echo $e->show();
        }
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