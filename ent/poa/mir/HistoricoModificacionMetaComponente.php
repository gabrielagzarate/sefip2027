<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class HistoricoModificacionMetaComponente extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //
    protected $ur_id; //
    protected $prgs_id; //
    protected $sprs_id; //
    protected $epfi_id; //
    protected $tri_id; //
    protected $mco_lb; //Año de la linea Base o Referencia Base
    protected $mco_lbValor; //Valor de la Linea Base
    protected $mco_lbIndicador; //Valor del Indicador
    protected $mco_anterior; //Indica el año anterior
    protected $mco_anteriorValor; //Indica el valor del año anterior
    protected $mco_anteriorIndicador; //Indica el valor del indicador del año anterior
    protected $mco_anio; //Año actual
    protected $mco_original; //Valor del año actual
    protected $mco_numerador; //Valor del numerador
    protected $mco_denominador; //Valor del denominador
    protected $mco_modificado; //Valor modificado
    protected $mco_alcanzado; //Valor alcanzado
    protected $mco_trimestreCierre; //Numero de trimestre  en que que se cierra
    protected $mcot_numeradorT1; //
    protected $mcot_numeradorT2; //
    protected $mcot_numeradorT3; //
    protected $mcot_numeradorT4; //
    protected $mcot_denominadorT1; //
    protected $mcot_denominadorT2; //
    protected $mcot_denominadorT3; //
    protected $mcot_denominadorT4; //
    protected $mcot_modificadoT1; //
    protected $mcot_modificadoT2; //
    protected $mcot_modificadoT3; //
    protected $mcot_modificadoT4; //
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ***************************************************** */

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

    public function getEpfi_id()
    {
        return $this->epfi_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getLb()
    {
        return $this->mco_lb->getVal();
    }

    public function getLbValor()
    {
        return $this->mco_lbValor->getVal();
    }

    public function getLbIndicador()
    {
        return $this->mco_lbIndicador->getVal();
    }

    public function getAnterior()
    {
        return $this->mco_anterior->getVal();
    }

    public function getAnteriorValor()
    {
        return $this->mco_anteriorValor->getVal();
    }

    public function getAnteriorIndicador()
    {
        return $this->mco_anteriorIndicador->getVal();
    }

    public function getAnio()
    {
        return $this->mco_anio->getVal();
    }

    public function getOriginal()
    {
        return $this->mco_original->getVal();
    }

    public function getNumerador()
    {
        return $this->mco_numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->mco_denominador->getVal();
    }

    public function getModificado()
    {
        return $this->mco_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->mco_alcanzado->getVal();
    }

    public function getTrimestreCierre()
    {
        return $this->mco_trimestreCierre->getVal();
    }

    public function getNumeradorT1()
    {
        return $this->mcot_numeradorT1->getVal();
    }

    public function getNumeradorT2()
    {
        return $this->mcot_numeradorT2->getVal();
    }

    public function getNumeradorT3()
    {
        return $this->mcot_numeradorT3->getVal();
    }

    public function getNumeradorT4()
    {
        return $this->mcot_numeradorT4->getVal();
    }

    public function getDenominadorT1()
    {
        return $this->mcot_denominadorT1->getVal();
    }

    public function getDenominadorT2()
    {
        return $this->mcot_denominadorT2->getVal();
    }

    public function getDenominadorT3()
    {
        return $this->mcot_denominadorT3->getVal();
    }

    public function getDenominadorT4()
    {
        return $this->mcot_denominadorT4->getVal();
    }

    public function getModificadoT1()
    {
        return $this->mcot_modificadoT1->getVal();
    }

    public function getModificadoT2()
    {
        return $this->mcot_modificadoT2->getVal();
    }

    public function getModificadoT3()
    {
        return $this->mcot_modificadoT3->getVal();
    }

    public function getModificadoT4()
    {
        return $this->mcot_modificadoT4->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************

    METODOS SET

     ***************************************************** */

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

    public function setEpfi_id($val)
    {
        $this->epfi_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setLb($val)
    {
        $this->mco_lb->setVal($val);
    }

    public function setLbValor($val)
    {
        $this->mco_lbValor->setVal($val);
    }

    public function setLbIndicador($val)
    {
        $this->mco_lbIndicador->setVal($val);
    }

    public function setAnterior($val)
    {
        $this->mco_anterior->setVal($val);
    }

    public function setAnteriorValor($val)
    {
        $this->mco_anteriorValor->setVal($val);
    }

    public function setAnteriorIndicador($val)
    {
        $this->mco_anteriorIndicador->setVal($val);
    }

    public function setAnio($val)
    {
        $this->mco_anio->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->mco_original->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->mco_numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->mco_denominador->setVal($val);
    }

    public function setModificado($val)
    {
        $this->mco_modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->mco_alcanzado->setVal($val);
    }

    public function setTrimestreCierre($val)
    {
        $this->mco_trimestreCierre->setVal($val);
    }

    public function setNumeradorT1($val)
    {
        $this->mcot_numeradorT1->setVal($val);
    }

    public function setNumeradorT2($val)
    {
        $this->mcot_numeradorT2->setVal($val);
    }

    public function setNumeradorT3($val)
    {
        $this->mcot_numeradorT3->setVal($val);
    }

    public function setNumeradorT4($val)
    {
        $this->mcot_numeradorT4->setVal($val);
    }

    public function setDenominadorT1($val)
    {
        $this->mcot_denominadorT1->setVal($val);
    }

    public function setDenominadorT2($val)
    {
        $this->mcot_denominadorT2->setVal($val);
    }

    public function setDenominadorT3($val)
    {
        $this->mcot_denominadorT3->setVal($val);
    }

    public function setDenominadorT4($val)
    {
        $this->mcot_denominadorT4->setVal($val);
    }

    public function setModificadoT1($val)
    {
        $this->mcot_modificadoT1->setVal($val);
    }

    public function setModificadoT2($val)
    {
        $this->mcot_modificadoT2->setVal($val);
    }

    public function setModificadoT3($val)
    {
        $this->mcot_modificadoT3->setVal($val);
    }

    public function setModificadoT4($val)
    {
        $this->mcot_modificadoT4->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

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

    public function staEpfi_id($val)
    {
        $this->epfi_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staLb($val)
    {
        $this->mco_lb->staVal($val);
    }

    public function staLbValor($val)
    {
        $this->mco_lbValor->staVal($val);
    }

    public function staLbIndicador($val)
    {
        $this->mco_lbIndicador->staVal($val);
    }

    public function staAnterior($val)
    {
        $this->mco_anterior->staVal($val);
    }

    public function staAnteriorValor($val)
    {
        $this->mco_anteriorValor->staVal($val);
    }

    public function staAnteriorIndicador($val)
    {
        $this->mco_anteriorIndicador->staVal($val);
    }

    public function staAnio($val)
    {
        $this->mco_anio->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->mco_original->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->mco_numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->mco_denominador->staVal($val);
    }

    public function staModificado($val)
    {
        $this->mco_modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->mco_alcanzado->staVal($val);
    }

    public function staTrimestreCierre($val)
    {
        $this->mco_trimestreCierre->staVal($val);
    }

    public function staNumeradorT1($val)
    {
        $this->mcot_numeradorT1->staVal($val);
    }

    public function staNumeradorT2($val)
    {
        $this->mcot_numeradorT2->staVal($val);
    }

    public function staNumeradorT3($val)
    {
        $this->mcot_numeradorT3->staVal($val);
    }

    public function staNumeradorT4($val)
    {
        $this->mcot_numeradorT4->staVal($val);
    }

    public function staDenominadorT1($val)
    {
        $this->mcot_denominadorT1->staVal($val);
    }

    public function staDenominadorT2($val)
    {
        $this->mcot_denominadorT2->staVal($val);
    }

    public function staDenominadorT3($val)
    {
        $this->mcot_denominadorT3->staVal($val);
    }

    public function staDenominadorT4($val)
    {
        $this->mcot_denominadorT4->staVal($val);
    }

    public function staModificadoT1($val)
    {
        $this->mcot_modificadoT1->staVal($val);
    }

    public function staModificadoT2($val)
    {
        $this->mcot_modificadoT2->staVal($val);
    }

    public function staModificadoT3($val)
    {
        $this->mcot_modificadoT3->staVal($val);
    }

    public function staModificadoT4($val)
    {
        $this->mcot_modificadoT4->staVal($val);
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
        $hm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(['tabla' => 'aux_HistoricoModificacionMetaComponente']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'prgs_id');
        $this->sprs_id = new Atributo('numero', 'NO', 'PRI', 'sprs_id');
        $this->epfi_id = new Atributo('numero', 'NO', 'PRI', 'epfi_id');
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'tri_id');
        $this->mco_lb = new Atributo('numero', 'NO', '', 'mco_lb');
        $this->mco_lbValor = new Atributo('numero', 'NO', '', 'mco_lbValor');
        $this->mco_lbIndicador = new Atributo('numero', 'NO', '', 'mco_lbIndicador');
        $this->mco_anterior = new Atributo('numero', 'NO', '', 'mco_anterior');
        $this->mco_anteriorValor = new Atributo('numero', 'NO', '', 'mco_anteriorValor');
        $this->mco_anteriorIndicador = new Atributo('numero', 'NO', '', 'mco_anteriorIndicador');
        $this->mco_anio = new Atributo('numero', 'NO', '', 'mco_anio');
        $this->mco_original = new Atributo('numero', 'NO', '', 'mco_original');
        $this->mco_numerador = new Atributo('numero', 'NO', '', 'mco_numerador');
        $this->mco_denominador = new Atributo('numero', 'NO', '', 'mco_denominador');
        $this->mco_modificado = new Atributo('numero', 'NO', '', 'mco_modificado');
        $this->mco_alcanzado = new Atributo('numero', 'NO', '', 'mco_alcanzado');
        $this->mco_trimestreCierre = new Atributo('numero', 'NO', '', 'mco_trimestreCierre');
        $this->mcot_numeradorT1 = new Atributo('numero', 'NO', '', 'mcot_numeradorT1');
        $this->mcot_numeradorT2 = new Atributo('numero', 'NO', '', 'mcot_numeradorT2');
        $this->mcot_numeradorT3 = new Atributo('numero', 'NO', '', 'mcot_numeradorT3');
        $this->mcot_numeradorT4 = new Atributo('numero', 'NO', '', 'mcot_numeradorT4');
        $this->mcot_denominadorT1 = new Atributo('numero', 'NO', '', 'mcot_denominadorT1');
        $this->mcot_denominadorT2 = new Atributo('numero', 'NO', '', 'mcot_denominadorT2');
        $this->mcot_denominadorT3 = new Atributo('numero', 'NO', '', 'mcot_denominadorT3');
        $this->mcot_denominadorT4 = new Atributo('numero', 'NO', '', 'mcot_denominadorT4');
        $this->mcot_modificadoT1 = new Atributo('numero', 'NO', '', 'mcot_modificadoT1');
        $this->mcot_modificadoT2 = new Atributo('numero', 'NO', '', 'mcot_modificadoT2');
        $this->mcot_modificadoT3 = new Atributo('numero', 'NO', '', 'mcot_modificadoT3');
        $this->mcot_modificadoT4 = new Atributo('numero', 'NO', '', 'mcot_modificadoT4');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
        if (!empty($hm->gpo_id) && !empty($hm->ur_id) && !empty($hm->prgs_id) && !empty($hm->sprs_id) && !empty($hm->epfi_id)) {
            $this->setGpo_id($hm->gpo_id);
            $this->setUr_id($hm->ur_id);
            $this->setPrgs_id($hm->prgs_id);
            $this->setSprs_id($hm->sprs_id);
            $this->setEpfi_id($hm->epfi_id);
            $this->setTri_id($hm->tri_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $hm = new HistoricoModificacionMetaComponente();
        if (isset($p->gpo_id)) {
            $hm->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $hm->setUr_id($p->ur_id);
        }

        if (isset($p->prgs_id)) {
            $hm->setPrgs_id($p->prgs_id);
        }

        if (isset($p->sprs_id)) {
            $hm->setSprs_id($p->sprs_id);
        }

        if (isset($p->epfi_id)) {
            $hm->setEpfi_id($p->epfi_id);
        }

        if (isset($p->tri_id)) {
            $hm->setTri_id($p->tri_id);
        }

        if (isset($p->lb)) {
            $hm->setLb($p->lb);
        }

        if (isset($p->lbValor)) {
            $hm->setLbValor($p->lbValor);
        }

        if (isset($p->lbIndicador)) {
            $hm->setLbIndicador($p->lbIndicador);
        }

        if (isset($p->anterior)) {
            $hm->setAnterior($p->anterior);
        }

        if (isset($p->anteriorValor)) {
            $hm->setAnteriorValor($p->anteriorValor);
        }

        if (isset($p->anteriorIndicador)) {
            $hm->setAnteriorIndicador($p->anteriorIndicador);
        }

        if (isset($p->anio)) {
            $hm->setAnio($p->anio);
        }

        if (isset($p->original)) {
            $hm->setOriginal($p->original);
        }

        if (isset($p->numerador)) {
            $hm->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $hm->setDenominador($p->denominador);
        }

        if (isset($p->modificado)) {
            $hm->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $hm->setAlcanzado($p->alcanzado);
        }

        if (isset($p->trimestreCierre)) {
            $hm->setTrimestreCierre($p->trimestreCierre);
        }

        if (isset($p->numeradorT1)) {
            $hm->setNumeradorT1($p->numeradorT1);
        }

        if (isset($p->num_trimestre2)) {
            $hm->setNumeradorT2($p->num_trimestre2);
        }

        if (isset($p->numeradorT3)) {
            $hm->setNumeradorT3($p->numeradorT3);
        }

        if (isset($p->num_trimestre4)) {
            $hm->setNumeradorT4($p->num_trimestre4);
        }

        if (isset($p->denominadorT1)) {
            $hm->setDenominadorT1($p->denominadorT1);
        }

        if (isset($p->den_trimestre2)) {
            $hm->setDenominadorT2($p->den_trimestre2);
        }

        if (isset($p->denominadorT3)) {
            $hm->setDenominadorT3($p->denominadorT3);
        }

        if (isset($p->den_trimestre4)) {
            $hm->setDenominadorT4($p->den_trimestre4);
        }

        if (isset($p->modificadoT1)) {
            $hm->setModificadoT1($p->modificadoT1);
        }

        if (isset($p->ori_trimestre2)) {
            $hm->setModificadoT2($p->ori_trimestre2);
        }

        if (isset($p->modificadoT3)) {
            $hm->setModificadoT3($p->modificadoT3);
        }

        if (isset($p->ori_trimestre4)) {
            $hm->setModificadoT4($p->ori_trimestre4);
        }

        if (isset($p->usr_id)) {
            $hm->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':$hm->insert();
                return $hm->getUltimoId();
                break;
            case 'b':$hm->delete();
                break;
            case 'c':$hm->update();
                break;
        }
    }
}
