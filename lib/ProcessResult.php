<?php
namespace manguto\cms5\lib;

class ProcessResult
{

    const key = 'ProcessResult';

    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    /**
     * obtem as mensagens (HTML) do tipo informado caso existam
     *
     * @return array
     */
    static function GET($unset_all = false): array
    {
        $return = [];

        if (Sessions::isset(self::key)) {
            $prs = Sessions::get(self::key,false);
            //deb($_SESSION);
            //deb(gettype($prs));
            //deb($prs);
            $prs = $prs=='' ? [] : $prs;
            foreach ($prs as $pr) {
                {
                    $type = $pr['type'];
                    $msg = $pr['msg'];
                    {
                        $type = strtolower($type);
                        if ($type == 'error') {
                            $col_class = 'alert-danger';
                        } else {
                            $col_class = "alert-$type";
                        }
                    }
                    {
                        if ($type == 'error') {
                            $row_class = '';
                        } else {
                            $row_class = 'hide';
                        }
                    }
                    {
                        $col_style = '';
                    }
                }
                $return[] = [
                    'type' => $type,
                    'msg' => $msg,
                    'row_class' => $row_class,
                    'col_class' => $col_class,
                    'col_style' => $col_style
                ];
            }
            // deb($unset_all,0);
            if ($unset_all == true) {
                Sessions::set(self::key, []);
            }
        }

        // deb($return);
        return $return;
    }

    // ====================================# ERROR CONTROL ==================================================#
    // ====================================# ERROR CONTROL ==================================================#
    // ====================================# ERROR CONTROL ==================================================#
    static function setError($expection_or_message)
    {
        // deb($expection_or_message);
        // verifica se o parametro informado eh do tipo exception ou string
        $parameterIsObject = is_object($expection_or_message);
        // deb($parameterIsObject);
        if ($parameterIsObject) {
            $msg = $expection_or_message->getMessage();
            // deb($msg);            
        } else {
            $msg = $expection_or_message;            
        }
        // deb($msg);
        Sessions::set(self::key, [
            'type' => 'Error',
            'msg' => $msg
        ],true);
        
        return $msg;
    }

    // ==================================# WARNING CONTROL ==================================================#
    // ==================================# WARNING CONTROL ==================================================#
    // ==================================# WARNING CONTROL ==================================================#
    static function setWarning($expection_or_message)
    {

        // verifica se o parametro informado eh do tipo exception ou string
        if (is_object($expection_or_message)) {
            $msg = $expection_or_message->getMessage();

        } else {
            $msg = $expection_or_message;

        }
        // deb($msg);
        Sessions::set(self::key, [
            'type' => 'Warning',
            'msg' => $msg
        ],true);
        
        return $msg;
    }

    // ==================================# SUCCESS CONTROL ==================================================#
    // ==================================# SUCCESS CONTROL ==================================================#
    // ==================================# SUCCESS CONTROL ==================================================#
    static function setSuccess($expection_or_message)
    {

        // verifica se o parametro informado eh do tipo exception ou string
        if (is_object($expection_or_message)) {
            $msg = $expection_or_message->getMessage();

        } else {
            $msg = $expection_or_message;
        }
        // deb($msg);
        Sessions::set(self::key, [
            'type' => 'Success',
            'msg' => $msg
        ],true);
        
        return $msg;
    }

    // ##########################################################################################################################################
    // ##########################################################################################################################################
    // ##########################################################################################################################################
}

?>