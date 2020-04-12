<?php
namespace manguto\cms7\lib;

class Calendario
{

    public $ano;

    public $mesNomeExibir = true;

    public $mesNomeTamanho = 'g';

    public $diaNomeExibir = true;

    public $diaNomeTamanho = 'p';

    public $tableId = '';

    public $tableClass = '';

    public $tableStyle = '';

    public $thTitleClass = '';

    public $thTitleStyle = '';

    public $thClass = '';

    public $thStyle = '';

    public $tdClass = '';

    public $tdStyle = '';

    // public $conteudoTD = '{$d}';
    public $conteudoMesDia = [];

    public $calendarioExibirMarcadores = true;

    const defaultCSS = '
.mes {}
.mes * {margin: 0; padding: 0;}
.mes th, td {vertical-align:bottom;}
.mes thead {}
.mes thead * {}
.mes thead th {text-align:center; padding:2px 3px;}
.mes thead th.nomedomes {font-size:100%; letter-spacing:2px; text-transform:uppercase; padding-bottom:10px;}
.mes thead th.diadasemana {font-size:80%; background:#ccc; border-left:solid 1px #000; border-right:solid 1px #000; border-top:solid 1px #000;}
.mes tbody * {}
.mes tbody {border-top:solid 1px #000; border-left:solid 1px #000;}
.mes tbody td {position:relative; vertical-align:top; padding:2px 3px;}
.mes tbody td.dia {width:50px; height:50px; border-bottom:solid 1px #000; border-right:solid 1px #000; text-align:right;}
.mes tbody td.dia div.numero {text-align:right; width:100%; font-size:60%;}
.mes tbody td.dia div.conteudo {text-align:right; vertical-align:bottom; width:100%; font-size:50%;}
.mes tbody td.dia-vazio { background: #eee;}
.mes tbody td.dia-feriado {background:#afa; cursor:pointer;}';

    public function __construct($ano)
    {
        $datas = new Datas("01/01/$ano", 'd/m/Y');
        // deb($datas);

        $this->ano = $datas->getDate('Y');
    }

    /**
     * retorna um array com o HTML de cada mes
     *
     * @return array
     */
    public function ObterMeses(): array
    {
        $meses = [];
        for ($i = 1; $i <= 12; $i ++) {
            $meses[$i] = $this->HTML_ObterMes($i);
        }
        return $meses;
    }

    /**
     * retorna um determinado mes em HTML
     *
     * @param int $mes
     * @return string
     */
    public function HTML_ObterMes(int $mes): string
    {
        $mes = intval($mes);
        { // VERIFICACAO DE DATA POSSIVEL
            Datas::checkdate('m-Y', "$mes-" . $this->ano);
        }

        $return = [];
        // -----------------------------------------------------------------------------------------
        // abertura da tabela -------------------------------------------------
        $return[] = "<table class='mes mes-$mes'>";
        // ---------------------------------------------------------------------
        $return[] = "<thead>";
        // mes nome exibir -------------------------------------------------
        if ($this->mesNomeExibir) {
            $mesNome = Datas::static_GetMonthName($mes, $this->mesNomeTamanho, false, true);
            // $mesNome = utf8_decode($mesNome);
            $return[] = "<tr>";
            $return[] = "<th colspan='7' class='$this->thTitleClass nomedomes' style='$this->thTitleStyle'>$mesNome</th>";
            $return[] = "</tr>";
        }
        // titulos dos dias exibir ------------------------------------------
        $return[] = "<tr>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(0, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(1, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(2, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(3, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(4, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(5, $this->diaNomeTamanho) . "</th>";
        $return[] = "<th class='diadasemana'>" . Datas::staticGetWeekDayName(6, $this->diaNomeTamanho) . "</th>";
        $return[] = "</tr>";
        // ---------------------------------------------------------------------
        $return[] = "</thead>";
        // ---------------------------------------------------------------------
        $return[] = "<tbody>";
        // ---------------------------------------------------------------------
        {
            $dia_vazio = "<td class='dia dia-vazio'>&nbsp;</td>";
        }
        // ---------------------------------------------------------------------
        $ultimoDiaDoMes = Datas::getMonthNumberOfDays($this->ano, $mes);
        // deb($ultimoDiaDoMes,0);
        for ($dia = 1; $dia <= $ultimoDiaDoMes; $dia ++) {

            $ddsNumero = Datas::staticGetWeekDayNumber(Datas::mktime('Y-m-d', $this->ano . "$mes-$dia"));
            { // debug help
              // deb($ddsNumero,0);
              // deb("$d/$mes/$this->ano => $ddsNumero",0);
            }

            // controle de abertura de linha
            if ($dia == 1 || $ddsNumero == 0) {
                $return[] = "<tr>";
            }

            // complemento INICIAL dos dias do mes anterior! <<<<<<<<<<!
            if ($dia == 1) {
                $return[] = str_repeat($dia_vazio, $ddsNumero);
            }

            { // detalhes do conteudo celula (MARCADORES [FERIADOS])
              // echo $this->ano."-".$mes."-$d<hr/>";
                $marcador = DatasMarcadores::obterEventualMarcador($this->ano, $mes, $dia);
                if ($marcador !== false && $this->calendarioExibirMarcadores == true) {
                    $class = 'dia-feriado';
                    $title = "title='$marcador'";
                } else {
                    $class = '';
                    $title = '';
                }
            }

            { // definicao do conteudo da celular (dia)              
                {//conteudo
                    if(isset($this->conteudoMesDia[$mes][$dia])){
                        $conteudo = implode('', $this->conteudoMesDia[$mes][$dia]);
                    }else{
                        $conteudo = '';
                    }
                    
                }
                $conteudoCelula = [];
                $conteudoCelula[] = "<div class='numero'>$dia</div>";
                $conteudoCelula[] = "<div class='conteudo'>$conteudo</div>";
                $conteudoCelula = implode('', $conteudoCelula);
            }
            // ##################################################################################>
            // ####################################################################################>
            // ######################################################################################>
            $return[] = "<td class='dia dia-$dia $class' $title>";
            $return[] = $conteudoCelula;
            $return[] = "</td>";
            // ######################################################################################>
            // ####################################################################################>
            // ##################################################################################>

            // complemento de dias do mes posterior
            if ($dia == $ultimoDiaDoMes) {
                // completa com celulas vazias relativas aos dias do mes seguinte >>>>>>>>>>>!
                $return[] = str_repeat($dia_vazio, (6 - $ddsNumero));
                // ativa o fechamento da linha
                $fecharLinha = true;
            } else {
                $fecharLinha = false;
            }

            // controle de fechamento de linha
            if ($ddsNumero == 6 || $fecharLinha) {
                $return[] = "</tr>";
            }
        }
        // tbody --------------------------------------------------------------------------------------------------------------------------
        $return[] = "</tbody>";
        // table --------------------------------------------------------------------------------------------------------------------------
        $return[] = "</table>";
        // implode content ----------------------------------------------------------------------------------------------------------------
        $return = implode('', $return);

        // ADD CLASS STYLE TAGS -----------------------------------------------------------------------------------------------------------
        // ADD CLASS STYLE TAGS -----------------------------------------------------------------------------------------------------------
        // ADD CLASS STYLE TAGS -----------------------------------------------------------------------------------------------------------

        { // DEFINICAO DE ATRIBUTOS DAS TAGS ENVOLVIDAS

            // DOM OBJECT
            $return_dom = simple_html_dom_parser::load_str($return); 
            // deb($return_dom);

            { // TABLE

                $return_dom->find('table', 0)->id = $this->tableId;
                $return_dom->find('table', 0)->class .= ' ' . $this->tableClass;
                $return_dom->find('table', 0)->style .= ' ' . $this->tableStyle;
                // deb($table);
            }
            { // TH
                $index = 0;
                foreach ($return_dom->find('th') as $th) {
                    $return_dom->find('th', $index)->class .= ' ' . $this->thClass;
                    $return_dom->find('th', $index)->style .= ' ' . $this->thStyle;
                    $index ++;
                }
            }
            { // TD
                $index = 0;
                foreach ($return_dom->find('td') as $td) {
                    $return_dom->find('td', $index)->class .= ' ' . $this->tdClass;
                    $return_dom->find('td', $index)->style .= ' ' . $this->tdStyle;
                    $index ++;
                }
            }
        }

        $return = $return_dom;

        { // REMOCAO DE ESPACOS DESNECESSARIOS
            $return = str_replace(' "', '"', $return);
            $return = str_replace('" ', '"', $return);
            $return = str_replace(" '", "'", $return);
            $return = str_replace("' ", "'", $return);
        }

        // return -------------------------------------------------------------------------------------------------------------------------
        return $return;
    }

    /**
     * retorna um determinado ano em HTML
     *
     * @param int $mes
     * @return string
     */
    public function HTML_ObterAno(): string
    {
        $meses_html_array = $this->ObterMeses();
        $ano_html = implode(chr(10), $meses_html_array);
        return $ano_html;
    }
    
    public function adicionarConteudoMesDia($mes, $dia, $conteudo)
    {
        { // start!
            if (! isset($this->conteudoMesDia[$mes][$dia])) {
                $this->conteudoMesDia[$mes][$dia] = [];
            }
        }
        $this->conteudoMesDia[$mes][$dia][] = $conteudo;
    }

    private function loadTagAttrStructure($tagAttrName, $classAttr)
    {
        $return = '';
        if ($this->$classAttr != '') {
            $return .= " $tagAttrName='" . $this->$classAttr . "' ";
        }
        return $return;
    }
}

?>