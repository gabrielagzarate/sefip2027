<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class PermisoUsuarioPrevio extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $usp_id; // Identificador del Menu
    protected $usp_ejercicio; //
    protected $sis_id; // Identificador del sistema
    protected $men_id; // Identificador del Menu
    protected $opc_id; //Identificador de la Opción
    protected $tar_id; //Identificador de la Tarea
    protected $usp_fechaInicio; //
    protected $usp_fechaFin; //
    protected $usr_id; //Usuario que ingresa el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getUsp_id()
    {
        return $this->usp_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->usp_ejercicio->getVal();
    }
    public function getSis_id()
    {
        return $this->sis_id->getVal();
    }
    public function getMen_id()
    {
        return $this->men_id->getVal();
    }
    public function getOpc_id()
    {
        return $this->opc_id->getVal();
    }
    public function getTar_id()
    {
        return $this->tar_id->getVal();
    }
    public function getFechaInicio()
    {
        return $this->usp_fechaInicio->getVal();
    }
    public function getFechaFin()
    {
        return $this->usp_fechaFin->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setUsp_id($val)
    {
        $this->usp_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->usp_ejercicio->setVal($val);
    }
    public function setSis_id($val)
    {
        $this->sis_id->setVal($val);
    }
    public function setMen_id($val)
    {
        $this->men_id->setVal($val);
    }
    public function setOpc_id($val)
    {
        $this->opc_id->setVal($val);
    }
    public function setTar_id($val)
    {
        $this->tar_id->setVal($val);
    }
    public function setFechaInicio($val)
    {
        $this->usp_fechaInicio->setVal($val);
    }
    public function setFechaFin($val)
    {
        $this->usp_fechaFin->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staUsp_id($val)
    {
        $this->usp_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->usp_ejercicio->staVal($val);
    }
    public function staSis_id($val)
    {
        $this->sis_id->staVal($val);
    }
    public function staMen_id($val)
    {
        $this->men_id->staVal($val);
    }
    public function staOpc_id($val)
    {
        $this->opc_id->staVal($val);
    }
    public function staTar_id($val)
    {
        $this->tar_id->staVal($val);
    }
    public function staFechaInicio($val)
    {
        $this->usp_fechaInicio->staVal($val);
    }
    public function staFechaFin($val)
    {
        $this->usp_fechaFin->staVal($val);
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

        parent::__construct(['tabla' => BD_USUARIO . '.cat_PermisoUsuarioPrevio']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->usp_id = new Atributo('numero', 'NO', 'MUL', 'usp_id');
        $this->usp_ejercicio = new Atributo('numero', 'NO', '', 'usp_ejercicio');
        $this->sis_id = new Atributo('cadena', 'NO', '', 'sis_id');
        $this->men_id = new Atributo('numero', 'NO', '', 'men_id');
        $this->opc_id = new Atributo('numero', 'NO', '', 'opc_id');
        $this->tar_id = new Atributo('numero', 'NO', '', 'tar_id');
        $this->usp_fechaInicio = new Atributo('fecha', 'NO', '', 'usp_fechaInicio');
        $this->usp_fechaFin = new Atributo('fecha', 'NO', '', 'usp_fechaFin');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $usp = new PermisoUsuarioPrevio();
        if (isset($p->usp_id)) {
            $usp->setUsp_id($p->usp_id);
        }

        if (isset($p->ejercicio)) {
            $usp->setEjercicio($p->ejercicio);
        }

        if (isset($p->sis_id)) {
            $usp->setSis_id($p->sis_id);
        }

        if (isset($p->men_id)) {
            $usp->setMen_id($p->men_id);
        }

        if (isset($p->opc_id)) {
            $usp->setOpc_id($p->opc_id);
        }

        if (isset($p->tar_id)) {
            $usp->setTar_id($p->tar_id);
        }

        if (isset($p->fechaInicio)) {
            $usp->setFechaInicio($p->fechaInicio);
        }

        if (isset($p->fechaFin)) {
            $usp->setFechaFin($p->fechaFin);
        }

        if (isset($p->usr_id)) {
            $usp->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':$usp->insert();
                return $usp->getUltimoId();
                break;
            case 'b':$usp->delete();
                break;
            case 'c':$usp->update();
                break;
        }
    }

}
