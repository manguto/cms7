<?php
use manguto\cms7\lib\Variables;

// #############################################################################################################################################
// ########################################################################################################################### GENERAL FUNCTIONS
// #############################################################################################################################################

/**
 * DEBUG VARIABLE - IMPRIME ou RETORNA (die=false) a representacao HTML da variavel informada
 *
 * @param number $level
 * @return string
 */
function debv($variable, $die = true, int $level = 0)
{
    $type = gettype($variable);
    // boolean, integer, double, string, NULL, array, object, resource, unknown type

    { // td key attr
        $td_attr = " title='$type ' style='cursor:pointer; text-align:right;'";
    }

    $return = array();
    $return[] = "<table border='0' style='border-left:solid 1px #aaa; border-bottom:solid 1px #aaa; ' '>";
    {
        if ($type == 'boolean' || $type == 'integer' || $type == 'double' || $type == 'string' || $type == 'NULL') {

            // ajuste para melhor exibição
            $variable = trim($variable) == '' ? '&nbsp;' : '= ' . $variable;

            $return[] = "<tr>";
            $return[] = "<td $td_attr>$variable</td>";
            $return[] = "</tr>";
        } else if ($type == 'array' || $type == 'object') {

            // conversao do objeto em array
            if ($type == 'object') {
                $variable = (array) $variable;
                $tagPre = '-> ';
                $tagPos = '';
            } else {
                $tagPre = '[';
                $tagPos = ']';
            }
            foreach ($variable as $key => $var) {
                $return[] = "<tr>";
                $return[] = "<td $td_attr>$tagPre$key$tagPos</td>";
                $return[] = "<td>" . debv($var, false, ($level + 1)) . "</td>";
                $return[] = "</tr>";
            }
        } else if ($type == 'resource') {
            $return[] = "<tr>";
            $return[] = "<td $td_attr>'resource'</td>";
            $return[] = "</tr>";
        } else {
            $return[] = "<tr>";
            $return[] = "<td $td_attr>'unknown type'</td>";
            $return[] = "</tr>";
        }
    }
    $return[] = "</table>";
    $return = implode(chr(10), $return);

    if ($die) {
        echo "<pre style='cursor:pointer;'>$return</pre>";
        die();
    } else {
        return $return;
    }
}

// #############################################################################################################################################
/**
 * DEBUG - IMPRIME a representacao HTML da variavel informada
 *
 * @param bool $die
 * @param bool $backtrace
 */
