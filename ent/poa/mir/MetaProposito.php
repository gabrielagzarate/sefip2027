<?php

require_once dirname(__FILE__) . "/../../Entidad.php";
require_once dirname(__FILE__) . "/MetaPropositoTrimestre.php";
require_once dirname(__FILE__) . "/HistoricoModificacionMetaProposito.php";

class MetaProposito extends Entidad
{

    // ================ ATRIBUTOS DE LA CLASE ================

    protected $pmi_id; //Programa Mir Indicador
    protected $mpr_id; //ID de la meta
    protected $mpr_descripcion;
    protected $mpr_lb; //Anio de referencia para la linea base
    protected $mpr_lbValor; //Valor de la Linea Base
    protected $mpr_lbIndicador; //Valor del Indicador
    protected $mpr_anterior; //Indica el anio anterior
    protected $mpr_anteriorValor; //Valor del anio anterior
    protected $mpr_anteriorIndicador; //Valor del indicador del anio anterior
    protected $mpr_anio; //Anio corriente
    protected $mpr_original; //Total anual de los trimestres original
    protected $mpr_numerador;
    protected $mpr_denominador;
    protected $mpr_modificado; //Total anual de los trimestres modificado
    protected $mpr_alcanzado; //Total anual de los trimestres alcanzado
    protected $mpr_tipoCalendario;
    protected $metaTrimestre; //Hace referencia a la tabla cat_MetaPropositoTrimestre
    protected $mpr_trimestreCierre; //Trimestre en que se registra la meta

    // ================ METODOS GET ================

    public function getPmi_id()
    {
        return $this->pmi_id->getVal();
    }

    public function getMpr_id()
    {
        return $this->mpr_id->getVal();
    }

    public function getDescripcion()
    {
        return $this->mpr_descripcion->getVal();
    }

    public function getLb()
    {
        return $this->mpr_lb->getVal();
    }

    public function getLbValor()
    {
        return $this->mpr_lbValor->getVal();
    }

    public function getLbIndicador()
    {
        return $this->mpr_lbIndicador->getVal();
    }

    public function getAnterior()
    {
        return $this->mpr_anterior->getVal();
    }

    public function getAnteriorValor()
    {
        return $this->mpr_anteriorValor->getVal();
    }

    public function getAnteriorIndicador()
    {
        return $this->mpr_anteriorIndicador->getVal();
    }

    public function getAnio()
    {
        return $this->mpr_anio->getVal();
    }

    public function getOriginal()
    {
        return $this->mpr_original->getVal();
    }

    public function getNumerador()
    {
        return $this->mpr_numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->mpr_denominador->getVal();
    }

    public function getModificado()
    {
        return $this->mpr_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->mpr_alcanzado->getVal();
    }

    public function getTipoCalendario()
    {
        return $this->mpr_tipoCalendario->getVal();
    }

    public function getOriTrimestre4()
    {
        return $this->metaTrimestre->getOriginal();
    }

    public function getNumTrimestre4()
    {
        return $this->metaTrimestre->getNumerador();
    }

    public function getDenTrimestre4()
    {
        return $this->metaTrimestre->getDenominador();
    }

    public function getTrimestreCierre()
    {
        return $this->mpr_trimestreCierre->getVal();
    }

    // ================ METODOS SET ================
    public function setPmi_id($val)
    {
        $this->pmi_id->setVal($val);
    }

    public function setMpr_id($val)
    {
        $this->mpr_id->setVal($val);
    }

    public function setDescripcion($val)
    {
        $this->mpr_descripcion->setVal($val);
    }

    public function setLb($val)
    {
        $this->mpr_lb->setVal($val);
    }

    public function setLbValor($val)
    {
        $this->mpr_lbValor->setVal($val);
    }

    public function setLbIndicador($val)
    {
        $this->mpr_lbIndicador->setVal($val);
    }

    public function setAnterior($val)
    {
        $this->mpr_anterior->setVal($val);
    }

    public function setAnteriorValor($val)
    {
        $this->mpr_anteriorValor->setVal($val);
    }

    public function setAnteriorIndicador($val)
    {
        $this->mpr_anteriorIndicador->setVal($val);
    }

    public function setAnio($val)
    {
        $this->mpr_anio->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->mpr_original->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->mpr_numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->mpr_denominador->setVal($val);
    }

