<?php

require_once dirname(__FILE__) . '../../../Entidad.php';

class bit_Subprogramas extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $bit_id;	//
    protected $bit_observacion;	//
    protected $prgs_id;	//
    protected $sprs_id;	//
    protected $esr_id;	//
    protected $pdf_id;	//
    protected $usr_id;	//

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getBit_id()
    {
        return $this->bit_id->getVal();
    }
    public function getObservacion()
    {
        return $this->bit_observacion->getVal();
    }
    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getSprs_id()
    {
        return $this->sprs_id->getVal();
    }
    public function getEsr_id()
    {
        return $this->esr_id->getVal();
    }
    public function getPdf_id()
    {
        return $this->pdf_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setBit_id($val)
    {
        $this->bit_id->setVal($val);
    }
    public function setObservacion($val)
    {
        $this->bit_observacion->setVal($val);
    }
    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setSprs_id($val)
    {
        $this->sprs_id->setVal($val);
    }
    public function setEsr_id($val)
    {
        $this->esr_id->setVal($val);
    }
    public function setPdf_id($val)
    {
        $this->pdf_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staBit_id($val)
    {
        $this->bit_id->staVal($val);
    }
    public function staObservacion($val)
    {
        $this->bit_observacion->staVal($val);
    }
    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staSprs_id($val)
    {
        $this->sprs_id->staVal($val);
    }
    public function staEsr_id($val)
    {
        $this->esr_id->staVal($val);
    }
    public function staPdf_id($val)
    {
        $this->pdf_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

        CONSTRUCTOR

    ******************************************************/

    public function __construct()
    {

        parent::__construct(['tabla' => '' . BD_BITACORA . '.bit_Subprogramas']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->bit_id = new Atributo('numero', 'NO', 'PRI', 'bit_id');
        $this->bit_observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->prgs_id = new Atributo('numero', 'NO', '', 'prgs_id');
        $this->sprs_id = new Atributo('numero', 'NO', '', 'sprs_id');
        $this->esr_id = new Atributo('numero', 'NO', '', 'esr_id');
        $this->pdf_id = new Atributo('numero', 'NO', '', 'pdf_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }

    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $bit = new bit_Subprogramas();
        if (isset($p->bit_id)) {
            $bit->setBit_id($p->bit_id);
        }
        if (isset($p->bit_observacion)) {
            $bit->setObservacion($p->bit_observacion);
        }
        if (isset($p->prgs_id)) {
            $bit->setPrgs_id($p->prgs_id);
        }
        if (isset($p->sprs_id)) {
            $bit->setSprs_id($p->sprs_id);
        }
        if (isset($p->esr_id)) {
            $bit->setEsr_id($p->esr_id);
        }
        if (isset($p->pdf_id)) {
            $bit->setPdf_id($p->pdf_id);
        }
        if (isset($p->usr_id)) {
            $bit->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a': $bit->insert();
                return $bit->getUltimoId();
                break;
            case 'b': $bit->delete();
                break;
            case 'c': $bit->update();
                break;
        }
    }
}
