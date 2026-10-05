<?php
require_once dirname(__FILE__) . "/../../Entidad.php";
require_once dirname(__FILE__) . "/MetaActividadTrimestre.php";

class MetaActividad extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $pii_id; // Vinculación Producto Intermedio-Indicador
    protected $epg_id; //Indica el numero de Obra Presupuestal , ( UR,Programa, Subprograma, Proyecto, ObraActividad), depende de enc_Programatico
    protected $descripcion; //Descripcion de la meta MIR
    protected $anio; //Registra el año que se toma como referencia del mem_valor
    protected $valor; //valor de meta estimada
    protected $original; //Total anual de los trimestres original
    protected $modificado; //Total anual de los trimestres modificado
    protected $alcanzado; //Total anual de los trimestres alcanzado
    protected $tipoCalendario; //indica sumable  (S) y No aplica  (N)
    protected $agrupadorPresupuestal; //inidica que es (1) o no (2) la meta mas significativa
    protected $triEvaluacionInicial; //indica el numero de trimestre en el que fue dada de alta la meta.
    protected $usr_id; //
    protected $tri_id;
    protected $metaTrimestre;

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }

    public function getDescripcion()
    {
        return $this->descripcion->getVal();
    }

    public function getAnio()
    {
        return $this->anio->getVal();
    }

    public function getValor()
    {
        return $this->valor->getVal();
    }

    public function getOriginal()
    {
        return $this->original->getVal();
    }

    public function getModificado()
    {
        return $this->modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->alcanzado->getVal();
    }

    public function getTipoCalendario()
    {
        return $this->tipoCalendario->getVal();
    }

    public function getAgrupadorPresupuestal()
    {
        return $this->agrupadorPresupuestal->getVal();
    }

    public function getTriEvaluacionInicial()
    {
        return $this->triEvaluacionInicial->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    // ATRIBUTOS DE METAACTIVIDADTRIMESTE
    public function getOriTrimestre1()
    {
        $this->metaTrimestre->setTri_id(1);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getOriginal();
    }

    public function getOriTrimestre2()
    {
        $this->metaTrimestre->setTri_id(2);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getOriginal();
    }

    public function getOriTrimestre3()
    {
        $this->metaTrimestre->setTri_id(3);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getOriginal();
    }

    public function getOriTrimestre4()
    {
        $this->metaTrimestre->setTri_id(4);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getOriginal();
    }

    public function getModTrimestre1()
    {
        $this->metaTrimestre->setTri_id(1);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getModificado();
    }

    public function getModTrimestre2()
    {
        $this->metaTrimestre->setTri_id(2);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getModificado();
    }

    public function getModTrimestre3()
    {
        $this->metaTrimestre->setTri_id(3);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getModificado();
    }

    public function getModTrimestre4()
    {
        $this->metaTrimestre->setTri_id(4);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getModificado();
    }

    public function getAlcTrimestre1()
    {
        $this->metaTrimestre->setTri_id(1);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getAlcanzado();
    }

    public function getAlcTrimestre2()
    {
        $this->metaTrimestre->setTri_id(2);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getAlcanzado();
    }

    public function getAlcTrimestre3()
    {
        $this->metaTrimestre->setTri_id(3);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getAlcanzado();
    }

    public function getAlcTrimestre4()
    {
        $this->metaTrimestre->setTri_id(4);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getAlcanzado();
    }

    public function getEstadoCapTrim1()
    {
        $this->metaTrimestre->setTri_id(1);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getCapturaAvance();
    }

    public function getEstadoCapTrim2()
    {
        $this->metaTrimestre->setTri_id(2);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getCapturaAvance();
    }

    public function getEstadoCapTrim3()
    {
        $this->metaTrimestre->setTri_id(3);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getCapturaAvance();
    }

    public function getEstadoCapTrim4()
    {
        $this->metaTrimestre->setTri_id(4);
        $this->metaTrimestre->select();

        return $this->metaTrimestre->getCapturaAvance();
    }
    /******************************************************

    METODOS SET

     ******************************************************/

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }

    public function setDescripcion($val)
    {
        $this->descripcion->setVal($val);
    }

    public function setAnio($val)
    {
        $this->anio->setVal($val);
    }

    public function setValor($val)
    {
        $this->valor->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->original->setVal($val);
    }

    public function setModificado($val)
    {
        $this->modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->alcanzado->setVal($val);
    }

    public function setTipoCalendario($val)
    {
        $this->tipoCalendario->setVal($val);
    }

    public function setAgrupadorPresupuestal($val)
    {
        $this->agrupadorPresupuestal->setVal($val);
    }

    public function setTriEvaluacionInicial($val)
    {
        $this->triEvaluacionInicial->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }

    public function staDescripcion($val)
    {
        $this->descripcion->staVal($val);
    }

    public function staAnio($val)
    {
        $this->anio->staVal($val);
    }

    public function staValor($val)
    {
        $this->valor->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->original->staVal($val);
    }

    public function staModificado($val)
    {
        $this->modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->alcanzado->staVal($val);
    }

    public function staTipoCalendario($val)
    {
        $this->tipoCalendario->staVal($val);
    }

    public function staAgrupadorPresupuestal($val)
    {
        $this->agrupadorPresupuestal->staVal($val);
    }

    public function staTriEvaluacionInicial($val)
    {
        $this->triEvaluacionInicial->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {
        $mac = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass());

        parent::__construct(array("tabla" => "cat_MetaActividad"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pii_id = new Atributo("numero", "NO", "PRI", "pii_id");
        $this->epg_id = new Atributo("numero", "NO", "PRI", "epg_id");
        $this->descripcion = new Atributo("cadena", "NO", "", "mac_descripcion");
        $this->anio = new Atributo("numero", "NO", "", "mac_anio");
        $this->valor = new Atributo("numero", "NO", "", "mac_valor");
        $this->original = new Atributo("numero", "NO", "", "mac_original");
        $this->modificado = new Atributo("numero", "NO", "", "mac_modificado");
        $this->alcanzado = new Atributo("numero", "NO", "", "mac_alcanzado");
        $this->tipoCalendario = new Atributo("cadena", "NO", "", "mac_tipoCalendario");
        $this->agrupadorPresupuestal = new Atributo("numero", "NO", "", "mac_agrupadorPresupuestal");
        $this->triEvaluacionInicial = new Atributo("numero", "NO", "", "mac_triEvaluacionInicial");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->metaTrimestre = new MetaActividadTrimestre($mac);

        if (!empty($mac->pii_id) && !empty($mac->epg_id)) {
            $this->setPii_id($mac->pii_id);
            $this->setEpg_id($mac->epg_id);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $mac = new MetaActividad();
        if (isset($p->pii_id)) {
            $mac->setPii_id($p->pii_id);
        }

        if (isset($p->epg_id)) {
            $mac->setEpg_id($p->epg_id);
        }

        if (isset($p->descripcion)) {
            $mac->setDescripcion($p->descripcion);
        }

        if (isset($p->anio)) {
            $mac->setAnio($p->anio);
        }

        if (isset($p->valor)) {
            $mac->setValor($p->valor);
        }

        if (isset($p->original)) {
            $mac->setOriginal($p->original);
        }

        if (isset($p->modificado)) {
            $mac->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mac->setAlcanzado($p->alcanzado);
        }

        if (isset($p->tipoCalendario)) {
            $mac->setTipoCalendario($p->tipoCalendario);
        }

        if (isset($p->agrupadorPresupuestal)) {
            $mac->setAgrupadorPresupuestal($p->agrupadorPresupuestal);
        }

        if (isset($p->triEvaluacionInicial)) {
            $mac->setTriEvaluacionInicial($p->triEvaluacionInicial);
        }

        if (isset($p->usr_id)) {
            $mac->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $mac->insert();
                return $mac->getUltimoId();
                break;
            case "b":
                $mac->delete();
                break;
            case "c":
                $mac->update();
                break;
        }
    }
}
