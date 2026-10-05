<?php

require_once dirname(__FILE__) . '/Entidad.php';

class ObraActividad extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //Grupo de la unidad responsable de la Obra Actividad. Foranea dependiente de cat_Proyecto
    protected $ur_id; //Identificador de la Unidad Responsable. Foranea dependiente de cat_Proyecto
    protected $prg_id; //Identificador del Programa del cual depende el subprograma del cual depende el proyecto del cual depende la obra/Actividad. Foranea dependiente de cat_Proyecto
    protected $spr_id; //Identificador del  subprograma del cual depende el proyecto del cual depende la obra/Actividad. Foranea dependiente de cat_Proyecto
    protected $py_id; //Identificador del  proyecto del cual depende la obra/Actividad. Foranea dependiente de cat_Proyecto
    protected $oa_id; //Identificador de la Obra/Actividad
    protected $ue_id; //Unidad Ejecutora de la Obra Actividad. Foranea dependiente de cat_UnidadEjecutora
    protected $nombre; //Nombre de la ObraActividad
    protected $oa_nombre;
    protected $hombres; //Numero de Hombres que la Obra contempla
    protected $oa_hombres;
    protected $mujeres; //numero de Mujeres que contempla la obra/actividad
    protected $oa_mujeres;
    protected $origen; //Indica de donde se genera la Obra/Actividad I importacion R Refrendo S sistema
    protected $oa_2026; //Encuadre Programatico del Ejercicio 2014 equivalente a la ObraActividad (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
    protected $anticipo; //Importe del anticipo de la Obra durante el Ejercicio
    protected $anticipo2026; //Importe de anticipos del ejercicio 2014
    protected $amortizacion; //Pagos hechos (amortizaciones) a los anticipos de la obra durante el Ejercicio
    protected $total; //Suma total de hombres y mujeres beneficiados con la Obra/Actividad
    protected $oa_total;
    protected $grupoEnfoque; //Descripcion del grupo de Enfoque de la Obra /Actividad
    protected $observacion; //ID de poa_HistorialMensaje para ObraActividad
    protected $descripcion; //ID de poa_HistorialMensaje para ObraActividad
    protected $pbf_id; //
    protected $eug_id; //Identificador del encuadre Geografico de la Obra / Actividad. Foranea Dependiente de enc_UbicacionGeografica
    protected $sts_id; //Estado del registro 1: Habilitado 2:Deshabilitado Foranea dependiente de cat_Estado
    protected $eoa_id; //Identificador del estado de la Obra/Actividad . Foranea dependiente de cat_EstadoObraActividad
    protected $toa_id; //Identicicador del tipode Obra Actividad. Foranea dependiente de cat_TipoObraActividad
    protected $numObra; //Es el numero de Obra segun Planeación
    protected $modalidadEjecucion; //Indica la modalidad de ejecucion segun Planeacion
    protected $fechaLimiteEjecucion; //Es la fecha maxima que se tiene para ejecutarse la Obra/Actividad
    protected $edoVinculacion; //Estado de la vinculación de la Actividad presupuestal con el producto Intermedio.\n1. Vinculada\n2. Enviada a Egresos\n3. Rechazada por Egresos\n4. Validada

    protected $usr_id; //

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
    public function getPrg_id()
    {
        return $this->prg_id->getVal();
    }
    public function getSpr_id()
    {
        return $this->spr_id->getVal();
    }
    public function getPy_id()
    {
        return $this->py_id->getVal();
    }
    public function getOa_id()
    {
        return $this->oa_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getNombre()
    {
        return $this->nombre->getVal();
    }
    public function getOa_nombre()
    {
        return $this->oa_nombre->getVal();
    }
    public function getHombres()
    {
        return $this->hombres->getVal();
    }
    public function getOa_hombres()
    {
        return $this->oa_hombres->getVal();
    }
    public function getMujeres()
    {
        return $this->mujeres->getVal();
    }
    public function getOa_mujeres()
    {
        return $this->oa_mujeres->getVal();
    }
    public function getOrigen()
    {
        return $this->origen->getVal();
    }
    public function getOa_2026()
    {
        return $this->oa_2026->getVal();
    }
    public function getAnticipo()
    {
        return $this->anticipo->getVal();
    }
    public function getAnticipo2026()
    {
        return $this->anticipo2026->getVal();
    }
    public function getAmortizacion()
    {
        return $this->amortizacion->getVal();
    }
    public function getTotal()
    {
        return $this->total->getVal();
    }
    public function getOa_total()
    {
        return $this->oa_total->getVal();
    }
    public function getGrupoEnfoque()
    {
        return $this->grupoEnfoque->getVal();
    }
    public function getObservacion()
    {
        return $this->observacion->getVal();
    }
    public function getDescripcion()
    {
        return $this->descripcion->getVal();
    }
    public function getPbf_id()
    {
        return $this->pbf_id->getVal();
    }
    public function getEug_id()
    {
        return $this->eug_id->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getEoa_id()
    {
        $eoa = $this->eoa_id->getVal();
        return (empty($eoa)) ? 0 : $eoa;
    }
    public function getToa_id()
    {
        return $this->toa_id->getVal();
    }
    public function getNumObra()
    {
        return $this->numObra->getVal();
    }
    public function getModalidadEjecucion()
    {
        return $this->modalidadEjecucion->getVal();
    }
    public function getFechaLimiteEjecucion()
    {
        return $this->fechaLimiteEjecucion->getVal();
    }
    public function getEdoVinculacion()
    {
        return $this->edoVinculacion->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getFin_id()
    {
        $gpo = $this->getGpo_id();
        $ur = $this->getUr_id();
        $prg = $this->getPrg_id();
        $spr = $this->getSpr_id();
        $py = $this->getPy_id();
        $oa = $this->getOa_id();
        $this->conectar();
        $params = [
            ':grupo'       => (($gpo === '') ? 'null' : $gpo * 1),
            ':ur'          => (($ur === '') ? 'null' : $ur * 1),
            ':programa'    => (($prg === '') ? 'null' : $prg * 1),
            ':subprograma' => (($spr === '') ? 'null' : $spr * 1),
            ':proyecto'    => (($py === '') ? 'null' : $py * 1),
            ':oa'          => (($oa === '') ? 'null' : $oa * 1),
        ];
        $this->setSql('
            SELECT fin_id
            FROM enc_Programatico
            JOIN vin_Programatico USING(epg_id)
            JOIN enc_Funcional USING(efn_id)
            WHERE gpo_id = :grupo AND
                   ur_id = :ur AND
                  prg_id = :programa AND
                  spr_id = :subprograma AND
                   py_id = :proyecto AND
                   oa_id = :oa;
        ');
        $this->consultar($params);
        $this->desconectar();
        $r = $this->extraer();
        return (isset($r['fin_id'])) ? $r['fin_id'] : '';
    }

    public function getFun_id()
    {
        $gpo = $this->getGpo_id();
        $ur = $this->getUr_id();
        $prg = $this->getPrg_id();
        $spr = $this->getSpr_id();
        $py = $this->getPy_id();
        $oa = $this->getOa_id();
        $this->conectar();

        $params = [
            ':grupo'       => (($gpo === '') ? 'null' : $gpo * 1),
            ':ur'          => (($ur === '') ? 'null' : $ur * 1),
            ':programa'    => (($prg === '') ? 'null' : $prg * 1),
            ':subprograma' => (($spr === '') ? 'null' : $spr * 1),
            ':proyecto'    => (($py === '') ? 'null' : $py * 1),
            ':oa'          => (($oa === '') ? 'null' : $oa * 1),
        ];

        $this->setSql('
            SELECT fun_id
            FROM enc_Programatico
            JOIN vin_Programatico USING(epg_id)
            JOIN enc_Funcional USING(efn_id)
            WHERE gpo_id = :grupo AND
                   ur_id = :ur AND
                  prg_id = :programa AND
                  spr_id = :subprograma AND
                   py_id = :proyecto AND
                   oa_id = :oa;
        ');

        $this->consultar($params);
        $this->desconectar();
        $r = $this->extraer();
        return (isset($r['fun_id'])) ? $r['fun_id'] : '';
    }

    public function getSfu_id()
    {
        $gpo = $this->getGpo_id();
        $ur = $this->getUr_id();
        $prg = $this->getPrg_id();
        $spr = $this->getSpr_id();
        $py = $this->getPy_id();
        $oa = $this->getOa_id();

        $this->conectar();
        $params = [
            ':grupo'       => (($gpo === '') ? 'null' : $gpo * 1),
            ':ur'          => (($ur === '') ? 'null' : $ur * 1),
            ':programa'    => (($prg === '') ? 'null' : $prg * 1),
            ':subprograma' => (($spr === '') ? 'null' : $spr * 1),
            ':proyecto'    => (($py === '') ? 'null' : $py * 1),
            ':oa'          => (($oa === '') ? 'null' : $oa * 1),
        ];

        $this->setSql('
            SELECT sfu_id
            FROM enc_Programatico
            JOIN vin_Programatico USING(epg_id)
            JOIN enc_Funcional USING(efn_id)
            WHERE gpo_id = :grupo AND
                    ur_id = :ur AND
                    prg_id = :programa AND
                    spr_id = :subprograma AND
                    py_id = :proyecto AND
                    oa_id = :oa;
        ');
        $this->consultar($params);
        $this->desconectar();
        $r = $this->extraer();
        return (isset($r['sfu_id'])) ? $r['sfu_id'] : '';
    }

    public function getEpg()
    {
        $oa = $this->getOa_id();
        if (!empty($oa) || $oa === 0) {
            $params = [
                ':grupo'       => $this->getGpo_id(),
                ':ur'          => $this->getUr_id(),
                ':programa'    => $this->getPrg_id(),
                ':subprograma' => $this->getSpr_id(),
                ':proyecto'    => $this->getPy_id(),
                ':oa'          => $this->getOa_id(),
            ];

            $this->setSql('
                SELECT epg_id
                FROM enc_Programatico
                WHERE gpo_id = :grupo
                    AND ur_id  = :ur
                    AND prg_id = :programa
                    AND spr_id = :subprograma
                    AND py_id  = :proyecto
                    AND oa_id  = :oa
            ');

            $this->conectar();
            $this->consultar($params);
            $this->desconectar();
            $epg = $this->extraer();
        } else {
            $epg = ['epg_id' => 0];
        }

        return $epg['epg_id'];
    }

    public function getPdi_id()
    {
        $oa = $this->getOa_id();
        if ((!empty($oa) || $oa === 0)) {
            $params = [
                ':grupo'       => $this->getGpo_id(),
                ':ur'          => $this->getUr_id(),
                ':programa'    => $this->getPrg_id(),
                ':subprograma' => $this->getSpr_id(),
                ':proyecto'    => $this->getPy_id(),
                ':oa'          => $this->getOa_id(),
            ];

            $this->setSql('
                SELECT pdi_id
            FROM enc_Programatico
                INNER JOIN cat_MetaActividad USING(epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                WHERE gpo_id = :grupo
                    AND ur_id  = :ur
                    AND prg_id = :programa
                    AND spr_id = :subprograma
                    AND py_id  = :proyecto
                    AND oa_id  = :oa
            ');

            $this->conectar();
            $this->consultar($params);
            $this->desconectar();
            $pdi = $this->extraer();
            $pdi = ($this->getNumeroRegistros() > 0) ? $pdi['pdi_id'] : 0;
        } else {
            $pdi = ['pdi_id' => 0];
        }
        return $pdi;
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
    public function setPrg_id($val)
    {
        $this->prg_id->setVal($val);
    }
    public function setSpr_id($val)
    {
        $this->spr_id->setVal($val);
    }
    public function setPy_id($val)
    {
        $this->py_id->setVal($val);
    }
    public function setOa_id($val)
    {
        $this->oa_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }
    public function setOa_nombre($val)
    {
        $this->oa_nombre->setVal($val);
    }
    public function setHombres($val)
    {
        $this->hombres->setVal($val);
    }
    public function setOa_hombres($val)
    {
        $this->oa_hombres->setVal($val);
    }
    public function setMujeres($val)
    {
        $this->mujeres->setVal($val);
    }
    public function setOa_mujeres($val)
    {
        $this->oa_mujeres->setVal($val);
    }
    public function setOrigen($val)
    {
        $this->origen->setVal($val);
    }
    public function setOa_2026($val)
    {
        $this->oa_2026->setVal($val);
    }
    public function setAnticipo($val)
    {
        $this->anticipo->setVal($val);
    }
    public function setAnticipo2026($val)
    {
        $this->anticipo2026->setVal($val);
    }
    public function setAmortizacion($val)
    {
        $this->amortizacion->setVal($val);
    }
    public function setTotal($val)
    {
        $this->total->setVal($val);
    }
    public function setOa_total($val)
    {
        $this->oa_total->setVal($val);
    }
    public function setGrupoEnfoque($val)
    {
        $this->grupoEnfoque->setVal($val);
    }
    public function setObservacion($val)
    {
        $this->observacion->setVal($val);
    }
    public function setDescripcion($val)
    {
        $this->descripcion->setVal($val);
    }
    public function setPbf_id($val)
    {
        $this->pbf_id->setVal($val);
    }
    public function setEug_id($val)
    {
        $this->eug_id->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setEoa_id($val)
    {
        $this->eoa_id->setVal($val);
    }
    public function setToa_id($val)
    {
        $this->toa_id->setVal($val);
    }
    public function setNumObra($val)
    {
        $this->numObra->setVal($val);
    }
    public function setModalidadEjecucion($val)
    {
        $this->modalidadEjecucion->setVal($val);
    }
    public function setFechaLimiteEjecucion($val)
    {
        $this->fechaLimiteEjecucion->setVal($val);
    }
    public function setEdoVinculacion($val)
    {
        $this->edoVinculacion->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
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
    public function staPrg_id($val)
    {
        $this->prg_id->staVal($val);
    }
    public function staSpr_id($val)
    {
        $this->spr_id->staVal($val);
    }
    public function staPy_id($val)
    {
        $this->py_id->staVal($val);
    }
    public function staOa_id($val)
    {
        $this->oa_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }
    public function staOa_nombre($val)
    {
        $this->oa_nombre->staVal($val);
    }
    public function staHombres($val)
    {
        $this->hombres->staVal($val);
    }
    public function staOa_hombres($val)
    {
        $this->oa_hombres->staVal($val);
    }
    public function staMujeres($val)
    {
        $this->mujeres->staVal($val);
    }
    public function staOa_mujeres($val)
    {
        $this->oa_mujeres->staVal($val);
    }
    public function staOrigen($val)
    {
        $this->origen->staVal($val);
    }
    public function staOa_2026($val)
    {
        $this->oa_2026->staVal($val);
    }
    public function staAnticipo($val)
    {
        $this->anticipo->staVal($val);
    }
    public function staAnticipo2026($val)
    {
        $this->anticipo2026->staVal($val);
    }
    public function staAmortizacion($val)
    {
        $this->amortizacion->staVal($val);
    }
    public function staTotal($val)
    {
        $this->total->staVal($val);
    }
    public function staOa_total($val)
    {
        $this->oa_total->staVal($val);
    }
    public function staGrupoEnfoque($val)
    {
        $this->grupoEnfoque->staVal($val);
    }
    public function staObservacion($val)
    {
        $this->observacion->staVal($val);
    }
    public function staDescripcion($val)
    {
        $this->descripcion->staVal($val);
    }
    public function staPbf_id($val)
    {
        $this->pbf_id->staVal($val);
    }
    public function staEug_id($val)
    {
        $this->eug_id->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }
    public function staEoa_id($val)
    {
        $this->eoa_id->staVal($val);
    }
    public function staToa_id($val)
    {
        $this->toa_id->staVal($val);
    }
    public function staNumObra($val)
    {
        $this->numObra->staVal($val);
    }
    public function staModalidadEjecucion($val)
    {
        $this->modalidadEjecucion->staVal($val);
    }
    public function staFechaLimiteEjecucion($val)
    {
        $this->fechaLimiteEjecucion->staVal($val);
    }
    public function staEdoVinculacion($val)
    {
        $this->edoVinculacion->staVal($val);
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

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => 'cat_ObraActividad']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->prg_id = new Atributo('numero', 'NO', 'PRI', 'prg_id');
        $this->spr_id = new Atributo('numero', 'NO', 'PRI', 'spr_id');
        $this->py_id = new Atributo('numero', 'NO', 'PRI', 'py_id');
        $this->oa_id = new Atributo('numero', 'NO', 'PRI', 'oa_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->nombre = new Atributo('cadena', 'NO', '', 'oa_nombre');
        $this->oa_nombre = new Atributo('cadena', 'NO', '', 'oa_nombre');
        $this->hombres = new Atributo('numero', 'NO', '', 'oa_hombres');
        $this->oa_hombres = new Atributo('numero', 'NO', '', 'oa_hombres');
        $this->mujeres = new Atributo('numero', 'NO', '', 'oa_mujeres');
        $this->oa_mujeres = new Atributo('numero', 'NO', '', 'oa_mujeres');
        $this->origen = new Atributo('cadena', 'NO', '', 'oa_origen');
        $this->oa_2026 = new Atributo('cadena', 'NO', '', 'oa_2026');
        $this->anticipo = new Atributo('numero', 'NO', '', 'oa_anticipo');
        $this->anticipo2026 = new Atributo('numero', 'NO', '', 'oa_anticipo2026');
        $this->amortizacion = new Atributo('numero', 'NO', '', 'oa_amortizacion');
        $this->total = new Atributo('numero', 'NO', '', 'oa_total');
        $this->oa_total = new Atributo('numero', 'NO', '', 'oa_total');
        $this->grupoEnfoque = new Atributo('cadena', 'NO', '', 'oa_grupoEnfoque');
        $this->observacion = new Atributo('numero', 'NO', '', 'oa_observacion');
        $this->descripcion = new Atributo('numero', 'NO', '', 'oa_descripcion');
        $this->pbf_id = new Atributo('numero', 'NO', 'MUL', 'pbf_id');
        $this->eug_id = new Atributo('numero', 'NO', 'MUL', 'eug_id');
        $this->sts_id = new Atributo('numero', 'NO', 'MUL', 'sts_id');
        $this->eoa_id = new Atributo('numero', 'NO', 'MUL', 'eoa_id');
        $this->toa_id = new Atributo('numero', 'NO', 'MUL', 'toa_id');
        $this->numObra = new Atributo('cadena', 'NO', '', 'oa_numObra');
        $this->modalidadEjecucion = new Atributo('cadena', 'NO', '', 'oa_modalidadEjecucion');
        $this->fechaLimiteEjecucion = new Atributo('fecha', 'YES', '', 'oa_fechaLimiteEjecucion');
        $this->edoVinculacion = new Atributo('numero', 'NO', '', 'oa_edoVinculacion');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($prm->epg_id)) {

            $params = [$prm->epg_id];
            $this->setSql('
                SELECT  gpo_id,ur_id,prg_id,spr_id,py_id,oa_id
                FROM enc_Programatico
                WHERE  epg_id = ?;
            ');
            $this->conectar();
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();
            $this->setGpo_id($datos['gpo_id']);
            $this->setUr_id($datos['ur_id']);
            $this->setPrg_id($datos['prg_id']);
            $this->setSpr_id($datos['spr_id']);
            $this->setPy_id($datos['py_id']);
            $this->setOa_id($datos['oa_id']);
            $this->select();
        } elseif (!empty($prm->gpo_id) && !empty($prm->ur_id) && !empty($prm->prg_id) && !empty($prm->spr_id) && !empty($prm->py_id) && !empty($prm->oa_id)) {
            $this->setGpo_id($prm->gpo_id);
            $this->setUr_id($prm->ur_id);
            $this->setPrg_id($prm->prg_id);
            $this->setSpr_id($prm->spr_id);
            $this->setPy_id($prm->py_id);
            $this->setOa_id($prm->oa_id);
            $this->select();
        }
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {

        if (empty($p->epg_id)) {
            $oa = new ObraActividad();
            $oa->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $oa->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $oa->setPrg_id($_SESSION[_S]['clasAdm-prg']);
            $oa->setSpr_id($_SESSION[_S]['clasAdm-spr']);
            $oa->setPy_id($_SESSION[_S]['clasAdm-py']);
        } else {
            $oa = new ObraActividad($p);
        }

        if (isset($p->gpo_id)) {
            $oa->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $oa->setUr_id($p->ur_id);
        }

        if (isset($p->prg_id)) {
            $oa->setPrg_id($p->prg_id);
        }

        if (isset($p->spr_id)) {
            $oa->setSpr_id($p->spr_id);
        }

        if (isset($p->py_id)) {
            $oa->setPy_id($p->py_id);
        }

        if (isset($p->oa_id)) {
            $oa->setOa_id($p->oa_id);
        }

        if (isset($p->ue_id)) {
            $oa->setUe_id($p->ue_id);
        }

        if (isset($p->nombre)) {
            $oa->setOa_nombre($p->nombre);
        }

        if (isset($p->hombres)) {
            $oa->setOa_hombres($p->hombres);
        }

        if (isset($p->mujeres)) {
            $oa->setOa_mujeres($p->mujeres);
        }

        if (isset($p->origen)) {
            $oa->setOrigen($p->origen);
        }

        if (isset($p->oa_2026)) {
            $oa->setOa_2026($p->oa_2026);
        }

        if (isset($p->anticipo)) {
            $oa->setAnticipo($p->anticipo);
        }

        if (isset($p->anticipo2026)) {
            $oa->setAnticipo2026($p->anticipo2026);
        }

        if (isset($p->amortizacion)) {
            $oa->setAmortizacion($p->amortizacion);
        }

        if (isset($p->total)) {
            $oa->setOa_total($p->total);
        }

        if (isset($p->grupoEnfoque)) {
            $oa->setGrupoEnfoque($p->grupoEnfoque);
        }

        if (isset($p->observacion)) {
            $oa->setObservacion($p->observacion);
        }

        if (isset($p->descripcion)) {
            $oa->setDescripcion($p->descripcion);
        }

        if (isset($p->pbf_id)) {
            $oa->setPbf_id($p->pbf_id);
        } elseif ($p->abc == 'a') {
            $oa->setPbf_id(1);
        }

        if (isset($p->eug_id)) {
            $oa->setEug_id($p->eug_id);
        }

        if (isset($p->sts_id)) {
            $oa->setSts_id($p->sts_id);
        }

        if (isset($p->eoa_id)) {
            $oa->setEoa_id($p->eoa_id);
        }

        if (isset($p->toa_id)) {
            $oa->setToa_id($p->toa_id);
        }

        if (isset($p->numObra)) {
            $oa->setNumObra($p->numObra);
        }

        if (isset($p->modalidadEjecucion)) {
            $oa->setModalidadEjecucion($p->modalidadEjecucion);
        }

        if (isset($p->fechaLimiteEjecucion)) {
            $oa->setFechaLimiteEjecucion($p->fechaLimiteEjecucion);
        }

        if (isset($p->edoVinculacion)) {
            $oa->setEdoVinculacion($p->edoVinculacion);
        }

        if (isset($p->usr_id)) {
            $oa->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($p->abc) {

            case 'a':
                $oa->conectar();
                $params = [':ip' => $ip, ':usr' => $_SESSION[_S]['usr_id']];
                $oa->setSql('SET @usr_ip =INET_ATON(:ip), @usr_id = :usr');
                $oa->consultar($params);
                if (isset($p->mensaje)) {
                    $params = [':observacion' => $p->mensaje];
                    $oa->setSql('SET @bit_observacion = :observacion');
                    $oa->consultar($params);
                }

                $p->epe_id = (!empty($p->epe_id)) ? $p->epe_id : 'null';
                $params = [':epe' => $p->epe_id];
                $oa->setSql('SET @epe_id = :epe;');
                $oa->consultar($params);
                //Encuadre ODS
                $p->epods_id = (!empty($p->epods_id)) ? $p->epods_id : 'null';
                $params = [':epods' => $p->epods_id];
                $oa->setSql('SET @epods_id = :epods;');
                $oa->consultar($params);
                // Encuadre Pes
                $p->eps_id = (!empty($p->eps_id)) ? $p->eps_id : 'null';
                $params = [':eps' => $p->eps_id];
                $oa->setSql('SET @eps_id = :eps;');
                $oa->consultar($params);
                // Encuadre Funcional
                $p->efn_id = (!empty($p->efn_id)) ? $p->efn_id : 'null';
                $params = [':efn' => $p->efn_id];
                $oa->setSql('SET @efn_id = :efn;');
                $oa->consultar($params);
                // Encuadre Presupuestario
                $p->epp_id = (!empty($p->epp_id)) ? $p->epp_id : 'null';
                $params = [':epp' => $p->epp_id];
                $oa->setSql('SET @epp_id = :epp;');
                $oa->consultar($params);

                // Plan especial
                $p->epc_id = (!empty($p->epc_id)) ? $p->epc_id : 'null';
                $params = [':epc' => $p->epc_id];
                $oa->setSql('SET @epc_id = :epc;');
                $oa->consultar($params);

                $oa->setSts_id(1);
                if ($_SESSION[_S]['clasAdm-py'] == 0) {
                    $oa->setSts_id(2);
                }

                $oa->insert(false);

                //OBTENEMOS EL ENCUADRE PROGRAMATICO QUE SE AGREGÓ

                $oa->setSql('SELECT @oa_id as oa_id;');
                $oa->consultar();
                $oa_id = $oa->extraer();
                $params = [
                    ':grupo'       => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur'          => $_SESSION[_S]['clasAdm-ur'],
                    ':programa'    => $_SESSION[_S]['clasAdm-prg'],
                    ':subprograma' => $_SESSION[_S]['clasAdm-spr'],
                    ':proyecto'    => $_SESSION[_S]['clasAdm-py'],
                    ':oa'          => $oa_id['oa_id'],
                ];
                $oa->setSql('
                    SELECT epg_id
                    FROM enc_Programatico
                    WHERE gpo_id = :grupo
                        AND ur_id = :ur
                        AND prg_id = :programa
                        AND spr_id = :subprograma
                        AND py_id = :proyecto
                        AND oa_id = :oa;');
                $oa->consultar($params);
                $epg_id = $oa->extraer();
                $values = '';

                if (!empty($p->cvesFin)) {
                    if (is_string($p->cvesFin)) {
                        $p->cvesFin = explode(',', $p->cvesFin);
                    }

                    foreach ($p->cvesFin as $ecf_id) {
                        $values .= ($values != '') ? ',' : '';
                        $values .= '(' . $_SESSION[_S]['clasAdm-gpo'] . ',' . $_SESSION[_S]['clasAdm-ur'] . ',' . $epg_id['epg_id'] . ',' . $ecf_id . ',0,0,"","","",' . $_SESSION[_S]['usr_id'] . ')';
                    }
                    $params = [':ip' => $ip];
                    $oa->setSql('SET @usr_ip =INET_ATON(:ip)');
                    $oa->consultar($params);
                    if (isset($p->mensaje)) {
                        $params = [':observacion' => $p->mensaje];
                        $oa->setSql('SET @bit_observacion = :observacion');
                        $oa->consultar($params);
                    }
                    $oa->setSql('INSERT INTO poa_OrigenRecurso (gpo_id,ur_id,epg_id,ecf_id,ore_folioModif,ore_folioLib,ore_oficioLib,ore_oficioAut,ore_idObra,usr_id) VALUES ' . $values . ';');
                    $oa->consultar();
                }

                if ($p->arrayPI) {
                    $insertPi = explode(',', $p->arrayPI);
                    foreach ($insertPi as $value) {
                        $sql = 'INSERT INTO vin_ProgramaticoInstitucional (epg_id,epi_id,usr_id) VALUES(?, ?, ?);';
                        $params = [$epg_id['epg_id'], $value, $_SESSION[_S]['usr_id']];
                        $oa->setSql($sql);
                        $oa->consultar($params);
                    }
                }

                $valuesPT = '';
                if (!empty($p->PoliticasT) && $p->PoliticasT != '[]') {
                    if (is_string($p->PoliticasT)) {
                        $p->PoliticasT = json_decode($p->PoliticasT);
                    }

                    foreach ($p->PoliticasT as $ept_id) {
                        $valuesPT .= ($valuesPT != '') ? ',' : '';
                        $valuesPT .= '(' . $epg_id['epg_id'] . ',' . $ept_id->etla_id . ',' . number_format($ept_id->ept_porcentaje, 2) . ',' . $_SESSION[_S]['usr_id'] . ')';
                    }
                    $oa->setSql('INSERT INTO vin_ProgramaticoTransversal (epg_id,etla_id,ept_porcentaje,usr_id) VALUES ' . $valuesPT . ';');
                    $oa->consultar();
                }

                if ($p->metas) {
                    foreach ($p->metas as $value) {
                        $sql = 'INSERT INTO vin_ObraMir (pii_id,epg_id,macm_id,usr_id) VALUES(?, ?, ?, ?);';
                        $params = [$value->pii_id,$epg_id['epg_id'],$value->macm_id,$_SESSION[_S]['usr_id']];
                        $oa->setSql($sql);
                        $oa->consultar($params);
                    }
                }

                $oa->desconectar();
                return $oa_id['oa_id'];

                break;
            case 'b':
                //SI LA OBRA ES DE GASTOS DE OPERACIÓN SE PROCEDE A ELIMINAR

                if ($oa->getToa_id() == 1) {

                    $oa_id = $oa->getOa_id() * 1;
                    $sqlPy = (empty($oa_id)) ? 'DELETE FROM cat_Proyecto WHERE gpo_id = :grupo AND ur_id = :ur AND  prg_id = :programa AND spr_id = :subprograma AND py_id= :proyecto' : '';

                    $con = new AccesoDatos();
                    $con->conectar();
                    $params = [':usr' => $_SESSION[_S]['usr_id']];
                    $con->setSql('SET @usr_ip =' . BD_SINPRES . '.fn_get_ip(), @usr_id =:usr;');
                    $con->consultar($params);

                    $params = [':epg' => $oa->getEpg() * 1];

                    $con->setSql('DELETE FROM poa_OrigenRecurso WHERE epg_id = :epg;');
                    $con->consultar($params);
                    $con->desconectar();
                    unset($con);

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql('DELETE FROM vin_ProgramaticoTransversal WHERE epg_id = :epg;');
                    $con->consultar($params);
                    $con->desconectar();
                    unset($con);

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql('DELETE FROM vin_ProgramaticoInstitucional WHERE epg_id = :epg;');
                    $con->consultar($params);
                    $con->desconectar();
                    unset($con);

                    $con = new AccesoDatos();
                    $con->setSql('SELECT ' . BD_SINPRES . '.fn_tableExists("cat_InventarioPlazas","' . BD_CTRL . '") AS existe;');
                    $con->conectar();
                    $con->consultar();
                    $con->desconectar();
                    $d = $con->extraer();

                    if ($d['existe'] == 1) {
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql('DELETE FROM cat_InventarioPlazas WHERE epg_id = :epg;');
                        $con->consultar($params);
                        $con->desconectar();
                        unset($con);
                    }

                    unset($con);

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql('DELETE FROM vin_Programatico WHERE epg_id = :epg;');
                    $con->consultar($params);
                    $con->desconectar();
                    unset($con);

                    if ($oa->getPrg_id() >= 100 and $oa->getPrg_id() <= 899) {
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql('
                            SELECT
                                pii_id, epg_id
                            FROM cat_MetaActividad
                            WHERE epg_id = :epg;
                        ');
                        $con->consultar($params);
                        if ($con->getNumeroRegistros() > 0) {
                            $con->setSql(
                                'SELECT pii_id,epg_id 
                                FROM vin_ObraMir WHERE epg_id = :epg;
                            '
                            );
                            $con->consultar($params);
                            while ($r = $con->extraer()) {
                                $observacion = 'Elimina meta actividad desde pantalla A_2_2';
                                $parametros = [':observacion' => $observacion,':usr' => $_SESSION[_S]['usr_id']];
                                $con2 = new AccesoDatos();
                                $con2->conectar();
                                $con2->setSql('
                                    SET @bit_observacion = :observacion, 
                                    @usr_ip =' . BD_SINPRES . '.fn_get_ip(), @usr_id = :usr
                                ;');
                                $con2->consultar($parametros);
                                $con2->setSql('CALL sp_RegistroMetaActividad' . APP_EJERCICIO . '(?, ?, ?, ?, "D", 0, @usr_ip, @usr_id);');
                                $con2->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$r['pii_id'],$r['epg_id']]);
                                $con2->setSql('SELECT @banderaPoa, @mensajePoa;');
                                $con2->consultar();
                                $res = $con2->extraer();
                                unset($con2);
                                if (!$res['@banderaPoa']) {
                                    $this->mixScript('
                                        notify("' . $res['@mensajePoa'] . '","'
                                            . ($res['@banderaPoa'] ? 'success' : 'error') . '")
                                    ');
                                    return false;
                                }
                            }
                            $con->desconectar();
                            unset($con);
                        } else {
                            $con = new AccesoDatos();
                            $con->conectar();
                            $con->setSql('DELETE FROM vin_ObraMir WHERE epg_id = :epg;');
                            $con->consultar($params);
                            $con->desconectar();
                            unset($con);
                        }
                    }

                    $con = new AccesoDatos();
                    $con->conectar();
                    $con->setSql('DELETE FROM enc_Programatico WHERE epg_id = :epg;');
                    $con->consultar($params);
                    $con->desconectar();
                    unset($con);

                    $oa->conectar();
                    $params = [':ip' => $ip];
                    $oa->setSql('SET @usr_ip =INET_ATON(:ip);');
                    $oa->consultar($params);

                    if (isset($p->mensaje)) {
                        $params = [':observacion' => $p->mensaje];
                        $oa->setSql('SET @bit_observacion = :observacion;');
                        $oa->consultar($params);
                    }

                    $params = [':usr' => $_SESSION[_S]['usr_id']];
                    $oa->setSql('SET @usr_id = :usr;');
                    $oa->consultar($params);

                    $oa->delete(false);
                    if (!empty($sqlPy)) {
                        $oa->setSql($sqlPy);
                        $oa->consultar($params);
                    }
                }
                break;
            case 'c':
                $oa->conectar();
                $params = [':ip' => $ip, ':usr' => $_SESSION[_S]['usr_id']];
                $sql = 'SET @usr_ip =INET_ATON(:ip)';
                if (isset($p->mensaje)) {
                    $params[':observacion'] = $p->mensaje;
                    $sql .= ',@bit_observacion = :observacion';
                }
                $sql .= ',@usr_id = :usr;';
                $oa->setSql($sql);
                $oa->consultar($params);
                $oa->update(false);
                $oa_id = $oa->getOa_id() * 1;
                $oa->conectar();
                if (empty($oa_id) && $oa->getToa_id() == 1) {
                    $params = [
                        ':estadoObra'  => $oa->getEoa_id(),
                        ':grupo'       => $oa->getGpo_id(),
                        ':ur'          => $oa->getUr_id(),
                        ':programa'    => $oa->getPrg_id(),
                        ':subprograma' => $oa->getSpr_id(),
                        ':proyecto'    => $oa->getPy_id(),
                    ];
                    $oa->setSql('UPDATE cat_Proyecto SET epy_id = :estadoObra WHERE gpo_id = :grupo AND ur_id = :ur AND  prg_id = :programa AND spr_id = :subprograma AND py_id = :proyecto;');
                    $oa->consultar($params);
                }
                $oa->desconectar();
                if ($oa->getToa_id() == 1) {
                    if ($oa->getPrg_id() >= 100 and $oa->getPrg_id() <= 899) {
                        $set = '';
                        $params = [];
                        if (isset($p->epe_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epe_id = :epe';
                            $params[':epe'] = $p->epe_id;
                        }
                        if (isset($p->eps_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'eps_id = :eps';
                            $params[':eps'] = $p->eps_id;
                        }
                        if (isset($p->efn_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'efn_id = :efn';
                            $params[':efn'] = $p->efn_id;
                        }
                        if (isset($p->epp_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epp_id = :epp';
                            $params[':epp'] = $p->epp_id;
                        }
                        if (isset($p->epods_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epods_id = :epods';
                            $params[':epods'] = $p->epods_id;
                        }
                        if (isset($p->epc_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epc_id = :epc';
                            $params[':epc'] = $p->epc_id;
                        }

                        if ($set != '') {
                            //consultamos si se encuentra el registro en vin_Programatico
                            $con1 = new AccesoDatos();
                            $con1->conectar();
                            $params = [':epg' => $oa->getEpg()];
                            $con1->setSql('
                                SELECT epg_id FROM vin_Programatico
                                WHERE epg_id = :epg;
                            ');
                            $con1->consultar($params);
                            $con1->desconectar();
                            $con = new AccesoDatos();
                            $con->conectar();
                            if ($con1->getNumeroRegistros() > 0) {
                                unset($con1);
                                $params = [
                                    ':epe'   => $p->epe_id,
                                    ':eps'   => $p->eps_id,
                                    ':efn'   => $p->efn_id,
                                    ':epp'   => $p->epp_id,
                                    ':epods' => $p->epods_id,
                                    ':epc'   => $p->epc_id,
                                    ':epg'   => $oa->getEpg(),
                                ];
                                $con->setSql('
                                    UPDATE vin_Programatico SET
                                            epe_id = :epe,
                                            eps_id = :eps,
                                            efn_id = :efn,
                                            epp_id = :epp,
                                            epods_id = :epods,
                                            epc_id = :epc
                                    WHERE epg_id = :epg;
                                ');
                                $con->consultar($params);
                            }
                        }
                    } else {

                        $set = '';
                        $params = [];
                        if (isset($p->epe_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epe_id = :epe';
                            $params[':epe'] = $p->epe_id;
                        }
                        if (isset($p->eps_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'eps_id = :eps';
                            $params[':eps'] = $p->eps_id;
                        }
                        if (isset($p->efn_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'efn_id = :efn';
                            $params[':efn'] = $p->efn_id;
                        }
                        if (isset($p->epp_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epp_id = :epp';
                            $params[':epp'] = $p->epp_id;
                        }
                        if (isset($p->epods_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epods_id = :epods';
                            $params[':epods'] = $p->epods_id;
                        }
                        if (isset($p->epc_id)) {
                            $set = ($set != '') ? ',' : '';
                            $set .= 'epc_id = :epc';
                            $params[':epc'] = $p->epc_id;
                        }

                        if ($set != '') {

                            //consultamos si se encuentra el registro en vin_Programatico
                            $con1 = new AccesoDatos();
                            $con1->conectar();
                            $params = [':epg' => $oa->getEpg()];
                            $con1->setSql('
                                SELECT epg_id FROM vin_Programatico
                                WHERE epg_id = :epg;
                            ');
                            $con1->consultar($params);
                            $con1->desconectar();
                            $con = new AccesoDatos();
                            $con->conectar();
                            if ($con1->getNumeroRegistros() > 0) {
                                unset($con1);
                                $params = [
                                    ':epe'   => $p->epe_id,
                                    ':eps'   => $p->eps_id,
                                    ':efn'   => $p->efn_id,
                                    ':epp'   => $p->epp_id,
                                    ':epc'   => $p->epc_id,
                                    ':epods' => $p->epods_id,
                                    ':epg'   => $oa->getEpg(),
                                ];
                                $con->setSql('
                                    UPDATE vin_Programatico SET
                                            epe_id = :epe,
                                            eps_id = :eps,
                                            efn_id = :efn,
                                            epp_id = :epp,
                                            epc_id = :epc,
                                            epods_id = :epods
                                    WHERE epg_id = :epg;
                                ');
                                $con->consultar($params);
                            } else {
                                $params = [
                                    ':epg'   => $oa->getEpg(),
                                    ':epe'   => $p->epe_id,
                                    ':eps'   => $p->eps_id,
                                    ':efn'   => $p->efn_id,
                                    ':epp'   => $p->epp_id,
                                    ':epc'   => $p->epc_id,
                                    ':epods' => $p->epods_id,
                                ];
                                $sqlnserta = 'INSERT INTO vin_Programatico (epg_id,epe_id,eps_id,efn_id,epp_id,epc_id,epods_id)
                                        VALUES(:epg, :epe, :eps, :efn, :epp, :epc, :epods);
                                ';
                                $con->setSql($sqlnserta);
                                $con->consultar($params);
                            }
                            $con->desconectar();
                            unset($con);
                        }
                    }
                    return $oa_id;
                }
                break;
        }
    }
}
