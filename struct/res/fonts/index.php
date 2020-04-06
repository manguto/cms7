<?php
/**
 * ARQUIVO QUE GERA O CODIGO CSS PARA IMPORTACAO 
 * COM TODAS AS FONTES DISPONIVEIS NA PASTA ONDE 
 * ESTE SE ENCONTRA (res/general/fonts)
 * @var array $css
 */

class Fonts{
    
    public $fontNameArray = [];
        
    public function Start(){
        if(!isset($_GET['show'])){
            //header("Content-type: text/css; charset: UTF-8");
            $filename = 'fonts.css';
            file_put_contents($filename,$this->generateCSS());
            echo "<h1 style='font-size:60px;'>Fontes Atualizadas com sucesso!</h1>";            
            echo '<hr/>';
            echo $this->show();
            echo '<hr/>';
            echo nl2br(file_get_contents($filename));
            echo '<hr/>';
        }else{
            header("Content-type: text/html; charset: UTF-8");
            echo $this->show();
        }
    }
    
    public function show(){
        $html = [];
        
        
        $html[] = "<style type='text/css'>";
        $html[] = $this->generateCSS();
        $html[] = "</style>";
        foreach ($this->fontNameArray as $fontURL=>$fontName){
            $html[] = "<div style='font-family:$fontName; font-size:18px; border-bottom:solid 1px #aaa; width:70%;'>";
            $html[] = "<h1>$fontName<h1>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat [$fontURL].";
            $html[] = "</div>";
        }
        return implode(chr(10), $html);        
    }
    
    public function generateCSS(){
        $cssFontFace = array();
        
        $arquivos = $this->obterArquivosPastas('.', true, true, false,array('ttf'));
        
        foreach ($arquivos as $ttf){
            
            //evita fontes desnecessarias
            //if(strpos($ttf, 'Regular')===false) continue;
            
            //name
            $fontName = str_replace('.ttf', '', $ttf);
            $fontName = str_replace('-', '', $fontName);
            $fontName = str_replace('Regular', '', $fontName);
            $fontName = explode('\\', $fontName);
            $fontName = array_pop($fontName);
            //url
            $fontURL = $ttf;
            $fontURL = str_replace('.\\','/', $fontURL);
            $fontURL = str_replace('\\', '/', $fontURL);
            //$fontURL = str_replace('/res/general/', '../', $fontURL);
            $fontURL = '../fonts'.$fontURL;
            //echo (var_dump($fontURL));
            //save 
            $this->fontNameArray[$fontURL] = $fontName;
            
            
            $cssFontFace[] = $this->generateFontFaceCode($fontName, $fontURL);
        }
        $cssFontFace = implode(chr(10), $cssFontFace);
        return $cssFontFace;
    }
    
    private function generateFontFaceCode($fontName,$fontURL){
        return "
            @font-face{ 
                font-family:\"$fontName\"; 
                src:url(\"$fontURL\"); 
                font-size:1em;
            }";
    }
    
    private function obterArquivosPastas(string $path,bool $recursive,bool $filesAllowed,bool $foldersAllowed,array $allowedExtensionArray = array()) {
        //die($path);
        $path = $this->fixDirectorySeparator ( $path );
        
        if ($filesAllowed == false) {
            $allowedExtensionArray = false;
        }
        
        $dh = opendir ( $path );
        
        $return = array ();
        
        while ( false !== ($filename = readdir ( $dh )) ) {
            
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            
            $filename = $path . DIRECTORY_SEPARATOR . $filename ;
            
            $filename = $this->fixDirectorySeparator ( $filename );
            
            if (is_dir ( $filename )) {
                // --- ADICIONA FOLDERNAME
                $return [] = $filename . DIRECTORY_SEPARATOR;
                
                if ($recursive) {
                    $filename = $this->obterArquivosPastas ( $filename, $recursive, $filesAllowed, $foldersAllowed, $allowedExtensionArray);
                    if (sizeof ( $filename ) > 0) {
                        foreach ( $filename as $f ) {
                            // --- ADICIONA SUB-FILENAMES
                            $return [] = $f;
                        }
                    }
                }
            } else {
                // --- ADICIONA FILENAME
                $return [] = $filename;
            }
        }
        
        foreach ( $return as $k => $filename ) {
            if (is_dir ( $filename ) && ! $foldersAllowed) {
                unset ( $return [$k] );
            }
            if (is_file ( $filename ) && $allowedExtensionArray === false) {
                unset ( $return [$k] );
            }
            if (is_file ( $filename ) && $allowedExtensionArray !== false) {
                $extension = $this->obterExtensao ( $filename );
                // debug($extension,0);
                // debug($allowedExtensionArray,0);
                if (sizeof ( $allowedExtensionArray ) > 0 && ! in_array ( $extension, $allowedExtensionArray )) {
                    unset ( $return [$k] );
                }
            }
        }
        
        return $return;
    }
    private function fixDirectorySeparator($path) {
        $path = str_replace ( '/', DIRECTORY_SEPARATOR, $path );
        $path = str_replace ( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path );
        return $path;
    }
    private function obterExtensao($filename) {
        $extensao = pathinfo ( $filename, PATHINFO_EXTENSION );
        // debug($extensao);
        return $extensao;
    }
}

/**
 * START!
 */
$Fonts = new Fonts();
$Fonts->Start();
/**
 * 
 */
?>

