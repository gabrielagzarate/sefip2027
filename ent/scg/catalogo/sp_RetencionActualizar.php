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

class sp_RetencionActualizar extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $ger_id; //
    protected $cgp_id; //
    protected $rub_id; //
    protected $cgcu_id; //
    protected $cgsc_id; //
    protected $cgc_id; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getGer_id()
    {return $this->ger_id->getVal();}
    public function getCgp_id()
    {return $this->cgp_id->getVal();}
    public function getRub_id()
    {return $this->rub_id->getVal();}
    public function getCgcu_id()
    {return $this->cgcu_id->getVal();}
    public function getCgsc_id()
    {return $this->cgsc_id->getVal();}
    public function getCgc_id()
    {return $this->cgc_id->getVal();}
    public function getSis_id()
    {return $this->sis_id;}
    public function getBit_observacion()
    {return $this->bit_observacion;}
    public function getEjercicio()
    {return $this->ejercicio;}

    /******************************************************
    METODOS SET
     ******************************************************/
    public function setGer_id($val)
    {$this->ger_id->setVal($val);}
    public function setCgp_id($val)
    {$this->cgp_id->setVal($val);}
    public function setRub_id($val)
    {$this->rub_id->setVal($val);}
    public function setCgcu_id($val)
    {$this->cgcu_id->setVal($val);}
    public function setCgsc_id($val)
    {$this->cgsc_id->setVal($val);}
    public function setCgc_id($val)
    {$this->cgc_id->setVal($val);}
    public function setSis_id($val)
    {$this->sis_id = $val;}
    public function setBit_observacion($val)
    {$this->bit_observacion = $val;}
    public function setEjercicio($val)
    {$this->ejercicio = $val;}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staGer_id($val)
    {$this->ger_id->staVal($val);}
    public function staCgp_id($val)
    {$this->cgp_id->staVal($val);}
    public function staRub_id($val)
    {$this->rub_id->staVal($val);}
    public function staCgcu_id($val)
    {$this->cgcu_id->staVal($val);}
    public function staCgsc_id($val)
    {$this->cgsc_id->staVal($val);}
    public function staCgc_id($val)
    {$this->cgc_id->staVal($val);}
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

        parent::__construct(array("procedimientoAlmacenado" => "sp_RetencionActualizar"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->ger_id = new Atributo("numero", "NO", "PRI", "in_ger_id", 1);
        $this->cgp_id = new Atributo("numero", "NO", "PRI", "in_cgp_id", 1);
        $this->rub_id = new Atributo("numero", "NO", "PRI", "in_rub_id", 2);
        $this->cgcu_id = new Atributo("numero", "NO", "PRI", "in_cgcu_id", 2);
        $this->cgsc_id = new Atributo("numero", "NO", "PRI", "in_cgsc_id", 1);
        $this->cgc_id = new Atributo("numero", "NO", "PRI", "in_cgc_id", 3);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $retencionActualizar = new sp_RetencionActualizar();
        if (isset($p->ger_id)) {
            $retencionActualizar->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $retencionActualizar->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $retencionActualizar->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $retencionActualizar->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $retencionActualizar->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->cgc_id)) {
            $retencionActualizar->setCgc_id($p->cgc_id);
        }

        if (isset($p->bit_observacion)) {
            $retencionActualizar->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {$retencionActualizar->setSis_id($p->sis_id);} else { $retencionActualizar->setSis_id('SCG');} //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {$retencionActualizar->setEjercicio($p->ejercicio);} else { $retencionActualizar->setEjercicio(APP_EJERCICIO);}

        $retencionActualizar->execute();

        $return = new stdClass();
        $return->bandera = $retencionActualizar->getBandera();
        $return->mensaje = $retencionActualizar->getMensaje();

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
