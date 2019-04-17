<?php

namespace manguto\cms5\lib;




class CSV {
    

	const separadorRegistrosTEMP = '(_)';
	
	const separadorParametros = ';';
	const separadorParametrosTEMP = '(;)';

	static private function separadorRegistros(){
	    return chr(10);
	}
	
	static function CSVToArray($csvContent) {
		$csvContentLines = explode ( CSV::separadorRegistros(), $csvContent );
		
	    //deb($csvContentLines);
		// nomes das colunas (1a linha)
		$colNames = array_shift ( $csvContentLines );
		$colNames = explode ( CSV::separadorParametros, $colNames );
	
		//deb($colNames);
		// valores das linhas
		$return = array ();
		foreach ( $csvContentLines as $lineNumber => $line ) {
			$line = trim ( $line );
			if ($line == '')
				continue;
				
			$atributos = explode ( CSV::separadorParametros, $line );
			//deb($atributos);
			foreach ( $atributos as $k => $v ) {
				
				$v = trim($v);
				
				// exception
				$v = str_replace ( CSV::separadorRegistrosTEMP, CSV::separadorRegistros(), $v );
				$v = str_replace ( CSV::separadorParametrosTEMP, CSV::separadorParametros, $v );
								
				$colname = trim ( $colNames [$k] ); 
				$return [$lineNumber] [$colname] = $v; 
			}
		}
		return $return;
	}
	
	static function ArrayToCSV($arrayContent,$header=true) {
		$colNames = array ();
		$csvContentLines = array ();
		foreach ( $arrayContent as $line ) {
			$csvLine = array ();
			if(!is_array($line)){
			    deb($arrayContent,0);
			    throw new Exception("Formato de array informado está incorreto para conversão em CSV. Formato correto a ser recebido: ARRAY. Tipo encontrado: ".strtoupper(gettype($line)));
			}
			foreach ( $line as $colname => $content ) {
	
				// exception
				$content = str_replace ( CSV::separadorRegistros(), CSV::separadorRegistrosTEMP, $content );
				$content = str_replace ( CSV::separadorParametros, CSV::separadorParametrosTEMP, $content );
	
				if (! in_array ( $colname, $colNames )) {
					$colNames [] = $colname;
				}
				$csvLine [] = $content;
			}
			$csvLine = implode ( CSV::separadorParametros, $csvLine );
			$csvContentLines [] = $csvLine;
		}
		{ // add colnames to the beginning of array
		    if($header){
		        $colNamesCSV = implode ( CSV::separadorParametros, $colNames );
		        array_unshift ( $csvContentLines, $colNamesCSV );
		    }			
		}
	
		$return = implode ( CSV::separadorRegistros(), $csvContentLines );
	
		return $return;
	}
	
	static function CSVToObjectArray($CSV)
	{		
		$registroBaseCSVArray = CSV::CSVToArray($CSV);
		
		$return = array();
		if (! is_array($registroBaseCSVArray))
			throw new Exception("Parâmetro deve ser um array.");
		foreach ($registroBaseCSVArray as $registroBaseCSV) {
			if (! is_array($registroBaseCSV))
				throw new Exception("Parâmetro deve ser um array.");
			$registroTmp = new \stdClass();
			foreach ($registroBaseCSV as $parametro => $valor) {
				$registroTmp->$parametro = $valor;
			}
			$return[] = $registroTmp;
		}
		return $return;
	}
	
	static function CSVToHTML(string $csv,array $tableAtt=[],bool $header=true,bool $sort=true,bool $pagination=true,bool$search=true){
	    $return = [];
	    
	    {//table attributes
	        {//class    
	            if(!isset($tableAtt['class'])){
	                $tableAtt['class'] = 'table table-striped table-bordered table-hover table-sm';	                                             
	            }
	        }
	        {//...to string
	            $tableAttributesString = '';
	            foreach ($tableAtt as $attr=>$content){
	                $tableAttributesString .= "$attr='$content' ";
	            }
	        }
	    }
	    
	    //deb($thAtt);
	    	    
	    {//html'ização	        
	        $array = CSV::CSVToArray(trim($csv));
	        //deb($array);
	        if(sizeof($array)>0){
	            $return[] = '<div class="table-responsive">';
	            
	            {//bootstraf table stuff (http://bootstrap-table.wenzhixin.net.cn/getting-started)                
	                $data_toggle = $header ? "data-toggle='table'" : "";	                
	                $pagination = $pagination ? "data-pagination='true'" : "";	                
	                $search = $search? "data-search='true'" : "";
	                $sort = $sort ? "data-sortable='true'" : "";
	            }	            
	            $return[] = "<table $tableAttributesString $data_toggle $pagination $search>";
	            
	            if($header==true){
	                
	                $return[] = '<thead>';
	                $return[] = '<tr>';
	                foreach (array_keys($array[0]) as $key){	                    
	                    //deb($key,0);
	                    $return[] = "<th scope='col' $sort data-field='$key'>$key</th>";
	                }
	                $return[] = '</tr>';
	                $return[] = '</thead>';
	            }
	            $return[] = '<tbody>';
	            foreach ($array as $line){
	                
	                $return[] = '<tr>';
	                foreach ($line as $value){
	                    $return[] = "<td scope='row'>$value</td>";
	                }
	                $return[] = '</tr>';
	            }	            
	            $return[] = '</tbody>';
	            $return[] = '</table>';
	            $return[] = '</div>';
	        }else{
	            $return[] = 'Nenhum registro encontrado.';
	        }
	    }
	    
	    $return = implode(chr(10),$return);
	    return $return;
	}	
	
}

?>
