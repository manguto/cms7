<?php
namespace manguto\cms5\lib;

class Exception extends \Exception
{

    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Exibicao de alguma excessao ou mensagem de erro
     *
     * @param boolean $echo
     * @return string
     */
    public function show($echo = false)
    {
        return self::static_show($this, $echo);
    }

    static function static_show($e, $echo = false)
    {
        $type = gettype($e);
        $return = "<pre title='$type'><br/>";
        $return .= '<b>' . nl2br($e->getMessage()) . '</b><br/><br/>';
        $return .= $e->getFile() . ' (' . $e->getLine() . ')<br/><br/>';
        $return .= nl2br($e->getTraceAsString()) . '<br/><br/>';
        if ($echo) {
            echo $return;
        } else {
            return $return;
        }
    }

    /**
     * imprime o caminho percorrido ate o local
     * quantas vezes o codigo passar pelo mesmo
     *
     * @param string $msg
     * @throws Exception
     */
    static function deb($msg = '')
    {
        try {
            throw new Exception($msg);
        } catch (Exception $e) {
            Exception::static_show($e, true);
        }
    }

    public function getTraceAsArray()
    {
        { // conf
            $lineDelimiter = '#';
        }
        $return = [];
        $traceAsString = $this->getTraceAsString();
        // deb($traceAsString);

        $trace_line_array = explode($lineDelimiter, $traceAsString);
        // deb($trace_line_array);

        foreach ($trace_line_array as $trace_line) {
            $trace_line = Strings::RemoverQuebrasDeLinha($trace_line);
            $trace_line = trim($trace_line);
            
            // pula linhas vazias
            if ($trace_line == ''){
                continue;
            }   
            
            //remove o indice à esquerda ("34 Xxxxxxxx xxxxxxxx")
            $trace_line = substr($trace_line, strpos($trace_line, ' '));
            
            //remoce o registro referente ao index
            if(strpos($trace_line, '{main}')){
                $trace_line = getcwd().DIRECTORY_SEPARATOR.'index.php';
            }            
            $return[] = trim($trace_line);

        }
        //remove ultimo registro referente a chamada a esta mesma funcao
        array_shift($return);
        
        //inverte a ordem do array
        rsort($return);
        return $return;
    }

    /**
     * parseia a string do trace em parametros
     * @param string $trace_line
     * @return array
     */
    private function getTraceAsArray_line(string $trace_line):array
    {
        $trace_line_info_array = explode(' ', $trace_line);
        // deb($trace_line_info_array);
        {
            $index = array_shift($trace_line_info_array);
            {
                $file_line = array_shift($trace_line_info_array);
                $file_line_s = explode('(', $file_line);
                $file = array_shift($file_line_s);
                $file = trim($file);
                $file_ = explode(' ', $file);
                $file = array_pop($file_);
                // deb($file);
                {
                    $lineNumber = array_shift($file_line_s);
                    $lineNumber = str_replace('):', '', $lineNumber);
                    $lineNumber = trim($lineNumber);
                    // deb($fileNumber);
                }
            }
            {
                $class_method_function = array_shift($trace_line_info_array);
            }
        }
        $return = [
            'index' => $index,
            'file' => $file,
            'line' => $lineNumber,
            'function' => $class_method_function
        ];
        
        return $return;
    }
}

?>