<?php

/**
 * Author:
 * Created: January 1, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_CogActualizar extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pg_id;
    protected $sis_id;
    protected $bit_observacion;
    protected $ejercicio;

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }
    public function getSis_id()
    {
        return $this->sis_id;
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
    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
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
    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
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
        parent::__construct(array("procedimientoAlmacenado" => "sp_CogActualizar"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pg_id = new Atributo("numero", "NO", "PRI", "in_pg_id", 3);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $cogActualizar = new sp_CogActualizar();
        if (isset($p->pg_id)) {
            $cogActualizar->setPg_id($p->pg_id);
        }

        if (isset($p->bit_observacion)) {
            $cogActualizar->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $cogActualizar->setSis_id($p->sis_id);
        } else {
            $cogActualizar->setSis_id('SCG');
        }
        if (isset($p->ejercicio)) {
            $cogActualizar->setEjercicio($p->ejercicio);
        } else {
            $cogActualizar->setEjercicio(APP_EJERCICIO);
        }
        $cogActualizar->execute();
        $return = new stdClass();
        $return->bandera = $cogActualizar->getBandera();
        $return->mensaje = $cogActualizar->getMensaje();

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
