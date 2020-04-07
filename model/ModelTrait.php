<?php
namespace manguto\cms7\lib\model;

use manguto\cms7\lib\Exception;

trait ModelTrait
{

    public function __construct($id = 0)
    {
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

    public function VerifyDataAndStructure($throwException = true)
    {
        if ($this->getId() != 0) {

            // ==================================================================================
            { // PARAMETERS
              // $xxx = $this->getXxx();
              // ...
            }
            // =================================================================================
            { // CHECK DATA!
                $exceptions = [];
                { // ANALYSIS
                    //-----------------------------------------------------------------
                    //if ($xxx=='') {
                    //    $exceptions[] = "... ($this).";
                    //}
                    
                    // ...
                }
                { // RETURN
                    if (sizeof($exceptions) > 0) {
                        if ($throwException) {
                            throw new Exception(implode(' | ', $exceptions));
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                }
            }
            // ==================================================================================
            { // SET DATA!
              // -----------------------------------------------------------------
              //if ($xxx == 'yyy') {
              //    $this->setXxx('www');
              //    $this->save();
              //}
                
            }
        }
    }
}

?>