<?php
namespace manguto\cms5\lib\html;


class HTMLPagination extends HTML
{

    public $page_varname = '';

    public $page = '0';

    public $length_varname = '';

    public $length = '10';

    public $min = false;

    public $max = false;

    public $tablename = false;

    public $objectClassName = false;

    public $page_amount = false;

    public $conditions_search = false;

    public $conditions_pagination = false;

    public $conditions_final = false;

    public $object_array_light = [];

    public $arguments = false;

    public $total = false;

    public $window_size = 5;

    public $url = '';

    public $class = 'btn btn-sm ';

    /*
     * public function __construct(array $GET, int $total, $page_varname = 'p', $length_varname = 'q')
     * {
     * $this->page_varname = $page_varname;
     * $this->length_varname = $length_varname;
     * $this->total = $total;
     * $this->load($GET);
     * }
     */
    public function __construct(string $tablename, string $conditions_search = '', array $arguments, string $page_varname = 'p', string $length_varname = 'q')
    {
        $this->tablename = $tablename;
        $this->arguments = $arguments;
        $objectClassName = Repository::getObjectClassname($tablename);
        $this->objectClassName = $objectClassName;

        { // repositorio completo

            {
                $conditions_search = $conditions_search == '' ? $this->set_conditions_search() : $conditions_search;
            }

            $object_array_light = $objectClassName::getList($conditions_search, $returnAsObject = true, $loadReferences = false, $loadCtrlParameters = false);
            // deb($produto_array_light);
        }
        {
            $total = sizeof($object_array_light);
            // deb($total);
        }

        $this->page_varname = $page_varname;
        $this->length_varname = $length_varname;
        $this->object_array_light = $object_array_light;
        $this->total = $total;

        $this->load($arguments);
    }

    public function getLinks()
    {
        $link_array = [];

        { // DEFINIÇÃO DE TODOS OS LINKS

            { // INICIO =======================================================================
                if ($this->page != 0) {
                    $link_array[] = $this->setLink(0, 'Início', $this->class . ' btn-outline-dark');
                }
            }
            { // ANTERIOR ======================================================================
                if ($this->page >= 1) {
                    $link_array[] = $this->setLink($this->page - 1, 'Anterior', $this->class . ' btn-outline-dark');
                }
            }
            { // INTERMEDIARIOAS (CONTEUDO JANELA) =============================================
                for ($i = ($this->page - $this->window_size); $i < ($this->page + $this->window_size); $i ++) {

                    if ($i < 0) {
                        continue;
                    }

                    if (intval($i) + 1 > $this->page_amount) {
                        continue;
                    }

                    $link_array[] = $this->setLink($i, ($i + 1), $this->class . ' btn-outline-dark');
                }
            }
            { // POSTERIOR =====================================================================
                if ($this->page + 1 < $this->page_amount) {
                    $link_array[] = $this->setLink($this->page + 1, 'Seguinte', $this->class . ' btn-outline-dark');
                }
            }
            { // FINAL =========================================================================
                if ($this->page + 1 != $this->page_amount) {
                    $link_array[] = $this->setLink($this->page_amount - 1, 'Final', $this->class . ' btn-outline-dark');
                }
            }
        }

        { // AJUSTE DE LINKS ESPECIFICOS
            foreach ($link_array as $key => $link) {
                // pagina atual
                if (intval($link['page']) == $this->page) {
                    $link_array[$key]['class'] .= ' btn-warning ';
                }
            }
        }

        // deb($link_array);
        return $link_array;
    }

    private function setLink(int $page, string $title, string $class = '', $attributes = '')
    {
        {
            $url = "&" . $this->page_varname . "=$page&" . $this->length_varname . "=" . $this->length;
        }
        return [
            'page' => $page,
            'url' => $this->url . $url,
            'title' => $title,
            'class' => $class,
            'attributes' => $attributes
        ];
    }

    // =======================================================================================================================================
    // =======================================================================================================================================
    // =======================================================================================================================================

