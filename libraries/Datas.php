<?php
namespace manguto\cms7\libraries;

/**
 * Classe de auxilio quando do tratamento de datas/horas
 *
 * @author Marcos Torres
 *        
 */
class Datas
{

    private $format;

    private $datestr;

    private $timestamp;

    private $date;

    // const FormatoDatahora = 'Y-m-d H:i:s';
    const FormatoDatahora = 'd/m/Y H:i';

    /**
     * Cria uma data de acordo com o FORMATO e DATAHORA informados
     * e já verifica se é uma data válida!
     *
     * @param string $data
     * @param string $dateFormat
     */
    public function __construct(string $dateStr, string $dateFormat = 'd-m-Y', bool $throwException = true)
    {
        self::checkdate($dateFormat, $dateStr, $throwException);
        $this->format = $dateFormat;
        $this->datestr = $dateStr;
        $this->timestamp = self::mktime($this->format, $this->datestr);
        $this->date = $this->date_create_from_format();
    }

    private function date_create_from_format()
    {

        /**
         * d - Day of the month; with leading zeros
         * j - Day of the month; without leading zeros
         *
         * m - Month (01-12)
         * n - Month (1-12)
         *
         * Y - Year (e.g 2013)
         * y - Year (e.g 13)
         *
         * g - 12 hour format without leading zeros
         * G - 24 hour format without leading zeros
         * h - 12 hour format with leading zeros
         * H - 24 hour format with leading zeros
         *
         * i - Minutes with leading zeros
         *
         * s - Seconds with leading zeros
         *
         * @var mixed $format
         */
        $format = $this->format;
        $datestr = $this->datestr;

        self::date_create_from_format_fix($format, $datestr, [
            'Y',
            'y'
        ], '01');

        self::date_create_from_format_fix($format, $datestr, [
            'm',
            'n'
        ], '01');

        self::date_create_from_format_fix($format, $datestr, [
            'd',
            'j'
        ], '01');

        self::date_create_from_format_fix($format, $datestr, [
            'H',
            'h',
            'G',
            'g'
        ], '00');

        self::date_create_from_format_fix($format, $datestr, [
            'i'
        ], '00');

        self::date_create_from_format_fix($format, $datestr, [
            's'
        ], '00');

        return date_create_from_format($format, $datestr);
    }

    /**
     * ajuste interno dos parametros de FORMATO e de DATA(STRING)
     * para adequacao a uma data fixa inicial ao invés da atual!
     *
     * @param string $format
     * @param string $datestr
     * @param array $params
     * @param string $defaultValue
     * @return array
     */
    private static function date_create_from_format_fix(string &$format, string &$datestr, array $params, string $defaultValue)
    {
        $paramSetted = false;
        foreach ($params as $f) {
            if (strpos($format, $f) !== false) {
                $paramSetted = true;
            }
        }

        if ($paramSetted == false) {
            $format .= ' ' . array_shift($params);
            $datestr .= ' ' . $defaultValue;
        }
    }

    /**
     * Obter o timestamp
     *
     * @return float
     */
    public function getTimestamp(): float
    {
        return floatval($this->timestamp);
    }

