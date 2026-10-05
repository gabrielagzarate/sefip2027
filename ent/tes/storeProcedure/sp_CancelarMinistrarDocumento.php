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

class sp_CancelarMinistrarDocumento extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $afe_id; //
    protected $bdo_id; //
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
    public function getAfe_id()
    {
        return $this->afe_id->getVal();
    }
    public function getBdo_id()
    {
        return $this->bdo_id->getVal();
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
    public function setAfe_id($val)
    {
        $this->afe_id->setVal($val);
    }
    public function setBdo_id($val)
    {
        $this->bdo_id->setVal($val);
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
    public function staAfe_id($val)
    {
        $this->afe_id->staVal($val);
    }
    public function staBdo_id($val)
    {
        $this->bdo_id->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => BD_TESORERIA . ".sp_CancelarMinistrarDocumento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->afe_id = new Atributo("numero", "NO", "PRI", "in_afe_id", 10);
        $this->bdo_id = new Atributo("numero", "NO", "PRI", "in_bdo_id", 10);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $cancelarMinistrarDocumento = new sp_CancelarMinistrarDocumento();
        if (isset($p->gpo_id)) {
            $cancelarMinistrarDocumento->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $cancelarMinistrarDocumento->setUr_id($p->ur_id);
        }

        if (isset($p->afe_id)) {
            $cancelarMinistrarDocumento->setAfe_id($p->afe_id);
        }

        if (isset($p->bdo_id)) {
            $cancelarMinistrarDocumento->setBdo_id($p->bdo_id);
        }

        if (isset($p->bit_observacion)) {
            $cancelarMinistrarDocumento->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $cancelarMinistrarDocumento->setSis_id($p->sis_id);
        } else {
            $cancelarMinistrarDocumento->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $cancelarMinistrarDocumento->setEjercicio($p->ejercicio);
        } else {
            $cancelarMinistrarDocumento->setEjercicio(APP_EJERCICIO);
        }

        $cancelarMinistrarDocumento->execute();

        $return = new stdClass();
        $return->bandera = $cancelarMinistrarDocumento->getBandera();
        $return->mensaje = $cancelarMinistrarDocumento->getMensaje();

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
