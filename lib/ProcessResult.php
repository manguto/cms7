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

        if (Session::isset(self::key)) {
            $prs = Session::get(self::key);
            // deb($prs);
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
                Session::set(self::key, []);
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
            Log::ProcessResult($msg, 'exception');
        } else {
            $msg = $expection_or_message;
            Log::ProcessResult($msg, 'error');
        }
        // deb($msg);
        Session::set(self::key, '', [
            'type' => 'Error',
            'msg' => $msg
        ]);
    }

    // ==================================# WARNING CONTROL ==================================================#
    // ==================================# WARNING CONTROL ==================================================#
    // ==================================# WARNING CONTROL ==================================================#
    static function setWarning($expection_or_message)
    {

        // verifica se o parametro informado eh do tipo exception ou string
        if (is_object($expection_or_message)) {
            $msg = $expection_or_message->getMessage();
            Log::ProcessResult($msg, 'warning');
        } else {
            $msg = $expection_or_message;
            Log::ProcessResult($msg, 'warning');
        }
        // deb($msg);
        Session::set(self::key, '', [
            'type' => 'Warning',
            'msg' => $msg
        ]);
    }

    // ==================================# SUCCESS CONTROL ==================================================#
    // ==================================# SUCCESS CONTROL ==================================================#
    // ==================================# SUCCESS CONTROL ==================================================#
    static function setSuccess($expection_or_message)
    {

        // verifica se o parametro informado eh do tipo exception ou string
        if (is_object($expection_or_message)) {
            $msg = $expection_or_message->getMessage();
            Log::ProcessResult($msg, 'success');
        } else {
            $msg = $expection_or_message;
            Log::ProcessResult($msg, 'success');
        }
        // deb($msg);
        Session::set(self::key, '', [
            'type' => 'Success',
            'msg' => $msg
        ]);
    }

    // ##########################################################################################################################################
    // ##########################################################################################################################################
    // ##########################################################################################################################################
}

?>