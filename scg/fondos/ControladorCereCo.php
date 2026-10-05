<?php
set_time_limit(0);
DEFINE("LINEAS_POR_PAGINA", 1000);
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacion.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereDiarioAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereCLCDiarioAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/fondos/sp_CereDiarioCancelado.php';
require_once dirname(__FILE__) . '/../../lib/tes/getTipoMinistracion.php';

class ControladorCereCo extends WebResponse
{
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $usr_ip;
    protected $usr_id;
    protected $origen;
    private $ejercicioActual;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['2']['2']) ? true : false;
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
            case 'MuestraDatosPaginacion':
                $this->MuestraDatosPaginacion();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'], $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->PonBarraBotones();
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

        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $this->agregaTools($this->ponBoton($btnRegresa));
        }

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->color = "cielo";
        $btnLimpiar->class = "me-2";
        $btnLimpiar->icon = "refresh me-2";

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

        $botones = '
            <div class="row">
                <div class="col-md">
                    <input id="inputGur" placeHolder="UR" ' . $vieneDesdeVinculacionClc . ' type="text" maxlength=3 class="form-control me-1" value="' . (isset($this->prm->gur) ? $this->prm->gur : "") . '"/>
                    <input id="inputClc_id" placeHolder="CLC" ' . $vieneDesdeVinculacionClc . ' type="text" maxlength=6 class="form-control me-1" value="' . ($clcFiltro ? $this->prm->clc_id : "") . '"/>
                </div>
                <div class="col-md">
                    <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control me-1" value="' . ($bdoFiltro ? $this->prm->bdo_id : "") . '"/>
                </div>
                <div class="col-md">
                    <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control me-1" value="' . ($bdoFiltro ? $this->prm->rfc_id : "") . '"/>
                    <input id="inputBdo_referencia" placeHolder="Referencia" maxlength=20 class="form-control me-1" value="' . ($bdoFiltro ? $this->prm->bdo_referencia : "") . '"/>
                </div>
                <div class="col-md">
                    <label for="chkSoloSinPolizaAutorizacion" class="form-check-label checkbox-label">
                        <input id="chkSoloSinPolizaAutorizacion" type="checkbox" class="form-control form-check-input input-checkbox">
                        <strong>Solo sin Póliza de Autorización</strong>
                    </label>
                    <label for="chkSoloSinPolizaCancelacion" class="form-check-label checkbox-label">
                        <input id="chkSoloSinPolizaCancelacion" type="checkbox" class="form-control form-check-input input-checkbox">
                        <strong>Solo sin Póliza de Cancelación</strong>
                    </label>
                </div>'
            . ($this->escritura ? '<div class="col-md" id="contenedorGeneracionPolizasAutomaticas">
                                        <button class="btn btn-outline-success me-2" id="btnGeneraPolizasAPorUR" name="btnGeneraPolizasAPorUR" value="Genera Pólizas de Autorización" type="button">
                                            <span class="fa fa-check me-2"></span>Genera Pólizas de Autorización
                                        </button>
                                    </div>' : '') .
            '</div>';

        $this->mixStyle('
            .checkbox-label {
                display: flex;
                align-items: center;
            }
        ');

        $this->mixScript('
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
            $("#btnGeneraPolizasAPorUR").click(function() {
                comunica(
                    "' . $this->pantalla . '",
                    {
                        metodo: "GeneraPolizasAutorizacionPorUR",
                        origen: "' . $this->origen . '",
                        gur: $("#inputGur").val(),
                        mes: $("#page-tabs li").children(".active").attr("mes")
                    }
                );
            });
        ');

        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado 
            FROM cat_MesContable 
                INNER JOIN cg_MesContable USING( msc_id) 
            WHERE gpo_id = :gpo AND ur_id = :ur 
                AND msc_id NOT IN ( 0, 13) 
        ');
        $parametro = [':gpo' => $this->gpo_id, ':ur' => $this->ur_id];
        $this->consultar($parametro);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) {
                $inicio = 'class = "active"';
            } else {
                $inicio = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <div></div><a class = "nav-link ' . $inicio . ' meses "  mes = "' . $row['msc_id'] . '" estado = "' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                </li>
                ';

            $contenedor .= '<div id = "contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>';
        }

        $pestana = str_replace('ultimo', 'class = "last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class = "last"></div>', $pestana);

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
                            e.preventDefault();
                            $("nav").menusinpres("clickMenu");
                            e.stopPropagation();
                        });
                        ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                        comunica("' . $this->pantalla . '", { 
                            metodo: "MuestraDatos", 
                            origen: "' . $this->origen . '", 
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: $("#page-tabs li").children("active").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                            soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                            bdo_referencia: $("#inputBdo_referencia").val()
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
                        
                        comunica("' . $this->pantalla . '",{ 
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                            soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        });
                    ');
                break;
        }

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text());
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '");
                $("#opciones").click( function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        gur: $("#inputGur").val(), 
                        clc_id: $("#inputClc_id").val(), 
                        bdo_id: $("#inputBdo_id").val(), 
                        rfc_id: $("#inputRfc_id").val(), 
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });
                
                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val(""); 
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");
                    
                    e.preventDefault();
                    var seleccionado = $(".meses");
                    $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("li").addClass("active");
                    
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        mes: seleccionado.attr( "mes"),
                        origen: "' . $this->origen . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                        soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                    
                    e.stopPropagation();
                    return false;
                });
                
                $("#excelCereCo").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/excel/scg/fondos/exp_CereCo.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "4"))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2"))
                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "G"))
                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes")))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val()))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val()))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val()))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val()))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val()))
                            
                        $("body").append(form);
                        form.submit();
                });
                
                $("#imprimirCereCo").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12"))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2"))
                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "G"))
                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes")))
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
        $html = '<div id="myGridCereCo" >
            <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                <table id="tabla-tbl-cereCo" class="table table-striped table-bordered">
                    <div class="col-md-12 row" style="text-align:center">
                        <thead class="sticky-top">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader">UR</th>
                                <th class="warning" role="columnheader">AFE</th>
                                <th class="warning" role="columnheader">DOC</th>
                                <th class="warning" role="columnheader">TDF</th>
                                <th class="warning" role="columnheader">RFC</th>
                                <th class="warning" role="columnheader">REFERENCIA</th>
                                <th class="warning" role="columnheader">CONCEPTO</th>
                                <th class="warning" role="columnheader">IMPORTE</th>
                                <th class="warning" role="columnheader">ESTADO</th>
                                <th class="warning" role="columnheader">AUTORIZACIÓN</th>
                                <th class="warning" role="columnheader">CANCELACIÓN</th>
                                <th class="warning" role="columnheader">POL AUT.</th>
                                <th class="warning" role="columnheader">FECHA</th>
                                <th class="warning" role="columnheader">POL CAN.</th>
                                <th class="warning" role="columnheader">FECHA</th>
                            </tr>
                        </thead>
                    </div>
                    <tbody></tbody>
                </table>
            </div>
            <div class="tabla-footer">
                <div class="p-2 border flex-wrap">
                    <div class="d-flex flex-row">
                        <div class="flex-grow-1 p-1">
                            Registros del <label id="rA-tbl-cereCo">1</label>
                            al <label id="rB-tbl-cereCo">0</label> 
                            de <label id="rTotal-tbl-cereCo">0</label>
                        </div>
                        <div class="flex-grow-2 justify-content-center p-1">
                            <a class="btn btn-outline-secondary btn-sm first disabled btnNavFPtbl-cereCo">Inicio</a>
                            <a class="btn btn-outline-secondary btn-sm previous disabled btnNavFPtbl-cereCo">Anterior</a>
                            <span id="btnsNavegaciontbl-cereCo" class="btn-group"></span>
                            <a class="btn btn-outline-secondary btn-sm next disabled btnNavNLtbl-cereCo">Siguiente</a>
                            <a class="btn btn-outline-secondary last btn-sm disabled btnNavNLtbl-cereCo">Final</a>
                            </div>
                                <div id="tabla_footer_sizes" class="flex-grow-1">
                                <div class="pull-right">
                                    <div class="d-flex justify-content-end p-1" id="tabla_footer_sizes">
                                        <button class="btn btn-outline-secondary btn-sm">+</button>
                                        <button class="btn btn-outline-secondary btn-sm">-</button>
                                    </div>
                                </div>
                                </div>
                            <div class="flex-grow-1">
                            <form class="pull-right">
                                <div class="d-flex justify-content-end p-1">
                                    <small for="numFilastbl-cereCo" class="me-1">Registros a mostrar:
                                    </small>
                                    <select id="numFilastbl-cereCo" class="btn btn-sm btn-outline-secondary">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="-1">Todos</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>          
        </div>';
        $this->mixHtml($html);
        
        $this->mixScript('
            $("#myGridCereCo").height($(myGridCereCo).height() + 350);
             $("#tabla-tbl-cereCo thead").css("z-index", "100");
            $("#myGridCereCo").on("mouseenter", function(){
                $(this).css("overflow", "initial");
            });

            $("#page-tabs a").click(function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    mes: seleccionado.attr( "mes"),
                    origen: "' . $this->origen . '",
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                    soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });

                e.stopPropagation();
                return false;
            });

            // Cargamos la información a mostrar
            function loadData( json ){
                $("#tabla-tbl-cereCo tbody").html("");
                const tbody = $("#tabla-tbl-cereCo tbody");
                dataCereCo = [];
                i = 0;
                for( var key in json ){
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                      style: "currency",
                      currency: "MXN",
                    });
                    var d = (dataCereCo[i] = {});
                    d["id"] = json[key].id;
                    gur = json[key].gpo_id + json[key].ur_id;
                    afe_id = json[key].afe_id;
                    bdo_id = json[key].bdo_id;
                    tdf_id = json[key].tdf_id;
                    rfc_id = json[key].rfc_id;
                    bdo_referencia = json[key].bdo_referencia;
                    bdo_concepto = json[key].bdo_concepto;
                    importe = (json[key].bdo_percepcionPresupuestal == 0) ? "" : 
                        formatoMoneda.format(json[key].bdo_percepcionPresupuestal).replace("$", "$ ");
                    estado = json[key].estado;
                    fechaAutorizacion = json[key].fechaAutorizacion;
                    fechaCancelacion = json[key].fechaCancelacion ?? "";
                    polizaAutorizado = json[key].polizaAutorizado ?? "";
                    fechaAutorizado = json[key].fechaAutorizado ?? "";
                    polizaCancelado = json[key].polizaCancelado ?? "";
                    fechaCancelado = json[key].fechaCancelado ?? "";
                    agrupador = json[key].agrupador;
                    i++;

                    tbody.append(
                        "<tr class=\'seleccionable\'><td>" 
                        + gur + "</td><td>" 
                        + afe_id + "</td><td>" 
                        + bdo_id + "</td><td>" 
                        + tdf_id + "</td><td>"
                        + rfc_id + "</td><td>" 
                        + bdo_referencia + "</td><td>" 
                        + bdo_concepto + "</td><td>"
                        + importe + "</td><td style = \' text-align: right; white-space: nowrap\'>" 
                        + estado + "</td><td>" 
                        + fechaAutorizacion + "</td><td>" 
                        + fechaCancelacion + "</td><td>" 
                        + polizaAutorizado + "</td><td>" 
                        + fechaAutorizado + "</td><td>" 
                        + polizaCancelado + "</td><td>"
                        + fechaCancelado + "</td><td style = \'display:none;\'>"
                        + agrupador
                        + "</td></tr>");

                    i++;
                }

                $(document).ready(function() {
                    $(".seleccionable").click(function() {
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                    });

                    $(".seleccionable").dblclick(function() {
                        let valores = [];

                        $(this).find("td").each(function() {
                            valores.push($(this).text().trim());
                        });

                        var var_clcFiltro = 1;
                        var var_bdoFiltro = 1;
                        if ($("#inputClc_id").val() == "") {
                            var_clcFiltro = 2;
                        }
                        if ($("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "") {
                            var_bdoFiltro = 2;
                        }

                        gur = valores[0];
                        afe_id = valores[1];
                        bdo_id = valores[2];
                        tdf_id = valores[3];
                        rfc_id = valores[4];
                        bdo_referencia = valores[5];
                        agrupador = valores[15];

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
                    });

                });

                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();
            }
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
            loadData( ' . $data . ' );
        ');
        return false;
    }

    private function MuestraDatosPaginacion()
    {
        $data = $this->DameInformacionPaginacion();

        $this->mixScript('
            loadData( ' . $data . ' );
        ');
        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $soloSinPolizaAutorizacion = (isset($this->prm->soloSinPolizaAutorizacion) ? $this->prm->soloSinPolizaAutorizacion : false);
        $soloSinPolizaCancelacion = (isset($this->prm->soloSinPolizaCancelacion) ? $this->prm->soloSinPolizaCancelacion : false);
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(' ', '', $this->prm->bdo_referencia));
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

        $detalle = false;
        $pagina = true;
        $registroInicial = 0;

        $datos = vinculacion(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'G',
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
            $pagina,
            $registroInicial,
            LINEAS_POR_PAGINA
        ); // el tru

        $nTotalPages = 0;
        if ($pagina == true) {
            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }

        if ($nTotalPages > 1 && $pagina == true) {
            $this->mixScript('
                $("#CerecoPaginador").attr("last", 1);
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function (type, page, current) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e, originalEvent, type, page) {
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#CerecoPaginador").attr("last") != page) {
                            comunica("' . $this->pantalla . '", {
                                metodo: "MuestraDatosPaginacion",
                                mes:$("#page-tabs li").children(".active").attr("mes"),
                                origen: "' . $this->origen . '",
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                soloSinPolizaAutorizacion: $("#chkSoloSinPolizaAutorizacion").is(":checked"),
                                soloSinPolizaCancelacion: $("#chkSoloSinPolizaCancelacion").is(":checked"),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                paginado: true,
                                numpagina: page,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#CerecoPaginador").attr("last", page);
                        }
                    }
                }
                $("#CerecoPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#CerecoPaginador").html("")');
        }
        return json_encode($datos);
    }

    private function DameInformacionPaginacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $soloSinPolizaAutorizacion = (isset($this->prm->soloSinPolizaAutorizacion) ? $this->prm->soloSinPolizaAutorizacion : false);
        $soloSinPolizaCancelacion = (isset($this->prm->soloSinPolizaCancelacion) ? $this->prm->soloSinPolizaCancelacion : false);
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(' ', '', $this->prm->bdo_referencia));
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

        $detalle = false;
        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);
        $datos = vinculacion(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'G',
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
            $numreg
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
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = :gpo AND ur_id = :ur");
        $p = [':gpo' => $this->gpo_id, ':ur' => $this->ur_id];
        $conexion->consultar($p);

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
        $btnRegresa->class = 'me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->icon = 'circle-left me-2';

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
                "mmc_id" => "G"
            )
        );
        $html = '<legend>Análisis del documento a generar la póliza de la cere</legend>';
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
                                    <span style="float:left">$</span>' . number_format($documento->percepcionPresupuestal, 2) . '
                                </td>
                                <td width="10%" align="right">
                                    <span style="float:left">$</span>' . number_format($documento->percepcionContable, 2) . '
                                </td>
                                <td width="10%" align="right">
                                    <span style="float:left">$</span>' . number_format($documento->retencionContable, 2) . '
                                </td>
                                <td>' . $documento->fechaDocumento . '</td>
                            </tr>
                        </table>
                    </fieldset>
            ';

        //preguntamos el estado de los meses contables en funcion de las fechas de la cere
        foreach ($documento->detalleCereCo as $key => $value) {
            if ($value->clc_id != 0) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->clc_id] = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }
            }

            if ($this->prm->agrupador == $value->agrupador) {
                if ($value->Presupuestal != 0) {
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
                <fieldset class = "col-md-12 col-xs-12 ejercido_bloque">
                    <legend>CLC en la que esta el documento
                        <span id = "menclc" style = "font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
            foreach ($clc_array as $clc_id) {
                $clcDocumento = pintaClc(
                    $gpo_id_documento,
                    $ur_id_documento,
                    $clc_id,
                    array(
                        "ejercicio" => $this->ejercicioActual,
                        "bdo_id" => $bdo_id_documento
                    )
                );
                $html .= $clcDocumento->html;
            }
            $html .= '</fieldset>';
        }

        /*         * *********************************** Información de la Póliza Contable ************************************************************** */
        $polizas = getPolizaAgrupador(1, 16, $this->prm->agrupador);
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
                                    alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                        function(){
                                            comunica( "' . $this->pantalla . '", { 
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                                tmc_id: "A" ,
                                                tipoP: "A" 
                                            });
                                        }, 
                                        function(){ }
                                    ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                });
                            ');
                        } elseif ($existeFechaCan == true) {
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
                                $("#btnReGeneraPolizaC").click(function() {
                                    alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                        function(){
                                            comunica( "' . $this->pantalla . '", { 
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                                tmc_id: "C" ,
                                                tipoP: "C"
                                            });
                                        }, 
                                        function(){ }
                                    ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
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
                        $("#btnGeneraPolizaA").click(function() {
                            comunica(
                                "' . $this->pantalla . '",
                                {
                                    metodo: "GeneraPoliza",
                                    origen: "' . $this->origen . '",
                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                    mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                    tmc_id: "A" ,
                                    tipoP: "A"
                                }
                            );
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
                    $btnGeneraPolizaCerrado->class = 'me-2';
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
                    $btnCancelar->class = 'me-2';
                    $btnCancelar->color = 'gris';
                    $btnCancelar->icon = 'ban me-2';

                    $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes de la Cere esta cerrado, capture una nueva fecha</legend>
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
                        $("#btnGeneraPolizaA").click(function() {
                            if ($("#bg_capfecha").length != 0) {
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
                                    mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                    tmc_id: "A" ,
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
            if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado && !$polizaCan->valido) {
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
                                    mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                    tmc_id: "C" ,
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
                    $btnGeneraPolizaCerrado->class = 'me-2';
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
                    $btnCancelar->class = 'me-2';
                    $btnCancelar->color = 'gris';
                    $btnCancelar->icon = 'block me-2';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes de cancelación de la CereCo esta cerrado, capture una nueva fecha</legend>
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
                                    mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
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
                                    tmc_id: "C" ,
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
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);
        $this->setContenedor('botonC');
        $this->mixHtml($botonGeneraPc);

        $this->mixScript('
            $("#btnRegresa").click(function() {
                comunica(
                    "' . $this->pantalla . '", 
                    {
                        metodo: "PonBarraBotones",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
                        gur: ' . $this->prm->gur . ', 
                        clc_id: "' . $this->prm->clc_id . '", 
                        clcFiltro: ' . $this->prm->clcFiltro . ', 
                        bdo_id: ' . $this->prm->bdo_id . ', 
                        tdf_id: "' . $this->prm->tdf_id . '", 
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
                $this->mixScript('notify("error:' . $res->mensaje . '", "error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("El año es incorrecto", "error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("El mes esta cerrado", "error");');
                return false;
            }
            $fecha = $res->fechaMysql;
        } else {
            $this->mixScript('notify("Error en la fecha", "error");');
            return false;
        }

        $bandera = 0;
        $mensajesp = "ERROR";
        if ($tipoP == "C") {
            $generaPolizaCere = new sp_CereDiarioCancelado();
            $generaPolizaCere->setGpo_id($gpo_id_documento);
            $generaPolizaCere->setUr_id($ur_id_documento);
            $generaPolizaCere->setBdg_agrupador($this->prm->agrupador);
            $generaPolizaCere->setPma_fecha($fecha);
            $generaPolizaCere->setTmc_id($this->prm->tmc_id);
            $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
            $generaPolizaCere->setEjercicio($this->ejercicioActual);
            $generaPolizaCere->setSis_id("SCG");
            $generaPolizaCere->execute(false, false);
            $bandera = $generaPolizaCere->getBandera();
            $mensajesp = $generaPolizaCere->getMensaje();
        } else {
            $tmi = new getTipoMinistracion();
            $clc_id = substr(($this->prm->agrupador), 7);
            $gpo_idc = substr(($this->prm->agrupador), 0, 1);
            $ur_idc = substr(($this->prm->agrupador), 1, 2);
            $banderaCuentas = false;
            $tipoministrado = $tmi->validaTMI($gpo_idc, $ur_idc, $clc_id, $this->ejercicioActual, 1);

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SELECT clc_tipoCereCo
                FROM scp_BancoDocumentoCereCo AS bdg
                    INNER JOIN scp_Clc AS c using(gpo_id,ur_id,clc_id)
                WHERE gpo_id = :gpo AND ur_id = :ur AND bdg.bdg_agrupador = :bdg
                GROUP BY clc_id LIMIT 1;
            ');
            $paraPol = [':gpo' => $gpo_idc, ':ur' => $ur_idc, ':bdg' => $this->prm->agrupador];
            $conexion->consultar($paraPol);
            $conexion->desconectar();
            $rowCereCo = $conexion->extraer();

            if ($tipoministrado->tmi_id == 5 && $rowCereCo["clc_tipoCereCo"] == 1) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql("
                    SELECT ncc_id
                    FROM `scp_Clc` c
                        INNER JOIN `scp_Contable` n USING(gpo_id, ur_id, clc_id)
                        INNER JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, Ce.bdo_id
                            FROM `scp_BancoDocumentoCereCo` Ce
                            WHERE gpo_id = :grupo AND ur_id = :urc
                                AND bdg_agrupador = :bdg
                            GROUP BY gpo_id, ur_id, clc_id, afe_id, Ce.bdo_id
                        )bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    WHERE n.gpo_id = :grupo AND n.ur_id = :urc
                        AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));
                ");
                $parAdmi = [':grupo' => $gpo_idc, ':urc' => $ur_idc, ':bdg' => $this->prm->agrupador];
                $conexion->consultar($parAdmi);

                $dataRow = $conexion->extraer();
                if ($dataRow["ncc_id"]) {
                    $banderaCuentas = true;
                }
                $conexion->desconectar();
            }

            if ($banderaCuentas) {
                $generaPolizaCere = new sp_CereCLCDiarioAutorizado();
                $generaPolizaCere->setGpo_id($gpo_id_documento);
                $generaPolizaCere->setUr_id($ur_id_documento);
                $generaPolizaCere->setBdg_agrupador($this->prm->agrupador);
                $generaPolizaCere->setPma_fecha($fecha);
                $generaPolizaCere->setTmc_id($this->prm->tmc_id);
                $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
                $generaPolizaCere->setEjercicio($this->ejercicioActual);
                $generaPolizaCere->setSis_id("SCG");
                $generaPolizaCere->execute(false, false);
                $bandera = $generaPolizaCere->getBandera();
                $mensajesp = $generaPolizaCere->getMensaje();
            } elseif ($tipoministrado->tmi_id != 0) {
                $generaPolizaCere = new sp_CereDiarioAutorizado();
                $generaPolizaCere->setGpo_id($gpo_id_documento);
                $generaPolizaCere->setUr_id($ur_id_documento);
                $generaPolizaCere->setBdg_agrupador($this->prm->agrupador);
                $generaPolizaCere->setPma_fecha($fecha);
                $generaPolizaCere->setTmc_id($this->prm->tmc_id);
                $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
                $generaPolizaCere->setEjercicio($this->ejercicioActual);
                $generaPolizaCere->setSis_id("SCG");
                $generaPolizaCere->execute(false, false);
                $bandera = $generaPolizaCere->getBandera();
                $mensajesp = $generaPolizaCere->getMensaje();
            } else {
                $bandera = 0;
                $mensajesp = $tipoministrado->error;
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
                notify("' . $mensaje . '","' . $tipo . '");
                comunica(
                    "' . $this->pantalla . '",
                    {
                        metodo: "Documento", 
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ',
                        gur: ' . $this->prm->gur . ', 
                        clc_id: "' . $this->prm->clc_id . '", 
                        clcFiltro: ' . $this->prm->clcFiltro . ', 
                        bdo_id: ' . $this->prm->bdo_id . ', 
                        tdf_id: "' . $this->prm->tdf_id . '", 
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
                notify("' . $mensaje . '","' . $tipo . '");
                comunica(
                    "' . $this->pantalla . '",
                    { 
                        metodo: "Documento", 
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' . (isset($this->prm->mes) ? $this->prm->mes : DATE("m")) . ', 
                        gur: ' . $this->prm->gur . ', 
                        clc_id: "' . $this->prm->clc_id . '", 
                        clcFiltro: ' . $this->prm->clcFiltro . ', 
                        bdo_id: ' . $this->prm->bdo_id . ', 
                        tdf_id: "' . $this->prm->tdf_id . '", 
                        rfc_id: "' . $this->prm->rfc_id . '", 
                        bdo_referencia: "' . $this->prm->bdo_referencia . '", 
                        bdoFiltro: ' . $this->prm->bdoFiltro . ', 
                        agrupador: "' . $this->prm->agrupador . '", 
                        fecha: "' . $fecha . '", 
                        tmc_id: "A" 
                    }
                );
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
        $gur = $this->prm->gur;
        //Ahora las validaciones del mes
        if (empty($this->prm->mes) || !is_numeric($this->prm->mes) || $this->prm->mes < 1 || $this->prm->mes > 12) {
            $this->mixScript('notify("ERROR: Debe seleccionar un mes válido.", "error");');
            return false;
        }
        $mes = $this->prm->mes;

        //Vamos a verificar que la UR exista.
        $gpo_id_documento = substr($gur, 0, 1);
        $ur_id_documento = substr($gur, 1, 2);

        $parAut = [
            ':gpo' => $gpo_id_documento,
            ':ur' => $ur_id_documento
        ];

        $this->conectar();
        $this->setSql("
            SELECT gpo_id, ur_id, ur_nombre
            FROM cat_UnidadResponsable
            WHERE gpo_id = :gpo AND ur_id = :ur;
        ");
        $this->consultar($parAut);
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

        $niveles = array(
            9 => 9,
            8 => 0,
            7 => 0,
            6 => 6,
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 1
        );

        $detalle = false;

        $documentosSinPoliza = vinculacion(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'G',
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

        unset($documentosSinPoliza[0]);

        //Si no hay documentos sin póliza de autorización, no hacemos nada.
        if (empty($documentosSinPoliza)) {
            $this->mixScript('notify("No hay documentos sin póliza de autorización en la UR ' . $gur . ' en el mes de ' . $this->nombreMes($mes) . '.", "warning");');
            return false;
        }

        $paraDoctos = [
            ':gpo' => $this->gpo_id,
            ':ur' => $this->ur_id
        ];
        //Ahora vamos a obtener los estados de los meses contables de la UR.
        $mesContable = array();
        $this->conectar();
        $this->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = :gpo AND ur_id = :ur");
        $this->consultar($paraDoctos);

        while ($dataRow = $this->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        //Tengo la información de los documentos sin poliza de autorización, ahora necesito la información detallada con las CERE-CO.
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
                    "mmc_id" => "G"
                )
            );
            $documentos[$key] = $documento;
        }

        //Ahora que ya tengo la lista de documentos con sus CERE-CO, debo revisar si se debe generar Poliza de Autorizado, en caso de ser así, extraer fechas y estados.
        $generaPolizaAutorizado = array();
        $fechaAutorizado = array();
        $estadoMesAutorizado = array();
        foreach ($documentos as $key => $documento) {
            foreach ($documento->detalleCereCo as $cereco) {
                if ($documentosSinPoliza[$key]['agrupador'] == $cereco->agrupador) {
                    if ($cereco->Presupuestal != 0) {
                        $generaPolizaAutorizado[$key] = true;
                        $fechaAutorizado[$key] = $cereco->fechaAutorizado;
                    } else {
                        $this->mixScript('notify("ERROR: No se puede generar la póliza de autorización para el documento ' . $documentosSinPoliza[$key]['agrupador'] . '.", "error");');
                        return false;
                    }
                    $estadoMesAutorizado[$key] = $mesContable[str_pad($cereco->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                }
            }
        }

        //Ahora ya tengo, por cada documento, la banderita que me indica en cada caso si se debe generar la poliza, la fecha de autorizacion/cancelacion y el estado del mes contable de dicha fecha.
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

        $mensajes = array();

        //Hasta aquí ya tenemos la información de los documentos que se van a procesar, bien validada, ahora necesitamos procesarla.
        //Inicia el proceso de generación de pólizas automático para cada documento.
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

            //Ahora obtenemos el tipo de ministrado.
            $bandera = 0;
            $mensajesp = "ERROR";
            $tmi = new getTipoMinistracion();
            $clc_id = substr(($value['agrupador']), 7);
            $gpo_idc = substr(($value['agrupador']), 0, 1);
            $ur_idc = substr(($value['agrupador']), 1, 2);
            $banderaCuentas = false;
            $tipoministrado = $tmi->validaTMI($gpo_idc, $ur_idc, $clc_id, $this->ejercicioActual, 1);

            $paraCereco = [
                ':gpo' => $gpo_idc,
                ':ur' => $ur_idc,
                ':bdg' => $value['agrupador']
            ];
            //Se obtiene el tipo de cereco
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SELECT clc_tipoCereCo
                FROM scp_BancoDocumentoCereCo AS bdg
                    INNER JOIN scp_Clc AS c using(gpo_id,ur_id,clc_id)
                WHERE gpo_id = :gpo AND ur_id = :ur AND bdg.bdg_agrupador = :bdg
                GROUP BY clc_id LIMIT 1;
            ');
            $conexion->consultar($paraCereco);
            $conexion->desconectar();
            $rowCereCo = $conexion->extraer();

            $paraMinis = [
                ':gpo' => $gpo_idc,
                ':ur' => $ur_idc,
                ':bdg' => $value['agrupador']
            ];

            //Si el tipo de ministrado es 5 y el tipo de cereco es 1, se valida que sea por CLC.
            if ($tipoministrado->tmi_id == 5 && $rowCereCo["clc_tipoCereCo"] == 1) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql("
                    SELECT ncc_id
                    FROM `scp_Clc` c
                        INNER JOIN `scp_Contable` n USING(gpo_id, ur_id, clc_id)
                        INNER JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, Ce.bdo_id
                            FROM `scp_BancoDocumentoCereCo` Ce
                            WHERE gpo_id = :gpo AND ur_id = :ur
                                AND bdg_agrupador = :bdg
                            GROUP BY gpo_id, ur_id, clc_id, afe_id, Ce.bdo_id
                        )bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    WHERE n.gpo_id = :gpo AND n.ur_id = :ur
                        AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));
                ");
                $conexion->consultar($paraMinis);

                $dataRow = $conexion->extraer();
                if ($dataRow["ncc_id"]) {
                    $banderaCuentas = true;
                }
                $conexion->desconectar();
            }

            //Se llama al SP adecuado, dependiendo del caso.
            if ($banderaCuentas) {
                $generaPolizaCere = new sp_CereCLCDiarioAutorizado();
                $generaPolizaCere->setGpo_id($gpo_id_documento);
                $generaPolizaCere->setUr_id($ur_id_documento);
                $generaPolizaCere->setBdg_agrupador($value['agrupador']);
                $generaPolizaCere->setPma_fecha($fecha);
                $generaPolizaCere->setTmc_id($value['tmc_id']);
                $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
                $generaPolizaCere->setEjercicio($this->ejercicioActual);
                $generaPolizaCere->setSis_id("SCG");
                $generaPolizaCere->execute(false, false);
                $bandera = $generaPolizaCere->getBandera();
                $mensajesp = $generaPolizaCere->getMensaje();
            } elseif ($tipoministrado->tmi_id != 0) {
                $generaPolizaCere = new sp_CereDiarioAutorizado();
                $generaPolizaCere->setGpo_id($gpo_id_documento);
                $generaPolizaCere->setUr_id($ur_id_documento);
                $generaPolizaCere->setBdg_agrupador($value['agrupador']);
                $generaPolizaCere->setPma_fecha($fecha);
                $generaPolizaCere->setTmc_id($value['tmc_id']);
                $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
                $generaPolizaCere->setEjercicio($this->ejercicioActual);
                $generaPolizaCere->setSis_id("SCG");
                $generaPolizaCere->execute(false, false);
                $bandera = $generaPolizaCere->getBandera();
                $mensajesp = $generaPolizaCere->getMensaje();
            } else {
                $bandera = 0;
                $mensajesp = $tipoministrado->error;
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
                mes: $("#page-tabs li").children("active").attr("mes"),
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
if (isset($_SESSION[_S]['prm']['C']['12']['2']['1'])) {
    new ControladorCereCo();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
