<?php
use manguto\cms5\lib\ProcessResult;
use manguto\cms5\mvc\model\User;    

// =============================================================================================================================================
// =============================================================================================================================================
// ======================================== HTML TEMPLATES FUNCTIONS USE ========================================================================
// =============================================================================================================================================
// =============================================================================================================================================

{

    // ---------------------------------- USER & SESSION
    function checkUserLogged()
    {   
        return User::checkUserLogged();
    }

    function getUserName()
    {
        $user = User::getSessionUser();        
        return $user->getName();
    }

    function checkUserLoggedAdmin()
    {
        return User::checkUserLoggedAdmin();
    }

    function checkUserLoggedDev()
    {
        return User::checkUserLoggedDev();
    }
}

{

    // ---------------------------- Process Results -> error / success / warning
    function getProcessResults($unset_all = false)
    {
        return ProcessResult::GET($unset_all);
    }
}

{

    // ------------------------------ Redirections
    function headerLocation($url,$die=true)
    {
        header('Location: ' . ROOT_LOCATION . $url);
        if($die){
            exit();
        }
    }

    function headerLocationPost(string $URLAbsolute, array $variables = [])
    {
        $url = ROOT_LOCATION . $URLAbsolute;
        
        $inputs = '';
        foreach ($variables as $key => $value) {
            
            // ajuste no caso de parametros informados em array (checkboxes...)
            if (! is_array($value)) {
                $inputs .= "$key: <input type='text' name='$key' value='$value' class='form-control mb-2' style='display:none;'>";
            } else {
                $key = $key . '[]';
                foreach ($value as $v) {
                    $inputs .= "$key: <input type='text' name='$key' value='$v' class='form-control mb-2' style='display:none;'>";
                }
            }
        }
        
        $html = "<!DOCTYPE html>
                <html>
                    <head>
                        <title>REDIRECTION...</title>
                    </head>
                    <body>
                        <section>
                        	<div class='container'>
                        		<form method='post' action='$url' id='postRedirect' style='display:none;'>
                                    $inputs
                        			<input type='submit' value='CLIQUE AQUI PARA CONTINUAR...' style='display:none;'>
                        		</form>
                        	</div>
                        </section>
                    </body>
                </html>
                <script type='text/javascript'>
                    (function() {
                        document.getElementById('postRedirect').submit();
                    })();
                </script>";
        echo $html;
    }

    // ------------------------------ VIEW - Templates
    /*function include_tpl(string $filename):string 
    {
        return CMSPage::include_tpl($filename);
    }*/
}

?>