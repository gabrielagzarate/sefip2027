<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class HistoricoModificacionMetaProposito extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $pmi_id; //
    protected $mpr_id; //
    protected $tri_id; //
    protected $lb; //Indica el año de referencia para la linea base
    protected $lbValor; //Indica el valor de referencia del año que es la linea base
    protected $lbIndicador; //
    protected $anterior; //Indica el año actual
    protected $anteriorValor; //Indica el valor de referencia acual
    protected $anteriorIndicador; //
    protected $anio; //
    protected $original; //
    protected $numerador; //
    protected $denominador; //
    protected $modificado; //
    protected $numeradorT1; //
    protected $denominadorT1; //
    protected $tipoCalendario; //
    protected $trimestreCierre; //Indica el trimestre al que pertenece el registro
    protected $usr_id; //Usuario que inserto el registro

    /******************************************************

    METODOS GET

     ***************************************************** */

    public function getPmi_id()
    {
        return $this->pmi_id->getVal();
    }

    public function getMpr_id()
    {
        return $this->mpr_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getLb()
    {
        return $this->lb->getVal();
    }

    public function getLbValor()
    {
        return $this->lbValor->getVal();
    }

    public function getLbIndicador()
    {
        return $this->lbIndicador->getVal();
    }

    public function getAnterior()
    {
        return $this->anterior->getVal();
    }

    public function getAnteriorValor()
    {
        return $this->anteriorValor->getVal();
    }

    public function getAnteriorIndicador()
    {
        return $this->anteriorIndicador->getVal();
    }

    public function getAnio()
    {
        return $this->anio->getVal();
    }

    public function getOriginal()
    {
        return $this->original->getVal();
    }

    public function getNumerador()
    {
        return $this->numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->denominador->getVal();
    }

    public function getModificado()
    {
        return $this->modificado->getVal();
    }

    public function getTipoCalendario()
    {
        return $this->tipoCalendario->getVal();
    }

    public function getTrimestreCierre()
    {
        return $this->trimestreCierre->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getNumeradorT1()
    {

        return $this->numeradorT1->getVal();
    }

    public function getdenominadorT1()
    {
        return $this->denominadorT1->getVal();
    }

    /******************************************************

    METODOS SET

     ***************************************************** */

    public function setPmi_id($val)
    {
        $this->pmi_id->setVal($val);
    }

    public function setMpr_id($val)
    {
        $this->mpr_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setLb($val)
    {
        $this->lb->setVal($val);
    }

    public function setLbValor($val)
    {
        $this->lbValor->setVal($val);
    }

    public function setLbIndicador($val)
    {
        $this->lbIndicador->setVal($val);
    }

    public function setAnterior($val)
    {
        $this->anterior->setVal($val);
    }

    public function setAnteriorValor($val)
    {
        $this->anteriorValor->setVal($val);
    }

    public function setAnteriorIndicador($val)
    {
        $this->anteriorIndicador->setVal($val);
    }

    public function setAnio($val)
    {
        $this->anio->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->original->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->denominador->setVal($val);
    }

    public function setModificado($val)
    {
        $this->modificado->setVal($val);
    }

    public function setTipoCalendario($val)
    {
        $this->tipoCalendario->setVal($val);
    }

    public function setTrimestreCierre($val)
    {
        $this->trimestreCierre->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    public function setNumeradorT1($numeradorT1)
    {
        $this->numeradorT1 = $numeradorT1;
    }

    public function setDenominadorT1($denominadorT1)
    {
        $this->denominadorT1 = $denominadorT1;
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

    public function staPmi_id($val)
    {
        $this->pmi_id->staVal($val);
    }

    public function staMpr_id($val)
    {
        $this->mpr_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staLb($val)
    {
        $this->lb->staVal($val);
    }

    public function staLbValor($val)
    {
        $this->lbValor->staVal($val);
    }

    public function staLbIndicador($val)
    {
        $this->lbIndicador->staVal($val);
    }

    public function staAnterior($val)
    {
        $this->anterior->staVal($val);
    }

    public function staAnteriorValor($val)
    {
        $this->anteriorValor->staVal($val);
    }

    public function staAnteriorIndicador($val)
    {
        $this->anteriorIndicador->staVal($val);
    }

    public function staAnio($val)
    {
        $this->anio->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->original->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->denominador->staVal($val);
    }

    public function staModificado($val)
    {
        $this->modificado->staVal($val);
    }

    public function staTipoCalendario($val)
    {
        $this->tipoCalendario->staVal($val);
    }

    public function staTrimestreCierre($val)
    {
        $this->trimestreCierre->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staDenominadorT1($val)
    {
        $this->denominadorT1->staVal($val);
    }

    public function staNumeradorT1($val)
    {
        $this->numeradorT1->staVal($val);
    }
    /******************************************************

    CONSTRUCTOR

     ***************************************************** */

    public function __construct()
    {
        $mpr = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(['tabla' => 'aux_HistoricoModificacionMetaProposito']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pmi_id = new Atributo('numero', 'NO', 'PRI', 'pmi_id');
        $this->mpr_id = new Atributo('numero', 'NO', 'PRI', 'mpr_id');
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'tri_id');
        $this->lb = new Atributo('numero', 'NO', '', 'mpr_lb');
        $this->lbValor = new Atributo('numero', 'NO', '', 'mpr_lbValor');
        $this->lbIndicador = new Atributo('numero', 'NO', '', 'mpr_lbIndicador');
        $this->anterior = new Atributo('numero', 'NO', '', 'mpr_anterior');
        $this->anteriorValor = new Atributo('numero', 'NO', '', 'mpr_anteriorValor');
        $this->anteriorIndicador = new Atributo('numero', 'NO', '', 'mpr_anteriorIndicador');
        $this->anio = new Atributo('numero', 'NO', '', 'mpr_anio');
        $this->original = new Atributo('numero', 'NO', '', 'mpr_original');
        $this->numerador = new Atributo('numero', 'NO', '', 'mpr_numerador');
        $this->denominador = new Atributo('numero', 'NO', '', 'mpr_denominador');
        $this->modificado = new Atributo('numero', 'NO', '', 'mpr_modificado');
        $this->numeradorT1 = new Atributo('numero', 'NO', '', 'mpr_numeradorT1');
        $this->denominadorT1 = new Atributo('numero', 'NO', '', 'mpr_denominadorT1');
        $this->tipoCalendario = new Atributo('cadena', 'NO', '', 'mpr_tipoCalendario');
        $this->trimestreCierre = new Atributo('numero', 'NO', '', 'mpr_trimestreCierre');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($mpr->eri_id) && !empty($mpr->mem_id)) {

            $this->setPmi_id($mpr->eri_id);
            $this->setMpr_id($mpr->mem_id);
            $this->setTri_id($mpr->tri_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $mpr = new HistoricoModificacionMetaProposito();
        if (isset($p->pmi_id)) {
            $mpr->setPmi_id($p->pmi_id);
        }

        if (isset($p->mpr_id)) {
            $mpr->setMpr_id($p->mpr_id);
        }

        if (isset($p->tri_id)) {
            $mpr->setTri_id($p->tri_id);
        }

        if (isset($p->lb)) {
            $mpr->setLb($p->lb);
        }

        if (isset($p->lbValor)) {
            $mpr->setLbValor($p->lbValor);
        }

        if (isset($p->lbIndicador)) {
            $mpr->setLbIndicador($p->lbIndicador);
        }

        if (isset($p->anterior)) {
            $mpr->setAnterior($p->anterior);
        }

        if (isset($p->anteriorValor)) {
            $mpr->setAnteriorValor($p->anteriorValor);
        }

        if (isset($p->anteriorIndicador)) {
            $mpr->setAnteriorIndicador($p->anteriorIndicador);
        }

        if (isset($p->anio)) {
            $mpr->setAnio($p->anio);
        }

        if (isset($p->original)) {
            $mpr->setOriginal($p->original);
        }

        if (isset($p->numerador)) {
            $mpr->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $mpr->setDenominador($p->denominador);
        }

        if (isset($p->modificado)) {
            $mpr->setModificado($p->modificado);
        }

        if (isset($p->numeradorT1)) {
            $mpr->setNumerador($p->numeradorT1);
        }

        if (isset($p->denominadorT1)) {
            $mpr->setDenominador($p->denominadorT1);
        }

        if (isset($p->tipoCalendario)) {
            $mpr->setTipoCalendario($p->tipoCalendario);
        }

        if (isset($p->trimestreCierre)) {
            $mpr->setTrimestreCierre($p->trimestreCierre);
        }

        if (isset($p->usr_id)) {
            $mpr->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':$mpr->insert();
                return $mpr->getUltimoId();
                break;
            case 'b':$mpr->delete();
                break;
            case 'c':$mpr->update();
                break;
        }
    }
}