    /**
     * Verifica se a data eh valida
     *
     * @param string $format
     * @param string $datestr
     * @param boolean $throwException
     * @throws Exception
     * @return bool
     */
    static function checkdate(string $format, string $datestr, $throwException = true): bool
    {
        $dateArray = date_parse_from_format($format, $datestr);

        $month = $dateArray['month'];
        $day = ! $dateArray['day'] ? 1 : $dateArray['day'];
        $year = $dateArray['year'];

        if (! checkdate($month, $day, $year)) {
            if ($throwException) {
                throw new Exception("Data com formato incorreto ou inexistente: $datestr.");
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Obtem o timestamp da data
     *
     * @param string $format
     * @param string $datestr
     * @return float
     */
    static function mktime(string $format, string $datestr): float
    {
        // deb($format,0);
        // deb($datestr,0);
        $dateArray = date_parse_from_format($format, $datestr);

        $hour = $dateArray['hour'];
        $minute = $dateArray['minute'];
        $second = $dateArray['second'];
        $month = $dateArray['month'];
        $day = $dateArray['day'];
        $year = $dateArray['year'];

        return floatval(mktime($hour, $minute, $second, $month, $day, $year));
    }

    /**
     * Retorna uma string com a data conforme formato informado
     *
     * @param string $datestr
     * @param string $format
     * @return string
     */
    public function getDate(string $format = 'd-m-Y'): string
    {
        $object = $this->date;
        // deb($object,0);
        // deb($format);

        if (! is_object($object)) {
            throw new \Exception('Parametro informado nao é um objeto date.');
        }
        $return = date_format($object, $format);
        return $return;
    }

    public function getDatetime()
    {
        return $this->date;
    }

    /**
     * retorna se a data representa um dia de final de semana
     *
     * @return boolean
     */
    public function itsWeekend()
    {
        return self::staticItsWeekend($this->datestr, $this->format);
    }

    /**
     * retorna a data/hora no formato solicitado
     * @param string $format
     * @return string
     */
    public function strftime(string $format):string{
        
        return strftime($format,$this->getTimestamp());
    }
    
    /**
     * Retorna o nome do dia da semana conforme o número do mesmo informado [0-6]
     *
     * @param int $dayNumber
     * @param boolean $abrev
     * @throws Exception
     * @return string
     */
    static function staticGetWeekDayName_(int $dayNumber, $fullName = false): string
    {
        if ($dayNumber < 0 || $dayNumber > 6) {
            throw new Exception("Número inadequado para um dia da semana [0-6]('$dayNumber').");
        } else {
            $code = $fullName ? '%A' : '%a';
            $return = strftime($code, strtotime("Sunday +{$dayNumber} days"));
        }
        $return = utf8_encode($return);
        return $return;
    }

    static function staticGetWeekDayName(int $dayNumber, string $size = 'P', bool $uppercase = true, bool $ucfirst = false): string
    {
        if ($dayNumber >= 0 && $dayNumber <= 6) {

            // tamanho ----------------------------------------------------------------------------------------------------
            $size = strtoupper($size);
            if ($size == 'P') {
                $return = strftime('%a', strtotime("Sunday +{$dayNumber} days"));
                $return = substr($return, 0, 1);
            } else if ($size == 'M') {
                $return = strftime('%a', strtotime("Sunday +{$dayNumber} days"));
            } else if ($size == 'G') {
                $return = strftime('%A', strtotime("Sunday +{$dayNumber} days"));
            } else {
                throw new Exception("Tamanho inadequado ('$size'). Tamanhos permitidos: P, M e G.");
            }
            // primeira - maiuscula ----------------------------------------------------------------------------------------
            if ($ucfirst) {
                $return = ucfirst($return);
            }
            // maiuscula ----------------------------------------------------------------------------------------------------
            if ($uppercase) {
                $return = strtoupper($return);
                { // fix
                    $return = str_replace('ç', 'Ç', $return);
                    $return = str_replace('á', 'Á', $return);
                }
            }
        } else {
            throw new Exception("Número inadequado para um dia da semana [0-6]('$dayNumber').");
        }
        $return = utf8_encode($return);
        return $return;
    }

    /**
     * retorna se o dia da data informada eh um final de semana
     *
     * @param string $dateStr
     * @param string $dateFormat
     * @return boolean
     */
    public static function staticItsWeekend(string $dateStr, string $dateFormat = 'd-m-Y')
    {
        $date = new Datas($dateStr, $dateFormat);
        $w = $date->getDate('w');
        if ($w == '0' || $w == '6') {
            return $date->getWeekDayName();
        } else {
            return false;
        }
    }

    /**
     * Retorna o nome do dia da semana
     *
     * @param int $dayNumber
     * @param boolean $abrev
     * @throws Exception
     * @return string
     */
    public function getWeekDayName($fullName = false): string
    {
        $dayNumber = $this->getWeekDayNumber();
        return self::staticGetWeekDayName_($dayNumber, $fullName);
    }

    /**
     * Obtem o número do dia da semana.
     * Por padrão no formato "0=>Dom,...,6=>Sab"
     * e caso sunZero==false "1=>Dom,...,7=>Sab"
     *
     * @return int
     */
    public function getWeekDayNumber($sunZero=true): int
    {
        $return = self::staticGetWeekDayNumber($this->getTimestamp());
        if($sunZero==false){
            $return++;
        }
        return $return;
    }

    /**
     * Obtem o número do dia da semana [0=>Dom,...,6=>Sab]
     *
     * @param int $timestamp
     * @return int
     */
    static function staticGetWeekDayNumber(float $timestamp): int
    {
        $format = 'd-m-Y';
        self::checkdate($format, date($format, $timestamp));

        $return = strftime('%w', $timestamp);
        $return = intval($return);
        return $return;
    }

    /**
     * Retorna o nome do dia da mes conforme o número do mesmo informado [0-6]
     *
     * @param int $dayNumber
     * @param boolean $abrev
     * @throws Exception
     * @return string
     */
    static function getMonthName(int $monthNumber, $fullName = true): string
    {
        if ($monthNumber < 1 || $monthNumber > 12) {
            throw new Exception("Número inadequado para um mês ('$monthNumber').");
        }
        $code = $fullName ? '%B' : '%b';
        $return = strftime($code, self::mktime('Y-m-d', "2018-$monthNumber-01"));
        return utf8_encode($return);
    }

    /**
     * Retorna o nome do mes conforme o número informado [1-12]
     *
     * @param int $monthNumber
     *            [1-12]
     * @param string $size
     *            [P,M,G]
     * @param bool $uppercase
     *            - todo em maiusculo?
     * @param bool $ucfirst
     *            - apenas a primeira letra em maiusculo?
     * @throws Exception
     * @return string
     */
    static function static_GetMonthName(int $monthNumber, string $size = 'P', bool $uppercase = true, bool $ucfirst = false): string
    {
        if ($monthNumber >= 1 && $monthNumber <= 12) {

            // tamanho ----------------------------------------------------------------------------------------------------
            $size = strtolower($size);
            if ($size == 'p') {
                $return = strftime('%b', self::mktime('Y-m-d', "2018-$monthNumber-01"));
                $return = substr($return, 0, 1);
            } else if ($size == 'm') {
                $return = strftime('%b', self::mktime('Y-m-d', "2018-$monthNumber-01"));
            } else if ($size == 'g') {
                $return = strftime('%B', self::mktime('Y-m-d', "2018-$monthNumber-01"));
            } else {
                throw new Exception("Tamanho inadequado ('$size'). Tamanhos permitidos: P, M e G.");
            }
            // primeira - maiuscula ----------------------------------------------------------------------------------------
            if ($ucfirst) {
                $return = ucfirst($return);
            }
            // maiuscula ----------------------------------------------------------------------------------------------------
            if ($uppercase) {
                $return = strtoupper($return);
            }
        } else {
            throw new Exception("Número inadequado para um mês ('$monthNumber').");
        }

        return $return;
    }

    static function getMonthNumberOfDays($year, $month)
    {
        $dateFormat = 'Y-m-d';
        $dateStr = "$year-$month-01";
        // deb($dateStr,0);
        return date('t', self::mktime($dateFormat, $dateStr));
    }

    static function getWeekTimestampStartEnd($dateStr, $dateFormat)
    {
        $d = new Datas($dateStr, $dateFormat);

        if ($d->getWeekDayNumber() == 0) {
            $timestampSemanaInicio = strtotime('today', $d->getTimestamp());
            // deb(date('d-m-Y H:i:s',$timestampSemanaInicio));
            $timestampSemanaFim = strtotime('next saturday', $d->getTimestamp()) + (24 * 60 * 60) - (1);
            // deb(date('d-m-Y H:i:s',$timestampSemanaFim));
        } else if ($d->getWeekDayNumber() == 6) {
            $timestampSemanaInicio = strtotime('last sunday', $d->getTimestamp());
            // deb(date('d-m-Y H:i:s',$timestampSemanaInicio));
            $timestampSemanaFim = strtotime('today', $d->getTimestamp()) + (24 * 60 * 60) - (1);
            // deb(date('d-m-Y H:i:s',$timestampSemanaFim));
        } else {
            $timestampSemanaInicio = strtotime('last sunday', $d->getTimestamp());
            // deb(date('d-m-Y H:i:s',$timestampSemanaInicio));
            $timestampSemanaFim = strtotime('next saturday', $d->getTimestamp()) + (24 * 60 * 60) - (1);
            // deb(date('d-m-Y H:i:s',$timestampSemanaFim));
        }

        return [
            $timestampSemanaInicio,
            $timestampSemanaFim
        ];
    }

    /**
     * increase or decrease some date according to the arguments informed
     *
     * @param int $quantity
     * @param string $unit
     *            (D-days|M-months|Y-years)
     */
    public function Operation(int $quantity, $unit = 'M')
    {
        {
            {
                $interval_spec = 'P' . abs($quantity) . strtoupper($unit);
            }
            $dateInterval = new \DateInterval($interval_spec);

            // inverte a referencia do interval
            if ($quantity < 0) {
                $dateInterval->invert = 1;
            }
        }
        $this->date->add($dateInterval);
        $this->datestr = $this->getDate($this->format);
        $this->timestamp = self::mktime($this->format, $this->datestr);
    }

    /**
     * obtem o intervalo entre duas datas
     * @param Datas $data1
     * @param Datas $data2
     * @return \DateInterval
     */
    static function getDateDifference(Datas $data1, Datas $data2):\DateInterval
    {
        $datetime1 = $data1->getDatetime();
        $datetime2 = $data2->getDatetime();
        $diff = $datetime2->diff($datetime1);
        // deb($diff);
        return $diff;
    }

    static function getDateDifferenceShow(Datas $data1, Datas $data2)
    {
        $return = [];
        {
            $diff = self::getDateDifference($data1, $data2);
            {
                $y = $diff->y;
                $m = $diff->m;
                $d = $diff->d;
            }
        }
        {
            if ($y > 0) {
                if ($y == 1) {
                    $return[] = "1 ano";
                } else {
                    $return[] = "$y anos";
                }
            }
            if ($m > 0) {
                if (sizeof($return) != 0) {
                    $return[] = ", ";
                }
                if ($m == 1) {
                    $return[] = "1 mês";
                } else {
                    $return[] = "$m meses";
                }
            }
            if ($d > 0) {
                if (sizeof($return) != 0) {
                    $return[] = " e ";
                }

                if ($d == 1) {
                    $return[] = "1 dia";
                } else {
                    $return[] = "$d dias";
                }
            }
        }
        return implode('', $return);
    }

    /**
     * retorna a quant.
     * de itens conforme descrito no intervalo (anos, meses, etc.)
     *
     * @param string $interval
     * @param string|int $date_from
     * @param string|int $date_to
     * @param bool $using_timestamps
     * @return number
     */
    static function datediff(string $interval, $date_from, $date_to, bool $using_timestamps = true)
    {
        /*
         * $interval can be:
         * yyyy - Number of full years
         * q - Number of full quarters
         * m - Number of full months
         * y - Difference between day numbers
         * (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
         * d - Number of full days
         * w - Number of full weekdays
         * ww - Number of full weeks
         * h - Number of full hours
         * n - Number of full minutes
         * s - Number of full seconds (default)
         */
        if (! $using_timestamps) {
            $date_from = strtotime($date_from, 0);
            $date_to = strtotime($date_to, 0);
        }

        $difference = $date_to - $date_from; // Difference in seconds
        $months_difference = 0;

        switch ($interval) {
            case 'yyyy': // Number of full years
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $date_from), date("i", $date_from), date("s", $date_from), date("n", $date_from), date("j", $date_from), date("Y", $date_from) + $years_difference) > $date_to) {
                    $years_difference --;
                }

                if (mktime(date("H", $date_to), date("i", $date_to), date("s", $date_to), date("n", $date_to), date("j", $date_to), date("Y", $date_to) - ($years_difference + 1)) > $date_from) {
                    $years_difference ++;
                }

                $datediff = $years_difference;
                break;

            case "q": // Number of full quarters
                $quarters_difference = floor($difference / 8035200);

                while (mktime(date("H", $date_from), date("i", $date_from), date("s", $date_from), date("n", $date_from) + ($quarters_difference * 3), date("j", $date_to), date("Y", $date_from)) < $date_to) {
                    $months_difference ++;
                }

                $quarters_difference --;
                $datediff = $quarters_difference;
                break;

            case "m": // Number of full months
                $months_difference = floor($difference / 2678400);

                while (mktime(date("H", $date_from), date("i", $date_from), date("s", $date_from), date("n", $date_from) + ($months_difference), date("j", $date_to), date("Y", $date_from)) < $date_to) {
                    $months_difference ++;
                }

                $months_difference --;

                $datediff = $months_difference;
                break;

            case 'y': // Difference between day numbers
                $datediff = date("z", $date_to) - date("z", $date_from);
                break;

            case "d": // Number of full days
                $datediff = floor($difference / 86400);
                break;

            case "w": // Number of full weekdays
                $days_difference = floor($difference / 86400);
                $weeks_difference = floor($days_difference / 7); // Complete weeks
                $first_day = date("w", $date_from);
                $days_remainder = floor($days_difference % 7);
                $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

                if ($odd_days > 7) { // Sunday
                    $days_remainder --;
                }

                if ($odd_days > 6) { // Saturday
                    $days_remainder --;
                }

                $datediff = ($weeks_difference * 5) + $days_remainder;
                break;

            case "ww": // Number of full weeks
                $datediff = floor($difference / 604800);
                break;

            case "h": // Number of full hours
                $datediff = floor($difference / 3600);
                break;

            case "n": // Number of full minutes
                $datediff = floor($difference / 60);
                break;

            default: // Number of full seconds (default)
                $datediff = $difference;
                break;
        }

        return $datediff;
    }

