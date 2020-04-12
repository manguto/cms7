<?php



namespace manguto\cms7\libraries;




class DatasMarcadores{
    
    //************************************************************ SET & CHECK
    static private function feriadosFixos($mes,$dia){
        $feriado = array();
        //----------------------------------------------------- 
        $feriado[ 1][ 1] = 'Confraternização Universal'; //Lei nº 662, de 06/04/49
        $feriado[ 4][21] = 'Tiradentes'; //Lei nº 662, de 06/04/49
        $feriado[ 5][ 1] = 'Dia do Trabalhador'; //Lei nº 662, de 06/04/49
        $feriado[ 6][24] = 'São João';
        $feriado[ 9][ 7] = 'Dia da Independência'; //Lei nº 662, de 06/04/49
        $feriado[10][12] = 'N. Sª Aparecida'; //Lei nº 6802, de 30/06/80
        $feriado[10][28] = 'Funcionário Público'; //Lei nº ____, de __/__/__
        $feriado[11][ 2] = 'Finados'; //Lei nº 662, de 06/04/49
        $feriado[11][15] = 'Proc. da Repúclica'; //Lei nº 662, de 06/04/49
        $feriado[12][25] = 'Natal'; //Lei nº 662, de 06/04/49        
        $feriado[12][31] = 'Final de Ano'; //
        //---------------------------------------------------- 
        if(isset($feriado[$mes][$dia])){
            $return = $feriado[$mes][$dia];
        }else{
            $return = false;
        }
        return $return;
    }
    
    //************************************************************ CALC & CHECK
    static private function feriadosMoveis($ano,$mes,$dia){
        $feriado = array();
        $ano = intval($ano);
        $mes = intval($mes);
        $dia = intval($dia);
        //-----------------------------------------------------
        
        // Limite de 1970 ou após 2037 da easter_date PHP, consulte: php.net/manual/pt_BR/function.easter-date.php
        if ($ano<=1970 || $ano>=2037 ){
            throw new Exception("O Ano precisa ser entre 1970 e 2037 ($ano).");
        }
        
        // Calculo da data base da PASCOA
        $pascoa     = easter_date($ano);
        
        //correcao servidor web
        //debug($ano);
        //debug(date('d/m/Y',easter_date(2018)));
        if(date('d/m/Y',easter_date(2018))!='01/04/2018'){
            $pascoa += 24*60*60;
        }
        
        $dia_pascoa = date('j', $pascoa);
        $mes_pascoa = date('n', $pascoa);
        $ano_pascoa = date('Y', $pascoa);
        
        $feriadoTimestamp = array();        
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa)] = "2ª Feria Carnaval";
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa)] = "3ª Feria Carnaval";
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 46, $ano_pascoa)] = "Cinzas";        
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa)] = "Paixão de Cristo";
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa)] = "Páscoa";
        $feriadoTimestamp[mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa)] = "Corpus Christ";
        
        foreach ($feriadoTimestamp as $timestamp=>$desc){            
            $feriado[date('Y',$timestamp)][date('n',$timestamp)][date('j',$timestamp)] = $desc;
        }
        //debug($feriado);
        //-------------------------------------------------------------------------------------------------------- 
        if(isset($feriado[$ano][$mes][$dia])){
            $return = $feriado[$ano][$mes][$dia];
        }else{
            $return = false;
        }
        return $return;
    }
   
    
    //************************************************************ CALCULOS
    static function ehFeriado($ano,$mes,$dia){
        $return = array();
        //feriados fixos ----------------------------------------------
        $feriadoFixo = self::feriadosFixos($mes,$dia);
        if($feriadoFixo!==false){
            $return[]=$feriadoFixo;
        }
        //feriados moveis ---------------------------------------------
        $feriadoMovel = self::feriadosMoveis($ano,$mes,$dia);
        if($feriadoMovel!==false){
            $return[]=$feriadoMovel;
        }
        //-------------------------------------------------------------
        $return = implode(', ', $return);
        $return = trim($return)=='' ? false : $return;
        return $return;
    }
    
    //************************************************************ START
    /**
     * Obtem (caso exista) o nome do marcador (conforme informado: Feriado, etc.) pra data informada.
     * @param $ano
     * @param $mes
     * @param $dia
     * @param string $tipo
     * @return boolean|string
     */
    static function obterEventualMarcador($ano,$mes,$dia,string $tipo='feriado'){
        $ano = intval($ano);
        $mes = intval($mes);
        $dia = intval($dia);
        if($tipo=='feriado'){
            $return = self::ehFeriado($ano,$mes,$dia);
        }else{
            $return = false;
        }
        return $return;        
    }
    
}

