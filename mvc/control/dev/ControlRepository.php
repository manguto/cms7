<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\view\dev\ViewRepository;
use manguto\cms5\lib\Exception;
use manguto\cms5\mvc\control\ControlDev;
class ControlRepository extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/repository', function () {
            self::PrivativeDevZone();
            ViewRepository::repository();
        });

        $app->get('/dev/repository/:repository', function ($repository) {
            self::PrivativeDevZone();
            ViewRepository::repository_view($repository);
        });

        $app->get('/dev/repository/:repository/:id/view', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            {
                //$repositoryNameCall = Repository::getObjectClassname($repository);
                throw new Exception("");
                
                // deb($repositoryNameCall);
                $register = new $repositoryNameCall($id);
                $register->replaceReferences();
                $register = $register->GET_DATA($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
                // deb($register);
            }
            ViewRepository::repository_register_view($repository, $register);
        });

        $app->get('/dev/repository/:repository/:id/delete', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            {
                //$repositoryNameCall = Repository::getObjectClassname($repository);
                throw new Exception("");
                
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
                //$repositoryNameCall = Repository::getObjectClassname($repository);
                throw new Exception("");
                // deb($repositoryNameCall);
                $register = new $repositoryNameCall($id);
                // deb($register);
                $register = $register->GET_DATA($extraIncluded = false, $ctrlParametersIncluded = false, $referencesIncluded = true, $singleLevelArray = false);
                // deb($register);
            }
            ViewRepository::repository_register_edit($repository, $register);
        });

        $app->post('/dev/repository/:repository/:id/edit', function ($repository, $id) {
            self::PrivativeDevZone();
            // deb($repository,0); deb($id);
            // deb($_POST);
            {
                //$repositoryNameCall = Repository::getObjectClassname($repository);
                throw new Exception("");
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
                $register->SET_DATA($_POST);
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
            ViewRepository::repository_sheet_view($repository);
        });
    }
}

?>