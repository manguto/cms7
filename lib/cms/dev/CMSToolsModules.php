<?php
namespace manguto\cms7\lib\cms\dev;

use manguto\cms7\lib\Arquivos;

class CMSToolsModules
{
    const menuFileEndBound = '{/if}';
    static function GenerateFile($platform, $ModuleName)
    {
        // deb($platform,0); deb($modulename);        
        {
            $platform = strtolower($platform);
            $Platform = ucfirst($platform);
        }
        {
            $modulename = strtolower($ModuleName);
        }
        {
            $fileListAndContent = self::getFileListAndContent($platform, $Platform);
            // debc($fileListAndContent);
        }
        
        {
            $replacedFileListAndContent = self::replaceFileListAndContent($fileListAndContent, $modulename, $ModuleName);
            //debc($replacedFileListAndContent);
        }
        {
            $return = self::createFileList($replacedFileListAndContent);
        }
        {
            $return[] = self::updateMenuLinks($platform,$ModuleName,$modulename); 
        }
        return $return;
    }
    
    static function updateMenuLinks($platform,$ModuleName,$modulename){
        $filename = "sis/tpl/$platform/_menu.html";
        
        $menufile_content = Arquivos::obterConteudo($filename);
        
        if($platform=='site'){
            $link = "<a href='/$modulename' class='btn btn-sm btn-light mr-1'>$ModuleName</a>";
        }else{
            $link = "<a href='/$platform/$modulename' class='btn btn-sm btn-light mr-1'>$ModuleName</a>";
        }
        

        
        //verifica se o link já nao foi inserido
        if(strpos($menufile_content, $link)===false){
            //caso nao tenha sido inserido, insere-o
            $replace = '    '.$link.chr(10).chr(10).self::menuFileEndBound;
            $menufile_content = str_replace(self::menuFileEndBound, $replace, $menufile_content);
            Arquivos::escreverConteudo($filename, $menufile_content);
            return "Link '$ModuleName' inserido no menu do ".strtoupper($platform).".";
        }else{
            return "Link '$ModuleName' já presente no menu do ".strtoupper($platform).".";
        }        
    }

    static function getFileListAndContent($platform, $Platform)
    {
        $return = [];
        
/*         $return["sis/control/$platform/Control{$Platform}Zzz.php"] = "";
        $return["sis/view/$platform/View{$Platform}Zzz.php"] = "";
        $return["sis/tpl/$platform/{$platform}_zzz.html"] = "";
         */
        $return["sis/control/$platform/ControlZzz.php"] = "";
        $return["sis/view/$platform/ViewZzz.php"] = "";
        $return["sis/tpl/$platform/zzz.html"] = "";        
        
        foreach (array_keys($return) as $filename) {
            $content = Arquivos::obterConteudo($filename);
            $return[$filename] = $content;
        }
        return $return;
    }
    static function replaceFileListAndContent($fileListAndContent, $modulename,$ModuleName)
    {
        $return = [];        
        foreach ($fileListAndContent as $filename=>$content) {
            {
                $filename = str_replace('Zzz', $ModuleName, $filename);
                $filename = str_replace('zzz', $modulename, $filename);
            }
            {
                $content = str_replace('Zzz', $ModuleName, $content);
                $content = str_replace('zzz', $modulename, $content);
            }
            $return[$filename] = $content;
        }
        return $return;
    }
    static function createFileList($replacedFileListAndContent)
    {
        $return = [];        
        foreach ($replacedFileListAndContent as $filename=>$content) {
            if(!file_exists($filename)){
                Arquivos::escreverConteudo($filename, $content);
                $return[] = "Arquivo '$filename' criado com sucesso!";
            }else{
                $return[] = "Arquivo '$filename' já existente. Nenhum procedimento a ser realizado.";
            }            
        }
        return $return;
    }
}
