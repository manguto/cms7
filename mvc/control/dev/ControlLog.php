<?php
namespace manguto\cms5\mvc\control\dev;


use manguto\cms5\lib\Logs;
use manguto\cms5\lib\Datas;
use manguto\cms5\lib\Calendario;
use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\Numbers;
use manguto\cms5\lib\CSV;
use manguto\cms5\mvc\view\dev\ViewLog;
use manguto\cms5\lib\ServerHelp;
use manguto\cms5\mvc\control\ControlDev;

class ControlLog extends ControlDev
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/log', function () {
            self::PrivativeDevZone();            
            headerLocation('/dev/log/dia/' . date(Logs::formato_data_arquivo_diario));
            exit();
        });
        
        $app->get('/dev/log/dia/:day', function ($day) {
            self::PrivativeDevZone();           
            {
                //deb($day);
                $date = new Datas($day,Logs::formato_data_arquivo_diario);
                $datashow = $date->getDate('d/m/Y');
                $ano = $date->getDate('Y');
            }
            
            //deb($day);
            $dayLogs = Logs::getDayLogs($day);
            //deb($dayLogs);
            
            ViewLog::load('log_dia', get_defined_vars());
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
                //debc($calendario);                
                //debc(Calendario::defaultCSS);
            }
            {
                $logs = Logs::getYearLogs($ano);
                //deb($logs);
                $js = [];
                foreach ($logs as $log){                    
                    {
                        //deb($log);
                        extract($log);
                        $MONTH = intval($MONTH);    
                        $DAY = intval($DAY);
                    }
                    {
                        $MES2D = Numbers::str_pad_left($MONTH);
                        $DIA2D = Numbers::str_pad_left($DAY);
                        $ymd = $YEAR.$MES2D.$DIA2D;
                        $href = '/dev/log/dia/'.$ymd;                        
                        $link = '<a href="'.$href.'" title="Clique para visualizar os registros desta data.">LOG<a>';                       
                    }
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    $js[$MONTH.$DAY] = "$('.mes-$MONTH .dia-$DAY .conteudo').html('$link'); ";
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    
                }
                $js = implode(chr(10),$js);
                //deb($js);
            }
            //debc($js);
            ViewLog::load('log_ano', get_defined_vars());
        });
        
        
    }
    
    
    //===================================================================================
    //===================================================================================
    //===================================================================================
    
    
}

?>