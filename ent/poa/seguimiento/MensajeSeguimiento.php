<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class MensajeSeguimiento extends Entidad
{
    /*****************************************************/
    /**************** ATRIBUTOS DE LA CLASE *************/
    /***************************************************/

    protected $msj_id; //
    protected $nim_id; //
    protected $pii_id; //
    protected $epf_id; //
    protected $epg_id; //
    protected $usr_id; //
    protected $usr_fecha; //
    protected $requerir_evidencias;
    protected $gpo_id; //
    protected $ur_id; //

    /***************************************/
    /********GETTERS***********/
    /***************************************/
    public function getMsj_id()
    {
        $this->msj_id->getVal();
    }
    public function getNim_id()
    {
        $this->nim_id->getVal();
    }
    public function getPii_id()
    {
        $this->pii_id->getVal();
    }
    public function getEpf_id()
    {
        $this->epf_id->getVal();
    }
    public function getEpg_id()
    {
        $this->epg_id->getVal();
    }
    public function getUsr_id()
    {
        $this->usr_id->getVal();
    }
    public function getUsr_fecha()
    {
        $this->usr_fecha->getVal();
    }
    public function getRequerir_evidencias()
    {
        $this->requerir_evidencias->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    /***************************************/
    /*******SETTERS**********/
    /***************************************/
    public function setMsj_id($msj_id)
    {
        $this->msj_id->setVal($msj_id);
    }
    public function setNim_id($nim_id)
    {
        $this->nim_id->setVal($nim_id);
    }
    public function setPii_id($pii_id)
    {
        $this->pii_id->setVal($pii_id);
    }
    public function setEpf_id($epf_id)
    {
        $this->epf_id->setVal($epf_id);
    }
    public function setEpg_id($epg_id)
    {
        $this->epg_id->setVal($epg_id);
    }
    public function setUsr_id($usr_id)
    {
        $this->usr_id->setVal($usr_id);
    }
    public function setUsr_fecha($usr_fecha)
    {
        $this->usr_fecha->setVal($usr_fecha);
    }
    public function setRequerir_evidencias($requerir_evidencias)
    {
        $this->requerir_evidencias->setVal($requerir_evidencias);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }

    /**************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_MensajeSeguimiento"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->msj_id = new Atributo("numero", "NO", "PRI", "msj_id");
        $this->nim_id = new Atributo("numero", "NO", "", "nim_id");
        $this->pii_id = new Atributo("numero", "NO", "", "pii_id");
        $this->epf_id = new Atributo("numero", "NO", "", "epf_id");
        $this->epg_id = new Atributo("cadena", "NO", "", "epg_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->usr_fecha = new Atributo("fecha", "NO", "", "usr_fecha");
        $this->requerir_evidencias = new Atributo("boolean", "NO", "", "requerir_evidencias");
        $this->gpo_id = new Atributo("numero", "YES", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "YES", "", "ur_id");
    }

    /* *****************************************************
    FUNCIONES ESTÁTICAS
     ***************************************************** */
    public static function abc($p)
    {
        $msj = new MensajeSeguimiento();
        if (isset($p->msj_id)) {
            $msj->setMsj_id($p->msj_id);
        }

        if (isset($p->nim_id)) {
            $msj->setNim_id($p->nim_id);
        }

        if (isset($p->pii_id)) {
            $msj->setPii_id($p->pii_id);
        }

        if (isset($p->epf_id)) {
            $msj->setEpf_id($p->epf_id);
        }

        if (isset($p->epg_id)) {
            $msj->setEpg_id($p->epg_id);
        }

        if (isset($p->usr_id)) {
            $msj->setUsr_id($p->usr_id);
        }

        if (isset($p->usr_fecha)) {
            $msj->setUsr_fecha($p->usr_fecha);
        }

        if (isset($p->requerir_evidencias)) {
            $msj->setRequerir_evidencias($p->requerir_evidencias);
        }

        if (isset($p->gpo_id)) {
            $msj->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $msj->setUr_id($p->ur_id);
        }

        switch ($p->abc) {
            case "a":
                $msj->insert();
                return $msj->getUltimoId();
                break;
            case "b":
                $msj->delete();
                break;
            case "c":
                $msj->update();
                break;
        }
    }
} //Fin class
