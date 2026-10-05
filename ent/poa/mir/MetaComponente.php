<?php

require_once dirname(__FILE__) . '/../../Entidad.php';
require_once dirname(__FILE__) . '/MetaComponenteTrimestre.php';
require_once dirname(__FILE__) . '/HistoricoModificacionMetaComponente.php';

class MetaComponente extends Entidad
{
    /*         * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */

    protected $gpo_id; //
    protected $ur_id; //
    protected $prgs_id; //
    protected $sprs_id; //
    protected $epfi_id; //
    protected $mco_descripcion; //
    protected $mco_lb; //A�o de la linea Base o Referencia Base
    protected $mco_lbValor; //Valor de la Linea Base
    protected $mco_lbIndicador; //Valor del Indicador
    protected $mco_anterior; //indica el a�o anterior
    protected $mco_anteriorValor; //Indica el valor del a�o anterior
    protected $mco_anteriorIndicador; //Indica el valor del indicador del a�o anterior
    protected $mco_anio; //a�o actual
    protected $mco_original; //valor del a�o actual
    protected $mco_numerador; //valor del numerador
    protected $mco_denominador; //valor del denominador
    protected $mco_modificado; //valor modificado
    protected $mco_alcanzado; //valor alcanzado
    protected $mco_trimestreCierre; //numero de trimestre  en que que se cierra

    /*         * ****************************************************

    METODOS GET

     * **************************************************** */

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

