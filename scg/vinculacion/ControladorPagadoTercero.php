<?php

session_start();
DEFINE("LINEAS_POR_PAGINA", 1000);
require_once dirname(__FILE__) . '/../../lib/getDocumentoTercero.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPagadoContable.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionPagadoTercero.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../lib/estadoMesContable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_AutorizarPagoDocumentoTercero.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_CancelarPagoDocumentoTercero.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_PagadoTerceroAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_PagadoTerceroCancelado.php';

class ControladorPagadoTercero extends WebResponse
{

    private $escritura;
    private $reporteAcumulado;
    private $campoHabilitado;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    private $origen;
    private $usr_id;
    private $appEjercicio;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['13']['2']) ? TRUE : FALSE;
        $this->reporteAcumulado = isset($_SESSION[_S]['prm']['C']['5']['13']['107']) ? TRUE : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }
        $this->usr_id = getSession('usr_id');

        // Marcos: 16/feb/2016
        // Determinaremos si viene de la vinculación por CLC, para que en éste caso, inhabilite los cuadros de texto
        $this->campoHabilitado = "";
        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $this->campoHabilitado = " disabled ";
        }

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla':
                $this->DibujaPantalla();
                break;
            case 'MuestraDatos':
                $this->MuestraDatos();
                break;
            case 'MuestraDatosPagina':
                $this->MuestraDatosPagina();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            case 'PagarDetalle':
                $this->PagarDetalle();
                break;
            case 'Pagar':
                $this->Pagar();
                break;
            case 'DocumentoDetalle':
                $this->DocumentoDetalle();
                break;
            case 'CallProcedure':
                $this->CallProcedure();
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $html = $this->getTablaPagadoTercero();
        $this->PonBarraBotones();
        $this->setContenedor('contenedor');
        $es116 = $this->gpo_id.$this->ur_id == 116;
        $clc = ((isset($this->prm->clc) && $this->prm->clc != "") ? $this->prm->clc : "");
        $this->mixHtml($html);

    }

    private function PonBarraBotones()
    {
        //Proviene de la pantalla de Vinculacion/Clc
        $tipo = ((isset($this->prm->tipo) && $this->prm->tipo != "") ? $this->prm->tipo : "");
        $edo = ((isset($this->prm->edo) && $this->prm->edo != "") ? $this->prm->edo : "");
        $buscarVinculacion = ((isset($this->prm->p) && $this->prm->p != "") ? $this->prm->p : "");

        // Botones y opciones que se dibujarán en la barra de botones
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = '&nbsp;&nbsp;Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->icon = "refresh me-2";
        $btnLimpiar->color = "cielo";
        $btnLimpiar->class = "me-1";


        $clcFiltro = FALSE;
        $bdoFiltro = FALSE;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = TRUE;
                break;
            case "bdo":
                $bdoFiltro = TRUE;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = TRUE;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = TRUE;
                }
                break;
        }

        $botones = '
            <div class="row">
                <div class="d-flex flex-row">
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc")
                    ? '
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    ' . $this->ponBoton($btnRegresa) . '
                    </fieldset>' : '') . '
                    <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                        <input id="inputClc_id" placeHolder="CLC" type="text" ' . $this->campoHabilitado . ' maxlength=6 class="form-control" value="' . ($clcFiltro ? $this->prm->clc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputBdo_id" placeHolder="# Documento" ' . $this->campoHabilitado . ' maxlength=7 class="form-control" value="' . ($bdoFiltro ? $this->prm->bdo_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputRfc_id" placeHolder="RFC" ' . $this->campoHabilitado . ' maxlength=20 class="form-control" value="' . ($bdoFiltro ? $this->prm->rfc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputBdo_referencia" placeHolder="Referencia" ' . $this->campoHabilitado . ' class="form-control" value="' . ($bdoFiltro ? $this->prm->bdo_referencia : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <button id="excelPagado" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>Excel</button>
                    </fieldset>
                </div>
                ' . (($this->reporteAcumulado) ? '
                <div class="row">
                    <fieldset class="col-xs-7" style="padding-right: 1px !important;">
                    </fieldset>
                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                        <br>
                        <input id="repacumulado" name="repacumulado" type="checkbox" value=0>&nbsp;Acumulado</input>
                    </fieldset>
                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                        <input id="repacumuladomes" name="repacumuladomes" type="checkbox" value=0>&nbsp;Acumulado al mes seleccionado</input>  
                    </fieldset>
                </div>' 
                : '');

        $this->agregaTools($botones);

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
            INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ? AND msc_id NOT IN ( 0, 13 )');
        $this->consultar([$this->gpo_id, $this->ur_id]);
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

        $this->mixHtml('
            <div id="page-tabs"><ul class="nav nav-tabs">
            ' . $pestana . '
            </ul></div>
        ');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                    $("#btnRegresa").click(function(e){
                        $("nav").menusinpres("clickMenu");
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
            case 'clc':
                $this->mixScript('
                    $("#btnRegresa").click(function(e){
                        e.preventDefault();
                        $("#btnRegresa").attr("origen", "");
                        comunica("scg/vinculacion/ControladorVinculacionClc.php",{ 
                            mes:  ' . $this->prm->mes . ', 
                            tipo: "'.$tipo.'",  
                            edo: "'.$edo.'", 
                            buscar: "'.$buscarVinculacion.'"
                        });
                        $("#contenedor").html("");
                        $("#toolsPantalla").html("");
                        e.stopPropagation();
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "") . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
        }

        $this->mixScript('
            $("#opciones").click( function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "Filtro",
                    origen: "' . $this->origen . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });
            });

            ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
            : '') . '
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );

            $("#btnLimpiar").click( function(e){
                $("#inputGur").val("");
                $("#inputClc_id").val("");
                $("#inputBdo_id").val("");
                $("#inputRfc_id").val("");
                $("#inputBdo_referencia").val("");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });

                e.stopPropagation();
                return false;
            });


            $("#repacumulado").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#repacumuladomes").prop("checked", false);
                }
            });
            $("#repacumuladomes").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#repacumulado").prop("checked", false);
                }
            });
            $("#excelPagado").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_PagadoTercero.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "7" ))
                    ' . (($this->reporteAcumulado) ? '
                    .append($("<input>").attr("type", "hidden").attr("name", "repacumuladomes").attr("value", ( $("#repacumuladomes").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "repacumulado").attr("value", ( $("#repacumulado").prop("checked" ) ? 1 : 0 ) ))' : '') . ' 
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "F" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "") . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                $("body").append(form);
                form.submit();
            });

            $("#imprimirPagado").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "7" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "F" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "") . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

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
        $html = $this->getTablaPagadoTercero();
        $this->mixHtml($html);
        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $html = '<div id="myGridPagado" ></div>';
        $this->mixHtml($html);

        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });

                e.stopPropagation();
                return false;
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

    private function getTablaPagadoTercero(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->id = 'tbl-pagado-tercero';
        $tabla->cabeceraFija = true;
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'MuestraDatos';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DOC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        $th->width = "15%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = "20%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF PAG';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA PAGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '20%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AUTORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL AUT.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL CAN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_referencia'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_concepto'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id_pagado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdp_referencia'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_importe'];
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['estado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizacion'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelacion'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaCancelado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelado'];
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);
    }

    private function MuestraDatos()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(', ', ',', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
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

        $detalle = FALSE;
        $paginado = TRUE;
        $registroInicial = 0;
        $PrimeraCon = TRUE;
        $this->MuestraDatosPagina(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'F',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );
        
        return false;
    }   

    private function MuestraDatosPagina($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, 
        $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array(),
        $paginado = false, $nInicial = 0, $numreg = 0, $ejecsql2 = false)
    {
        set_time_limit(0);

        $ejercicio = APP_EJERCICIO;
        $sqlFiltro = '';
        $sqlParametros = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':mmc' => $mmc_id,
        ];
        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = " AND n.cta_gpo_id = :gpo AND n.cta_ur_id = :nur";
            $sqlParametros[':ngpo'] = substr($parametroExtra['gur'], 0, 1);
            $sqlParametros[':nur'] = substr($parametroExtra['gur'], 1, 2);
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= " AND t.clc_id = :tclc ";
            $sqlParametros[':tclc'] = $parametroExtra['clc_id'];
        }
        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltro .= " AND t.bdo_id = :tbdo ";
            $sqlParametros[':tbdo'] = $parametroExtra['bdo_id'];
        }
        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= " AND b.rfc_id = :brfc ";
            $sqlParametros[':brfc'] = $parametroExtra['rfc_id'];
        }
        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
        }

        $var_MAX = " MAX";

        if (isset($parametroExtra['repacumulado']) && $parametroExtra['repacumulado']) {
            $periodo['mesDesde'] = '';
        }
        if (isset($parametroExtra['repacumuladomes']) && $parametroExtra['repacumuladomes']) {
            if ($periodo['mesDesde'] != '') {
                $sqlFiltro .= " AND t.msc_id <= :mesdesde ";
                $sqlParametros[':mesdesde'] = $periodo['mesDesde'];
            }
        } else {
            if ($periodo['mesDesde'] != '') {
                $sqlFiltro .= " AND t.msc_id = :mesdesde ";
                $sqlParametros[':mesdesde'] = $periodo['mesDesde'];
                $var_MAX = "";
            }
        }
        $sql =
            "SELECT
                t.gpo_id, t.ur_id,MAX(t.clc_id) AS clc_id, MAX(t.afe_id) AS afe_id,
                t.bdo_id, t.tdf_id, t.rfc_id, t.bdo_referencia, t.bdo_concepto,
                MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) AS bdp_referencia,
                MAX(t.bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal, MAX(t.bdo_percepcionContable) AS bdo_percepcionContable, MAX(t.bdo_retencionContable) AS bdo_retencionContable,
                CONCAT(t.gpo_id, t.ur_id) as gur,
                (MAX(t.bdo_percepcionPresupuestal) + MAX(t.bdo_percepcionContable)) AS bdo_importe,
                MAX(v.msc_id) AS msc_id,
                MAX(v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
                v.bdp_agrupador AS agrupador,
                pa.polizaAutorizado, max(pa.fechaAutorizado) AS fechaAutorizado ,
                max(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, max(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                max(pa.msc_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, max(pa.prf_id_polizaAutorizado)AS prf_id_polizaAutorizado,
                max(pa.pma_id_polizaAutorizado) AS gpo_id_polizaAutorizado,
                max(pc.polizaCancelado) AS polizaCancelado, max(pc.fechaCancelado) AS  fechaCancelado,
                max(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, max(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, max(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                max(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, max(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, max(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                max(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

                CONCAT( t.gpo_id, t.ur_id, '.', LPAD( {$var_MAX}(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ) ) AS id,ecf_clave
            FROM (
                SELECT
                    t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id , t.afe_id, {$var_MAX}(t.msc_id) AS  msc_id,
                    t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                    SUM(t.bdm_percepcionPresupuestal) AS bdo_percepcionPresupuestal, SUM(t.bdm_percepcionContable) AS bdo_percepcionContable, SUM(t.bdm_retencionContable) AS bdo_retencionContable,
                    IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen, IFNULL(o.ur_id, n.ur_id) AS ur_id_origen, IFNULL(MAX(o.clc_id), MAX(n.clc_id)) AS
                    clc_id_origen, IFNULL(MAX(o.afe_id), n.afe_id) AS afe_id_origen, IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen,ecf_clave
                FROM cat_BancoDocumentos b
                    INNER JOIN tesoreria.min_BancoDocumentoMinistrado_{$ejercicio} t USING( gpo_id, ur_id, bdo_id )
                    INNER JOIN scp_Contable n USING(gpo_id, ur_id, clc_id,afe_id,bdo_id)
                    INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                    INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
                    LEFT JOIN ctrl{$ejercicio}.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
                WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
                    AND c.tcl_id = 'C'
                GROUP BY gpo_id, ur_id,t.afe_id, bdo_id, gpo_id_origen, ur_id_origen, bdo_id_origen
            )t
                INNER JOIN (
                    SELECT
                        gpo_id, ur_id, bdo_id,afe_id,tdf_id,bdp_referencia,msc_id,bdp_estado,bdp_fechaAutorizado,bdp_fechaCancelado,bdp_agrupador
                    FROM cg_BancoDocumentoPagadoContable
                    WHERE -- gpo_id = $gpo_id AND ur_id = $ur_id and
                        bdp_estado='A'
            )v USING( gpo_id, ur_id, bdo_id,afe_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ) AS polizaAutorizado,
                        x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                        x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                        x.pdx_agrupador AS bdp_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,pma_id
                )pa USING( bdp_agrupador )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                        MAX(m.msc_id) AS msc_id_polizaCancelado, MAX(m.pot_id) AS pot_id_polizaCancelado, MAX(m.prf_id) AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                        CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ) AS polizaCancelado,
                        x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                        x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                        x.pdx_agrupador AS bdp_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,pma_id
                )pc USING( bdp_agrupador )
            GROUP BY gpo_id, ur_id, bdo_id, bdp_agrupador,polizaAutorizado
        ";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($sqlParametros);
        $this->desconectar();
        $this->setParams($sqlParametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = $this->getNumeroRegistros();
        $nav->idTabla = 'tbl-pagado-tercero';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {

            $this->mixScript('
                $("#tbl-pagado-tercero tr").on("dblclick", function (ev){
                    ev.stopPropagation();
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }
                    const prm = $("#tbl-pagado-tercero").tabla("getFilaSeleccion");

                    if( prm.estado != "A" ){
                        notify("Debe realizar el registro de pago, para ver el detalle", "warning" );
                    }else{
                        comunica("' . $this->pantalla . '", {
                            metodo: "Documento",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                            mes: $("#page-tabs li").children(".active").attr("mes"),
                            clc_id: $("#inputClc_id").val(),
                            clcFiltro: var_clcFiltro,
                            gur: prm.gur,
                            afe_id: prm.afe_id,
                            bdo_id: prm.bdo_id,
                            tdf_id: prm.tdf_id,
                            rfc_id: prm.rfc_id,
                            bdo_referencia: prm.bdo_referencia,
                            bdoFiltro: var_bdoFiltro,
                            agrupador: prm.agrupador
                        });
                    }

                });
            ');

            $this->mixScript('
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();'
            );
        }

    }

    private function DameInformacionPaginado()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(', ', ',', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $paginado = (isset($this->prm->paginado) ? $this->prm->paginado : false);
        $numpagina = (isset($this->prm->numpagina) ? $this->prm->numpagina : 1);
        $numreg = (isset($this->prm->numreg) ? $this->prm->numreg : 0);

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

        $detalle = FALSE;
        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionRegistroPagadoTercero(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'F',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $nInicial,
            LINEAS_POR_PAGINA
        );
        if ($paginado == true) {
            unset($datos[0]);
        }
        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(" SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        $mesContableAbierto = FALSE;
        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
            if (!$mesContableAbierto && $dataRow['msc_id'] == 'A') {
                $mesContableAbierto = $dataRow['msc_id'];
            }
        }
        if (isset($this->prm->gur)) {
            $gpo_id_documento = substr($this->prm->gur, 0, 1);
            $ur_id_documento = substr($this->prm->gur, 1, 2);
        } else {
            $gpo_id_documento = $this->gpo_id;
            $ur_id_documento = $this->ur_id;
            $this->prm->gur = $this->gpo_id . $this->ur_id;
        }
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $afe_idstr = (isset($this->prm->afe_id) ? $this->prm->afe_id : '');

        $clc_array = array();

        $generaPolizaAutorizado = FALSE;
        $generaPolizaCancelado = FALSE;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = FALSE;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $botones = $this->ponBoton($btnRegresa);

        $estadoMesAutorizado = null;

        $this->agregaTools($botones);
        $documento = getDocumentoTercero(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array(
                "ejercicio" => APP_EJERCICIO,
                "tdf_id" => (isset($this->prm->tdf_id) ? $this->prm->tdf_id
                    : ''),
                "mmc_id" => "F",
                "gpo_id_origen" => $this->gpo_id,
                "ur_id_origen" => $this->ur_id,
                "afe_id" => $afe_idstr
            )
        );
        if (!isset($this->prm->tdf_id)) {
            $this->prm->tdf_id = $documento->tipoDocumentoFuente;
        }
        $html = '
            <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del documento a generar la póliza del pagado</legend>
            <div class="px-3">
                <fieldset class="col-md-12 col-xs-12">
                    <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Información del Documento</legend>
                    <table class="table table-bordered">
                        <tr class="success" style="background-color: #dff0d8 !important;">
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
                            <td width="10%" align="right"><span style="float:left">$</span>' . 
                                number_format($documento->percepcionPresupuestal,2) . 
                            '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . 
                            number_format($documento->percepcionContable,2) . 
                            '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' .
                                number_format($documento->retencionContable,2) . 
                            '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>
        ';
        //preguntamos la(s) CLC(s) del documento
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        if (is_array($documento->detallePagadoTercero)) {
            foreach ($documento->detallePagadoTercero as $key => $value) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id']
                            = $value->gpo_id;
                        $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id']
                            = $value->ur_id;
                        $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id']
                            = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id']
                        = $value->gpo_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id']
                        = $value->ur_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id']
                        = $value->clc_id;
                }
                if ($this->prm->agrupador == $value->agrupador) {
                    if ($value->tipoPagado != 3) {
                        $generaPolizaAutorizado = TRUE;
                        $fechaAutorizado = $value->fechaAutorizado;
                    }
                    $estadoMesAutorizado = $mesContable[str_pad(
                        $value->mesAutorizado,
                        2,
                        '0',
                        STR_PAD_LEFT
                    )]['ems_id'];
                    if ($value->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = TRUE;
                            $fechaCancelado = $value->fechaCancelado;
                        }
                        $estadoMesCancelado = $mesContable[str_pad(
                            $value->mesCancelado,
                            2,
                            '0',
                            STR_PAD_LEFT
                        )]['ems_id'];
                    }
                }
                $estadoPago = $value->estado;
            }
        }
        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        if (count($clc_array) != 0) {
            $html .= '
                <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                    <legend>CLC en la que esta el documento
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
            foreach ($clc_array as $key => $value) {
                $clcDocumento = pintaClc(
                    $value['gpo_id'],
                    $value['ur_id'],
                    $value['clc_id'],
                    array(
                        "ejercicio" => APP_EJERCICIO,
                        "bdo_id" => $bdo_id_documento,
                        "todo" => TRUE,
                        "gpo_id_origen" => $this->gpo_id,
                        "ur_id_origen" => $this->ur_id
                    )
                );
                $html .= $clcDocumento->html;
            }
            $html .= '
                </fieldset></br>
            ';
        }

        /* DATOS DEL REGISTRO DE PAGO */
        $html .= '
            <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                <legend>Datos del Registro de Pago
                    <span id="pagado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                </legend>
        ';
        $pagado = pintaPagadoContable(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            array("ejercicio" => APP_EJERCICIO, "clc_id" => $clc_id_documento, "agrupador" => $this->prm->agrupador)
        );
        $html .= $pagado->html;
        $html .= '
            </fieldset></br>
        ';

        /** *********************************** Información de la Póliza Contable ************************************************************** */
        $polizas = getPolizaAgrupador(
            $this->gpo_id,
            $this->ur_id,
            $this->prm->agrupador
        );

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
            $polizaAut->valido = FALSE;
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
                            $btnGenera->color = 'verde';
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
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
                                                mes: ' . $this->prm->mes . ',
                                                gur: ' . $this->prm->gur . ',
                                                clc_id: "' . $this->prm->clc_id . '",
                                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                                bdo_id: ' . $this->prm->bdo_id . ',
                                                tdf_id: "' . $this->prm->tdf_id . '",
                                                rfc_id: "' . $this->prm->rfc_id . '",
                                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                gpo_id: "' . $value->gpo_id . '",
                                                ur_id: "' . $value->ur_id . '",
                                                ue_id: "' . $value->ue_id . '",
                                                fecha: "' . $fechaAutorizado . '",
                                                agrupador: "' . $this->prm->agrupador . '",
                                                tmc_id: "A"
                                            });
                                        },
                                        undefined
                                    ).set("labels", {
                                        ok: "Proceder", cancel: "Cancelar"
                                    });
                                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                                });
                            ');
                        } else {
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
            $polizaCan->valido = FALSE;
        }

        //si existe tal póliza
        if (isset($polizaCancelado)) {
            foreach ($polizaCancelado as $key => $value) {
                if ($value->valido) {
                    $polizaCanceladoHtml = pintaPoliza($polizaCancelado[$key]);
                    $html .= $polizaCanceladoHtml->html;
                    //si el mes cpntable NO esta cerrado
                    if ($this->escritura) {
                        if ($estadoMesCancelado == 'A') {
                            //podra regenerar la póliza contable
                            $btnGenera = new stdClass();
                            $btnGenera->id = 'btnReGeneraPolizaC';
                            $btnGenera->name = 'btnReGeneraPolizaC';
                            $btnGenera->text = 'Re-Genera Póliza de Cancelación';
                            $btnGenera->value = 'Re-Genera Póliza de Cancelación';
                            $btnGenera->color = 'verde';
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPc = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaC").click(function(){
                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: ' . $this->prm->mes . ',
                                                gur: ' . $this->prm->gur . ',
                                                clc_id: "' . $this->prm->clc_id . '",
                                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                                bdo_id: ' . $this->prm->bdo_id . ',
                                                tdf_id: "' . $this->prm->tdf_id . '",
                                                rfc_id: "' . $this->prm->rfc_id . '",
                                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                gpo_id: "' . $value->gpo_id . '",
                                                ur_id: "' . $value->ur_id . '",
                                                ue_id: "' . $value->ue_id . '",
                                                fecha: "' . $fechaCancelado . '",
                                                agrupador: "' . $this->prm->agrupador . '",
                                                tmc_id: "C"
                                            });
                                        },
                                        undefined
                                    ).set("labels", {
                                        ok: "Aceptar", cancel: "Cancelar"
                                    });
                                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
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
                $btnGeneraPoliza->color = 'verde';
                $btnGeneraPoliza->icon = 'solid fa-check me-2';
                $btnGeneraPoliza->class = 'me-1';

                $botones = $this->ponBoton($btnGeneraPoliza);
                //si el mes contable se encuentra abierto
                if ($estadoMesAutorizado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaA").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                bdo_id: ' . $this->prm->bdo_id . ',
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                fecha: "' . $fechaAutorizado . '",
                                agrupador: "' . $this->prm->agrupador . '",
                                tmc_id: "A"
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
                    $btnGeneraPolizaCerrado->color = 'verde';
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

                    $inputFecha = new stdClass();
                    $inputFecha->id = "cap_fecha";
                    $inputFecha->name = "control";
                    $inputFecha->class = 'fecha';
                    $inputFecha->label = '';
                    $inputFecha->enDiv = TRUE;
                    $inputFecha->formato = 'fecha';
                    $inputFecha->labelClass = '_32 txtL';
                    $inputFecha->required = TRUE;

                    $btnCancelar = new stdClass();
                    $btnCancelar->id = 'btnCancelar';
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes del Pagado esta cerrado, capture una nueva fecha</legend>
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
                            if($("#bg_capfecha").length != 0 ){
                                $(this).hide();
                                $("#bg_capfecha").show();
                            }
                        });

                        $("#btnGeneraPolizaCerrado").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                bdo_id: ' . $this->prm->bdo_id . ',
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                agrupador: "' . $this->prm->agrupador . '",
                                fecha: $("#cap_fecha").val(),
                                tmc_id: "A"
                            });
                        });

                        $("#btnCancelar").click(function(){
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
                $generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado
                && !$polizaCan->valido
            ) {

                $btnGeneraPoliza = new stdClass();
                $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->color = 'verde';
                $btnGeneraPoliza->icon = 'solid fa-check me-2';
                $btnGeneraPoliza->class = 'me-1';

                $botones = $this->ponBoton($btnGeneraPoliza);

                if ($estadoMesCancelado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaC").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                bdo_id: ' . $this->prm->bdo_id . ',
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                agrupador: "' . $this->prm->agrupador . '",
                                fecha: "' . $fechaCancelado . '",
                                tmc_id: "C"
                            });
                        });
                    ');
                } else {
                    $btnGeneraPolizaCerrado = new stdClass();
                    $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                    $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                    $btnGeneraPolizaCerrado->text = 'Genera Póliza de Cancelación';
                    $btnGeneraPolizaCerrado->value = 'Genera Póliza de Cancelación';
                    $btnGeneraPolizaCerrado->color = 'verde';
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

                    $inputFecha = new stdClass();
                    $inputFecha->id = "cap_fecha";
                    $inputFecha->name = "control";
                    $inputFecha->class = 'fecha';
                    $inputFecha->label = '';
                    $inputFecha->enDiv = TRUE;
                    $inputFecha->formato = 'fecha';
                    $inputFecha->labelClass = '_32 txtL';
                    $inputFecha->required = TRUE;

                    $btnCancelar = new stdClass();
                    $btnCancelar->id = 'btnCancelar';
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes de cancelación del Pagado esta cerrado, capture una nueva fecha</legend>
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
                        $("#btnGeneraPolizaC").click(function(){
                            if($("#bg_capfecha").length != 0 ){
                                $(this).hide();
                                $("#bg_capfecha").show();
                            }
                        });

                        $("#btnGeneraPolizaCerrado").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: ' . $this->prm->clcFiltro . ',
                                bdo_id: ' . $this->prm->bdo_id . ',
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                agrupador: "' . $this->prm->agrupador . '",
                                fecha: $("#cap_fecha").val(),
                                tmc_id: "C"
                            });
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

        if(isset($estadoPago)){
            if ($this->escritura && $estadoPago == 'A') {
                $btnCancelar = new stdClass();
                $btnCancelar->id = 'btnCancelar';
                $btnCancelar->name = 'nuevo';
                $btnCancelar->color = 'rojo';
                $btnCancelar->text = 'Cancelar Pago';
                $btnCancelar->icon = 'times me-2';
                $btnCancelar->value = 'Cancelar Pago';

                $botones .= $this->ponBoton($btnCancelar);

                $mensajecancela = '';
                if ($estadoMesAutorizado == 'C') {
                    $mensajecancela = 'Mes de pago Cerrado, se generara póliza de CANCELACIÓN en el primer mes abierto<br/>';
                }
                $this->mixScript('
                    $("#btnCancelar").click(function(){
                        alertify.confirm(
                            "CANCELAR PAGO ",
                            "' . $mensajecancela . 'Esta acción Cancelara su registro de pago, desea proceder? ",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo: "CallProcedure",
                                    origen: "' . $this->origen . '",
                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                    mes: ' . $this->prm->mes . ',
                                    gur: ' . $this->prm->gur . ',
                                    clc_id: "' . $this->prm->clc_id . '",
                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                    bdo_id: ' . $this->prm->bdo_id . ',' . ($afe_idstr == '' ? '' : 'afes: ' . $afe_idstr . ',') . '
                                    tdf_id: "' . $this->prm->tdf_id . '",
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                    agrupador: "' . $this->prm->agrupador . '",
                                    fecha: "' . $fechaAutorizado . '",
                                    mesContableAbierto: "' . $mesContableAbierto . '",
                                    tmc_id: "C"
                                });
                            },
                            undefined
                        ).set("labels", {
                            ok: "Proceder", cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-success");
                        }
                    });
                ');
            } else {
            }
        }
        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);
        $this->setContenedor('botonC');
        $this->mixHtml($botonGeneraPc);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . '
                });
            });
        ');
    }

    private function GeneraPoliza()
    {
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
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

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaPagado = new sp_PagadoTerceroCancelado();
            $generaPolizaPagado->setGpo_id($this->gpo_id);
            $generaPolizaPagado->setUr_id($this->ur_id);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO T");
            $generaPolizaPagado->setEjercicio(APP_EJERCICIO);
            $generaPolizaPagado->setSis_id("SCG");
            $generaPolizaPagado->execute(false, false);
            $bandera = $generaPolizaPagado->getBandera();
            $mensajesp = $generaPolizaPagado->getMensaje();
        } else {

            $generaPolizaPagado = new sp_PagadoTerceroAutorizado();
            $generaPolizaPagado->setGpo_id($this->gpo_id);
            $generaPolizaPagado->setUr_id($this->ur_id);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO T");
            $generaPolizaPagado->setEjercicio(APP_EJERCICIO);
            $generaPolizaPagado->setSis_id("SCG");
            $generaPolizaPagado->execute(false, false);
            $bandera = $generaPolizaPagado->getBandera();
            $mensajesp = $generaPolizaPagado->getMensaje();
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
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? $this->prm->clc_id : '') . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "C"
                });
            ');
        } else {
            $this->mixScript('
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? $this->prm->clc_id : '') . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
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

    //muestra la informacion de los datos de transferencia de los documentos a pagar
    private function PagarDetalle()
    {
        if (!isset($this->prm->gur)) {
            $this->prm->gur = $this->gpo_id . $this->ur_id;
        }
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $datos = $this->prm->data;
        $botones = '';
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = '&nbsp;&nbsp;Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $botones = $this->ponBoton($btnRegresa);

        $bdos = '';
        $afes = '';
        foreach ($datos as $bd) {
            if ($bdos == "") $bdos .= $bd->bdo_id;
            else $bdos .= "," . $bd->bdo_id;

            if ($afes == "") {

                $afes .= $bd->afe_id;
            } else {
                $pos = strpos($afes, $bd->afe_id);
                if ($pos === false) {
                    $afes .= "," . $bd->afe_id;
                }
            }
        }

        $html = '<span id="bds" bds="' . $bdos . '"/><span id="afes" afes="' . $afes . '"/><div>';
        if (count($datos) > 1)
            $html .= '<legend>Análisis de los documento a tercero a pagar</legend>';
        else
            $html .= '<legend>Análisis del documento a tercero a pagar</legend>';

        $om = $this->DocumentoPagar($datos);
        $html .= '
            <fieldset class="col-md-12 col-xs-12">
                ' . $om->html . '
            </fieldset><br/>
        ';

        $this->mixScript($om->script);

        if ($om->valido) {

            //Obtener fecha ministrado para mostrar como sugerencia
            $query = "
                SELECT MAX(bdm_fechaAutorizado) AS bdm_fechaAutorizado 
                FROM tesoreria.min_BancoDocumentoMinistrado_{$this->appEjercicio}
                WHERE gpo_id = :gpo AND ur_id = :ur AND bdo_id IN( " . $bdos . " ) AND afe_id IN( " . $afes . " ) ";
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($query);
            $conexion->consultar([
                ':gpo' => $gpo_id_documento,
                ':ur' => $ur_id_documento,
            ]);
            $conexion->desconectar();
            $datosInputFecha = $conexion->extraer();

            $bdp_concepto = new stdClass();
            $bdp_concepto->id = 'bdp_concepto';
            $bdp_concepto->placeholder = "Concepto de pago";
            $bdp_concepto->required = true;
            $bdp_concepto->maxlength = 600;
            $bdp_concepto->label = "Concepto de pago";
            $bdp_concepto->value = htmlspecialchars($om->concepto);

            $bdp_referencia = new stdClass();
            $bdp_referencia->id = 'bdp_referencia';
            $bdp_referencia->placeholder = "Número de documento";
            $bdp_referencia->required = true;
            $bdp_referencia->maxlength = 100;
            $bdp_referencia->label = "Número de documento";
            $bdp_referencia->value = $om->bdm_referencia;

            $tdf_id = new stdClass();
            $tdf_id->id = 'tdf_id';
            $tdf_id->placeholder = "Tipo de Documeto";
            $tdf_id->required = true;
            $tdf_id->label = "Tipo de Documeto";
            $tdf_id->sql = "SELECT * FROM cat_TipoDocumentoFuente";
            $tdf_id->comoAttr = "tdf_id";
            $tdf_id->selecciona = $om->tdf_id_ministrado;

            $pot_id = new stdClass();
            $pot_id->id = 'pot_id';
            $pot_id->placeholder = "Tipo de Poliza";
            $pot_id->required = true;
            $pot_id->label = "Tipo de Poliza";
            $pot_id->sql = 'SELECT pot_id,pot_nombre FROM cat_TipoPoliza WHERE pot_id IN("D","E")';
            $pot_id->comoAttr = "pot_id";
            $pot_id->selecciona = $om->pot_id;

            $bdp_polizaSql = '
                SELECT 3 AS bdp_poliza, "NO GENERAR PÓLIZAS"
                UNION ALL
                SELECT 4 AS bdp_poliza, "SOLO PÓLIZA DE GASTO"
            ';

            $bdp_poliza = new stdClass();
            $bdp_poliza->id = 'bdp_poliza';
            $bdp_poliza->placeholder = "Generar Poliza";
            $bdp_poliza->required = true;
            $bdp_poliza->label = "Generar poliza";
            $bdp_poliza->sql = $bdp_polizaSql;
            $bdp_poliza->comoAttr = "bdp_poliza";
            $bdp_poliza->selecciona = $om->bdp_poliza;
            $validarFecha = fecha(
                substr($om->bdm_fechaAutorizado, 0, 4),
                substr($om->bdm_fechaAutorizado, 5, 2),
                substr($om->bdm_fechaAutorizado, 8, 2)
            );
            $bdp_fechaAutorizado = new stdClass();
            $bdp_fechaAutorizado->id = 'bdp_fechaAutorizado';
            $bdp_fechaAutorizado->name = 'control';
            $bdp_fechaAutorizado->class = 'fecha';
            $bdp_fechaAutorizado->label = "Fecha de pago"; #5337329
            $bdp_fechaAutorizado->placeholder = "Fecha";
            $bdp_fechaAutorizado->required = true;
            $bdp_fechaAutorizado->formato = 'fecha';
            $bdp_fechaAutorizado->labelClass = '_32 txtL';
            $bdp_fechaAutorizado->enDiv = true;
            $bdp_fechaAutorizado->value = date(
                'd/m/Y',
                strtotime($datosInputFecha["bdm_fechaAutorizado"])
            );

            $html .= '<fieldset class="col-md-12 col-xs-12"><fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponInput($bdp_concepto);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponInput($bdp_referencia);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponCombo($tdf_id);
            $html .= '</fieldset><br>';

            $html .= '<fieldset class="col-md-6 col-xs-6">';
            $html .= $this->ponCombo($pot_id);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-2 col-xs-2">';
            $html .= $this->ponInput($bdp_fechaAutorizado);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponCombo($bdp_poliza);
            $html .= '</fieldset></fieldset>';

            if ($this->escritura) {
                $btnRegistraPago = new stdClass();
                $btnRegistraPago->id = 'btnRegistraPago';
                $btnRegistraPago->icon = 'send';
                $btnRegistraPago->name = 'nuevo';
                $btnRegistraPago->color = 'verde';
                $btnRegistraPago->text = 'Nuevo';
                $btnRegistraPago->value = 'Registrar Pago';
                $botones .= $this->ponBoton($btnRegistraPago);

                $anio = APP_EJERCICIO;
                $mes = date('m');
                $dia = date('d');

                $this->mixScript('
                    $("#btnRegistraPago").click(function(){
                        $("#btnRegistraPago").attr("disabled", "disabled");
                        if( $("#bdp_concepto").val() == "" ){
                            notify("Es necesario el concepto","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        if( $("#bdp_referencia").val() == "" ){
                            notify("Es necesario un numero de documento","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        if( $("#tdf_id").val() == "" ){
                            notify("Es necesario un tipo de documento","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        if( $("#bdp_fechaAutorizado").val() == "" ){
                            notify("Es necesario un tipo de documento","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        fec = $("#bdp_fechaAutorizado").val().split("/");
                        if( fec[2] != ' . $anio . ' ){
                            notify("El año debe ser ' . $anio . '","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        if( fec[1] > ' . $mes . ' ){
                            notify("El mes  debe ser igual o menor al actual","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }else{
                            if( fec[1] == ' . $mes . ' && fec[0] > ' . $dia . ' ){
                                notify("El dia  debe ser igual o inferior al actual (' . $dia . ')","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                        }
                        if(fec[2] + "-" + fec[1] + "-" + fec[0] < "' . $datosInputFecha["bdm_fechaAutorizado"] . '"){
                            notify("La fecha no puede ser menor a ' . date('d/m/Y',strtotime($datosInputFecha["bdm_fechaAutorizado"])) . '","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                        }
                        if( $("#pot_id").val() == "" ){
                            notify("Seleccione el tipo de póliza a generar","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }
                        if( $("#bdp_poliza").val() == "" ){
                            notify("Seleccione un Valor para la generación de poliza","error");
                            $("#btnRegistraPago").removeAttr("disabled");
                            return false;
                        }

                        comunica("' . $this->pantalla . '",{
                            metodo: "Pagar",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                            mes: ' . $this->prm->mes . ',
                            gur: ' . $this->prm->gur . ',
                            clc_id: "' . $this->prm->clc_id . '",
                            clcFiltro: ' . $this->prm->clcFiltro . ',
                            bdo_id: "' . $this->prm->bdo_id . '",
                            rfc_id: "' . $this->prm->rfc_id . '",
                            bdo_referencia: "' . $this->prm->bdo_referencia . '",
                            bdoFiltro: ' . $this->prm->bdoFiltro . ',
                            data: $("#bds").attr("bds"),
                            afes: $("#afes").attr("afes"),
                            bdp_concepto: $("#bdp_concepto").val(),
                            bdp_referencia: $("#bdp_referencia").val(),
                            tdf_id_pagado: $("#tdf_id").val(),
                            bdp_fechaAutorizado: $("#bdp_fechaAutorizado").val(),
                            pot_id: $("#pot_id").val(),
                            bdp_poliza: $("#bdp_poliza").val(),
                            tmc_id: "A"
                        });

                    });
                ');
            } else {
                if ($_SESSION[_S]['nvj_id'] > 2)
                    $html .= '<div class="alert alert-danger">EL REGISTRO DE PAGO LO REALIZA LA UNIDAD RESPONSABLE</div>';
                else
                    $html .= '<div class="alert alert-danger">NO TIENE PERMISOS PARA GENERAR EL PAGO</div>';
            }
        }

        $html .= '</div>';
        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: "' . $this->prm->bdo_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . '
                });
            });
        ');
    }

    private function DocumentoPagar($prm = NULL)
    {
        if (!$prm) return FALSE;

        $bdos = "";
        $afes = "";
        $i = 0;

        foreach ($prm as $bd) {
            $i++;
            if ($bdos == "") $bdos .= $bd->bdo_id;
            else $bdos .= "," . $bd->bdo_id;

            if ($afes == "") $afes .= $bd->afe_id;
            else {
                $pos = strpos($afes, $bd->afe_id);
                if ($pos === false) $afes .= "," . $bd->afe_id;
            }
        }

        $concepto = "";
        $html = '
            <legend>Registro de pago de los Documentos</legend>
            <table class="table table-bordered"cellpadding="10" style="padding:10px; width:100%; border:1px solid #EAEAEA; box-shadow:1px 2px 3px #999;">
                <tr bgcolor="#EAEAEA">
                    <th colspan="8" style="text-align:center;" class="warning">Datos del Documento</th>
                    <th style="text-align:center;border: 1px solid rgba(45,171,102,0.5);" colspan="5" class="success">Importes Presupuestales/Contables </th>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <th class="warning">UR</th>
                    <th class="warning">clc_id</th>
                    <th class="warning">Afectacion</th>
                    <th class="warning">Referencia</th>
                    <th class="warning">Proveedor</th>
                    <th class="warning">Importe</th>
                    <th class="warning">Retención</th>
                    <th class="warning">Percepción</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Ministrado</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Retenciones</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Percepciones</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Resultado</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Detalle</th>
                </tr>
        ';

        $valido = true;

        $pagado = $this->DocumentoDatos($bdos, $afes, false);

        foreach ($pagado as $data) {
            if ($i > 1) {
                $ag = $i;
            } else {
                $ag = $data->bdo_id;
            }
            if ($this->prm->gur == 118 && $this->gpo_id . $this->ur_id != 118) {
                $tdf_id_ministrado = $data->tdf_id_ministrado;
                $bdm_referencia = $data->bdm_referencia;
                $bdm_fechaAutorizado = $data->bdm_fechaAutorizado;
                $pot_id = 'D';
                $bdp_poliza = 4;
            } else {
                $tdf_id_ministrado = '';
                $bdm_referencia = '';
                $bdm_fechaAutorizado = '';
                $pot_id = '';
                $bdp_poliza = '';
            }
            $concepto = $data->clc_concepto;

            $imp = round($data->bdo_importe, 2);
            $min = round($data->Percepcion, 2);
            $per = round($data->ncc_percepcion, 2);
            $ret = round($data->ncc_retencion, 2);
            $perD = round($data->bdo_percepcionContable, 2);
            $retD = round($data->bdo_retencionContable, 2);

            $html .= '
                <tr>
                    <td>' . $data->gpo_id . $data->ur_id . '</td>
                    <td>' . $data->clc_id . '</td>
                    <td>' . $data->afe_id . '</td>
                    <td>' . $data->bdo_referencia . '</td>
                    <td>' . $data->rfc_id . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                        number_format($data->bdo_importe,2) .
                    '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                            number_format($data->bdo_retencionContable,2) .
                    '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                        number_format($data->bdo_percepcionContable,2) . 
                    '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                        number_format($data->Percepcion,2) .
                    '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                        number_format($data->ncc_retencion,2) .
                    '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' .
                        number_format($data->ncc_percepcion,2) .
                    '</td>
            ';
            if ($imp == $min && $per == $perD && $retD == $ret)
                $html .= '<td class="success">PAGO COMPLETO</td>';
            else $html .= '<td class="error">PAGO PARCIAL</td>';

            $html .= '<td align="center">';
            $html .= '<a href="#" class="DocumentoDetalle" bdo="' . $data->bdo_id . '" afe="' . $data->afe_id . '">Ver</a></td>';
            $html .= '</tr>';
        }
        $html .= '</table>
            <script type="text/javascript" src="js/jma_viewer.js?1" id="scri_pdf" />
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({
                            opacity:0,
                            height:"0px",
                            width:"0px"
                        },400,function(){ $(".documento_fs").remove(); });
                });
            </script>
        ';
        $script = '
            $(".DocumentoDetalle").click(function(){
                bdo = $(this).attr("bdo");
                afe = $(this).attr("afe");
                load_viewer(bdo,"DETALLE DEL USO DE DOCUMENTO","div");
                comunica("' . $this->pantalla . '",{
                    metodo: "DocumentoDetalle",
                    bdo_id: bdo,
                    afe_id: afe,
                    gur: ' . $this->prm->gur . '
                });
            });
        ';

        $r = new stdClass;
        $r->valido = $valido;
        $r->html = $html;
        $r->concepto = $concepto;
        $r->tdf_id_ministrado = $tdf_id_ministrado;
        $r->bdm_referencia = $bdm_referencia;
        $r->bdm_fechaAutorizado = $bdm_fechaAutorizado;
        $r->pot_id = $pot_id;
        $r->bdp_poliza = $bdp_poliza;
        $r->script = $script;

        return $r;
    }

    private function Pagar()
    {
        /* VALIDAR LOS DATOS DE LOS DOCUMENTOS QUE VIENEN  */
        $ta = ''; //TIPO AGRUPADOR  A:AGRUPADO: D: DOCUMENTO
        if (isset($this->prm->data)) {
            if (count($this->prm->data) == 0) {
                $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            } else $ta = count($this->prm->data) > 1 ? "A" : "D";
        } else {
            $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return FALSE;
        }

        $fecha = new stdClass();
        $fecha->gpo_id = $this->gpo_id;
        $fecha->ur_id = $this->ur_id;
        $fecha->fecha = $this->prm->bdp_fechaAutorizado;

        if ($this->prm->tdf_id_pagado == "SPEI") {
            $hoy = new DateTime("now");
            $fecha->fechaMaxima = $hoy->format('d/m/Y');
            unset($hoy);
        } else $fecha->fechaMaxima = "31/12/{$this->appEjercicio}";

        $fecha->fechaMinima = "01/01/{$this->appEjercicio}";

        $res = validaFecha($fecha);

        if ($res->valido) {
            if (!$res->fechaMaxima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            }
            if (!$res->fechaMinima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            }
        } else {
            $this->mixScript('notify("Error 1:' . $res->mensaje . '","error"); '
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return FALSE;
        }

        $this->prm->fechaMysql = $res->fechaMysql;
        $this->prm->mes = $res->mes;

        if ($res->edoMesContable == 'C') {
            $this->mixScript('notify("El mes esta cerrado","error");'
                . '$("#btnRegistraPago").removeAttr("disabled"); ');
            return FALSE;
        }

        $this->CallProcedure();
    }

    private function DocumentoDetalle()
    {
        $this->setContenedor("det_fs" . $this->prm->bdo_id);
        $pagado = $this->DocumentoDatos(
            $this->prm->bdo_id,
            $this->prm->afe_id,
            true
        );
        $trsm = '';
        $trsp = '';
        $paga = new stdClass();
        $paga->pres = 0;
        $paga->perc = 0;
        $paga->retc = 0;
        $mini = new stdClass();
        $mini->pres = 0;
        $mini->perc = 0;
        $mini->retc = 0;

        $top = new stdClass();
        $top->pres = 0;
        $top->perc = 0;
        $top->retc = 0;
        foreach ($pagado as $res) {
            $min = $res->bdm_fechaAutorizado != "" ? 'OK' : '';
            $pag = $res->bdp_fechaAutorizado != "" ? 'OK' : '';
            if ($pag != '') {
                $trsp .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_percepcion,2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion,2) . '</td>
                        <td>' . $min . '</td>
                        <td>' . $res->bdm_fechaAutorizado . '</td>
                        <td>' . $pag . '</td>
                        <td>' . $res->bdp_fechaAutorizado . '</td>
                    </tr>';
                $paga->pres += $res->Percepcion;
                $paga->perc += $res->ncc_percepcion;
                $paga->retc += $res->ncc_retencion;
            } else {
                $trsm .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_percepcion,2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion,2) . '</td>
                        <td>' . $min . '</td>
                        <td>' . $res->bdm_fechaAutorizado . '</td>
                    </tr>';
                $top->pres += $res->Percepcion;
                $top->perc += $res->ncc_percepcion;
                $top->retc += $res->ncc_retencion;
            }
            $mini->pres += $res->Percepcion;
            $mini->perc += $res->ncc_percepcion;
            $mini->retc += $res->ncc_retencion;
        }

        $html = '
            <h4>Informaci&oacute;n del documento</h4>
            <table class="table table-bordered">
                <tr>
                    <td class="warning">ID</td>
                    <td class="warning">RFC</td>
                    <td class="warning">Referencia</td>
                    <td class="warning">Fecha</td>
                    <td class="warning">Concepto</td>
                    <td class="warning">TDF</td>
                    <td class="warning">Percepcion Presupuestal</td>
                    <td class="warning">Percepcion Contable</td>
                    <td class="warning">Retencion Contable</td>
                </tr>
                <tr>
                    <td >' . $pagado[0]->bdo_id . '</td>
                    <td >' . $pagado[0]->rfc_id . '</td>
                    <td >' . $pagado[0]->bdo_referencia . '</td>
                    <td >' . $pagado[0]->bdo_fechaDocumento . '</td>
                    <td >' . $pagado[0]->bdo_concepto . '</td>
                    <td >' . $pagado[0]->tdf_id . '</td>
                    <td align="right">' . number_format($pagado[0]->bdo_importe,2) . ' </td>
                    <td align="right">' . number_format($pagado[0]->bdo_percepcionContable,2) . ' </td>
                    <td align="right">' . number_format($pagado[0]->bdo_retencionContable,2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="">Total Ministrado</td>
                    <td align="right">' . number_format($mini->pres, 2) . ' </td>
                    <td align="right">' . number_format($mini->perc, 2) . ' </td>
                    <td align="right">' . number_format($mini->retc, 2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="">Total Pagado</td>
                    <td align="right">' . number_format($paga->pres, 2) . ' </td>
                    <td align="right">' . number_format($paga->perc, 2) . ' </td>
                    <td align="right">' . number_format($paga->retc, 2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="success">Por Pagar</td>
                    <td align="right" class="success">' . number_format(($mini->pres - $paga->pres), 2) . ' </td>
                    <td align="right" class="success">' . number_format(($mini->perc - $paga->perc), 2) . ' </td>
                    <td align="right" class="success">' . number_format(($mini->retc - $paga->retc), 2) . ' </td>
                </tr>
            </table>
            <h4>Ya pagado</h4>
            <table class="table table-bordered">
                <tr>
                    <td class="warning">UR</td>
                    <td class="warning">CLC</td>
                    <td class="warning">Afectacion</td>
                    <td class="warning">Percepcion Presupuestal</td>
                    <td class="warning">Percepcion Contable</td>
                    <td class="warning">Retencion</td>
                    <td class="warning">Ministrado</td>
                    <td class="warning">Fecha Ministrado</td>
                    <td class="warning">Pagado</td>
                    <td class="warning">Fecha Pagado</td>
                </tr>.' . ($trsp == "" ? '<tr><td colspan="10">Sin registros</td></tr>'
            : $trsp) . '</table>
            <h4>Este Pago</h4>
            <table class="table table-bordered">
                <tr>
                    <td class="warning">UR</td>
                    <td class="warning">CLC</td>
                    <td class="warning">Afectacion</td>
                    <td class="warning">Percepcion Presupuestal</td>
                    <td class="warning">Percepcion Contable</td>
                    <td class="warning">Retencion</td>
                    <td class="warning">Ministrado</td>
                    <td class="warning">Fecha Ministrado</td>
                </tr>.' . $trsm . '
                <tr>
                    <td colspan="3" class="success">Total a Pagar</td>
                    <td align="right" class="success">' . number_format($top->pres,2) . ' </td>
                    <td align="right" class="success">' . number_format($top->perc,2) . ' </td>
                    <td align="right" class="success">' . number_format($top->retc,2) . ' </td>
                    <td align="right" class="success"></td>
                    <td align="right" class="success"></td>
                </tr>
            </table>
        ';
        $this->mixHtml($html);
    }

    private function DocumentoDatos($bdos, $afe, $all = false)
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);

        $res = array();
        $conexion = new AccesoDatos();
        $sqlParams = [
            ':gpo' => $this->gpo_id,
            ':ur' => $this->ur_id,
            ':ogpo' => $gpo_id_documento,
            ':our' => $ur_id_documento,
        ];
        $groupBy = $all ? 'gpo_id, ur_id, clc_id, afe_id ' : 'bdo_id,afe_id';
        $sql =
            "SELECT
                n.gpo_id, n.ur_id, n.clc_id, n.afe_id,
                b.bdo_id, b.bdo_referencia, b.rfc_id, b.bdo_fechaDocumento, b.tdf_id, b.bdo_concepto,
                b.bdo_percepcionPresupuestal AS bdo_importe, b.bdo_retencionContable, b.bdo_percepcionContable,
                (n.Percepcion) AS Percepcion, (n.ncc_retencion) AS ncc_retencion, (n.ncc_percepcion) AS ncc_percepcion,
                n.clc_concepto,
                t.tdf_id AS tdf_id_ministrado, t.bdm_referencia, t.bdm_fechaAutorizado,
                p.bdp_fechaAutorizado
            FROM cat_BancoDocumentos b
                INNER JOIN tesoreria.min_BancoDocumentoMinistrado_{$this->appEjercicio} t USING( gpo_id, ur_id, bdo_id )
                LEFT JOIN (
                    SELECT
                        n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                        SUM(n.ncp_percepcionNeto) AS Percepcion, 0 AS ncc_retencion, 0 AS ncc_percepcion,
                        c.clc_concepto
                    FROM scp_Presupuestal n
                        INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                        LEFT JOIN cg_BancoDocumentoPagadoContable v USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                    WHERE n.gpo_id = :ogpo AND n.ur_id = :our
                        AND    n.bdo_id IN ( $bdos )
                        AND n.afe_id IN ( $afe )
                        AND c.clc_id > 0
                        AND c.ecl_id > 3
                        AND ( v.bdp_estado IS NULL OR v.bdp_estado = 'C' )
                    GROUP BY {$groupBy}
                UNION ALL
                    SELECT
                        n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                        0 AS Percepcion, SUM(n.ncc_retencion) AS ncc_retencion, SUM(n.ncc_percepcion) AS ncc_percepcion,
                        c.clc_concepto
                    FROM scp_Contable n
                        INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                        LEFT JOIN cg_BancoDocumentoPagadoContable v USING( gpo_id, ur_id, clc_id, afe_id, bdo_id,ncc_id )
                    WHERE n.gpo_id = :ogpo AND n.ur_id = :our
                        AND    n.bdo_id IN ( $bdos )
                        AND n.afe_id IN ( $afe )
                        AND c.clc_id > 0
                        AND c.ecl_id > 3
                        AND n.cta_gpo_id = :gpo AND n.cta_ur_id = :ur
                        AND ( v.bdp_estado IS NULL OR v.bdp_estado = 'C' )
                    GROUP BY {$groupBy}
                )n USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN(
                    SELECT
                        gpo_id, ur_id, clc_id, afe_id,
                        bdp_estado, bdp_fechaAutorizado
                    FROM cg_BancoDocumentoPagadoContable
                    WHERE bdo_id IN ( {$bdos} )
                    AND afe_id IN ( {$afe} )
                        AND bdp_estado = 'A'
                )p USING( gpo_id, ur_id, clc_id, afe_id )
            WHERE gpo_id = :ogpo AND ur_id = :our
                AND    bdo_id IN ( $bdos )
                AND bdm_estado = 'A'
                AND afe_id IN ( {$afe} )
                AND ( Percepcion > 0 OR ncc_retencion > 0 OR ncc_percepcion > 0 )
        ";
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();
        while ($datos = $conexion->extraer()) {
            $d = new stdClass();
            foreach ($datos as $name => $valor) {
                $d->$name = $valor;
            }
            array_push($res, $d);
        }
        return $res;
    }

    private function CallProcedure()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);

        switch ($this->prm->tmc_id) {
            case 'A':
                $bdos = '';
                $bdos = $this->prm->data;

                $afes = '';
                $afes = $this->prm->afes;
                $bdoArray = explode(',', $bdos);
                $agrupador = $this->gpo_id . $this->ur_id . $this->prm->mes . 'F';
                if (count(explode(",", $this->prm->data)) > 1) {
                    $agrupador .= "A" . $this->prm->gur . "" . md5($this->prm->data); // AGRUPADOR POR VARIOS DOCUMENTOS
                } else {
                    $agrupador .= "A" . $this->prm->gur . "" . $this->prm->data; // AGRUPADOR PARA UN SOLO DOCUMENTO
                }

                $this->prm->bdp_concepto = strtoupper(htmlspecialchars(str_replace(
                    '"',
                    '\"',
                    $this->prm->bdp_concepto
                )));

                // INICIAMOS LA TRANSACCIÓN
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $this->prm->bdp_referencia = str_replace("'", "\'", $this->prm->bdp_referencia);
                $this->prm->bdp_concepto = str_replace("'", "\'", $this->prm->bdp_concepto);
                $sqlParams = [
                    ':dgpo' => $gpo_id_documento,
                    ':dur' => $ur_id_documento,
                    ':gpo' => $this->gpo_id,
                    ':ur' => $this->ur_id,
                ];
                $referencia = strtoupper($this->prm->bdp_referencia);
                $sql =
                    "INSERT INTO ctrl{$this->appEjercicio}Temporal.tem_BancoDocumentoPagadoContable(
                        gpo_id, ur_id, msc_id, clc_id, afe_id, ncc_id, bdo_id,
                        bdp_fechaAutorizado,
                        bdp_percepcionPresupuestal, bdp_percepcionContable, bdp_retencionContable,
                        bdp_agrupador, tdf_id, bdp_referencia,
                        bdp_concepto,
                        pot_id,bdp_estado,bdp_tipoAgrupador,
                        gpo_id_origen, ur_id_origen,
                        msc_id_origen,
                        clc_id_origen, afe_id_origen, ncc_id_origen, bdo_id_origen,
                        bdp_poliza,
                        usr_id
                    )
                    SELECT
                        t.gpo_id, t.ur_id, {$this->prm->mes}, t.clc_id, t.afe_id, a.ncc_id, t.bdo_id,
                        '{$this->prm->fechaMysql}',
                        a.ncp_percepcionNeto,a.ncc_percepcion,a.ncc_retencion,
                        '{$agrupador}', '{$this->prm->tdf_id_pagado}', '{$referencia}',
                        '{$this->prm->bdp_concepto}',
                        '{$this->prm->pot_id}', 'A','D',
                        gpo_id_origen, ur_id_origen, msc_id_origen,
                        t.clc_id_origen, t.afe_id_origen, ncc_id_origen, t.bdo_id_origen,
                        {$this->prm->bdp_poliza},
                        {$this->usr_id}
                    FROM(
                        SELECT t.gpo_id, t.ur_id, t.clc_id, t.afe_id, t.bdo_id,
                            IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen,
                            IFNULL(o.ur_id, n.ur_id) AS ur_id_origen,
                            IFNULL(o.clc_id, n.clc_id) AS clc_id_origen,
                            IFNULL(o.afe_id, n.afe_id) AS afe_id_origen,
                            IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen,t.bdm_estado 
                        FROM cat_BancoDocumentos b
                        INNER JOIN tesoreria.min_BancoDocumentoMinistrado_{$this->appEjercicio} t USING( gpo_id, ur_id, bdo_id )
                        INNER JOIN scp_Contable n USING(gpo_id, ur_id, clc_id,afe_id,bdo_id)
                        INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                        LEFT JOIN ctrl{$this->appEjercicio}.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
                            WHERE t.gpo_id = :dgpo AND t.ur_id = :dur
                        AND t.bdo_id IN ( $bdos )
                            AND t.afe_id IN ( $afes )
                            AND t.bdm_estado = 'A'
                    )t
                        LEFT JOIN (
                            SELECT
                                n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.ncc_id, n.bdo_id,
                                0 AS ncp_percepcionNeto, n.ncc_percepcion, n.ncc_retencion,
                                COALESCE( n.ncc_gpo, n.cta_gpo_id ) AS gpo_id_origen, COALESCE( n.ncc_ur, n.cta_ur_id ) AS ur_id_origen,
                                MONTH( c.clc_fechaEnvio ) AS msc_id_origen,
                                IFNULL(o.clc_id, n.clc_id) AS clc_id_origen,
                                IFNULL(o.afe_id, n.afe_id) AS afe_id_origen, o.ncc_id AS ncc_id_origen,
                                IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen
                            FROM scp_Contable n
                                INNER JOIN scp_Afectacion a USING( gpo_id, ur_id, clc_id, afe_id )
                                LEFT JOIN cg_BancoDocumentoPagadoContable p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id,ncc_id )
                                LEFT JOIN scp_Contable o ON o.gpo_id = n.ncc_gpo AND o.ur_id = n.ncc_ur AND o.ncc_id = n.ncc_nccAmortiza
                                LEFT JOIN `scp_Clc` c ON c.gpo_id = o.gpo_id AND c.ur_id = o.ur_id AND c.clc_id = o.clc_id
                            WHERE n.gpo_id = :dgpo AND n.ur_id = :dur
                                AND n.bdo_id IN ( $bdos )
                                AND n.afe_id IN ( $afes )
                                AND a.eaf_id = 'A'
                                AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                                AND n.cta_gpo_id = :gpo AND n.cta_ur_id = :ur
                        )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id, gpo_id_origen, ur_id_origen,clc_id_origen, afe_id_origen, bdo_id_origen)
                    WHERE t.gpo_id = :dgpo AND t.ur_id = :dur
                        AND t.bdo_id IN ( $bdos )
                        AND t.afe_id IN ( $afes )
                        AND t.bdm_estado = 'A'
                        AND a.afe_id IS NOT NULL
                        AND a.gpo_id_origen = :gpo AND a.ur_id_origen = :ur
                    ON DUPLICATE KEY UPDATE
                        bdp_fechaAutorizado = '{$this->prm->fechaMysql}',
                        bdp_agrupador = '$agrupador',
                        tdf_id = '{$this->prm->tdf_id_pagado}',
                        bdp_referencia = '{$referencia}',
                        bdp_concepto = '{$this->prm->bdp_concepto}',
                        pot_id = '{$this->prm->pot_id}',
                        bdp_estado = 'A',
                        bdp_poliza = '{$this->prm->bdp_poliza}',
                        usr_id = {$this->usr_id}
                ";
                $conexion->setSql($sql);
                $conexion->bindParams($sqlParams);
                $conexion->consultar();
                $conexion->desconectar();
                $bandera = 0;
                $mensajesp = "ERROR";
                $generaPagado = new sp_AutorizarPagoDocumentoTercero();
                $generaPagado->setGpo_id($this->gpo_id);
                $generaPagado->setUr_id($this->ur_id);
                $generaPagado->setBdp_agrupador($agrupador);
                $generaPagado->setTmc_id("A");
                $generaPagado->setBit_observacion("REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN");
                $generaPagado->setEjercicio(APP_EJERCICIO);
                $generaPagado->setSis_id("SCP");
                $generaPagado->execute(false, false);
                $bandera = $generaPagado->getBandera();
                $mensajesp = $generaPagado->getMensaje();
                $mensaje = "";
                $tipo = "";

                if ($bandera != 1) {
                    $mensaje = "ERROR: " . $mensajesp;
                    $tipo = "error";
                    $conexion->conectar();
                    $conexion->setSql("DELETE
                        FROM ctrl{$this->appEjercicio}Temporal.tem_BancoDocumentoPagadoContable
                        WHERE gpo_id = ? AND ur_id = ?
                            AND bdp_agrupador = ?
                    ");
                    $conexion->consultar([$this->gpo_id, $this->ur_id, $agrupador]);
                    $conexion->desconectar();

                    unset($conexion);
                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                            $("#btnRegistraPago").removeAttr("disabled");
                    ');
                    return FALSE;
                } else {

                    unset($conexion);
                    $mensaje = $mensajesp;
                    $tipo = "success";

                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                        $("#btnRegistraPago").removeAttr("disabled");
                        comunica("' . $this->pantalla . '",{
                            metodo: "Documento",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: ' . $this->prm->mes . ',
                            gur: ' . $this->prm->gur . ',
                            clc_id: "' . $this->prm->clc_id . '",
                            clcFiltro: ' . $this->prm->clcFiltro . ',
                            bdo_id: "' . ($bdoArray[0]) . '",
                            afe_id: "' . ($afes) . '",
                            rfc_id: "",
                            bdo_referencia: "",
                            bdoFiltro: ' . $this->prm->bdoFiltro . ',
                            agrupador: "' . $agrupador . '"
                        });
                    ');
                }
                break;
            case 'C':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "SELECT msc_id, bdp_concepto, bdp_referencia, tdf_id, bdp_fechaAutorizado, pot_id, bdp_poliza, bdo_id
                    FROM cg_BancoDocumentoPagadoContable
                    WHERE gpo_id = ? AND ur_id = ? AND bdp_agrupador = ? ";

                $conexion->setSql($sql);
                $conexion->consultar([$gpo_id_documento, $ur_id_documento, $this->prm->agrupador]);
                $data = $conexion->extraer();
                $afes = '';
                $afes = (isset($this->prm->afes) ? $this->prm->afes : '');
                $res = NULL;
                if (isset($this->prm->fecha) && $this->prm->fecha != '') {
                    $fecha = $this->prm->fecha;
                    $fechaValidar = new stdClass();
                    $fechaValidar->gpo_id = $this->gpo_id;
                    $fechaValidar->ur_id = $this->ur_id;
                    $fechaValidar->fecha = $fecha;
                    $fechaValidar->anio = APP_EJERCICIO;

                    $res = validaFecha($fechaValidar);
                    if (!$res->valido) {
                        $this->mixScript('notify("error:' . $res->mensaje . '","error");');
                        return FALSE;
                    }
                    if (!$res->anio) {
                        $this->mixScript('notify("El año es incorrecto","error");');
                        return FALSE;
                    }
                    if ($res->edoMesContable == 'C') {
                        $this->mixScript('notify("El mes esta cerrado","error");');
                        return FALSE;
                    }
                    $fecha = $res->fechaMysql;
                } else {
                    $dr = new stdClass();
                    $dr->gpo_id = $this->gpo_id;
                    $dr->ur_id = $this->ur_id;
                    $dr->mes = $data['msc_id'];
                    $ems_id = estadoMesContable($dr);
                    if ($ems_id == 'C') {
                        $this->mixScript('notify("el mes esta cerrado","error");');
                        return FALSE;
                    }
                }
                $poliza = $data['bdp_poliza'];

                $bandera = 0;
                $mensajesp = "ERROR";
                $generaPagado = new sp_CancelarPagoDocumentoTercero();
                $generaPagado->setGpo_id($this->gpo_id);
                $generaPagado->setUr_id($this->ur_id);
                $generaPagado->setBdp_agrupador($this->prm->agrupador);

                $generaPagado->setTmc_id('C');
                $generaPagado->setBit_observacion("CANCELACIÓN DEL REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN");
                $generaPagado->setEjercicio(APP_EJERCICIO);
                $generaPagado->setSis_id("SCP");
                $generaPagado->execute(false, false);
                $bandera = $generaPagado->getBandera();
                $mensajesp = $generaPagado->getMensaje();

                $mensaje = "";
                $tipo = "";

                if ($bandera != 1) {
                    $mensaje = "ERROR: " . $mensajesp;
                    $tipo = "error";
                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                    ');
                    return FALSE;
                    unset($conexion);
                } else {
                    unset($conexion);
                    $mensaje = "Se ha cancelado correctamente el registro de Pago";
                    $tipo = "success";
                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                        comunica("' . $this->pantalla . '",{
                            metodo: "Documento",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: ' . $this->prm->mes . ',
                            gur: ' . $this->prm->gur . ',
                            clc_id: "' . $this->prm->clc_id . '",
                            clcFiltro: ' . $this->prm->clcFiltro . ',
                            bdo_id: "' . $this->prm->bdo_id . '",
                            afe_id: "' . ($afes) . '",
                            rfc_id: "",
                            bdo_referencia: "",
                            bdoFiltro: ' . $this->prm->bdoFiltro . ',
                            agrupador: "' . $this->prm->agrupador . '"
                        });
                    ');
                }
                break;
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['13']['1'])) {
    new ControladorPagadoTercero();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
