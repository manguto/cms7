<?php
namespace manguto\cms5\lib\cms;

use manguto\cms5\lib\Exception;
use manguto\cms5\lib\Logs;
use manguto\cms5\lib\ServerHelp;

/**
 * Documentation for web designers:
 * https://github.com/feulf/raintpl/wiki/Documentation-for-web-designers *
 *
 * @author Marcos Torres *
 */
class CMSPage_Tools
{

    /**
     * strings que precisam ser resguardadas quanto as substituicoes da cte 'change'
     */
    private const dontChange = [
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
    
    /**
     * strings que devem ser substituidas
     */ 
    private const change = [
        // ------------------------------------------------- href root
        'href="' => 'href="' . ROOT_URL,
        "href='" => "href='" . ROOT_URL,
        // ------------------------------------------------- href errors fix
        ROOT . 'javascript' => 'javascript',
        ROOT . '#' => '#',
        // ------------------------------------------------- src
        'src="' => 'src="' . ROOT_URL,
        "src='" => "src='" . ROOT_URL,
        // ------------------------------------------------- action
        'action="' => 'action="' . ACTION_ROOT,
        "action='" => "action='" . ACTION_ROOT
        // ------------------------------------------------- 
    ];

    /**
     * corrige as referencias feitas no HTML (href,src...) com base nos padroes informados do servidor
     */
    static function TplReferencesFix($html)
    {
        if (! defined('ROOT')) {
            throw new Exception("Constante 'ROOT' não encontrada (definida).");
        }

        $html = self::TplReferencesFix_MASK($html);
        //debc($html);
        
        $html = self::TplReferencesFix_ACTION($html);
        

        $html = self::TplReferencesFix_UNMASK($html);
        
        //debc($html);

        return $html;
    }

    private static function TplReferencesFix_ACTION($html)
    {
        foreach (self::change as $search => $replace) {             
            if (strpos($html, $search) !== false) {
                Logs::set('info', "Template Reference fix ('$search' => '$replace') ", [
                    __METHOD__
                ]);
            }
            $html = str_replace($search, $replace, $html);
        }
        return $html;
    }

    private static function TplReferencesFix_MASK($html)
    {
        foreach (self::dontChange as $k => $v) {
            $html = str_replace($v, '#_' . $k . '_#', $html);
        }
        return $html;
    }

    private static function TplReferencesFix_UNMASK($html)
    {
        foreach (self::dontChange as $k => $v) {
            $html = str_replace('#_' . $k . '_#', $v, $html);
        }
        return $html;
    }
}

?>