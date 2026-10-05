<?php

/**
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: April 15, 2019
 * Comment: Cierre de mes contable y respaldo de la información para los reportes LDF
 * Example:
 * Class Parameters:
 *
 * Considerations:
 *
 */

require_once dirname(__FILE__) . "/../../StoreProcedure.php";

class sp_CerrarMesContable extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $msc_id; //
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
        parent::__construct(array("procedimientoAlmacenado" => "sp_CerrarMesContable"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "in_gpo_id", 1);
        $this->ur_id = new Atributo("numero", "NO", "PRI", "in_ur_id", 2);
        $this->msc_id = new Atributo("numero", "NO", "PRI", "in_msc_id", 3);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $cerrarMesContable = new sp_CerrarMesContable();
        if (isset($p->gpo_id)) {
            $cerrarMesContable->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $cerrarMesContable->setUr_id($p->ur_id);
        }

        if (isset($p->msc_id)) {
            $cerrarMesContable->setMsc_id($p->msc_id);
        }

        if (isset($p->bit_observacion)) {
            $cerrarMesContable->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {$cerrarMesContable->setSis_id($p->sis_id);} else { $cerrarMesContable->setSis_id('SCG');} //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {$cerrarMesContable->setEjercicio($p->ejercicio);} else { $cerrarMesContable->setEjercicio(2022);}

        $cerrarMesContable->execute();

        $return = new stdClass();
        $return->bandera = $cerrarMesContable->getBandera();
        $return->mensaje = $cerrarMesContable->getMensaje();

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
