<?php

require_once dirname(__FILE__).'/../lib/WebResponse.php';

const CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO = 'CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO';

if (!session_id()) {
    session_start();
}
class CatalogoProgramaticoFinanciamiento extends WebResponse
{

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'srv/CatalogoProgramaticoFinanciamiento.php';
        if ($this->metodo==='llenaTablaProgramaticoFinanciamiento') {
            $this->llenaTablaProgramaticoFinanciamiento();
        } else {
            $this->index();
        }
        return $this->returnJson();
    }

    public function index()
    {
        $this->setContenedor(CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO);
        if (isset($this->prm->tipo) && $this->prm->tipo ==='ventana') {
            $this->setContenedor('det_fs'.CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO);
        }
        $tabla = $this->ponTablaProgramatico();
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript($this->wrLlamaLlenaTabla($tabla->id));
    }

    public function ponTablaProgramatico(): object
    {
        $tabla = new stdClass();
        $tabla->id = "TABLA_" . CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO;
        $tabla->comunica = new stdClass();
        $tabla->tools = $this->ponFiltros();
        $tabla->inputFiltro = "inp_cat_prog_fin_search";
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaProgramaticoFinanciamiento';
        $tabla->comunica->filtro = isset($this->prm->filtro)  ? $this->prm->filtro : '';
        $tabla->comunica->paso = isset($this->prm->paso)  ? $this->prm->paso : '';
        $tabla->comunica->toa_id = isset($this->prm->toa_id)  ? $this->prm->toa_id : '';
        $tabla->comunica->ipu_id = isset($this->prm->ipu_id)  ? $this->prm->ipu_id : '';
        $tabla->comunica->mod_ue_id = isset($this->prm->mod_ue_id)  ? $this->prm->mod_ue_id : '';
        $tabla->trDblClick = '_encProgFinFunction';
        $tabla->esCompacta = true;

        $tr = array();
        $th = new stdClass();
        $th->texto = 'Encuadre';
        $th->width = '10%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Obra/Actividad';
        array_push($tr, $th);

        // $th = new stdClass();
        // $th->texto = 'Nombre Obra/Actividad';
        // array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave';
        $th->width = '8%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Financiamiento';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'prog';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        $td->ajustable = true;
        array_push($trbody, $td);

        // $td = new stdClass();
        // $td->campoTxt = 'npy';
        // $td->ajustable = true;
        // array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cveF';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ncve';
        $td->ajustable = true;
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $tabla;
    }

    private function ponFiltros()
    {
        return <<<HTML
            <div class="py-4 d-flex align-items-center gap-2">
                <input class="form-control w-50" id="inp_cat_prog_fin_search" name="inp_cat_prog_fin_search" placeholder="Filtro" />
            </div>
        HTML;
    }

    public function llenaTablaProgramaticoFinanciamiento()
    {
        $ejecutora = "";
        $paramsEje = [];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            // ERROR: De momento, filtramos por UE sólo cuando se trate de un usuario de nivel UE
            if ($_SESSION[_S]['nvj_id'] == 1) {
                $ejecutora = " AND ue_id = ?";
                array_push($paramsEje, $_SESSION[_S]['clasAdm-ue']);
            }
        }

        $select = "";
        $sql = "";
        $joinInversion = "";

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        $obr = "";
        $paramsObr = [];
        $filtro  = isset($_POST['filtro']) && $_POST['filtro'] != ''? $_POST['filtro'] : null;

        $ecf_id  = isset($_POST['ecf_id']) && intval($_POST['ecf_id']) > 0  ? intval($_POST['ecf_id']) : null;
        $toa_id  = isset($_POST['toa_id']) && intval($_POST['toa_id']) > 0  ? intval($_POST['toa_id']) : null;
        $mod_ue_id  = isset($_POST['mod_ue_id']) && intval($_POST['mod_ue_id']) > 0  ? intval($_POST['mod_ue_id']) : null;
        $ipu_id  = isset($_POST['ipu_id']) && intval($_POST['ipu_id']) > 0  ? intval($_POST['ipu_id']) : null;
        $paso  = isset($_POST['paso']) && $_POST['paso'] !="" ? $_POST['paso'] : null;
        $esFondos =  $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 && $mod_ue_id;

        if ($filtro) {
            switch ($filtro) {
                case 'enc_InversionObra':
                    $joinInversion = 'LEFT JOIN enc_InversionObra obr USING(epg_id, gpo_id, ur_id)';
                    /* El filtro ipu_id  filtra las obras por el contrato ya que en el en encuadre enc_InversionObra ahi vienen las obras que tiene cada contrato */
                    if ($paso) {
                        switch ((string) $paso) {
                            case '0': //otras clc´s
                                break;
                            case '1': //obra con contrato
                                if ($ipu_id) {
                                    $obr = " and ipu_id= ?";
                                    array_push($paramsObr, $ipu_id);
                                }
                                break;
                            case '2': //obra sin contrato
                                break;
                        }
                    }
                // no break
                case 'porUrClaveFinanciamiento':
                    $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
                    if ($esFondos && $mod_ue_id) {
                        $ejecutora = " AND ue_id = ?";
                        array_push($params, $mod_ue_id);
                    }
                    $sql = "
                        FROM presupuesto p
                            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                            INNER JOIN enc_Programatico USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                            INNER JOIN cat_ObraActividad oa USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                            INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                            INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                            " . $joinInversion . "
                        WHERE p.gpo_id = ? AND p.ur_id = ?
                            AND ur.eur_id = 5
                            AND f.sts_id  = 1
                            AND ((oa.toa_id = 1 AND oa.eoa_id = 4)
                            OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                            " . ($ecf_id ? ' AND p.ecf_id = ?' : "") . "
                            " . ($toa_id  ? ' AND oa.toa_id = ?' : "") . "
                            " . $obr . "
                            $ejecutora
                        GROUP BY gpo_id, ur_id, epg_id, p.ecf_id, cfi_nombre";
                        if ($ecf_id){ array_push($params, $ecf_id); }
                        if ($toa_id) {array_push($params, $toa_id); }
                        $arrayMerge = array_merge($params, $paramsEje); 
                    break;
                case 'Reintegro':
                    if ($esFondos && $mod_ue_id) {
                        $ejecutora = " AND ue_id = " . $mod_ue_id . " ";
                    }
                    $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
                    $sql = "
                        from presupuesto p
                            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                            INNER JOIN enc_Programatico USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                            INNER JOIN cat_ObraActividad oa USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                            INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                            INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                        WHERE p.gpo_id = ?
                            AND p.ur_id = ?
                            AND ur.eur_id = 5
                            AND f.sts_id  = 1
                            AND ((oa.toa_id = 1 AND oa.eoa_id = 4) or (oa.toa_id = 2 AND oa.eoa_id = 5))
                            " . ($ecf_id ? ' AND p.ecf_id = ?' : "") . "
                            " . ($toa_id  ? ' AND oa.toa_id = ?' : "") . "
                            $ejecutora
                            AND oa.oa_origen = 'R'
                            AND ((oa.oa_anticipo + oa_anticipo".APP_EJERCICIO_ANTERIOR.") - oa.oa_amortizacion) > 0
                        group by gpo_id, ur_id, epg_id, ecf_id, cfi_nombre
                    ";
                    if ($ecf_id){ array_push($params, $ecf_id); }
                    if ($toa_id) {array_push($params, 2); }
                    $arrayMerge = array_merge($params, $paramsEje);
                    break;
            }
        } else {
            $params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
            $sql = "
                FROM poa_OrigenRecurso o
                    INNER JOIN enc_Programatico p USING (epg_id, gpo_id, ur_id)
                    INNER JOIN enc_ClaveFinanciamiento f USING (ecf_id)
                    INNER JOIN cat_Proyecto py USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                    INNER JOIN cat_ObraActividad oa ON (p.gpo_id = oa.gpo_id AND p.ur_id = oa.ur_id AND p.prg_id = oa.prg_id AND p.spr_id = oa.spr_id AND p.py_id = oa.py_id AND p.oa_id = oa.oa_id)
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                WHERE o.gpo_id = ? AND o.ur_id = ?
                    AND oa.ue_id IS NOT NULL
                    AND f.sts_id = 1
                    AND ((oa.toa_id = 1 AND oa.eoa_id = 4) OR (oa.toa_id = 2 AND oa.eoa_id = 5))
                    $ejecutora
                    AND f.ecf_poa = 1
            ";
            $arrayMerge = array_merge($params, $paramsEje);
            //El estado 7 es validada por Egresos, a diferencia del 6 que solo es de planeación.
        }

        // Obtenemos la primera página a mostrar
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
                    break;
                case 'Reintegro':
                    $select = "
                        SELECT
                            p.epg_id,
                            p.ecf_id,
                            CONCAT( p.gpo_id, p.ur_id, '-', ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id ) AS prog,
                            if(p.oa_id != 0,
                                oa_nombre,
                                py_nombre) AS nombre,
                            oa_nombre AS npro,
                            py_nombre AS npy,
                            CONCAT( f.cff_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, '-', f.cfi_id, f.cfi_anio ) AS cveF,
                            cfi_nombre AS ncve
                    ";
                    break;
                default:
                    $select = "";
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
        }
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 80;
        $nav->idTabla = "TABLA_" . CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO;
        
        $this->setParams($arrayMerge);
        $this->setSql($select.$sql);

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros","warning");
                $(".tcl_ida").removeClass("loadingclas");');
        }
    }

    public static function ponF1($parametros = null) : string
    {
        $prm = $parametros ? 
            (is_array($parametros) ? (object) $parametros : ( is_object($parametros) ? $parametros :  new stdClass())
        ): new stdClass();

        $ecf_id = isset($prm->ecf_id) ? $prm->ecf_id : null;
        $epg_id = isset($prm->epg_id) ? $prm->epg_id : null;
        $filtro = isset($prm->filtro) ? $prm->filtro : '';
        
        $claveProgramatico = '';
        $nombreProgramatico = '';
        $claveFinanciamiento = '';
        $nombreFinanciamiento = '';
        $readOnly = '';

        if ($ecf_id) {
            $datosFin = self::formateaDatosFinanciamiento(ClasificacionFinanciamiento::PonEnVariablesEcf($ecf_id));
            $claveFinanciamiento = $datosFin['claveFinanciamiento'];
            $nombreFinanciamiento = $datosFin['nombreFinanciamiento'];
            $readOnly = 'disabled';
        }
        if ($epg_id) {
            $datosProgramatico = ClasificacionAdministrativa::PonEnVariablesEpg($epg_id, true);
            $claveProgramatico = $datosProgramatico['programatico'];
            $nombreProgramatico = $datosProgramatico['oa_nombre'];
            $readOnly = 'disabled';
        }

        $js = self::construyeJs($parametros);
        return <<<HTML
            <div class="row mb-2" id="div_programa_financiamiento_container" aria-filtro="{$filtro}">
                <fieldset class="col-md-12 col-xs-12">
                    <legend  style="color:#FF4000" class="mb-2">Encuadre Programático y Clave de Financiamiento
                        <span id="comoSeUsa" style="text-decoration: underline; color:red; cursor: pointer;">¿Cómo se usa ?</span>
                    </legend>
                    <br>
                    <label class="mb-2">Encuadre Programático</label>
                        <div class="row mb-2" >
                            <fieldset class="col-md-3 col-xs-12">
                                <input class="form-control F1Catalogo" id="encProg" 
                                    placeholder="Encuadre (F1/F2)" required="1" 
                                    maxlength="20" 
                                    name="encProg" type="text" size="20"
                                    value ="{$claveProgramatico}"
                                    {$readOnly}
                                    autocomplete="off"
                                    >
                            </fieldset>
                            <fieldset class="col-md-9 col-xs-12">
                                <input readonly="" class="form-control " id="nomProg" 
                                    placeholder="Nombre Obra/Actividad"
                                    name="nomProg" 
                                    value="{$nombreProgramatico}"
                                    disabled="1" 
                                    type="text"
                                >
                            </fieldset>
                        </div>
                    <label class="mb-2">Clave de Financiamiento</label>
                    <div class="row mb-2" >
                        <fieldset class="col-md-3 col-xs-12">
                            <input class="form-control F1Catalogo" id="encCveFin" 
                                placeholder="Clave (F1/F2)" 
                                required="1" maxlength="12" 
                                name="encCveFin" type="text" size="12"
                                value="{$claveFinanciamiento}"
                                {$readOnly}
                                autocomplete="off"
                                />
                        </fieldset>
                        <fieldset class="col-md-9 col-xs-12">
                            <input class="form-control " id="nomCveFin" 
                                placeholder="Nombre de la Clave"
                                name="nomCveFin"
                                disabled="1" type="text"
                                value="{$nombreFinanciamiento}"
                                >
                        </fieldset>
                    </div>
                </fieldset>
                <script type="text/javascript">
                    {$js}
                </script>
            </div>
        HTML;
    }

    public static function formateaDatosFinanciamiento($datos)
    {
        if (!is_array($datos)) {
            return [
                'claveFinanciamiento' => 'error',
                'nombreFinanciamiento' => 'error',
            ];
        }
        $encuadre = $datos['ter_id'] . "-"
            . $datos['cff_id']
            . $datos['fte_id']
            . $datos['sft_id']
            . $datos['ori_id']
            . $datos['sor_id'] . "."
            . $datos['cfi_id']
            . $datos['cfi_anio'];
        $nombre = $datos['cfi_nombre'];
        return [
            'claveFinanciamiento' => $encuadre,
            'nombreFinanciamiento' => $nombre,
        ];
    }

    public static function construyeJs($parametros)
    {
        $cveFinblurEvent = '';
        if (isset($parametros->cvefinBlur)) {
            $cveFinblurEvent =<<<JS
                $("#encCveFin").on('blur', function(){
                    {$parametros->cvefinBlur}
                });
            JS;
        }
        $programaticoBlur = '';
        if (isset($parametros->$programaticoBlur)) {
            $programaticoBlur =<<<JS
                $("#encCveFin").on('keydown', function(){
                    {$parametros->$programaticoBlur}
                });
            JS;
        }

        $onSelect = '';

        if (isset($parametros->onSelect)) {
            $onSelect = $parametros->onSelect;
        }
        $idContainer = CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO;

        $shootContainer  = <<<JS
            $("#{$idContainer}").remove();
            $("body").append(
                $("<div/>").prop("id","{$idContainer}")
            );
            $("#{$idContainer}").dialog({
                modal: true,
                autoOpen: true,
                title: "Seleccionar Obra y Clave de Financiamiento",
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
        $tipo = isset($parametros->tipo) && $parametros->tipo ==='ventana' ? 'ventana' : 'modal';
        if (isset($parametros->tipo) && $parametros->tipo ==='ventana') {
            $shootContainer =<<<JS
                load_viewer('{$idContainer}','Seleccionar Obra y Clave de Financiamiento', 'div','', 1031, true);
            JS;
        }

        $jsonComunica = [];
        $jsonComunica[] = "tipo: '".$tipo."'";
        if (isset($parametros->filtro)) {
            $jsonComunica[] = "filtro:'".$parametros->filtro."'";
        }
        if (isset($parametros->paso)) {
            $jsonComunica[] = 'paso:'.$parametros->paso;
        }
        if (isset($parametros->toa_id)) {
            $jsonComunica[] = 'toa_id:"'.$parametros->toa_id.'"';
        }

        if (isset($parametros->filtroObra)) {
            $jsonComunica[] = 'ipu_id:"'.$parametros->filtroId.'"';
        }
        if (isset($parametros->mod_ue_id)) {
            $jsonComunica[] = 'mod_ue_id: "'.$parametros->mod_ue_id.'"';
        }

        $jsonComunica ='{'. join(',',$jsonComunica).'}';
        return <<<JS
            $(function(){
                $("#encProg").off('keydown').on('keydown', function(e){
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        {$shootContainer}
                        comunica('srv/CatalogoProgramaticoFinanciamiento.php',{$jsonComunica});
                    } else if ( key == 113 ){ /* F2 muestra los combos de clasAdmin */
                        e.preventDefault();
                        $("#clasAdminSeccionCombos").toggle();
                    } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                });

                $("#encCveFin").on('keydown', function(event){
                    const key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                    if (key == 112) {
                        event.preventDefault();
                        {$shootContainer}
                        comunica('srv/CatalogoProgramaticoFinanciamiento.php', {$jsonComunica});
                    } else if( key == 113 ) {
                        if ( $("#encProg").val() != "" ) {
                            event.preventDefault();
                            comunica( "srv/ClasificacionFinanciamiento.php", {
                                metodoCveFin: "muestraCveFin"
                            });
                        } else {
                            notify("Debes seleccionar primero el Encuadre Programático", "warning");
                            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                        }
                    } else if (( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 )) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                })

                {$cveFinblurEvent}
                {$programaticoBlur}
            })
            function _encProgFinFunction (selection, event) {
                try {
                    if (!typeof(selection) !== 'object' || !event) {
                        $("#encProg").attr( "enc", selection.epg_id)
                            .attr( "ori", selection.prog)
                            .val( selection.prog);
                        $("#nomProg").val( selection.nombre?.replace( /\./g, "" ) );
                        $("#encCveFin").attr( "enc", selection.ecf_id )
                            .attr( "ori", selection.cveF )
                            .val( selection.cveF );
                        $("#nomCveFin").val( selection.ncve?.replace( /\./g, "" ) );
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin: "selEncProgFin",
                            encProg: selection.epg_id?.trim(),
                            prog: selection.prog?.trim(),
                            encCveFin: selection.ecf_id?.trim(),
                            cveFin: selection.cveF?.trim(),
                            nomFin: selection.ncve?.replace( /\./g, "" )
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
    new CatalogoProgramaticoFinanciamiento();
}
