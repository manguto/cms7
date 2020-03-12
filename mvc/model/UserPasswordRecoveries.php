<?php

namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\model\Model;   
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelTrait;

class UserPasswordRecoveries extends Model
{   
    
    use ModelTrait;
    use ModelRepository;
    
    const deadline = 60*60*2; //prazo de validade da solicitacao de reset de senha (2 horas)
        
    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        // ---------------------------------------------------
        $a = new ModelAttribute('status');
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('user_id');
        $a->setType(ModelAttribute::TYPE_INT);        
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('ip');
        $a->setType(ModelAttribute::TYPE_VARCHAR);
        $a->setValue($_SERVER["REMOTE_ADDR"]);
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('datetime');
        $a->setType(ModelAttribute::TYPE_TIMESTAMP);
        $a->setValue(time());
        $this->SetAttribute($a);
        // ---------------------------------------------------
        $a = new ModelAttribute('deadline');
        $a->setType(ModelAttribute::TYPE_TIMESTAMP);
        $a->setValue((time()+self::deadline));
        $this->SetAttribute($a);
        // ---------------------------------------------------
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
    
    static function setForgotUsed($id)
    {
        $upr = new UserPasswordRecoveries($id);
        $upr->setdeadline(time());
        $upr->setStatus('used');
        $upr->save();
        // deb($upr);
    }
}

?>
