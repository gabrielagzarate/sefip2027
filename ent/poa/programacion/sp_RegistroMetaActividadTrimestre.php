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

class sp_RegistroMetaActividadTrimestre extends StoreProcedure
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //
    protected $epg_id; //
    protected $tri_id; //
    protected $mact_original; //
    protected $mact_modificado; //
    protected $mact_alcanzado; //
    protected $abc; //
    protected $macm_ValorDeseado; //
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
    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }
    public function getMact_original()
    {
        return $this->mact_original->getVal();
    }
    public function getMact_modificado()
    {
        return $this->mact_modificado->getVal();
    }
    public function getMact_alcanzado()
    {
        return $this->mact_alcanzado->getVal();
    }
    public function getAbc()
    {
        return $this->abc->getVal();
    }
    public function getMacm_ValorDeseado()
    {
        return $this->macm_ValorDeseado->getVal();
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
    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }
    public function setMact_original($val)
    {
        $this->mact_original->setVal($val);
    }
    public function setMact_modificado($val)
    {
        $this->mact_modificado->setVal($val);
    }
    public function setMact_alcanzado($val)
    {
        $this->mact_alcanzado->setVal($val);
    }
    public function setAbc($val)
    {
        $this->abc->setVal($val);
    }
    public function setMacm_ValorDeseado($val)
    {
        $this->macm_ValorDeseado->setVal($val);
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
    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }
    public function staMact_original($val)
    {
        $this->mact_original->staVal($val);
    }
    public function staMact_modificado($val)
    {
        $this->mact_modificado->staVal($val);
    }
    public function staMact_alcanzado($val)
    {
        $this->mact_alcanzado->staVal($val);
    }
    public function staAbc($val)
    {
        $this->abc->staVal($val);
    }
    public function staMacm_ValorDeseado($val)
    {
        $this->macm_ValorDeseado->staVal($val);
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

        parent::__construct(['procedimientoAlmacenado' => 'sp_RegistroMetaActividadTrimestre']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id', 1);
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'in_ur_id', 2);
        $this->pii_id = new Atributo('numero', 'NO', 'PRI', 'in_pii_id', 10);
        $this->epg_id = new Atributo('numero', 'NO', 'PRI', 'in_epg_id', 6);
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'in_tri_id', 1);
        $this->mact_original = new Atributo('numero', 'NO', 'PRI', 'in_mact_original', 18);
        $this->mact_modificado = new Atributo('numero', 'NO', 'PRI', 'in_mact_modificado', 18);
        $this->mact_alcanzado = new Atributo('numero', 'NO', 'PRI', 'in_mact_alcanzado', 18);
        $this->abc = new Atributo('cadena', 'NO', 'PRI', 'in_abc', "'I','U'");
        $this->macm_ValorDeseado = new Atributo('numero', 'NO', 'PRI', 'in_macm_ValorDeseado', 18);
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $registroMetaActividadTrime = new sp_RegistroMetaActividadTrimestre();
        if (isset($p->gpo_id)) {
            $registroMetaActividadTrime->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $registroMetaActividadTrime->setUr_id($p->ur_id);
        }
        if (isset($p->pii_id)) {
            $registroMetaActividadTrime->setPii_id($p->pii_id);
        }
        if (isset($p->epg_id)) {
            $registroMetaActividadTrime->setEpg_id($p->epg_id);
        }
        if (isset($p->tri_id)) {
            $registroMetaActividadTrime->setTri_id($p->tri_id);
        }
        if (isset($p->mact_original)) {
            $registroMetaActividadTrime->setMact_original($p->mact_original);
        }
        if (isset($p->mact_modificado)) {
            $registroMetaActividadTrime->setMact_modificado($p->mact_modificado);
        }
        if (isset($p->mact_alcanzado)) {
            $registroMetaActividadTrime->setMact_alcanzado($p->mact_alcanzado);
        }
        if (isset($p->abc)) {
            $registroMetaActividadTrime->setAbc($p->abc);
        }
        if (isset($p->macm_ValorDeseado)) {
            $registroMetaActividadTrime->setMacm_ValorDeseado($p->macm_ValorDeseado);
        }
        if (isset($p->bit_observacion)) {
            $registroMetaActividadTrime->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $registroMetaActividadTrime->setSis_id($p->sis_id);
        } else {
            $registroMetaActividadTrime->setSis_id('');
        } //Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $registroMetaActividadTrime->setEjercicio($p->ejercicio);
        } else {
            $registroMetaActividadTrime->setEjercicio(APP_EJERCICIO);
        }

        $registroMetaActividadTrime->execute();

        $return = new stdClass();
        $return->bandera = $registroMetaActividadTrime->getBandera();
        $return->mensaje = $registroMetaActividadTrime->getMensaje();

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
