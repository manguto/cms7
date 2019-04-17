<?php
namespace manguto\manguto\mvc\control;

use manguto\manguto\mvc\view\ViewDevModels;
use manguto\manguto\mvc\model\Models;
use manguto\manguto\lib\Diretorios;
use manguto\manguto\lib\Arquivos;
use manguto\manguto\repository\Repository;
use manguto\manguto\lib\ProcessResult;

class ControlDevModels extends ControlDev
{

    static function Executar($app)
    {

        // ----------------------------------------------------------------------
        $app->get('/dev/models', function () {
            self::PrivativeDevZone();
            {
                $models = Models::get();
            }
            ViewDevModels::load('models', get_defined_vars());
        });
        // ----------------------------------------------------------------------
        $app->get('/dev/models/initialize', function () {
            self::PrivativeDevZone();
            {
                self::inicializar();
            }
            headerLocation('/dev/models');
            exit();
        });
    }

    // ##################################################################
    // ##################################################################
    // ##################################################################
    static function inicializar()
    {
        $dir = ROOT_SIS . 'model';
        // deb($dir);
        $models = Diretorios::obterArquivosPastas($dir, false, true, false, [
            'php'
        ]);
        // deb($models);
        foreach ($models as $model) {

            $modelClassName = Arquivos::obterNomeArquivo($model, false);
            // deb($modelClassName);

            //avoid default module sample, and classes started with "_"
            if ($modelClassName == 'Zzz' || substr($modelClassName, 0,1)=='_'){
                continue;
            }                

            $modelClassNameFull = Repository::getObjectClassname($modelClassName);
            // deb($modelClassNameFull);

            $classMethods = get_class_methods($modelClassNameFull);
            // deb($classMethods);

            if (in_array('inicializar', $classMethods)) {
                $modelClassNameFull::inicializar();
            } else {
                ProcessResult::setWarning("Classe sem método de inicialização ($modelClassName).");
            }
        }
    }
}

?>