    /**
     * obtencao das semanas uteis (inicio e fim) entre duas datas
     *
     * @param string|int $date_ini
     * @param string|int $date_end
     * @param bool $dates_timestamp_format
     * @return array
     */
    static function getWeeksInfoBetweenDates($date_ini, $date_end, bool $dates_timestamp_format = false): array
    {
        $return = [];

        { // verificacao ajuste do formato dos dados
            if ($dates_timestamp_format) {
                $date_ini_str = date('Y-m-d', $date_ini);
                $date_end_str = date('Y-m-d', $date_end);
            } else {
                $date_ini_str = $date_ini;
                $date_end_str = $date_end;
            }
        }
        { // criacao de objetos para tratamento
            $date_ini = new \DateTime($date_ini_str);
            { // shift ate uma segunda-feira
                while ($date_ini->format('w') != '1') {
                    // deb($date_ini->format('d/m/Y [w/l]'),0);
                    $date_ini->add(new \DateInterval('P1D'));
                }
                // deb($date_ini->format('d/m/Y [w/l]'), 0);
            }
            $date_end = new \DateTime($date_end_str);
            { // shift ate uma segunda-feira
                while ($date_end->format('w') != '5' && (floatval($date_ini->format('Ymd')) < floatval($date_end->format('Ymd')))) {
                    // deb($date_ini->format('d/m/Y [w/l]'),0);
                    $date_end->sub(new \DateInterval('P1D'));
                }
                // deb($date_end->format('d/m/Y [w/l]'), 0);
            }
        }
        // retorna um array vazio caso o inicio e o fim nao compreendam uma semana (seg-sex)
        if ($date_ini == $date_end) {
            return $return;
        }
        // --------------------------------------------------------------------------------------------------------------
        {
            { // calculo semanas iniciais
                $week_ini = new \DateTime($date_ini->format('Y-m-d'));
                $week_end = new \DateTime($date_ini->format('Y-m-d'));
                // shift de 5 dias (sexta-feira)
                $week_end->add(new \DateInterval('P4D'));
            }

            while (floatval($week_end->format('Ymd')) <= floatval($date_end->format('Ymd'))) {

                $return[] = [
                    $week_ini->format('Y-m-d'),
                    $week_end->format('Y-m-d')
                ];
                { // shift 7 dias
                    $week_ini->add(new \DateInterval('P7D'));
                    $week_end->add(new \DateInterval('P7D'));
                }
            }

            return $return;
        }
    }

