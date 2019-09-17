<?php
//#################################################################################################################################
//###################################### FUNCTIONS #############################################################################
//#################################################################################################################################



function getFixContent($filename){
        
    $content = file_get_contents($filename);
    
    { // replaces
        { // tabs
            // $content = str_replace('\t', ' ', $content);
            $tab = '#TAB#';
            $content = trim(preg_replace('[\t]', $tab, $content));
        }
        { // lines
            $lines = explode(chr(10), $content);
            
            { // confs
                $exts = [
                    'php',
                    'html',
                    'json',
                    ''
                ];
            }
            for ($i = 0; $i < sizeof($lines); $i ++) {
                
                { // parameters
                    
                    $line = $lines[$i];
                    
                    
                    
                    { // margin-left
                        
                        //remove os espacoes a direita de linhas com conteudo
                        if(trim($line)!=''){
                            $line = rtrim($line);
                        }
                        
                        {//contagem de espacamentos a esquerda
                            $line_tab_n = substr_count($line, $tab);
                        }
                        
                        {//remove espacamentos desnecessarios ded
                            $line = str_replace($tab, '', $line);
                        }
                        
                        $margin_left = ($line_tab_n * 20) . 'px';                        
                    }
                    
                    { // comentarios
                        if(strpos($line,'-')!==false){
                            $line_ = explode('-', $line);
                            $line = array_shift($line_);
                            $comment = implode(' - ',$line_);
                            $comment = trim($comment);
                        }else{
                            $comment = '';
                        }
                        
                        if($comment!=''){
                            $comment = "<span class='comment'> &#8594; $comment</span>";
                        }                        
                    }
                    
                    
                }
                { // analisys
                    
                    { // class
                        if (trim($line) != '') {
                            $class = 'item ';
                        } else {
                            $class = '';
                        }
                        
                        if (strpos($line, '/')) {
                            $class .= 'folder ';
                        }
                        
                        foreach ($exts as $ext) {
                            if (strpos($line, '.' . $ext)) {
                                $class .= 'file ';
                            }
                        }
                    }
                }
                
                $lines[$i] = "<div class='$class' style='margin-left:$margin_left;' title='$line_tab_n'>" . $line . $comment . "</div>";
            }
            $content = implode(chr(10), $lines);
        }
    }
    return $content;
}

function getReplace($filename,$variables){
    $content = file_get_contents($filename);
    
    foreach ($variables as $variableName=>$variableContent) {
        
        $search = "{\$$variableName}";
        
        if(strpos($content, $search)!==false){
            $content = str_replace($search, $variableContent, $content);
        }
    }
    return $content;
}

//#################################################################################################################################
//########################################### CONTENT #############################################################################
//#################################################################################################################################

$parameters = [
    'front' =>getFixContent('front.html'),
    'back' =>getFixContent('back.html')
];


$content = getReplace('struct.html', $parameters);

echo $content;

exit();






?>
