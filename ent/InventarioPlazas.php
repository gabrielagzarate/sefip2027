<?php

require_once __DIR__ . '/../lib/constantes.php';
require_once __DIR__ . '/Entidad.php';

class InventarioPlazas extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $inp_id; //
    protected $gpo_id; //cat_UnidadEjecutora - Llave Foranea
    protected $ur_id; //cat_UnidadEjecutora - Llave Foranea
    protected $ue_id; //cat_UnidadEjecutora - Llave Foranea
    protected $epg_id; //
    protected $ecf_id; //
    protected $enr_id; //
    protected $activas; //
    protected $vacantes; //
    protected $litigio; //
    protected $usr_id; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getInp_id()
    {
        return $this->inp_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getEnr_id()
    {
        return $this->enr_id->getVal();
    }
    public function getActivas()
    {
        return $this->activas->getVal();
    }
    public function getVacantes()
    {
        return $this->vacantes->getVal();
    }
    public function getLitigio()
    {
        return $this->litigio->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************
    METODOS SET
     ******************************************************/
    public function setInp_id($val)
    {
        $this->inp_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setEnr_id($val)
    {
        $this->enr_id->setVal($val);
    }
    public function setActivas($val)
    {
        $this->activas->setVal($val);
    }
    public function setVacantes($val)
    {
        $this->vacantes->setVal($val);
    }
    public function setLitigio($val)
    {
        $this->litigio->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staInp_id($val)
    {
        $this->inp_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staEnr_id($val)
    {
        $this->enr_id->staVal($val);
    }
    public function staActivas($val)
    {
        $this->activas->staVal($val);
    }
    public function staVacantes($val)
    {
        $this->vacantes->staVal($val);
    }
    public function staLitigio($val)
    {
        $this->litigio->staVal($val);
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

        parent::__construct(['tabla' => 'cat_InventarioPlazas']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->inp_id = new Atributo('numero', 'NO', 'PRI', 'inp_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', 'MUL', 'epg_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'MUL', 'ecf_id');
        $this->enr_id = new Atributo('numero', 'NO', 'MUL', 'enr_id');
        $this->activas = new Atributo('numero', 'NO', '', 'inp_activas');
        $this->vacantes = new Atributo('numero', 'NO', '', 'inp_vacantes');
        $this->litigio = new Atributo('numero', 'NO', '', 'inp_litigio');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    } // fin __construct

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $inp = new InventarioPlazas();
        if (isset($p->inp_id)) {
            $inp->setInp_id($p->inp_id);
        }

        if (isset($p->gpo_id)) {
            $inp->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $inp->setUr_id($p->ur_id);
        }

        if (isset($p->ue_id)) {
            $inp->setUe_id($p->ue_id);
        }

        if (isset($p->epg_id)) {
            $inp->setEpg_id($p->epg_id);
        }

        if (isset($p->ecf_id)) {
            $inp->setEcf_id($p->ecf_id);
        }

        if (isset($p->enr_id)) {
            $inp->setEnr_id($p->enr_id);
        }

        if (isset($p->activas)) {
            $inp->setActivas($p->activas);
        }

        if (isset($p->vacantes)) {
            $inp->setVacantes($p->vacantes);
        }

        if (isset($p->litigio)) {
            $inp->setLitigio($p->litigio);
        }

        if (isset($p->usr_id)) {
            $inp->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':$inp->insert();
                return $inp->getUltimoId();
                break;
            case 'b':$inp->delete();
                break;
            case 'c':$inp->update();
                break;
        }
    } // fin abc
} // fin class
