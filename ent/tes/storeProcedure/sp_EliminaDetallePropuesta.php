<?php

/**
 * Author:
 * Created: February 12, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_EliminaDetallePropuesta extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pro_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $ejercicio; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicioCLC; //
    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPro_id()
    {
        return $this->pro_id->getVal();
    }
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
    public function getejercicioCLC()
    {
        return $this->ejercicioCLC->getVal();
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
    public function setPro_id($val)
    {
        $this->pro_id->setVal($val);
    }
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
    public function setejercicioCLC($val)
    {
        $this->ejercicioCLC->setVal($val);
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
    public function staPro_id($val)
    {
        $this->pro_id->staVal($val);
    }
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
    public function staejercicioCLC($val)
    {
        $this->ejercicioCLC->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_EliminaDetallePropuesta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pro_id = new Atributo("numero", "NO", "PRI", "in_pro_id", 10);
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->clc_id = new Atributo("numero", "NO", "PRI", "in_clc_id", 10);
        $this->ejercicioCLC = new Atributo("numero", "NO", "PRI", "in_ejercicio", 4);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }
    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $eliminaDetallePropuesta = new sp_EliminaDetallePropuesta();
        if (isset($p->pro_id)) {
            $eliminaDetallePropuesta->setPro_id($p->pro_id);
        }
        if (isset($p->gpo_id)) {
            $eliminaDetallePropuesta->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $eliminaDetallePropuesta->setUr_id($p->ur_id);
        }
        if (isset($p->clc_id)) {
            $eliminaDetallePropuesta->setClc_id($p->clc_id);
        }
        if (isset($p->ejercicioCLC)) {
            $eliminaDetallePropuesta->setejercicioCLC($p->ejercicioCLC);
        }
        if (isset($p->bit_observacion)) {
            $eliminaDetallePropuesta->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $eliminaDetallePropuesta->setSis_id($p->sis_id);
        } else {
            $eliminaDetallePropuesta->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $eliminaDetallePropuesta->setEjercicio($p->ejercicio);
        } else {
            $eliminaDetallePropuesta->setEjercicio(APP_EJERCICIO);
        }

        $eliminaDetallePropuesta->execute();
        $return = new stdClass();
        $return->bandera = $eliminaDetallePropuesta->getBandera();
        $return->mensaje = $eliminaDetallePropuesta->getMensaje();

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
