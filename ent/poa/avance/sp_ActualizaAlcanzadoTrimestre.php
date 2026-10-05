<?php
/**
 * Author:
 * Created: March 13, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */
require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_ActualizaAlcanzadoTrimestre extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //
    protected $epg_id; //
    protected $tri_id; //
    protected $mac_alcanzado; //
    protected $mact_alcanzado; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

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

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getMac_alcanzado()
    {
        return $this->mac_alcanzado->getVal();
    }

    public function getMact_alcanzado()
    {
        return $this->mact_alcanzado->getVal();
    }

    public function getSis_id()
    {
        return $this->sis_id->getVal();
    }

    public function getBit_observacion()
    {
        return $this->bit_observacion;
    }

    public function getEjercicio()
    {
        return $this->ejercicio;
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

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setMac_alcanzado($val)
    {
        $this->mac_alcanzado->setVal($val);
    }

    public function setMact_alcanzado($val)
    {
        $this->mact_alcanzado->setVal($val);
    }

    public function setSis_id($val)
    {
        $this->sis_id = $val;
    }

    public function setBit_observacion($val)
    {
        $this->bit_observacion = $val;
    }

    public function setEjercicio($val)
    {
        $this->ejercicio = $val;
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

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staMac_alcanzado($val)
    {
        $this->mac_alcanzado->staVal($val);
    }

    public function staMact_alcanzado($val)
    {
        $this->mact_alcanzado->staVal($val);
    }

    public function staSis_id($val)
    {
        $this->sis_id = $val;
    }

    public function staBit_observacion($val)
    {
        $this->bit_observacion = $val;
    }

    public function staEjercicio($val)
    {
        $this->ejercicio = $val;
    }
    /******************************************************
    CONSTRUCTOR
    ******************************************************/

    public function __construct()
    {

        parent::__construct(array("procedimientoAlmacenado" => "sp_ActualizaAlcanzadoTrimestre"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->pii_id = new Atributo("numero", "NO", "PRI", "in_pii_id", 10);
        $this->epg_id = new Atributo("numero", "NO", "PRI", "in_epg_id", 6);
        $this->tri_id = new Atributo("numero", "NO", "PRI", "in_tri_id", 1);
        $this->mac_alcanzado = new Atributo("numero", "NO", "PRI", "in_mac_alcanzado", 18,2);
        $this->mact_alcanzado = new Atributo("numero", "NO", "PRI", "in_mact_alcanzado", 18,2);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }
    /******************************************************
    FUNCIONES ESTÁTICAS
    ******************************************************/

    public static function call($p)
    {

        $actualizaAlcanzadoTrimestre = new sp_ActualizaAlcanzadoTrimestre();
        if (isset($p->gpo_id)) {
            $actualizaAlcanzadoTrimestre->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $actualizaAlcanzadoTrimestre->setUr_id($p->ur_id);
        }

        if (isset($p->pii_id)) {
            $actualizaAlcanzadoTrimestre->setPii_id($p->pii_id);
        }

        if (isset($p->epg_id)) {
            $actualizaAlcanzadoTrimestre->setEpg_id($p->epg_id);
        }

        if (isset($p->tri_id)) {
            $actualizaAlcanzadoTrimestre->setTri_id($p->tri_id);
        }

        if (isset($p->mac_alcanzado)) {
            $actualizaAlcanzadoTrimestre->setMac_alcanzado($p->mac_alcanzado);
        }

        if (isset($p->mact_alcanzado)) {
            $actualizaAlcanzadoTrimestre->setMact_alcanzado($p->mact_alcanzado);
        }

        if (isset($p->bit_observacion)) {
            $actualizaAlcanzadoTrimestre->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $actualizaAlcanzadoTrimestre->setSis_id($p->sis_id);
        } else {
            $actualizaAlcanzadoTrimestre->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $actualizaAlcanzadoTrimestre->setEjercicio($p->ejercicio);
        } else {
            $actualizaAlcanzadoTrimestre->setEjercicio(2024);
        }

        $actualizaAlcanzadoTrimestre->execute();

        $return = new stdClass();
        $return->bandera = $actualizaAlcanzadoTrimestre->getBandera();
        $return->mensaje = $actualizaAlcanzadoTrimestre->getMensaje();

        return $return;
    }
    /******************************************************
    FUNCIONES SOBRECARGA
    ******************************************************/

    public function execute($conectar = true, $error = false)
    {

        parent::execute();

        $return = new stdClass();
        $return->bandera = $this->getBandera();
        $return->mensaje = $this->getMensaje();

        return $return;
    }
}
