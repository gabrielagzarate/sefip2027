<?php

    require_once dirname(__FILE__)."/../../Entidad.php";

    class ClaveFinanciamiento2024 extends Entidad
    {

    /****************************************************** 
        ATRIBUTOS DE LA CLASE
    ******************************************************/ 

    protected $cff_id;	//
    protected $fte_id;	//
    protected $sft_id;	//
    protected $ori_id;	//
    protected $sor_id;	//
    protected $cfi_id;	//
    protected $anio;	//
    protected $clave;	//
    protected $nombre;	//
    protected $ejercicio;	//
    protected $descripcion;	//
    protected $abreviatura;	//
    protected $trp_id;	//
    protected $trp_id_distribucion;	//
    protected $ejercicio2;	//COntrola por año de los  registros insertados
    protected $usr_id;	//

    /****************************************************** 
        METODOS GET
    ******************************************************/ 

    public function getCff_id() { return $this->cff_id->getVal(); }
    public function getFte_id() { return $this->fte_id->getVal(); }
    public function getSft_id() { return $this->sft_id->getVal(); }
    public function getOri_id() { return $this->ori_id->getVal(); }
    public function getSor_id() { return $this->sor_id->getVal(); }
    public function getCfi_id() { return $this->cfi_id->getVal(); }
    public function getAnio() { return $this->anio->getVal(); }
    public function getClave() { return $this->clave->getVal(); }
    public function getNombre() { return $this->nombre->getVal(); }
    public function getEjercicio() { return $this->ejercicio->getVal(); }
    public function getDescripcion() { return $this->descripcion->getVal(); }
    public function getAbreviatura() { return $this->abreviatura->getVal(); }
    public function getTrp_id() { return $this->trp_id->getVal(); }
    public function getTrp_id_distribucion() { return $this->trp_id_distribucion->getVal(); }
    public function getEjercicio2() { return $this->ejercicio2->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 
        METODOS SET
    ******************************************************/ 

    public function setCff_id($val) { $this->cff_id->setVal($val); }
    public function setFte_id($val) { $this->fte_id->setVal($val); }
    public function setSft_id($val) { $this->sft_id->setVal($val); }
    public function setOri_id($val) { $this->ori_id->setVal($val); }
    public function setSor_id($val) { $this->sor_id->setVal($val); }
    public function setCfi_id($val) { $this->cfi_id->setVal($val); }
    public function setAnio($val) { $this->anio->setVal($val); }
    public function setClave($val) { $this->clave->setVal($val); }
    public function setNombre($val) { $this->nombre->setVal($val); }
    public function setEjercicio($val) { $this->ejercicio->setVal($val); }
    public function setDescripcion($val) { $this->descripcion->setVal($val); }
    public function setAbreviatura($val) { $this->abreviatura->setVal($val); }
    public function setTrp_id($val) { $this->trp_id->setVal($val); }
    public function setTrp_id_distribucion($val) { $this->trp_id_distribucion->setVal($val); }
    public function setEjercicio2($val) { $this->ejercicio2->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 
        METODOS STA (SET sin persistencia)
    ******************************************************/ 

    public function staCff_id($val) { $this->cff_id->staVal($val); }
    public function staFte_id($val) { $this->fte_id->staVal($val); }
    public function staSft_id($val) { $this->sft_id->staVal($val); }
    public function staOri_id($val) { $this->ori_id->staVal($val); }
    public function staSor_id($val) { $this->sor_id->staVal($val); }
    public function staCfi_id($val) { $this->cfi_id->staVal($val); }
    public function staAnio($val) { $this->anio->staVal($val); }
    public function staClave($val) { $this->clave->staVal($val); }
    public function staNombre($val) { $this->nombre->staVal($val); }
    public function staEjercicio($val) { $this->ejercicio->staVal($val); }
    public function staDescripcion($val) { $this->descripcion->staVal($val); }
    public function staAbreviatura($val) { $this->abreviatura->staVal($val); }
    public function staTrp_id($val) { $this->trp_id->staVal($val); }
    public function staTrp_id_distribucion($val) { $this->trp_id_distribucion->staVal($val); }
    public function staEjercicio2($val) { $this->ejercicio2->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 
        CONSTRUCTOR
    ******************************************************/ 

    public function  __construct()
    {
        parent::__construct(array("tabla"=>"sinpres.cat_ClaveFinanciamiento2024"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cff_id = new Atributo("cadena","NO","PRI","cff_id");
        $this->fte_id = new Atributo("cadena","NO","PRI","fte_id");
        $this->sft_id = new Atributo("cadena","NO","PRI","sft_id");
        $this->ori_id = new Atributo("cadena","NO","PRI","ori_id");
        $this->sor_id = new Atributo("cadena","NO","PRI","sor_id");
        $this->cfi_id = new Atributo("numero","NO","PRI","cfi_id");
        $this->anio = new Atributo("numero","NO","PRI","cfi_anio");
        $this->clave = new Atributo("cadena","NO","","cfi_clave");
        $this->nombre = new Atributo("cadena","NO","","cfi_nombre");
        $this->ejercicio = new Atributo("numero","NO","","cfi_ejercicio");
        $this->descripcion = new Atributo("cadena","NO","","cfi_descripcion");
        $this->abreviatura = new Atributo("cadena","NO","","cfi_abreviatura");
        $this->trp_id = new Atributo("cadena","YES","MUL","trp_id");
        $this->trp_id_distribucion = new Atributo("cadena","YES","MUL","trp_id_distribucion");
        $this->ejercicio2 = new Atributo("numero","NO","","cfi_ejercicio2");
        $this->usr_id = new Atributo("numero","NO","","usr_id");

    }

    /****************************************************** 
        FUNCIONES ESTÁTICAS
    ******************************************************/ 

    public static function abc($p)
    {
        $cfi =  new ClaveFinanciamiento2024();
        $bit_observacion = '';
        if( isset($p->cff_id) ) $cfi->setCff_id($p->cff_id);
        if( isset($p->fte_id) ) $cfi->setFte_id($p->fte_id);
        if( isset($p->sft_id) ) $cfi->setSft_id($p->sft_id);
        if( isset($p->ori_id) ) $cfi->setOri_id($p->ori_id);
        if( isset($p->sor_id) ) $cfi->setSor_id($p->sor_id);
        if( isset($p->cfi_id) ) $cfi->setCfi_id($p->cfi_id);
        if( isset($p->anio) ) $cfi->setAnio($p->anio);
        if( isset($p->clave) ) {
            $cfi->setClave($p->clave);
            $bit_observacion = "CLAVE: ".$p->clave;
        }
        if( isset($p->nombre) ){
             $cfi->setNombre($p->nombre);
             $bit_observacion = "NOMBRE: ".$p->nombre;
        }
        if( isset($p->ejercicio) ) {
            $cfi->setEjercicio($p->ejercicio);
            $bit_observacion = "EJERCICIO: ".$p->ejercicio;
        }
        if( isset($p->descripcion) ) $cfi->setDescripcion($p->descripcion);
        if( isset($p->abreviatura) ) $cfi->setAbreviatura($p->abreviatura);
        if( isset($p->trp_id) ) $cfi->setTrp_id($p->trp_id);
        if( isset($p->trp_id_distribucion) ) $cfi->setTrp_id_distribucion($p->trp_id_distribucion);
        if( isset($p->ejercicio2) ){
            $cfi->setEjercicio2($p->ejercicio2);
            $bit_observacion = "EJERCICIO2: ".$p->ejercicio2;
        }
        if( isset($p->usr_id) ) $cfi->setUsr_id($p->usr_id);

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? 
            $_SESSION[_S]['ses-ip']:
            '127.0.0.1';
        $bit_usr_id =  $_SESSION[_S]['s24-id'];

        switch($p->abc){
            case "a": 
                $cfi->conectar();
                $bit_observacion= 'REGISTRO INSERTADO '.$bit_observacion;
                $cfi->setSql(' SET @usr_ip =INET_ATON("'.$ip.'"), @usr_id="'.$bit_usr_id.'" , @bit_observacion ="'.$bit_observacion.'";');
                $cfi->consultar();
                $cfi->insert(false); 
                return $cfi->getUltimoId(); 
                break;
            case "b": $cfi->delete(); break;
            case "c": 
                $cfi->conectar();
                $bit_observacion= 'ACTUALIZAR REGISTRO '.$bit_observacion;
                $cfi->setSql(' SET @usr_ip =INET_ATON("'.$ip.'"), @usr_id="'.$bit_usr_id.'" , @bit_observacion ="'.$bit_observacion.'";');
                $cfi->consultar();
                $cfi->update(false); 
            break;
        }
    }
}