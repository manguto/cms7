<?php
namespace manguto\cms5\lib;

use OfxParser\Parser;

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

    private $filename;

    private $parser;

    private $ofx;

    private $bankAccount;

    private $bankAccountMainInfo;

    private $transactions;
    
    private $transactionsResume;

    private $transactionsMainInfo;

    const dateFormat = 'Y-m-d H:i:s';

    public function __construct($filename)
    {
        $this->filename = $filename;
        
        $this->parser = new Parser();
        // deb($this->parser);
        
        $this->ofx = $this->parser->loadFromFile($this->filename);
        // deb($this->ofx);
        
        $this->bankAccount = reset($this->ofx->bankAccounts);
        // deb($this->bankAccount);
        
        $this->bankAccountMainInfo = $this->getBankAccountMainInfo();
        deb($this->bankAccountMainInfo, 0);
        
        $this->transactions = $this->bankAccount->statement->transactions;
        // deb($this->transactions);
        
        $this->transactionsMainInfo = $this->getTransactionsMainInfo();
        deb($this->transactionsMainInfo,0);
        
        $this->transactionsResume = $this->getTransactionsResume();
        deb($this->transactionsResume);
    }

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
            $return['amount'] = sizeof($this->transactions);
        }
        return $return;
    }

    private function getTransactionsResume()
    {
        $return = [];
        {
            //deb($this->transactions);
            foreach ($this->transactions as $k=>$transaction){
                //deb($transaction);
                $return[$k]['type'] = strval($transaction->type);
                $return[$k]['uniqueId'] = strval($transaction->uniqueId);
                //$return[$k]['checkNumber'] = strval(reset($transaction->checkNumber));
                $return[$k]['date'] = strval($transaction->date->format(self::dateFormat));
                $return[$k]['memo'] = strval($transaction->memo);
                $return[$k]['amount'] = $transaction->amount;
                //$return[$k]['name'] = strval($transaction->name);
                //$return[$k]['sic'] = strval($transaction->sic);
            }
            
        }
        return $return;
    }
}

?>