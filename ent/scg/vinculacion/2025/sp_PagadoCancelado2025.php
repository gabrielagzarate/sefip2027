<?php

/**
 * Author:
 * Created: Enero 20, 2021
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../../../StoreProcedure.php";

class sp_PagadoCancelado2025 extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $in_gpo_id; //
    protected $in_ur_id; //
    protected $in_bdp_agrupador; //
    protected $in_pma_fecha; //
    protected $in_tmc_id; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getGpo_id()
    {
        return $this->in_gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->in_ur_id->getVal();
    }
    public function getBdp_agrupador()
    {
        return $this->in_bdp_agrupador->getVal();
    }
    public function getPma_fecha()
    {
        return $this->in_pma_fecha->getVal();
    }
    public function getTmc_id()
    {
        return $this->in_tmc_id->getVal();
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
        $this->in_gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->in_ur_id->setVal($val);
    }
    public function setBdp_agrupador($val)
    {
        $this->in_bdp_agrupador->setVal($val);
    }
    public function setPma_fecha($val)
    {
        $this->in_pma_fecha->setVal($val);
    }
    public function setTmc_id($val)
    {
        $this->in_tmc_id->setVal($val);
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
        $this->in_gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->in_ur_id->staVal($val);
    }
    public function staBdp_agrupador($val)
    {
        $this->in_bdp_agrupador->staVal($val);
    }
    public function staPma_fecha($val)
    {
        $this->in_pma_fecha->staVal($val);
    }
    public function staTmc_id($val)
    {
        $this->in_tmc_id->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => "sp_PagadoCancelado"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->in_gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->in_ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->in_bdp_agrupador = new Atributo("cadena", "NO", "PRI", "in_bdp_agrupador", 75);
        $this->in_pma_fecha = new Atributo("fecha", "NO", "PRI", "in_pma_fecha", "date");
        $this->in_tmc_id = new Atributo("caracter", "NO", "PRI", "in_tmc_id", 1);
        $this->sis_id = new Atributo("cadena", "NO", "", "sis_id");
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $pagadoCancelado = new sp_PagadoCancelado2025();
        if (isset($p->gpo_id)) {
            $pagadoCancelado->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $pagadoCancelado->setUr_id($p->ur_id);
        }

        if (isset($p->bdp_agrupador)) {
            $pagadoCancelado->setBdp_agrupador($p->bdp_agrupador);
        }

        if (isset($p->pma_fecha)) {
            $pagadoCancelado->setPma_fecha($p->pma_fecha);
        }

        if (isset($p->tmc_id)) {
            $pagadoCancelado->setTmc_id($p->tmc_id);
        }

        if (isset($p->bit_observacion)) {
            $pagadoCancelado->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $pagadoCancelado->setSis_id($p->sis_id);
        } else {
            $pagadoCancelado->setSis_id('SCG');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $pagadoCancelado->setEjercicio($p->ejercicio);
        } else {
            $pagadoCancelado->setEjercicio(2025);
        }

        $pagadoCancelado->execute();

        $return = new stdClass();
        $return->bandera = $pagadoCancelado->getBandera();
        $return->mensaje = $pagadoCancelado->getMensaje();

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
