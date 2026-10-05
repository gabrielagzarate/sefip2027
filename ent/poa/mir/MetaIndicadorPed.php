<?php

require_once dirname(__FILE__) . '/../../Entidad.php';
require_once dirname(__FILE__) . '/HistoricoModificacionMetaFin.php';

class MetaIndicadorPed extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $pmi_id; //
    protected $mtf_id; //Indentificador numerico del registro (Metas)
    protected $mtf_descripcion; //Descripcion de la meta MIR
    protected $mtf_anio; //Registra el año que se toma como referencia del mtf_valor
    protected $mtf_valor; //valor de meta estimada
    protected $mtf_original; //Total anual de los trimestres original
    protected $mtf_modificado; //Total anual de los trimestres modificado
    protected $mtf_alcanzado; //Total anual de los trimestres alcanzado
    protected $mtf_tipoCalendario; //
    protected $mtf_trimestreCierre; //
    protected $usr_id; //

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

    public function getDescripcion()
    {
        return $this->mtf_descripcion->getVal();
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
        return $this->mtf_ori_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->mtf_alcanzado->getVal();
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

    public function setDescripcion($val)
    {
        $this->mtf_descripcion->setVal($val);
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

    public function setAlcanzado($val)
    {
        $this->mtf_alcanzado->setVal($val);
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

    public function staDescripcion($val)
    {
        $this->mtf_descripcion->staVal($val);
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

    public function staAlcanzado($val)
    {
        $this->mtf_alcanzado->staVal($val);
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

        parent::__construct(['tabla' => 'cat_MetaFin']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pmi_id = new Atributo('numero', 'NO', 'PRI', 'pmi_id');
        $this->mtf_id = new Atributo('numero', 'NO', 'PRI', 'mtf_id');
        $this->mtf_descripcion = new Atributo('cadena', 'NO', '', 'mtf_descripcion');
        $this->mtf_anio = new Atributo('numero', 'NO', '', 'mtf_anio');
        $this->mtf_valor = new Atributo('numero', 'NO', '', 'mtf_valor');
        $this->mtf_original = new Atributo('numero', 'NO', '', 'mtf_original');
        $this->mtf_modificado = new Atributo('numero', 'NO', '', 'mtf_modificado');
        $this->mtf_alcanzado = new Atributo('numero', 'NO', '', 'mtf_alcanzado');
        $this->mtf_tipoCalendario = new Atributo('cadena', 'NO', '', 'mtf_tipoCalendario');
        $this->mtf_trimestreCierre = new Atributo('numero', 'NO', '', 'mtf_trimestreCierre');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($mtf->pmi_id) && !empty($mtf->mem_id)) {
            $this->setpmi_id($mtf->pmi_id);
            $this->setMtf_id($mtf->mem_id);
            $this->select();
        }
    }
    /*         * ****************************************************

    FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $parametros = [];
        $mtf = new MetaIndicadorPed();
        if (isset($p->pmi_id)) {
            $mtf->setpmi_id($p->pmi_id);
        }

        if (isset($p->mtf_id)) {
            $mtf->setMtf_id($p->mtf_id);
        }

        if (isset($p->descripcion)) {
            $mtf->setDescripcion($p->descripcion);
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

        if (isset($p->alcanzado)) {
            $mtf->setAlcanzado($p->alcanzado);
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

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        if (!isset($p->origen)) {
            $p->origen = '---';
        }

        switch ($p->abc) {
            case 'a':
                $mtf->insert();
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO cat_MetaFinTrimestre (pmi_id, mtf_id, tri_id, mtft_original, mtft_modificado, mtft_alcanzado, usr_id)
                    VALUES (:pmi_id, :mtf_id, :tri_id, :mtft_original, :mtft_modificado, :mtft_alcanzado, :usr_id);
                ');
                $parametros = [
                    ':pmi_id'          => $p->pmi_id,
                    ':mtf_id'          => 1,
                    ':tri_id'          => 4,
                    ':mtft_original'   => $p->ori_trimestre4,
                    ':mtft_modificado' => 0,
                    ':mtft_alcanzado'  => 0,
                    ':usr_id'          => 1,
                ];
                $conexion->consultar($parametros);
                HistoricoModificacionMetaFin::abc($p);
                $conexion->desconectar();
                //Insertar en bitácora
                if (!isset($p->mtf_id)) {
                    $p->mtf_id = 1;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaFin (bit_observacion, pmi_id, mtf_id, mtf_descripcion, mtf_anio, mtf_valor, mtf_original, mtf_modificado, mtf_alcanzado, mtf_tipoCalendario,
                    mtf_trimestreCierre, tri_id, mtft_original, mtft_modificado, mtft_alcanzado, usr_ip, usr_id)
                    VALUES ("' . $p->observacion . '", :pmi_id, :mtf_id, :mtf_descripcion, :mtf_anio, :mtf_valor, :mtf_original, :mtf_modificado, :mtf_alcanzado, :mtf_tipoCalendario,
                    :mtf_trimestreCierre, :tri_id, :mtft_original, :mtft_modificado, :mtft_alcanzado, INET_ATON(:usr_ip), :usr_id);
                ');
                $parametros = [
                    ':pmi_id'              => $p->pmi_id,
                    ':mtf_id'              => $p->mtf_id,
                    ':mtf_descripcion'     => $p->descripcion,
                    ':mtf_anio'            => $p->anio,
                    ':mtf_valor'           => $p->valor,
                    ':mtf_original'        => $p->original,
                    ':mtf_modificado'      => $p->modificado,
                    ':mtf_alcanzado'       => $p->alcanzado,
                    ':mtf_tipoCalendario'  => $p->tipoCalendario,
                    ':mtf_trimestreCierre' => $p->trimestreCierre,
                    ':tri_id'              => 4,
                    ':mtft_original'       => $p->ori_trimestre4,
                    ':mtft_modificado'     => 0,
                    ':mtft_alcanzado'      => 0,
                    ':usr_ip'              => $ip,
                    ':usr_id'              => $_SESSION[_S]['usr_id'],
                ];
                $conexion->consultar($parametros);
                $conexion->desconectar();
                return $mtf->getUltimoId();
                break;
            case 'b':
                $mtf->conectar();
                $mtf->setSql('SET @mip =INET_ATON(?);');
                $mtf->consultar([$ip]);
                $mtf->delete(false);
                break;
            case 'c':
                $mtf->conectar();
                $mtf->setSql('SET @mip =INET_ATON(?);');
                $mtf->consultar([$ip]);
                $mtf->update(false);
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    UPDATE cat_MetaFinTrimestre set mtft_original=' . $p->ori_trimestre4 . '
                    WHERE pmi_id=? AND tri_id=?;
                ');
                $parametros = [
                    $p->pmi_id,
                    4,
                ];
                $conexion->consultar($parametros);
                HistoricoModificacionMetaFin::abc($p);
                $conexion->desconectar();

                //Insertar en bitácora
                if (!isset($p->mtf_id)) {
                    $p->mtf_id = 1;
                }

                if (!isset($p->modificado)) {
                    $p->modificado = 0;
                }

                if (!isset($p->alcanzado)) {
                    $p->alcanzado = 0;
                }

                $fecha = date('Y-m-d');
                if (strtotime($fecha) < strtotime('2026-03-31')) {
                    $p->trimestreCierre = 1;
                } elseif (strtotime($fecha) >= strtotime('2026-03-01') && strtotime($fecha) <= strtotime('2026-06-30')) {
                    $p->trimestreCierre = 2;
                } elseif (strtotime($fecha) >= strtotime('2026-07-01') && strtotime($fecha) <= strtotime('2026-09-30')) {
                    $p->trimestreCierre = 3;
                } elseif (strtotime($fecha) >= strtotime('2026-10-01')) {
                    $p->trimestreCierre = 4;
                }
                $conexion->conectar();
                $conexion->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_MetaFin (bit_observacion, pmi_id, mtf_id, mtf_descripcion, mtf_anio, mtf_valor, mtf_original, mtf_modificado, mtf_alcanzado, mtf_tipoCalendario,
                        mtf_trimestreCierre, tri_id, mtft_original, mtft_modificado, mtft_alcanzado, usr_ip, usr_id)
                    VALUES ("' . $p->observacion . '", :pmi_id, :mtf_id, :mtf_descripcion, :mtf_anio, :mtf_valor, :mtf_original, :mtf_modificado, :mtf_alcanzado, :mtf_tipoCalendario,
                        :mtf_trimestreCierre, :tri_id, :mtft_original, :mtft_modificado, :mtft_alcanzado, INET_ATON(:usr_ip), :usr_id);
                ');
                $parametros = [
                    ':pmi_id'              => $p->pmi_id,
                    ':mtf_id'              => $p->mtf_id,
                    ':mtf_descripcion'     => $p->descripcion,
                    ':mtf_anio'            => $p->anio,
                    ':mtf_valor'           => $p->valor,
                    ':mtf_original'        => $p->original,
                    ':mtf_modificado'      => $p->modificado,
                    ':mtf_alcanzado'       => $p->alcanzado,
                    ':mtf_tipoCalendario'  => $p->tipoCalendario,
                    ':mtf_trimestreCierre' => $p->trimestreCierre,
                    ':tri_id'              => 4,
                    ':mtft_original'       => $p->ori_trimestre4,
                    ':mtft_modificado'     => 0,
                    ':mtft_alcanzado'      => 0,
                    ':usr_ip'              => $ip,
                    ':usr_id'              => $_SESSION[_S]['usr_id'],
                ];
                $conexion->consultar($parametros);
                $conexion->desconectar();
                break;
        }
    }
}
