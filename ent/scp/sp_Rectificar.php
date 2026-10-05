<?php

/**
 * Author:
 * Created: February 15, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
*/

require_once dirname(__FILE__) . '/../StoreProcedure.php';

class sp_Rectificar extends StoreProcedure
{
    /******************************************************
                ATRIBUTOS DE LA CLASE
    ******************************************************/
    protected $gpo_id;	//
    protected $ur_id;	//
    protected $rct_id;	//
    protected $sis_id;	//
    protected $bit_observacion;	//
    protected $ejercicio;	//

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
    public function getRct_id()
    {
        return $this->rct_id->getVal();
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
    public function setRct_id($val)
    {
        $this->rct_id->setVal($val);
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
    public function staRct_id($val)
    {
        $this->rct_id->staVal($val);
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
        parent::__construct(['procedimientoAlmacenado' => 'sp_Rectificar']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id', 1);
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'in_ur_id', 2);
        $this->rct_id = new Atributo('numero', 'NO', 'PRI', 'in_rct_id', 10);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $rectificar = new sp_Rectificar();
        if (isset($p->gpo_id)) {
            $rectificar->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $rectificar->setUr_id($p->ur_id);
        }
        if (isset($p->rct_id)) {
            $rectificar->setRct_id($p->rct_id);
        }
        if (isset($p->bit_observacion)) {
            $rectificar->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {  //Agregar el sistema del mensaje que necesitas
            $rectificar->setSis_id($p->sis_id);
        } else {
            $rectificar->setSis_id('');
        }
        if (isset($p->ejercicio)) {
            $rectificar->setEjercicio($p->ejercicio);
        } else {
            $rectificar->setEjercicio(2023);
        }

        $rectificar->execute();

        $return = new stdClass();
        $return->bandera = $rectificar->getBandera();
        $return->mensaje = $rectificar->getMensaje();

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
