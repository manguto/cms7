<?php

namespace manguto\manguto\mvc\model;

use manguto\manguto\repository\Repository;

class UserPasswordRecoveries extends Repository
{   
    const deadline = 60*60*2; //prazo de validade da solicitacao de reset de senha (2 horas)
    
    public function __construct($id=0){
        { // default values
            $this->values = [
                'status' => 'new'
            ];
        }
        parent::__construct($id);
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
    
    public static function setForgotUsed($userpasswordrecoveriesid)
    {
        $upr = new UserPasswordRecoveries($userpasswordrecoveriesid);
        $upr->setdeadline(time());
        $upr->setStatus('used');
        $upr->save();
        // deb($upr);
    }
}

?>
