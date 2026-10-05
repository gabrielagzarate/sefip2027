<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class ObraClc extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $epg_id; //Identificador del Encuadre programtico
    protected $obr_origen; //
    protected $ipu_id; //
    protected $obr_contrato; //Numero de Contrato de la Obra
    protected $obr_avanceFisico; //Porcentaje del avance fisico de la Obra que avala la CLC
    protected $obr_avanceFinanciero; //Porcentaje del avance Financiero de la Obra que avala la CLC
    protected $obr_cumplimiento; //Numero Oficio, o identificador del cumplimiento
    protected $obr_vicios; //Número de Oficio de Vicios Ocultos
    protected $obr_documentacion; //Documentación que se presenta con la CLC para avalar la Obra
    protected $obr_anticipo; //Anticipo del la Obra
    protected $obr_fianza; //Fianza de la Obra
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }
    public function getOrigen()
    {
        return $this->obr_origen->getVal();
    }
    public function getIpu_id()
    {
        return $this->ipu_id->getVal();
    }
    public function getContrato()
    {
        return $this->obr_contrato->getVal();
    }
    public function getAvanceFisico()
    {
        return $this->obr_avanceFisico->getVal();
    }
    public function getAvanceFinanciero()
    {
        return $this->obr_avanceFinanciero->getVal();
    }
    public function getCumplimiento()
    {
        return $this->obr_cumplimiento->getVal();
    }
    public function getVicios()
    {
        return $this->obr_vicios->getVal();
    }
    public function getDocumentacion()
    {
        return $this->obr_documentacion->getVal();
    }
    public function getAnticipo()
    {
        return $this->obr_anticipo->getVal();
    }
    public function getFianza()
    {
        return $this->obr_fianza->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }
    public function setOrigen($val)
    {
        $this->obr_origen->setVal($val);
    }
    public function setIpu_id($val)
    {
        $this->ipu_id->setVal($val);
    }
    public function setContrato($val)
    {
        $this->obr_contrato->setVal($val);
    }
    public function setAvanceFisico($val)
    {
        $this->obr_avanceFisico->setVal($val);
    }
    public function setAvanceFinanciero($val)
    {
        $this->obr_avanceFinanciero->setVal($val);
    }
    public function setCumplimiento($val)
    {
        $this->obr_cumplimiento->setVal($val);
    }
    public function setVicios($val)
    {
        $this->obr_vicios->setVal($val);
    }
    public function setDocumentacion($val)
    {
        $this->obr_documentacion->setVal($val);
    }
    public function setAnticipo($val)
    {
        $this->obr_anticipo->setVal($val);
    }
    public function setFianza($val)
    {
        $this->obr_fianza->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }
    public function staOrigen($val)
    {
        $this->obr_origen->staVal($val);
    }
    public function staIpu_id($val)
    {
        $this->ipu_id->staVal($val);
    }
    public function staContrato($val)
    {
        $this->obr_contrato->staVal($val);
    }
    public function staAvanceFisico($val)
    {
        $this->obr_avanceFisico->staVal($val);
    }
    public function staAvanceFinanciero($val)
    {
        $this->obr_avanceFinanciero->staVal($val);
    }
    public function staCumplimiento($val)
    {
        $this->obr_cumplimiento->staVal($val);
    }
    public function staVicios($val)
    {
        $this->obr_vicios->staVal($val);
    }
    public function staDocumentacion($val)
    {
        $this->obr_documentacion->staVal($val);
    }
    public function staAnticipo($val)
    {
        $this->obr_anticipo->staVal($val);
    }
    public function staFianza($val)
    {
        $this->obr_fianza->staVal($val);
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

        parent::__construct(['tabla' => 'cat_ObraClc']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->clc_id = new Atributo('numero', 'NO', 'PRI', 'clc_id');
        $this->epg_id = new Atributo('numero', 'NO', 'MUL', 'epg_id');
        $this->obr_origen = new Atributo('numero', 'NO', '', 'obr_origen');
        $this->ipu_id = new Atributo('numero', 'YES', '', 'ipu_id');
        $this->obr_contrato = new Atributo('cadena', 'NO', '', 'obr_contrato');
        $this->obr_avanceFisico = new Atributo('numero', 'NO', '', 'obr_avanceFisico');
        $this->obr_avanceFinanciero = new Atributo('numero', 'NO', '', 'obr_avanceFinanciero');
        $this->obr_cumplimiento = new Atributo('cadena', 'NO', '', 'obr_cumplimiento');
        $this->obr_vicios = new Atributo('cadena', 'NO', '', 'obr_vicios');
        $this->obr_documentacion = new Atributo('cadena', 'NO', '', 'obr_documentacion');
        $this->obr_anticipo = new Atributo('cadena', 'NO', '', 'obr_anticipo');
        $this->obr_fianza = new Atributo('cadena', 'NO', '', 'obr_fianza');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $obr = new ObraClc();
        if (isset($p->gpo_id)) {
            $obr->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $obr->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $obr->setClc_id($p->clc_id);
        }

        if (isset($p->epg_id)) {
            $obr->setEpg_id($p->epg_id);
        }

        if (isset($p->origen)) {
            $obr->setOrigen($p->origen);
        }

        if (isset($p->ipu_id)) {
            $obr->setIpu_id($p->ipu_id);
        }

        if (isset($p->contrato)) {
            $obr->setContrato($p->contrato);
        }

        if (isset($p->avanceFisico)) {
            $obr->setAvanceFisico($p->avanceFisico);
        }

        if (isset($p->avanceFinanciero)) {
            $obr->setAvanceFinanciero($p->avanceFinanciero);
        }

        if (isset($p->cumplimiento)) {
            $obr->setCumplimiento($p->cumplimiento);
        }

        if (isset($p->vicios)) {
            $obr->setVicios($p->vicios);
        }

        if (isset($p->documentacion)) {
            $obr->setDocumentacion($p->documentacion);
        }

        if (isset($p->anticipo)) {
            $obr->setAnticipo($p->anticipo);
        }

        if (isset($p->fianza)) {
            $obr->setFianza($p->fianza);
        }

        if (isset($p->usr_id)) {
            $obr->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                if (!is_null($obr->getContrato()) && empty($p->contrato)) {
                    $obr->setContrato(' ');
                }
                $obr->conectar();
                $obr->insert(false, false);
                if (intval($obr->getErrno()) > 0) {
                    return $obr->getErrno();
                }

                $obr->desconectar();

                break;
            case 'b':$obr->delete();
                break;
            case 'c':
                $obr->update();
                if (!is_null($obr->getContrato())) {
                    $obr->setContrato(' ');
                }
                break;
        }
    }

}
