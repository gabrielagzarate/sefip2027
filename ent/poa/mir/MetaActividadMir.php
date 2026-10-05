<?php

require_once dirname(__FILE__) . '/../../Entidad.php';
require_once dirname(__FILE__) . '/MetaActividadTrimestreMir.php';
require_once dirname(__FILE__) . '/HistoricoMetaActividadMir.php';

class MetaActividadMir extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //Identificador del Producto Intermedio Indicador
    protected $macm_id; //Identificador de la Meta Actividad MIR
    protected $macm_descripcion; //Descripcion de la meta MIR
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
    protected $macm_alcanzado; //Total anual de los trimestres alcanzado
    protected $macm_numerador;
    protected $macm_denominador;
    protected $macm_triEvaluacionInicial; //Indica el numero de trimestre en el que fue dada de alta la meta.
    protected $usr_id; //Usuario que inserto el registro
    protected $metaTrimestreMir; //MetaMirTrimestre

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
    public function getDescripcion()
    {
        return $this->macm_descripcion->getVal();
    }

    public function getLb()
    {
        return $this->macm_lb->getVal();
    }

    public function getLbvalor()
    {
        return $this->macm_lbValor->getVal();
    }

    public function getLbIndicador()
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

    public function getAnterioinidicador()
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

    public function setDescripcion($val)
    {
        $this->macm_descripcion->setVal(addslashes($val));
    }

    public function setLb($val)
    {
        $this->macm_lb->setVal($val);
    }

    public function setLbvalor($val)
    {
        $this->macm_lbValor->setVal($val);
    }

    public function setLbIndicador($val)
    {
        $this->macm_lbIndicador->setVal($val);
    }

    public function setAnterior($val)
    {
        $this->macm_anterior->setVal($val);
    }

    public function setAnteriovalor($val)
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
    public function staMacm_id($val)
    {
        $this->macm_id->staVal($val);
    }
    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staDescripcion($val)
    {
        $this->macm_descripcion->staVal($val);
    }

    public function staLb($val)
    {
        $this->macm_lb->staVal($val);
    }

    public function staLbvalor($val)
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

    public function staAnterioindicador($val)
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

        parent::__construct(['tabla' => 'cat_MetaActividadMir']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->pii_id = new Atributo('numero', 'NO', 'PRI', 'pii_id');
        $this->macm_id = new Atributo('numero', 'NO', 'PRI', 'macm_id');
        $this->macm_descripcion = new Atributo('cadena', 'NO', '', 'macm_descripcion');
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
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
        $this->metaTrimestreMir = new MetaActividadTrimestreMir($macm);

        if (!empty($macm->gpo_id) && !empty($macm->ur_id) && !empty($macm->eri_id) && !empty($macm->macm_id)) {
            $this->setGpo_id($macm->gpo_id);
            $this->setUr_id($macm->ur_id);
            $this->setPii_id($macm->eri_id);
            $this->setMacm_id($macm->macm_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $parametros = [];
        $macm = new MetaActividadMir();

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

        if (isset($p->descripcion)) {
            $macm->setDescripcion($p->descripcion);
        }

        if (isset($p->lb)) {
            $macm->setLb($p->lb);
        }

        if (isset($p->lbValor)) {
            $macm->setLbvalor($p->lbValor);
        }

        if (isset($p->lbIndicador)) {
            $macm->setLbIndicador($p->lbIndicador);
        }

        if (isset($p->anterior)) {
            $macm->setAnterior($p->anterior);
        }

        if (isset($p->anteriorValor)) {
            $macm->setAnteriovalor($p->anteriorValor);
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

        if (isset($p->usr_id)) {
            $macm->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        // ASIGNAR VARIBLES
        $macm->conectar();
        $macm->setSql('SET @bit_observacion=?,@usr_id = ?,@usr_ip = INET_ATON( ? );');
        $parametros = [
            $p->observacion,
            $_SESSION[_S]['usr_id'],
            $ip,
        ];
        $macm->consultar($parametros);

        switch ($p->abc) {
            case 'a':
                $macm->insert(false);
                $macm->setMacm_id($macm->getUltimoId());
                $p->macm_id = $macm->getUltimoId();
                MetaActividadMir::abcTrimestres($p, $macm);
                HistoricoMetaActividadMir::abc($p);
                return $macm->getUltimoId();
                break;
            case 'b':
                HistoricoMetaActividadMir::abc($p);
                MetaActividadMir::abcTrimestres($p, $macm);
                $macm->delete(false);
                break;
            case 'c':$macm->update(false);
                MetaActividadMir::abcTrimestres($p, $macm);
                HistoricoMetaActividadMir::abc($p);
                break;
        }
    }

    private static function abcTrimestres($p, $macm)
    {
        for ($trimestre = 1; $trimestre <= 4; $trimestre++) {
            $metaTrimestre = new stdClass();
            $metaTrimestre->pii_id = $macm->getPii_id();
            $metaTrimestre->gpo_id = $macm->getGpo_id();
            $metaTrimestre->ur_id = $macm->getUr_id();
            $metaTrimestre->macm_id = $macm->getMacm_id();
            $metaTrimestre->tri_id = $trimestre;
            switch ($trimestre) {
                case 1:
                    $metaTrimestre->original = isset($p->ori_trimestre1) ? $p->ori_trimestre1 : 0;
                    $metaTrimestre->modificado = isset($p->mod_trimestre1) ? $p->mod_trimestre1 : 0;
                    break;
                case 2:
                    $metaTrimestre->original = isset($p->ori_trimestre2) ? $p->ori_trimestre2 : 0;
                    $metaTrimestre->modificado = isset($p->mod_trimestre2) ? $p->mod_trimestre2 : 0;
                    break;
                case 3:
                    $metaTrimestre->original = isset($p->ori_trimestre3) ? $p->ori_trimestre3 : 0;
                    $metaTrimestre->modificado = isset($p->mod_trimestre3) ? $p->mod_trimestre3 : 0;

                    break;
                case 4:
                    $metaTrimestre->original = isset($p->ori_trimestre4) ? $p->ori_trimestre4 : 0;
                    $metaTrimestre->modificado = isset($p->mod_trimestre4) ? $p->mod_trimestre4 : 0;

                    break;
            }
            $metaTrimestre->abc = $p->abc;
            MetaActividadTrimestreMir::abc($metaTrimestre);
        }
    }

    public function getOriTrimestre($trim)
    {
        if ($this->metaTrimestreMir->getMacm_id()) {
            $this->metaTrimestreMir->setTri_id($trim);
            $this->metaTrimestreMir->select();

            return $this->metaTrimestreMir->getOriginal();
        } else {
            return 0;
        }
    }

    public function getModTrimestre($trim)
    {
        if ($this->metaTrimestreMir->getMacm_id()) {
            $this->metaTrimestreMir->setTri_id($trim);
            $this->metaTrimestreMir->select();

            return $this->metaTrimestreMir->getModificado();
        } else {
            return 0;
        }
    }
}
