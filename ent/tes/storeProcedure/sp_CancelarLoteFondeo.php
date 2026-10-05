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

class sp_CancelarLoteFondeo extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $lot_id; //
    protected $fpa_id; //
    protected $msc_id; //
    protected $lot_ejercicio; //
    protected $tlo_id; //
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
    public function getTlo_id()
    {
        return $this->tlo_id->getVal();
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
    public function setTlo_id($val)
    {
        $this->tlo_id->setVal($val);
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
    public function staTlo_id($val)
    {
        $this->tlo_id->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_CancelarLoteFondeo"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->lot_id = new Atributo("numero", "NO", "PRI", "in_lot_id", 10);
        $this->fpa_id = new Atributo("numero", "NO", "PRI", "in_fpa_id", 1);
        $this->msc_id = new Atributo("numero", "NO", "PRI", "in_msc_id", 2);
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "in_lot_ejercicio", 4);
        $this->tlo_id = new Atributo("numero", "NO", "PRI", "in_tlo_id", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $cancelarLoteFondeo = new sp_CancelarLoteFondeo();
        if (isset($p->lot_id)) {
            $cancelarLoteFondeo->setLot_id($p->lot_id);
        }
        if (isset($p->fpa_id)) {
            $cancelarLoteFondeo->setFpa_id($p->fpa_id);
        }
        if (isset($p->msc_id)) {
            $cancelarLoteFondeo->setMsc_id($p->msc_id);
        }
        if (isset($p->lot_ejercicio)) {
            $cancelarLoteFondeo->setLot_ejercicio($p->lot_ejercicio);
        }
        if (isset($p->tlo_id)) {
            $cancelarLoteFondeo->setTlo_id($p->tlo_id);
        }
        if (isset($p->bit_observacion)) {
            $cancelarLoteFondeo->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $cancelarLoteFondeo->setSis_id($p->sis_id);
        } else {
            $cancelarLoteFondeo->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas

        if (isset($p->ejercicio)) {
            $cancelarLoteFondeo->setEjercicio($p->ejercicio);
        } else {
            $cancelarLoteFondeo->setEjercicio(APP_EJERCICIO);
        }

        $cancelarLoteFondeo->execute();

        $return = new stdClass();
        $return->bandera = $cancelarLoteFondeo->getBandera();
        $return->mensaje = $cancelarLoteFondeo->getMensaje();

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
