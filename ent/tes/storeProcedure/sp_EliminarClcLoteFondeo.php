<?php

/**
 * Author:
 * Created: January 30, 2025
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_EliminarClcLoteFondeo extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $lot_id; //
    protected $fpa_id; //
    protected $msc_id; //
    protected $lot_ejercicio; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $lom_id; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getLot_id()
    {
        return $this->lot_id->getVal();
    }
    public function getFpa_id()
    {
        return $this->fpa_id->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getLot_ejercicio()
    {
        return $this->lot_ejercicio->getVal();
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
    public function getLom_id()
    {
        return $this->lom_id->getVal();
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
    public function setLot_id($val)
    {
        $this->lot_id->setVal($val);
    }
    public function setFpa_id($val)
    {
        $this->fpa_id->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setLot_ejercicio($val)
    {
        $this->lot_ejercicio->setVal($val);
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
    public function setLom_id($val)
    {
        $this->lom_id->setVal($val);
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
    public function staLot_id($val)
    {
        $this->lot_id->staVal($val);
    }
    public function staFpa_id($val)
    {
        $this->fpa_id->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staLot_ejercicio($val)
    {
        $this->lot_ejercicio->staVal($val);
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
    public function staLom_id($val)
    {
        $this->lom_id->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_EliminarClcLoteFondeo"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->lot_id = new Atributo("numero", "NO", "PRI", "in_lot_id", 10);
        $this->fpa_id = new Atributo("numero", "NO", "PRI", "in_fpa_id", 1);
        $this->msc_id = new Atributo("numero", "NO", "PRI", "in_msc_id", 2);
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "in_lot_ejercicio", 4);
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->clc_id = new Atributo("numero", "NO", "PRI", "in_clc_id", 10);
        $this->lom_id = new Atributo("numero", "NO", "PRI", "in_lom_id", 10);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $eliminarClcLoteFondeo = new sp_EliminarClcLoteFondeo();
        if (isset($p->lot_id)) {
            $eliminarClcLoteFondeo->setLot_id($p->lot_id);
        }
        if (isset($p->fpa_id)) {
            $eliminarClcLoteFondeo->setFpa_id($p->fpa_id);
        }
        if (isset($p->msc_id)) {
            $eliminarClcLoteFondeo->setMsc_id($p->msc_id);
        }
        if (isset($p->lot_ejercicio)) {
            $eliminarClcLoteFondeo->setLot_ejercicio($p->lot_ejercicio);
        }
        if (isset($p->gpo_id)) {
            $eliminarClcLoteFondeo->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $eliminarClcLoteFondeo->setUr_id($p->ur_id);
        }
        if (isset($p->clc_id)) {
            $eliminarClcLoteFondeo->setClc_id($p->clc_id);
        }
        if (isset($p->lom_id)) {
            $eliminarClcLoteFondeo->setLom_id($p->lom_id);
        }
        if (isset($p->bit_observacion)) {
            $eliminarClcLoteFondeo->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $eliminarClcLoteFondeo->setSis_id($p->sis_id);
        } else {
            $eliminarClcLoteFondeo->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $eliminarClcLoteFondeo->setEjercicio($p->ejercicio);
        } else {
            $eliminarClcLoteFondeo->setEjercicio(APP_EJERCICIO);
        }

        $eliminarClcLoteFondeo->execute();

        $return = new stdClass();
        $return->bandera = $eliminarClcLoteFondeo->getBandera();
        $return->mensaje = $eliminarClcLoteFondeo->getMensaje();

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
