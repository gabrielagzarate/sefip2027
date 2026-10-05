<?php

/**
 * Author:
 * Created: February 9, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_GenerarPropuesta extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $metodo_pago; //
    protected $ejecicio; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getMetodo_pago()
    {
        return $this->metodo_pago->getVal();
    }
    public function getEjecicio()
    {
        return $this->ejecicio->getVal();
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
    public function setMetodo_pago($val)
    {
        $this->metodo_pago->setVal($val);
    }
    public function setEjecicio($val)
    {
        $this->ejecicio->setVal($val);
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
    public function staMetodo_pago($val)
    {
        $this->metodo_pago->staVal($val);
    }
    public function staEjecicio($val)
    {
        $this->ejecicio->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_GenerarPropuesta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->metodo_pago = new Atributo("numero", "NO", "PRI", "in_metodo_pago", 10);
        $this->ejecicio = new Atributo("numero", "NO", "PRI", "in_ejecicio", 4);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }
    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $generarPropuesta = new sp_GenerarPropuesta();
        if (isset($p->metodo_pago)) {
            $generarPropuesta->setMetodo_pago($p->metodo_pago);
        }
        if (isset($p->ejecicio)) {
            $generarPropuesta->setEjecicio($p->ejecicio);
        }
        if (isset($p->bit_observacion)) {
            $generarPropuesta->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $generarPropuesta->setSis_id($p->sis_id);
        } else {
            $generarPropuesta->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $generarPropuesta->setEjercicio($p->ejercicio);
        } else {
            $generarPropuesta->setEjercicio(APP_EJERCICIO);
        }

        $generarPropuesta->execute();

        $return = new stdClass();
        $return->bandera = $generarPropuesta->getBandera();
        $return->mensaje = $generarPropuesta->getMensaje();

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
