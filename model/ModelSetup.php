<?php
namespace manguto\cms7\model;

use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\Alert;

trait ModelSetup
{

    public function __construct($id = 0)
    {
    	//intval
    	$id = intval($id);
    	
        // atributos basicos (fundamentais)
        $this->SetFundamentalAttributes($id);
        // deb($this);

        // definicao dos atributos deste modelo
        $this->defineAttributes();
        // deb($this);

        // carregamento de atributos do banco de dados
        if ($id != 0) {
            $this->load();
            // deb($this);
        }
        // verifica corretude da estrutura dos dados
        parent::checkSetStruct();
    }

    /**
     * inicializa o repositorio caso defindos os registros base (constante 'default').
     *
     * @throws Exception
     */
    static function initialize()
    {
        Alert::Warning("Inicialização do modelo '" . __CLASS__ . "' iniciado.");
        $n = (new self())->length();
        if ($n == 0) {
            Alert::Warning("Repositório '" . __CLASS__ . "' vazio. Processo de inserção de registros base iniciada.");
            if (defined('self::default') && sizeof(self::default) > 0) {
                $default = [];
                Alert::Warning(sizeof(self::default) . " Registro(s) base encontrado(s).");
                foreach (self::default as $register) {
                    $default[] = $register;
                }
                foreach ($default as $k => $register) {
                    $new = new self();
                    if ($register !== false) {
                        $new->SET_DATA($register);
                    }
                    $new->save();
                    Alert::Success("Registro base Nº " . ($k + 1) . " inserido com sucesso (ID: {$new->getId()}).");
                }
            } else {
                Alert::Success("Nenhum registro base definido.");
            }
        } else {
            Alert::Warning("Repositório '" . __CLASS__ . "' NÃO se encontra vazio. Inicialização NÃO realizada!");
        }
    }

    /**
     * Verificacao da integridade do objeto (estrutura dos dados)
     *
     * @param boolean $throwException
     * @throws Exception
     * @return boolean
     */
    public function CheckDataIntegrity($throwException = true)
    {
        $errors = [];
        if ($this->getId() != 0) {
            {
                // ...
            }
        }
        if (sizeof($errors) > 0) {
            if ($throwException) {
                throw new Exception(implode('<hr/>', $errors));
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}

?>