    public function setModificado($val)
    {
        $this->mpr_modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->mpr_alcanzado->setVal($val);
    }

    public function setTipoCalendario($val)
    {
        $this->mpr_tipoCalendario->setVal($val);
    }

    public function setTrimestreCierre($val)
    {
        $this->mpr_trimestreCierre->setVal($val);
    }

    // ================ METODOS STA (SET sin persistencia) ================
    public function staPmi_id($val)
    {
        $this->pmi_id->staVal($val);
    }

    public function staMpr_id($val)
    {
        $this->mpr_id->staVal($val);
    }

    public function staDescripcion($val)
    {
        $this->mpr_descripcion->staVal($val);
    }

    public function staLb($val)
    {
        $this->mpr_lb->staVal($val);
    }

    public function staLbValor($val)
    {
        $this->mpr_lbValor->staVal($val);
    }

    public function staLbIndicador($val)
    {
        $this->mpr_lbIndicador->staVal($val);
    }

    public function staAnterior($val)
    {
        $this->mpr_anterior->staVal($val);
    }

    public function staAnteriorValor($val)
    {
        $this->mpr_anteriorValor->staVal($val);
    }

    public function staAnteriorIndicador($val)
    {
        $this->mpr_anteriorIndicador->staVal($val);
    }

    public function staAnio($val)
    {
        $this->mpr_anio->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->mpr_original->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->mpr_numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->mpr_denominador->staVal($val);
    }

    public function staModificado($val)
    {
        $this->mpr_modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->mpr_alcanzado->staVal($val);
    }

    public function staTipoCalendario($val)
    {
        $this->mpr_tipoCalendario->staVal($val);
    }

    public function staTrimestreCierre($val)
    {
        $this->mpr_trimestreCierre->staVal($val);
    }