    /**
     * obtem o microtempo atual
     *
     * @return string
     */
    static function getMicrotime(): string
    {
        return round(microtime(true) * 1000);
    }

    /**
     * obtem um array com os dias da semana
     * @param string $size
     * @param bool $uppercase
     * @param bool $ucfirst
     * @return array
     */
    static function getWeekdays(string $size = 'p', bool $uppercase = true, bool $ucfirst = false): array
    {
        $return = [];
        for ($i = 0; $i < 7; $i ++) {
            $return[$i] = self::staticGetWeekDayName($i, $size, $uppercase, $ucfirst);
        }
        return $return;
    }
    
    
    // ####################################################################################################
    static function getDatesBetweenDates($date_ini, $date_end,$format='Y-m-d'): array
    {
        $return = [];
        $date_ini_o = new Datas($date_ini,$format);
        $date_tmp_o = new Datas($date_ini,$format);
        $date_end_o = new Datas($date_end,$format);        
        
        $dif = self::getDateDifference($date_ini_o, $date_end_o);
        if($dif->invert==0){
            $msg = "A ordenação das datas informadas está incorreta (Início ($date_ini) > Fim ($date_end)).";            
            throw new Exception($msg);
        }
        
        $n = $dif->days;
        //debc($n,0);
        if($n>0){
            $return[] = $date_ini_o;
            for ($i = 0; $i < $n; $i++) {
                $date_tmp_o->Operation(1,'D');
                $return[] = new Datas($date_tmp_o->getDate($format),$format);
            }
        }
        //deb("$date_ini - $date_end [$n]",0); deb($return);
        return $return;
    }
    