    public function getDescripcion()
    {
        return $this->mco_descripcion->getVal();
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

    public function getTrimestre($trimestre, $campo = 'mcot_original')
    {
        $parametros = [];
        if (!empty($this->gpo_id->getVal()) && !empty($this->ur_id->getVal()) && !empty($this->prgs_id->getVal()) &&
            !empty($this->sprs_id->getVal()) && !empty($this->epfi_id->getVal())) {
            $this->conectar();
            $this->setSql('
                SELECT ' . $campo . ' FROM cat_MetaComponenteTrimestre
                WHERE gpo_id = ?
                AND ur_id = ?
                AND prgs_id = ?
                AND sprs_id = ?
                AND epfi_id = ?
                AND tri_id = ?
        ');
            $parametros = [
                $this->gpo_id->getVal(),
                $this->ur_id->getVal(),
                $this->prgs_id->getVal(),
                $this->sprs_id->getVal(),
                $this->epfi_id->getVal(),
                $trimestre,
            ];
            $this->consultar($parametros);
            $datos = $this->extraer();
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                return $datos[$campo];
            }

        }

        return null;
    }
    /*         * ****************************************************

    METODOS SET

     * **************************************************** */

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

    public function setDescripcion($val)
    {
        $this->mco_descripcion->setVal($val);
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
    /*         * ****************************************************

    METODOS STA (SET sin persistencia)

     * **************************************************** */

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

    public function staDescripcion($val)
    {
        $this->mco_descripcion->staVal($val);
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
    /*         * ****************************************************

    CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        $mco = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();
        parent::__construct(['tabla' => 'cat_MetaComponente']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'prgs_id');
        $this->sprs_id = new Atributo('numero', 'NO', 'PRI', 'sprs_id');
        $this->epfi_id = new Atributo('numero', 'NO', 'PRI', 'epfi_id');
        $this->mco_descripcion = new Atributo('cadena', 'NO', '', 'mco_descripcion');
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

        if (!empty($mco->gpo_id) && !empty($mco->ur_id) && !empty($mco->prgs_id) && !empty($mco->sprs_id) && !empty($mco->epfi_id)) {
            $this->setGpo_id($mco->gpo_id);
            $this->setUr_id($mco->ur_id);
            $this->setPrgs_id($mco->prgs_id);
            $this->setSprs_id($mco->sprs_id);
            $this->setEpfi_id($mco->epfi_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES EST�TICAS

     ***************************************************** */

    public static function abc($p)
    {

        $parametros = [];
        $mco = new MetaComponente();
        if (isset($p->gpo_id)) {
            $mco->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $mco->setUr_id($p->ur_id);
        }

        if (isset($p->prgs_id)) {
            $mco->setPrgs_id($p->prgs_id);
        }

        if (isset($p->sprs_id)) {
            $mco->setSprs_id($p->sprs_id);
        }

        if (isset($p->epfi_id)) {
            $mco->setEpfi_id($p->epfi_id);
        }

        if (isset($p->descripcion)) {
            $mco->setDescripcion($p->descripcion);
        }

        if (isset($p->lb)) {
            $mco->setLb($p->lb);
        }

        if (isset($p->lbValor)) {
            $mco->setLbValor($p->lbValor);
        }

        if (isset($p->lbIndicador)) {
            $mco->setLbIndicador($p->lbIndicador);
        }

        if (isset($p->anterior)) {
            $mco->setAnterior($p->anterior);
        }

        if (isset($p->anteriorValor)) {
            $mco->setAnteriorValor($p->anteriorValor);
        }

        if (isset($p->anteriorIndicador)) {
            $mco->setAnteriorIndicador($p->anteriorIndicador);
        }

        if (isset($p->anio)) {
            $mco->setAnio($p->anio);
        }

        if (isset($p->original)) {
            $mco->setOriginal($p->original);
        }

        if (isset($p->numerador)) {
            $mco->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $mco->setDenominador($p->denominador);
        }

        if (isset($p->modificado)) {
            $mco->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mco->setAlcanzado($p->alcanzado);
        }

        if (isset($p->trimestreCierre)) {
            $mco->setTrimestreCierre($p->trimestreCierre);
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        if (!isset($p->origen)) {
            $p->origen = '---';
        }

        switch ($p->abc) {
            case 'a':
                $mco->insert();
                MetaComponente::abcTrimestres($p, $mco);
                HistoricoModificacionMetaComponente::abc($p);
                if (!isset($p->anterior)) {
                    $p->anterior = 0;
                }

                if (!isset($p->anteriorValor)) {
                    $p->anteriorValor = 0;
                }

                if (!isset($p->anteriorIndicador)) {
                    $p->anteriorIndicador = 0;
                }

                if (!isset($p->numerador)) {
                    $p->numerador = 0;
                }

                if (!isset($p->denominador)) {
                    $p->denominador = 0;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaComponente (bit_observacion, gpo_id, ur_id, prgs_id, sprs_id, epfi_id, mco_descripcion, mco_lb, mco_lbValor, mco_lbIndicador,
                        mco_anterior, mco_anteriorValor, mco_anteriorIndicador, mco_anio, mco_original, mco_numerado, mco_denominador, mco_modificado, mco_alcanzado, mco_trimestreCierre, usr_ip, usr_id)
                    VALUES ("' . $p->observacion . '", :gpo_id, :ur_id, :prgs_id, :sprs_id, :epfi_id, :mco_descripcion,:mco_lb, :mco_lbValor, :mco_lbIndicador, :mco_anterior, :mco_anteriorValor,
                        :mco_anteriorIndicador, :mco_anio, :mco_original, :mco_numerado, :mco_denominador, :mco_modificado, :mco_alcanzado, :mco_trimestreCierre,
                        INET_ATON("' . $ip . '"), :usr_id);
                ');
                $parametros = [
                    ':gpo_id'                => $p->gpo_id,
                    ':ur_id'                 => $p->ur_id,
                    ':prgs_id'               => $p->prgs_id,
                    ':sprs_id'               => $p->sprs_id,
                    ':epfi_id'               => $p->epfi_id,
                    ':mco_descripcion'       => $p->descripcion,
                    ':mco_lb'                => $p->lb,
                    ':mco_lbValor'           => $p->lbValor,
                    ':mco_lbIndicador'       => $p->lbIndicador,
                    ':mco_anterior'          => $p->anterior,
                    ':mco_anteriorValor'     => $p->anteriorValor,
                    ':mco_anteriorIndicador' => $p->anteriorIndicador,
                    ':mco_anio'              => $p->anio,
                    ':mco_original'          => $p->original,
                    ':mco_numerado'          => $p->numerador,
                    ':mco_denominador'       => $p->denominador,
                    ':mco_modificado'        => $p->modificado,
                    ':mco_alcanzado'         => $p->alcanzado,
                    ':mco_trimestreCierre'   => $p->trimestreCierre,
                    ':usr_id'                => $_SESSION[_S]['usr_id'],
                ];

                $conexion->consultar($parametros);
                $conexion->desconectar();
                break;
            case 'b':
                MetaComponente::abcTrimestres($p, $mco);
                HistoricoModificacionMetaComponente::abc($p);
                $mco->delete();
                break;
            case 'c':
                $mco->update();
                MetaComponente::abcTrimestres($p, $mco);
                HistoricoModificacionMetaComponente::abc($p);

                if (!isset($p->auxoriginal)) {
                    $p->auxoriginal = 0;
                }

                if (!isset($p->anterior)) {
                    $p->anterior = 0;
                }

                if (!isset($p->anteriorValor)) {
                    $p->anteriorValor = 0;
                }

                if (!isset($p->anteriorIndicador)) {
                    $p->anteriorIndicador = 0;
                }

                if (!isset($p->numerador)) {
                    $p->numerador = 0;
                }

                if (!isset($p->denominador)) {
                    $p->denominador = 0;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                $fecha = date('Y-m-d');
                if (strtotime($fecha) < strtotime('2025-03-31')) {
                    $p->trimestreMod = 1;
                } elseif (strtotime($fecha) >= strtotime('2025-03-01') && strtotime($fecha) <= strtotime('2025-06-30')) {
                    $p->trimestreMod = 2;
                } elseif (strtotime($fecha) >= strtotime('2025-07-01') && strtotime($fecha) <= strtotime('2025-09-30')) {
                    $p->trimestreMod = 3;
                } elseif (strtotime($fecha) >= strtotime('2025-10-01')) {
                    $p->trimestreMod = 4;
                }
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaComponente (bit_observacion, gpo_id, ur_id, prgs_id, sprs_id, epfi_id, mco_descripcion, mco_lb, mco_lbValor, mco_lbIndicador, mco_anterior,
                        mco_anteriorValor, mco_anteriorIndicador, mco_anio, mco_original, mco_numerado, mco_denominador, mco_modificado, mco_alcanzado, mco_trimestreCierre, usr_ip, usr_id)
                    VALUES ("' . $p->observacion . '", :gpo_id, :ur_id, :prgs_id, :sprs_id, :epfi_id, :mco_descripcion, :mco_lb, :mco_lbValor, :mco_lbIndicador, :mco_anterior,
                        :mco_anteriorValor, :mco_anteriorIndicador, :mco_anio, :mco_original, :mco_numerado, :mco_denominador, :mco_modificado, :mco_alcanzado, :mco_trimestreCierre,
                        INET_ATON("' . $ip . '"), :usr_id);
                ');
                $parametros = [
                    ':gpo_id'               => $p->gpo_id,
                    'ur_id'                 => $p->ur_id,
                    'prgs_id'               => $p->prgs_id,
                    'sprs_id'               => $p->sprs_id,
                    'epfi_id'               => $p->epfi_id,
                    'mco_descripcion'       => $p->descripcion,
                    'mco_lb'                => $p->lb,
                    'mco_lbValor'           => $p->lbValor,
                    'mco_lbIndicador'       => $p->lbIndicador,
                    'mco_anterior'          => $p->anterior,
                    'mco_anteriorValor'     => $p->anteriorValor,
                    'mco_anteriorIndicador' => $p->anteriorIndicador,
                    'mco_anio'              => $p->anio,
                    'mco_original'          => $p->auxoriginal,
                    'mco_numerado'          => $p->numerador,
                    'mco_denominador'       => $p->denominador,
                    'mco_modificado'        => $p->modificado,
                    'mco_alcanzado'         => $p->alcanzado,
                    'mco_trimestreCierre'   => $p->trimestreMod,
                    'usr_id'                => $_SESSION[_S]['usr_id'],
                ];
                $conexion->consultar($parametros);
                $conexion->desconectar();
                break;
        }
    }

    private static function abcTrimestres($p, $mco)
    {
        for ($trimestre = 1; $trimestre <= 4; $trimestre++) {
            $tri = new stdClass();
            $tri->gpo_id = $mco->getGpo_id();
            $tri->ur_id = $mco->getUr_id();
            $tri->prgs_id = $mco->getPrgs_id();
            $tri->sprs_id = $mco->getSprs_id();
            $tri->epfi_id = $mco->getEpfi_id();
            $tri->tri_id = $trimestre;
            switch ($trimestre) {
                case 1:
                    if ($p->abc == 'a') {
                        $tri->original = isset($p->ori_trimestre1) ? $p->ori_trimestre1 : 0;
                    } else {
                        $tri->modificado = isset($p->ori_trimestre1) ? $p->ori_trimestre1 : 0;
                    }

                    $tri->numerador = isset($p->num_trimestre1) ? $p->num_trimestre1 : 0;
                    $tri->denominador = isset($p->den_trimestre1) ? $p->den_trimestre1 : 0;
                    break;
                case 2:
                    if ($p->abc == 'a') {
                        $tri->original = isset($p->ori_trimestre2) ? $p->ori_trimestre2 : 0;
                    } else {
                        $tri->modificado = isset($p->ori_trimestre2) ? $p->ori_trimestre2 : 0;
                    }

                    $tri->numerador = isset($p->num_trimestre2) ? $p->num_trimestre2 : 0;
                    $tri->denominador = isset($p->den_trimestre2) ? $p->den_trimestre2 : 0;
                    break;
                case 3:
                    if ($p->abc == 'a') {
                        $tri->original = isset($p->ori_trimestre3) ? $p->ori_trimestre3 : 0;
                    } else {
                        $tri->modificado = isset($p->ori_trimestre3) ? $p->ori_trimestre3 : 0;
                    }

                    $tri->numerador = isset($p->num_trimestre3) ? $p->num_trimestre3 : 0;
                    $tri->denominador = isset($p->den_trimestre3) ? $p->den_trimestre3 : 0;
                    break;
                case 4:
                    if ($p->abc == 'a') {
                        $tri->original = isset($p->ori_trimestre4) ? $p->ori_trimestre4 : 0;
                    } else {
                        $tri->modificado = isset($p->ori_trimestre4) ? $p->ori_trimestre4 : 0;
                    }

                    $tri->numerador = isset($p->num_trimestre4) ? $p->num_trimestre4 : 0;
                    $tri->denominador = isset($p->den_trimestre4) ? $p->den_trimestre4 : 0;
                    break;
            }
            $tri->abc = $p->abc;
            MetaComponenteTrimestre::abc($tri);
        }
    }
}
