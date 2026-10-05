<?php

DEFINE('LINEAS_POR_PAGINA', 99);
require_once dirname(__FILE__) . '/../lib/WebResponse.php';

const CONTENEDOR_ENCUADRE_PROGRAMATICO = 'CONTENEDOR_ENCUADRE_PROGRAMATICO';

if (!session_id()) {
    session_start();
}

class dameProgramaFinanciamiento2 extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'lib/dameProgramaFinanciamiento2.php';
        if ($this->metodo === 'llenaTablaEncuadreProgramatico') {
            $this->llenaTablaEncuadreProgramatico();
        } else {
            $this->index();
        }
        return $this->returnJson();

    }

    public function index()
    {
        $this->setContenedor(CONTENEDOR_ENCUADRE_PROGRAMATICO);
        if (isset($this->prm->tipo) && $this->prm->tipo === 'ventana') {
            $this->setContenedor('det_fs' . CONTENEDOR_ENCUADRE_PROGRAMATICO);
        }
        $tabla = $this->ponTablaProgramatico();
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript($this->wrLlamaLlenaTabla($tabla->id));
    }

    public function ponTablaProgramatico(): object
    {

        $tabla = new stdClass();
        $tabla->id = 'TABLA_' . CONTENEDOR_ENCUADRE_PROGRAMATICO;
        $tabla->comunica = new stdClass();
        $tabla->tools = $this->ponFiltros();
        $tabla->inputFiltro = 'inp_cat_prog_fin_search';
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaEncuadreProgramatico';
        $tabla->comunica->filtro = isset($this->prm->filtro) ? $this->prm->filtro : '';
        $tabla->comunica->ecf_id = isset($this->prm->ecf_id) ? $this->prm->ecf_id : '';
        $tabla->comunica->toa_id = isset($this->prm->toa_id) ? $this->prm->toa_id : '';
        $tabla->comunica->mod_ue_id = isset($this->prm->mod_ue_id)  ? $this->prm->mod_ue_id : '';
        $tabla->trDblClick = '_encProgFinFunction';
        $tabla->esCompacta = true;

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Encuadre Programático';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Proyecto';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Obra/Actividad';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave de Financiamiento';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de la Clave de Financiamiento';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'prog';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'npy';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cveF';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ncve';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        return $tabla;
    }

    private function ponFiltros()
    {
        return <<<HTML
            <div class="py-4 d-flex align-items-center gap-2">
                <input class="form-control w-50" id="inp_cat_prog_fin_search" name="inp_cat_prog_fin_search" placeholder="Texto de la descripción a buscar ..." />
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                <span style="color:red">
                ( doble click sobre la línea del encuadre para seleccionarlo )
                </span>
            </div>
        HTML;
    }


    public function llenaTablaEncuadreProgramatico()
    {

        $nInicial = 0;
        $select = '';
        $sql = '';
        $ejecutora = isset($_POST['ue_id']) ? $_POST['ue_id'] : '';
        $omo_id = isset($_POST['omo_d']) ? $_POST['omo_d'] : '';
        $params = [];

        $paginaActual = ((isset($_POST['p']) && $_POST['p'] != '0') ? $_POST['p'] : '');
        $arrayMerge = [];
        // Si es un usuario de nivel UE, limitamos por UE
        // Si no, mostramos todas las UE
        $ejecutora = '';
        $paramsEje = [];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            // ERROR: De momento, filtramos por UE sólo cuando se trate de un usuario de nivel UE
            if ($_SESSION[_S]['nvj_id'] == 1) {
                $ejecutora = ' AND ue_id = ?';
                array_push($paramsEje, $_SESSION[_S]['clasAdm-ue']);
            }
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != '') {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        $select = '';
        $sql = '';
        $joinInversion = '';

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        $obr = '';
        $paramsObr = [];

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'enc_InversionObra':
                    $joinInversion = 'LEFT JOIN enc_InversionObra obr USING(epg_id, gpo_id, ur_id)';
                    /* El filtro ipu_id  filtra las obras por el contrato ya que en el en encuadre enc_InversionObra ahi vienen las obras que tiene cada contrato */
                    if (isset($_POST['paso']) && $_POST['paso'] != '') {
                        switch ($_POST['paso']) {
                            case 0: //otras clc´s
                                break;
                            case 1: //obra con contrato
                                if (isset($_POST['ipu_id']) && $_POST['ipu_id'] != '') {
                                    $obr = ' and ipu_id= ?';
                                    array_push($paramsObr, $_POST['ipu_id']);
                                }
                                break;
                            case 2: //obra sin contrato
                                break;
                        }
                    }
                    // no break
                case 'porUrClaveFinanciamiento':
                    $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 && isset($_POST['mod_ue_id'])) {
                        $ejecutora = ' AND ue_id = ?';
                        array_push($params, $_POST['mod_ue_id']);
                    }
                    $sql = '
                        FROM presupuesto p
                            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                            INNER JOIN enc_Programatico USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                            INNER JOIN cat_ObraActividad oa USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                            INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                            INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                            ' . $joinInversion . "
                        WHERE p.gpo_id = ? AND p.ur_id = ?
                            AND ur.eur_id = 5
                            AND f.sts_id  = 1
                            AND ((oa.toa_id = 1 AND oa.eoa_id = 4)
                            OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                            $ejecutora
                            " . (isset($_POST['ecf_id']) ? ' AND p.ecf_id = ?' : '') . '
                            ' . (isset($_POST['toa_id']) ? ' AND oa.toa_id = ?' : '') . '
                            ' . $obr . '
                        GROUP BY gpo_id, ur_id, epg_id, p.ecf_id, cfi_nombre';
                    isset($_POST['ecf_id']) ? array_push($params, $_POST['ecf_id']) : '';
                    isset($_POST['toa_id']) ? array_push($params, $_POST['toa_id']) : '';
                    $arrayMerge = $params;
                    break;
            }
        } else {
            $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
            $sql = '
                FROM poa_OrigenRecurso o
                    INNER JOIN enc_Programatico p USING (epg_id, gpo_id, ur_id)
                    INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id)
                    INNER JOIN cat_Proyecto py USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                    INNER JOIN cat_ObraActividad oa ON (p.gpo_id = oa.gpo_id AND p.ur_id = oa.ur_id AND p.prg_id = oa.prg_id AND p.spr_id = oa.spr_id AND p.py_id = oa.py_id AND p.oa_id = oa.oa_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                WHERE o.gpo_id = ? AND o.ur_id = ?
                    AND oa.ue_id IS NOT NULL
                    AND f.sts_id = 1
                    AND ((oa.toa_id = 1 AND oa.eoa_id = 4) OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                    $ejecutora
                    AND f.ecf_poa = 1
            ";
            $arrayMerge = array_merge($params, $paramsEje);
        }

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'enc_InversionObra':
                case 'porUrClaveFinanciamiento':
                    $select = "
                        SELECT
                            p.epg_id,
                            p.ecf_id,
                            CONCAT(p.gpo_id, p.ur_id, '-', ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id) AS prog,
                            if(p.oa_id != 0,
                                oa_nombre,
                                py_nombre) AS nombre,
                            oa_nombre AS npro,
                            py_nombre AS npy,
                            CONCAT(f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio) AS cveF,
                            cfi_nombre AS ncve
                    ";
                    $limit = '';
                    break;
                default:
                    $select = '';
                    $limit = '';
            }
        } else {
            $select = "
                SELECT
                    o.epg_id,
                    o.ecf_id,
                    CONCAT( p.gpo_id, p.ur_id, '-', IFNULL( ue_id, 'XX' ), '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id ) AS prog,
                    if(p.oa_id != 0,
                        oa_nombre,
                        py_nombre) AS nombre,
                    oa_nombre AS npro,
                    py_nombre AS npy,
                    CONCAT( f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio ) AS cveF,
                    cfi_nombre AS ncve
            ";
            $limit = 'LIMIT ' . $nInicial . ', ' . LINEAS_POR_PAGINA;
        }

        $this->setParams($arrayMerge);
        $this->setSql($select . $sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 80;
        $nav->idTabla = 'TABLA_' . CONTENEDOR_ENCUADRE_PROGRAMATICO;



        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros","warning");
                $(".tcl_ida").removeClass("loadingclas");');
        }
    }

    public static function ponModalEncuadre($parametros): string
    {

        $prm = $parametros ?
            (
                is_array($parametros) ? (object) $parametros : (is_object($parametros) ? $parametros : new stdClass())
            ) : new stdClass();
        $epg_id = isset($prm->epg_id) ? $prm->epg_id : null;

        $filtro = isset($prm->filtro) ? $prm->filtro : '';
        //var_dump($filtro);
        //$datosProgramatico = ClasificacionAdministrativa::PonEnVariablesEpg($epg_id, true);
        $claveProgramatico = '';//$datosProgramatico['programatico'];
        //$nombreProgramatico = $datosProgramatico['oa_nombre'];
        $readOnly = true;

        $js = self::construyeJs($parametros);
        return <<<HTML
        <div id="cargaDatosProgFin" style="width:100%;display:none;">
            <div class="input-group input-group-sm" style="float: left; width:600px">
                <input id="filtrarTexto" tp="0" type="text" class="form-control" onKeyUp="filtrarProgFte()" placeholder="Texto en la descripción a buscar ..." >
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                &nbsp;&nbsp;&nbsp;
                <span style="color:red">
                ( doble click sobre la línea del encuadre para seleccionarlo )
                </span>
            </div>
            <br />
            <div style="position:relative">
                <div id="planPaginador" style="float:right;position:absolute;right:0;top:-10px;z-index:100;"></div>
            </div>
            <p>&nbsp;</p>
            <div id="myGridProgFte" style="width:100%;height:92%;">
            </div>
        </div>
            <p>&nbsp;</p>
            <script type="text/javascript">
                {$js}
            </script>
        </div>
        HTML;
    }

    public static function construyeJs($parametros)
    {

        $programaticoBlur = '';
        $filtro = $parametros['filtro'];
        $toa = $parametros['toa_id'];
        $mod_ue_id = $parametros['mod_ue_id'];
        $ecf_id = $parametros['ecf_id'];

        if (isset($parametros->$programaticoBlur)) {
            $programaticoBlur = <<<JS
                $("#encProg").on('keydown', function(){
                    {$parametros->$programaticoBlur}
                });
            JS;
        }

        $onSelect = '';

        if (isset($parametros->onSelect)) {
            $onSelect = $parametros->onSelect;
        }
        $idContainer = CONTENEDOR_ENCUADRE_PROGRAMATICO;

        $shootContainer = <<<JS
            $("#{$idContainer}").remove();
            $("body").append(
                $("<div/>").prop("id","{$idContainer}")
            );
            $("#{$idContainer}").dialog({
                modal: true,
                autoOpen: true,
                title: "Buscar y seleccionar Encuadre Programatico",
                dragable: false,
                resizable: false,
                width: Math.abs(window.innerWidth * .8),
                position: {my: "center center", at: "center top"},
                open: function(e) {
                    $(".ui-widget-overlay").css("z-index", 10000);
                    $("#{$idContainer}").parent().css("z-index", 10001);
                    $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").append("X");
                }
            })
        JS;
        $tipo = isset($parametros->tipo) && $parametros->tipo === 'ventana' ? 'ventana' : 'modal';

        $jsonComunica = [];
        $jsonComunica[] = "tipo: '" . $tipo . "'";
        $jsonComunica[] = "toa_id: '" . $toa . "'";
        $jsonComunica[] = "ecf_id: '" . $ecf_id . "'";
        if (isset($filtro)) {
            $jsonComunica[] = "filtro:'" . $filtro . "'";
            $jsonComunica[] = "toa_id:'" . $toa . "'";
            $jsonComunica[] = "ecf_id:'" . $ecf_id . "'";
            $jsonComunica[] = "mod_ue_id:'".$mod_ue_id."'";
        }

        if (isset($parametros->ejecutora)) {
            $jsonComunica[] = 'ue_id:"' . $parametros->ejecutora . '"';
        }

        $nivel = $parametros['nivelRevision'] != '' ? ',nivelRevision:"' . $parametros['nivelRevision'] . '"' : '';


        $jsonComunica = '{' . join(',', $jsonComunica) . '}';

        return <<<JS
            $(function(){
                $("#encProg").off('keydown').on('keydown', function(e){
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        {$shootContainer}
                        comunica('lib/dameProgramaFinanciamiento2.php',{$jsonComunica});
                    } else if ( key == 113 ){ /* F2 muestra los combos de clasAdmin */
                        e.preventDefault();
                        $("#clasAdminSeccionCombos").toggle();
                    } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                });

                {$programaticoBlur}
            })
            function _encProgFinFunction (selection, event) {
                try {
                    if (!typeof(selection) !== 'object' || !event) {
                        $("#encProg").attr( "enc", selection.epg_id)
                            .attr( "ori", selection.prog)
                            .val( selection.prog);
                        $("#nomProg").val( selection.nombre?.replace( /\./g, "" ) );
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin:'selEncProgFin',
                            encProg: selection.epg_id?.trim(),
                            prog: selection.prog?.trim(),
                            encCve: selection.ecf_id?.trim(),
                            cveFin: selection.cveF?.trim(),
                            nomFin: selection.ncve?.trim()
                        });
                        comunica( "srv/ObjetoGasto.php", {
                            metodoObjGto:"cargaPg",
                            filtro:"modificaciones",
                            tmd_id:'{$parametros['tmd_id']}',
                            tfm_id:'{$parametros['tfm_id']}',
                            mmd_id:'{$parametros['mmd_id']}',
                            tipoMovimiento:'{$parametros['tipoMovimiento']}',
                            omo_id:'{$parametros['omo_id']}',
                            ecf_id:'{$parametros['ecf_id']}',
                            
                        });
                        $("#{$idContainer}close_jma_viewer").trigger('click');
                        $("#{$idContainer}").remove();
                        {$onSelect}

                    }
                } catch (error) {
                    if (window.serverData.environment !=='production') {
                        console.log(error)
                    }
                }
            }
        JS;
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    new dameProgramaFinanciamiento2();
}
