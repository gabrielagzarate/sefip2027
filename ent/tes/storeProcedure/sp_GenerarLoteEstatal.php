<?php

/**
 * Author:
 * Created: March 25, 2019
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_GenerarLoteEstatal extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $in_todas_clcs; //
    protected $in_fpa_id; //
    protected $in_lot_ejercicio; //
    protected $in_fecha_pago; //
    protected $in_pro_id; //
    protected $in_documento; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getTodas_clcs()
    {
        return $this->in_todas_clcs->getVal();
    }
    public function getFpa_id()
    {
        return $this->in_fpa_id->getVal();
    }
    public function getLot_ejercicio()
    {
        return $this->in_lot_ejercicio->getVal();
    }
    public function getFecha_pago()
    {
        return $this->in_fecha_pago->getVal();
    }
    public function getPro_id()
    {
        return $this->in_pro_id->getVal();
    }
    public function getDocumento()
    {
        return $this->in_documento->getVal();
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
    public function setTodas_clcs($val)
    {
        $this->in_todas_clcs->setVal($val);
    }
    public function setFpa_id($val)
    {
        $this->in_fpa_id->setVal($val);
    }
    public function setLot_ejercicio($val)
    {
        $this->in_lot_ejercicio->setVal($val);
    }
    public function setFecha_pago($val)
    {
        $this->in_fecha_pago->setVal($val);
    }
    public function setPro_id($val)
    {
        $this->in_pro_id->setVal($val);
    }
    public function setDocumento($val)
    {
        $this->in_documento->setVal($val);
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
    public function staTodas_clcs($val)
    {
        $this->in_todas_clcs->staVal($val);
    }
    public function staFpa_id($val)
    {
        $this->in_fpa_id->staVal($val);
    }
    public function staLot_ejercicio($val)
    {
        $this->in_lot_ejercicio->staVal($val);
    }
    public function staFecha_pago($val)
    {
        $this->in_fecha_pago->staVal($val);
    }
    public function staPro_id($val)
    {
        $this->in_pro_id->staVal($val);
    }
    public function staDocumento($val)
    {
        $this->in_documento->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA.".sp_GenerarLoteEstatal"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->in_todas_clcs = new Atributo("numero", "NO", "PRI", "in_todas_clcs", 1);
        $this->in_fpa_id = new Atributo("numero", "NO", "PRI", "in_fpa_id", 1);
        $this->in_lot_ejercicio = new Atributo("numero", "NO", "PRI", "in_lot_ejercicio", 4);
        $this->in_fecha_pago = new Atributo("fecha", "NO", "PRI", "in_fecha_pago", "date");
        $this->in_pro_id = new Atributo("numero", "NO", "PRI", "in_pro_id", 10);
        $this->in_documento = new Atributo("numero", "NO", "PRI", "in_documento", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $generarLoteEstatal = new sp_GenerarLoteEstatal();
        if (isset($p->todas_clcs)) {
            $generarLoteEstatal->setTodas_clcs($p->todas_clcs);
        }
        if (isset($p->fpa_id)) {
            $generarLoteEstatal->setFpa_id($p->fpa_id);
        }
        if (isset($p->lot_ejercicio)) {
            $generarLoteEstatal->setLot_ejercicio($p->lot_ejercicio);
        }
        if (isset($p->fecha_pago)) {
            $generarLoteEstatal->setFecha_pago($p->fecha_pago);
        }
        if (isset($p->pro_id)) {
            $generarLoteEstatal->setPro_id($p->pro_id);
        }
        if (isset($p->documento)) {
            $generarLoteEstatal->setDocumento($p->documento);
        }
        if (isset($p->bit_observacion)) {
            $generarLoteEstatal->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $generarLoteEstatal->setSis_id($p->sis_id);
        } else {
            $generarLoteEstatal->setSis_id('');
        } //Agregar el sistema del mensaje     que necesitas
        if (isset($p->ejercicio)) {
            $generarLoteEstatal->setEjercicio($p->ejercicio);
        } else {
            $generarLoteEstatal->setEjercicio(APP_EJERCICIO);
        }

        $generarLoteEstatal->execute();

        $return = new stdClass();
        $return->bandera = $generarLoteEstatal->getBandera();
        $return->mensaje = $generarLoteEstatal->getMensaje();

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
