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

class sp_VinculaSubprogramaUr extends StoreProcedure
{
    /******************************************************
                 ATRIBUTOS DE LA CLASE
    ******************************************************/
    protected $gpo_id;	//
    protected $ur_id;	//
    protected $prgs_id;	//
    protected $sprs_id;	//
    protected $validaEstado;	//
    protected $mov;	//
    protected $sis_id;	//
    protected $bit_observacion;	//
    protected $ejercicio;	//

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
    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getSprs_id()
    {
        return $this->sprs_id->getVal();
    }
    public function getValidaEstado()
    {
        return $this->validaEstado->getVal();
    }
    public function getMov()
    {
        return $this->mov->getVal();
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
    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setSprs_id($val)
    {
        $this->sprs_id->setVal($val);
    }
    public function setValidaEstado($val)
    {
        $this->validaEstado->setVal($val);
    }
    public function setMov($val)
    {
        $this->mov->setVal($val);
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
    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staSprs_id($val)
    {
        $this->sprs_id->staVal($val);
    }
    public function staValidaEstado($val)
    {
        $this->validaEstado->staVal($val);
    }
    public function staMov($val)
    {
        $this->mov->staVal($val);
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

        parent::__construct(['procedimientoAlmacenado' => 'sp_VinculaSubprogramaUr']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'in_gpo_id', 1);
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'in_ur_id', 2);
        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'in_prgs_id', 3);
        $this->sprs_id = new Atributo('numero', 'NO', 'PRI', 'in_sprs_id', 9);
        $this->validaEstado = new Atributo('numero', 'NO', 'PRI', 'in_validaEstado', 1);
        $this->mov = new Atributo('cadena', 'NO', 'PRI', 'in_mov', "'V','D'");
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                   FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {

        $vinculaSubprogramaUr = new sp_VinculaSubprogramaUr();
        if (isset($p->gpo_id)) {
            $vinculaSubprogramaUr->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $vinculaSubprogramaUr->setUr_id($p->ur_id);
        }
        if (isset($p->prgs_id)) {
            $vinculaSubprogramaUr->setPrgs_id($p->prgs_id);
        }
        if (isset($p->sprs_id)) {
            $vinculaSubprogramaUr->setSprs_id($p->sprs_id);
        }
        if (isset($p->validaEstado)) {
            $vinculaSubprogramaUr->setValidaEstado($p->validaEstado);
        }
        if (isset($p->mov)) {
            $vinculaSubprogramaUr->setMov($p->mov);
        }
        if (isset($p->bit_observacion)) {
            $vinculaSubprogramaUr->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->sis_id)) {
            $vinculaSubprogramaUr->setSis_id($p->sis_id);
        } else {
            $vinculaSubprogramaUr->setSis_id('');
        }//Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $vinculaSubprogramaUr->setEjercicio($p->ejercicio);
        } else {
            $vinculaSubprogramaUr->setEjercicio(APP_EJERCICIO);
        }

        $vinculaSubprogramaUr->execute();

        $return = new stdClass();
        $return->bandera = $vinculaSubprogramaUr->getBandera();
        $return->mensaje = $vinculaSubprogramaUr->getMensaje();

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