    // ####################################################################################################
    public function __toString() {
    	return $this->getDate('d/m/Y');
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    
}

/**
 * =====================================================================================================================================
 * =====================================================================================================================================
 * =====================================================================================================================================
 * =====================================================================================================================================
 * Formatos:
 * d - Day of the month; with leading zeros
 * j - Day of the month; without leading zeros
 * D - Day of the month (Mon - Sun)
 * l - Day of the month (Monday - Sunday)
 * S - English suffix for day of the month (st, nd, rd, th)
 * F - Monthname (January - December)
 * M - Monthname (Jan-Dec)
 * m - Month (01-12)
 * n - Month (1-12)
 * Y - Year (e.g 2013)
 * y - Year (e.g 13)
 * a and A - am or pm
 * g - 12 hour format without leading zeros
 * G - 24 hour format without leading zeros
 * h - 12 hour format with leading zeros
 * H - 24 hour format with leading zeros
 * i - Minutes with leading zeros
 * s - Seconds with leading zeros
 * u - Microseconds (up to six digits)
 * e, O, P and T - Timezone id
 * U - Seconds since Unix Epoch
 * (space)
 * # - One of the following separation symbol: ;,:,/,.,,,-,(,)
 * ? - A random byte
 * - Rondom bytes until next separator/digit
 * ! - Resets all fields to Unix Epoch
 * | - Resets all fields to Unix Epoch if they have not been parsed yet
 * + - If present, trailing data in the string will cause a warning, not an error
 *
 * =====================================================================================================================================
 * Formatos da funcao strftime():
 *
 * %a Representação textual abreviada do dia Sun até Sat
 * %A Representação textual completa do dia Sunday até Saturday
 * %d Dia do mês, com dois dígitos (com zeros à esquerda) 01 a 31
 * %e Dia do mês com um dígito, precedido com um espaço. Não implementado como descrito no Windows. Veja abaixo para mais informações. 1 a 31
 * %j Dia do ano, com 3 dígitos e zeros à esquerda 001 a 366
 * %u Representação numérica, do dia da semana, compatível com a ISO-8601 1 (para Monday) até 7 (para Sunday)
 * %w Representação numérica do dia da semana 0 (para Sunday) até 6 (para Saturday)
 * Semana --- ---
 * %U Número da semana de um dado ano, iniciado com o primeiro Domingo sendo a primeira semana 13 (como a 13ª semana completa do ano)
 * %V Número da semana, compatível com a ISO-8601:1988 de um dado ano, iniciada com a primeira semana do ano com pelo menos 4 finais de semana, sendo a Segunda-feira como o início da semana. 01 até 53 (onde 53 é responsável por uma sobreposição)
 * %W Representação numérica da semana do ano, começando pela primeira Segunda-feira como primeira semana 46 (como a 46ª semana do do ano iniciando na Segunda-feira)
 * Mês --- ---
 * %b Nome do mês abreviado, baseado no idioma Jan até Dez
 * %B Nome completo do mês, baseado no idioma Janeiro até Dezembro
 * %h Nome do mês abreviado, baseado no idioma (sinônimo de %b) Jan até Dez
 * %m Representação com dois dígitos do mês 01 (para January) through 12 (para December)
 * Ano --- ---
 * %C Representação, com dois dígitos, do século (ano dividido por 100, truncado como inteiro) 19 para o século 20
 * %g Representação do ano, com dois dígitos, seguindo o padrão ISO-8601:1988 (veja %V) Exemplo: 09 de January 6, 2009
 * %G Versão de 4 dígitos de %g Exemplo: 2008 de January 3, 2009
 * %y Representação, com dois dígitos, do ano Exemplo: 09 de 2009, 79 de 1979
 * %Y Representação, com quatro dígitos, do ano Exemplo: 2038
 * Hora --- ---
 * %H Representação, com dois dígitos, da hora no formato 24 horas 00 até 23
 * %k Representação, com dois dígitos, da hora no formato 24 horas, com um espaço precedendo aqueles com somente um dígito 0 até 23
 * %I Representação, com dois dígitos, da hora no formato 12 horas 01 até 12
 * %l ('L' minúsculo) Representação, com dois dígitos, da hora no formato 12 horas, com um espaço precedendo aqueles com somente um dígito 1 até 12
 * %M Representação, com dois dígitos, do minuto 00 até 59
 * %p 'AM' ou 'PM' maiúsculo baseado na hora informada Exemplo: AM para 00:31, PM para 22:23
 * %P 'am' ou 'pm' maiúsculo baseado na hora informada Exemplo: am para 00:31, pm para 22:23
 * %r O mesmo que "%I:%M:%S %p" Exemplo: 09:34:17 PM de 21:34:17
 * %R O mesmo que "%H:%M" Exemplo: 00:35 de 12:35 AM, 16:44 de 4:44 PM
 * %S Representação, com dois dígitos, do segundo 00 até 59
 * %T O mesmo que "%H:%M:%S" Exemplo: 21:34:17 para 09:34:17 PM
 * %X Representação escolhida baseada no idioma, sem a data Exemplo: 03:59:16 ou 15:59:16
 * %z O deslocamento do fuso horário. Não implementado como descrito no Windows. Veja mais informações a seguir. Exemplo: -0500 para hora à leste dos EUA
 * %Z A abreviação do fuso horário. Não implementado como descrito no Windows. Veja mais informações a seguir. Exemplo: EST para Eastern Time
 * Carimbos de Data e Hora --- ---
 * %c Carimbo escolhido de data e hora baseado no idioma Exemplo: Ter Fev 5 00:45:10 2009 de February 5, 2009 at 12:45:10 AM
 * %D O mesmo que "%m/%d/%y" Exemplo: 02/05/09 de February 5, 2009
 * %F O mesmo que "%Y-%m-%d" (geralmente utilizado em carimbos de data em bancos de dados) Exemplo: 2009-02-05 de February 5, 2009
 * %s Timestamp Unix Epoch Time (o mesmo que a função time() function) Exemplo: 305815200 de September 10, 1979 08:40:00 AM
 * %x Carimbo escolhido de data e hora baseado no idioma, sem a hora Exemplo: 02/05/09 de February 5, 2009
 * Miscelânea --- ---
 * %n Uma nova linha ("\n") ---
 * %t Um caractere TAB ("\t") ---
 * %% Um caractere literal de porcento ("%") ---
 */

/**
 * DATETIME PHP 5.0 Functions Descriptions (http://www.w3schools.com/php/php_ref_date.asp)
 * checkdate() Validates a Gregorian date
 * date_add() Adds days, months, years, hours, minutes, and seconds to a date
 * date_create_from_format() Returns a new DateTime object formatted according to a specified format
 * date_create() Returns a new DateTime object
 * date_date_set() Sets a new date
 * date_default_timezone_get() Returns the default timezone used by all date/time functions
 * date_default_timezone_set() Sets the default timezone used by all date/time functions
 * date_diff() Returns the difference between two dates
 * date_format() Returns a date formatted according to a specified format
 * date_get_last_errors() Returns the warnings/errors found in a date string
 * date_interval_create_from_date_string() Sets up a DateInterval from the relative parts of the string
 * date_interval_format() Formats the interval
 * date_isodate_set() Sets the ISO date
 * date_modify() Modifies the timestamp
 * date_offset_get() Returns the timezone offset
 * date_parse_from_format() Returns an associative array with detailed info about a specified date, according to a specified format
 * date_parse() Returns an associative array with detailed info about a specified date
 * date_sub() Subtracts days, months, years, hours, minutes, and seconds from a date
 * date_sun_info() Returns an array containing info about sunset/sunrise and twilight begin/end, for a specified day and location
 * date_sunrise() Returns the sunrise time for a specified day and location
 * date_sunset() Returns the sunset time for a specified day and location
 * date_time_set() Sets the time
 * date_timestamp_get() Returns the Unix timestamp
 * date_timestamp_set() Sets the date and time based on a Unix timestamp
 * date_timezone_get() Returns the time zone of the given DateTime object
 * date_timezone_set() Sets the time zone for the DateTime object
 * date() Formats a local date and time
 * getdate() Returns date/time information of a timestamp or the current local date/time
 * gettimeofday() Returns the current time
 * gmdate() Formats a GMT/UTC date and time
 * gmmktime() Returns the Unix timestamp for a GMT date
 * gmstrftime() Formats a GMT/UTC date and time according to locale settings
 * idate() Formats a local time/date as integer
 * localtime() Returns the local time
 * microtime() Returns the current Unix timestamp with microseconds
 * mktime() Returns the Unix timestamp for a date
 * strftime() Formats a local time and/or date according to locale settings
 * strptime() Parses a time/date generated with strftime()
 * strtotime() Parses an English textual datetime into a Unix timestamp
 * time() Returns the current time as a Unix timestamp
 * timezone_abbreviations_list() Returns an associative array containing dst, offset, and the timezone name
 * timezone_ids_list() Returns an indexed array with all timezone ids
 * timezone_location_get() Returns location information for a specified timezone
 * timezone_name_from_ abbr() Returns the timezone name from abbreviation
 * timezone_name_get() Returns the name of the timezone
 * timezone_offset_get() Returns the timezone offset from GMT
 * timezone_open() Creates new DateTimeZone object
 * timezone_transitions_get() Returns all transitions for the timezone
 * timezone_version_get() Returns the version of the timezone db
 * PHP 5 Predefined Date/Time Constants
 * Constant Description
 * DATE_ATOM Atom (example: 2005-08-15T16:13:03+0000)
 * DATE_COOKIE HTTP Cookies (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_ISO8601 ISO-8601 (example: 2005-08-14T16:13:03+0000)
 * DATE_RFC822 RFC 822 (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_RFC850 RFC 850 (example: Sunday, 14-Aug-05 16:13:03 UTC)
 * DATE_RFC1036 RFC 1036 (example: Sunday, 14-Aug-05 16:13:03 UTC)
 * DATE_RFC1123 RFC 1123 (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_RFC2822 RFC 2822 (Sun, 14 Aug 2005 16:13:03 +0000)
 * DATE_RSS RSS (Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_W3C World Wide Web Consortium (example: 2005-08-14T16:13:03+0000)
 */

?>