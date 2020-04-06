<?php
namespace manguto\cms7\lib\cms;

/**
 * Documentation for web designers:
 * https://github.com/feulf/raintpl/wiki/Documentation-for-web-designers
 * @author Marcos Torres
 */
class CMSPageTools
{

    private const referenceTags = [
        'href',
        'src',
        'action'
    ];

    private const changeExceptions = [
        '#',                //ancoras
        'http',
        'https',
        'javascript',       //javascript a serem acionados
        'mailto',           //referencias para email
        SIS_HTML_PATH       //para o caso de referencias que sejam iniciadas com esta constante
    ];

    private const replace = SIS_HTML_PATH;
    
    /**
     * corrige as referencias feitas no HTML (href,src...) com base nos padroes informados do servidor
     */
    static function TplReferencesFix($html)
    {
        $replace = self::replace;
        
        { // ajuste do espacamento entre os caracteres em questao (ex.: href ='#indice' => href='#indice')
            foreach (self::referenceTags as $tag) {                
                $html = strtr($html, [
                    $tag." =" => $tag."=",
                    $tag."= " => $tag."=",
                    $tag."=' " => $tag."='",
                    $tag.'=" ' => $tag.'="',
                ]);
            }
        }
        {//substituicoes (ex.: href='/admin/login' => href='{SIS_HTML_PATH}/admin/login')
            foreach (self::referenceTags as $tag) {
                
                {//formas de exposicao da tag em questao
                    $search_array = [];
                    $search_array[] = $tag . "='"; //aspas simples
                    $search_array[] = $tag . '="'; //aspas duplas
                }
                
                {//substituicao
                    foreach ($search_array as $search){
                        $html = str_replace($search, $search . $replace , $html);
                    }
                } 
            }
        }
        {//retificacao de substituicoes indevidas (ex.: href='javascript' => href='{SIS_HTML_PATH}javascript' => href='javascript' )
            foreach (self::referenceTags as $tag) {
                foreach (self::changeExceptions as $exception){                    
                    $html = str_replace($replace.$exception, $exception, $html);
                }
            }
        }        
        
        return $html;
    }

    //########################################################################################################################
    //########################################################################################################################
    //########################################################################################################################
    //########################################################################################################################
    //########################################################################################################################
    //########################################################################################################################
    
    /*//strings que precisam ser resguardadas quanto as substituicoes da cte 'change'
    private const dontChange = [
        // -------------------------------------------------
        'href="#',
        "href='#",
        // -------------------------------------------------
        'href="http',
        "href='http",
        // -------------------------------------------------
        'href="javascript',
        "href='javascript",
        // -------------------------------------------------
        'src="http',
        "src='http",
        // -------------------------------------------------
        'src="javascript',
        "src='javascript",
        // -------------------------------------------------
        'action="http',
        "action='http",
        // -------------------------------------------------
        'action="javascript',
        "action='javascript"
        // -------------------------------------------------
    ];
    
    //strings que devem ser substituidas
    private const change = [
        // ------------------------------------------------- href root
        'href="' => 'href="' . SIS_HTML_PATH,
        "href='" => "href='" . SIS_HTML_PATH,
        // ------------------------------------------------- src
        'src="' => 'src="' . SIS_HTML_PATH,
        "src='" => "src='" . SIS_HTML_PATH,
        // ------------------------------------------------- action
        'action="' => 'action="' . SIS_HTML_PATH,
        "action='" => "action='" . SIS_HTML_PATH,
        // ------------------------------------------------- href errors fix
        SIS_PATH . 'javascript' => 'javascript',
        SIS_PATH . '#' => '#'
        // -------------------------------------------------
    ];
    
    //corrige as referencias feitas no HTML (href,src...) com base nos padroes informados do servidor
    static function TplReferencesFix($html)
    {
        // ==================================================================================== masc
        foreach (self::dontChange as $k => $v) {
            $html = str_replace($v, '#_' . $k . '_#', $html);
        }
        // ==================================================================================== action
        foreach (self::change as $search => $replace) {
            if (strpos($html, $search) !== false) {
                Logs::set('info', "Template Reference fix ('$search' => '$replace') ", [
                    __METHOD__
                ]);
            }
            $html = str_replace($search, $replace, $html);
        }
        // ==================================================================================== unmask
        foreach (self::dontChange as $k => $v) {
            $html = str_replace('#_' . $k . '_#', $v, $html);
        }
        // ======================================================================================== 
        return $html;
    }/**/
}

?>