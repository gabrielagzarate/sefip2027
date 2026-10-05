<?php

/**
 * Author:
 * Created: September 17, 2019
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . "/../StoreProcedure.php";

class sp_AutorizarModificacion extends StoreProcedure
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $omo_id;
    protected $mod_gpo_id;
    protected $mod_ur_id;
    protected $mod_id;
    protected $mod_Subsecretaria;
    protected $sis_id;
    protected $bit_observacion;
    protected $ejercicio;

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getOmo_id()
    {
        return $this->omo_id->getVal();
    }
    public function getMod_gpo_id()
    {
        return $this->mod_gpo_id->getVal();
    }
    public function getMod_ur_id()
    {
        return $this->mod_ur_id->getVal();
    }
    public function getMod_id()
    {
        return $this->mod_id->getVal();
    }
    public function getMod_Subsecretaria()
    {
        return $this->mod_Subsecretaria->getVal();
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
    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
    }
    public function setMod_gpo_id($val)
    {
        $this->mod_gpo_id->setVal($val);
    }
    public function setMod_ur_id($val)
    {
        $this->mod_ur_id->setVal($val);
    }
    public function setMod_id($val)
    {
        $this->mod_id->setVal($val);
    }
    public function setMod_Subsecretaria($val)
    {
        $this->mod_Subsecretaria->setVal($val);
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
    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
    }
    public function staMod_gpo_id($val)
    {
        $this->mod_gpo_id->staVal($val);
    }
    public function staMod_ur_id($val)
    {
        $this->mod_ur_id->staVal($val);
    }
    public function staMod_id($val)
    {
        $this->mod_id->staVal($val);
    }
    public function staMod_Subsecretaria($val)
    {
        $this->mod_Subsecretaria->staVal($val);
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

        parent::__construct(array("procedimientoAlmacenado" => "sp_AutorizarModificacion"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->omo_id = new Atributo("caracter", "NO", "PRI", "in_omo_id", 1);
        $this->mod_gpo_id = new Atributo("numero", "NO", "PRI", "in_mod_gpo_id", 1);
        $this->mod_ur_id = new Atributo("numero", "NO", "PRI", "in_mod_ur_id", 2);
        $this->mod_id = new Atributo("numero", "NO", "PRI", "in_mod_id", 10);
        $this->mod_Subsecretaria = new Atributo("numero", "NO", "PRI", "in_mod_Subsecretaria", 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $autorizarModificacion = new sp_AutorizarModificacion();
        if (isset($p->omo_id)) {
            $autorizarModificacion->setOmo_id($p->omo_id);
        }

        if (isset($p->mod_gpo_id)) {
            $autorizarModificacion->setMod_gpo_id($p->mod_gpo_id);
        }

        if (isset($p->mod_ur_id)) {
            $autorizarModificacion->setMod_ur_id($p->mod_ur_id);
        }

        if (isset($p->mod_id)) {
            $autorizarModificacion->setMod_id($p->mod_id);
        }

        if (isset($p->mod_Subsecretaria)) {
            $autorizarModificacion->setMod_Subsecretaria($p->mod_Subsecretaria);
        }

        if (isset($p->bit_observacion)) {
            $autorizarModificacion->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {$autorizarModificacion->setSis_id($p->sis_id);} else { $autorizarModificacion->setSis_id('SCP');} //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {$autorizarModificacion->setEjercicio($p->ejercicio);} else { $autorizarModificacion->setEjercicio(APP_EJERCICIO);}

        $autorizarModificacion->execute();

        $return = new stdClass();
        $return->bandera = $autorizarModificacion->getBandera();
        $return->mensaje = $autorizarModificacion->getMensaje();

        return $return;
    }

    /******************************************************
    FUNCIONES SOBRECARGA
     ******************************************************/
    public function execute($conectar = true, $error = false)
    {

        parent::execute($conectar, $error);

        $return = new stdClass();
        $return->bandera = $this->getBandera();
        $return->mensaje = $this->getMensaje();

        return $return;
    }

}
