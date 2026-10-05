<?php

    require_once dirname(__FILE__)."/../../Entidad.php";

    class ClaveFinanciamiento extends Entidad{

    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $ecf_id;	//Identificador del encuandre de la Clave de Financiamiento
    protected $cff_id;	//
    protected $fte_id;	//Identificador de la Fuente de Financiamiento, depende de cat_ClaveFinanciamiento
    protected $sft_id;	//Identificador de la Subfuente de Financiamiento, depende de cat_ClaveFinanciamiento
    protected $ori_id;	//Identificador del Origen del recurso . Depende de cat_ClaveFinanciamiento
    protected $sor_id;	//Identificador del Sub Origen del Recurso, depende de cat_ClaveFinanciamiento
    protected $cfi_id;	//Referencia de la Clave de Financiamiento.- depende de cat_ClaveFinanciamiento
    protected $anio;	//Año del ejercicio de la Clave de Financiamiento.- Depende de cat_ClaveFinanciemiento
    protected $poa;	//Indica si esta habilitada el uso de la Clave de Financiamiento en el Programa operativo Anual
    //1: Habilitado
    //2:No habilitado
    protected $division;	//indicador de la division de Compras
    //1:tiene division
    //2:NO tiene division
    protected $clave;	//Es el conjunto de la Fuente, subfuente, origen,  suborigen, id, año de la clave de financiamiento para hacer la CLAVE
    protected $techoFinanciero;	//
    protected $claveAnterior;	//
    protected $pdp_id;	//
    protected $sts_id;	//indica si esta habilitada para su uso
    //1:Habilitada
    //2:No habilitada

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getEcf_id() { return $this->ecf_id->getVal(); }
    public function getCff_id() { return $this->cff_id->getVal(); }
    public function getFte_id() { return $this->fte_id->getVal(); }
    public function getSft_id() { return $this->sft_id->getVal(); }
    public function getOri_id() { return $this->ori_id->getVal(); }
    public function getSor_id() { return $this->sor_id->getVal(); }
    public function getCfi_id() { return $this->cfi_id->getVal(); }
    public function getAnio() { return $this->anio->getVal(); }
    public function getPoa() { return $this->poa->getVal(); }
    public function getDivision() { return $this->division->getVal(); }
    public function getClave() { return $this->clave->getVal(); }
    public function getTechoFinanciero() { return $this->techoFinanciero->getVal(); }
    public function getClaveAnterior() { return $this->claveAnterior->getVal(); }
    public function getPdp_id() { return $this->pdp_id->getVal(); }
    public function getSts_id() { return $this->sts_id->getVal(); }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setEcf_id($val) { $this->ecf_id->setVal($val); }
    public function setCff_id($val) { $this->cff_id->setVal($val); }
    public function setFte_id($val) { $this->fte_id->setVal($val); }
    public function setSft_id($val) { $this->sft_id->setVal($val); }
    public function setOri_id($val) { $this->ori_id->setVal($val); }
    public function setSor_id($val) { $this->sor_id->setVal($val); }
    public function setCfi_id($val) { $this->cfi_id->setVal($val); }
    public function setAnio($val) { $this->anio->setVal($val); }
    public function setPoa($val) { $this->poa->setVal($val); }
    public function setDivision($val) { $this->division->setVal($val); }
    public function setClave($val) { $this->clave->setVal($val); }
    public function setTechoFinanciero($val) { $this->techoFinanciero->setVal($val); }
    public function setClaveAnterior($val) { $this->claveAnterior->setVal($val); }
    public function setPdp_id($val) { $this->pdp_id->setVal($val); }
    public function setSts_id($val) { $this->sts_id->setVal($val); }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staEcf_id($val) { $this->ecf_id->staVal($val); }
    public function staCff_id($val) { $this->cff_id->staVal($val); }
    public function staFte_id($val) { $this->fte_id->staVal($val); }
    public function staSft_id($val) { $this->sft_id->staVal($val); }
    public function staOri_id($val) { $this->ori_id->staVal($val); }
    public function staSor_id($val) { $this->sor_id->staVal($val); }
    public function staCfi_id($val) { $this->cfi_id->staVal($val); }
    public function staAnio($val) { $this->anio->staVal($val); }
    public function staPoa($val) { $this->poa->staVal($val); }
    public function staDivision($val) { $this->division->staVal($val); }
    public function staClave($val) { $this->clave->staVal($val); }
    public function staTechoFinanciero($val) { $this->techoFinanciero->staVal($val); }
    public function staClaveAnterior($val) { $this->claveAnterior->staVal($val); }
    public function staPdp_id($val) { $this->pdp_id->staVal($val); }
    public function staSts_id($val) { $this->sts_id->staVal($val); }

    /******************************************************

        CONSTRUCTOR

    ******************************************************/

    public function  __construct()
    {
        parent::__construct(array("tabla"=>"ctrl2024.enc_ClaveFinanciamiento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ecf_id = new Atributo("numero","NO","PRI","ecf_id");
        $this->cff_id = new Atributo("cadena","NO","MUL","cff_id");
        $this->fte_id = new Atributo("cadena","NO","","fte_id");
        $this->sft_id = new Atributo("cadena","NO","","sft_id");
        $this->ori_id = new Atributo("cadena","NO","","ori_id");
        $this->sor_id = new Atributo("cadena","NO","","sor_id");
        $this->cfi_id = new Atributo("numero","NO","","cfi_id");
        $this->anio = new Atributo("numero","NO","","cfi_anio");
        $this->poa = new Atributo("numero","NO","","ecf_poa");
        $this->division = new Atributo("numero","NO","","ecf_division");
        $this->clave = new Atributo("cadena","NO","","ecf_clave");
        $this->techoFinanciero = new Atributo("numero","NO","","ecf_techoFinanciero");
        $this->claveAnterior = new Atributo("cadena","NO","","ecf_claveAnterior");
        $this->pdp_id = new Atributo("numero","NO","","pdp_id");
        $this->sts_id = new Atributo("numero","NO","MUL","sts_id");

    }

    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $cfi =  new ClaveFinanciamiento();
        $bit_observacion = '';
        if( isset($p->ecf_id) ) $cfi->setEcf_id($p->ecf_id);
        if( isset($p->cff_id) ) $cfi->setCff_id($p->cff_id);
        if( isset($p->fte_id) ) $cfi->setFte_id($p->fte_id);
        if( isset($p->sft_id) ) $cfi->setSft_id($p->sft_id);
        if( isset($p->ori_id) ) $cfi->setOri_id($p->ori_id);
        if( isset($p->sor_id) ) $cfi->setSor_id($p->sor_id);
        if( isset($p->cfi_id) ) $cfi->setCfi_id($p->cfi_id);
        if( isset($p->anio) ) $cfi->setAnio($p->anio);
        if( isset($p->poa) ) $cfi->setPoa($p->poa);
        if( isset($p->division) ) $cfi->setDivision($p->division);
        if( isset($p->clave) ){
            $cfi->setClave($p->clave);
            $bit_observacion = "CLAVE: ".$p->clave;
        }
        if( isset($p->techoFinanciero) ) $cfi->setTechoFinanciero($p->techoFinanciero);
        if( isset($p->claveAnterior) ) $cfi->setClaveAnterior($p->claveAnterior);
        if( isset($p->pdp_id) ) $cfi->setPdp_id($p->pdp_id);
        if( isset($p->sts_id) ) $cfi->setSts_id($p->sts_id);

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ?
            $_SESSION[_S]['ses-ip']:
            '127.0.0.1';
        $bit_usr_id =  $_SESSION[_S]['s24-id'];

        switch($p->abc){
            case "a":
                $cfi->insert();
                return $cfi->getUltimoId();
                break;
            case "b": $cfi->delete(); break;
            case "c":
                $cfi->conectar();
                $bit_observacion= 'REGISTRO ACTUALIZADO '.$bit_observacion;
                $cfi->setSql(' SET @usr_ip =INET_ATON("'.$ip.'"), @usr_id="'.$bit_usr_id.'" , @bit_observacion ="'.$bit_observacion.'";');
                $cfi->consultar();
                $cfi->update(false);
                break;
        }
    }
}