<?php
namespace manguto\cms5\mvc\control\dev;


use manguto\cms5\lib\Logs;
use manguto\cms5\lib\Datas;
use manguto\cms5\lib\Calendario;
use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Numbers;
use manguto\cms5\lib\CSV;
use manguto\cms5\mvc\view\dev\ViewDevLog;

class ControlDevLog extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/log', function () {
            self::PrivativeDevZone();
            $data = date(Logs::formato_data_arquivo);
            headerLocation('/dev/log/dia/' . $data);
            exit();
        });
        
        $app->get('/dev/log/dia/:data', function ($data) {
            self::PrivativeDevZone();
            {//par
                $data = new Datas($data,Logs::formato_data_arquivo);
                $datashow = $data->getDate('d/m/Y');
                $ano = $data->getDate('Y');                
            }
            {
                $datahora = $data->getDate(Logs::formato_datahora);
                $filename = Logs::get_filename($datahora);
                $csvContent = utf8_encode(Arquivos::obterConteudo($filename));
                $logs = CSV::CSVToArray($csvContent);
                //deb($logs);
            }
            ViewDevLog::load('log_dia', get_defined_vars());
        });
        
        $app->get('/dev/log/ano/:ano', function ($ano) {
            self::PrivativeDevZone();
            {
                $ano_ant = intval($ano)-1;
                $ano_post = intval($ano)+1;
            }
            {
                $calendario = new Calendario($ano);                
                $calendario = $calendario->HTML_ObterAno();
                $calendario = utf8_encode($calendario);
                //debc($calendario);                
                //debc(Calendario::defaultCSS);
            }
            {
                $logs = self::obterDiasComRegistros($ano);
                $js = [];
                foreach ($logs as $mes=>$dia_filepath){
                    foreach ($dia_filepath as $dia=>$filepath){
                        $mes2d = Numbers::str_pad_left($mes);
                        $dia2d = Numbers::str_pad_left($dia);
                        $link = '<a href="/dev/log/dia/'.$ano.$mes2d.$dia2d.'" title="Clique para visualizar os registros desta data.">LOG<a>';
                        $js[] = "$('.mes-$mes .dia-$dia .conteudo').html('$link'); ";
                    }
                }
                $js = implode(chr(10),$js);
                //debc($js);
            }
            
            ViewDevLog::load('log_ano', get_defined_vars());
        });
    }
    
    
    //===================================================================================
    //===================================================================================
    //===================================================================================
    
    static private function obterDiasComRegistros($ano){
        $path = Logs::dir;
        $logs = Diretorios::obterArquivosPastas($path, false, true, false, ['csv']);
        $return = [];
        foreach ($logs as $log){
            $nomearquivo = Arquivos::obterNomeArquivo($log,false);
            //deb($nomearquivo);
            if(substr($nomearquivo, 0,4)==$ano){
                $data = new Datas($nomearquivo,Logs::formato_data_arquivo);
                $mes = intval($data->getDate('m'));
                $dia = intval($data->getDate('d'));
                $return[$mes][$dia] = $log;
            }
        }
        //deb($return);
        return $return;
    }
    
}

?>