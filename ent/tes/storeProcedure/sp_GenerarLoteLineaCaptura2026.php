<?php

/**
 * Author:
 * Created: April 29, 2019
 * Update: Oct 24, 2019
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_GenerarLoteLineaCaptura2026 extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $in_pro_id; //
    protected $in_lot_ejercicio; //
    protected $in_tem_carga; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPro_id()
    {
        return $this->in_pro_id->getVal();
    }
    public function getLot_ejercicio()
    {
        return $this->in_lot_ejercicio->getVal();
    }
    public function getTem_carga()
    {
        return $this->in_tem_carga->getVal();
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
        $this->in_pro_id->setVal($val);
    }
    public function setLot_ejercicio($val)
    {
        $this->in_lot_ejercicio->setVal($val);
    }
    public function setTem_carga($val)
    {
        $this->in_tem_carga->setVal($val);
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
        $this->in_pro_id->staVal($val);
    }
    public function staLot_ejercicio($val)
    {
        $this->in_lot_ejercicio->staVal($val);
    }
    public function staTem_carga($val)
    {
        $this->in_tem_carga->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA.".sp_GenerarLoteLineaCaptura"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->in_pro_id = new Atributo("numero", "NO", "PRI", "in_pro_id", 10);
        $this->in_lot_ejercicio = new Atributo("numero", "NO", "PRI", "in_lot_ejercicio", 4);
        $this->in_tem_carga = new Atributo("cadena", "NO", "PRI", "in_tem_carga", 50);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $generarLoteLineaCaptura = new sp_GenerarLoteLineaCaptura2026();
        if (isset($p->pro_id)) {
            $generarLoteLineaCaptura->setPro_id($p->pro_id);
        }
        if (isset($p->lot_ejercicio)) {
            $generarLoteLineaCaptura->setLot_ejercicio($p->lot_ejercicio);
        }
        if (isset($p->tem_carga)) {
            $generarLoteLineaCaptura->setTem_carga($p->tem_carga);
        }
        if (isset($p->bit_observacion)) {
            $generarLoteLineaCaptura->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $generarLoteLineaCaptura->setSis_id($p->sis_id);
        } else {
            $generarLoteLineaCaptura->setSis_id('');
        } //Agregar el sistema     del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $generarLoteLineaCaptura->setEjercicio($p->ejercicio);
        } else {
            $generarLoteLineaCaptura->setEjercicio(APP_EJERCICIO);
        }

        $generarLoteLineaCaptura->execute();

        $return = new stdClass();
        $return->bandera = $generarLoteLineaCaptura->getBandera();
        $return->mensaje = $generarLoteLineaCaptura->getMensaje();

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