function deb($var, bool $die = true, bool $backtrace = true)
{

    // backtrace show?
    if ($backtrace) {
        $backtrace = backtraceFix(get_backtrace(), false);
    } else {
        $backtrace = '';
    }

    // var_dump to string
    ob_start();
    var_dump($var);
    $var = ob_get_clean();

    // remove a kind of break lines
    { // values highligth
        $var = str_replace('=>' . chr(10), ' => <span class="varContent">', $var);
        $var = str_replace(chr(10), '</span>' . chr(10), $var);
    }
    { // remove spaces
        while (strpos($var, '  ')) {
            $var = str_replace('  ', ' ', $var);
        }
    }
    { // parameter name highligth
        $var = str_replace('["', '[<span class="varName">', $var);
        $var = str_replace('"]', '</span>]', $var);
    }
    { // content highligth
        $var = str_replace('{', '<div class="varArrayContent">{', $var);
        $var = str_replace(' }', '}</div>', $var);
    }
    { // bold values
        $var = str_replace(' "', ' "<span class="varContentValue">', $var);
        $var = str_replace('"</', '</span>"</', $var);
    }

    echo "
<pre class='deb' title='$backtrace'>$var</pre>
<style>
.deb {	line-height:17px;}
.deb .varName {	background: #ffb;}
.deb .varContent {}
.deb .varContentValue {	background: #fbb;    padding: 0px 5px;    border-radius:2px;}
.deb .varArrayContent {	border-bottom: solid 1px #ccc;	border-left: solid 1px #eee;	padding: 0px 0px 5px 5px;	margin: 0px 0px 0px 10px;    cursor:pointer;}
.deb .varArrayContent:hover {    border-color:#555;}
</style>";
    if ($die) {
        die();
    }
}

// #############################################################################################################################################
/**
 * realiza ajustes no backtrace informado para uma melhor apresentacao
 *
 * @param string $backtrace
 * @param boolean $sortAsc
 */
function backtraceFix(string $backtrace, $sortAsc = true)
{
    $backtrace = str_replace("'", '"', $backtrace);
    { // revert order
        $backtrace_ = explode(chr(10), $backtrace);
        if ($sortAsc == false) {
            krsort($backtrace_);
        }
        $backtrace = implode(chr(10), $backtrace_);
    }
    return $backtrace;
}

// #############################################################################################################################################
/**
 * DEBUG - IMPRIME a representacao STRING da variavel informada em um campo de texto
 *
 * @param bool $die
 * @param bool $backtrace
 */
function debc($var, bool $die = true, bool $backtrace = true)
{
    // backtrace show?
    if ($backtrace) {
        $backtrace = backtraceFix(get_backtrace(), false);
    } else {
        $backtrace = '';
    }
    // var_dump to string
    ob_start();
    var_dump($var);
    $var = ob_get_clean();
    echo "<textarea style='border:solid 1px #000; padding:5px; width:90%; height:400px;' title='$backtrace'>$var</textarea>";
    if ($die) {
        die();
    }
}

// #############################################################################################################################################
/**
 * Get Backtrace
 *
 * @return string
 */
function get_backtrace(): string
{
    // obtem backtrace
    $trace = debug_backtrace();
    // removao da primeira linha relativa a chamada a esta mesma funcao
    array_shift($trace);
    // inversao da ordem de exibicao
    krsort($trace);
    $return = '';
    $step = 1;
    foreach ($trace as $t) {

        if (isset($t['file'])) {
            $file = $t['file'];
            $line = $t['line'];
            $func = $t['function'];
            $return .= "#" . $step ++ . " => $func() ; $file ($line)\n";
        }
    }
    // identacao
    $return = str_replace(';', '', $return);

    return $return;
}

// #############################################################################################################################################
// ############################################################################################################################ ERRORS FUNCTIONS
// #############################################################################################################################################

/**
 * funcao utilizaca para tratamento de erros fatais
 */
function fatal_error_handler()
{
    $error = error_get_last();

    if ($error !== NULL) {

        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];

        echo format_fatal_error($errno, $errstr, $errfile, $errline);
        exit();
    }
}
// #############################################################################################################################################
/**
 * formatacao da exibicao do erro fatal
 *
 * @param integer $errno
 * @param string $errstr
 * @param
 *            $errfile
 * @param integer $errline
 * @return string
 */
function format_fatal_error($errno, $errstr, $errfile, $errline)
{
    $trace = print_r(debug_backtrace(), true);

    $content = "<br />
    <table border='1' style='font-family:Courier New'>        
        <tbody>
            <tr>
                <th>Error</th>
                <td><pre>$errstr</pre></td>
            </tr>
            <tr>
                <th>Errno</th>
                <td><pre>$errno</pre></td>
            </tr>
            <tr>
                <th>File</th>
                <td><pre>$errfile</pre></td>
            </tr>
            <tr>
                <th>Line</th>
                <td><pre>$errline</pre></td>
            </tr>
            <tr>
                <th>Trace</th>
                <td><pre>$trace</pre></td>
            </tr>
        </tbody>
    </table>";

    return $content;
}

// #############################################################################################################################################
// ########################################################################################################################## VARIABLE FUNCTIONS
// #############################################################################################################################################

/**
 * Obtencao de variavel via GET
 *
 * @param string $varname
 * @param string $default
 * @param bool $throwException
 * @return mixed
 */
function GET(string $varname, $default = '', bool $throwException = false)
{
    return Variables::GET($varname, $default, $throwException);
}
// #############################################################################################################################################
/**
 * Obtencao de variavel via POST
 *
 * @param string $varname
 * @param string $default
 * @param bool $throwException
 * @return mixed
 */
function POST(string $varname, $default = '', bool $throwException = false)
{
    return Variables::POST($varname, $default, $throwException);
}


// #############################################################################################################################################
// ############################################################################################################################### CSS FUNCTIONS
// #############################################################################################################################################

/**
 * retorna o termo informado repetido em sequencia
 * na quantidade informada
 *
 * @param string $input
 * @param int $multiplier
 * @return string
 */
function css_repeat(string $term, string $separator = ',', int $multiplier)
{
    return str_repeat($term . $separator, $multiplier - 1) . $term;
}

// #############################################################################################################################################
// ##################################################################################################################################### CLASSES
// #############################################################################################################################################

/**
 * Retorna o resultado da chamada a classe, metodo e parametros informados
 *
 * @param string $className
 * @param string $methodName
 * @return
 */
function staticClassMethod_call(string $className, string $methodName, $par1 = NULL, $par2 = NULL)
{
    $return = false;
    { // obtem os eventuais parametros do metodo a ser chamado
        $args = func_get_args();
        // percorre os argumentos informados, exceto os 2 primeiros (classname e methodname)
        $evalTextPars = [];
        if (sizeof($args) > 2) {
            foreach (array_keys($args) as $key) {
                $evalTextPars[] = "\$args[$key]";
            }
        }
        $evalTextPars = implode(',', $evalTextPars);
    }
    $evalText = "\$return = $className::$methodName($evalTextPars);";
    eval($evalText);
    return $return;
}

// #############################################################################################################################################
// #############################################################################################################################################
// #############################################################################################################################################
// #############################################################################################################################################
// #############################################################################################################################################

?>