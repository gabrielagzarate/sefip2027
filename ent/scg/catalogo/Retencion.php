<?php
require_once dirname(__FILE__)."/../../Entidad.php";

class Retencion extends Entidad
{
    /******************************************************
        ATRIBUTOS DE LA CLASE
    ******************************************************/
    
    protected $ger_id;	//
    protected $cgp_id;	//
    protected $rub_id;	//
    protected $cgcu_id;	//
    protected $cgsc_id;	//
    protected $cgc_id;	//
    protected $cta_id;	//nivel de registro
    protected $cta_nombre;	//
    protected $usr_id;	//
    
    /******************************************************
        METODOS GET
    ******************************************************/
    
    public function getGer_id() { return $this->ger_id->getVal(); }
    public function getCgp_id() { return $this->cgp_id->getVal(); }
    public function getRub_id() { return $this->rub_id->getVal(); }
    public function getCgcu_id() { return $this->cgcu_id->getVal(); }
    public function getCgsc_id() { return $this->cgsc_id->getVal(); }
    public function getCgc_id() { return $this->cgc_id->getVal(); }
    public function getCta_id() { return $this->cta_id->getVal(); }
    public function getNombre() { return $this->cta_nombre->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }
    
    /******************************************************
        METODOS SET
    ******************************************************/
    
    public function setGer_id($val) { $this->ger_id->setVal($val); }
    public function setCgp_id($val) { $this->cgp_id->setVal($val); }
    public function setRub_id($val) { $this->rub_id->setVal($val); }
    public function setCgcu_id($val) { $this->cgcu_id->setVal($val); }
    public function setCgsc_id($val) { $this->cgsc_id->setVal($val); }
    public function setCgc_id($val) { $this->cgc_id->setVal($val); }
    public function setCta_id($val) { $this->cta_id->setVal($val); }
    public function setNombre($val) { $this->cta_nombre->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }
    
    /******************************************************
        METODOS STA (SET sin persistencia)
    ******************************************************/
    
    public function staGer_id($val) { $this->ger_id->staVal($val); }
    public function staCgp_id($val) { $this->cgp_id->staVal($val); }
    public function staRub_id($val) { $this->rub_id->staVal($val); }
    public function staCgcu_id($val) { $this->cgcu_id->staVal($val); }
    public function staCgsc_id($val) { $this->cgsc_id->staVal($val); }
    public function staCgc_id($val) { $this->cgc_id->staVal($val); }
    public function staCta_id($val) { $this->cta_id->staVal($val); }
    public function staNombre($val) { $this->cta_nombre->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }
    
    /******************************************************
        CONSTRUCTOR
    ******************************************************/
    
    public function  __construct()
    {
        parent::__construct(array("tabla"=>"vin_Retencion"));
        
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->ger_id = new Atributo("numero","NO","PRI","ger_id");
        $this->cgp_id = new Atributo("numero","NO","PRI","cgp_id");
        $this->rub_id = new Atributo("numero","NO","PRI","rub_id");
        $this->cgcu_id = new Atributo("numero","NO","PRI","cgcu_id");
        $this->cgsc_id = new Atributo("numero","NO","PRI","cgsc_id");
        $this->cgc_id = new Atributo("cadena","NO","PRI","cgc_id");
        $this->cta_id = new Atributo("cadena","NO","PRI","cta_id");
        $this->cta_nombre = new Atributo("cadena","NO","","cta_nombre");
        $this->usr_id = new Atributo("numero","NO","","usr_id");
        
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $cta_id = ( !isset($prm->cta_id) ) ? '': $prm->cta_id;
        if($cta_id != '' ){
            $this->setGer_id($prm->ger_id);
            $this->setCgp_id($prm->cgp_id);
            $this->setRub_id($prm->rub_id);
            $this->setCgcu_id($prm->cgcu_id);
            $this->setCgsc_id($prm->cgsc_id);
            $this->setCgc_id($prm->cgc_id);
            $this->setCta_id($prm->cta_id);
            $this->select();
        }
    }
    
    /******************************************************
        FUNCIONES ESTÁTICAS
    ******************************************************/
    
    public static function abc($p)
    {
        $cta =  new Retencion();
        if( isset($p->ger_id) ) $cta->setGer_id($p->ger_id);
        if( isset($p->cgp_id) ) $cta->setCgp_id($p->cgp_id);
        if( isset($p->rub_id) ) $cta->setRub_id($p->rub_id);
        if( isset($p->cgcu_id) ) $cta->setCgcu_id($p->cgcu_id);
        if( isset($p->cgsc_id) ) $cta->setCgsc_id($p->cgsc_id);
        if( isset($p->cgc_id) ) $cta->setCgc_id($p->cgc_id);
        if( isset($p->cta_id) ) $cta->setCta_id($p->cta_id);
        if( isset($p->cta_nombre) ) $cta->setNombre($p->cta_nombre);
        if( isset($p->usr_id) ) $cta->setUsr_id($p->usr_id);
        switch($p->abc){
            case "a": $cta->insert(); return $cta->getUltimoId(); break;
            case "b": $cta->delete(); break;
            case "c": $cta->update(); break;
        }
    }
}
