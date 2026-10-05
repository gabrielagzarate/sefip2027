<?php
declare (strict_types = 1);
set_time_limit(0);
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
include_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';
include_once dirname(__FILE__) . '/../../lib/MesNombre.php';
include_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../lib/scg/reporte/validacionPoliza.php';
require_once dirname(__FILE__) . '/../../ent/scg/mesContable/sp_CierreContableUe.php';
require_once dirname(__FILE__) . '/../../ent/scg/mesContable/sp_CierreContableUr.php';
require_once dirname(__FILE__) . '/../../ent/scg/mesContable/sp_CerrarMesContable.php';
require_once dirname(__FILE__) . '/../../ent/scg/mesContable/sp_ReabrirMesContable.php';

class ControladorMesContable extends WebResponse
{
    public $gpo_id;
    public $ur_id;
    public $nivel;
    public $usr_id;
    public $usr_ip;
    public $lectura;
    public $escritura;

    public function __construct()
    {
        //EncabezadoFijo
        $this->mixScript("
        $('<link/>', {
            rel: 'stylesheet',
            type: 'text/css',
            href: 'css/tabla-encabezado.css'
        }).appendTo('head');
        ");

        parent::__construct();
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != "::1") ? $_SESSION[_S]['ses_ip'] : 'ERROR';
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['1']['1']) ? true : false;
        if ($this->nivel < 2) {
            $this->escritura = false;
        } else {
            $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['1']['2']) ? true : false;
        }

        switch ($this->metodo) {
            case 'CerrarMes':
                $this->CerrarMes();
                break;
            case 'Re_AbrirMes':
                $this->Re_AbrirMes();
                break;
            case 'DetalleRe_AbrirMes':
                $this->DetalleRe_AbrirMes();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $pinta = false;
        $cols = 2;
        if ($this->escritura) {
            $pinta = true;
            $cols = 3;
        }

        $tabla = new stdClass();
        $tabla->id = 'TabProv';
        $tr = array();

        // DIBUJAMOS LOS ENCABEZADOS

        // Si hay que pintar el estado de CADA UNA DE LAS URs
        if ($this->nivel > 2) {
            $th = new stdClass();
            $th->texto = 'UR';
            array_push($tr, $th);

            // El nombre de los meses de apertura a cierre
            for ($mes = 0; $mes <= 13; $mes++) {
                $th = new stdClass();
                $th->texto = MesNombre($mes);
                $th->align = 'center';
                $th->colspan = '' . $cols . '';
                array_push($tr, $th);
            }

            $tr2 = array();
            $th = new stdClass();
            $th->texto = '';
            array_push($tr2, $th);

            // Las columnas para cada mes, de apertura a cierre
            for ($mes = 0; $mes <= 13; $mes++) {
                $th = new stdClass();
                $th->texto = 'Estado ';
                array_push($tr2, $th);
                $th = new stdClass();
                $th->texto = 'Fecha ';
                array_push($tr2, $th);
                if ($pinta) {
                    $th = new stdClass();
                    $th->texto = 'Abrir/Cerrar';
                    array_push($tr2, $th);
                }
            }

            $tabla->encabezado = array($tr, $tr2);

            // Sí estamos hablando de una sola UR
        } else {
            $th = new stdClass();
            $th->texto = '<b>ABRIR / CERRAR</b>';
            $th->width = '5px';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'MES';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'FECHA DE APERTURA';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'FECHA DE CIERRE';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'ESTATUS';
            $th->width = '30px';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'USUARIO';
            array_push($tr, $th);

            $tabla->encabezado = array($tr);
        }

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMes';
        $tabla->numFilas = 300;

        $puede_cerrar_mes = ($this->nivel < 5 && $this->escritura);
        $puede_reabrir_mes = ($this->nivel > 4 && $this->escritura);

        // Si hay que dibujar el estado mes de VARIAS URs
        if ($this->nivel > 2) {
            $tabla->template = '
                <tr class="odd">
                    <td class="sorting_1"><label style="text-align:left; width:100%;">${gpo_id}${ur_id}</label></td>
            ';

            // Dibujamos, para cada mes, las columnas y botones a los que tiene permiso
            for ($mes = 0; $mes <= 13; $mes++) {
                $tabla->template .= '
                        <td class="sorting_1"><label style="text-align:left; width:100%;">
                        {{if ems_id' . $mes . '=="A"}}
                            ${ems_nombre' . $mes . '}
                        {{else}}
                            <font color="red">${ems_nombre' . $mes . '}</font>
                        {{/if}}
                        </label></td>

                        <td class="sorting_1"><label style="text-align:left; width:100%;">${fecha' . $mes . '}</label></td>
                ';

                if ($pinta) {

                    $tabla->template .= '
                        <td class="sorting_1">
                            {{if ems_id' . $mes . '=="A"}}
                                ' . (($puede_cerrar_mes) ?
                        '{{if msc_reabrir' . $mes . '==1}}
                                    <button type="button" name="abrir" class="btn btn-outline-warning CerrarMes"
                                        ems_id = "${ems_id' . $mes . '}" msc_periodo = "${msc_periodo' . $mes . '}"
                                        gpo_id = "${gpo_id}" ur_id = "${ur_id}"><span class="fa fa-lock"></span>
                                    </button>
                                {{/if}}' :
                        '') . '
                            {{else}}
                                {{if ems_id' . $mes . '=="C"}}
                                    ' . (($puede_reabrir_mes) ?
                        '{{if msc_reabrir' . $mes . '==1}}
                                        <button type="button" name="abrir" class="btn btn-outline-success Re_AbrirMes"
                                            ems_id = "${ems_id' . $mes . '}" msc_periodo = "${msc_periodo' . $mes . '}"
                                            gpo_id = "${gpo_id}" ur_id = "${ur_id}"><span class="fa fa-lock"></span>
                                        </button>
                                    {{/if}} ' :
                        '') . '
                                {{/if}}
                            {{/if}}
                        </td>
                    ';
                }

            }

            $tabla->template .= '
                </tr>
            ';

            // Si sólo tiene derecho a una UR
        } else {
            $tabla->full = true;

            $tabla->template = '
                <tr class="odd">
                    <td class="sorting_1">
                        {{if ems_id=="A"}}
                            ' . (($puede_cerrar_mes) ? '<button type="button" name="abrir"
                                class="btn btn-outline-warning CerrarMes"
                                ems_id = "${ems_id}" msc_periodo = "${msc_id}" gpo_id = "${gpo_id}" ur_id = "${ur_id}">
                                <span class="fa fa-lock"></span></button></button>'
                : '<i class="fa fa-unlock"></i>') . '
                        {{else}}
                            {{if ems_id=="C"}}
                            ' . (($puede_reabrir_mes) ? '<button type="button" name="abrir"
                                class="btn btn-outline-success Re_AbrirMes" ems_id = "${ems_id}" msc_periodo = "${msc_id}"
                                gpo_id = "${gpo_id}" ur_id = "${ur_id}"><span class="fa fa-lock"></span></button></button>'
                : '<i class="fa fa-lock"></i>') . '
                            {{else}}
                                <i class="fa fa-lock"></i>
                            {{/if}}
                        {{/if}}
                    </td><td class="sorting_1"><label style="text-align:left; width:100%;">${msc_nombre}</label></td>
                    <td class="sorting_1">
                        <label style="text-align:left; width:100%;">${abierto}</label>
                    </td>
                    <td class="sorting_1">
                        <label style="text-align:left; width:100%;">${cerrado}</label>
                    </td>
                    <td class="sorting_1">
                        <label style="text-align:left; width:100%;">${ems_nombre}</label>
                    </td>
                    <td class="sorting_1">
                        <label style="text-align:left; width:100%;">${usr_usuario}</label>
                    </td>
                </tr>
            ';
        }

        $btn_pdf = '<button id="pdf" type="button" form="frm_agrupacionCuentas" class="btn btn-outline-danger me-1"  title="Imprimir Mes Contable"><i class="fa-solid fa-file-pdf"></i>
        PDF
        </button>';

        $btn_excel = '<button id="excel" type="button" form="frm_agrupacionCuentas" class="btn btn-outline-success me-1"  title="Exportar Mes Contable"><i class="fa-solid fa-file-excel"></i>
            Excel
        </button>';

        //Se pintan los elementos en patalla
        $this->agregaTools($btn_pdf . $btn_excel);

        $this->mixHtml($this->wrPonTabla($tabla)
            . '<div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 col-xs-12" id="motivo" style="z-index:999999;">
                </fieldset>
            </div>
        ');

        $this->mixScript('
            $(".tabla-footer").hide();

            $("#pdf").click(function(){
                var form = $("<form>")
                .attr("action", "rep/doc/scg/reporte.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                $("body").append(form);
                form.submit();
                form.remove();
            });
            $("#excel").click(function(){
                var form = $("<form>")
                .attr("action", "rep/excel/scg/catalogo/exp_MesContable.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                .append($("<input>").attr("type", "hidden").attr("name", "estado").val( $("#estado").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "mes").val( $("#mes").val()));
                $("body").append(form);
                form.submit();
                form.remove();
            });

            var modalCargando = modalCargando || (function ($) {
                "use strict";
                // Creating modal dialog"s DOM
                var $dialog = $(
                    "<div id=\"modalAviso\" class=\"modal\" data-backdrop=\"static\" data-keyboard=\"false\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\" style=\"padding-top:15%; overflow-y:visible;\">" +
                    "<div class=\"modal-dialog modal-m\">" +
                    "<div class=\"modal-content\">" +
                        "<div class=\"modal-header\"><h3 style=\"margin:0;\"></h3></div>" +
                        "<div class=\"modal-body\">" +
                            "<div class=\"progress progress-striped active\" style=\"margin-bottom:0;\"><div class=\"progress-bar\" style=\"width: 100%\"></div></div>" +
                        "</div>" +
                    "</div></div></div>");

                return {
                    /**
                     * Opens our dialog
                     * @param message Custom message
                     * @param options Custom options:
                     *                options.dialogSize - bootstrap postfix for dialog size, e.g. \"sm\", \"m\";
                     *                options.progressType - bootstrap postfix for progress bar type, e.g. \"success\", \"warning\".
                     */
                    show: function (message, options) {
                        // Assigning defaults
                        if (typeof options === "undefined") {
                            options = {};
                        }
                        if (typeof message === "undefined") {
                            message = "Loading";
                        }
                        var settings = $.extend({
                            dialogSize: "m",
                            progressType: "",
                            onHide: null // This callback runs after the dialog was hidden
                        }, options);

                        // Configuring dialog
                        $dialog.find(".modal-dialog").attr("class", "modal-dialog").addClass("modal-" + settings.dialogSize);
                        $dialog.find(".progress-bar").attr("class", "progress-bar");
                        if (settings.progressType) {
                            $dialog.find(".progress-bar").addClass("progress-bar-" + settings.progressType);
                        }
                        $dialog.find("h3").text(message);
                        // Adding callbacks
                        if (typeof settings.onHide === "function") {
                            $dialog.off("hidden.bs.modal").on("hidden.bs.modal", function (e) {
                                settings.onHide.call($dialog);
                            });
                        }
                        // Opening dialog
                        $dialog.modal();
                    },
                    /**
                     * Closes dialog
                     */
                    hide: function () {
                        $dialog.modal("hide");
                    }
                };
            })(jQuery);
        ');

        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        $this->llenaTablaMes();
    }

    private function llenaTablaMes()
    {
        $wh = $where = $order = $group = $join = $select = '';
        if ($this->lectura == 1) {
            switch ($this->nivel) {
                case 1:case 2:
                    $group = ' GROUP BY msc_periodo';
                    $order = ' ORDER BY msc_periodo';
                    $where = ' AND  e.gpo_id = ' . $this->gpo_id . ' AND e.ur_id = ' . $this->ur_id . ' ';
                    $wh = ' WHERE  gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' ';
                    break;
                case 3:case 4:case 5:
                    $join = ' INNER JOIN cat_CargaTrabajo using(gpo_id,ur_id) ';
                    $group = ' GROUP BY gpo_id,ur_id  ';
                    $order = ' ORDER BY gpo_id,ur_id ';
                    $where = ' AND cat_CargaTrabajo.usr_id = ' . $this->usr_id;
                    $wh = ' WHERE cat_CargaTrabajo.usr_id = ' . $this->usr_id;
                    break;
                default:
                    $group = ' GROUP BY gpo_id,ur_id  ';
                    $order = ' ORDER BY gpo_id,ur_id ';
                    $wh = ' WHERE gpo_id!=0 AND ur_id!= 0 ';
                    break;
            }
        } else {
            $this->mixScript('notify("No tienes permisos para acceder a esta pantalla"); ');
            return false;
        }

        // Si tiene permisos para ver más de una UR...
        if ($this->nivel > 2) {
            $sql = '
                SELECT gpo_id,ur_id,
                    z.msc_id as msc_periodo0, z.msc_nombre,z.ems_id as ems_id0,z.ems_nombre as ems_nombre0,z.fecha as fecha0,z.msc_reabrir as msc_reabrir0,
                    a.msc_id as msc_periodo1 ,a.msc_nombre,a.ems_id as ems_id1,a.ems_nombre as ems_nombre1,a.fecha as fecha1,a.msc_reabrir as msc_reabrir1,
                    b.msc_id as msc_periodo2 ,b.msc_nombre,b.ems_id as ems_id2,b.ems_nombre as ems_nombre2,b.fecha as fecha2,b.msc_reabrir as msc_reabrir2,
                    c.msc_id as msc_periodo3 ,c.msc_nombre,c.ems_id as ems_id3,c.ems_nombre as ems_nombre3,c.fecha as fecha3,c.msc_reabrir as msc_reabrir3,
                    d.msc_id as msc_periodo4 ,d.msc_nombre,d.ems_id as ems_id4,d.ems_nombre as ems_nombre4,d.fecha as fecha4,d.msc_reabrir as msc_reabrir4,
                    e.msc_id as msc_periodo5 ,e.msc_nombre,e.ems_id as ems_id5,e.ems_nombre as ems_nombre5,e.fecha as fecha5,e.msc_reabrir as msc_reabrir5,
                    f.msc_id as msc_periodo6 ,f.msc_nombre,f.ems_id as ems_id6,f.ems_nombre as ems_nombre6,f.fecha as fecha6,f.msc_reabrir as msc_reabrir6,
                    g.msc_id as msc_periodo7 ,g.msc_nombre,g.ems_id as ems_id7,g.ems_nombre as ems_nombre7,g.fecha as fecha7,g.msc_reabrir as msc_reabrir7,
                    h.msc_id as msc_periodo8 ,h.msc_nombre,h.ems_id as ems_id8,h.ems_nombre as ems_nombre8,h.fecha as fecha8,h.msc_reabrir as msc_reabrir8,
                    i.msc_id as msc_periodo9 ,i.msc_nombre,i.ems_id as ems_id9,i.ems_nombre as ems_nombre9,i.fecha as fecha9,i.msc_reabrir as msc_reabrir9,
                    j.msc_id as msc_periodo10 ,j.msc_nombre,j.ems_id as ems_id10,j.ems_nombre as ems_nombre10,j.fecha as fecha10,j.msc_reabrir as msc_reabrir10,
                    k.msc_id as msc_periodo11 ,k.msc_nombre,k.ems_id as ems_id11,k.ems_nombre as ems_nombre11,k.fecha as fecha11,k.msc_reabrir as msc_reabrir11,
                    l.msc_id as msc_periodo12 ,l.msc_nombre,l.ems_id as ems_id12,l.ems_nombre as ems_nombre12,l.fecha as fecha12,l.msc_reabrir as msc_reabrir12,
                    m.msc_id as msc_periodo13 ,m.msc_nombre,m.ems_id as ems_id13,m.ems_nombre as ems_nombre13,m.fecha as fecha13,m.msc_reabrir as msc_reabrir13
                FROM(
                    SELECT gpo_id,ur_id
                    FROM cat_UnidadResponsable
                    ' . $join . '
                    ' . $wh . '
                )x
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(0, $join, $where, $group, $order) . '
                )z USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(1, $join, $where, $group, $order) . '
                )a USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(2, $join, $where, $group, $order) . '
                )b USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(3, $join, $where, $group, $order) . '
                )c USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(4, $join, $where, $group, $order) . '
                )d USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(5, $join, $where, $group, $order) . '
                )e USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(6, $join, $where, $group, $order) . '
                )f USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(7, $join, $where, $group, $order) . '
                )g USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(8, $join, $where, $group, $order) . '
                )h USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(9, $join, $where, $group, $order) . '
                )i USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(10, $join, $where, $group, $order) . '
                )j USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(11, $join, $where, $group, $order) . '
                )k USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(12, $join, $where, $group, $order) . '
                )l USING(gpo_id,ur_id)
                LEFT JOIN(
                    ' . $this->dame_sql_por_mes(13, $join, $where, $group, $order) . '
                )m USING(gpo_id,ur_id)
                ORDER BY gpo_id,ur_id
            ';

            // Si sólo puede ver 1 UR
        } else {

            $sql = "
                SELECT e.gpo_id, e.ur_id, msc_periodo,msc_id, msc_nombre,
                    IF( ISNULL(ems_id), 0, ems_id) ems_id,(msc_fechaAbierto) AS abierto  ,(msc_fechaCerrado) AS cerrado,
                    IF(ISNULL(ems_nombre), '', ems_nombre) AS ems_nombre ,IF(ISNULL(usr_usuario), '', usr_usuario) AS usr_usuario
                FROM cat_MesContable
                    LEFT JOIN cg_MesContable e USING(msc_id)
                    LEFT JOIN cat_Usuario u ON e.usr_id=u.usr_id
                    LEFT JOIN cat_EstadoMesContable USING(ems_id)
                WHERE e.gpo_id = $this->gpo_id AND e.ur_id = $this->ur_id
                    OR ISNULL(e.msc_id)
                ORDER BY msc_id
            ";

        }

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'TabProv';
        $nav->nfDefault = -1;
        $this->wrLlenaTabla($nav);

        // Si tiene permisos para reabrir meses
        if ($this->nivel > 4) {
            $this->mixScript('
                $(".Re_AbrirMes").click(function(){
                    var ems = $(this).attr("ems_id");
                    var msc = $(this).attr("msc_periodo");
                    var gpo_id = $(this).attr("gpo_id");
                    var ur_id = $(this).attr("ur_id");
                    comunica("' . $this->pantalla . '",{
                        metodo : "DetalleRe_AbrirMes",
                        ems_id: ems,
                        msc_periodo: msc,
                        gpo_id: gpo_id,
                        ur_id: ur_id
                        },
                        true);
                    return false;
                });
            ');

            // Si sólo tiene permisos para cerrarlos
        } elseif ($this->nivel < 5) {
            $this->mixScript('
                $(".CerrarMes").click(function(){
                    let ems = $(this).attr("ems_id");
                    let msc = $(this).attr("msc_periodo");
                    let gpo_id = $(this).attr("gpo_id");
                    let ur_id = $(this).attr("ur_id");
                    alertify.confirm("CERRAR MES", "¿Estas seguro de cerrar el mes?",
                    function(){
                        modalCargando.show("Cerrando mes, por favor espere un momento...");
                        if( ems != "C" ){
                            comunica("' . $this->pantalla . '",{
                                metodo: "CerrarMes",
                                ems_id: ems,
                                msc_periodo: msc,
                                gpo_id: gpo_id,
                                ur_id: ur_id
                            });
                        }
                    }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"CERRAR MES", cancel:"Cancelar Operación"});
                });
            ');
        }

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
        ');
    }

    private function CerrarMes()
    {
        if ($this->prm->msc_periodo) {
            if ($this->escritura && $this->nivel < 5) {
                if ($this->validaMes()) {
                    $cerrarMesContable = new sp_CerrarMesContable();
                    $cerrarMesContable->setGpo_id($this->prm->gpo_id);
                    $cerrarMesContable->setUr_id($this->prm->ur_id);
                    $cerrarMesContable->setMsc_id($this->prm->msc_periodo);
                    $cerrarMesContable->setBit_observacion("SISTEMA: CERRAR MES CONTABLE");
                    $cerrarMesContable->setEjercicio($_SESSION[_S]['ejercicioActual']);
                    $cerrarMesContable->setSis_id("SCG");
                    $cerrarMesContable->execute(false, false);
                    $bandera = $cerrarMesContable->getBandera();
                    $mensajesp = $cerrarMesContable->getMensaje();

                    if ($bandera != 1) {
                        $mensaje = $mensajesp;
                        $tipo = "error";
                    } else {
                        $mensaje = $mensajesp;
                        $tipo = "success";
                    }
                    $this->mixScript('notify("' . $mensajesp . '","' . $tipo . '");');
                    $this->cierreContable();
                } else {
                    $this->mixScript('
                        console.log("Entramos aquí y debe imprimir un pdf");
                        alertify.confirm("IMPRIMIR REPORTE", "MES CONTABLE CON INCONSISTENCIA\n\n ¿Desea Imprimir el Reporte?",
                        function(){
                            var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "10" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "10" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", ' . $this->prm->msc_periodo . '))
                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesHasta").attr("value", ' . $this->prm->msc_periodo . '))
                            $("body").append(form);
                            form.submit();
                            form.remove();
                        },function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Regresar", cancel:"Cancelar"});
                        );
                    ');
                }

                $this->llenaTablaMes();

            } else {
                $this->mixSCript('notify("SISTEMA: EL USUARIO NO CUENTA CON LOS PERMISOS NECESARIOS");');
            }

        } else {
            $this->mixSCript('notify("SISTEMA: EL PERIODO NO ES CORRECTO");');
        }
        $this->mixScript(' modalCargando.hide();');

    }

    private function Re_AbrirMes()
    {
        if ($this->prm->msc_periodo) {
            if ($this->nivel > 3 && $this->escritura) {
                $this->conectar();
                $this->setSql('
                    SET
                    @usr_ip = INET_ATON( "' . $this->usr_ip . '" ),
                    @usr_id = ' . $this->usr_id . ',
                    @bit_observacion = "SISTEMA: REABRIR MES CONTABLE"'
                );
                $this->consultar();
                $reabrirMesContable = new sp_ReabrirMesContable();
                $reabrirMesContable->setGpo_id($this->prm->gpo_id);
                $reabrirMesContable->setUr_id($this->prm->ur_id);
                $reabrirMesContable->setMsc_id($this->prm->msc_periodo);
                $reabrirMesContable->setMsc_fechaPropuestaCierre($this->prm->propuesta);
                $reabrirMesContable->setMsc_observacion($this->prm->observacion);
                $reabrirMesContable->setBit_observacion("SISTEMA: REABRIR MES CONTABLE");
                $reabrirMesContable->setEjercicio($_SESSION[_S]['ejercicioActual']);
                $reabrirMesContable->setSis_id("SCG");
                $reabrirMesContable->execute(false, false);
                $bandera = $reabrirMesContable->getBandera();
                $mensajesp = $reabrirMesContable->getMensaje();
                $this->desconectar();
                if ($bandera != 1) {
                    $mensaje = $mensajesp;
                    $tipo = "error";
                } else {
                    $mensaje = $mensajesp;
                    $tipo = "success";

                    $gpo_id = $this->prm->gpo_id;
                    $ur_id = $this->prm->ur_id;
                    $msc_id = $this->prm->msc_periodo;

                    // Borramos las Notas a los Estados Financieros, si las hay para este mes
                    $this->conectar();
                    $this->setSql("
                        DELETE FROM cg_NotasEstadosFinancieros
                        WHERE gpo_id = ?
                            and ur_id = ?
                            and msc_id = ?
                        ;");
                    $this->consultar([$gpo_id, $ur_id, $msc_id]);

                    // Creamos el evento para el re-cierre automático
                    $this->conectar();

                    $this->setSql('
                        SET
                        @usr_ip = INET_ATON(?),
                        @usr_id = ?,
                        @bit_observacion = "SISTEMA: CIERRE AUTOMÁTICO DESPUÉS DE RE-APERTURA"
                    ');
                    $this->consultar([$this->usr_ip, $this->usr_id]);

                    $fecha = new stdClass();
                    $fecha->fecha = $this->prm->propuesta;

                    $fecha_validada = validaFecha($fecha);

                    if (!$fecha_validada->valido) {
                        $this->mixScript('notify("' . $fecha_validada->mensaje . '","error");');
                    } else {
                        $fecha_cierre_automatico = $fecha_validada->fechaMysql;
                        $this->setSql("
                            CREATE EVENT
                                volver_a_cerrar_mes_contable_reabierto_" . $msc_id . "_" . $gpo_id . "_" . $ur_id . "
                                ON SCHEDULE AT '" . $fecha_cierre_automatico . "'
                                COMMENT 'Cierre calendarizado de mes reabierto'
                                DO
                                    BEGIN
                                        SET @usr_ip = sinpres.fn_get_ip(), @usr_id = 4, @bit_observacion = 'cierre automático después de reabrir mes';
                                        call sp_CerrarMesContable" . APP_EJERCICIO . "( $gpo_id, $ur_id, $msc_id, @usr_ip, @usr_id );
                                END
                        ");
                        $this->consultar();
                    }

                    $this->desconectar();

                }
                $this->mixScript('notify("' . $mensajesp . '","' . $tipo . '");');
                $this->llenaTablaMes();
            } else {
                $this->mixSCript('notify("SISTEMA: EL USUARIO NO CUENTA CON LOS PERMISOS NECESARIOS");');
            }

        } else {
            $this->mixSCript('notify("SISTEMA: EL PERIODO NO ES CORRECTO");');
        }

    }

    private function validaMes()
    {
        if ($this->prm->msc_periodo != '') {
            $desde = $this->prm->msc_periodo;
            $hasta = $this->prm->msc_periodo;

            $periodo = array('mesDesde' => $desde, 'mesHasta' => $hasta);
            $extra = array('origen' => 'controlador');

            $datos = validacionPoliza($this->prm->gpo_id, $this->prm->ur_id, '',
                '', $periodo, '', '', '', '', '', '', $extra);

            return count($datos) == 0;
        } else {
            $this->mixScript('notify("Error en el periodo");');
            return false;
        }
    }

    private function DetalleRe_AbrirMes()
    {
        $this->prm->ems_id = (isset($this->prm->ems_id)) ? $this->prm->ems_id : '';
        $this->prm->msc_periodo = (isset($this->prm->msc_periodo)) ? $this->prm->msc_periodo : '';

        $this->setContenedor('motivo');

        $fecha = new stdClass();
        $fecha->id = "fecha";
        $fecha->name = "control";
        $fecha->class = 'fecha';
        $fecha->label = 'Fecha de cierre <strong>AUTOMÁTICO</strong>';
        $fecha->enDiv = true;
        $fecha->formato = 'fecha';
        $fecha->labelClass = '_32 txtL';
        $fecha->required = true;

        /* Motivo de apertura  */
        $concepto = new stdClass();
        $concepto->id = 'concepto';
        $concepto->name = 'concepto';
        $concepto->label = 'Motivo de Reapertura';
        $concepto->placeholder = "Motivo de Reapertura";
        $concepto->enDiv = true;
        $concepto->required = true;
        $concepto->width = '400px';
        $concepto->class = 'anulaenter habilitar';
        $concepto->height = '150px';
        $concepto->maxlength = '600';

        $autorizo = new StdClass();
        $autorizo->id = 'autorizo';
        $autorizo->label = 'Autorizó';
        $autorizo->enDiv = true;

        $modal = "";
        $modal = '
            <div id="modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="width: 50% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ModalLabel">Reabrir Mes</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                            <div class="row">
                                <div  class="col-md-12 col-xs-12 mb-2">
                                    ' . $this->ponTextarea($concepto) . '

                                </div>
                                <div  class="col-md-12 col-xs-12 mb-2">
                                    ' . $this->ponInput($autorizo) . '
                                </div>
                                <div  class="col-md-6 col-xs-6 mb-2">
                                    ' . $this->ponInput($fecha) . '
                                </div>
                                <div  class="col-md-6 col-xs-6 mb-2">
                                    <label for="fecha_hora">Hora de cierre <strong>AUTOMÁTICO</strong></label>
                                    <input type="time" class="form-control" id="fecha_hora" name="fecha_hora" required />
                                </div>
                            </div>
                            <br>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cerrar" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-outline-success proceder" >Proceder</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';

        $this->mixHtml($modal);
        $this->mixScript('
                $("#modal").modal();

                $(".cerrar").click(function(){
                    $("#modal").remove();
                    $(".modal-backdrop").remove();
                });


                $(".proceder").click(function(){

                    if($("#fecha").val()==""){
                        notify("Captura la fecha de Cierre Automático del mes","error",false);
                        return false;
                    }
                    if($("#fecha_hora").val()==""){
                        notify("Captura la hora de Ciere Automático del mes","error",false);
                        return false;
                    }

                    // Juntamos la fecha y la hora
                    let my_datetime = $("#fecha").val() + " " + $("#fecha_hora").val();

                    // Para que se muestre el alertify...
                    $("#modal").hide();

                    alertify.confirm("REABRIR MES", "¿Confirmas reabrir el mes de ' . $this->prm->msc_periodo . ' ?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"Re_AbrirMes",
                                ems_id:"' . $this->prm->ems_id . '",
                                msc_periodo:"' . $this->prm->msc_periodo . '",
                                gpo_id:"' . $this->prm->gpo_id . '",
                                ur_id:"' . $this->prm->ur_id . '",
                                observacion:$("#concepto").val(),
                                propuesta: my_datetime
                                });
                            $("#modal").remove();
                            $(".modal-backdrop").remove();
                    }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Rabrir Mes", cancel:"Cancelar Operación"});
                });
            ');
    }

    private function cierreContable()
    {
        $this->conectar();
        //verificar si todos los meses estan cerrados
        $this->setSql("
            SELECT msc_id
            FROM cg_MesContable
            WHERE gpo_id =:gpo  AND ur_id = :ur
                AND ems_id = :ems;
        ");
        $parametros = [
            ':gpo' => $this->gpo_id,
            ':ur' => $this->ur_id,
            ':ems' => 'A'];
        $this->conectar();
        $this->consultar($parametros);
        $this->desconectar();
        if ($this->getNumeroRegistros() < 0) {
            if ($this->gpo_id . $this->ur_id == 116) {
                $nivel = 'Ue';
                $ueSql = "
                    SELECT ue_id
                    FROM cat_UnidadEjecutora
                    WHERE gpo_id = :gpo AND ur_id = :ur
                    ORDER BY ue_id DESC;
                ";
                $parametro = [
                    ':gpo' => $this->gpo_id,
                    ':ur' => $this->ur_id];
            } else {
                $nivel = 'Ur';
                $ueSql = "
                    SELECT ur_ueContable AS ue_id
                    FROM cat_UnidadResponsable
                    WHERE gpo_id = :gpo AND ur_id = :ur
                ";
                $parametro = [
                    ':gpo' => $this->gpo_id,
                    ':ur' => $this->ur_id];
            }

            $ueContable = new AccesoDatos(); //variable de konexion para los saldos iniciales
            $ueContable->conectar();
            $ueContable->setSql($ueSql);
            $ueContable->consultar($parametro);
            $ueContable->desconectar();
            $error = false;
            $mensaje = '';
            while ($row = $ueContable->extraer()) {
                if ($nivel == 'Ue') {
                    $cierreContableUe = new sp_CierreContableUe();
                    $cierreContableUe->setGpo_id($this->gpo_id);
                    $cierreContableUe->setUr_id($this->ur_id);
                    $cierreContableUe->setUe_id($row['ue_id']);

                    $cierreContableUe->setSis_id('SCG');
                    $cierreContableUe->setBit_observacion('CIERRE CONTABLE UE DESDE LA PANTALLA C_2_1');
                    $cierreContableUe->setEjercicio($_SESSION[_S]['ejercicioActual']);

                    $cierreContableUe->execute();
                    if (!$cierreContableUe->getBandera()) {
                        $error = true;
                        $mensaje = $cierreContableUe->getMensaje();
                    }

                    if ($error) {
                        break;
                    }
                } else {
                    $error = false;
                    $cierreContableUr = new sp_CierreContableUr();
                    $cierreContableUr->setGpo_id($this->gpo_id);
                    $cierreContableUr->setUr_id($this->ur_id);
                    $cierreContableUr->setUe_id($row['ue_id']);

                    $cierreContableUr->setSis_id('SCG');
                    $cierreContableUr->setBit_observacion('CIERRE CONTABLE UR DESDE LA PANTALLA C_2_1');
                    $cierreContableUr->setEjercicio(APP_EJERCICIO);

                    $cierreContableUr->execute();
                    if (!$cierreContableUr->getBandera()) {
                        $error = true;
                        $mensaje = $cierreContableUr->getMensaje();
                    }
                    if ($error) {
                        break;
                    }
                }
            }

            $this->desconectar();
            if ($error) {
                $this->mixScript('notify(" ' . $mensaje . '", "error");');
            } else {
                $this->mixScript('notify("Se ha generado el cierre contable");');
            }
        }
        //
    }

    private function dame_sql_por_mes($mes, &$join, &$where, &$group, &$order)
    {
        $sql = '
            SELECT e.gpo_id, e.ur_id, msc_id, msc_periodo, msc_nombre,
                IF( ISNULL(ems_id), 0, ems_id) AS ems_id, msc_reabrir,
                IF(ISNULL(ems_nombre), "", ems_nombre) AS ems_nombre,
                IF(ems_id="A",min(msc_fechaAbierto),max(msc_fechaCerrado)) as fecha
            FROM  cat_MesContable
            INNER JOIN cg_MesContable e USING(msc_id)
            INNER JOIN cat_EstadoMesContable USING (ems_id)
                ' . $join . '
            WHERE  msc_id = ' . $mes . '
                ' . $where . '
                ' . $group . '
                ' . $order . '
            ';

        return $sql;

    }
}

if (isset($_SESSION[_S]['prm']['C']['2']['1'])) {
    new ControladorMesContable();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
