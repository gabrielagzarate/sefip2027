<?php
set_time_limit(0);

//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionMinistrado.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereCoEgresoAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereCoEgresoCancelado.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereEgresoAutorizado.php';

class ControladorMinistrado extends WebResponse
{
    protected $ejercicioActual;
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $usr_ip;
    protected $usr_id;
    protected $origen;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['3']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : 'ERROR';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion') {
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");
        }

        switch ($this->metodo) {
            case 'DibujaPantalla':
                $this->DibujaPantalla();
                break;
            case 'MuestraDatos':
                $this->MuestraDatos();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'GeneraPolizasAutorizacionPorUR':
                $this->GeneraPolizasAutorizacionPorUR();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            case 'MuestraDatosPagina':
                $this->MuestraDatosPagina();
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                if ($this->origen == 'clc') {
                    $this->PonBarraBotones();
                } else {
                    $this->PonBarraBotones();
                    $this->prm->seleccion = "clc";
                }
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        // Botones y opciones que se dibujarán en la barra de botones
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-2";
        $btnRegresa->icon = "circle-left me-2";

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->color = "amarillo";
        $btnLimpiar->class = "btn btn-outline-info me-2";
        $btnLimpiar->icon = "solid fa-trash-can me-2";
        $btnLimpiar->click = '
            $("#inputGur").val("");
            $("#inputClc_id").val("");
            $("#inputBdo_id").val("");
            $("#inputRfc_id").val("");
            $("#inputBdo_referencia").val("");

            comunica("' . $this->pantalla . '",{
                metodo: "MuestraDatos",
                origen: "' . $this->origen . '",
                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                mes: $("#page-tabs li").children(".active").attr("mes"),
                gur: $("#inputGur").val(),
                clc_id: $("#inputClc_id").val(),
                bdo_id: $("#inputBdo_id").val(),
                rfc_id: $("#inputRfc_id").val(),
                bdo_referencia: $("#inputBdo_referencia").val(),
                soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked")
            });

            e.stopPropagation();
            return false;
        ';

        $clcFiltro = false;
        $bdoFiltro = false;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = true;
                break;
            case "bdo":
                $bdoFiltro = true;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = true;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = true;
                }
                break;
        }

        $vieneDesdeVinculacionClc = "";
        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $vieneDesdeVinculacionClc = "disabled";
        }

        $inputGur = new StdClass();
        $inputGur->id = 'inputGur';
        $inputGur->name = 'inputGur';
        $inputGur->placeholder = 'UR';
        $inputGur->enDiv = true;
        $inputGur->class = $vieneDesdeVinculacionClc;
        $inputGur->value = (isset($this->prm->gur) ? $this->prm->gur : "");
        $inputGur->maxlength = '3';

        $inputClc = new StdClass();
        $inputClc->id = 'inputClc_id';
        $inputClc->name = 'inputClc_id';
        $inputClc->placeholder = 'CLC';
        $inputClc->enDiv = true;
        $inputClc->class = $vieneDesdeVinculacionClc;
        $inputClc->value = ($clcFiltro ? $this->prm->clc_id : "");
        $inputClc->maxlength = '6';

        $inputBdo = new StdClass();
        $inputBdo->id = 'inputBdo_id';
        $inputBdo->name = 'inputBdo_id';
        $inputBdo->placeholder = '# Documento';
        $inputBdo->enDiv = true;
        $inputBdo->value = ($bdoFiltro ? $this->prm->bdo_id : "");
        $inputBdo->maxlength = '7';

        $inputRfc = new StdClass();
        $inputRfc->id = 'inputRfc_id';
        $inputRfc->name = 'inputRfc_id';
        $inputRfc->placeholder = 'R.F.C.';
        $inputRfc->enDiv = true;
        $inputRfc->value = ($bdoFiltro ? $this->prm->rfc_id : "");
        $inputRfc->maxlength = '20';

        $inputReferencia = new StdClass();
        $inputReferencia->id = 'inputBdo_referencia';
        $inputReferencia->name = 'inputBdo_referencia';
        $inputReferencia->placeholder = 'Referencia';
        $inputReferencia->enDiv = true;
        $inputReferencia->value = ($bdoFiltro ? $this->prm->bdo_referencia : "");
        $inputReferencia->maxlength = '100';

        $btnExcel = new stdClass();
        $btnExcel->id = 'excelMinistrado';
        $btnExcel->name = 'excelMinistrado';
        $btnExcel->text = 'Excel';
        $btnExcel->value = '  Excel';
        $btnExcel->class = "btn btn-successs me-1";
        $btnExcel->color = "verde";
        $btnExcel->icon = "fa-solid fa-file-excel";
        $btnExcel->click = '
            var form = $("<form>")
                .attr("action", "rep/excel/scg/vinculacion/exp_Ministrado.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12"))
                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3"))
                .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B"))
                .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '"))
                .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes")))
                .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "soloSinPolizaAutorizacion").attr("value", $("#chkSoloSinPolizaAutorizacion").is(":checked") ? 1 : 0))
                .append($("<input>").attr("type", "hidden").attr("name", "soloSinPolizaCancelacion").attr("value", $("#chkSoloSinPolizaCancelacion").is(":checked") ? 1 : 0))
                .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val()))

                $("body").append(form);
                var mr = $("#page-tabs li").children(".active").attr("mes");
                if(mr == undefined){
                    notify("Debe seleccionar un mes","error");
                }else{
                    form.submit();
                }
        ';

        $btnGenerarpolizasAut = new stdClass();
        $btnGenerarpolizasAut->id = 'btnGeneraPolizasAPorUR';
        $btnGenerarpolizasAut->name = 'btnGeneraPolizasAPorUR';
        $btnGenerarpolizasAut->text = 'Genera Pólizas de Autorización';
        $btnGenerarpolizasAut->value = '  Genera Pólizas de Autorización';
        $btnGenerarpolizasAut->type = 'button';
        $btnGenerarpolizasAut->color = 'verde';
        $btnGenerarpolizasAut->class = "btn btn-outline-success me-2";
        $btnGenerarpolizasAut->icon = "fa fa-check me-2";
        $btnGenerarpolizasAut->click = '
            comunica(
                    "' . $this->pantalla . '",
                    {
                        metodo: "GeneraPolizasAutorizacionPorUR",
                        origen: "' . $this->origen . '",
                        gur: $("#inputGur").val(),
                        mes:  $("#page-tabs li").children(".active").attr("mes")
                    }
                );
        ';

        $botones = '
            <div class="row mb-4 class="col-md-12 col-xs-12">
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc") ?
               '<fieldset class="col-xs-2" style="padding-right: 1px !important;">' .
                    $this->ponInput($btnRegresa) .
                '</fieldset>' : '') . '
                '.($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 ?
                '<fieldset class="col-md-3 col-xs-3 " id="">' .
                    $this->ponInput($inputGur) .
                '</fieldset>' : '') . '
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputClc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputBdo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputRfc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6" id="">
                    ' . $this->ponInput($inputReferencia) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                        <input id="chkSoloSinPolizaAutorizacion" type="checkbox" class="form-control form-check-input input-checkbox">
                        <strong>Sólo sin Póliza de Autorización</strong>
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                        <input id="chkSoloSinPolizaCancelacion" type="checkbox" class="form-control form-check-input input-checkbox">
                        <strong>Sólo sin Póliza de Cancelación</strong>
                </fieldset>
                <fieldset class="col-xs-6 col-md-6" style="text-align: right;">
                    ' . $this->ponBoton($btnExcel) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">                 
                    ' . ($this->escritura ? $this->ponBoton($btnGenerarpolizasAut) : '') .'
                </fieldset>
            </div>';

        $this->agregaTools($botones);

        $this->mixScript(
            '
            $("#chkSoloSinPolizaAutorizacion").on("click", function() {
                if( $(this).is(":checked") && $("#chkSoloSinPolizaCancelacion").is(":checked")) {
                    $("#chkSoloSinPolizaCancelacion").prop("checked", false);
                }
            });
            $("#chkSoloSinPolizaCancelacion").on("click", function() {
                if( $(this).is(":checked") && $("#chkSoloSinPolizaAutorizacion").is(":checked")) {
                    $("#chkSoloSinPolizaAutorizacion").prop("checked", false);
                }
            });
        ');

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                INNER JOIN cg_MesContable USING(msc_id)
            WHERE gpo_id = ? AND ur_id = ?
                AND msc_id NOT IN (0, 13)
        ');
        $p1 = [$this->gpo_id,$this->ur_id];
        $this->consultar($p1);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) $inicio = 'active';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li class="nav-item" opcion="' . $row['msc_id'] . '" ultimo>
                        <a class="nav-link ' . $inicio . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
            
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('<div id="page-tabs"><ul class="nav nav-tabs">' . $pestana . '</ul></div>');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript(
                    '
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            $("nav").menusinpres("clickMenu");
                            e.stopPropagation();
                        });
                        ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '')
                );

                $this->mixScript('
                        comunica("' . $this->pantalla . '",{
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val(),
                            soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                            soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked")
                        });
                    ');
                break;
            case 'clc':
                $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            comunica("scg/fondos/ControladorVinculacionClcFondos.php", {
                                metodo: "PonBarraBotones",
                                bandera : "btnR"
                            });
                            e.stopPropagation();
                        });
                        ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                    ');
                $this->mixScript('
                        comunica("' . $this->pantalla . '",{
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val(),
                            soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                            soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked")
                        });
                    ');
                break;
        }

        $this->mixScript('
            $("#otraInfoFooter").attr("original", $("#otraInfoFooter").text());
	        $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '");
                $("#opciones").click(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        mes:  $("#page-tabs li").children(".active").attr("mes"),
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#imprimirMinistrado").click(function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12"))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3"))
                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B"))
                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '"))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val()))

                    $("body").append(form);
                    form.submit();
                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });

            ');

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $html = '<div id="myGridMinistrado" class="tabla-contenedor">
                <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                    <table id="tabla-fondos-ministrado" class="table table-striped table-bordered">
                        <div class="col-md-12 row" style="text-align:center">
                            <thead class="sticky-top" style="z-index:100;">
                                <tr class="warning" role="row">
                                    <th class="warning" role="columnheader" style= "text-align:center">UR</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AFE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">DOC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">TDF</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">RFC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">REFERENCIA</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CONCEPTO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">TDF MINISTRADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">REFERENCIA MINISTRADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">IMPORTE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">ESTADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AUTORIZACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CANCELACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">POLIZA AUT.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">FECHA AUT.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">POLIZA CANC.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">FECHA CANC.</th>
                                </tr>
                            </thead>
                        </div>
                        <tbody></tbody>
                    </table> 
                    </div>
                </div>
                    <div class="tabla-footer">
                        <div class="p-2 border flex-wrap"> 
                            <div class="d-flex flex-row">
                                 <div class="flex-grow-1 p-1">
                                    <label id="rA-tbl-vinculacion"></label>
                                    <label id="rB-tbl-vinculacion"></label> 
                                    <label id="rTotal-tbl-vinculacion"></label>
                                </div>
                                <div id="MinistradoPaginador" class="flex-grow-2 justify-content-center p-1" ></div>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        $this->mixHtml($html);

        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaMinistrado > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
            $("#page-tabs a").click(function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: seleccionado.attr("mes"),
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val(),
                    soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                    soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked")
                });

                e.stopPropagation();
                return false;
            });
        ');

        $this->mixScript('
            var sortcol = "CLAVE";
            var sortdir = 1;
            var searchString = "";

            $("#buscar").keyup(function (e) {
                // ESC limpia el campo
                if (e.which == 27) { this.value = ""; }
                searchString = this.value.toLowerCase();
                updateFilter();
            });

            function updateFilter(){
                dataViewMinistrado.setFilterArgs({ searchString: searchString });
                dataViewMinistrado.refresh();
            }

            // Cargamos la información a mostrar
            function loadData(json){
                $("#tabla-fondos-ministrado tbody").html("");
                const tbody = $("#tabla-fondos-ministrado tbody");

                dataMinistrado = [];

                i = 0;
                for(var key in json){
                    //
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    var d = (dataMinistrado[i] = {});
                    id = json[key].id;
                    gur = json[key].gpo_id + json[key].ur_id;
                    afe_id = json[key].afe_id;
                    bdo_id = json[key].bdo_id;
                    tdf_id = json[key].tdf_id;
                    rfc_id = json[key].rfc_id;
                    bdo_referencia = json[key].bdo_referencia;
                    bdo_concepto = json[key].bdo_concepto;
                    tdf_id_ministrado = json[key].tdf_id_ministrado;
                    bdm_referencia = json[key].bdm_referencia;
                    importe = (json[key].bdo_importe == 0) ? "" : formatoMoneda.format(json[key].bdo_importe);
                    estado = json[key].estado;
                    fechaAutorizacion = (json[key].fechaAutorizacion == null) ? "" : json[key].fechaAutorizacion;
                    fechaCancelacion = (json[key].fechaCancelacion == null) ? "" : json[key].fechaCancelacion;
                    polizaAutorizado = (json[key].polizaAutorizado == null) ? "" : json[key].polizaAutorizado;
                    fechaAutorizado = (json[key].fechaAutorizado == null) ? "" : json[key].fechaAutorizado;
                    polizaCancelado = (json[key].polizaCancelado == null) ? "" : json[key].polizaCancelado;
                    fechaCancelado = (json[key].fechaCancelado == null) ? "" : json[key].fechaCancelado;
                    agrupador = json[key].agrupador;
                   
                /**********************************************/
                tbody.append(
                    "<tr class=\'seleccionable\'><td style = \' text-align: center; white-space: nowrap\'>" 
                        + gur + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                        + afe_id + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                        + bdo_id + "</td><td>" 
                        + tdf_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>"
                        + rfc_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                        + bdo_referencia + "</td><td style = \' text-align: leftt; white-space: pre-wrap\'>" 
                        + bdo_concepto + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                        + tdf_id_ministrado + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                        + bdm_referencia + "</td><td style = \' text-align: right; white-space: pre-wrap\'>" 
                        + importe + "</td><td style = \' text-align: center; white-space: pre-wrap\'>"
                        + estado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                        + fechaAutorizacion + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                        + fechaCancelacion + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                        + polizaAutorizado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                        + fechaAutorizado + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                        + polizaCancelado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                        + fechaCancelado + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>" 
                        + agrupador + "</td></tr>");
                    i++;
                }
                
                /********************************/
                $(document).ready(function (){
                    $(".seleccionable").click(function() {                                             
                        $(".seleccionable").css("font-weight","normal");
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                        $(this).css("font-weight","bold");
                    });

                     $("#tabla-fondos-ministrado tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaMinistrado");
                        },
                        function() {                      
                            $(this).removeClass("coloreaMinistrado"); 
                        }
                    ); 
                });
            }

            $("#tabla-fondos-ministrado").on("dblclick", "tbody>tr", function() {
                // Obtener el índice de la fila (comenzando desde 0)
                var rowIndex = $(this).index();

                 // Obtener el contenido de la fila
                var rowData = [];
                $(this).find("td").each(function() {
                    rowData.push($(this).text());
                });

                var gur = rowData[0];
                var afe_id = rowData[1];
                var bdo_id = rowData[2];
                var tdf_id = rowData[3];
                var rfc_id = rowData[4];
                var bdo_referencia = rowData[5];
                var agrupador = rowData[17];

                // Si no hay cuenta que seleccionar, no hacemos nada
                if ( gur == "" ) {
                    e.stopPropagation();
                    return false;
                }

                var var_clcFiltro = 1;
                var var_bdoFiltro = 1;
                
                if ($("#inputClc_id").val() == "") {
                    var_clcFiltro = 2;
                }
                if ($("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "") {
                    var_bdoFiltro = 2;
                }

                comunica("' . $this->pantalla . '", {
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    clc_id: $("#inputClc_id").val(),
                    clcFiltro: var_clcFiltro,
                    gur: gur,
                    afe_id: afe_id,
                    bdo_id: bdo_id,
                    tdf_id: tdf_id,
                    rfc_id: rfc_id,
                    bdo_referencia: bdo_referencia,
                    bdoFiltro: var_bdoFiltro,
                    agrupador: agrupador
                });
                e.stopPropagation();
            });
        ');

        if ($this->origen == 'clc') {
            $this->mixScript('
                $("#page-tabs").hide();
                $("#inputBdo_id").hide();
                $("#inputRfc_id").hide();
                $("#inputBdo_referencia").hide();
            ');
        }
    }

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
            loadData(' . $data . ');
        ');

        return false;
    }

    private function MuestraDatosPagina()
    {
        $data = $this->DameInformacionPaginado();

        $this->mixScript('
            loadData(' . $data . ');
        ');

        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(' ', '', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $soloSinPolizaAutorizacion = (isset($this->prm->soloSinPolizaAutorizacion) ? $this->prm->soloSinPolizaAutorizacion : false);
        $soloSinPolizaCancelacion = (isset($this->prm->soloSinPolizaCancelacion) ? $this->prm->soloSinPolizaCancelacion : false);
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = '';
        $ClaveFinanciamiento['ter_id'] = '';

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;
        $paginado = true;
        $registroInicial = 0;
        $PrimeraCon = true;
        $datos = vinculacionMinistrado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'B',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "soloSinPolizaAutorizacion" => $soloSinPolizaAutorizacion,
                "soloSinPolizaCancelacion" => $soloSinPolizaCancelacion
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );

        $nTotalPages = 0;
        if (isset($paginado)) {
            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado) {
            $this->mixScript('
                $("#MinistradoPaginador").attr("last", 1);
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    itemContainerClass: function (type, page, current) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e, originalEvent, type, page) {
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ($("#MinistradoPaginador").attr("last") != page) {
                            $("#MinistradoPaginador").attr("last", page);
                            setTimeout(function() { $("#buscar").focus() }, 100);
                            

                            comunica(
                                "' . $this->pantalla . '", 
                                {
                                    metodo: "MuestraDatosPagina",
                                    origen: "' . $this->origen . '",
                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                    mes:  $("#page-tabs li").children(".active").attr("mes"),
                                    gur: $("#inputGur").val(),
                                    clc_id: $("#inputClc_id").val(),
                                    bdo_id: $("#inputBdo_id").val(),
                                    rfc_id: $("#inputRfc_id").val(),
                                    bdo_referencia: $("#inputBdo_referencia").val(),
                                    paginado: true,
                                    numpagina: page ,
                                    numreg: ' . LINEAS_POR_PAGINA . ',
                                    soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                                    soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked")
                                }
                            );
                        }
                    }
                }
                $("#MinistradoPaginador").bootstrapPaginator(options);
                $("#MinistradoPaginador").addClass("pagination");
                $("#MinistradoPaginador li").addClass("page-item")
                $("#MinistradoPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");

            ');
        } else {
            $this->mixScript('$("#MinistradoPaginador").html("")');
        }
        return json_encode($datos);
    }

    private function DameInformacionPaginado()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = (isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia : '');
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $paginado = (isset($this->prm->paginado) ? $this->prm->paginado : false);
        $numpagina = (isset($this->prm->numpagina) ? $this->prm->numpagina : 1);
        $soloSinPolizaAutorizacion = (isset($this->prm->soloSinPolizaAutorizacion) ? $this->prm->soloSinPolizaAutorizacion : false);
        $soloSinPolizaCancelacion = (isset($this->prm->soloSinPolizaCancelacion) ? $this->prm->soloSinPolizaCancelacion : false);

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = '';
        $ClaveFinanciamiento['ter_id'] = '';

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;

        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionMinistrado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'B',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "soloSinPolizaAutorizacion" => $soloSinPolizaAutorizacion,
                "soloSinPolizaCancelacion" => $soloSinPolizaCancelacion
            ),
            $paginado,
            $nInicial,
            LINEAS_POR_PAGINA
        );

        if ($paginado) {
            unset($datos[0]);
        }

        $this->mixScript('               
            $("#MinistradoPaginador").addClass("pagination");
            $("#MinistradoPaginador li").addClass("page-item")
            $("#MinistradoPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
        ');

        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ?");
        $p2 = [ $this->gpo_id,$this->ur_id];
        $conexion->consultar($p2);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $clc_array = array();
        $generaPolizaAutorizado = false;
        $generaPolizaCancelado = false;
        $fechaAutorizado = '';
        $fechaCancelado = '';
        $estadoMesCancelado = false;
        $existeFechaCan = true;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-2";
        $btnRegresa->icon = "circle-left me-2";

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $documento = getDocumento(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array(
                "ejercicio" => $this->ejercicioActual,
                "tdf_id" => $this->prm->tdf_id,
                "afe_id" => $this->prm->afe_id,
                "mmc_id" => "B"
            )
        );
        $html = '<legend>Análisis del documento a generar la póliza del ministrado</legend>';
        $html .= '
            <div>
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Información del Documento</legend>
                    <table class="table table-bordered">
                        <tr class="success">
                            <th>Proveedor</th>
                            <th>Referencia</th>
                            <th>Concepto</th>
                            <th>Percepción Presupuestal</th>
                            <th>Percepción Contable</th>
                            <th>Retención Contable</th>
                            <th>Fecha</th>
                        </tr>
                        <tr>
                            <td>' . $documento->rfc_id . "-" . $documento->nombreProveedor . '</td>
                            <td>' . $documento->tipoDocumentoFuente . "-" . $documento->referencia . '</td>
                            <td>' . $documento->concepto . '</td>
                            <td width="10%" align="right">
                                <span style="float:left">$</span>' .
            number_format($documento->percepcionPresupuestal, 2) .
            '</td>
                            <td width="10%" align="right">
                                <span style="float:left">$</span>' .
            number_format($documento->percepcionContable, 2) .
            '</td>
                            <td width="10%" align="right">
                                <span style="float:left">$</span>' .
            number_format($documento->retencionContable, 2) .
            '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>
        ';
        $tipoministrado = 0;
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        foreach ($documento->detalleMinistrado as $key => $value) {
            if ($clc_id_documento != '') {
                if ($clc_id_documento == $value->clc_id) {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }
            } else {
                $clc_array[$value->clc_id] = $value->clc_id;
            }

            if ($this->prm->agrupador == $value->agrupador) {
                $tipoministrado = $value->tipoMinistracion;
                if ($value->tipoMinistracion != 2) {
                    $generaPolizaAutorizado = true;
                    $fechaAutorizado = $value->fechaAutorizado;
                }
                $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                if ($value->estado == 'C') {
                    if ($generaPolizaAutorizado) {
                        $generaPolizaCancelado = true;
                        $fechaCancelado = $value->fechaCancelado;
                    }
                    $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                }
            }
        }
        if (!isset($estadoMesCancelado) || $estadoMesCancelado == "") {
            $existeFechaCan = false;
        }
        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        if (count($clc_array) != 0) {
            $html .= '
                <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                    <legend>CLC en la que esta el documento
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
            foreach ($clc_array as $clc_id) {
                $clcDocumento = pintaClc(
                    $gpo_id_documento,
                    $ur_id_documento,
                    $clc_id,
                    array(
                        "ejercicio" => $this->ejercicioActual,
                        "bdo_id" => $bdo_id_documento,
                        "todo" => true
                    )
                );
                $html .= $clcDocumento->html;
            }
            $html .= '
                    </fieldset></br>
                ';
        }

        /* DATOS DE LA MINISTRACIÓN */
        $html .= '
            <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                <legend>Datos de la Ministración
                    <span id="ministrado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                </legend>
            ';
        $ministrado = pintaMinistrado(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            array(
                "ejercicio" => $this->ejercicioActual,
                "clc_id" => $clc_id_documento,
            )
        );
        $html .= $ministrado->html;
        $html .= '</fieldset></br>';
        $banderaCuentas = false;
        if ($tipoministrado == 5) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("
				SELECT ncc_id
				FROM `scp_Clc` c
					INNER JOIN `scp_Contable` n USING(gpo_id, ur_id, clc_id)
					INNER JOIN (
						SELECT gpo_id, ur_id, clc_id, afe_id, t.bdo_id
						FROM ".BD_TESORERIA.".`min_BancoDocumentoMinistrado_" . $this->ejercicioActual . "` t
						WHERE gpo_id = :gpo AND ur_id = :ur
							AND bdm_agrupador = :agrupa
						GROUP BY gpo_id, ur_id, clc_id, afe_id, t.bdo_id
					)bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
				WHERE n.gpo_id = :gpo AND n.ur_id = :ur 
					AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
					OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
					OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));
            ");
            $p3 = [':gpo'=> $gpo_id_documento,':ur'=> $ur_id_documento,':agrupa'=> $this->prm->agrupador];
            $conexion->consultar($p3);
            $dataRow = $conexion->extraer();
            if (isset($dataRow["ncc_id"])) {
                $banderaCuentas = true;
            }
            $conexion->desconectar();
        }
        if (!$banderaCuentas && ($tipoministrado == 1 || $tipoministrado == 4 || $tipoministrado == 5 || $tipoministrado == 6 || $tipoministrado == 7 || $tipoministrado == 8 || $tipoministrado == 9)) {

            /* ************************************ Información de la Póliza Contable ************************************************************** */
            $polizas = getPolizaAgrupador($this->gpo_id, $this->ur_id, $this->prm->agrupador, '', '', $this->prm->afe_id);
            $polizaAut = new stdClass;
            $botonGeneraPa = '';

            //preguntamos los datos de la póliza contable de autorización
            if ($polizas->valido) {
                foreach ($polizas->polizas as $key => $value) {
                    if ($value->tipoMomento == 'A') {
                        $polizaAutorizado[$key] = getPoliza(
                            $value->gpo_id,
                            $value->ur_id,
                            $value->ue_id,
                            $value->msc_id,
                            $value->pot_id,
                            $value->prf_id,
                            $value->pma_id
                        );
                        $polizaAut->valido = $polizaAutorizado[$key]->valido;
                    }
                }
            }
            if (!isset($polizaAut->valido)) {
                $polizaAut->valido = false;
            }
            //si existe una póliza
            if (isset($polizaAutorizado)) {
                foreach ($polizaAutorizado as $key => $value) {
                    if ($value->valido) {
                        $polizaAutorizadoHtml = pintaPoliza($polizaAutorizado[$key]);
                        $html .= $polizaAutorizadoHtml->html;
                        //si el mes NO esta cerrado
                        if ($this->escritura) {
                            if ($estadoMesAutorizado == 'A') {
                                //podra regenerar la póliza contable
                                $btnGenera = new stdClass();
                                $btnGenera->id = 'btnReGeneraPolizaA';
                                $btnGenera->name = 'btnReGeneraPolizaA';
                                $btnGenera->text = 'Re-Genera Póliza de Autorización';
                                $btnGenera->value = 'Re-Genera Póliza de Autorización';
                                $btnGenera->color = 'azul';
                                $btnGenera->class = 'me-2';
                                $btnGenera->icon = 'bars-progress me-2';
                                $botonGeneraPa = $this->ponBoton($btnGenera);

                                $this->mixScript('
                                    $("#btnReGeneraPolizaA").click(function(){
                                        alertify.confirm(
                                            "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                            "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?",
                                            function(){
                                                comunica("' . $this->pantalla . '",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "' . $this->origen . '",
                                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                    mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ',  
                                                    gur: ' . $this->prm->gur . ',
                                                    clc_id: "' . $this->prm->clc_id . '",
                                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                                    bdo_id: ' . $this->prm->bdo_id . ',
                                                    tdf_id: "' . $this->prm->tdf_id . '",
                                                    afe_id: "' . $this->prm->afe_id . '",
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaAutorizado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "A",
                                                    tipoP: "A"
                                                });
                                            },
                                            undefined
                                        ).set("labels", {
                                            ok: "Aceptar", cancel: "Cancelar"
                                        });

                                        
                                    });
                                ');
                            } elseif ($existeFechaCan) {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                            }
                        } elseif ($estadoMesAutorizado == 'A') {
                            $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                        } elseif ($estadoMesAutorizado == 'C') {
                            $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                        }
                    }
                }
            }

            $polizaCan = new stdClass;
            $botonGeneraPc = '';

            //preguntamos los datos de la póliza contable de cancelación
            if ($polizas->valido) {
                foreach ($polizas->polizas as $key => $value) {
                    if ($value->tipoMomento == 'C') {
                        $polizaCancelado[$key] = getPoliza(
                            $value->gpo_id,
                            $value->ur_id,
                            $value->ue_id,
                            $value->msc_id,
                            $value->pot_id,
                            $value->prf_id,
                            $value->pma_id
                        );
                        $polizaCan->valido = $polizaCancelado[$key]->valido;
                    }
                }
            }
            if (!isset($polizaCan->valido)) {
                $polizaCan->valido = false;
            }

            //si existe tal póliza
            if (isset($polizaCancelado)) {
                foreach ($polizaCancelado as $key => $value) {
                    if ($value->valido) {
                        $polizaCanceladoHtml = pintaPoliza($polizaCancelado[$key]);
                        $html .= $polizaCanceladoHtml->html;
                        //si el mes contable NO esta cerrado
                        if ($this->escritura) {
                            if ($estadoMesCancelado == 'A') {
                                //podra regenerar la póliza contable
                                $btnGenera = new stdClass();
                                $btnGenera->id = 'btnReGeneraPolizaC';
                                $btnGenera->name = 'btnReGeneraPolizaC';
                                $btnGenera->text = 'Re-Genera Póliza de Cancelación';
                                $btnGenera->value = 'Re-Genera Póliza de Cancelación';
                                $btnGenera->type = 'button';
                                $btnGenera->class = 'me-2';
                                $btnGenera->color = 'azul';
                                $btnGenera->icon = 'bars-progress me-2';
                                $botonGeneraPc = $this->ponBoton($btnGenera);

                                $this->mixScript('
                                    $("#btnReGeneraPolizaC").click(function(){
                                        alertify.confirm(
                                            "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                            "Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?",
                                            function(){
                                                comunica("' .$this->pantalla . '", {
                                                    metodo: "GeneraPoliza",
                                                    origen: "' . $this->origen . '",
                                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                    mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                                                    gur: ' . $this->prm->gur . ',
                                                    clc_id: "' . $this->prm->clc_id . '",
                                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                                    bdo_id: ' . $this->prm->bdo_id . ',
                                                    tdf_id: "' . $this->prm->tdf_id . '",
                                                    afe_id: "' . $this->prm->afe_id . '",
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaCancelado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "C",
                                                    tipoP: "C"
                                                });
                                            },
                                            undefined
                                        ).set("labels", {
                                            ok: "Aceptar", cancel: "Cancelar"
                                        });
                                    });
                                ');
                            } else {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                            }
                        } elseif ($estadoMesCancelado == 'A') {
                            $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                        } elseif ($estadoMesCancelado == 'C') {
                            $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                        }
                    }
                }
            }

            $botones = '';
            if ($this->escritura) {
                //si deberia generar la póliza y NO existe
                if ($generaPolizaAutorizado && !$polizaAut->valido) {
                    $btnGeneraPoliza = new stdClass();
                    $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                    $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                    $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                    $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                    $btnGeneraPoliza->type = 'button';
                    $btnGeneraPoliza->color = 'verde';
                    $btnGeneraPoliza->class = 'me-2';
                    $btnGeneraPoliza->icon = 'check me-2';

                    $botones = $this->ponBoton($btnGeneraPoliza);
                    //si el mes contable se encuentra abierto
                    if ($estadoMesAutorizado == 'A') {
                        $this->mixScript('
                            $("#btnGeneraPolizaA").click(function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo: "GeneraPoliza",
                                    origen: "' . $this->origen . '",
                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                    mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                                    gur: ' . $this->prm->gur . ',
                                    clc_id: "' . $this->prm->clc_id . '",
                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                    bdo_id: ' . $this->prm->bdo_id . ',
                                    tdf_id: "' . $this->prm->tdf_id . '",
                                    afe_id: "' . $this->prm->afe_id . '",
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                    fecha: "' . $fechaAutorizado . '",
                                    agrupador: "' . $this->prm->agrupador . '",
                                    tmc_id: "A",
                                    tipoP: "A"
                                });
                            });
                        ');
                    } else {
                        //en caso contrario, debera pedir la fecha donde debe generarse
                        $btnGeneraPolizaCerrado = new stdClass();
                        $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                        $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                        $btnGeneraPolizaCerrado->text = 'Genera Póliza de Autorización';
                        $btnGeneraPolizaCerrado->value = 'Genera Póliza de Autorización';
                        $btnGeneraPolizaCerrado->type = 'button';
                        $btnGeneraPolizaCerrado->color = 'verde';
                        $btnGeneraPolizaCerrado->class = 'btn btn-outline-success me-2';
                        $btnGeneraPolizaCerrado->icon = 'check me-2';

                        $inputFecha = new stdClass();
                        $inputFecha->id = "cap_fecha";
                        $inputFecha->name = "control";
                        $inputFecha->class = 'fecha';
                        $inputFecha->label = '';
                        $inputFecha->enDiv = true;
                        $inputFecha->formato = 'fecha';
                        $inputFecha->labelClass = '_32 txtL';
                        $inputFecha->required = true;

                        $btnCancelar = new stdClass();
                        $btnCancelar->id = 'btnCancelar';
                        $btnCancelar->name = 'nuevo';
                        $btnCancelar->text = 'Cancelar';
                        $btnCancelar->value = 'Cancelar';
                        $btnCancelar->class = 'btn-outline-danger me-2';
                        $btnCancelar->color = 'rojo';
                        $btnCancelar->icon = 'fa-solid fa-trash-can';

                        $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes de la Ministrado esta cerrado, capture una nueva fecha</legend>
                                    <center>
                                    ' . $this->ponInput($inputFecha) . '
                                    </center>
                                    <hr>
                                    ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                    ' . $this->ponBoton($btnCancelar) . '
                                </div>
                            </div>
                        ';

                        $this->mixScript('
                            $("#btnGeneraPolizaA").click(function(){
                                if($("#bg_capfecha").length != 0){
                                    $(this).hide();
                                    $("#bg_capfecha").show();
                                }
                            });

                            $("#btnGeneraPolizaCerrado").click(function(){
                                comunica(
                                    "' . $this->pantalla . '",
                                    {
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        afe_id: "' . $this->prm->afe_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "A",
                                        tipoP: "A"
                                    }
                                );
                            });

                            $("#btnCancelar").click(function() {
                                $("#bg_capfecha").hide();
                                $("#btnGeneraPoliza").show();
                            });
                        ');
                    }
                }
            } elseif ($estadoMesAutorizado == 'A') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
            } elseif ($estadoMesAutorizado == 'C') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
            }

            if ($this->escritura) {
                if (
                    $generaPolizaAutorizado &&
                    $polizaAut->valido &&
                    $generaPolizaCancelado
                    && !$polizaCan->valido
                ) {
                    $btnGeneraPoliza = new stdClass();
                    $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                    $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                    $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                    $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                    $btnGeneraPoliza->type = 'button';
                    $btnGeneraPoliza->class = 'me-2';
                    $btnGeneraPoliza->color = 'verde';
                    $btnGeneraPoliza->icon = 'check me-2';

                    $botones = $this->ponBoton($btnGeneraPoliza);

                    if ($estadoMesCancelado == 'A') {
                        $this->mixScript('
                            $("#btnGeneraPolizaC").click(function() {
                                comunica(
                                    "' . $this->pantalla . '",
                                    {
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        afe_id: "' . $this->prm->afe_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: "' . $fechaCancelado . '",
                                        tmc_id: "C",
                                        tipoP: "C"
                                    }
                                );
                            });
                        ');
                    } else {
                        $btnGeneraPolizaCerrado = new stdClass();
                        $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                        $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                        $btnGeneraPolizaCerrado->text = 'Genera Póliza de Cancelación';
                        $btnGeneraPolizaCerrado->value = 'Genera Póliza de Cancelación';
                        $btnGeneraPolizaCerrado->type = 'button';
                        $btnGeneraPolizaCerrado->color = 'verde';
                        $btnGeneraPolizaCerrado->class = 'btn btn-outline-success me-2';
                        $btnGeneraPolizaCerrado->icon = 'check me-2';

                        $inputFecha = new stdClass();
                        $inputFecha->id = "cap_fecha";
                        $inputFecha->name = "control";
                        $inputFecha->class = 'fecha';
                        $inputFecha->label = '';
                        $inputFecha->enDiv = true;
                        $inputFecha->formato = 'fecha';
                        $inputFecha->labelClass = '_32 txtL';
                        $inputFecha->required = true;

                        $btnCancelar = new stdClass();
                        $btnCancelar->id = 'btnCancelar';
                        $btnCancelar->name = 'nuevo';
                        $btnCancelar->text = 'Cancelar';
                        $btnCancelar->value = 'Cancelar';
                        $btnCancelar->type = 'button';
                        $btnCancelar->class = 'btn-outline-danger me-2';
                        $btnCancelar->color = 'rojo';
                        $btnCancelar->icon = 'fa-solid fa-trash-can';

                        $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes de cancelación de la Ministrado esta cerrado, capture una nueva fecha</legend>
                                    <center>
                                    ' . $this->ponInput($inputFecha) . '
                                    </center>
                                    <hr>
                                    ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                    ' . $this->ponBoton($btnCancelar) . '
                                </div>
                            </div>
                        ';

                        $this->mixScript('
                            $("#btnGeneraPolizaC").click(function() {
                                if($("#bg_capfecha").length != 0) {
                                    $(this).hide();
                                    $("#bg_capfecha").show();
                                }
                            });

                            $("#btnGeneraPolizaCerrado").click(function() {
                                comunica(
                                    "' . $this->pantalla . '",
                                    {
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        afe_id: "' . $this->prm->afe_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "C",
                                        tipoP: "C"
                                    }
                                );
                            });

                            $("#btnCancelar").click(function(){
                                $("#bg_capfecha").hide();
                                $("#btnGeneraPoliza").show();
                            });
                        ');
                    }
                }
            } elseif ($estadoMesCancelado == 'A') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
            } elseif ($estadoMesCancelado == 'C') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
            }

            $this->agregaTools($botones);
        } //ministracion 
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        if(isset($botonGeneraPa)){
            $this->mixHtml($botonGeneraPa);
        }
        $this->setContenedor('botonC');
        if(isset($botonGeneraPc)){
            $this->mixHtml($botonGeneraPc);
        }

        $this->mixScript('
            $("#btnRegresa").click(function() {
                comunica(
                    "' . $this->pantalla . '", 
                    {
                        metodo: "PonBarraBotones",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                        gur: ' . $this->prm->gur . ',
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: ' . $this->prm->clcFiltro . ',
                        bdo_id: ' . $this->prm->bdo_id . ',
                        tdf_id: "' . $this->prm->tdf_id . '",
                        afe_id: "' . $this->prm->afe_id . '",
                        rfc_id: "' . $this->prm->rfc_id . '",
                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                        bdoFiltro: ' . $this->prm->bdoFiltro . '
                    }
                );
            });
        ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $tipoP = (isset($this->prm->tipoP) ? $this->prm->tipoP : 'A');
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = $this->ejercicioActual;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error:' . $res->mensaje . '","error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("El año es incorrecto","error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("El mes esta cerrado","error");');
                return false;
            }
            $fecha = $res->fechaMysql;
        } else {
            $this->mixScript('notify("Error en la fecha","error");');
            return false;
        }
        $bandera = 0;
        $mensajesp = "ERROR";

        $this->conectar();
        $this->setSql('
                SELECT clc_tipoCereCo
                FROM '.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $this->ejercicioActual . ' AS bdm
                    INNER JOIN scp_Clc AS c using(gpo_id, ur_id, clc_id)
                WHERE gpo_id = ? AND ur_id = ? AND bdm.bdo_id = ?
                    ' . ((isset($this->prm->clc_id) && $this->prm->clc_id != '') ? 'and clc_id= ' . $this->prm->clc_id . ' ' : '') . '
                GROUP BY clc_id LIMIT 1;
            ');
        $p4 = [$gpo_id_documento,$ur_id_documento,$this->prm->bdo_id];
        $this->consultar($p4);
        $this->desconectar();
        $rowCereCo = $this->extraer();

        if ($tipoP == "C") {
            $generaPolizaMinistrado = new sp_CereCoEgresoCancelado();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
            $generaPolizaMinistrado->setEjercicio($this->ejercicioActual);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {
            if ($rowCereCo["clc_tipoCereCo"] == 1) {
                $generaPolizaMinistrado = new sp_CereEgresoAutorizado();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaMinistrado->setEjercicio($this->ejercicioActual);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            } elseif ($rowCereCo["clc_tipoCereCo"] == 2) {
                $generaPolizaMinistrado = new sp_CereCoEgresoAutorizado();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaMinistrado->setEjercicio($this->ejercicioActual);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            }
        }

        $mensaje = "";
        $tipo = "";
        if ($bandera != 1) {
            $mensaje = "ERROR:" . $mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                notify("' . $mensaje . '", "' . $tipo . '");
                comunica(
                    "' . $this->pantalla . '",
                    {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                        gur: ' . $this->prm->gur . ',
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: ' . $this->prm->clcFiltro . ',
                        bdo_id: ' . $this->prm->bdo_id . ',
                        tdf_id: "' . $this->prm->tdf_id . '",
                        afe_id: "' . $this->prm->afe_id . '",
                        rfc_id: "' . $this->prm->rfc_id . '",
                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                        agrupador: "' . $this->prm->agrupador . '",
                        fecha: "' . $fecha . '",
                        tmc_id: "C"
                    }
                );
            ');
        } else {
            $this->mixScript('
                notify("' . $mensaje . '", "' . $tipo . '");
                comunica(
                    "' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' .(isset($this->prm->mes) ? $this->prm->mes : DATE("m")). ', 
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    afe_id: "' . $this->prm->afe_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "A"
                });
            ');
        }
    }



    private function GeneraPolizasAutorizacionPorUR()
    {
        //Verificamos que el usuario tenga permisos de escritura.
        if (!$this->escritura) {
            $this->mixScript('notify("ERROR: No tiene permisos para realizar esta acción.", "error");');
            return false;
        }
        //Lo único que necesitamos es la UR y el mes, así que hacemos las validaciones de UR (que nos hayan mandado una válida y que exista).
        if (empty($this->prm->gur) || strlen($this->prm->gur) != 3) {
            $this->mixScript('notify("ERROR: Debe indicar una UR válida.", "error");');
            return false;
        }
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        //Ahora las validaciones del mes
        if (empty($this->prm->mes) || !is_numeric($this->prm->mes) || $this->prm->mes < 1 || $this->prm->mes > 12) {
            $this->mixScript('notify("ERROR: Debe seleccionar un mes válido.", "error");');
            return false;
        }
        $mes = $this->prm->mes;

        //Vamos a verificar que la UR exista.
        $gpo_id_documento = substr($gur, 0, 1);
        $ur_id_documento = substr($gur, 1, 2);

        $this->conectar();
        $this->setSql("
            SELECT gpo_id, ur_id, ur_nombre
            FROM cat_UnidadResponsable
            WHERE gpo_id = ? AND ur_id = ?;
        ");
        $p5 = [$gpo_id_documento,$ur_id_documento];
        $this->consultar($p5);
        $this->desconectar();

        $ur = $this->extraer();
        if (empty($ur)) {
            $this->mixScript('notify("ERROR: La UR ' . $gur . ' no existe.", "error");');
            return false;
        }

        //Traemos los Documentos de la UR que no tengan Póliza de Autorización
        $periodo = array(
            'mesDesde' => $mes,
            'mesHasta' => $mes
        );

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento = array(
            'ecf_id' => '',
            'ter_id' => ''
        );

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;
        $documentosSinPoliza = vinculacionMinistrado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'B',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => '',
                "bdo_id" => '',
                "rfc_id" => '',
                "bdo_referencia" => '',
                "soloSinPolizaAutorizacion" => true,
                "soloSinPolizaCancelacion" => false
            )
        );

        //Si no hay documentos sin póliza de autorización, no hacemos nada.
        if (empty($documentosSinPoliza)) {
            $this->mixScript('notify("No hay documentos sin póliza de autorización en la UR ' . $gur . ' en el mes de ' . $this->nombreMes($mes) . '.", "warning");');
            return false;
        }

        //Ahora vamos a obtener los estados de los meses contables de la UR.
        $mesContable = array();
        $this->conectar();
        $this->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ?");
        $p6 = [ $this->gpo_id, $this->ur_id];
        $this->consultar($p6);

        while ($dataRow = $this->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        //Tengo la información de los documentos sin poliza de autorización, ahora necesito la información detallada con los detalles de ministrado
        $documentos = array();
        foreach ($documentosSinPoliza as $key => $documentoSinPoliza) {
            $documento = getDocumento(
                $gpo_id_documento,
                $ur_id_documento,
                $documentoSinPoliza['bdo_id'],
                $documentoSinPoliza['rfc_id'],
                $documentoSinPoliza['bdo_referencia'],
                array(
                    "ejercicio" => $this->ejercicioActual,
                    $documentoSinPoliza['tdf_id'],
                    "mmc_id" => "B"
                )
            );
            $documentos[$key] = $documento;
        }

        //Ahora que ya tengo la lista de documentos con sus detalles de ministrado, reviso si puedo generar las polizas y las fechas
        $generaPolizaAutorizado = array();
        $fechaAutorizado = array();
        $estadoMesAutorizado = array();
        foreach ($documentos as $key => $documento) {
            foreach ($documento->detalleMinistrado as $detalle) {
                if ($documentosSinPoliza[$key]['agrupador'] == $detalle->agrupador) {
                    if ($detalle->tipoMinistracion == 5) {
                        $conexion = new AccesoDatos();
                        $conexion->conectar();
                        $conexion->setSql("SELECT ncc_id
                                            FROM scp_Clc AS c
                                                INNER JOIN scp_Contable AS n USING(gpo_id, ur_id, clc_id)
                                                INNER JOIN (SELECT gpo_id, ur_id, clc_id, afe_id, t.bdo_id
                                                            FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_" . $this->ejercicioActual . " t
                                                            WHERE gpo_id = :gpo AND ur_id = :ur
                                                            AND bdm_agrupador = :bdm
                                                            GROUP BY gpo_id, ur_id, clc_id, afe_id, t.bdo_id)bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                                            WHERE n.gpo_id = :gpo AND n.ur_id = :ur
                                                AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
                                                OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
                                                OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));");
                        $p7 = [':gpo'=>$gpo_id_documento,':ur'=> $ur_id_documento,':bdm'=> $documentosSinPoliza[$key]['agrupador']];
                        $conexion->consultar($p7);
                        $dataRow = $conexion->extraer();
                        if ($dataRow["ncc_id"]) {
                            $this->mixScript('notify("ERROR: No se puede generar la póliza de autorización para el documento ' . $documentosSinPoliza[$key]['agrupador'] . '.", "error");');
                            return false;
                        }
                        $conexion->desconectar();
                    } elseif ($detalle->tipoMinistracion == 1 || $detalle->tipoMinistracion == 4 || $detalle->tipoMinistracion == 5 || $detalle->tipoMinistracion == 6 || $detalle->tipoMinistracion == 7 || $detalle->tipoMinistracion == 8 || $detalle->tipoMinistracion == 9) {
                        $generaPolizaAutorizado[$key] = true;
                        $fechaAutorizado[$key] = $detalle->fechaAutorizado;
                    } else {
                        $this->mixScript('notify("ERROR: No se puede generar la póliza de autorización para el documento ' . $documentosSinPoliza[$key]['agrupador'] . '.", "error");');
                        return false;
                    }
                    $estadoMesAutorizado[$key] = $mesContable[str_pad($detalle->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                }
            }
        }

        //Ahora ya tengo, por cada documento, la banderita que me indica en cada caso si se debe generar la poliza, la fecha de autorizacion y el estado del mes contable de dicha fecha.
        //Ahora creo un array donde voy a tener la informacion de los documentos que se van a procesar, tal cual se van a procesar.
        $documentosAProcesar = array();
        foreach ($documentosSinPoliza as $key => $value) {
            //si el mes contable se encuentra abierto se registra la información tal cual se tiene
            if ($estadoMesAutorizado[$key] == 'A') {
                $documentosAProcesar[$key] = array(
                    'mes' => substr($fechaAutorizado[$key], 3, 2),
                    'gur' => $gur,
                    'clc_id' => $value['clc_id'],
                    'clcFiltro' => '',
                    'bdo_id' => $value['bdo_id'],
                    'tdf_id' => $value['tdf_id'],
                    'rfc_id' => $value['rfc_id'],
                    'bdo_referencia' => $value['bdo_referencia'],
                    'bdoFiltro' => '',
                    'fecha' => $fechaAutorizado[$key],
                    'agrupador' => $value['agrupador'],
                    'tmc_id' => 'A',
                    'tipoP' => 'A'
                );
            } else {
                $this->mixScript('notify("ERROR: En el documento ' . $key . ', el mes esta cerrado.", "error");');
                return false;
            }
        }

        //Hasta aquí ya tenemos la información de los documentos que se van a procesar, bien validada, ahora necesitamos procesarla.
        //Inicia el proceso de generación de pólizas automático para cada documento.
        $mensajes = array();
        foreach ($documentosAProcesar as $key => $value) {
            //Primero, validamos las fechas.
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $value['fecha'];
            $fechaValidar->anio = $this->ejercicioActual;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error: Documento ' . $key . ' - ' . $res->mensaje . '.", "error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("En el documento ' . $key . ', el año es incorrecto", "error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("En el documento ' . $key . ', el mes esta cerrado.", "error");');
                return false;
            }
            $fecha = $res->fechaMysql;

            //Ahora obtenemos el tipo de cereco, para saber a qué SP llamar.
            $bandera = 0;
            $mensajesp = "ERROR";

            $this->conectar();
            $this->setSql('SELECT clc_tipoCereCo
                            FROM '.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $this->ejercicioActual . ' AS bdm
                                INNER JOIN scp_Clc AS c using(gpo_id, ur_id, clc_id)
                            WHERE gpo_id = ? AND ur_id = ? AND bdm.bdo_id = ? ' . ((isset($value['clc_id']) && $value['clc_id'] != '') ? ' AND clc_id= ' . $value['clc_id'] . ' ' : '') . '
                            GROUP BY clc_id LIMIT 1;');
            $p8 = [$gpo_id_documento, $ur_id_documento, $value['bdo_id']];
            $this->consultar($p8);
            $this->desconectar();
            $rowCereCo = $this->extraer();

            //Llamamos al SP adecuado (ya sea para Cere o Cereco)
            if ($rowCereCo["clc_tipoCereCo"] == 1) {
                $generaPolizaMinistrado = new sp_CereEgresoAutorizado();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($value['agrupador']);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($value['tmc_id']);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaMinistrado->setEjercicio($this->ejercicioActual);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            } elseif ($rowCereCo["clc_tipoCereCo"] == 2) {
                $generaPolizaMinistrado = new sp_CereCoEgresoAutorizado();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($value['agrupador']);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($value['tmc_id']);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaMinistrado->setEjercicio($this->ejercicioActual);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            }

            //Mensajes que se muestran al usuario, ya sea de error o de éxito.
            if ($bandera != 1) {
                $mensaje = "ERROR:" . $mensajesp;
                $tipo = "error";
            } else {
                $mensaje = $mensajesp;
                $tipo = "success";
            }
            array_push($mensajes, array(
                'mensaje' => $mensaje,
                'tipo' => $tipo
            ));
        }
        //Ahora separamos los mensajes de éxito y los de falla.
        $mensajeExitosos = array_filter($mensajes, function ($dato) {
            return $dato["tipo"] === "success";
        });
        $mensajeFallas = array_filter($mensajes, function ($dato) {
            return $dato["tipo"] === "error";
        });
        $mensajeFinal = '';
        foreach ($mensajeExitosos as $mensaje) {
            $mensajeFinal .= (empty($mensajeFinal) ? '' : '\n') . $mensaje['mensaje'];
        }
        if (!empty($mensajeFinal)) {
            $this->mixScript('notify("' . $mensajeFinal . '","success");');
        }
        $mensajeFinal = '';
        foreach ($mensajeFallas as $mensaje) {
            $mensajeFinal .= (empty($mensajeFinal) ? '' : '\n') . $mensaje['mensaje'];
        }
        if (!empty($mensajeFinal)) {
            $this->mixScript('notify("' . $mensajeFinal . '","error");');
        }
        $this->mixScript(
            (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
            comunica("' . $this->pantalla . '", { 
                metodo: "MuestraDatos", 
                origen: "' . $this->origen . '", 
                seleccion: "",
                mes: $("#page-tabs li").children(".active").attr("mes"),
                gur: $("#inputGur").val(),
                clc_id: $("#inputClc_id").val(),
                bdo_id: $("#inputBdo_id").val(),
                rfc_id: $("#inputRfc_id").val(),
                soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                bdo_referencia: $("#inputBdo_referencia").val()
            });'
        );
    }

    private function nombreMes($mes): string
    {
        $meses = array(
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        );
        return $meses[$mes];
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['12']['3']['1'])) {
    new ControladorMinistrado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
