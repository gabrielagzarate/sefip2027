<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 100);
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
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_MinistradoAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_MinistradoCancelado.php';

class ControladorMinistrado extends WebResponse
{
    private $reporteAcumulado;
    private $campoHabilitado;
    private $seleccion;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['5']['2']) ? TRUE : FALSE;
        $this->reporteAcumulado = isset($_SESSION[_S]['prm']['C']['5']['5']['107']) ? TRUE : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }

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
            case 'pintaTablaMinistrado';
                $this->PonBarraBotones();
                $this->setContenedor('contenedor');
                $this->mixHtml($this->getTablaMinistrado());
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                if ($this->origen == 'clc') {
                    $this->main();
                } else {
                    if ($this->gpo_id . $this->ur_id == 801) {
                        $this->pintaBotoneVinculaciones();
                    } else {
                        $this->main();
                        $this->prm->seleccion = "clc";
                    }
                }
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $html = $this->getTablaMinistrado();
        $this->PonBarraBotones();
        $this->setContenedor('contenedor');
        $es801 = ($this->gpo_id . $this->ur_id == 801) ? 801 : 0;
        $this->origen = ((isset($this->prm->origen) && $this->prm->origen != "") ? $this->prm->origen : "");
        $this->seleccion = ((isset($this->prm->seleccion) && $this->prm->seleccion != "") ? $this->prm->seleccion : "");
        $this->mixHtml($html);
    }

    private function pintaBotoneVinculaciones()
    {
        $this->agregaTools('');
        
        $html = '
            <center>
                <div  id="vinculacionClc" style="" class="boton_opcion">
                    <div class="boton_opcion_titulo">VINCULACIÓN <br> POR CLC</div>
                    <div class="price">
                        <div class="center validafoliour">
                            <img src="img/ListadoDelMes.png">
                        </div>
                    </div>
                </div>

                <!--
                <div style="display:table-cell; width:5px;"></div>
                <div id="vinculacionFederal" class="boton_opcion">
                    <div class="boton_opcion_titulo">RECURSO FEDERAL</div>
                    <div class="price">
                        <div class="center validafoliour">
                            <img src="img/VinculacionClcRF.png">
                        </div>
                    </div>
                </div>
                <div style="display:table-cell; width:5px;"></div>
                <div id="vinculacionPlaneacion" class="boton_opcion">
                    <div class="boton_opcion_titulo">VINCULACION<br/>PLANEACION</div>
                    <div class="price">
                        <div class="center validafoliour">
                            <img src="img/VinculacionClcPL.png">
                        </div>
                    </div>
                </div>
                <div style="display:table-cell; width:5px;"></div>
                -->
                ' . ($this->gpo_id . $this->ur_id == 801 ? '
                <div style="display:table-cell;width:5px;"></div>
                <div style="width:5px; display:table-cell"></div>
                <div id="vinculacionPensiones" class="boton_opcion">
                    <div class="boton_opcion_titulo">PENSIONES<br><br></div>
                    <div class="price">
                        <div class="center validafoliour">
                            <img src="img/ListadoDelMes.png">
                        </div>
                    </div>
                </div>
                ' : '') . '

            </center>
        ';

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';

        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $this->agregaTools($this->ponBoton($btnRegresa));
        }
        $this->mixHtml($html);
        $this->mixScript('

            //ES DE LA TABLA tes_BancoDocumentoMinistrado
            $("#vinculacionClc").click(function(){
                console.log( "' . $this->prm->mes . '" );
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaMinistrado",
                    origen: "' . $this->origen . '",
                    mes: "' . $this->prm->mes . '",
                    seleccion: "clc"
                });
            });

            //ES DE LA TABLA tes_MinistradoPlaneacion
            //mip_tipoRecurso = 1
            $("#vinculacionFederal").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tes",
                    origen: "' . $this->origen . '",
                    mes: "' . $this->prm->mes . '",
                    seleccion: "federal"
                });
            });

            $("#vinculacionPlaneacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tes",
                    origen: "' . $this->origen . '",
                    mes: "' . $this->prm->mes . '",
                    seleccion: "planeacion"
                });
            });
            ' . ($this->gpo_id . $this->ur_id == 801 ? '
            $("#vinculacionPensiones").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaMinistrado",
                    origen: "' . $this->origen . '",
                    mes: "' . $this->prm->mes . '",
                    seleccion: "pensiones"
                });
            });
            ' : '') . '
            $("#btnRegresa").click(function(){
                $("nav").menusinpres("clickMenu");
            });
        ');
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
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->icon = 'refresh me-2';
        $btnLimpiar->color = 'cielo';
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
        
                    ' . ($this->gpo_id . $this->ur_id == 801 && isset($this->prm->seleccion)
                    && $this->prm->seleccion == 'pensiones' ? '<fieldset class="col-xs-1" style="padding-right: 1px !important;">
                    <input id="inputGur" placeHolder="UR" type="text" ' . $this->campoHabilitado . ' maxlength=3 class="form-control" value="' . (isset($this->prm->gur)
                    ? $this->prm->gur : "") . '"/>
                    </fieldset>' : '') . '
                    <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                        <input id="inputClc_id" placeHolder="CLC" type="text" ' . $this->campoHabilitado . ' maxlength=6 class="form-control" value="' . ($clcFiltro
                        ? $this->prm->clc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputBdo_id" placeHolder="# Documento" ' . $this->campoHabilitado . ' maxlength=7 class="form-control" value="' . ($bdoFiltro
                        ? $this->prm->bdo_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputRfc_id" placeHolder="RFC" ' . $this->campoHabilitado . ' maxlength=20 class="form-control" value="' . ($bdoFiltro
                        ? $this->prm->rfc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <input id="inputBdo_referencia" placeHolder="Referencia" ' . $this->campoHabilitado . ' class="form-control" value="' . ($bdoFiltro
                        ? $this->prm->bdo_referencia : "") . '"/>
                    </fieldset>
                    <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                        <button id="excelMinistrado" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>Excel</button>
                    </fieldset>
                </div>
                ' . (($this->reporteAcumulado) ? '
                <div class="row">
                    <fieldset class="col-xs-7" style="padding-right: 1px !important;">
                    </fieldset>
                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                        <br>
                        <input id="repacumulado" class="me-1" name="repacumulado" type="checkbox" value=0>Acumulado</input>
                    </fieldset>
                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">                          
                        <input id="repacumuladomes" class="me-1" name="repacumuladomes" type="checkbox" value=0>Acumulado al mes seleccionado</input>  
                    </fieldset>
                </div>
            ' : '');

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
            WHERE gpo_id = ? AND ur_id = ?
                AND msc_id NOT IN ( 0, 13 )
        ');
        $this->consultar([$this->gpo_id, $this->ur_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) $inicio = 'active';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <div></div><a class="nav-link ' . $inicio . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
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
                if ($this->gpo_id . $this->ur_id == 801) {
                    $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            comunica("' . $this->pantalla . '", {
                                metodo: "pintaBotoneVinculaciones"
                            });
                        });
                    ');
                } else {
                    $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            $("nav").menusinpres("clickMenu");
                        });
                    ');
                }

                $this->mixScript('
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

        $this->mixScript((($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
            : '') . '
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


            $("#excelMinistrado").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_Ministrado.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))
                    ' . (($this->reporteAcumulado) ? '
                    .append($("<input>").attr("type", "hidden").attr("name", "repacumuladomes").attr("value", ( $("#repacumuladomes").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "repacumulado").attr("value", ( $("#repacumulado").prop("checked" ) ? 1 : 0 ) ))' : '') . ' 
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T" ))
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

            $("#imprimirMinistrado").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T" ))
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

        $html = $this->getTablaMinistrado();
        $this->mixHtml($html);
        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

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

    private function getTablaMinistrado(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->cabeceraFija = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-ministrado';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'MuestraDatos';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'AFE';
        $th->width = '90px';
        array_push($tr, $th);

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
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA MINISTRADO';
        $th->width = "20%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = "20%";
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
        $td->campoTxt->campos = ['afe_id']; 
        array_push($trbody, $td);

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
        $td->campoTxt->campos = ['tdf_id_ministrado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdm_referencia'];
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['importe'];
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
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones'
            ? TRUE : FALSE);

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
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
            'T',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "pensiones" => $pensiones
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );

        return false;
    }

    private function MuestraDatosPagina($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array(), $paginado = false, $nInicial = 0, $numreg = 0, $ejecsql2 = false)
    {
        $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        $paginadosql = "";
        if ($paginado) {
            $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
        }

        $parametrosSQL = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':mmc' => $mmc_id,
            ':ejercicio' => $ejercicioActual
        ];

        $sqlFiltro = '';
        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = " AND c.gpo_id = :cgpo and c.ur_id = :cur ";
            $parametrosSQL[':cgpo'] = substr($parametroExtra['gur'], 0, 1);
            $parametrosSQL[':cur'] = substr($parametroExtra['gur'],1,2);
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= " AND v.clc_id = :vclc ";
            $parametrosSQL[':vclc'] = $parametroExtra['clc_id'];
        }
        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltro .= " AND v.bdo_id = :vbdo ";
            $parametrosSQL[':vbdo'] = $parametroExtra['bdo_id'];
        }
        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= " AND b.rfc_id = :brfc ";
            $parametrosSQL[':brfc'] = $parametroExtra['rfc_id'];
        }
        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
        }
        if (array_key_exists('soloSinPolizaAutorizacion', $parametroExtra) && $parametroExtra['soloSinPolizaAutorizacion']) {
            $sqlFiltro .= " AND polizaAutorizado IS NULL AND v.tmi_id IN ('1', '4', '5', '6', '7', '8', '9') AND polizaCancelado IS NULL ";
        } elseif (array_key_exists('soloSinPolizaCancelacion', $parametroExtra) && $parametroExtra['soloSinPolizaCancelacion']) {
            $sqlFiltro .= " AND polizaCancelado IS NULL AND polizaAutorizado IS NOT NULL ";
        }

        $pensiones = (isset($parametroExtra['pensiones']) && $parametroExtra['pensiones']) ? " AND v.tmi_id = 7 " : "";
        $var_MAX = " MAX";

        switch ($mmc_id) {
            case 'B':
                $tablaAuxiliar = ''.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $ejercicioActual;
                $letraAuxiliar = 'm';
                $polizaSql = 'CASE ' . $var_MAX . '(tmi_id) WHEN 2 THEN  ' . $var_MAX . '(tmi_nombre) ELSE  ' . $var_MAX . '(pa.polizaAutorizado) END AS polizaAutorizado, MAX(pa.fechaAutorizado)AS fechaAutorizado, ';
                if ($periodo['mesDesde'] != '') {
                    $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id = " . $periodo['mesDesde'] . " " : "");
                    $var_MAX = "";
                }
                break;
            case 'T':
                $tablaAuxiliar = ''.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $ejercicioActual;
                $letraAuxiliar = 'm';
                $polizaSql = 'CASE  ' . $var_MAX . '(tmi_id) WHEN 1 THEN  ' . $var_MAX . '(pa.polizaAutorizado) ELSE  ' . $var_MAX . '(tmi_nombre) END AS polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado, ';
                if (isset($parametroExtra['repacumulado']) && $parametroExtra['repacumulado']) {
                    $periodo['mesDesde'] = '';
                }
                if (isset($parametroExtra['repacumuladomes']) && $parametroExtra['repacumuladomes']) {
                    if ($periodo['mesDesde'] != '') {
                        $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id <= " . $periodo['mesDesde'] . " " : "");
                    }
                } else {
                    if ($periodo['mesDesde'] != '') {
                        $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id = " . $periodo['mesDesde'] . "" : "");
                        $var_MAX = "";
                    }
                }
                break;
            default:
                break;
        }

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                $var_MAX(v.tdf_id) AS tdf_id_ministrado, $var_MAX(v.bdm_referencia) AS bdm_referencia,
                (b.bdo_percepcionPresupuestal + b.bdo_percepcionContable - b.bdo_retencionContable) as importe,
                concat(gpo_id, ur_id) as gur,
                $var_MAX(v.msc_id),
                $var_MAX(v.bd{$letraAuxiliar}_estado) AS estado, 
                DATE( {$var_MAX}(v.bd{$letraAuxiliar}_fechaAutorizado) ) AS fechaAutorizacion, 
                DATE( {$var_MAX}(v.bd{$letraAuxiliar}_fechaCancelado) ) AS fechaCancelacion,
                {$var_MAX}(v.bd{$letraAuxiliar}_agrupador) AS agrupador,
                {$polizaSql}
                MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado,
                MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado,
                MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado,
                MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
                MAX(pa.prf_id_polizaAutorizado ) AS prf_id_polizaAutorizado,
                MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
                MAX(pc.polizaCancelado)as polizaCancelado, 
                MAX(pc.fechaCancelado) as fechaCancelado,
                MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado,
                MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado,
                MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( $var_MAX(v.msc_id), 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ) ) AS id,ecf_clave
            FROM cat_BancoDocumentos b
                INNER JOIN {$tablaAuxiliar} v USING( gpo_id, ur_id, bdo_id )
                INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
                INNER JOIN ".BD_TESORERIA.".cat_TipoMinistracion USING( tmi_id )
                INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ,' ',MIN(m.apg_id)) AS polizaAutorizado,
                        (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                        (x.pdx_afe_id_vinculacion) AS afe_id, (x.pdx_bdo_id_vinculacion) AS bdo_id,
                        (x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = :ejercicio
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,  m.pot_id,m.prf_id, m.pma_id,gpo_id, ur_id, afe_id, bdo_id,agrupador
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                        MAX(m.msc_id) AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id,' ',MIN(m.apg_id) ) AS polizaCancelado,
                        (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                        (x.pdx_afe_id_vinculacion) AS afe_id, (x.pdx_bdo_id_vinculacion) AS bdo_id,
                        (x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = :ejercicio
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,m.pot_id,m.prf_id, m.pma_id,gpo_id, ur_id, afe_id, bdo_id,agrupador
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
            WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
                {$pensiones}
            GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id,pa.polizaAutorizado
            ORDER BY gpo_id, ur_id, clc_id, bdo_id, afe_id,pa.polizaAutorizado";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametrosSQL);
        $this->desconectar();
        $this->setParams($parametrosSQL);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = $this->getNumeroRegistros();
        $nav->idTabla = 'tbl-ministrado';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {

            $this->mixScript('
                $("#tbl-ministrado tr").on("dblclick", function (ev){
                    ev.stopPropagation();
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }
                    const prm = $("#tbl-ministrado").tabla("getFilaSeleccion");
                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        clc_id: prm.clc_id,
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        afe_id: prm.afe_id,
                        bdo_id: prm.bdo_id,
                        tdf_id: prm.tdf_id,
                        rfc_id: prm.rfc_id,
                        bdo_referencia: prm.bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: prm.agrupador,
                    });

                });
            ');

            $this->mixScript('
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();'
            );
        }
        
    }

    private function DameInformacion()
    {
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones'
            ? TRUE : FALSE);

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
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
            'T',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "pensiones" => $pensiones
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );
        $nTotalPages = 0;
        if ($paginado == true) {

            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado == true) {

            $this->mixScript('$("#MinistradoPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#MinistradoPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "MuestraDatosPagina",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                mes: $("#page-tabs li").children(".active").attr("mes"),
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                paginado: true,
                                numpagina: page ,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#MinistradoPaginador").attr("last", page );
                        }
                    }
                }
                $("#MinistradoPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#MinistradoPaginador").html("")');
        }
        return json_encode($datos);
    }

    private function DameInformacionPaginado()
    {
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones'
            ? TRUE : FALSE);

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
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
            'T',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "pensiones" => $pensiones
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
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }


        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $conexion->setSql("SELECT tmi_id
            FROM tesoreria.min_BancoDocumentoMinistrado_" . APP_EJERCICIO . "
            where gpo_id = ? and ur_id = ? and bdo_id = ?
            limit 1");
        $conexion->consultar([$gpo_id_documento, $ur_id_documento, $bdo_id_documento]);
        $dataMinistrado = $conexion->extraer();

        $clc_array = array();

        $generaPolizaAutorizado = FALSE;
        $generaPolizaCancelado = FALSE;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = FALSE;
        $existeFechaCan = TRUE;
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';
        $btnPDF = new stdClass();
        $btnPDF->id = 'btnPDF';
        $btnPDF->name = 'btnPDF';
        $btnPDF->text = 'PDF';
        $btnPDF->value = 'PDF';
        $btnPDF->icon = 'solid fa-file-pdf me-2';
        $btnPDF->color = 'rojo';
        $botones = $this->ponBoton($btnRegresa);
        if ($dataMinistrado['tmi_id'] == 4) {
            $botones .= $this->ponBoton($btnPDF);
        }
        $this->agregaTools($botones);

        $documento = getDocumento(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array("ejercicio" => APP_EJERCICIO, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "T")
        );
        $html = '
            <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del documento a generar la póliza del ministrado</legend>
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
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format(
                                $documento->percepcionPresupuestal,
                                2
                            ) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format(
                                $documento->percepcionContable,
                                2
                            ) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format(
                                $documento->retencionContable,
                                2
                            ) . '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset>
            </div>
        ';
        if ($dataMinistrado['tmi_id'] == 4) {
            $this->mixScript('
                $("#btnPDF").click(function(){
                    var form = $("<form>")
                    .attr("action", "rep/doc/scg/vinculacion/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").attr("value", "' . $gpo_id_documento . '"  ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", "' . $ur_id_documento . '"  ))
                        .append($("<input>").attr("type", "hidden").attr("name", "clc_id").attr("value", "' . $clc_id_documento . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "bdo_id").attr("value", "' . $bdo_id_documento . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "rfc_id").attr("value", "' . $this->prm->rfc_id . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "referencia").attr("value", "' . $this->prm->bdo_referencia . '" ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                });
            ');
        }
        $tipoministrado = 1;
        ////preguntamos la(s) CLC(s) del documento
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
                if ($value->tipoMinistracion == 1) {
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
        }
        if (!isset($estadoMesCancelado) || $estadoMesCancelado == "") {
            $existeFechaCan = FALSE;
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
                    array("ejercicio" => APP_EJERCICIO, "bdo_id" => $bdo_id_documento, "todo" => TRUE)
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
            array("ejercicio" => APP_EJERCICIO, "clc_id" => $clc_id_documento)
        );
        $html .= $ministrado->html;
        $html .= '</fieldset></br>';
        $botonGeneraPa = "";
        $botonGeneraPc = "";
        if ($tipoministrado == 1) {
            /*             * *********************************** Información de la Póliza Contable ************************************************************** */
            $polizas = getPolizaAgrupador(
                $this->gpo_id,
                $this->ur_id,
                $this->prm->agrupador
            );

            $polizaAut = new stdClass;

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
                                            "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
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
                                                    tmc_id: "A",
                                                    tipoP: "A"
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
                            } elseif ($existeFechaCan == TRUE) {
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
                                            "Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?",
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
                                                    tmc_id: "C",
                                                    tipoP: "C"
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
                                    <legend>Mes del Ministrado esta cerrado, capture una nueva fecha</legend>
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
                                    tmc_id: "A",
                                    tipoP: "A"
                                });
                            });

                            $("#btnCancelar").click(function(){
                                $("#bg_capfecha").hide();
                                $("#btnGeneraPoliza").show();
                            });
                        ');
                    }
                } //*/
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
                                    tmc_id: "C",
                                    tipoP: "C"
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
                                    <legend>Mes de cancelación del Ministrado esta cerrado, capture una nueva fecha</legend>
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
                                    tmc_id: "C",
                                    tipoP: "C"
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


            $this->agregaTools($botones);
        } /// tipo de ministrado
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);
        $this->setContenedor('botonC');
        $this->mixHtml($botonGeneraPc);
        if ($this->gpo_id.$this->ur_id == 801) {
            $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("' . $this->pantalla . '", {
                        metodo: "pintaTablaMinistrado",
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
                        bdoFiltro: ' . $this->prm->bdoFiltro . '
                    });
                });
            ');
        } else {
            $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("' . $this->pantalla . '", {
                        metodo: "main",
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
                        bdoFiltro: ' . $this->prm->bdoFiltro . '
                    });
                });
            ');
        }
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $tipoP = (isset($this->prm->tipoP) ? $this->prm->tipoP : 'A');
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $gpo_id_documento;
            $fechaValidar->ur_id = $ur_id_documento;
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


        $bandera = 0;
        $mensajesp = "ERROR";
        if ($tipoP == "C") {
            $generaPolizaMinistrado = new sp_MinistradoCancelado();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL MINISTRADO");
            $generaPolizaMinistrado->setEjercicio(APP_EJERCICIO);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {
            $generaPolizaMinistrado = new sp_MinistradoAutorizado();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL MINISTRADO");
            $generaPolizaMinistrado->setEjercicio(APP_EJERCICIO);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);

            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        }
        $mensaje = "";
        $tipo = "";

        if ($bandera != 1) {
            $mensaje = $mensajesp;
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
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
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
                });
            ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['5']['1'])) {
    new ControladorMinistrado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
