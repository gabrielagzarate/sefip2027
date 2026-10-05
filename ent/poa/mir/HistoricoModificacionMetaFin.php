<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class HistoricoModificacionMetaFin extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $pmi_id; //
    protected $mtf_id; //
    protected $tri_id; //Trimestre al que pertenece el valor registrado tomado de cat_Trimestre
    protected $mtf_anio; //El año
    protected $mtf_valor; //
    protected $mtf_original; //
    protected $mtf_modificado; //
    protected $mtf_tipoCalendario; //
    protected $mtf_trimestreCierre; //Indica el numero de trimestre alque pertenece el registro
    protected $usr_id; //Usuario que inserto el registro

    /******************************************************

    METODOS GET

     ***************************************************** */

    public function getPmi_id()
    {
        return $this->pmi_id->getVal();
    }

    public function getMtf_id()
    {
        return $this->mtf_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getAnio()
    {
        return $this->mtf_anio->getVal();
    }

    public function getValor()
    {
        return $this->mtf_valor->getVal();
    }

    public function getOriginal()
    {
        return $this->mtf_original->getVal();
    }

    public function getModificado()
    {
        return $this->mtf_modificado->getVal();
    }

    public function getTipoCalendario()
    {
        return $this->mtf_tipoCalendario->getVal();
    }

    public function getTrimestreCierre()
    {
        return $this->mtf_trimestreCierre->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************

    METODOS SET

     ***************************************************** */

    public function setPmi_id($val)
    {
        $this->pmi_id->setVal($val);
    }

    public function setMtf_id($val)
    {
        $this->mtf_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setAnio($val)
    {
        $this->mtf_anio->setVal($val);
    }

    public function setValor($val)
    {
        $this->mtf_valor->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->mtf_original->setVal($val);
    }

    public function setModificado($val)
    {
        $this->mtf_modificado->setVal($val);
    }

    public function setTipoCalendario($val)
    {
        $this->mtf_tipoCalendario->setVal($val);
    }

    public function setTrimestreCierre($val)
    {
        $this->mtf_trimestreCierre->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

    public function staPmi_id($val)
    {
        $this->pmi_id->staVal($val);
    }

    public function staMtf_id($val)
    {
        $this->mtf_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staAnio($val)
    {
        $this->mtf_anio->staVal($val);
    }

    public function staValor($val)
    {
        $this->mtf_valor->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->mtf_original->staVal($val);
    }

    public function staModificado($val)
    {
        $this->mtf_modificado->staVal($val);
    }

    public function staTipoCalendario($val)
    {
        $this->mtf_tipoCalendario->staVal($val);
    }

    public function staTrimestreCierre($val)
    {
        $this->mtf_trimestreCierre->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /******************************************************

    CONSTRUCTOR

     ***************************************************** */

    public function __construct()
    {
        $mtf = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(['tabla' => 'aux_HistoricoModificacionMetaFin']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pmi_id = new Atributo('numero', 'NO', 'PRI', 'pmi_id');
        $this->mtf_id = new Atributo('numero', 'NO', 'PRI', 'mtf_id');
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'tri_id');
        $this->mtf_anio = new Atributo('numero', 'NO', '', 'mtf_anio');
        $this->mtf_valor = new Atributo('numero', 'NO', '', 'mtf_valor');
        $this->mtf_original = new Atributo('numero', 'NO', '', 'mtf_original');
        $this->mtf_modificado = new Atributo('numero', 'NO', '', 'mtf_modificado');
        $this->mtf_tipoCalendario = new Atributo('cadena', 'NO', '', 'mtf_tipoCalendario');
        $this->mtf_trimestreCierre = new Atributo('numero', 'NO', '', 'mtf_trimestreCierre');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($mtf->pmi_id) && !empty($mtf->mem_id)) {
            $this->setpmi_id($mtf->pmi_id);
            $this->setMtf_id($mtf->mem_id);
            $this->setTri_id($mtf->tri_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $mtf = new HistoricoModificacionMetaFin();
        if (isset($p->pmi_id)) {
            $mtf->setPmi_id($p->pmi_id);
        }

        if (isset($p->mtf_id)) {
            $mtf->setMtf_id($p->mtf_id);
        }

        if (isset($p->trimestreCierre)) {
            $mtf->setTri_id($p->trimestreCierre);
        }

        if (isset($p->anio)) {
            $mtf->setAnio($p->anio);
        }

        if (isset($p->valor)) {
            $mtf->setValor($p->valor);
        }

        if (isset($p->original)) {
            $mtf->setOriginal($p->original);
        }

        if (isset($p->modificado)) {
            $mtf->setModificado($p->modificado);
        }

        if (isset($p->tipoCalendario)) {
            $mtf->setTipoCalendario($p->tipoCalendario);
        }

        if (isset($p->trimestreCierre)) {
            $mtf->setTrimestreCierre($p->trimestreCierre);
        }

        if (isset($p->usr_id)) {
            $mtf->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':$mtf->insert();
                return $mtf->getUltimoId();
                break;
            case 'b':$mtf->delete();
                break;
            case 'c':$mtf->update();
                break;
        }
    }
}