    /**
     * controle e gerenciamento dos parametros de controle da paginacao
     *
     * @param array $arguments
     * @return array
     */
    public function get_conditions_pagination(): string
    {
        { // ======================================================================================================= CONDICOES
            $conditions_pagination = [];

            // ----------------------------------------------------------------------------------------
            { // produto_id
              // deb($arguments);

                $min = $this->min;
                $max = $this->max;
                // deb($min,0); deb($max);

                $i = 0;
                $produto_id_min = '';
                $produto_id_max = '';

                if (sizeof($this->object_array_light) > 0) {
                    foreach (array_keys($this->object_array_light) as $produto_id) {

                        // ------------------------------
                        if ($i <= $min) {
                            $produto_id_min = $produto_id;
                        }
                        // ------------------------------
                        if ($i < $max) {
                            $produto_id_max = $produto_id;
                        }
                        // ------------------------------
                        $i ++;
                    }
                    // deb($produto_id_min, 0);deb($produto_id_max);

                    $conditions_pagination[] = " \$id>=$produto_id_min && \$id<=$produto_id_max ";
                }
            }
        } // ====================================================================================================================

        $conditions_pagination = trim(implode(' && ', $conditions_pagination));
        // deb($conditions);
        $this->conditions_pagination = $conditions_pagination;

        return $conditions_pagination;
    }

    public function get_conditions_final($conditions_search='', $conditions_pagination='')
    {
        $conditions_search = $conditions_search=='' ? $this->set_conditions_search() : $conditions_search;
        $conditions_pagination = $conditions_pagination=='' ? $this->get_conditions_pagination() : $conditions_pagination;
        
        {
            $conditions_final = [];
            $conditions_final[] = $this->conditions_search;
            $conditions_final[] = $this->get_conditions_pagination();
            foreach ($conditions_final as $k => $c) {
                if ($c == '') {
                    unset($conditions_final[$k]);
                }
            }
            $conditions_final = implode(' && ', $conditions_final);
            // deb($conditions);
            $this->conditions_final = $conditions_final;

            return $this->conditions_final;
        }
    }

    public function set_conditions_search()
    {
        $filter_array = Repository::get_filters($this->tablename);
        //deb($filter_array);

        { // ======================================================================================================= CONDICOES
            $condition_search = [];
            // ----------------------------------------------------------------------------------------
            foreach (array_keys($filter_array) as $filter) {
                // tombamento

                if (isset($this->arguments[$filter])) {
                    
                    if(is_array($this->arguments[$filter])){
                        
                        $array_string = [];
                        foreach ($this->arguments[$filter] as $filter_unit){
                            $array_string[] = $filter_unit;
                        }
                        $condition_search[] = " in_array($$filter, ['".implode("','",$array_string)."'])!==false ";                        
                    }else{
                        if(trim($this->arguments[$filter])!=''){
                            $condition_search[] = " \$$filter == '".$this->arguments[$filter]."' ";
                        }                        
                    }
                }
            }
        } // ====================================================================================================================

        $condition_search = implode(' && ', $condition_search);
        //deb($condition_search,0);
        $this->conditions_search = $condition_search;

        return $condition_search;
    }

    // =======================================================================================================================================
    // =======================================================================================================================================
    // =======================================================================================================================================
    private function load($GET)
    {
        $this->set_page($GET);
        $this->set_length($GET);
        $this->set_data($GET);
        // ...
    }

    // =======================================================================================================================================
    // =======================================================================================================================================
    // =======================================================================================================================================
    private function set_page(array $GET): void
    {
        $page_varname = $this->page_varname;

        if (! isset($GET[$page_varname])) {
            $p = 0;
        } else {
            $p = $GET[$page_varname];
        }

        $p = intval($p);
        if ($p < 0) {
            $p = 0;
        }
        { // SET <<<<<<<<<<<<<<<<<<<
            $this->page = $p;
        } // <<<<<<<<<<<<<<<<<<<<<<<
    }

    private function set_length(array $GET): void
    {
        $length_varname = $this->length_varname;

        if (! isset($GET[$length_varname])) {
            $q = 0;
        } else {
            $q = $GET[$length_varname];
        }
        $q = intval($q);
        if ($q < 1) {
            $q = 10;
        }
        { // SET <<<<<<<<<<<<<<<<<<<
            $this->length = $q;
        } // <<<<<<<<<<<<<<<<<<<<<<<
    }

    private function set_data(array $GET)
    {
        { // MIN
            $min = $this->page * $this->length;
            $this->min = $min;
        }

        { // MAX
            $max = ($this->page + 1) * $this->length;
            $this->max = $max;
        }

        { // PAGE AMOUNT
            $this->page_amount = ceil($this->total / $this->length);
        }
        { // URL
            {
                $GET_CLEAN = $GET;
                $url = [];
                unset($GET_CLEAN[$this->page_varname]);
                unset($GET_CLEAN[$this->length_varname]);
                foreach ($GET_CLEAN as $k => $v) {
                    if(is_array($v)){
                        $v = implode(',', $v);
                    }
                    $url[] = "$k=$v";
                }
                $url = '?' . implode('&', $url);
            }
            $this->url = $url;
        }
    }
}

?>