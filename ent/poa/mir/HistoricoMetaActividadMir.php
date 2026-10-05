<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class HistoricoMetaActividadMir extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //Identificador del Producto Intermedio Indicador
    protected $macm_id; //Identificador de la Meta Actividad MIR
    protected $tri_id; //
    protected $macm_lb;
    protected $macm_lbValor;
    protected $macm_lbIndicador;
    protected $macm_anterior;
    protected $macm_anteriorValor;
    protected $macm_anteriorIndicador;
    protected $macm_anio; //Registra el año que se toma como referencia del mem_valor
    protected $macm_valor; //Valor de meta estimada
    protected $macm_original; //Total anual de los trimestres original
    protected $macm_modificado; //Total anual de los trimestres modificado
    protected $macm_alcanzado;
    protected $macm_numerador;
    protected $macm_denominador;
    protected $macm_triEvaluacionInicial; //Indica el numero de trimestre en el que fue dada de alta la meta.
    protected $macm_numeradorT1;
    protected $macm_numeradorT2;
    protected $macm_numeradorT3;
    protected $macm_numeradorT4;
    protected $macm_denominadorT1;
    protected $macm_denominadorT2;
    protected $macm_denominadorT3;
    protected $macm_denominadorT4;
    protected $macm_modificadoT1; //
    protected $macm_modificadoT2; //
    protected $macm_modificadoT3; //
    protected $macm_modificadoT4; //
    protected $usr_id; //Usuario que inserto el registro

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

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getMacm_id()
    {
        return $this->macm_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getlb()
    {
        return $this->macm_lb->getVal();
    }

    public function getlbValor()
    {
        return $this->macm_lbValor->getVal();
    }

    public function getlbindicador()
    {
        return $this->macm_lbIndicador->getVal();
    }

    public function getAnterior()
    {
        return $this->macm_anterior->getVal();
    }

    public function getAnteriorvalor()
    {
        return $this->macm_anteriorValor->getVal();
    }

    public function getAnteriorindicador()
    {
        return $this->macm_anteriorIndicador->getVal();
    }

    public function getAnio()
    {
        return $this->macm_anio->getVal();
    }

    public function getValor()
    {
        return $this->macm_valor->getVal();
    }

    public function getOriginal()
    {
        return $this->macm_original->getVal();
    }

    public function getModificado()
    {
        return $this->macm_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->macm_alcanzado->getVal();
    }

    public function getNumerador()
    {
        return $this->macm_numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->macm_denominador->getVal();
    }

    public function getTriEvaluacionInicial()
    {
        return $this->macm_triEvaluacionInicial->getVal();
    }

    public function getNumeradorT1()
    {
        return $this->macm_numeradorT1->getVal();
    }

    public function getNumeradorT2()
    {
        return $this->macm_numeradorT2->getVal();
    }

    public function getNumeradorT3()
    {
        return $this->macm_numeradorT3->getVal();
    }

    public function getNumeradorT4()
    {
        return $this->macm_numeradorT4->getVal();
    }

    public function getDenominadorT1()
    {
        return $this->macm_denominadorT1->getVal();
    }

    public function getDenominadorT2()
    {
        return $this->macm_denominadorT2->getVal();
    }

    public function getDenominadorT3()
    {
        return $this->macm_denominadorT3->getVal();
    }

    public function getDenominadorT4()
    {
        return $this->macm_denominadorT4->getVal();
    }

    public function getModificadoT1()
    {
        return $this->macm_modificadoT1->getVal();
    }

    public function getModificadoT2()
    {
        return $this->macm_modificadoT2->getVal();
    }

    public function getModificadoT3()
    {
        return $this->macm_modificadoT3->getVal();
    }

    public function getModificadoT4()
    {
        return $this->macm_modificadoT4->getVal();
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

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setMacm_id($val)
    {
        $this->macm_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setlb($val)
    {
        $this->macm_lb->setVal($val);
    }

    public function setlbValor($val)
    {
        $this->macm_lbValor->setVal($val);
    }

    public function setlbIndicador($val)
    {
        $this->macm_lbIndicador->setVal($val);
    }

    public function setAnterior($val)
    {
        $this->macm_anterior->setVal($val);
    }

    public function setAnteriorvalor($val)
    {
        $this->macm_anteriorValor->setVal($val);
    }

    public function setAnteriorindicador($val)
    {
        $this->macm_anteriorIndicador->setVal($val);
    }

    public function setAnio($val)
    {
        $this->macm_anio->setVal($val);
    }

    public function setValor($val)
    {
        $this->macm_valor->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->macm_original->setVal($val);
    }

    public function setModificado($val)
    {
        $this->macm_modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->macm_alcanzado->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->macm_numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->macm_denominador->setVal($val);
    }

    public function setTriEvaluacionInicial($val)
    {
        $this->macm_triEvaluacionInicial->setVal($val);
    }

    public function setNumeradorT1($val)
    {
        $this->macm_numeradorT1->setVal($val);
    }

    public function setNumeradorT2($val)
    {
        $this->macm_numeradorT2->setVal($val);
    }

    public function setNumeradorT3($val)
    {
        $this->macm_numeradorT3->setVal($val);
    }

    public function setNumeradorT4($val)
    {
        $this->macm_numeradorT4->setVal($val);
    }

    public function setDenominadorT1($val)
    {
        $this->macm_denominadorT1->setVal($val);
    }

    public function setDenominadorT2($val)
    {
        $this->macm_denominadorT2->setVal($val);
    }

    public function setDenominadorT3($val)
    {
        $this->macm_denominadorT3->setVal($val);
    }

    public function setDenominadorT4($val)
    {
        $this->macm_denominadorT4->setVal($val);
    }

    public function setModificadoT1($val)
    {
        $this->macm_modificadoT1->setVal($val);
    }

    public function setModificadoT2($val)
    {
        $this->macm_modificadoT2->setVal($val);
    }

    public function setModificadoT3($val)
    {
        $this->macm_modificadoT3->setVal($val);
    }

    public function setModificadoT4($val)
    {
        $this->macm_modificadoT4->setVal($val);
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

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staMacm_id($val)
    {
        $this->macm_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staLb($val)
    {
        $this->macm_lb->staVal($val);
    }

    public function staLbValor($val)
    {
        $this->macm_lbValor->staVal($val);
    }

    public function staLbIndicador($val)
    {
        $this->macm_lbIndicador->staVal($val);
    }

    public function staAnterior($val)
    {
        $this->macm_anterior->staVal($val);
    }

    public function staAnteriorvalor($val)
    {
        $this->macm_anteriorValor->staVal($val);
    }

    public function staAnteriorindicador($val)
    {
        $this->macm_anteriorIndicador->staVal($val);
    }

    public function staAnio($val)
    {
        $this->macm_anio->staVal($val);
    }

    public function staValor($val)
    {
        $this->macm_valor->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->macm_original->staVal($val);
    }

    public function staModificado($val)
    {
        $this->macm_modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->macm_alcanzado->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->macm_numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->macm_denominador->staVal($val);
    }

    public function staTriEvaluacionInicial($val)
    {
        $this->macm_triEvaluacionInicial->staVal($val);
    }

    public function staNumeradorT1($val)
    {
        $this->macm_numeradorT1->staVal($val);
    }

    public function staNumeradorT2($val)
    {
        $this->macm_numeradorT2->staVal($val);
    }

    public function staNumeradorT3($val)
    {
        $this->macm_numeradorT3->staVal($val);
    }

    public function staNumeradorT4($val)
    {
        $this->macm_numeradorT4->staVal($val);
    }

    public function staDenominadorT1($val)
    {
        $this->macm_denominadorT1->staVal($val);
    }

    public function staDenominadorT2($val)
    {
        $this->macm_denominadorT2->staVal($val);
    }

    public function staDenominadorT3($val)
    {
        $this->macm_denominadorT3->staVal($val);
    }

    public function staDenominadorT4($val)
    {
        $this->macm_denominadorT4->staVal($val);
    }

    public function staModificadoT1($val)
    {
        $this->macm_modificadoT1->staVal($val);
    }

    public function staModificadoT2($val)
    {
        $this->macm_modificadoT2->staVal($val);
    }

    public function staModificadoT3($val)
    {
        $this->macm_modificadoT3->staVal($val);
    }

    public function staModificadoT4($val)
    {
        $this->macm_modificadoT4->staVal($val);
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
        $macm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(['tabla' => 'aux_HistoricoMetaActividadMir']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->pii_id = new Atributo('numero', 'NO', 'PRI', 'pii_id');
        $this->macm_id = new Atributo('numero', 'NO', 'PRI', 'macm_id');
        $this->tri_id = new Atributo('numero', 'NO', 'PRI', 'tri_id');
        $this->macm_lb = new Atributo('numero', 'NO', '', 'macm_lb');
        $this->macm_lbValor = new Atributo('numero', 'NO', '', 'macm_lbValor');
        $this->macm_lbIndicador = new Atributo('numero', 'NO', '', 'macm_lbIndicador');
        $this->macm_anterior = new Atributo('numero', 'NO', '', 'macm_anterior');
        $this->macm_anteriorValor = new Atributo('numero', 'NO', '', 'macm_anteriorValor');
        $this->macm_anteriorIndicador = new Atributo('numero', 'NO', '', 'macm_anteriorIndicador');
        $this->macm_anio = new Atributo('numero', 'NO', '', 'macm_anio');
        $this->macm_valor = new Atributo('numero', 'NO', '', 'macm_valor');
        $this->macm_original = new Atributo('numero', 'NO', '', 'macm_original');
        $this->macm_modificado = new Atributo('numero', 'NO', '', 'macm_modificado');
        $this->macm_alcanzado = new Atributo('numero', 'NO', '', 'macm_alcanzado');
        $this->macm_numerador = new Atributo('numero', 'NO', '', 'macm_numerador');
        $this->macm_denominador = new Atributo('numero', 'NO', '', 'macm_denominador');
        $this->macm_triEvaluacionInicial = new Atributo('numero', 'NO', '', 'macm_triEvaluacionInicial');
        $this->macm_numeradorT1 = new Atributo('numero', 'NO', '', 'macm_numeradorT1');
        $this->macm_numeradorT2 = new Atributo('numero', 'NO', '', 'macm_numeradorT2');
        $this->macm_numeradorT3 = new Atributo('numero', 'NO', '', 'macm_numeradorT3');
        $this->macm_numeradorT4 = new Atributo('numero', 'NO', '', 'macm_numeradorT4');
        $this->macm_denominadorT1 = new Atributo('numero', 'NO', '', 'macm_denominadorT1');
        $this->macm_denominadorT2 = new Atributo('numero', 'NO', '', 'macm_denominadorT2');
        $this->macm_denominadorT3 = new Atributo('numero', 'NO', '', 'macm_denominadorT3');
        $this->macm_denominadorT4 = new Atributo('numero', 'NO', '', 'macm_denominadorT4');
        $this->macm_modificadoT1 = new Atributo('numero', 'NO', '', 'macm_modificadoT1');
        $this->macm_modificadoT2 = new Atributo('numero', 'NO', '', 'macm_modificadoT2');
        $this->macm_modificadoT3 = new Atributo('numero', 'NO', '', 'macm_modificadoT3');
        $this->macm_modificadoT4 = new Atributo('numero', 'NO', '', 'macm_modificadoT4');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($macm->gpo_id) && !empty($macm->ur_id) && !empty($macm->eri_id) && !empty($macm->macm_id) && isset($macm->tri_id)) {
            $this->setGpo_id($macm->gpo_id);
            $this->setUr_id($macm->ur_id);
            $this->setPii_id($macm->eri_id);
            $this->setMacm_id($macm->macm_id);
            $this->setTri_id($macm->tri_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $parametros = [];
        $macm = new HistoricoMetaActividadMir();

        if (isset($p->gpo_id)) {
            $macm->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $macm->setUr_id($p->ur_id);
        }

        if (isset($p->eri_id)) {
            $macm->setPii_id($p->eri_id);
        }

        if (isset($p->macm_id)) {
            $macm->setMacm_id($p->macm_id);
        }

        if (isset($p->tri_id)) {
            $macm->setTri_id($p->tri_id);
        }

        if (isset($p->lb)) {
            $macm->setLb($p->lb);
        }

        if (isset($p->lbValor)) {
            $macm->setlbValor($p->lbValor);
        }

        if (isset($p->lbIndicador)) {
            $macm->setlbIndicador($p->lbIndicador);
        }

        if (isset($p->anterior)) {
            $macm->setAnterior($p->anterior);
        }

        if (isset($p->anteriorValor)) {
            $macm->setAnteriorvalor($p->anteriorValor);
        }

        if (isset($p->anteriorIndicador)) {
            $macm->setAnteriorindicador($p->anteriorIndicador);
        }

        if (isset($p->anio)) {
            $macm->setAnio($p->anio);
        }

        if (isset($p->macm_valor)) {
            $macm->setValor($p->macm_valor);
        }

        if (isset($p->original)) {
            $macm->setOriginal($p->original);
        }

        if (isset($p->modificado)) {
            $macm->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $macm->setAlcanzado($p->alcanzado);
        }

        if (isset($p->numerador)) {
            $macm->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $macm->setDenominador($p->denominador);
        }

        if (isset($p->trimestreCierre)) {
            $macm->setTriEvaluacionInicial($p->trimestreCierre);
        }

        if (isset($p->num_trimestre1)) {
            $macm->setNumeradorT1($p->num_trimestre1);
        }

        if (isset($p->num_trimestre2)) {
            $macm->setNumeradorT2($p->num_trimestre2);
        }

        if (isset($p->num_trimestre3)) {
            $macm->setNumeradorT3($p->num_trimestre3);
        }

        if (isset($p->num_trimestre4)) {
            $macm->setNumeradorT4($p->num_trimestre4);
        }

        if (isset($p->den_trimestre1)) {
            $macm->setDenominadorT1($p->den_trimestre1);
        }

        if (isset($p->den_trimestre2)) {
            $macm->setDenominadorT2($p->den_trimestre2);
        }

        if (isset($p->den_trimestre3)) {
            $macm->setDenominadorT3($p->den_trimestre3);
        }

        if (isset($p->den_trimestre4)) {
            $macm->setDenominadorT4($p->den_trimestre4);
        }

        if (isset($p->mod_trimestre1)) {
            $macm->setModificadoT1($p->mod_trimestre1);
        }

        if (isset($p->mod_trimestre2)) {
            $macm->setModificadoT2($p->mod_trimestre2);
        }

        if (isset($p->mod_trimestre3)) {
            $macm->setModificadoT3($p->mod_trimestre3);
        }

        if (isset($p->mod_trimestre4)) {
            $macm->setModificadoT4($p->mod_trimestre4);
        }

        if (isset($p->usr_id)) {
            $macm->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        // ASIGNAR VARIBLES
        $macm->conectar();
        $macm->setSql('SET @pass_procedure = "casc9n3cd930dc0", @bit_observacion="Modificación desde entidad HistoricoMetaActividadMIR",@usr_id = ?,@usr_ip = INET_ATON( ? );');
        $parametros = [
            $_SESSION[_S]['usr_id'],
            $ip,
        ];
        $macm->consultar($parametros);

        switch ($p->abc) {
            case 'a':
                $macm->insert(false);
                return $macm->getUltimoId();
                break;
            case 'b':
                $macm->delete();
                break;
            case 'c':
                $macm->update(false);
                break;
        }
    }
}
