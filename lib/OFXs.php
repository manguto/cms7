<?php
namespace manguto\cms7\lib;

/**
 * Classe de suporte ao tratamento de arquivos OFX
 * http://www.ofx.net/downloads/OFX%202.2.pdf
 * http://cdn.cdata.com/help/RZA/ado/RSBOFX_p_AccountId.htm
 *
 * @author Marcos Torres
 *        
 */
class OFXs
{

    public $filename;

    public $parser;

    public $ofx;

    public $bankAccount;

    public $bankAccountMainInfo;

    private $transactions;

    public $transactionsResume;

    public $transactionsMainInfo;

    public $xml_info;

    public $xml;

    public $internacionalTransactionInfo = [];

    const dateFormat = 'Y-m-d H:i:s';

    public function __construct($filename)
    {
        $this->filename = $filename;

        //$this->parser = new Parser();
        $this->parser = new \stdClass();
        // deb($this->parser);

        $this->ofx = $this->parser->loadFromFile($this->filename);
        // deb($this->ofx);

        $this->bankAccount = reset($this->ofx->bankAccounts);
        // deb($this->bankAccount);

        $this->bankAccountMainInfo = $this->getBankAccountMainInfo();
        // deb($this->bankAccountMainInfo, 0);

        $this->transactions = $this->bankAccount->statement->transactions;
        // deb($this->transactions);

        $this->transactionsMainInfo = $this->getTransactionsMainInfo();
        // deb($this->transactionsMainInfo,0);

        /**
         * o procedimento abaixo 
         * sera realizado apenas 
         * para o cartao de credito
         */
        if($this->bankAccountMainInfo['accountType']==''){
            
            $this->loadFileBaseXMLStructure($filename);
            // deb($this->xml_info,0);
            // deb($this->xml);
            
            $this->loadInternacionalTransactionInfo();
            //deb($this->internacionalTransactionInfo,0);
        }
        
        
        
        $this->transactionsResume = $this->getTransactionsResume();
        // deb($this->transactionsResume);
    }

    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    
    private function loadFileBaseXMLStructure($filename)
    {
        {
            $xml_file_content = Arquivos::obterConteudo($filename);
            $ofx_tag_position = strpos($xml_file_content, '<OFX>');
            $xml_info = substr($xml_file_content, 0, $ofx_tag_position);
            $xml_str = trim(substr($xml_file_content, $ofx_tag_position));
            $xml = simplexml_load_string($xml_str);
        }
        $this->xml_info = $xml_info;
        $this->xml = $xml;
    }

    private function loadInternacionalTransactionInfo()
    {
        $transactions = $this->xml->CREDITCARDMSGSRSV1->CCSTMTTRNRS->CCSTMTRS->BANKTRANLIST->STMTTRN;

        foreach ($transactions as $transaction) {
            //deb($transaction,0);

            if (isset($transaction->CURRENCY)) {
                {
                    $FITID = $transaction->FITID;
                    $FITID = strval($FITID);
                    // deb($FITID,0);
                    $CURRATE = $transaction->CURRENCY->CURRATE;
                    $CURRATE = floatval($CURRATE);
                    // deb($CURRATE,0);
                }
                //deb("$FITID $CURRATE",0);
                $this->internacionalTransactionInfo[$FITID] = $CURRATE;
            }
        }
    }

    private function getAmountCurrencyAdjusted($uniqueid, $amount)
    {
        //deb($this->internacionalTransactionInfo,0);
        if (isset($this->internacionalTransactionInfo[$uniqueid])) {            
            $currate = $this->internacionalTransactionInfo[$uniqueid];
        } else {
            $currate = 1;
        }        
        return round($amount * $currate, 2);
    }

    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    private function getBankAccountMainInfo()
    {
        $return = [];
        {
            // deb($this->bankAccount,0);
            $return['accountType'] = strval($this->bankAccount->accountType);
            $return['accountNumber'] = strval(trim($this->bankAccount->accountNumber));
            $return['agencyNumber'] = strval($this->bankAccount->agencyNumber);
            $return['routingNumber'] = strval($this->bankAccount->routingNumber);
            $return['transactionUid'] = strval($this->bankAccount->transactionUid);
            $return['balanceDate'] = strval($this->bankAccount->balanceDate->format(self::dateFormat));
        }
        return $return;
    }

    private function getTransactionsMainInfo()
    {
        $return = [];
        {
            // deb($this->bankAccount->statement,0);
            $return['currency'] = strval($this->bankAccount->statement->currency);
            $return['startDate'] = strval($this->bankAccount->statement->startDate->format(self::dateFormat));
            $return['endDate'] = strval($this->bankAccount->statement->endDate->format(self::dateFormat));
            $return['length'] = sizeof($this->transactions);
        }
        return $return;
    }

    private function getTransactionsResume()
    {
        $return = [];
        {
            // deb($this->transactions);
            foreach ($this->transactions as $k => $transaction) {
                // deb($transaction,0);
                {
                    $uniqueid = strval($transaction->uniqueId);
                    $amount = $transaction->amount;
                }
                $return[$k]['type'] = strval($transaction->type);
                $return[$k]['uniqueId'] = $uniqueid;
                // $return[$k]['checkNumber'] = strval(reset($transaction->checkNumber));
                $return[$k]['date'] = strval($transaction->date->format(self::dateFormat));
                $return[$k]['memo'] = strval($transaction->memo);
                {
                    $newAmount = $this->getAmountCurrencyAdjusted($uniqueid, $amount);                    
                }
                $return[$k]['amount'] = $newAmount;
                // $return[$k]['name'] = strval($transaction->name);
                // $return[$k]['sic'] = strval($transaction->sic);
            }
        }
        return $return;
    }
}

?>