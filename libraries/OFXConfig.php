<?php
namespace manguto\cms7\libraries;

/**
 * Definicoes e padores OFX
 *
 * @author MAGT
 *        
 */
class OFXConfig
{

    const ofx_needle_start = '<OFX>';

    const ofx_needle_end = '</OFX>';

    // ####################################################################################################
    // tipos de extrato (exibicao)
    const OFXType = [
        'CHECKING' => 'Conta Corrente',
        'SAVINGS' => 'Poupança',
        'CREDITCARD' => 'Cartão de Crédito'
    ];

    // ####################################################################################################
    // definicao dos parametros de interesse dentre os diversos
    const OFXTypeParameters = [
        'CREDITCARD' => [
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,SEVERITY', // ==========> string(4) INFO
            'DTSERVER' => 'SIGNONMSGSRSV1,SONRS,DTSERVER', // ==========> string(14) 20201012080259
            '' => 'SIGNONMSGSRSV1,SONRS,LANGUAGE', // ==========> string(3) POR
            'ORG' => 'SIGNONMSGSRSV1,SONRS,FI,ORG', // ==========> string(15) Banco do Brasil
            'FID' => 'SIGNONMSGSRSV1,SONRS,FI,FID', // ==========> string(1) 1
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,TRNUID', // ==========> string(1) 1
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,STATUS,SEVERITY', // ==========> string(4) INFO
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,CURDEF', // ==========> string(3) BRL
            'ACCTID' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,CCACCTFROM,ACCTID', // ==========> string(18) 4984000000009611
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,DTSTART', // ==========> string(8) 20191231
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,DTEND', // ==========> string(8) 20200927 //
            'BALAMT' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,LEDGERBAL,BALAMT', // ==========> string(8) -1792.51
            'DTASOF' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,LEDGERBAL,DTASOF', // ==========> string(8) 20201010

            'STMTTRN' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN', // ==========> []
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,TRNTYPE', // ==========> string(7) PAYMENT
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,DTPOSTED', // ==========> string(8) 20200921
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,TRNAMT', // ==========> string(6) -11.99
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,FITID', // ==========> string(34) 2020092149840000000096110000000022
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,MEMO', // ==========> string(39) Dropbox IE
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,CURRENCY,CURRATE', // ==========> string(6) 5.7894
            '' => 'CREDITCARDMSGSRSV1,CCSTMTTRNRS,CCSTMTRS,BANKTRANLIST,STMTTRN,y,CURRENCY,CURSYM' // ==========> string(3) USD
        ],
        'CHECKING' => [
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,SEVERITY', // ==========> string(4) INFO
            'DTSERVER' => 'SIGNONMSGSRSV1,SONRS,DTSERVER', // ==========> string(22) 20201012120000-3:BRT
            '' => 'SIGNONMSGSRSV1,SONRS,LANGUAGE', // ==========> string(3) POR
            'ORG' => 'SIGNONMSGSRSV1,SONRS,FI,ORG', // ==========> string(15) Banco do Brasil
            'FID' => 'SIGNONMSGSRSV1,SONRS,FI,FID', // ==========> string(1) 1
            '' => 'BANKMSGSRSV1,STMTTRNRS,TRNUID', // ==========> string(1) 1
            '' => 'BANKMSGSRSV1,STMTTRNRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'BANKMSGSRSV1,STMTTRNRS,STATUS,SEVERITY', // ==========> string(4) INFO
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,CURDEF', // ==========> string(3) BRL
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,BANKID', // ==========> string(1) 1
            'BRANCHID' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,BRANCHID', // ==========> string(6) KKKX-X (AGENCIA)
            'ACCTID' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,ACCTID', // ==========> string(9) KKKXX-X (CONTA-POUPANCA) //
            'ACCTTYPE' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,ACCTTYPE', // ==========> string(8) CHECKING
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,DTSTART', // ==========> string(22) 20200930120000-3:BRT
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,DTEND', // ==========> string(22) 20201013120000-3:BRT
            'BALAMT' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,LEDGERBAL,BALAMT', // ==========> string(7) 3535.70
            'DTASOF' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,LEDGERBAL,DTASOF', // ==========> string(22) 20201013120000-3:BRT

            'STMTTRN' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN', // ==========> []
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,TRNTYPE', // ==========> string(5) OTHER
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,DTPOSTED', // ==========> string(22) 20201001120000-3:BRT
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,TRNAMT', // ==========> string(7) 5555.55
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,FITID', // ==========> string(16) 20201001065KKKXX
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,CHECKNUM', // ==========> string(12) 000000262KKK
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,REFNUM', // ==========> string(7) 262.KKK
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,MEMO' // ==========> string(65) Pagamento
        ],
        'SAVINGS' => [
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'SIGNONMSGSRSV1,SONRS,STATUS,SEVERITY', // ==========> string(4) INFO
            'DTSERVER' => 'SIGNONMSGSRSV1,SONRS,DTSERVER', // ==========> string(22) 20201012120000-3:BRT
            '' => 'SIGNONMSGSRSV1,SONRS,LANGUAGE', // ==========> string(3) POR
            'ORG' => 'SIGNONMSGSRSV1,SONRS,FI,ORG', // ==========> string(15) Banco do Brasil
            'FID' => 'SIGNONMSGSRSV1,SONRS,FI,FID', // ==========> string(1) 1
            '' => 'BANKMSGSRSV1,STMTTRNRS,TRNUID', // ==========> string(1) 1
            '' => 'BANKMSGSRSV1,STMTTRNRS,STATUS,CODE', // ==========> string(1) 0
            '' => 'BANKMSGSRSV1,STMTTRNRS,STATUS,SEVERITY', // ==========> string(4) INFO
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,CURDEF', // ==========> string(3) BRL
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,BANKID', // ==========> string(1) 1
            'BRANCHID' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,BRANCHID', // ==========> string(6) KKKX-X (AGENCIA)
            'ACCTID' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,ACCTID', // ==========> string(9) KKKXX-X/51 (CONTA-POUPANCA)
            'ACCTTYPE' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKACCTFROM,ACCTTYPE', // ==========> string(7) SAVINGS
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,DTSTART', // ==========> string(14) 120000-3:BRT
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,DTEND', // ==========> string(14) 120000-3:BRT
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,DTEND', // ==========> string(14) 120000-3:BRT
            'BALAMT' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,LEDGERBAL,BALAMT', // ==========> string(4) 5.77
            'DTASOF' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,LEDGERBAL,DTASOF', // ==========> string(22) 20201012120000-3:BRT

            'STMTTRN' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN', // ==========> []
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,TRNTYPE', // ==========> string(5) OTHER
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,DTPOSTED', // ==========> string(22) 20201001120000-3:BRT
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,TRNAMT', // ==========> string(7) 5555.55
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,FITID', // ==========> string(16) 20201001065KKKXX
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,CHECKNUM', // ==========> string(12) 000000262KKK
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,REFNUM', // ==========> string(7) 262.KKK
            '' => 'BANKMSGSRSV1,STMTTRNRS,STMTRS,BANKTRANLIST,STMTTRN,x,MEMO' // ==========> string(65) Pagamento
        ]
    ];

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>