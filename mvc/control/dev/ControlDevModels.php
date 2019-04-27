<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\dev\ViewDevModels;
use manguto\cms5\lib\model\Model_Helper;
use manguto\cms5\lib\Exception;

class ControlDevModels extends ControlDev
{   

    static function RunRouteAnalisys($app)
    {

        // ----------------------------------------------------------------------
        $app->get('/dev/models', function () {
            self::PrivativeDevZone();
            {
                $models = Model_Helper::get();
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

            //$modelClassNameFull = Repository::getObjectClassname($modelClassName);
            // deb($modelClassNameFull);
            throw new Exception();

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