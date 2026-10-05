<?php

    require_once dirname(__FILE__)."/../../Entidad.php";

    class sinpresClaveFinanciamiento extends Entidad{

    /****************************************************** 

        ATRIBUTOS DE LA CLASE

    ******************************************************/ 

    protected $ecf_id;	//Indice automatico de la Tabla Indetificador Unico de la Clave de Financiamiento a traves de los años
    protected $cff_id;	//
    protected $fte_id;	//
    protected $sft_id;	//
    protected $ori_id;	//
    protected $sor_id;	//
    protected $cso_id;	//
    protected $cfi_id;	//
    protected $anio;	//
    protected $clave;	//
    protected $ejercicio;	//
    protected $usr_id;	//

    /****************************************************** 

        METODOS GET

    ******************************************************/ 

    public function getEcf_id() { return $this->ecf_id->getVal(); }
    public function getCff_id() { return $this->cff_id->getVal(); }
    public function getFte_id() { return $this->fte_id->getVal(); }
    public function getSft_id() { return $this->sft_id->getVal(); }
    public function getOri_id() { return $this->ori_id->getVal(); }
    public function getSor_id() { return $this->sor_id->getVal(); }
    public function getCso_id() { return $this->cso_id->getVal(); }
    public function getCfi_id() { return $this->cfi_id->getVal(); }
    public function getAnio() { return $this->anio->getVal(); }
    public function getClave() { return $this->clave->getVal(); }
    public function getEjercicio() { return $this->ejercicio->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 

        METODOS SET

    ******************************************************/ 

    public function setEcf_id($val) { $this->ecf_id->setVal($val); }
    public function setCff_id($val) { $this->cff_id->setVal($val); }
    public function setFte_id($val) { $this->fte_id->setVal($val); }
    public function setSft_id($val) { $this->sft_id->setVal($val); }
    public function setOri_id($val) { $this->ori_id->setVal($val); }
    public function setSor_id($val) { $this->sor_id->setVal($val); }
    public function setCso_id($val) { $this->cso_id->setVal($val); }
    public function setCfi_id($val) { $this->cfi_id->setVal($val); }
    public function setAnio($val) { $this->anio->setVal($val); }
    public function setClave($val) { $this->clave->setVal($val); }
    public function setEjercicio($val) { $this->ejercicio->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 

        METODOS STA (SET sin persistencia)

    ******************************************************/ 

    public function staEcf_id($val) { $this->ecf_id->staVal($val); }
    public function staCff_id($val) { $this->cff_id->staVal($val); }
    public function staFte_id($val) { $this->fte_id->staVal($val); }
    public function staSft_id($val) { $this->sft_id->staVal($val); }
    public function staOri_id($val) { $this->ori_id->staVal($val); }
    public function staSor_id($val) { $this->sor_id->staVal($val); }
    public function staCso_id($val) { $this->cso_id->staVal($val); }
    public function staCfi_id($val) { $this->cfi_id->staVal($val); }
    public function staAnio($val) { $this->anio->staVal($val); }
    public function staClave($val) { $this->clave->staVal($val); }
    public function staEjercicio($val) { $this->ejercicio->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 

        CONSTRUCTOR

    ******************************************************/ 

    public function  __construct(){

        parent::__construct(array("tabla"=>"sinpres.enc_ClaveFinanciamiento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ecf_id = new Atributo("numero","NO","PRI","ecf_id");
        $this->cff_id = new Atributo("cadena","NO","MUL","cff_id");
        $this->fte_id = new Atributo("cadena","NO","MUL","fte_id");
        $this->sft_id = new Atributo("cadena","NO","","sft_id");
        $this->ori_id = new Atributo("cadena","NO","","ori_id");
        $this->sor_id = new Atributo("cadena","NO","","sor_id");
        $this->cso_id = new Atributo("cadena","NO","","cso_id");
        $this->cfi_id = new Atributo("numero","NO","","cfi_id");
        $this->anio = new Atributo("numero","NO","","cfi_anio");
        $this->clave = new Atributo("cadena","NO","MUL","ecf_clave");
        $this->ejercicio = new Atributo("numero","NO","","ecf_ejercicio");
        $this->usr_id = new Atributo("numero","NO","","usr_id");

    }


    /****************************************************** 

        FUNCIONES ESTÁTICAS

    ******************************************************/ 

    public static function abc($p){
        $cfi =  new sinpresClaveFinanciamiento();
        $bit_observacion = '';
        if( isset($p->ecf_id) ) {
            $cfi->setEcf_id($p->ecf_id);
            $bit_observacion = 'ECF: '.$p->ecf_id;
        }
        if( isset($p->cff_id) ) $cfi->setCff_id($p->cff_id);
        if( isset($p->fte_id) ) $cfi->setFte_id($p->fte_id);
        if( isset($p->sft_id) ) $cfi->setSft_id($p->sft_id);
        if( isset($p->ori_id) ) $cfi->setOri_id($p->ori_id);
        if( isset($p->sor_id) ) $cfi->setSor_id($p->sor_id);
        if( isset($p->cso_id) ) $cfi->setCso_id($p->cso_id);
        if( isset($p->cfi_id) ) $cfi->setCfi_id($p->cfi_id);
        if( isset($p->anio) ) $cfi->setAnio($p->anio);
        if( isset($p->clave) ) {
            $cfi->setClave($p->clave);
            $bit_observacion = 'Clave: '.$p->clave;
        }
        if( isset($p->ejercicio) ) $cfi->setEjercicio($p->ejercicio);
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
            case "c": $cfi->update(); break;
        }
    }
}