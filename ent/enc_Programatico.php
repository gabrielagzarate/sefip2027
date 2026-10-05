<?php
require_once dirname(__FILE__)."/Entidad.php";
require_once dirname(__FILE__)."/ObraActividad.php";

class enc_Programatico extends Entidad
{
        
    /****************************************************** 
            ATRIBUTOS DE LA CLASE
    ******************************************************/ 
    
    protected $epg_id;	//Identificador numerico  del encuadre programatico
    protected $gpo_id;	//Grupo de la Unidad Responsable 
    protected $ur_id;	//Identificador de la Unidad Responsable. Foranea dependiente de cat_ObraActividad
    protected $prg_id;	//Identificador del Programa del cual depende el subprograma, del cual depende el proyecto del cual depende la Obra Actividad. Foranea dependiente de cat_ObraActividad
    protected $spr_id;	//Identificador del subprograma, del cual depende el proyecto del cual depende la Obra Actividad. Foranea dependiente de cat_ObraActividad
    protected $py_id;	//Identificador del  proyecto del cual depende la Obra Actividad. Foranea dependiente de cat_ObraActividad
    protected $oa_id;	//
    protected $clave;	//La clave del encuadre formada por prg_id,spr_id,py_id,oa_id
    
    /****************************************************** 
            METODOS GET
    ******************************************************/ 
    
    public function getEpg_id() { return $this->epg_id->getVal(); }
    public function getGpo_id() { return $this->gpo_id->getVal(); }
    public function getUr_id() { return $this->ur_id->getVal(); }
    public function getPrg_id() { return $this->prg_id->getVal(); }
    public function getSpr_id() { return $this->spr_id->getVal(); }
    public function getPy_id() { return $this->py_id->getVal(); }
    public function getOa_id() { return $this->oa_id->getVal(); }
    public function getClave() { return $this->clave->getVal(); }
    public function getNombre() { 
        $obraActividad = new ObraActividad();
        $obraActividad->setGpo_id( $this->getGpo_id() );
        $obraActividad->setUr_id( $this->getUr_id() );
        $obraActividad->setPrg_id( $this->getPrg_id() );
        $obraActividad->setSpr_id( $this->getSpr_id() );
        $obraActividad->setPy_id( $this->getPy_id() );
        $obraActividad->setOa_id( $this->getOa_id() );
        $obraActividad->select();
        
        return $obraActividad->getNombre();
    }
    
    
    /****************************************************** 
            METODOS SET
    ******************************************************/ 
    
    public function setEpg_id($val) { $this->epg_id->setVal($val); }
    public function setGpo_id($val) { $this->gpo_id->setVal($val); }
    public function setUr_id($val) { $this->ur_id->setVal($val); }
    public function setPrg_id($val) { $this->prg_id->setVal($val); }
    public function setSpr_id($val) { $this->spr_id->setVal($val); }
    public function setPy_id($val) { $this->py_id->setVal($val); }
    public function setOa_id($val) { $this->oa_id->setVal($val); }
    public function setClave($val) { $this->clave->setVal($val); }
    
    /****************************************************** 
            METODOS STA (SET sin persistencia)
    ******************************************************/ 
    
    public function staEpg_id($val) { $this->epg_id->staVal($val); }
    public function staGpo_id($val) { $this->gpo_id->staVal($val); }
    public function staUr_id($val) { $this->ur_id->staVal($val); }
    public function staPrg_id($val) { $this->prg_id->staVal($val); }
    public function staSpr_id($val) { $this->spr_id->staVal($val); }
    public function staPy_id($val) { $this->py_id->staVal($val); }
    public function staOa_id($val) { $this->oa_id->staVal($val); }
    public function staClave($val) { $this->clave->staVal($val); }
    
    /****************************************************** 
            CONSTRUCTOR
    ******************************************************/ 
    
    public function  __construct()
    {
        parent::__construct(array("tabla"=>"enc_Programatico"));
        
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->epg_id = new Atributo("numero","NO","PRI","epg_id");
        $this->gpo_id = new Atributo("numero","NO","MUL","gpo_id");
        $this->ur_id = new Atributo("numero","NO","","ur_id");
        $this->prg_id = new Atributo("numero","NO","MUL","prg_id");
        $this->spr_id = new Atributo("numero","NO","","spr_id");
        $this->py_id = new Atributo("numero","NO","MUL","py_id");
        $this->oa_id = new Atributo("numero","NO","","oa_id");
        $this->clave = new Atributo("cadena","NO","","epg_clave");
        
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $epg_id = (!isset($prm->epg_id)) ? '' : $prm->epg_id;
        
        if ($epg_id != '') {
            $this->setEpg_id($epg_id);
            $this->select();
        }
    }
    
    
    /****************************************************** 
            FUNCIONES ESTÁTICAS
    ******************************************************/ 
    
    public static function abc($p)
    {
        $epg =  new enc_Programatico();
        if (isset($p->epg_id)) $epg->setEpg_id($p->epg_id);
        if (isset($p->gpo_id)) $epg->setGpo_id($p->gpo_id);
        if (isset($p->ur_id)) $epg->setUr_id($p->ur_id);
        if (isset($p->prg_id)) $epg->setPrg_id($p->prg_id);
        if (isset($p->spr_id)) $epg->setSpr_id($p->spr_id);
        if (isset($p->py_id)) $epg->setPy_id($p->py_id);
        if (isset($p->oa_id)) $epg->setOa_id($p->oa_id);
        if (isset($p->clave)) $epg->setClave($p->clave);
        switch ($p->abc) {
            case "a": $epg->insert(); return $epg->getUltimoId(); break;
            case "b": $epg->delete(); break;
            case "c": $epg->update(); break;
        }
    }        
}
