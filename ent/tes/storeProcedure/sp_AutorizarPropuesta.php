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

class sp_AutorizarPropuesta extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pro_id; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //
    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPro_id()
    {
        return $this->pro_id->getVal();
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_AutorizarPropuesta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pro_id = new Atributo("numero", "NO", "PRI", "in_pro_id", 10);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }
    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $autorizarPropuesta = new sp_AutorizarPropuesta();
        if (isset($p->pro_id)) {
            $autorizarPropuesta->setPro_id($p->pro_id);
        }
        if (isset($p->bit_observacion)) {
            $autorizarPropuesta->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $autorizarPropuesta->setSis_id($p->sis_id);
        } else {
            $autorizarPropuesta->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $autorizarPropuesta->setEjercicio($p->ejercicio);
        } else {
            $autorizarPropuesta->setEjercicio(APP_EJERCICIO);
        }

        $autorizarPropuesta->execute();
        $return = new stdClass();
        $return->bandera = $autorizarPropuesta->getBandera();
        $return->mensaje = $autorizarPropuesta->getMensaje();

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
