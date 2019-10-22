<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewZzz;
use manguto\cms5\mvc\control\ControlDev;
use manguto\cms5\mvc\model\Manutencao;
use manguto\cms5\lib\ProcessResult;

class ControlManutencao extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/manutencao', function () {
            self::PrivativeDevZone();
            {
                $manutencao = Manutencao::EmFuncionamento();
            }            
            ViewZzz::load('manutencao',get_defined_vars());
        });
        
        $app->post('/dev/manutencao', function () {
            self::PrivativeDevZone();
            {
                if(isset($_POST['motivo'])){
                    //ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO!
                    //ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO!
                    //ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO! - ATIVACAO!
                    $manutencao = new Manutencao();
                    $manutencao->setMotivo($_POST['motivo']);
                    $manutencao->save();
                    ProcessResult::setSuccess("Manutenção ativada com sucesso!");
                    headerLocation('/dev/manutencao');                    
                }else{
                    //DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO!
                    //DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO!
                    //DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO! - DESATIVACAO!
                    $manutencao = new Manutencao($_POST['id']);
                    $manutencao->setStatus('inativa');
                    $manutencao->save();
                    ProcessResult::setSuccess("Manutenção desativada com sucesso!");
                    headerLocation('/dev/manutencao');      
                }
                $manutencao = Manutencao::EmFuncionamento();
            }            
            ViewZzz::load('manutencao',get_defined_vars());
        });
        
    }
}

?>