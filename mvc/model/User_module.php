<?php
namespace manguto\cms5\mvc\model;

use manguto\cms5\lib\model\Model;
use manguto\cms5\lib\database\ModelDatabase;
use manguto\cms5\lib\database\repository\ModelRepository;
use manguto\cms5\lib\model\ModelAttribute;
use manguto\cms5\lib\model\ModelTrait;
use manguto\cms5\lib\Sessions;

class User_module extends Model implements ModelDatabase
{

    use ModelTrait;
    use ModelRepository;

    /**
     * Função para definicao do atributos do modelo (ModelAttribute's)
     */
    private function defineAttributes()
    {
        // -------------------------------------------------
        $a = new ModelAttribute('user_id');
        $a->setType(ModelAttribute::TYPE_INT);
        $this->SetAttribute($a);
        // -------------------------------------------------
        $a = new ModelAttribute('module');
        $this->SetAttribute($a);
        // -------------------------------------------------
        $a = new ModelAttribute('nature');
        $this->SetAttribute($a);
        // -------------------------------------------------
    }

    //=================================================================================
    
    static function obterUsuarioModulosPermitidos($user_id):array{
        $user_modules = (new self())->search(" \$user_id==$user_id ");
        $return = [];
        foreach ($user_modules as $user_module){
            $return[] = $user_module->getModule();
        }
        {//adicionar o proprio sistema em questao   
            $return[] = SIS_FOLDERNAME;
        }
        return $return; 
    }
    //=================================================================================
    static function obterUsuarioHomeModulosMenu(){
        $return = [];
        
        if(Sessions::isset(User::SESSION.'_modules')){            
            foreach (Sessions::get(User::SESSION.'_modules') as $module){                
                if($module!=SIS_FOLDERNAME){
                    if(sizeof($return)==0){
                        $return[] = "<h3 style='font-style: italic;'>Outros módulos disponíveis para este usuário:</h3>";
                    }
                    $return[] = "<hr />
                    <a href='http://nti.uast.br/".$module."' class='btn btn-outline-success'>".strtoupper($module)."</a>";
                }
            }
        }
        
        $return=implode(chr(10), $return);
        //debc($return,0);        
        return $return;
    }
    
}











?>