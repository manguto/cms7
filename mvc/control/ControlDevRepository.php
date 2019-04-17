<?php
namespace manguto\cms5\mvc\control;

use manguto\cms5\mvc\view\ViewDevRepository;
use manguto\cms5\lib\ProcessResult;

class ControlDevRepository extends ControlDev
{

    static function Executar($app)
    {
        $app->get('/dev/repository', function () {
            self::PrivativeDevZone();
            ViewDevRepository::repository();
        });

        $app->get('/dev/repository/:repository', function ($repository) {
            self::PrivativeDevZone();
            ViewDevRepository::repository_view($repository);
        });

        $app->get('/dev/repository/:repository/:id/view', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            {
                $repositoryNameCall = Repository::getObjectClassname($repository);
                // deb($repositoryNameCall);
                $register = new $repositoryNameCall($id);
                $register->replaceReferences();
                $register = $register->getData($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
                // deb($register);
            }
            ViewDevRepository::repository_register_view($repository, $register);
        });

        $app->get('/dev/repository/:repository/:id/delete', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            {
                $repositoryNameCall = Repository::getObjectClassname($repository);
                // deb($repositoryNameCall);
                $register = new $repositoryNameCall($id);
                // deb($register);
                $register->delete();
                ProcessResult::setSuccess("Registro removido com sucesso.");
                headerLocation("/dev/repository/$repository");
                exit();
            }
        });

        $app->get('/dev/repository/:repository/:id/edit', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            {
                $repositoryNameCall = Repository::getObjectClassname($repository);
                // deb($repositoryNameCall);
                $register = new $repositoryNameCall($id);
                // deb($register);
                $register = $register->getData($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
                // deb($register);
            }
            ViewDevRepository::repository_register_edit($repository, $register);
        });

        $app->post('/dev/repository/:repository/:id/edit', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            // deb($_POST);
            {
                $repositoryNameCall = Repository::getObjectClassname($repository);
                // deb($repositoryNameCall);
                { // success msg
                    if ($id == 0) {
                        $msg = 'cadastrado';
                    } else {
                        $msg = 'salvo';
                    }
                    $msg = "Registro $msg com sucesso!";
                }
                $register = new $repositoryNameCall($id);
                $register->setData($_POST);
                $register->save();
                // deb($register);
                $id = $register->getId();
                ProcessResult::setSuccess($msg);
                // headerLocation("/dev/repository/$repository/$id/view");
                headerLocation("/dev/repository/$repository");
                exit();
            }
        });

        // ..................................................................
        $app->get('/dev/repository/sheet/:repository', function ($repository) {
            self::PrivativeDevZone();
            ViewDevRepository::repository_sheet_view($repository);
        });
    }
}

?>