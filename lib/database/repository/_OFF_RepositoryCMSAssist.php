<?php
namespace manguto\cms5\lib\database\repository;

use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\CSV;
use manguto\cms5\lib\Exception;

class _OFF_RepositoryCMSAssist
{    
    //pasta onde ficarao os arquivos iniciais de cada objeto
    private const ini_foldername = 'repository/ini';
    
    static function generateLibFiles(array $array)
    {
        $msg = [];
        foreach ($array as $tablename => $info) {
            // deb($tablename,0);
            // deb($info);
            // $Tablename = ucfirst($tablename);

            $modeloEncontrado = self::modelExists($tablename);

            $repositorioEncontrado = self::repositoryExists($tablename);
            if ($repositorioEncontrado !== false) {
                $csvContent = Arquivos::obterConteudo($repositorioEncontrado);
            } else {
                $csvContent = '';
            }

            // verifica se o repositorio está vazio
            $repositorioVazio = sizeof(CSV::CSVToArray($csvContent)) < 2;
            // deb($repositorioVazio, 0);

            foreach ($info as $id => $content) {
                // deb($id, 0); deb($content);

                { // CRIACAO DA CLASSE - verifica se o arquivo modelo jah existe e cria-o caso contrario
                    if ($modeloEncontrado == false) {
                        // default
                        $msg[] = self::createModelFile($tablename, $content);
                        $modeloEncontrado = true;
                    }
                }

                { // CRIACAO DOS REGISTROS INICIAIS INFORMADOS - insere registros caso o atual do loop seja um registro real e caso o repositorio tenha sido encontrado inicialmente vazio
                    if ((intval($id) != 0) && $repositorioVazio) {
                        // new
                        $msg[] = self::createObject($tablename, $content);
                    }
                }
            }
        }
        return $msg;
    }

    // --------------------------------------------------------------------------- private functions
    static private function modelExists($tablename)
    {
        $filename = 'prj/model/' . ucfirst($tablename) . '.php';
        if (file_exists($filename)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * verifica se o arquivo do repositorio existe e retorna o path
     * (caso contrario retorna FALSE)
     *
     * @param string $tablename
     * @return string|boolean
     */
    static private function repositoryExists(string $tablename)
    {
        $filename = 'repository/' . $tablename . '.csv';
        if (file_exists($filename)) {
            return $filename;
        } else {
            return false;
        }
    }

    static private function createModelFile($tablename, $default = [])
    {
        $Tablename = ucfirst($tablename);
        $modelFilename = 'prj/model/Zzz.php';
        if (! file_exists($modelFilename)) {
            throw new \Exception("Modelo de arquivo não encontrado ($modelFilename).");
        } else {
            $modelContent = file_get_contents($modelFilename);
            { // replaces
                $modelContent = str_replace('zzz', $tablename, $modelContent);
                $modelContent = str_replace('Zzz', $Tablename, $modelContent);
                { // default_values
                    $default_values = [];
                    $default_values[] = '';
                    ;
                    foreach ($default as $key => $value) {
                        $key = strtolower($key);
                        $default_values[] = chr(9) . chr(9) . chr(9) . "\$this->values['$key'] = '$value';";
                    }
                    $default_values = implode(chr(10), $default_values);
                }
                // deb($default_values);
                $modelContent = str_replace('// parameters-and-default', $default_values, $modelContent);
                $modelContent = utf8_decode($modelContent);
            }
            // deb($modelContent);
            $modelFilenameNew = "prj/model/$Tablename.php";
            Arquivos::escreverConteudo($modelFilenameNew, $modelContent);
            return "Um modelo para '$Tablename' foi criado com sucesso!";
        }
    }

    static private function createObject($tablename, $content = [])
    {
        // deb($tablename,0); deb($content);
        $Tablename = ucfirst($tablename);

        $classname = '\prj\model\\' . $Tablename;

        $obj = new $classname();
        // deb($a);
        // deb($content);
        foreach ($content as $key => $value) {
            $Key = ucfirst($key);
            $method = "set$Key";
            $obj->$method($value);
        }
        // deb($obj);
        $obj->save();
        // deb($obj,0);

        $id = $obj->getId();
        return "'$Tablename' criado(a) com com sucesso! [id=$id]";
    }

    /**
     * caso o repositorio não exista ou se não possuir nenhum registro, inicializa-o com um item vazio com os valores padroes especificados
     *
     * @param string $csv_default
     */
    static function inicializar()
    {
        throw new Exception("Método aguardando redefinição/aprimoramento.");
        /*try {

            { // obter o nome do repositorio
                $called_class = '\\' . get_called_class();
                $n = new $called_class();
                $repositoryname = strtolower($n->getModelname());
                // deb($repositoryname);
            }

            { // verifica se o arquivo do repositorio existe
                if (! Repository::repositoryFileExist($repositoryname)) {
                    ProcessResult::setSuccess("Repositório '<b>$repositoryname</b>' não encontrado.");
                }
            }

            { // verifica a quantidade de registros
                $quantRegistros = Repository::getRepositoryLength($repositoryname);
            }

            if ($quantRegistros == 0) {

                { // nomes dos arquivos envolvidos necessarios
                    $repositoryFilename = Repository::getRepositoryFilename($repositoryname);
                    $repositoryIniFilename = self::ini_foldername . '/' . $repositoryname . '.csv';
                }

                if (file_exists($repositoryIniFilename)) {
                    Arquivos::copiarArquivo($repositoryIniFilename, $repositoryFilename);
                    ProcessResult::setSuccess("Repositório '<b>$repositoryname</b>' preenchido (via '$repositoryIniFilename') com sucesso.");
                } else {
                    ProcessResult::setWarning("A inicialização do repositório '<b>$repositoryname</b>' não pode ser realizada, pois não foi encontrado nenhum arquivo base para inicialização.");
                }
            } else {
                ProcessResult::setWarning("A inicialização do repositório '<b>$repositoryname</b>' não foi realizada por este já possui conteúdo (<b>$quantRegistros registro(s)</b>).");
            }
        } catch (Exception $e) {
            ProcessResult::setError($e);
        }*/
    }
}