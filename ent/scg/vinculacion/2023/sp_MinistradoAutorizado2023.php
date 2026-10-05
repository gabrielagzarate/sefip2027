<?php

/**
 * Author:
 * Created: December 20, 2018
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../../StoreProcedure.php";

class sp_MinistradoAutorizado2023 extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $bdm_agrupador; //
    protected $pma_fecha; //
    protected $tmc_id; //
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
    public function getBdm_agrupador()
    {return $this->bdm_agrupador->getVal();}
    public function getPma_fecha()
    {return $this->pma_fecha->getVal();}
    public function getTmc_id()
    {return $this->tmc_id->getVal();}
    public function getSis_id()
    {return $this->sis_id->getVal();}
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
    public function setBdm_agrupador($val)
    {$this->bdm_agrupador->setVal($val);}
    public function setPma_fecha($val)
    {$this->pma_fecha->setVal($val);}
    public function setTmc_id($val)
    {$this->tmc_id->setVal($val);}
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
    public function staBdm_agrupador($val)
    {$this->bdm_agrupador->staVal($val);}
    public function staPma_fecha($val)
    {$this->pma_fecha->staVal($val);}
    public function staTmc_id($val)
    {$this->tmc_id->staVal($val);}
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

        parent::__construct(array("procedimientoAlmacenado" => "sp_MinistradoAutorizado"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->bdm_agrupador = new Atributo("cadena", "NO", "PRI", "in_bdm_agrupador", 14);
        $this->pma_fecha = new Atributo("fecha", "NO", "PRI", "in_pma_fecha", "date");
        $this->tmc_id = new Atributo("caracter", "NO", "PRI", "in_tmc_id", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $ministradoAutorizado = new sp_MinistradoAutorizado2023();
        if (isset($p->gpo_id)) {
            $ministradoAutorizado->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $ministradoAutorizado->setUr_id($p->ur_id);
        }

        if (isset($p->bdm_agrupador)) {
            $ministradoAutorizado->setBdm_agrupador($p->bdm_agrupador);
        }

        if (isset($p->pma_fecha)) {
            $ministradoAutorizado->setPma_fecha($p->pma_fecha);
        }

        if (isset($p->tmc_id)) {
            $ministradoAutorizado->setTmc_id($p->tmc_id);
        }

        if (isset($p->bit_observacion)) {
            $ministradoAutorizado->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {$ministradoAutorizado->setSis_id($p->sis_id);} else { $ministradoAutorizado->setSis_id('SCG');} //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {$ministradoAutorizado->setEjercicio($p->ejercicio);} else { $ministradoAutorizado->setEjercicio(2021);}

        $ministradoAutorizado->execute();

        $return = new stdClass();
        $return->bandera = $ministradoAutorizado->getBandera();
        $return->mensaje = $ministradoAutorizado->getMensaje();

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
