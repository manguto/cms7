<?php

namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\database\sql\ModelSql;
use manguto\cms5\lib\model\ModelAttribute;

class UserPasswordRecoveries extends ModelSql
{   
    const deadline = 60*60*2; //prazo de validade da solicitacao de reset de senha (2 horas)
    
        
    public function __construct($id = 0)
    {
        // definicao dos atributos deste modelo
        $this->DefineAttributes();
        
        // construct
        parent::__construct($id);
    }
    
    // definicao dos atributos deste modelo
    private function DefineAttributes()
    {
        $attributes = [
            'status' => [
                'type' => ModelAttribute::TYPE_VARCHAR,
                'value' => 'new',
                'length' => 16
            ],
            'deadline' => [
                'type' => ModelAttribute::TYPE_TIMESTAMP,
                'value' => (time()+self::deadline)                
            ]
        ];
        
        $this->SetAttributes($attributes);
    }
    
    public function DeadlineValid(){
        $timestampNow = (int) time();
        $timestampDeadline = (int) $this->getdeadline();
        if($timestampNow > $timestampDeadline){
            return false;
        }else{
            return true;
        }
    }
    
    public static function setForgotUsed($id)
    {
        $upr = new UserPasswordRecoveries($id);
        $upr->setdeadline(time());
        $upr->setStatus('used');
        $upr->save();
        // deb($upr);
    }
}

?>
