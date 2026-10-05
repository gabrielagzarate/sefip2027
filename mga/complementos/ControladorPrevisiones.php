<?php

/**
 * Description of ControladorPrevisiones
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Update: May-2023
 * Comment: -----
 * Objective: Asignarle a la unidad responsable 115 una programa, subprograma y actividad.
 * En el caso de la actividad se le puede asignar una fuente de financiamiento y un objeto de gasto.
 * Se genera un reporte en formato PDF de las actividades con financiamiento y objeto de gasto.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 **/

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/mga/Prevision.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorPrevisiones extends WebResponse
{
    public $nvj;
    public $ip;
    public $usr_id;
    public function __construct()
    {
        parent::__construct();

        $this->nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        switch ($this->metodo) {
            case 'EliminarObjetoGasto':
                $this->EliminarObjetoGasto();
                break;
            case 'addModCveFin':
                $this->addModCveFin();
                break;
            case 'agregarCveFin':
                $this->agregarCveFin();
                break;
            case 'agregarCosteo':
                $this->agregarCosteo();
                break;
            case 'abcCosteo':
                $this->abcCosteo();
                break;
            case 'verCosteo':
                $this->verCosteo();
                break;
            case 'eliminaCveFin':
                $this->eliminaCveFin();
                break;
            case 'altaSubprograma':
                $this->altaSubprograma();
                break;
            case 'llenaPonTablaActividades':
                $this->llenaPonTablaActividades();
                break;
            case 'cargaSpr':
                $this->cargaSpr();
                break;
            case 'abcSpr':
                $this->abcSpr();
                break;
            case 'editSpr':
                $this->altaSubprograma(true);
                break;
            case 'ponTablaActividades':
            default:
                $this->ponTablaActividades();
                break;
        }

        $this->returnJson();
    }
    /**
     *    Pantalla principal de Actividades + Claves de Financiamiento + Partidas Genéricas + Partidas Específicas del programa actual
     */
    private function ponTablaActividades()
    {
        $this->mixScript('
            $("#clasadminue").html("");
            $("#clasadminprg").html("");
            $("#clasadminspr").html("");
            $("#clasadminpy").html("");
            $("#clasadminoa").html("");
        ');
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                $("#combour").val(115).change();
            ') . '
            $("#toggleCA").hide();
            $("#combour").attr("disabled",true);
        '
        );
        $this->mixStyle('
            tr.mygroups td
            {
                font-weight: bold;
                padding: 10px 20px;
                font-variant: small-caps;
            }
        ');

        // Sólo los de nivel > 2 (REVISORES) podrán agregar programas en éstas pantallas
        if ($this->nvj > 2) {
            $botones = '
                <button id="nuevoSpr" type="button" class="btn btn-outline-primary me-1" title="Registrar nuevo SubPrograma">
                    <i class="fa fa-plus me-2"></i>SubProgramas
                </button>
                <button id="nuevaA" type="button" class="btn btn-outline-primary me-1" title="Registrar nueva Actividad">
                    <i class="fa fa-plus me-2"></i>Actividad
                </button>
                <button id="modA" type="button" class="btn btn-outline-secondary me-1" title="Modificar la Actividad seleccionada">
                    <i class="fa fa-pencil" style="transform: rotate(0deg); font-size: 12px;"></i>
                </button>
                <button id="addCF" type="button" class="btn btn-outline-primary me-1" title="Adicionar Clave de Financiamiento a la Actividad seleccionada">
                    <i class="fa fa-plus me-2"></i>CF
                </button>
                <button id="addOG" type="button" class="btn btn-outline-primary me-1" title="Adicionar Objeto del Gasto a la Actividad seleccionada">
                    <i class="fa fa-plus me-2"></i>OG
                </button>
                <button id="eliCF" type="button" class="btn btn-outline-danger me-1" title="Eliminar Clave de Financiamiento de la Actividad, y todos sus Objetos del Gasto">
                    <i class="fa-solid fa-trash me-2"></i>CF
                </button>
                <button id="verOG" type="button" class="btn btn-outline-primary me-1" title="Ver Costeo de la Actividad seleccionada">
                    <i class="fa fa-eye me-2"></i>Costeo
                </button>
                <button id="eliOG" type="button" class="btn btn-outline-danger me-1" title="Eliminar  Objetos del Gasto">
                    <i class="fa fa-trash me-2"></i>OG
                </button>
                <button id="printact" type="button" class="btn btn-outline-danger me-1" title="Imprimir actividades">
                    <i class="fa-solid fa-file-pdf me-2"></i> PDF
                </button>
            ';
        } else {
            $botones = '';
        }

        // Cuadro de búsqueda (filtra las Unidades Responsables a mostrar)
        $busqueda = '
            <input type="search" width=30
                id="filtroTabla"
                class="form-control my-4"
                placeholder="Filtrar Actividad" />
        ';
        $this->agregaTools($botones);
        $tabla = new stdClass();
        $tabla->titulo = 'Actividades';
        $tabla->id = 'tbl-Act';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaPonTablaActividades';
        $tabla->comunica->prg_id = '{$("#prg_id").val()}';
        $tabla->comunica->spr_id = '{$("#spr_id").val()}';

        $tr = [];

        //Actividad
        $th = new stdClass();
        $th->texto = 'Actividad';
        array_push($tr, $th);

        //Clave de financiamiento
        $th = new stdClass();
        $th->texto = 'Cve. Fin.';
        $th->width = '12%';
        $th->align = 'center';
        array_push($tr, $th);

        //Nombre de clave de financiamiento
        $th = new stdClass();
        $th->texto = 'Nombre Clave de Financiamiento';
        $th->width = '40%';
        $th->align = 'center';
        array_push($tr, $th);

        //Partida Generica
        $th = new stdClass();
        $th->texto = 'PGen';
        $th->align = 'center';
        array_push($tr, $th);

        //Partido Especifica
        $th = new stdClass();
        $th->texto = 'PEsp';
        $th->align = 'center';
        array_push($tr, $th);

        //Total
        $th = new stdClass();
        $th->texto = 'Total';
        $th->width = '10%';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'oa';
        $td->attr = new stdClass();
        $td->attr->prg_id = 'prg_id';
        $td->attr->oa_id = 'oa_id';
        $td->attr->epg_id = 'epg_id';
        $td->attr->ecf_id = 'ecf_id';
        $td->attr->tieneCF = 'tieneCF';
        $td->attr->aju_id = 'aju_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cveF';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ncve';
        $td->align = 'left';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pg_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'pe_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $programas = new stdClass();
        $programas->id = 'prg_id';
        $programas->sql = '
            SELECT distinct p.prgs_id as prg_id, p.prgs_nombre
            FROM cat_Programas p
            WHERE p.prgs_id IN(997,999)
        ';
        $programas->label = 'Programas';
        $programas->change = '
            if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                metodo:"cargaSpr",
                prg_id:$(this).val()
                });
            }else{
                $("#spr_id").html("<option>Seleccione una opcion de la lista</option>");
                $("#spr_id").selectpicker("refresh");
                $("#tbody-tabla-tbl-Act").empty();
            }
        ';
        $spr = new stdClass();
        $spr->id = 'spr_id';
        $spr->label = '<button id="editSpr" type="button" class="btn btn-outline-primary me-1" title="Editar Subprograma"><i class="fa fa-pencil" style="transform: rotate(0deg); font-size: 12px; color: black"></i></button>Subprograma';

        if (isset($this->prm->prg_id)) {
            $programas->selecciona = $this->prm->prg_id;
            $programas->detonar = false;
            $spr->sql = '
                SELECT sp.spr_id,sp.spr_nombre 
                FROM cat_Subprograma sp
                INNER JOIN cat_Subprogramas sub ON sp.prg_id=sub.prgs_id AND sp.spr_id=sub.sprs_id
                WHERE sp.gpo_id = 1 AND sp.ur_id = 15 AND sp.spr_id != 0 
                AND sub.esr_id = 4 AND sp.prg_id = :prg';
            $spr->params = [':prg' => $this->prm->prg_id];
            $spr->selecciona = $this->prm->spr_id;
            $spr = $this->ponCombo($spr);
        } else {
            $spr = $this->templateCombo($spr);
        }

        // Dibujamos la estructura de la pantalla
        $this->mixHtml('
            <div class="tab-content">
                <div class="tab-pane active" id="prg1">
                    <div class="row">
                        <fieldset class="col-md-12">
                            <legend class="mb-4">' . $this->ponCombo($programas) . '</legend>
                            <legend>
                            ' . $spr . '
                            </legend>
                            <fieldset>' . $busqueda . '</fieldset>
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                </div>
            </div>
            <div id="historial" />
        ');

        if (isset($this->prm->prg_id) && isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $this->llenaPonTablaActividades();
        }

        $this->mixScript('
            $("#editSpr").click(function(){
                if($("#spr_id").val()!= "Seleccione una opcion de la lista") {
                    if($("#spr_id").val()!= ""){
                        comunica("' . $this->pantalla . '",{metodo:"editSpr",prg_id:$("#prg_id").val(),spr_id:$("#spr_id").val()});
                    }else{
                        notify("Selecciona el Subprograma a editar.","error");
                    }
                }
                else {
                    notify("Selecciona el Subprograma a editar.","error");
                }
            });

            $("#spr_id").change(function(){
                ' . $this->wrLlamaLlenaTabla('tbl-Act') . '
            });

            $("#combour").val(115).change();
            $("#tituloPantalla").html("");

            $("#regresaPrg").click(function(){
                $("nav").menu' . BD_SINPRES . '("clickMenu");
            });

            $("#nuevoSpr").click(function(){
                if($("#prg_id").val()) {
                    comunica("' . $this->pantalla . '",{
                        comboprg:$("#prg_id").val(),
                        combopy:"00",
                        metodo:"altaSubprograma",
                    });
                }else{
                    notify("Selecciona un Programa","error")
                }
            });

            $("#nuevaA").click(function(){
                if($("#prg_id").val() && $("#spr_id").val()){
                    $("#myModal").remove();
                    comunica("mga/complementos/ControladorActividades.php",{
                        comboprg:$("#prg_id").val(),
                        combosp:$("#spr_id").val(),
                        combopy:"00",
                        metodoCtrlAct:"addModActividad",
                        pp:"1"
                    });
                }else{
                    notify("Selecciona un Programa y un Subprograma ","error");
                }
            });

            $("#modA").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){

                    if( $("#prg_id").val()!="" && $("#spr_id").val()!="" ){
                        $("#myModal").remove();
                        comunica("mga/complementos/ControladorActividades.php",{
                            comboprg:$("#prg_id").val(),
                            combosp:$("#spr_id").val(),
                            combopy:"00",
                            oa_id:celda.oa_id,
                            metodoCtrlAct:"addModActividad",
                            pp:"1"
                        });
                    }else{
                        notify("Selecciona un Programa y un Subprograma ","error");
                    }
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            $("#addCF").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){
                    comunica("mga/complementos/ControladorPrevisiones.php",{
                        metodo : "addModCveFin",
                        epg_id : celda.epg_id,
                        prg_id : $("#prg_id").val(),
                        spr_id : $("#spr_id").val(),
                        oa_id  : celda.oa_id
                    });
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            $("#addOG").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){
                    // Sólo si ya tienen Clave de Financiamiento pueden dar de alta el Objeto del Gasto
                    if ( celda.tieneCF == 1 ) {

                        comunica("mga/complementos/ControladorPrevisiones.php", {
                            metodo : "agregarCosteo",
                            epg_id : celda.epg_id,
                            ecf_id : celda.ecf_id,
                            ue_id : celda.ue_id
                        });
                    } else {
                        notify("La Actividad DEBE tener Claves de Financiamiento antes de agregar Objetos del Gasto", "error");
                    }
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            $("#eliCF").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){
                    // Sólo si ya tienen Clave de Financiamiento pueden borrarla
                    if ( celda.tieneCF == 1 ) {
                    alertify.confirm("ELIMINAR CLAVE DE FINANCIAMIENTO", "Se eliminarán TODOS los Objetos del Gasto de ésta Clave de Financiamiento que correspondan a la Actividad seleccionada ¿Confirma que desea eliminar la Clave de Financiamiento?", function(){ 
                        comunica("mga/complementos/ControladorPrevisiones.php",{
                            metodo : "eliminaCveFin",
                            epg_id : celda.epg_id,
                            ecf_id : celda.ecf_id
                        });
                    }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});

                    } else {
                        notify("La Actividad no tienen ninguna Clave de Financiamiento registrada", "error");
                    }
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            $("#eliOG").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){
                    // Sólo si ya tienen Objeto de Gasto
                    if ( celda.tieneOG == 1 ) {
                    alertify.confirm("ELIMINAR OBJETO DEL GASTO DE LA CLAVE DE FINANCIAMIENTO", "Se eliminará el Objeto del Gasto de ésta Clave de Financiamiento que correspondan a la Actividad seleccionada ¿ Confirma que desea eliminar el Obejto del Gasto ?", function(){ 
                        comunica("mga/complementos/ControladorPrevisiones.php",{
                            metodo : "EliminarObjetoGasto",
                            ue_id : celda.ue_id,
                            epg_id : celda.epg_id,
                            ecf_id : celda.ecf_id,
                            pg_id : celda.pg_id,
                            pe_id : celda.pe_id
                        });
                    }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    } else {
                        notify("La Actividad no tienen ningun Objeto del Gasto registrado", "error");
                    }
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            $("#verOG").click(function(){
                // Si hay una seleccionada ...
                if (celda = $("#tbl-Act").tabla("getFilaSeleccion")){
                    // Sólo si ya tienen Objeto del Gasto pueden ver su costeo
                    if ( celda.pg_id.trim() != "" ) {
                        comunica("mga/complementos/ControladorPrevisiones.php", {
                            metodo : "verCosteo",
                            ue_id : celda.ue_id,
                            epg_id : celda.epg_id,
                            ecf_id : celda.ecf_id,
                            pg_id : celda.pg_id,
                            pe_id : celda.pe_id
                        });
                    } else {
                        notify("La Actividad + Clave de Financiamiento aún no tiene costeo", "error");
                    }
                } else {
                    notify( "Debes seleccionar la actividad (click sobre la línea)", "error" );
                }
            });

            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $("#homeMenu").click(function(){
                location.reload();
            });
            $(".tabla-footer").remove();

            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );

            $("#printact").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/mga/complementos/rep_MgaComplementos.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "prg_id").attr("value", $("#prg_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "spr_id").attr("value", $("#spr_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    /*
     *    Función principal del dibujado de pantalla
     */
    private function llenaPonTablaActividades()
    {
        $this->prm->prg_id = (isset($this->prm->prg_id)) ? $this->prm->prg_id : '';
        $this->prm->spr_id = (isset($this->prm->spr_id)) ? $this->prm->spr_id : '';
        $where = '';
        $params = [];

        if ($this->prm->prg_id != '') {
            $where .= ' AND a.prg_id = :prg ';
            $params[':prg'] = $this->prm->prg_id;
        }

        if ($this->prm->spr_id != '') {
            $where .= ' AND a.spr_id = :spr ';
            $params[':spr'] = $this->prm->spr_id;
        }

        $sql = '
            SELECT a.ue_id,epg.epg_id,ore.ecf_id AS ecf_id,a.prg_id,a.oa_id,CONCAT( a.oa_id, " - ", a.oa_nombre) AS oa,
                IFNULL( CONCAT( f.cff_id, "-", f.fte_id, f.sft_id, f.ori_id, f.sor_id, "-", f.cfi_id, f.cfi_anio ), "" ) AS cveF, IFNULL( cfi.cfi_nombre, "" ) AS ncve,
                CASE WHEN so.ter_id IS NULL THEN 0 ELSE 1 END AS tieneCF,
                IFNULL( pp.pg_id, "" ) AS pg_id, IFNULL( pp.pe_id, "" ) AS pe_id,
                CASE WHEN pp.pg_id IS NULL THEN 0 ELSE 1 END AS tieneOG,
                IFNULL( pp.mon_ene + pp.mon_feb + pp.mon_mar + pp.mon_abr + pp.mon_may + pp.mon_jun + pp.mon_jul + pp.mon_ago + pp.mon_sep + pp.mon_oct + pp.mon_nov + pp.mon_dic, 0 ) AS total
            FROM cat_ObraActividad a
            INNER JOIN enc_Programatico epg USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
            LEFT JOIN poa_OrigenRecurso ore USING( gpo_id, ur_id, epg_id )
            LEFT JOIN enc_ClaveFinanciamiento f USING( ecf_id )
            LEFT JOIN poa_Prevision pp USING( gpo_id, ur_id, ue_id, epg_id, ecf_id )
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            LEFT JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING( cff_id, fte_id, sft_id, ori_id, sor_id )
            WHERE epg.gpo_id=1 AND epg.ur_id=15 AND a.oa_id!=0
            ' . $where . '
        ';

        $this->setSql($sql);
        if ($where != '') {
            $this->setParams($params);
        }
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Act';
        $nav->nf = 999; // Sin paginación
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(document).ready(function() {
                $("table").filterTable({
                    inputSelector: "#filtroTabla",
                    minRows: 1
                });
                // Para que puedan seleccionar una línea
                $("#tbl-Act").jTableCheckbox();
                $("#tbl-Act").tableGroup({groupColumn: 1, groupClass: "mygroups", useNumChars: 0});

            });

            // Limpiamos el nombre del programa (lo usamos al entrar al subprograma)
            $("#otraInfoFooter").html( "" );
        ');
    }

    //Adicionamos una o más claves de financiamiento para el programa + actividad actual
    private function addModCveFin()
    {
        $epg_id = $this->prm->epg_id;
        $and = '';
        $params = [];
        if (date('Y') == APP_EJERCICIO_ANTERIOR) {
            $and = 'AND cfi_anio = :anio';
            $params[':anio'] = substr(APP_EJERCICIO, -2);
        }
        // Preparamos el combo que servirá para adicionar más de una clave de financiamiento
        $combo = new stdClass();
        $combo->id = 'comboCveFin';
        $combo->class = 'comboCveFin';
        $combo->sql = '
            SELECT DISTINCT cfi.cfi_clave, cfi.cfi_nombre, e.ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi
            JOIN enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE e.ecf_id NOT IN( SELECT ore.ecf_id FROM poa_OrigenRecurso ore WHERE ore.epg_id = ' . $epg_id . ' ) AND e.sts_id = 1 
            ' . $and . '
        ';
        if ($and != '') {
            $combo->params = $params;
        }
        $combo->search = true;
        $combo->comoAtr = 'ecf_id';
        $combo->enDiv = true;
        $combo->horizontal = 12;

        $this->setContenedor('paraModales');
        $this->mixHtml('
            <div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Adicionar una o más Claves de Financiamiento</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <legend mb-4>Clave de Financiamiento</legend>
                                    ' . $this->ponCombo($combo) . '
                                </fieldset>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" epg_id="' . $this->prm->epg_id . '" class="btn btn-outline-success me-1" id="adicionarCF">
                                <span class="fa fa-floppy-disk me-2"></span>Adicionar
                                </button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click( function() {
                $("#myModal").modal().remove();
                comunica("mga/complementos/ControladorPrevisiones.php",{
                    metodo   : "ponTablaActividades",
                    prg_id : ' . $this->prm->prg_id . ',
                    spr_id : ' . $this->prm->spr_id . '
                });
            });

            $("#adicionarCF").click( function() {
                epg = $(this).attr("epg_id");
                if( $("#comboCveFin").val() != "" ){
                    comunica( "mga/complementos/ControladorPrevisiones.php", {
                        metodo : "agregarCveFin",
                        epg_id : epg,
                        ecf_id : $("#comboCveFin option:selected").attr("ecf_id"),
                        prg_id : ' . $this->prm->prg_id . ',
                        spr_id : ' . $this->prm->spr_id . '
                    });
                } else {
                    notify( "Debes seleccionar la Clave de Financiamiento", "error" );
                }
            });

        ');
    }

    //Adicionamos una o más claves de financiamiento para el programa + actividad actual
    private function agregarCveFin()
    {
        $ip = $this->ip;
        $this->conectar();
        $params = [
            ':ip'          => $ip,
            ':usr'         => $this->usr_id,
            ':observacion' => 'REGISTRO INSERTADO DESDE PANTALLA MGA-Complementos-Alta de previsiones',
        ];
        $this->setSql('
            SET
            @usr_ip = INET_ATON(:ip),
            @usr_id = :usr,
            @bit_observacion = :observacion;
        ');
        $this->consultar($params);

        //Adicionamos la Clave de Financiamiento para el programa + actividad actual
        $this->setSql('
            INSERT INTO poa_OrigenRecurso (gpo_id,ur_id,epg_id,ecf_id,ore_folioModif,ore_folioLib,ore_oficioLib,ore_oficioAut,ore_idObra,ore_idMml,usr_id )
            VALUES(?, ?, ?, ?, ?, ?," "," "," "," ", ?);
        ');
        $params = [1, 15, $this->prm->epg_id, $this->prm->ecf_id, 0, 0, $this->usr_id];
        $this->consultar($params);
        $this->desconectar();

        //Como el combo muestra sólo aquellas que aún no se le hayan asignado al programa + actividad actual
        //volvemos a calcularlo, para no incluir la reciente
        $combo = new stdClass();
        $combo->id = 'comboCveFin';
        $combo->sql = '
            SELECT DISTINCT cfi.cfi_clave, cfi.cfi_nombre, e.ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi
            JOIN enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE e.ecf_id NOT IN(
                SELECT ore.ecf_id
                FROM poa_OrigenRecurso ore
                WHERE ore.epg_id = :epg);
        ';
        $combo->params = [':epg' => $this->prm->epg_id];
        $combo->comoAtr = 'ecf_id';
        $combo->enDiv = true;
        $combo->horizontal = 1;
        $this->recargaCombo($combo);

        $this->mixScript('
            notify("Se ha adicionado exitosamente la Clave de Financiamiento", "success" );
            $("#myModal").modal().remove();
            comunica("mga/complementos/ControladorPrevisiones.php",{
                metodo   : "ponTablaActividades",
                prg_id : ' . $this->prm->prg_id . ',
                spr_id : ' . $this->prm->spr_id . '
            });
        ');
    }
    /*
     * Adicionamos Partida Genérica + Partida Específica + Costeo mensual para el encuadre de programa y clave
     * de financiamiento seleccionado
     */
    private function agregarCosteo()
    {
        $this->setContenedor('paraModales');

        //Si clasAdm-epd es nulo no carga combo de partida generica
        $_SESSION[_S]['clasAdm-epg'] = 0;

        $objGto = new stdClass();
        $objGto->required = true;
        $objGto->vienDe = 'Altas previsiones';
        $form = new stdClass();
        $form->id = 'datosCosteo';
        $form->controlador = 'mga/complementos/ControladorPrevisiones.php';
        $form->metodo = 'abcCosteo';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'a';
        $form->datosExtra->epg_id = $this->prm->epg_id;
        $form->datosExtra->ecf_id = $this->prm->ecf_id;
        $form->datosExtra->ue_id = $this->prm->ue_id;
        $form->datosExtra->montos = '"{$(\'#montosA\').data(\'wr-calendarioMontos\').options.montos}"';
        $form = new form($form);
        $form->mixHtml('
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <Calendario id="montosA"></Calendario>
                </fieldset>
            </div>
        ');
        $this->mixHtml('
            <div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Agregar Objeto del Gasto + Costeo</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>

                        </div>
                        <div class="modal-body">
                            ' . $form->ponForm() . '
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-test btn-outline-success" id="acepta-modalNewCosteo" form="datosCosteo">
                                <span class="fa fa-floppy-disk me-2"></span>Adicionar
                            </button>
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->conectar();
        $this->setSql('
            SELECT ep.prg_id,ep.spr_id
            FROM enc_Programatico ep
            WHERE ep.epg_id = ?
        ');
        $this->consultar([$this->prm->epg_id]);
        $prg = $this->extraer();
        $this->desconectar();
        $this->mixScript('
            $("#myModal").modal().show();
            $("#montosA").calendarioMontos();
            $(".mes.moneda").val(0).blur();

            $(".cerrar").click( function() {
                $("#myModal").remove();
                comunica("mga/complementos/ControladorPrevisiones.php",{
                    metodo : "ponTablaActividades",
                    prg_id : ' . $prg['prg_id'] . ',
                    spr_id : ' . $prg['spr_id'] . ',
                    comboprg : $("#nuevaA").attr("prg_id")
                });
            });
        ');
    }
    /*
     *    Guardamos el Objeto del Gasto + su costeo
     */
    private function abcCosteo()
    {
        $ip = $this->ip;
        $this->prm->gpo_id = 1;
        $this->prm->ur_id = 15;
        $this->prm->total = 0;
        $nr = 0;
        $elimina = [];

        switch ($this->prm->abc) {
            case 'a':
                $this->prm->bit_observacion = 'REGISTRO INSERTADO DESDE ALTA DE PREVISIONES';
                $sql = '
                    SELECT * FROM poa_Prevision poap
                    WHERE poap.gpo_id = :gpo_id
                    AND poap.ur_id = :ur_id
                    AND poap.ue_id = :ue_id
                    AND poap.epg_id = :epg_id
                    AND poap.ecf_id = :ecf_id
                    AND poap.pg_id = :pg_id
                    AND poap.pe_id = :pe_id
                    AND poap.tcp_id = :tcp_id
                ';
                $parametros = [
                    ':gpo_id' => $this->prm->gpo_id,
                    ':ur_id'  => $this->prm->ur_id,
                    ':ue_id'  => $this->prm->ue_id,
                    ':epg_id' => $this->prm->epg_id,
                    ':ecf_id' => $this->prm->ecf_id,
                    ':pg_id'  => $this->prm->pg_id,
                    ':pe_id'  => $this->prm->pe_id,
                    ':tcp_id' => 'P',
                ];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($parametros);
                $this->desconectar();
                $nr = $this->getNumeroRegistros();
                break;
            case 'c':
                if ($nr == 0) {
                    $this->prm->montos = explode(',', $this->prm->montos);
                    $this->mixScript('$("#myModal .cerrar").click();');
                } else {
                    $this->mixScript('
                        notify("Objeto del gasto ya registrado en esta clave de financiamiento", "error");
                    ');
                    return false;
                }

                $this->prm->bit_observacion = 'REGISTRO MODIFICADO DESDE ALTA DE PREVISIONES';
                break;
            case 'b':
                // Al migrarlo: Validar que ctrl2026.scp_Presupuestal y ctrl2026.scp_DetalleModificacion existan
                //     Cuando se suba a produccion se debe quitar
                $sql = '
                    SELECT ' . BD_SINPRES . '.fn_tableExists("scp_Presupuestal","' . BD_CTRL . '") AS scp_Presupuestal,' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacion","' . BD_CTRL . '") AS scp_DetalleModificacion;
                ';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar();
                $existenTablas = $this->extraer();

                if ($existenTablas['scp_Presupuestal'] == 1 && $existenTablas['scp_DetalleModificacion'] == 1) {
                    // Nuevas reglas
                    // Si se elimina un objeto del gasto primero verificamos que no existan registros en
                    // ctrl2026.scp_Presupuestal
                    // ctrl2026.scp_DetalleModificacion
                    // ctrl2026.poa_Prevision
                    $sql = '
                        SELECT gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id
                        FROM ' . BD_CTRL . '.scp_Presupuestal 
                        INNER JOIN ' . BD_CTRL . '.presupuesto USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE gpo_id = :gpo 
                        AND ur_id = :ur 
                        AND ue_id = :ue 
                        AND epg_id = :epg 
                        AND pg_id = :pg 
                        AND pe_id = :pe
                        AND ecf_id = :ecf 
                        AND tcp_id = :tcp
                    ';
                    $parametros = [
                        ':gpo' => $this->prm->gpo_id,
                        ':ur'  => $this->prm->ur_id,
                        ':ue'  => $this->prm->ue_id,
                        ':epg' => $this->prm->epg_id,
                        ':pg'  => $this->prm->pg_id,
                        ':pe'  => $this->prm->pe_id,
                        ':ecf' => $this->prm->ecf_id,
                        ':tcp' => $this->prm->tcp_id,
                    ];
                    $this->setSql($sql);
                    $this->conectar();
                    $this->consultar($parametros);
                    $this->desconectar();
                    if ($this->getNumeroRegistros() > 0) {
                        $this->mixScript('notify("No puede eliminar el objeto de gasto porque ya se tiene presupuesto aprobado o modificado","warning");');
                        return false;
                    }
                    $sql = '
                        SELECT gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id 
                        FROM ' . BD_CTRL . '.scp_DetalleModificacion
                        INNER JOIN ' . BD_CTRL . '.presupuesto USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE gpo_id = :gpo_id
                        AND ur_id = :ur_id
                        AND ue_id = :ue_id
                        AND epg_id = :epg_id
                        AND pg_id = :pg_id
                        AND pe_id = :pe_id
                        AND ecf_id = :ecf_id
                        AND tcp_id = :tcp_id
                    ';
                    $parametros = [
                        ':gpo_id' => $this->prm->gpo_id,
                        ':ur_id'  => $this->prm->ur_id,
                        ':ue_id'  => $this->prm->ue_id,
                        ':epg_id' => $this->prm->epg_id,
                        ':pg_id'  => $this->prm->pg_id,
                        ':pe_id'  => $this->prm->pe_id,
                        ':ecf_id' => $this->prm->ecf_id,
                        ':tcp_id' => $this->prm->tcp_id,
                    ];
                    $this->setSql($sql);
                    $this->conectar();
                    $this->consultar($parametros);
                    $this->desconectar();
                    if ($this->getNumeroRegistros() > 0) {
                        $this->mixScript('notify("No puede eliminar el objeto de gasto porque ya se encuentra dentro del detalle de modificaciones presupuestarias","warning");');
                        return false;
                    }
                }

                $mon = new Prevision();
                $mon->setGpo_id($this->prm->gpo_id);
                $mon->setUr_id($this->prm->ur_id);
                $mon->setUe_id($this->prm->ue_id);
                $mon->setEpg_id($this->prm->epg_id);
                $mon->setPg_id($this->prm->pg_id);
                $mon->setPe_id($this->prm->pe_id);
                $mon->setEcf_id($this->prm->ecf_id);
                $mon->setTcp_id($this->prm->tcp_id);
                $mon->select();
                $this->prm->epg_id = $mon->getEpg_id();
                $this->prm->ecf_id = $mon->getEcf_id();
                $this->prm->tcp_id = $mon->getTcp_id();

                $this->prm->bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA';

                $wherePresupuestos = '
                    WHERE gpo_id = ' . $this->prm->gpo_id . '
                    AND ur_id = ' . $this->prm->ur_id . '
                    AND ue_id = ' . $this->prm->ue_id . '
                    AND epg_id = ' . $this->prm->epg_id . '
                    AND ecf_id = ' . $this->prm->ecf_id . '
                    AND pg_id = ' . $mon->getPg_id() . '
                    AND pe_id = ' . $mon->getPe_id() . '
                    AND tcp_id= "' . $this->prm->tcp_id . '"
                ';

                // Ojo: Se eliminan todos los registros desde el disparador de ctrl2026.presupuesto
                array_push($elimina, 'DELETE FROM ' . BD_CTRL . '.presupuesto ' . $wherePresupuestos . ';');
                break;
        }

        $this->prm->tcp_id = 'P';
        // Del ticket #160518(Migrar MGA-Complementos-Alta de previsiones)
        // Si una partida especifica esta deshabilitada mostrar el mensaje correcto.
        switch ($this->prm->abc) {
            case 'a':
                $this->conectar();

                $mensajePeInhabilitada = 'NO SE PUEDE REGISTRAR, LA PARTIDA ESPECÍFICA SE ENCUENTRA INHABILITADA';

                $this->setSql('
                    SELECT sts_id
                    FROM cat_PartidaEspecifica
                    WHERE pg_id = ? AND pe_id = ?;
                ');
                $params = [$this->prm->pg_id, $this->prm->pe_id];
                $this->consultar($params);
                $resultQuery = $this->extraer();
                if ($resultQuery['sts_id'] == 2) {
                    $res = new stdClass();
                    $res->comunica = new stdClass();
                    $res->comunica->script = '
                        alertify.confirm("Información", "' . $mensajePeInhabilitada . '", function(){ 
                        }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    ';
                    echo json_encode($res);
                    die();
                }

                // Del ticket #245373, si la UR no tiene asignado la partida generica que desean registrar
                // entonces le indicamos que lo consulte con su analista

                $mensajeNoHayVinculadaPGaUr = 'La UR <strong>' . $this->prm->gpo_id . $this->prm->ur_id . '</strong> no tiene asignado la partida genérica <strong>' . $this->prm->pg_id . '</strong>';
                $sql = '
                    SELECT gpo_id,ur_id, pg_id
                    FROM ' . BD_CTRL . '.enc_PartGenUr
                    WHERE gpo_id = :gpo AND ur_id = :ur AND pg_id = :pg;
                ';
                $parametros = [
                    ':gpo' => $this->prm->gpo_id,
                    ':ur'  => $this->prm->ur_id,
                    ':pg'  => $this->prm->pg_id,
                ];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($parametros);
                if ($this->getNumeroRegistros() == 0) {
                    $res = new stdClass();
                    $res->comunica = new stdClass();
                    $res->comunica->script = '
                        alertify.confirm("Información", "' . $mensajeNoHayVinculadaPGaUr . '", function(){ 
                        }, function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                        $("#myModal").remove();
                    ';
                    echo json_encode($res);
                    die;
                }
                $this->desconectar();

                if ($nr == 0) {
                    $this->prm->montos = explode(',', $this->prm->montos);
                    $this->mixScript('$("#myModal .cerrar").click();');
                } else {
                    $this->mixScript('
                        $("#myModal").remove();
                        notify("Objeto del gasto ya registrado en esta clave de financiamiento", "error");
                    ');
                    return false;
                }
                break;
        }
        // Adicionamos la Partida Genérica + Partida Específica + Costeo
        Prevision::abc($this->prm);

        // Eliminamos la entrada de las tablas de presupuesto
        foreach ($elimina as $sqlElimina) {
            $this->conectar();
            $params = [
                ':ip'          => $ip,
                ':usr'         => $this->usr_id,
                ':observacion' => $this->prm->bit_observacion,
            ];
            $this->setSql('
                SET
                @usr_ip = INET_ATON(:ip),
                @usr_id = :usr,
                @bit_observacion = :observacion;
            ');
            $this->consultar($params);

            $this->setSql($sqlElimina);
            $this->consultar();
            $this->desconectar();
        }

        $notifica = ($this->prm->abc == 'a') ? 'Se agregó' : 'Se eliminó';
        $this->mixScript('
            $("#myModal").remove();
            comunica("mga/complementos/ControladorPrevisiones.php",{
                metodo : "ponTablaActividades",
                prg_id : $("#prg_id").val(),
                spr_id : $("#spr_id").val()
            });
            notify("' . $notifica . ' exitosamente");
        ');
    }
    /*
     *    Eliminamos una clave de financiamiento con todos sus Objetos del Gasto registrados
     */
    private function eliminaCveFin()
    {
        $ip = $this->ip;
        // Primero cancelamos el costeo de todos los objetos del gasto actuales
        $c1 = new AccesoDatos();
        $sql = '
            SELECT poap.gpo_id,poap.ur_id,poap.ue_id,poap.epg_id,poap.pg_id,poap.pe_id,poap.ecf_id,poap.tcp_id FROM poa_Prevision poap
            WHERE poap.gpo_id = :gpo
            AND poap.ur_id = :ur
            AND poap.epg_id = :epg
            AND poap.ecf_id = :ecf
        ';
        $parametros = [
            ':gpo' => 1,
            ':ur'  => 15,
            ':epg' => $this->prm->epg_id,
            ':ecf' => $this->prm->ecf_id,
        ];
        $c1->setSql($sql);
        $c1->conectar();
        $c1->consultar($parametros);
        $c1->desconectar();
        while ($row = $c1->extraer()) {
            $this->prm->ue_id = $row['ue_id'];
            $this->prm->epg_id = $row['epg_id'];
            $this->prm->pg_id = $row['pg_id'];
            $this->prm->pe_id = $row['pe_id'];
            $this->prm->ecf_id = $row['ecf_id'];
            $this->prm->tcp_id = $row['tcp_id'];
            $this->prm->abc = 'b';
            $this->abcCosteo(false);
        }
        // Y luego borramos todo rastro de ésta clave de financiamiento, para el programa + actividad actuales
        $this->conectar();
        $params = [
            ':ip'          => $ip,
            ':usr'         => $this->usr_id,
            ':observacion' => 'REGISTRO ELIMINADO DESDE ALTA DE PREVISIONES',
        ];
        $this->setSql('
            SET
            @usr_ip = INET_ATON(:ip),
            @usr_id = :usr,
            @bit_observacion = :observacion
        ');
        $this->consultar($params);

        $this->setSql('
            DELETE FROM poa_OrigenRecurso
            WHERE gpo_id = 1 
            AND ur_id = 15
            AND epg_id = ?
            AND ecf_id = ?;
        ');
        $this->consultar([$this->prm->epg_id, $this->prm->ecf_id]);
        $this->desconectar();

        $this->conectar();
        $this->setSql('SELECT epg.prg_id,epg.spr_id FROM enc_Programatico epg WHERE epg.epg_id = ?;');
        $params = [$this->prm->epg_id];
        $this->consultar($params);
        $prg = $this->extraer();
        $this->desconectar();

        $this->mixScript('
            notify("Clave de Financiamiento eliminada exitosamente");
            comunica("mga/complementos/ControladorPrevisiones.php",{
                metodo : "ponTablaActividades",
                prg_id : ' . $prg['prg_id'] . ',
                spr_id : ' . $prg['spr_id'] . ',
                comboprg : $("#nuevaA").attr("prg_id")
            });
        ');
    }

    private function EliminarObjetoGasto()
    {
        $c1 = new AccesoDatos();
        $sql = '
            SELECT poap.gpo_id,poap.ur_id,poap.ue_id,poap.epg_id,poap.pg_id,poap.pe_id,poap.ecf_id,poap.tcp_id
            FROM ' . BD_CTRL . '.poa_Prevision poap
            WHERE poap.gpo_id = ?
            AND poap.ur_id = ?
            AND poap.epg_id = ?
            AND poap.ecf_id = ?
            AND poap.ue_id = ?
            AND poap.pg_id = ?
            AND poap.pe_id = ?
            AND poap.tcp_id = ?
        ';
        $parametros = [1, 15, $this->prm->epg_id, $this->prm->ecf_id, $this->prm->ue_id, $this->prm->pg_id, $this->prm->pe_id, 'P'];
        $c1->setSql($sql);
        $c1->conectar();
        $c1->consultar($parametros);
        $c1->desconectar();

        if ($c1->getNumeroRegistros() > 0) {
            $row = $c1->extraer();
            $this->prm->ue_id = $row['ue_id'];
            $this->prm->epg_id = $row['epg_id'];
            $this->prm->pg_id = $row['pg_id'];
            $this->prm->pe_id = $row['pe_id'];
            $this->prm->ecf_id = $row['ecf_id'];
            $this->prm->tcp_id = $row['tcp_id'];
            $this->prm->abc = 'b';
            $seEliminoTodo = $this->abcCosteo(false);
            if ($seEliminoTodo == false) {
                return false;
            }

            $this->conectar();
            $this->setSql('
                SELECT epg.prg_id,epg.spr_id
                FROM enc_Programatico epg
                WHERE epg.epg_id = ?;
            ');
            $params = [$this->prm->epg_id];
            $this->consultar($params);
            $prg = $this->extraer();
            $this->desconectar();

            // Volvemos a recargar ...
            $this->mixScript('
                notify("Objetos del Gasto eliminado exitosamente");
                comunica("mga/complementos/ControladorPrevisiones.php",{
                    metodo : "ponTablaActividades",
                    prg_id : ' . $prg['prg_id'] . ',
                    spr_id : ' . $prg['spr_id'] . ',
                    comboprg : $("#nuevaA").attr("prg_id")
                });
            ');
        } else {
            $this->mixScript(' notify("La Clave Presupuestal no existe","error"); ');
        }
    }

    /*
     *    Mostramos solamente el costeo registrado
     */
    private function verCosteo()
    {
        $this->prm->gpo_id = 1;
        $this->prm->ur_id = 15;

        $this->setContenedor('paraModales');
        $form = new stdClass();
        $form->id = 'datosCosteo';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcCosteo';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'c';
        $form->datosExtra->ue_id = $this->prm->ue_id;
        $form->datosExtra->epg_id = $this->prm->epg_id;
        $form->datosExtra->pg_id = $this->prm->pg_id;
        $form->datosExtra->pe_id = $this->prm->pe_id;
        $form->datosExtra->ecf_id = $this->prm->ecf_id;
        $form->datosExtra->montos = '"{$(\'#montos\').data(\'wr-calendarioMontos\').options.montos}"';
        $form = new form($form);
        $form->mixHtml('
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <Calendario id="montos"></Calendario>
                </fieldset>
            </div>
        ');
        $this->mixHtml('
            <div id="modalNewCosteo" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Consultar costeo registrado</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </div>
                        <div class="modal-body">
                            <p>' . $form->ponForm() . '</p>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        ');
        $sql = '
            SELECT poap.mon_ene, poap.mon_feb, poap.mon_mar, poap.mon_abr, poap.mon_may, poap.mon_jun, poap.mon_jul, poap.mon_ago, poap.mon_sep, poap.mon_oct, poap.mon_nov, poap.mon_dic
            FROM poa_Prevision poap 
            WHERE poap.gpo_id = :gpo 
            AND poap.ur_id = :ur 
            AND poap.ue_id = :ue 
            AND poap.epg_id = :epg 
            AND poap.ecf_id = :ecf 
            AND poap.pg_id = :pg 
            AND poap.pe_id = :pe 
            AND poap.tcp_id = :tcp_id
        ';
        $parametros = [
            ':gpo'    => $this->prm->gpo_id,
            ':ur'     => $this->prm->ur_id,
            ':ue'     => $this->prm->ue_id,
            ':epg'    => $this->prm->epg_id,
            ':ecf'    => $this->prm->ecf_id,
            ':pg'     => $this->prm->pg_id,
            ':pe'     => $this->prm->pe_id,
            ':tcp_id' => 'P',
        ];
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($parametros);
        $this->desconectar();
        $costeo = $this->sacar();
        $costeo = [$costeo[0], $costeo[1], $costeo[2], $costeo[3], $costeo[4], $costeo[5], $costeo[6], $costeo[7], $costeo[8], $costeo[9], $costeo[10], $costeo[11]];
        $this->mixScript('
            $("#montos").calendarioMontos( { montos:' . (json_encode($costeo)) . ' } );
            $("#modalNewCosteo").modal().show();
            $(".cerrar").click( function() {
                $("#modalNewCosteo").modal().remove();
            });
        ');
    }

    private function cargaSpr($detonar = true)
    {
        $spr = new stdClass();
        $spr->id = 'spr_id';
        //Si solo habia un programa y un subprograma no habia forma de cargar sus actividades
        $spr->detonar = $detonar;
        //La correccion anterior provocaba que si existian dos o mas subprogramas cargara
        //las actividades del primero, pero no lo seleccionaba en el combo.
        $spr->mensaje = false;

        if (!$detonar && isset($this->prm->spr_id)) {
            $spr->selecciona = $this->prm->spr_id;
        }

        $spr->sql = '
            SELECT sp.spr_id,sp.spr_nombre 
            FROM cat_Subprograma sp
            INNER JOIN cat_Subprogramas sub ON sp.prg_id=sub.prgs_id AND sp.spr_id=sub.sprs_id
            WHERE sp.prg_id = :prg 
            AND sub.esr_id = 4 
            AND sp.spr_id != 0 
            AND gpo_id = 1 AND ur_id = 15;
        ';
        $spr->params = [':prg' => $this->prm->prg_id];

        $this->recargaCombo($spr);
    }

    private function altaSubprograma($edicion = false)
    {
        $titulo = '';
        $this->setContenedor('paraModales');
        if ($edicion) {
            $titulo = 'Editar ';
            $this->mixScript('
                $("#myModal").modal().show();
                $(".cerrar").click(function(){
                    $("#myModal").modal().remove();
                });
                $("#guardarSpr").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcSpr",
                        abc:"C",
                        prg_id:' . $this->prm->prg_id . ',
                        spr_id:' . $this->prm->spr_id . ',
                        nombre:$("#spr_nombre").val()
                    })
                });
            ');
            $this->conectar();
            $this->setSql('
                SELECT sp.spr_nombre 
                FROM cat_Subprograma sp 
                WHERE sp.gpo_id = ? 
                AND sp.ur_id = ? AND sp.prg_id = ?
                AND  sp.spr_id = ?;
            ');
            $params = [1, 15, $this->prm->prg_id, $this->prm->spr_id];
            $this->consultar($params);
            $nombre = $this->extraer();
            $nombre = $nombre['spr_nombre'];
            $this->desconectar();
        } else {
            $nombre = '';
            $this->mixScript('
                $("#myModal").modal().show();
                $(".cerrar").click(function(){
                    $("#myModal").modal().remove();
                });
                $("#guardarSpr").click(function(){
                    comunica("' . $this->pantalla . '",{metodo:"abcSpr",prg_id:' . $this->prm->comboprg . ',nombre:$("#spr_nombre").val()})
                });
            ');
        }
        $this->mixHtml('
            <div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">' . $titulo . 'Nombre del SubPrograma</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <legend>Subprograma</legend>
                                    <br />
                                    <p><input id="spr_nombre" type="text" class="form-control" value="' . $nombre . '"></p>

                                </fieldset>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" epg_id="" class="btn btn-outline-success me-1" id="guardarSpr"><span class="fa fa-floppy-disk me-2"></span>Guardar</button>
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
    }

    private function abcSpr()
    {
        $user = $this->usr_id;
        $ip = $this->ip;
        if (isset($this->prm->abc)) {
            $this->conectar();
            $params = [
                ':ip'          => $ip,
                ':usr'         => $user,
                ':observacion' => 'REGISTRO EDITADO DESDE PANTALLA MGA-Complementos-Alta de previsiones',
            ];
            $this->setSql('
                SET
                @usr_ip = INET_ATON(:ip),
                @usr_id = :usr,
                @bit_observacion = :observacion
            ');
            $this->consultar($params);

            $this->setSql('
                UPDATE cat_Subprograma 
                SET spr_nombre = ?
                WHERE  prg_id = ?
                AND  spr_id = ?;
            ');
            $this->consultar([mb_strtoupper($this->prm->nombre, 'UTF-8'), $this->prm->prg_id, $this->prm->spr_id]);
            $this->desconectar();
            $this->mixScript('notify("Subprograma ' . $this->prm->spr_id . ' modificado");');
        } else {

            //Se va a consultar si ya existe el subprograma 0 en el enc_UrSubprogramas si no existe se tiene que hacer el insert si ya existe se hace
            //Nueva regla [Ticket 872797 17/01/2019 ]: Si es de la UR: 115 No importa en que estado se encuentre
            //El subprograma 00 con que exista el programa con eso seria suficiente.
            $c = new AccesoDatos();
            $sql = '
                SELECT * FROM enc_UrSubprogramas 
                WHERE gpo_id = :gpo AND ur_id = :ur 
                AND prgs_id = :prgs AND sprs_id = :sprs
            ';
            $parametros = [':gpo' => 1, ':ur' => 15, ':prgs' => $this->prm->prg_id, ':sprs' => 0];
            $c->setSql($sql);
            $c->conectar();
            $c->consultar($parametros);
            $c->desconectar();

            if ($c->getNumeroRegistros() > 0) {
                //Primero se tiene que insertar en cat_Subprogramas el subprograma
                $this->conectar();

                $this->setSql('
                    INSERT INTO cat_Subprogramas(prgs_id,sprs_id,sprs_nombre,pdf_id,esr_id,efn_id,epp_id,usr_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?);
                ');
                $params = [$this->prm->prg_id, 1, mb_strtoupper($this->prm->nombre, 'UTF-8'), 0, 4, 1, 2, $user];
                $this->consultar($params);

                $this->setSql('SELECT @sprs_id sprs_id;');
                $this->consultar();
                $sprs_id = $this->extraer();

                $params = [
                    ':ip'  => $ip,
                    ':usr' => $user,
                ];
                $this->setSql('
                    SET
                    @usr_ip = INET_ATON(:ip),
                    @usr_id = :usr;
                ');
                $this->consultar($params);

                $this->setSql('
                    INSERT INTO enc_UrSubprogramas(gpo_id,ur_id,prgs_id,sprs_id,sts_id,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?);
                ');
                $params = [
                    1,
                    15,
                    $this->prm->prg_id,
                    $sprs_id['sprs_id'],
                    1,
                    $user,
                ];
                $this->consultar($params);
                $this->desconectar();
            } else {
                //No existe y lo insertamos el subprograma 0  y el programa que quieran ingresar
                $c1 = new AccesoDatos();
                $c1->conectar();

                $params = [
                    ':ip'          => $ip,
                    ':usr'         => $user,
                    ':observacion' => 'REGISTRO EDITADO DESDE PANTALLA MGA-Complementos-Alta de previsiones',
                ];
                $c1->setSql('
                    SET
                    @usr_ip = INET_ATON(:ip),
                    @usr_id = :usr,
                    @bit_observacion = :observacion
                ');
                $c1->consultar($params);

                $c1->setSql('
                    INSERT INTO enc_UrSubprogramas (gpo_id,ur_id,prgs_id,sprs_id,sts_id,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?);
                ');
                $values = [
                    1,
                    15,
                    $this->prm->prg_id,
                    0,
                    1,
                    $user,
                ];
                $c1->consultar($values);
                $c1->desconectar();

                $this->conectar();

                $this->setSql('
                    INSERT INTO cat_Subprogramas(prgs_id,sprs_id,sprs_nombre,pdf_id,esr_id,efn_id,epp_id,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?);
                ');
                $values = [
                    $this->prm->prg_id,
                    1,
                    $this->prm->nombre,
                    0,
                    4,
                    1,
                    2,
                    $user,
                ];
                $this->consultar($values);

                $this->setSql('SELECT @sprs_id sprs_id;');
                $this->consultar();
                $sprs_id = $this->extraer();

                $params = [
                    ':ip'  => $ip,
                    ':usr' => $user,
                ];
                $this->setSql('
                    SET
                    @usr_ip = INET_ATON(:ip),
                    @usr_id = :usr;
                ');
                $this->consultar($params);

                $this->setSql('
                    INSERT INTO enc_UrSubprogramas(gpo_id,ur_id,prgs_id,sprs_id,sts_id,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?);
                ');
                $values = [1, 15, $this->prm->prg_id, $sprs_id['sprs_id'], 1, $user];
                $this->consultar($values);
                $this->desconectar();
            }

            $this->mixScript('notify("Subprograma ' . $sprs_id['sprs_id'] . ' creado","success");');
            $this->prm->spr_id = $sprs_id['sprs_id'];
        }
        $this->cargaSpr(false);
        $this->mixScript('$("#myModal").modal().remove();$("#tbl-Act").tabla("llenaTabla");');
    }
}
if (isset($_SESSION[_S]['prm']['M']['4']['2'])) {
    new ControladorPrevisiones();
} else {
    WebResponse::sinPermisos();
}
