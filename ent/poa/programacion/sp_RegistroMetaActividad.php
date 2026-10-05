<?php

/**
 * Author:
 * Created: September 29, 2017
 * Comment:
 * Example:
 * Class Parameters:
 *
 * Considerations:
 */

require_once dirname(__FILE__) . '/../../StoreProcedure.php';

class sp_RegistroMetaActividad extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //
    protected $epg_id; //
    protected $abc; //
    protected $tri_id; //
    protected $sis_id; //
    protected $bit_observacion; //
    protected $ejercicio; //

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
    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }
    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }
    public function getAbc()
    {
        return $this->abc->getVal();
    }
    public function getTri_id()
    {
        return $this->tri_id->getVal();
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
    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }
    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }
    public function setAbc($val)
    {
        $this->abc->setVal($val);
    }
    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
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
    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }
    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }
    public function staAbc($val)
    {
        $this->abc->staVal($val);
    }
    public function staTri_id($val)
    {
        $this->epg_id->staVal($val);
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

        parent::__construct(['procedimientoAlmacenado' => 'sp_RegistroMetaActividad']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id', 1);
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'in_ur_id', 2);
        $this->pii_id = new Atributo('numero', 'NO', 'PRI', 'in_pii_id', 10);
        $this->epg_id = new Atributo('numero', 'NO', 'PRI', 'in_epg_id', 6);
        $this->abc = new Atributo('cadena', 'NO', 'PRI', 'in_abc', "'I','U','D'");
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'in_tri_id', 1);
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
        $registroMetaActiv = new sp_RegistroMetaActividad();
        if (isset($p->gpo_id)) {
            $registroMetaActiv->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $registroMetaActiv->setUr_id($p->ur_id);
        }
        if (isset($p->pii_id)) {
            $registroMetaActiv->setPii_id($p->pii_id);
        }
        if (isset($p->epg_id)) {
            $registroMetaActiv->setEpg_id($p->epg_id);
        }
        if (isset($p->abc)) {
            $registroMetaActiv->setAbc($p->abc);
        }
        if (isset($p->tri_id)) {
            $registroMetaActiv->setTri_id($p->tri_id);
        }
        if (isset($p->bit_observacion)) {
            $registroMetaActiv->setBit_observacion($p->bit_observacion);
        }
        //Agregar el sistema del mensaje que necesitas
        if (isset($p->sis_id)) {
            $registroMetaActiv->setSis_id($p->sis_id);
        } else {
            $registroMetaActiv->setSis_id('');
        }
        if (isset($p->ejercicio)) {
            $registroMetaActiv->setEjercicio($p->ejercicio);
        } else {
            $registroMetaActiv->setEjercicio(APP_EJERCICIO);
        }

        $registroMetaActiv->execute();
        $return = new stdClass();
        $return->bandera = $registroMetaActiv->getBandera();
        $return->mensaje = $registroMetaActiv->getMensaje();
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
