<?php

require_once __DIR__ . '/../../Entidad.php';

class TelefoniaCelular extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $ced_id; //
    protected $ccd_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $ue_id; //
    protected $epg_id; //
    protected $pg_id; //
    protected $pe_id; //
    protected $ecf_id; //
    protected $ccc_id; //
    protected $cve_id; //
    protected $utf_id; //Ubicacion
    protected $tcl_numeroCelular; //El numero debera ser unico
    protected $usr_id; //Clave identificadora del usuario que inserto el registro
    protected $unidades; //calendario de costos por partida
    protected $montos; //calendario de costos por partida
    protected $tcl_ubicacion;
    protected $tcl_mensualidad;

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getCed_id()
    {
        return $this->ced_id->getVal();
    }
    public function getCcd_id()
    {
        return $this->ccd_id->getVal();
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
    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }
    public function getPe_id()
    {
        return $this->pe_id->getVal();
    }
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getCcc_id()
    {
        return $this->ccc_id->getVal();
    }
    public function getCve_id()
    {
        return $this->cve_id->getVal();
    }
    public function getUtf_id()
    {
        return $this->utf_id->getVal();
    }
    public function getTcl_numeroCelular()
    {
        return $this->tcl_numeroCelular->getVal();
    }
    public function getMontos()
    {
        return $this->montos;
    }
    public function getUnidades()
    {
        return $this->unidades;
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getTcl_ubicacion()
    {
        return $this->tcl_ubicacion->getVal();
    }
    public function getTcl_mensualidad()
    {
        return $this->tcl_mensualidad->getVal();
    }

    /******************************************************
    METODOS SET
     ******************************************************/
    public function setCed_id($val)
    {
        $this->ced_id->setVal($val);
    }
    public function setCcd_id($val)
    {
        $this->ccd_id->setVal($val);
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
    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
    }
    public function setPe_id($val)
    {
        $this->pe_id->setVal($val);
    }
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setCcc_id($val)
    {
        $this->ccc_id->setVal($val);
    }
    public function setCve_id($val)
    {
        $this->cve_id->setVal($val);
    }
    public function setUtf_id($val)
    {
        $this->utf_id->setVal($val);
    }
    public function settcl_NumeroCelular($val)
    {
        $this->tcl_numeroCelular->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setTcl_ubicacion($val)
    {
        $this->tcl_ubicacion->setVal($val);
    }
    public function setTcl_mensualidad($val)
    {
        $this->tcl_mensualidad->setVal($val);
    }

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staCed_id($val)
    {
        $this->ced_id->staVal($val);
    }
    public function staCcd_id($val)
    {
        $this->ccd_id->staVal($val);
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
    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
    }
    public function staPe_id($val)
    {
        $this->pe_id->staVal($val);
    }
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staCcc_id($val)
    {
        $this->ccc_id->staVal($val);
    }
    public function staCve_id($val)
    {
        $this->cve_id->staVal($val);
    }
    public function staUtf_id($val)
    {
        $this->utf_id->staVal($val);
    }
    public function statcl_NumeroCelular($val)
    {
        $this->tcl_numeroCelular->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staTcl_ubicacion($val)
    {
        $this->tcl_ubicacion->staVal($val);
    }
    public function staTcl_mensualidad($val)
    {
        $this->tcl_mensualidad->staVal($val);
    }

    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {
        parent::__construct(['tabla' => 'poa_TelefoniaCelular']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->ced_id = new Atributo('numero', 'NO', 'PRI', 'ced_id');
        $this->ccd_id = new Atributo('numero', 'NO', '', 'ccd_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'MUL', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->ccc_id = new Atributo('numero', 'NO', 'MUL', 'ccc_id');
        $this->cve_id = new Atributo('numero', 'NO', '', 'cve_id');
        $this->utf_id = new Atributo('numero', 'NO', 'MUL', 'utf_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
        $this->tcl_numeroCelular = new Atributo('cadena', 'NO', 'UNI', 'tcl_numeroCelular');
        $this->tcl_ubicacion = new Atributo('cadena', 'NO', '', 'tcl_ubicacion');
        $this->tcl_mensualidad = new Atributo('numero', 'NO', '', 'tcl_mensualidad');
        $this->montos = new Meses(['prefix' => 'mon_']);
        $this->unidades = new Meses(['prefix' => 'uni_']);

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $ccd_id = 3;
        $ced_id = (!isset($prm->ced_id)) ? '' : $prm->ced_id;

        if ($ccd_id != '' && $ced_id != '') {
            $this->setCcd_id($ccd_id);
            $this->setCed_id($ced_id);
            $this->select();
        }
    } // fin __construct

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {

        $tcl = new TelefoniaCelular();

        $tcl->setCcd_id(3);
        if (isset($p->ced_id)) {
            $tcl->setCed_id($p->ced_id);
        }
        if (isset($p->gpo_id)) {
            $tcl->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $tcl->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $tcl->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $tcl->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $tcl->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $tcl->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $tcl->setEcf_id($p->ecf_id);
        }
        if (isset($p->ccc_id)) {
            $tcl->setCcc_id($p->ccc_id);
        }
        if (isset($p->cve_id)) {
            $tcl->setCve_id($p->cve_id);
        }
        if (isset($p->utf_id)) {
            $tcl->setUtf_id($p->utf_id);
        }
        if (isset($p->tcl_numeroCelular)) {
            $tcl->settcl_NumeroCelular($p->tcl_numeroCelular);
        }
        if (isset($p->usr_id)) {
            $tcl->setUsr_id($p->usr_id);
        }
        if (isset($p->tcl_ubicacion)) {
            $tcl->setTcl_ubicacion($p->tcl_ubicacion);
        }
        if (isset($p->tcl_mensualidad)) {
            $tcl->setTcl_mensualidad($p->tcl_mensualidad);
        }
        if (isset($p->montos)) {
            $tcl->getMontos()->setArrayMeses($p->montos);
        }
        if (isset($p->usr_id)) {
            $tcl->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $tcl->conectar();
        $params = [$ip];
        $tcl->setSql('SET @usr_ip  =INET_ATON(?);');
        $tcl->consultar($params);
        $params = [$_SESSION[_S]['usr_id']];
        $tcl->setSql('SET @usr_id = ?;');
        $tcl->consultar($params);
        $params = [$p->bit_observacion];
        $tcl->setSql('SET @bit_observacion = ?;');
        $tcl->consultar($params);

        switch ($p->abc) {
            case 'a':
                $tcl->insert(false);
                return $tcl->getUltimoId();
                break;
            case 'b':
                $tcl->delete(false);
                break;
            case 'c':
                $tcl->update(false);
                break;
        }
    } // fin abc
} // FIN CLASS
