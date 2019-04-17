<?php
namespace manguto\manguto\lib;

/**
 * Classe de auxilio quando do tratamento de moedas
 *
 * @author Marcos Torres
 *        
 */
class Moedas
{

    /**
     * Formata um valor para uma determinada MOEDA (real, dolar)
     *
     * @param string $tipo
     * @param $valor
     * @param int $decimals
     * @param string $decimalpoint
     * @param string $separator
     * @return string
     */
    static function formatar(string $tipo, $valor, int $decimals = 2, string $decimalpoint = ',', string $separator = '.')
    {
        $valor = self::normalizar($valor, $decimals, $decimalpoint, $separator);
        if ($tipo == 'real') {
            $valor = 'R$ ' . $valor;
        } else if ($tipo == 'dolar') {
            $valor = '$ ' . $valor;
        } else {
            $valor = '# ' . $valor;
        }
        return $valor;
    }

    /**
     * Normaliza um vamor para o formato de moeda
     * @param $valor
     * @param int $decimals
     * @param string $decimalpoint
     * @param string $separator
     * @return string
     */
    static function normalizar($valor, int $decimals = 2, string $decimalpoint = ',', string $separator = '.')
    {
        $valor = str_replace(',', '.', $valor);
        $valor = floatval($valor);
        $valor = number_format($valor, $decimals, $decimalpoint, $separator);
        return $valor;
    }
}
?>