<?php

require_once dirname(__FILE__) . '/Entidad.php';

class InversionPublica extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $ipu_id;//
    protected $eip_id;//
    protected $tci_id;//
    protected $mdc_id;//
    protected $iva_id;//
    protected $vur_id;//
    protected $gpo;//
    protected $ur;//
    protected $ejercicio;//
    protected $numeroContrato;//
    protected $objetoContrato;//
    protected $vigencia;//
    protected $rfc_id;//
    protected $fechaContrato;//
    protected $plazoEjecucion;//
    protected $inicio;//
    protected $termino;//
    protected $licitacion;//
    protected $fechaFallo;//
    protected $importeBruto;//
    protected $importeIva;//
    protected $importeTotal;
    protected $importeAnticipo;//
    protected $porcentajeAnticipo;//
    protected $importeAcomprometer;//
    protected $usr_id;//

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getIpu_id()
    {
        return $this->ipu_id->getVal();
    }
    public function getEip_id()
    {
        return $this->eip_id->getVal();
    }
    public function getTci_id()
    {
        return $this->tci_id->getVal();
    }
    public function getMdc_id()
    {
        return $this->mdc_id->getVal();
    }
    public function getIva_id()
    {
        return $this->iva_id->getVal();
    }
    public function getVur_id()
    {
        return $this->vur_id->getVal();
    }
    public function getGpo()
    {
        return $this->gpo->getVal();
    }
    public function getUr()
    {
        return $this->ur->getVal();
    }
    public function getEjercicio()
    {
        return $this->ejercicio->getVal();
    }
    public function getNumeroContrato()
    {
        return $this->numeroContrato->getVal();
    }
    public function getObjetoContrato()
    {
        return $this->objetoContrato->getVal();
    }
    public function getVigencia()
    {
        return $this->vigencia->getVal();
    }
    public function getRfc_id()
    {
        return $this->rfc_id->getVal();
    }
    public function getFechaContrato()
    {
        return $this->fechaContrato->getVal();
    }
    public function getPlazoEjecucion()
    {
        return $this->plazoEjecucion->getVal();
    }
    public function getInicio()
    {
        return $this->inicio->getVal();
    }
    public function getTermino()
    {
        return $this->termino->getVal();
    }
    public function getLicitacion()
    {
        return $this->licitacion->getVal();
    }
    public function getFechaFallo()
    {
        return $this->fechaFallo->getVal();
    }
    public function getImporteBruto()
    {
        return $this->importeBruto->getVal();
    }
    public function getImporteIva()
    {
        return $this->importeIva->getVal();
    }
    public function getImporteTotal()
    {
        return $this->importeTotal->getVal();
    }
    public function getImporteAnticipo()
    {
        return $this->importeAnticipo->getVal();
    }
    public function getPorcentajeAnticipo()
    {
        return $this->porcentajeAnticipo->getVal();
    }
    public function getImporteAcomprometer()
    {
        return $this->importeAcomprometer->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setIpu_id($val)
    {
        $this->ipu_id->setVal($val);
    }
    public function setEip_id($val)
    {
        $this->eip_id->setVal($val);
    }
    public function setTci_id($val)
    {
        $this->tci_id->setVal($val);
    }
    public function setMdc_id($val)
    {
        $this->mdc_id->setVal($val);
    }
    public function setIva_id($val)
    {
        $this->iva_id->setVal($val);
    }
    public function setVur_id($val)
    {
        $this->vur_id->setVal($val);
    }
    public function setGpo($val)
    {
        $this->gpo->setVal($val);
    }
    public function setUr($val)
    {
        $this->ur->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->ejercicio->setVal($val);
    }
    public function setNumeroContrato($val)
    {
        $this->numeroContrato->setVal($val);
    }
    public function setObjetoContrato($val)
    {
        $this->objetoContrato->setVal($val);
    }
    public function setVigencia($val)
    {
        $this->vigencia->setVal($val);
    }
    public function setRfc_id($val)
    {
        $this->rfc_id->setVal($val);
    }
    public function setFechaContrato($val)
    {
        $this->fechaContrato->setVal($val);
    }
    public function setPlazoEjecucion($val)
    {
        $this->plazoEjecucion->setVal($val);
    }
    public function setInicio($val)
    {
        $this->inicio->setVal($val);
    }
    public function setTermino($val)
    {
        $this->termino->setVal($val);
    }
    public function setLicitacion($val)
    {
        $this->licitacion->setVal($val);
    }
    public function setFechaFallo($val)
    {
        $this->fechaFallo->setVal($val);
    }
    public function setImporteBruto($val)
    {
        $this->importeBruto->setVal($val);
    }
    public function setImporteIva($val)
    {
        $this->importeIva->setVal($val);
    }
    public function setImporteTotal($val)
    {
        $this->importeTotal->setVal($val);
    }
    public function setImporteAnticipo($val)
    {
        $this->importeAnticipo->setVal($val);
    }
    public function setPorcentajeAnticipo($val)
    {
        $this->porcentajeAnticipo->setVal($val);
    }
    public function setImporteAcomprometer($val)
    {
        $this->importeAcomprometer->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staIpu_id($val)
    {
        $this->ipu_id->staVal($val);
    }
    public function staEip_id($val)
    {
        $this->eip_id->staVal($val);
    }
    public function staTci_id($val)
    {
        $this->tci_id->staVal($val);
    }
    public function staMdc_id($val)
    {
        $this->mdc_id->staVal($val);
    }
    public function staIva_id($val)
    {
        $this->iva_id->staVal($val);
    }
    public function staVur_id($val)
    {
        $this->vur_id->staVal($val);
    }
    public function staGpo($val)
    {
        $this->gpo->staVal($val);
    }
    public function staUr($val)
    {
        $this->ur->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->ejercicio->staVal($val);
    }
    public function staNumeroContrato($val)
    {
        $this->numeroContrato->staVal($val);
    }
    public function staObjetoContrato($val)
    {
        $this->objetoContrato->staVal($val);
    }
    public function staVigencia($val)
    {
        $this->vigencia->staVal($val);
    }
    public function staRfc_id($val)
    {
        $this->rfc_id->staVal($val);
    }
    public function staFechaContrato($val)
    {
        $this->fechaContrato->staVal($val);
    }
    public function staPlazoEjecucion($val)
    {
        $this->plazoEjecucion->staVal($val);
    }
    public function staInicio($val)
    {
        $this->inicio->staVal($val);
    }
    public function staTermino($val)
    {
        $this->termino->staVal($val);
    }
    public function staLicitacion($val)
    {
        $this->licitacion->staVal($val);
    }
    public function staFechaFallo($val)
    {
        $this->fechaFallo->staVal($val);
    }
    public function staImporteBruto($val)
    {
        $this->importeBruto->staVal($val);
    }
    public function staImporteIva($val)
    {
        $this->importeIva->staVal($val);
    }
    public function staImporteTotal($val)
    {
        $this->importeTotal->staVal($val);
    }
    public function staImporteAnticipo($val)
    {
        $this->importeAnticipo->staVal($val);
    }
    public function staPorcentajeAnticipo($val)
    {
        $this->porcentajeAnticipo->staVal($val);
    }
    public function staImporteAcomprometer($val)
    {
        $this->importeAcomprometer->staVal($val);
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

        parent::__construct(['tabla' => 'sinpres.cat_InversionPublica']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ipu_id = new Atributo('numero', 'NO', 'PRI', 'ipu_id');
        $this->eip_id = new Atributo('numero', 'NO', 'MUL', 'eip_id');
        $this->tci_id = new Atributo('numero', 'NO', 'MUL', 'tci_id');
        $this->mdc_id = new Atributo('numero', 'NO', 'MUL', 'mdc_id');
        $this->iva_id = new Atributo('numero', 'NO', 'MUL', 'iva_id');
        $this->vur_id = new Atributo('numero', 'NO', 'MUL', 'vur_id');
        $this->gpo = new Atributo('numero', 'NO', '', 'gpo');
        $this->ur = new Atributo('numero', 'NO', '', 'ur');
        $this->ejercicio = new Atributo('numero', 'NO', '', 'ipu_ejercicio');
        $this->numeroContrato = new Atributo('cadena', 'NO', '', 'ipu_numeroContrato');
        $this->objetoContrato = new Atributo('cadena', 'NO', '', 'ipu_objetoContrato');
        $this->vigencia = new Atributo('fecha', 'YES', '', 'ipu_vigencia');
        $this->rfc_id = new Atributo('cadena', 'NO', '', 'rfc_id');
        $this->fechaContrato = new Atributo('fecha', 'NO', '', 'ipu_fechaContrato');
        $this->plazoEjecucion = new Atributo('numero', 'NO', '', 'ipu_plazoEjecucion');
        $this->inicio = new Atributo('fecha', 'NO', '', 'ipu_inicio');
        $this->termino = new Atributo('fecha', 'NO', '', 'ipu_termino');
        $this->licitacion = new Atributo('numero', 'YES', '', 'ipu_licitacion');
        $this->fechaFallo = new Atributo('fecha', 'YES', '', 'ipu_fechaFallo');
        $this->importeBruto = new Atributo('numero', 'NO', '', 'ipu_importeBruto');
        $this->importeIva = new Atributo('numero', 'NO', '', 'ipu_importeIva');
        $this->importeTotal = new Atributo('numero', 'NO', '', 'ipu_importeTotal');
        $this->importeAnticipo = new Atributo('numero', 'NO', '', 'ipu_importeAnticipo');
        $this->porcentajeAnticipo = new Atributo('numero', 'NO', '', 'ipu_porcentajeAnticipo');
        $this->importeAcomprometer = new Atributo('numero', 'NO', '', 'ipu_importeAcomprometer');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }


    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $ipu = new InversionPublica();
        if (isset($p->ipu_id)) {
            $ipu->setIpu_id($p->ipu_id);
        }
        if (isset($p->eip_id)) {
            $ipu->setEip_id($p->eip_id);
        }
        if (isset($p->tci_id)) {
            $ipu->setTci_id($p->tci_id);
        }
        if (isset($p->mdc_id)) {
            $ipu->setMdc_id($p->mdc_id);
        }
        if (isset($p->iva_id)) {
            $ipu->setIva_id($p->iva_id);
        }
        if (isset($p->vur_id)) {
            $ipu->setVur_id($p->vur_id);
        }
        if (isset($p->gpo)) {
            $ipu->setGpo($p->gpo);
        }
        if (isset($p->ur)) {
            $ipu->setUr($p->ur);
        }
        if (isset($p->ejercicio)) {
            $ipu->setEjercicio($p->ejercicio);
        }
        if (isset($p->numeroContrato)) {
            $ipu->setNumeroContrato($p->numeroContrato);
        }
        if (isset($p->objetoContrato)) {
            $ipu->setObjetoContrato($p->objetoContrato);
        }
        if (isset($p->vigencia)) {
            $ipu->setVigencia($p->vigencia);
        }
        if (isset($p->rfc_id)) {
            $ipu->setRfc_id($p->rfc_id);
        }
        if (isset($p->fechaContrato)) {
            $ipu->setFechaContrato($p->fechaContrato);
        }
        if (isset($p->plazoEjecucion)) {
            $ipu->setPlazoEjecucion($p->plazoEjecucion);
        }
        if (isset($p->inicio)) {
            $ipu->setInicio($p->inicio);
        }
        if (isset($p->termino)) {
            $ipu->setTermino($p->termino);
        }
        if (isset($p->licitacion)) {
            $ipu->setLicitacion($p->licitacion);
        }
        if (isset($p->fechaFallo)) {
            $ipu->setFechaFallo($p->fechaFallo);
        }
        if (isset($p->importeBruto)) {
            $ipu->setImporteBruto($p->importeBruto);
        }
        if (isset($p->importeIva)) {
            $ipu->setImporteIva($p->importeIva);
        }
        if (isset($p->importeTotal)) {
            $ipu->setImporteTotal($p->importeTotal);
        }
        if (isset($p->importeAnticipo)) {
            $ipu->setImporteAnticipo($p->importeAnticipo);
        }
        if (isset($p->porcentajeAnticipo)) {
            $ipu->setPorcentajeAnticipo($p->porcentajeAnticipo);
        }
        if (isset($p->importeAcomprometer)) {
            $ipu->setImporteAcomprometer($p->importeAcomprometer);
        }
        if (isset($p->usr_id)) {
            $ipu->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a': $ipu->insert();
                return $ipu->getUltimoId();
                break;
            case 'b': $ipu->delete();
                break;
            case 'c': $ipu->update();
                break;
        }
    }

}