    // ================ CONSTRUCTOR ================
    public function __construct()
    {
        $mpr = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();
        parent::__construct(array("tabla" => "cat_MetaProposito"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->pmi_id = new Atributo("numero", "NO", "PRI", "pmi_id");
        $this->mpr_id = new Atributo("numero", "NO", "PRI", "mpr_id");
        $this->mpr_descripcion = new Atributo("cadena", "NO", "", "mpr_descripcion");
        $this->mpr_lb = new Atributo("numero", "NO", "", "mpr_lb");
        $this->mpr_lbValor = new Atributo("numero", "NO", "", "mpr_lbValor");
        $this->mpr_lbIndicador = new Atributo("numero", "NO", "", "mpr_lbIndicador");
        $this->mpr_anterior = new Atributo("numero", "NO", "", "mpr_anterior");
        $this->mpr_anteriorValor = new Atributo("numero", "NO", "", "mpr_anteriorValor");
        $this->mpr_anteriorIndicador = new Atributo("numero", "NO", "", "mpr_anteriorIndicador");
        $this->mpr_anio = new Atributo("numero", "NO", "", "mpr_anio");
        $this->mpr_original = new Atributo("numero", "NO", "", "mpr_original");
        $this->mpr_numerador = new Atributo("numero", "NO", "", "mpr_numerador");
        $this->mpr_denominador = new Atributo("numero", "NO", "", "mpr_denominador");
        $this->mpr_modificado = new Atributo("numero", "NO", "", "mpr_modificado");
        $this->mpr_alcanzado = new Atributo("numero", "NO", "", "mpr_alcanzado");
        $this->mpr_tipoCalendario = new Atributo("cadena", "NO", "", "mpr_tipoCalendario");
        $this->mpr_trimestreCierre = new Atributo("numero", "NO", "", "mpr_trimestreCierre");

        $this->metaTrimestre = new MetaPropositoTrimestre($mpr);

        if (!empty($mpr->eri_id) && !empty($mpr->mem_id)) {

            $this->setPmi_id($mpr->eri_id);
            $this->setMpr_id($mpr->mem_id);
            $this->select();
        }
    }

    // ================ FUNCIONES ESTÁTICAS ================
    public static function abc($p)
    {
        $mpr = new MetaProposito();

        if (isset($p->pmi_id)) {
            $mpr->setPmi_id($p->pmi_id);
        }

        if (isset($p->mem_id)) {
            $mpr->setMpr_id($p->mem_id);
        }

        if (isset($p->descripcionM)) {
            $mpr->setDescripcion($p->descripcionM);
        }

        if (isset($p->mlb_anioT1)) {
            $mpr->setLb($p->mlb_anioT1);
        }

        if (isset($p->mlb_valorT1)) {
            $mpr->setLbValor($p->mlb_valorT1);
        }

        if (isset($p->mlb_valorIndicador)) {
            $mpr->setLbIndicador($p->mlb_valorIndicador);
        }

        if (isset($p->mlb_anioT0)) {
            $mpr->setAnterior($p->mlb_anioT0);
        }

        if (isset($p->mvp_valorT0)) {
            $mpr->setAnteriorValor($p->mvp_valorT0);
        }

        if (isset($p->mvp_valorIndicador)) {
            $mpr->setAnteriorIndicador($p->mvp_valorIndicador);
        }

        if (isset($p->anio)) {
            $mpr->setAnio($p->anio);
        }

        if (isset($p->mvp_valorT1)) {
            $mpr->setOriginal($p->mvp_valorT1);
        }

        if (isset($p->mlb_numerador)) {
            $mpr->setNumerador($p->mlb_numerador);
        }

        if (isset($p->mlb_denominador)) {
            $mpr->setDenominador($p->mlb_denominador);
        }

        if (isset($p->modificado)) {
            $mpr->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mpr->setAlcanzado($p->alcanzado);
        }

        if (isset($p->tipoCalendario)) {
            $mpr->setTipoCalendario($p->tipoCalendario);
        }

        if (isset($p->trimestreCierre)) {
            $mpr->setTrimestreCierre($p->trimestreCierre);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        if (!isset($p->origen)) {
            $p->origen = '---';
        }

        switch ($p->abc) {
            case "a":
                if (empty($p->mem_id)) {
                    $mpr->setMpr_id(1);
                }

                $mpr->insert();
                $p->mpr_id = $p->mem_id;
                MetaPropositoTrimestre::abc($p);
                if (!isset($p->mem_id)) {
                    $p->mem_id = 1;
                }

                if (!isset($p->mlb_valorT1)) {
                    $p->mlb_valorT1 = 0;
                }

                if (!isset($p->mlb_anioT0)) {
                    $p->mlb_anioT0 = 0;
                }

                if (!isset($p->mlb_numerador)) {
                    $p->mlb_numerador = 0;
                }

                if (!isset($p->mlb_denominador)) {
                    $p->mlb_denominador = 0;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                if (!isset($p->mlb_anioT1)) {
                    $p->alcanzado = 0;
                }

                if (!isset($p->mlb_valorIndicador)) {
                    $p->mlb_valorIndicador = 0;
                }

                if (!isset($p->mpr_anteriorIndicador)) {
                    $p->mpr_anteriorIndicador = 0;
                }

                if (!isset($p->mpr_anteriorValor)) {
                    $p->mpr_anteriorValor = 0;
                }

                if (!isset($p->mpr_lbValor)) {
                    $p->mpr_lbValor = 0;
                }

                if (!isset($p->mpr_lbIndicador)) {
                    $p->mpr_lbIndicador = 0;
                }

                if (!isset($p->mvp_valorT0)) {
                    $p->mvp_valorT0 = 0;
                }

                if (!isset($p->mvp_valorIndicador)) {
                    $p->mvp_valorIndicador = 0;
                }

                $fecha = date("Y-m-d");
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaProposito (bit_observacion, pmi_id, mpr_id, mpr_descripcion, mpr_lb, mpr_lbValor, mpr_lbIndicador, mpr_anterior, mpr_anteriorValor, mpr_anteriorIndicador, mpr_anio, mpr_original, mpr_numerador, mpr_denominador, mpr_modificado, mpr_alcanzado,  mpr_tipoCalendario, usr_ip, usr_id)
                    VALUES ("' . $p->observacion  . '",' . $p->pmi_id . ', ' . $p->mem_id . ', "' . $p->descripcionM . '", ' . $p->mlb_anioT1 . ', ' . $p->mlb_valorT1 . ', ' . $p->mlb_valorIndicador . ', ' . $p->mlb_anioT0 . ', ' . $p->mvp_valorT0 . ', ' . $p->mvp_valorIndicador . ', ' . $p->mlb_anioT1 . ', ' . $p->mvp_valorT1 . ', ' . $p->mlb_numerador . ', ' . $p->mlb_denominador . ', ' . $p->modificado . ', ' . $p->alcanzado . ', "' . $p->tipoCalendario . '", INET_ATON("' . $ip . '"), ' . $_SESSION[_S]['usr_id'] . ');
                ');

                $conexion->consultar();
                $conexion->desconectar();
                $conexion2 = new AccesoDatos();
                $conexion2->conectar();
                $conexion2->setSql('
                    INSERT INTO ' . BD_CTRL . '.aux_HistoricoModificacionMetaProposito (pmi_id, mpr_id, tri_id, mpr_lb, mpr_lbValor, mpr_lbIndicador, mpr_anterior, mpr_anteriorValor, mpr_anteriorIndicador, mpr_anio, mpr_original, mpr_numerador, mpr_denominador, mpr_modificado, mpr_numeradorT1, mpr_denominadorT1, mpr_tipoCalendario, mpr_trimestreCierre, usr_id)
                    VALUES (' . $p->pmi_id . ', ' . $p->mem_id . ', ' . $p->trimestreCierre . ', ' . $p->mlb_anioT1 . ', ' . $p->mlb_valorT1 . ', ' . $p->mlb_valorIndicador . ', ' . $p->mlb_anioT0 . ', ' . $p->mvp_valorT0 . ', ' . $p->mvp_valorIndicador . ', ' . $p->mlb_anioT1 . ', ' . $p->mvp_valorT1 . ', ' . $p->mlb_numerador . ', ' . $p->mlb_denominador . ', ' . $p->modificado . ', ' . $p->num_trimestre4 . ' , ' . $p->den_trimestre4 . ' , "' . $p->tipoCalendario . '", '. $p->trimestreCierre . ', ' . $_SESSION[_S]['usr_id'] . ');
                ');

                $conexion2->consultar();
                $conexion2->desconectar();
                return $mpr->getUltimoId();
                break;
            case "b":
                HistoricoModificacionMetaProposito::abc($p);
                MetaPropositoTrimestre::abc($p);
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaProposito (bit_observacion, pmi_id, mpr_id, mpr_descripcion, mpr_lb, mpr_lbValor, mpr_lbIndicador, mpr_anterior, mpr_anteriorValor, mpr_anteriorIndicador, mpr_anio, mpr_original, mpr_numerador, mpr_denominador, mpr_modificado, mpr_alcanzado,  mpr_tipoCalendario, tri_id, usr_ip, usr_id)
                    VALUES ("' . $p->observacion  . '",' . $p->pmi_id . ', ' . $p->mem_id . ', "", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, "S", '. $p->trimestreCierre . ', INET_ATON("' . $ip . '"), ' . $_SESSION[_S]['usr_id'] . ');
                ');

                $conexion->consultar();
                $conexion->desconectar();
                $mpr->conectar();
                $mpr->delete(false);
                break;
            case "c":
                $mpr->conectar();
                $mpr->update(false);
                $p->mpr_id = $p->mem_id;
                $p->pmi_id = $p->eri_id;
                MetaPropositoTrimestre::abc($p);
                if (!isset($p->mem_id)) {
                    $p->mem_id = 1;
                }

                if (!isset($p->mlb_valorT1)) {
                    $p->mlb_valorT1 = 0;
                }

                if (!isset($p->mlb_anioT0)) {
                    $p->mlb_anioT0 = 0;
                }

                if (!isset($p->mlb_numerador)) {
                    $p->mlb_numerador = 0;
                }

                if (!isset($p->mlb_denominador)) {
                    $p->mlb_denominador = 0;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                if (!isset($p->mlb_anioT1)) {
                    $p->alcanzado = 0;
                }

                if (!isset($p->mlb_valorIndicador)) {
                    $p->mlb_valorIndicador = 0;
                }

                if (!isset($p->mpr_anteriorIndicador)) {
                    $p->mpr_anteriorIndicador = 0;
                }

                if (!isset($p->mpr_anteriorValor)) {
                    $p->mpr_anteriorValor = 0;
                }

                if (!isset($p->mpr_lbValor)) {
                    $p->mpr_lbValor = 0;
                }

                if (!isset($p->mpr_lbIndicador)) {
                    $p->mpr_lbIndicador = 0;
                }

                if (!isset($p->mvp_valorT0)) {
                    $p->mvp_valorT0 = 0;
                }

                if (!isset($p->mvp_valorIndicador)) {
                    $p->mvp_valorIndicador = 0;
                }

                $fecha = date("Y-m-d");
                if (strtotime($fecha) < strtotime(APP_EJERCICIO ."-03-31")) {
                    $p->trimestreCierre = 1;
                } elseif (strtotime($fecha) >= strtotime(APP_EJERCICIO  ."-03-01") && strtotime($fecha) <= strtotime(APP_EJERCICIO ."-06-30")) {
                    $p->trimestreCierre = 2;
                } elseif (strtotime($fecha) >= strtotime(APP_EJERCICIO ."-07-01") && strtotime($fecha) <= strtotime(APP_EJERCICIO ."-09-30")) {
                    $p->trimestreCierre = 3;
                } elseif (strtotime($fecha) >= strtotime(APP_EJERCICIO ."-10-01")) {
                    $p->trimestreCierre = 4;
                }
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaProposito (bit_observacion, pmi_id, mpr_id, mpr_descripcion, mpr_lb, mpr_lbValor, mpr_lbIndicador, mpr_anterior, mpr_anteriorValor, mpr_anteriorIndicador, mpr_anio, mpr_original, mpr_numerador, mpr_denominador, mpr_modificado, mpr_alcanzado,  mpr_tipoCalendario, usr_ip, usr_id)
                    VALUES ("' . $p->observacion . '", ' . $p->pmi_id . ', ' . $p->mem_id . ', "' . $p->descripcionM . '", ' . $p->mlb_anioT1 . ', ' . $p->mlb_valorT1 . ', ' . $p->mlb_valorIndicador . ', ' . $p->mlb_anioT0 . ', ' . $p->mvp_valorT0 . ', ' . $p->mvp_valorIndicador . ', ' . $p->mlb_anioT1 . ', ' . $p->mvp_valorT1 . ', ' . $p->mlb_numerador . ', ' . $p->mlb_denominador . ', ' . $p->modificado . ', ' . $p->alcanzado . ',"' . $p->tipoCalendario . '", INET_ATON("' . $ip . '"), ' . $_SESSION[_S]['usr_id'] . ');
                ');
                $conexion->consultar();
                $conexion->desconectar();

                $conexion2 = new AccesoDatos();
                $conexion2->conectar();
                $conexion2->setSql('
                    UPDATE ' . BD_CTRL . '.aux_HistoricoModificacionMetaProposito
                    SET 
                    pmi_id = ' . $p->pmi_id . ',
                    mpr_id = ' . $p->mem_id . ',
                    tri_id = ' . $p->tri_id . ',
                    mpr_lb = ' . $p->mlb_anioT1 . ',
                    mpr_lbValor = ' . $p->mlb_valorT1 . ',
                    mpr_lbIndicador = ' . $p->mlb_valorIndicador . ',
                    mpr_anterior = ' . $p->mlb_anioT0 . ',
                    mpr_anteriorValor = ' . $p->mvp_valorT0 . ',
                    mpr_anteriorIndicador = ' . $p->mvp_valorIndicador . ',
                    mpr_anio = ' . $p->mlb_anioT1 . ',
                    mpr_original = ' . $p->mvp_valorT1 . ',
                    mpr_numerador = ' . $p->mlb_numerador . ',
                    mpr_denominador = ' . $p->mlb_denominador . ',
                    mpr_modificado = ' . $p->modificado . ',
                    mpr_numeradorT1 = ' . $p->num_trimestre4 . ',
                    mpr_denominadorT1 = ' . $p->den_trimestre4 . ',
                    mpr_tipoCalendario = "' . $p->tipoCalendario . '",
                    mpr_trimestreCierre = '. $p->trimestreCierre . ',
                    usr_id = ' . $_SESSION[_S]['usr_id'] . '
                    WHERE pmi_id = ' . $p->pmi_id . ' AND  tri_id = '.$p->tri_id.';
                ');
                $conexion2->consultar();
                $conexion2->desconectar();
                break;
        }
    }

}
