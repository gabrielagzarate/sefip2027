<?php

    require_once dirname(__FILE__)."/../../Entidad.php";

    class OrigenRecurso2024 extends Entidad
    {

    /****************************************************** 
        ATRIBUTOS DE LA CLASE
    ******************************************************/ 

    protected $cff_id;	//Identificador del Clasificador de la Fuente de Financiamiento
    protected $fte_id;	//Identificador de la Fuente de Financiamiento
    protected $sft_id;	//Identificador de la Subfuente de Financiamiento
    protected $ori_id;	//Identificador del Origen de Financiamiento
    protected $ejercicio;	//AÑo del registro
    protected $nombre;	//Nombre del Origen
    protected $descripcion;	//Descripcion del Origen del recurso
    protected $clave;	//
    protected $usr_id;	//Identificador del usuario que hace el registro.

    /****************************************************** 
        METODOS GET
    ******************************************************/ 

    public function getCff_id() { return $this->cff_id->getVal(); }
    public function getFte_id() { return $this->fte_id->getVal(); }
    public function getSft_id() { return $this->sft_id->getVal(); }
    public function getOri_id() { return $this->ori_id->getVal(); }
    public function getEjercicio() { return $this->ejercicio->getVal(); }
    public function getNombre() { return $this->nombre->getVal(); }
    public function getDescripcion() { return $this->descripcion->getVal(); }
    public function getClave() { return $this->clave->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 
        METODOS SET
    ******************************************************/ 

    public function setCff_id($val) { $this->cff_id->setVal($val); }
    public function setFte_id($val) { $this->fte_id->setVal($val); }
    public function setSft_id($val) { $this->sft_id->setVal($val); }
    public function setOri_id($val) { $this->ori_id->setVal($val); }
    public function setEjercicio($val) { $this->ejercicio->setVal($val); }
    public function setNombre($val) { $this->nombre->setVal($val); }
    public function setDescripcion($val) { $this->descripcion->setVal($val); }
    public function setClave($val) { $this->clave->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 
        METODOS STA (SET sin persistencia)
    ******************************************************/ 

    public function staCff_id($val) { $this->cff_id->staVal($val); }
    public function staFte_id($val) { $this->fte_id->staVal($val); }
    public function staSft_id($val) { $this->sft_id->staVal($val); }
    public function staOri_id($val) { $this->ori_id->staVal($val); }
    public function staEjercicio($val) { $this->ejercicio->staVal($val); }
    public function staNombre($val) { $this->nombre->staVal($val); }
    public function staDescripcion($val) { $this->descripcion->staVal($val); }
    public function staClave($val) { $this->clave->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 
        CONSTRUCTOR
    ******************************************************/ 

    public function  __construct()
    {

        parent::__construct(array("tabla"=>"sinpres.cat_OrigenRecurso2024"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cff_id = new Atributo("cadena","NO","PRI","cff_id");
        $this->fte_id = new Atributo("cadena","NO","PRI","fte_id");
        $this->sft_id = new Atributo("cadena","NO","PRI","sft_id");
        $this->ori_id = new Atributo("cadena","NO","PRI","ori_id");
        $this->ejercicio = new Atributo("numero","NO","","ori_ejercicio");
        $this->nombre = new Atributo("cadena","NO","","ori_nombre");
        $this->descripcion = new Atributo("cadena","NO","","ori_descripcion");
        $this->clave = new Atributo("cadena","NO","","ori_clave");
        $this->usr_id = new Atributo("numero","NO","","usr_id");

    }

    /****************************************************** 
        FUNCIONES ESTÁTICAS
    ******************************************************/ 

    public static function abc($p)
    {
        $ori =  new OrigenRecurso2024();
        $bit_observacion = '';
        if( isset($p->cff_id) ) $ori->setCff_id($p->cff_id);
        if( isset($p->fte_id) ) $ori->setFte_id($p->fte_id);
        if( isset($p->sft_id) ) $ori->setSft_id($p->sft_id);
        if( isset($p->ori_id) ) $ori->setOri_id($p->ori_id);
        if( isset($p->nombre) ) {
            $ori->setNombre($p->nombre);
            $bit_observacion = "Nombre: ".$p->nombre;
        }
        if( isset($p->descripcion) ) $ori->setDescripcion($p->descripcion);
        if( isset($p->ejercicio) ) {
            $ori->setEjercicio($p->ejercicio);
            $bit_observacion = "Ejercicio: ".$p->ejercicio;
        }
        if( isset($p->clave) ) $ori->setClave($p->clave);
        if( isset($p->usr_id) ) $ori->setUsr_id($p->usr_id);

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip']:'127.0.0.1';
        $bit_usr_id =  $_SESSION[_S]['s24-id'];
        switch ($p->abc) {
            case 'a':
                $ori->conectar();
                $bit_observacion= 'REGISTRO INSERTADO '.$bit_observacion;
                $ori->setSql(' SET @usr_ip =INET_ATON("'.$ip.'"), @usr_id="'.$bit_usr_id.'" , @bit_observacion ="'.$bit_observacion.'";');
                $ori->consultar();
                $ori->insert(false);
                return $ori->getUltimoId();
                break;
            case 'b': $ori->delete(); break;
            case 'c': $ori->update(); break;
        }
    }
}