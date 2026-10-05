<?php

/**
 * Author:
 * Created: August 23, 2024
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */
require_once dirname(__FILE__) . '/../StoreProcedure.php';

class sp_AutorizarModificacionMeta2026 extends StoreProcedure
{
    /*     * ****************************************************
    ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $in_omo_id; //
    protected $in_mme_gpo_id; //
    protected $in_mme_ur_id; //
    protected $in_mme_id; //
    protected $in_mme_Subsecretaria; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

    /*     * ****************************************************
    METODOS GET
     * **************************************************** */

    public function getOmo_id()
    {
        return $this->in_omo_id->getVal();
    }

    public function getMme_gpo_id()
    {
        return $this->in_mme_gpo_id->getVal();
    }

    public function getMme_ur_id()
    {
        return $this->in_mme_ur_id->getVal();
    }

    public function getMme_id()
    {
        return $this->in_mme_id->getVal();
    }

    public function getMme_Subsecretaria()
    {
        return $this->in_mme_Subsecretaria->getVal();
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

    public function setOmo_id($val)
    {
        $this->in_omo_id->setVal($val);
    }

    public function setMme_gpo_id($val)
    {
        $this->in_mme_gpo_id->setVal($val);
    }

    public function setMme_ur_id($val)
    {
        $this->in_mme_ur_id->setVal($val);
    }

    public function setMme_id($val)
    {
        $this->in_mme_id->setVal($val);
    }

    public function setMme_Subsecretaria($val)
    {
        $this->in_mme_Subsecretaria->setVal($val);
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

    public function staOmo_id($val)
    {
        $this->in_omo_id->staVal($val);
    }

    public function staMme_gpo_id($val)
    {
        $this->in_mme_gpo_id->staVal($val);
    }

    public function staMme_ur_id($val)
    {
        $this->in_mme_ur_id->staVal($val);
    }

    public function staMme_id($val)
    {
        $this->in_mme_id->staVal($val);
    }

    public function staMme_Subsecretaria($val)
    {
        $this->in_mme_Subsecretaria->staVal($val);
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

        parent::__construct(['procedimientoAlmacenado' => BD_CTRL . '.sp_AutorizarModificacionMeta']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->in_omo_id = new Atributo('caracter', 'NO', 'PRI', 'in_omo_id', 1);
        $this->in_mme_gpo_id = new Atributo('numero', 'NO', 'PRI', 'in_mme_gpo_id', 1);
        $this->in_mme_ur_id = new Atributo('numero', 'NO', 'PRI', 'in_mme_ur_id', 2);
        $this->in_mme_id = new Atributo('numero', 'NO', 'PRI', 'in_mme_id', 10);
        $this->in_mme_Subsecretaria = new Atributo('numero', 'NO', 'PRI', 'in_mme_Subsecretaria', 1);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }
    /*     * ****************************************************
    FUNCIONES ESTÁTICAS
     * **************************************************** */

    public static function call($p)
    {

        $autorizarModificacionMeta = new sp_AutorizarModificacionMeta();
        if (isset($p->omo_id)) {
            $autorizarModificacionMeta->setOmo_id($p->omo_id);
        }

        if (isset($p->mme_gpo_id)) {
            $autorizarModificacionMeta->setMme_gpo_id($p->mme_gpo_id);
        }

        if (isset($p->mme_ur_id)) {
            $autorizarModificacionMeta->setMme_ur_id($p->mme_ur_id);
        }

        if (isset($p->mme_id)) {
            $autorizarModificacionMeta->setMme_id($p->mme_id);
        }

        if (isset($p->mme_Subsecretaria)) {
            $autorizarModificacionMeta->setMme_Subsecretaria($p->mme_Subsecretaria);
        }

        if (isset($p->bit_observacion)) {
            $autorizarModificacionMeta->setBit_observacion($p->bit_observacion);
        }

        if (isset($p->sis_id)) {
            $autorizarModificacionMeta->setSis_id($p->sis_id);
        } else {
            $autorizarModificacionMeta->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $autorizarModificacionMeta->setEjercicio($p->ejercicio);
        } else {
            $autorizarModificacionMeta->setEjercicio(APP_EJERCICIO);
        }

        $autorizarModificacionMeta->execute();

        $return = new stdClass();
        $return->bandera = $autorizarModificacionMeta->getBandera();
        $return->mensaje = $autorizarModificacionMeta->getMensaje();

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
