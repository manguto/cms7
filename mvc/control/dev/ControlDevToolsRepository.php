<?php
namespace manguto\cms5\mvc\control\dev;

use manguto\cms5\mvc\view\dev\ViewDev;
use manguto\cms5\lib\Diretorios;
use manguto\cms5\lib\Arquivos;
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\lib\CSV;
use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\model\Model_Helper;

class ControlDevToolsRepository extends ControlDevTools
{

    static function RunRouteAnalisys($app)
    {
        $app->get('/dev/tools/repository', function () {
            self::PrivativeDevZone();
            {
                $repository_filename_array = Diretorios::obterArquivosPastas('repository', false, true, false, 'csv');
                // deb($r);
            }
            {
                $repository_array = [];
                foreach ($repository_filename_array as $repository_filename) {
                    {
                        $repository_content = utf8_encode(Arquivos::obterConteudo($repository_filename));
                    }
                    {
                        $tablename = Arquivos::obterNomeArquivo($repository_filename, false);
                    }
                    {//especificacao das colunas                        
                        $cols = explode(chr(10), trim($repository_content));
                        $cols = array_shift($cols);
                        $cols = explode(';', trim($cols));
                        foreach ($cols as $key=>$col) {
                            if (in_array($col, Model::fundamentalAttributes) && $col != 'id') {
                                unset($cols[trim($key)]);
                            }
                        }
                        //deb($cols);
                    }
                    {
                        $rows = CSV::CSVToArray($repository_content);                                            
                        // deb($rows,0);
                        {//remocao das colunas fundamentais desnecessarias
                            foreach ($rows as $line=>$info){
                                //deb($col,0); deb($info);
                                foreach ($info as $col=>$val){
                                    if (in_array($col, Model::fundamentalAttributes) && $col != 'id') {
                                        unset($rows[$line][trim($col)]);
                                    }
                                }
                            }
                        }
                        {//adicao do registro vazio para adicao
                            $n = sizeof($rows);
                            foreach ($cols as $col){                                
                                $rows[$n][trim($col)]='';
                            }                            
                        }
                        //deb($rows,0);
                    }
                    
                    {
                        $tableContent = $rows;
                        foreach ($tableContent as $key=>$row){
                            if(trim(implode('',$row))==''){
                                unset($tableContent[$key]);
                            }
                        }
                        $db[$tablename.'_id']=$tableContent;
                    }
                    if ($tablename == 'zzz' || $tablename == 'user') {
                        continue;
                    }
                    
                    $repository_array[$tablename] = [
                        'cols' => $cols,
                        'rows' => $rows
                    ];
                }
                //deb($repository_array);
            }
            //deb($db);
            // deb($textarea_array);
            ViewDev::load('tools_repository', get_defined_vars());
        });
        
        $app->post('/dev/tools/repository/save', function () {
            self::PrivativeDevZone();
            {
                //deb($_POST,0);
                {
                    $tablename = $_POST['tablename'];
                }
                {
                    $classname = Model_Helper::getObjectClassname($tablename);
                }
                //deb($_POST);
                $registrosSalvos = 0;
                foreach ($_POST['registros'] as $register) {
                    
                    if(trim(implode('', $register))==''){
                        continue;
                    }
                    $obj = new $classname(intval($register['id']));
                    $obj->SetData($register,false);
                    //deb($obj,0);
                    $obj->save();
                    //deb($obj,0);
                    $registrosSalvos++;
                }
            }
            if($registrosSalvos>0){
                ProcessResult::setSuccess("$registrosSalvos registro(s) salvo(s) com sucesso!");
            }else{
                ProcessResult::setWarning("Nenhum registro salvo ou afetado.");
            }            
            headerLocation("/dev/tools/repository#$tablename");
        });
    }
}

?>