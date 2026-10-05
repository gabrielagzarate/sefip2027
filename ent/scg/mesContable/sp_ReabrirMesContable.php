<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sp_ReabrirMesContable
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 17-abr-2019
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 *
 * */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_ReabrirMesContable extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $msc_id; //
    protected $msc_fechaPropuestaCierre; //
    protected $msc_observacion; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getGpo_id()
    {return $this->gpo_id->getVal();}
    public function getUr_id()
    {return $this->ur_id->getVal();}
    public function getMsc_id()
    {return $this->msc_id->getVal();}
    public function getMsc_fechaPropuestaCierre()
    {return $this->msc_fechaPropuestaCierre->getVal();}
    public function getMsc_observacion()
    {return $this->msc_observacion->getVal();}
    public function getSis_id()
    {return $this->sis_id;}
    public function getBit_observacion()
    {return $this->bit_observacion;}
    public function getEjercicio()
    {return $this->ejercicio;}

    /******************************************************
    METODOS SET
     ******************************************************/
    public function setGpo_id($val)
    {$this->gpo_id->setVal($val);}
    public function setUr_id($val)
    {$this->ur_id->setVal($val);}
    public function setMsc_id($val)
    {$this->msc_id->setVal($val);}
    public function setMsc_fechaPropuestaCierre($val)
    {$this->msc_fechaPropuestaCierre->setVal($val);}
    public function setMsc_observacion($val)
    {$this->msc_observacion->setVal($val);}
    public function setSis_id($val)
    {$this->sis_id = $val;}
    public function setBit_observacion($val)
    {$this->bit_observacion = $val;}
    public function setEjercicio($val)
    {$this->ejercicio = $val;}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staGpo_id($val)
    {$this->gpo_id->staVal($val);}
    public function staUr_id($val)
    {$this->ur_id->staVal($val);}
    public function staMsc_id($val)
    {$this->msc_id->staVal($val);}
    public function staMsc_fechaPropuestaCierre($val)
    {$this->msc_fechaPropuestaCierre->staVal($val);}
    public function staMsc_observacion($val)
    {$this->msc_observacion->staVal($val);}
    public function staSis_id($val)
    {$this->sis_id = $val;}
    public function staBit_observacion($val)
    {$this->bit_observacion = $val;}
    public function staEjercicio($val)
    {$this->ejercicio = $val;}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {
        parent::__construct(array("procedimientoAlmacenado" => "sp_ReabrirMesContable"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->msc_id = new Atributo("numero", "NO", "PRI", "in_msc_id", 3);
        $this->msc_fechaPropuestaCierre = new Atributo("fecha", "NO", "PRI", "in_msc_fechaPropuestaCierre", "date");
        $this->msc_observacion = new Atributo("cadena", "NO", "PRI", "in_msc_observacion", 600);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $reabrirMesContable = new sp_ReabrirMesContable();
        if (isset($p->gpo_id)) {
            $reabrirMesContable->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $reabrirMesContable->setUr_id($p->ur_id);
        }

        if (isset($p->msc_id)) {
            $reabrirMesContable->setMsc_id($p->msc_id);
        }

        if (isset($p->msc_fechaPropuestaCierre)) {
            $reabrirMesContable->setMsc_fechaPropuestaCierre($p->msc_fechaPropuestaCierre);
        }

        if (isset($p->msc_observacion)) {
            $reabrirMesContable->setMsc_observacion($p->msc_observacion);
        }

        if (isset($p->bit_observacion)) {
            $reabrirMesContable->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {$reabrirMesContable->setSis_id($p->sis_id);} else { $reabrirMesContable->setSis_id('SCG');} //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {$reabrirMesContable->setEjercicio($p->ejercicio);} else { $reabrirMesContable->setEjercicio(APP_EJERCICIO);}

        $reabrirMesContable->execute();

        $return = new stdClass();
        $return->bandera = $reabrirMesContable->getBandera();
        $return->mensaje = $reabrirMesContable->getMensaje();

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
