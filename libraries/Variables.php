<?php
namespace manguto\cms7\libraries;

class Variables
{

    static function isset($variableName)
    {
        global $$variableName;

        if (isset($variableName)) {
            return true;
        } else {
            return false;
        }
    }

    static function issetAndNotEmpty($variableName)
    {
        global $$variableName;

        if (self::isset($variableName) && trim($variableName) != '') {
            return true;
        } else {
            return false;
        }
    }

    static function isntset_set(&$variableName, $value)
    {
        global $$variableName;

        if (! self::isset($variableName)) {
            return $value;
        }
    }

    /**
     * The filter_input() function gets an external variable and optionally filters it.
     * This function is used to validate variables from insecure sources, such as user input.
     *
     * @param int $INPUT_
     *            Required. The input type to check for. Can be one of the following: INPUT_GET,INPUT_POST,INPUT_...
     * @param string $variable_name
     *            Required. The variable name to check
     * @param int $FILTER_VALIDATE_
     *            Optional. Specifies the ID or name of the filter to use. Default is FILTER_DEFAULT, which results in no filtering. (Ex.: FILTER_VALIDATE_EMAIL, FILTER_VALIDATE_BOOLEAN, FILTER_VALIDATE_...)
     * @param mixed $options
     *            Optional. Specifies one or more flags/options to use. Check each filter for possible options and flags
     * @return mixed
     */
    static function filter_input(int $INPUT_, string $variable_name, int $FILTER_VALIDATE_ = NULL, $options = NULL, bool $throwException = true)
    {
        $return = filter_input($INPUT_, $variable_name, $FILTER_VALIDATE_, $options);
        $return = substr($return, 1);
        if($throwException==true && ($return==false || $return==NULL)){
            throw new Exception("Não foi possível obter o conteúdo da variável solicitada ('$variable_name').");
        }
        return $return;
    }

    static function GET(string $varname, $default = '', bool $throwException = false)
    {
        if (isset($_GET[$varname])) {
            $return = $_GET[$varname];
        } else {
            if ($throwException) {
                throw new Exception("Não foi possível obter o conteúdo da variável \$_GET[$varname]. Variável não definida.");
            } else {
                $return = $default;
            }
        }
        return $return;
    }

    static function POST(string $varname, $default = '', bool $throwException = false)
    {
        if (isset($_POST[$varname])) {
            $return = $_POST[$varname];
        } else {
            if ($throwException) {
                throw new Exception("Não foi possível obter o conteúdo da variável \$_POST[$varname]. Variável não definida.");
            } else {
                $return = $default;
            }
        }
        return $return;
    }
}


