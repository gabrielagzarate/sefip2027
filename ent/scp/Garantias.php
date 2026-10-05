<?php
require_once dirname(__FILE__) . "/../Entidad.php"; 

class BancoGarantia extends Entidad
{
    /****************************************************** 
        ATRIBUTOS DE LA CLASE
    ******************************************************/ 

    protected $bga_id;    //
    protected $vur_id;    //indice / llave indentificador unico del reigstro o garantia
    protected $iaf_id;    //clave de la institucion afianzadora
    protected $tga_id;    //Clave del tipo de Garantia
    protected $stg_id;    //Clave del subtipo de Garantia
    protected $numeroDocumento;    //Numero/Folio/indentificador  del documento
    protected $fechaEmision;    //Fecha de emisión del documento
    protected $importeGarantia;    //importe que avala la garantia
    protected $observacion;    //Onservaciones de las garantias
    protected $ejercicio;    //Año del registro
    protected $usr_id;    //usuario que hace el registro ctrl[ejercicio].cat_Usuario

    /****************************************************** 
        METODOS GET
    ******************************************************/ 

    public function getBga_id() { return $this->bga_id->getVal(); }
    public function getVur_id() { return $this->vur_id->getVal(); }
    public function getIaf_id() { return $this->iaf_id->getVal(); }
    public function getTga_id() { return $this->tga_id->getVal(); }
    public function getStg_id() { return $this->stg_id->getVal(); }
    public function getNumeroDocumento() { return $this->numeroDocumento->getVal(); }
    public function getFechaEmision() { return $this->fechaEmision->getVal(); }
    public function getImporteGarantia() { return $this->importeGarantia->getVal(); }
    public function getObservacion() { return $this->observacion->getVal(); }
    public function getEjercicio() { return $this->ejercicio->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 
        METODOS SET
    ******************************************************/ 

    public function setBga_id($val) { $this->bga_id->setVal($val); }
    public function setVur_id($val) { $this->vur_id->setVal($val); }
    public function setIaf_id($val) { $this->iaf_id->setVal($val); }
    public function setTga_id($val) { $this->tga_id->setVal($val); }
    public function setStg_id($val) { $this->stg_id->setVal($val); }
    public function setNumeroDocumento($val) { $this->numeroDocumento->setVal($val); }
    public function setFechaEmision($val) { $this->fechaEmision->setVal($val); }
    public function setImporteGarantia($val) { $this->importeGarantia->setVal($val); }
    public function setObservacion($val) { $this->observacion->setVal($val); }
    public function setEjercicio($val) { $this->ejercicio->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 
        METODOS STA (SET sin persistencia)
    ******************************************************/ 

    public function staBga_id($val) { $this->bga_id->staVal($val); }
    public function staVur_id($val) { $this->vur_id->staVal($val); }
    public function staIaf_id($val) { $this->iaf_id->staVal($val); }
    public function staTga_id($val) { $this->tga_id->staVal($val); }
    public function staStg_id($val) { $this->stg_id->staVal($val); }
    public function staNumeroDocumento($val) { $this->numeroDocumento->staVal($val); }
    public function staFechaEmision($val) { $this->fechaEmision->staVal($val); }
    public function staImporteGarantia($val) { $this->importeGarantia->staVal($val); }
    public function staObservacion($val) { $this->observacion->staVal($val); }
    public function staEjercicio($val) { $this->ejercicio->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 
        CONSTRUCTOR
    ******************************************************/ 

    public function  __construct()
    {
        $tabla = BD_SINPRES . ".cat_BancoGarantia";
        parent::__construct(array("tabla" => $tabla));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->bga_id = new Atributo("numero","NO","PRI","bga_id");
        $this->vur_id = new Atributo("numero","NO","MUL","vur_id");
        $this->iaf_id = new Atributo("numero","NO","MUL","iaf_id");
        $this->tga_id = new Atributo("cadena","NO","MUL","tga_id");
        $this->stg_id = new Atributo("numero","NO","","stg_id");
        $this->numeroDocumento = new Atributo("cadena","NO","","bga_numeroDocumento");
        $this->fechaEmision = new Atributo("fecha","YES","","bga_fechaEmision");
        $this->importeGarantia = new Atributo("numero","NO","","bga_importeGarantia");
        $this->observacion = new Atributo("cadena","NO","","bga_observacion");
        $this->ejercicio = new Atributo("numero","NO","","bga_ejercicio");
        $this->usr_id = new Atributo("numero","NO","","usr_id");
    }

    /****************************************************** 
        FUNCIONES ESTÁTICAS
    ******************************************************/ 

    public static function abc($p)
    {
        $bga =  new BancoGarantia();
        if( isset($p->bga_id) ) $bga->setBga_id($p->bga_id);
        if( isset($p->vur_id) ) $bga->setVur_id($p->vur_id);
        if( isset($p->iaf_id) ) $bga->setIaf_id($p->iaf_id);
        if( isset($p->tga_id) ) $bga->setTga_id($p->tga_id);
        if( isset($p->stg_id) ) $bga->setStg_id($p->stg_id);
        if( isset($p->numeroDocumento) ) $bga->setNumeroDocumento($p->numeroDocumento);
        if( isset($p->fechaEmision) ) $bga->setFechaEmision($p->fechaEmision);
        if( isset($p->importeGarantia) ) $bga->setImporteGarantia($p->importeGarantia);
        if( isset($p->observacion) ) $bga->setObservacion($p->observacion);
        if( isset($p->ejercicio) ) $bga->setEjercicio($p->ejercicio);
        if( isset($p->usr_id) ) $bga->setUsr_id($p->usr_id);
        switch($p->abc){
            case "a": $bga->insert(); 
                return $bga->getUltimoId(); break;
            case "b": $bga->delete(); break;
            case "c": $bga->update(); break;
        }
    }
}
