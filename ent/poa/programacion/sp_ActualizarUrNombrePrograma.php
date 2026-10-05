<?php

/**
 * Author:
 * Created: August 7, 2017
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */
require_once dirname(__FILE__) . '/../../StoreProcedure.php';

class sp_ActualizarUrNombrePrograma extends StoreProcedure
{
    /*     * ****************************************************
      ATRIBUTOS DE LA CLASE
     * **************************************************** */

    protected $accion; //
    protected $prgs_id; //
    protected $gpo_id_actual; //
    protected $ur_id_actual; //
    protected $gpo_id_nueva; //
    protected $ur_id_nueva; //
    protected $prgs_nombre_actual; //
    protected $prgs_nombre_nuevo; //
    protected $quien_valida; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /*     * ****************************************************
      METODOS GET
     * **************************************************** */

    public function getAccion()
    {
        return $this->accion->getVal();
    }

    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }

    public function getGpo_id_actual()
    {
        return $this->gpo_id_actual->getVal();
    }

    public function getUr_id_actual()
    {
        return $this->ur_id_actual->getVal();
    }

    public function getGpo_id_nueva()
    {
        return $this->gpo_id_nueva->getVal();
    }

    public function getUr_id_nueva()
    {
        return $this->ur_id_nueva->getVal();
    }

    public function getPrgs_nombre_actual()
    {
        return $this->prgs_nombre_actual->getVal();
    }

    public function getPrgs_nombre_nuevo()
    {
        return $this->prgs_nombre_nuevo->getVal();
    }

    public function getQuien_valida()
    {
        return $this->quien_valida->getVal();
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

    /*     * ****************************************************
      METODOS SET
     * **************************************************** */

    public function setAccion($val)
    {
        $this->accion->setVal($val);
    }

    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }

    public function setGpo_id_actual($val)
    {
        $this->gpo_id_actual->setVal($val);
    }

    public function setUr_id_actual($val)
    {
        $this->ur_id_actual->setVal($val);
    }

    public function setGpo_id_nueva($val)
    {
        $this->gpo_id_nueva->setVal($val);
    }

    public function setUr_id_nueva($val)
    {
        $this->ur_id_nueva->setVal($val);
    }

    public function setPrgs_nombre_actual($val)
    {
        $this->prgs_nombre_actual->setVal($val);
    }

    public function setPrgs_nombre_nuevo($val)
    {
        $this->prgs_nombre_nuevo->setVal($val);
    }

    public function setQuien_valida($val)
    {
        $this->quien_valida->setVal($val);
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

    /*     * ****************************************************
      METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staAccion($val)
    {
        $this->accion->staVal($val);
    }

    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }

    public function staGpo_id_actual($val)
    {
        $this->gpo_id_actual->staVal($val);
    }

    public function staUr_id_actual($val)
    {
        $this->ur_id_actual->staVal($val);
    }

    public function staGpo_id_nueva($val)
    {
        $this->gpo_id_nueva->staVal($val);
    }

    public function staUr_id_nueva($val)
    {
        $this->ur_id_nueva->staVal($val);
    }

    public function staPrgs_nombre_actual($val)
    {
        $this->prgs_nombre_actual->staVal($val);
    }

    public function staPrgs_nombre_nuevo($val)
    {
        $this->prgs_nombre_nuevo->staVal($val);
    }

    public function staQuien_valida($val)
    {
        $this->quien_valida->staVal($val);
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

    /*     * ****************************************************
      CONSTRUCTOR
     * **************************************************** */

    public function __construct()
    {

        parent::__construct(['procedimientoAlmacenado' => 'sp_ActualizarUrNombrePrograma']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->accion = new Atributo('numero', 'NO', 'PRI', 'in_accion', 1);
        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'in_prgs_id', 3);
        $this->gpo_id_actual = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id_actual', 1);
        $this->ur_id_actual = new Atributo('numero', 'NO', 'PRI', 'in_ur_id_actual', 2);
        $this->gpo_id_nueva = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id_nueva', 1);
        $this->ur_id_nueva = new Atributo('numero', 'NO', 'PRI', 'in_ur_id_nueva', 2);
        $this->prgs_nombre_actual = new Atributo('cadena', 'NO', '', 'in_prgs_nombre_actual', 100);
        $this->prgs_nombre_nuevo = new Atributo('cadena', 'NO', '', 'in_prgs_nombre_nuevo', 100);
        $this->quien_valida = new Atributo('numero', 'NO', 'PRI', 'in_quien_valida', 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
        $this->actualizaNombreur = 1;
    }

    /*     * ****************************************************
      FUNCIONES ESTÁTICAS
     * **************************************************** */

    public static function call($p)
    {

        $actualizarUrNombrePrograma = new sp_ActualizarUrNombrePrograma();
        if (isset($p->accion)) {
            $actualizarUrNombrePrograma->setAccion($p->accion);
        }
        if (isset($p->prgs_id)) {
            $actualizarUrNombrePrograma->setPrgs_id($p->prgs_id);
        }
        if (isset($p->gpo_id_actual)) {
            $actualizarUrNombrePrograma->setGpo_id_actual($p->gpo_id_actual);
        }
        if (isset($p->ur_id_actual)) {
            $actualizarUrNombrePrograma->setUr_id_actual($p->ur_id_actual);
        }
        if (isset($p->gpo_id_nueva)) {
            $actualizarUrNombrePrograma->setGpo_id_nueva($p->gpo_id_nueva);
        }
        if (isset($p->ur_id_nueva)) {
            $actualizarUrNombrePrograma->setUr_id_nueva($p->ur_id_nueva);
        }
        if (isset($p->prgs_nombre_actual)) {
            $actualizarUrNombrePrograma->setPrgs_nombre_actual($p->prgs_nombre_actual);
        }
        if (isset($p->prgs_nombre_nuevo)) {
            $actualizarUrNombrePrograma->setPrgs_nombre_nuevo($p->prgs_nombre_nuevo);
        }
        if (isset($p->quien_valida)) {
            $actualizarUrNombrePrograma->setQuien_valida($p->quien_valida);
        }
        if (isset($p->bit_observacion)) {
            $actualizarUrNombrePrograma->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $actualizarUrNombrePrograma->setSis_id($p->sis_id);
        } else {
            $actualizarUrNombrePrograma->setSis_id('POA');
        }//Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $actualizarUrNombrePrograma->setEjercicio($p->ejercicio);
        } else {
            $actualizarUrNombrePrograma->setEjercicio(APP_EJERCICIO);
        }

        $actualizarUrNombrePrograma->execute();

        $return = new stdClass();
        $return->bandera = $actualizarUrNombrePrograma->getBandera();
        $return->mensaje = $actualizarUrNombrePrograma->getMensaje();

        return $return;
    }

    /*     * ****************************************************
      FUNCIONES SOBRECARGA
     * **************************************************** */

    public function execute($conectar = true, $error = false)
    {

        parent::execute();

        $return = new stdClass();
        $return->bandera = $this->getBandera();
        $return->mensaje = $this->getMensaje();

        return $return;
    }
}
