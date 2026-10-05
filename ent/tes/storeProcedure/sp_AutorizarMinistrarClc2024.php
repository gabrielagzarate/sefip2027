<?php

/**
 * Author:
 * Created: February 28, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_AutorizarMinistrarClc2024 extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $tdf_id; //
    protected $bdm_referenciaMinistrado; //
    protected $bdm_fechaMinistrado; //
    protected $tmi_id; //
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
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getTdf_id()
    {
        return $this->tdf_id->getVal();
    }
    public function getBdm_referenciaMinistrado()
    {
        return $this->bdm_referenciaMinistrado->getVal();
    }
    public function getBdm_fechaMinistrado()
    {
        return $this->bdm_fechaMinistrado->getVal();
    }
    public function getTmi_id()
    {
        return $this->tmi_id->getVal();
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
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setTdf_id($val)
    {
        $this->tdf_id->setVal($val);
    }
    public function setBdm_referenciaMinistrado($val)
    {
        $this->bdm_referenciaMinistrado->setVal($val);
    }
    public function setBdm_fechaMinistrado($val)
    {
        $this->bdm_fechaMinistrado->setVal($val);
    }
    public function setTmi_id($val)
    {
        $this->tmi_id->setVal($val);
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
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staTdf_id($val)
    {
        $this->tdf_id->staVal($val);
    }
    public function staBdm_referenciaMinistrado($val)
    {
        $this->bdm_referenciaMinistrado->staVal($val);
    }
    public function staBdm_fechaMinistrado($val)
    {
        $this->bdm_fechaMinistrado->staVal($val);
    }
    public function staTmi_id($val)
    {
        $this->tmi_id->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_AutorizarMinistrarClc2024_"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->clc_id = new Atributo("numero", "NO", "PRI", "in_clc_id", 10);
        $this->tdf_id = new Atributo("cadena", "NO", "PRI", "in_tdf_id", 4);
        $this->bdm_referenciaMinistrado = new Atributo("cadena", "NO", "PRI", "in_bdm_referenciaMinistrado", 50);
        $this->bdm_fechaMinistrado = new Atributo("fecha", "NO", "PRI", "in_bdm_fechaMinistrado", "date");
        $this->tmi_id = new Atributo("numero", "NO", "PRI", "in_tmi_id", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $autorizarMinistrarClc = new sp_AutorizarMinistrarClc2024();
        if (isset($p->gpo_id)) {
            $autorizarMinistrarClc->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $autorizarMinistrarClc->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $autorizarMinistrarClc->setClc_id($p->clc_id);
        }

        if (isset($p->tdf_id)) {
            $autorizarMinistrarClc->setTdf_id($p->tdf_id);
        }

        if (isset($p->bdm_referenciaMinistrado)) {
            $autorizarMinistrarClc->setBdm_referenciaMinistrado($p->bdm_referenciaMinistrado);
        }

        if (isset($p->bdm_fechaMinistrado)) {
            $autorizarMinistrarClc->setBdm_fechaMinistrado($p->bdm_fechaMinistrado);
        }

        if (isset($p->tmi_id)) {
            $autorizarMinistrarClc->setTmi_id($p->tmi_id);
        }

        if (isset($p->bit_observacion)) {
            $autorizarMinistrarClc->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $autorizarMinistrarClc->setSis_id($p->sis_id);
        } else {
            $autorizarMinistrarClc->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $autorizarMinistrarClc->setEjercicio($p->ejercicio);
        } else {
            $autorizarMinistrarClc->setEjercicio(APP_EJERCICIO);
        }

        $autorizarMinistrarClc->execute();

        $return = new stdClass();
        $return->bandera = $autorizarMinistrarClc->getBandera();
        $return->mensaje = $autorizarMinistrarClc->getMensaje();

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
