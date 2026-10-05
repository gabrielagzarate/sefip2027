<?php

/**
 * Author: 
 * Created: March 16, 2018
 * Comment: 
 * Example: 
 * Class Parameters: 
 *     
 * Considerations: 
 */

require_once dirname(__FILE__) . "/../../../StoreProcedure.php";

class sp_Modificado extends StoreProcedure
{

    /******************************************************
                 ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id;    //
    protected $ur_id;    //
    protected $mod_id;    //
    protected $pma_fecha;    //
    protected $omo_id;    //
    protected $sis_id;    //
    protected $bit_observacion;    //
    protected $ejercicio;    //

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
    public function getMod_id()
    {
        return $this->mod_id->getVal();
    }
    public function getPma_fecha()
    {
        return $this->pma_fecha->getVal();
    }
    public function getOmo_id()
    {
        return $this->omo_id->getVal();
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
    public function setMod_id($val)
    {
        $this->mod_id->setVal($val);
    }
    public function setPma_fecha($val)
    {
        $this->pma_fecha->setVal($val);
    }
    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
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
    public function staMod_id($val)
    {
        $this->mod_id->staVal($val);
    }
    public function staPma_fecha($val)
    {
        $this->pma_fecha->staVal($val);
    }
    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
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
    public function  __construct()
    {

        parent::__construct(array("procedimientoAlmacenado" => "sp_Modificado"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->mod_id = new Atributo("numero", "NO", "PRI", "in_mod_id", 10);
        $this->pma_fecha = new Atributo("fecha", "NO", "PRI", "in_pma_fecha", "date");
        $this->omo_id = new Atributo("caracter", "NO", "PRI", "in_omo_id", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                   FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $modificado =  new sp_Modificado();
        if (isset($p->gpo_id)) $modificado->setGpo_id($p->gpo_id);
        if (isset($p->ur_id)) $modificado->setUr_id($p->ur_id);
        if (isset($p->mod_id)) $modificado->setMod_id($p->mod_id);
        if (isset($p->pma_fecha)) $modificado->setPma_fecha($p->pma_fecha);
        if (isset($p->omo_id)) $modificado->setOmo_id($p->omo_id);
        if (isset($p->bit_observacion)) $modificado->setBit_observacion($p->bit_observacion);
        if (isset($p->sis_id)) {
            $modificado->setSis_id($p->sis_id);
        } else {
            $modificado->setSis_id('SCG');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $modificado->setEjercicio($p->ejercicio);
        } else {
            $modificado->setEjercicio(2024);
        }

        $modificado->execute();

        $return = new stdClass();
        $return->bandera = $modificado->getBandera();
        $return->mensaje = $modificado->getMensaje();

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
