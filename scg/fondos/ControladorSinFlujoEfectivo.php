<?php
set_time_limit(0);

//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__).'/../../lib/getinfoCLC.php';
require_once dirname(__FILE__).'/../../lib/opcionConteo.php';
require_once dirname(__FILE__).'/../../lib/pintaClc.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaSinFlujoEfectivo.php';
require_once dirname(__FILE__).'/../../lib/scg/vinculacion/vinculacionSinFlujoEfectivo.php';
require_once dirname(__FILE__).'/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__).'/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__).'/../../lib/validaFecha.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoAutorizado.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoCancelado.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereEgresoAutorizado.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereDiarioAutorizado.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCLCDiarioAutorizado.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereDiarioCancelado.php';
require_once dirname(__FILE__).'/../../lib/tes/getTipoMinistracion.php';

class ControladorSinFlujoEfectivo extends WebResponse
{
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $usr_id;
    protected $usr_ip;
    protected $origen;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['16']['2']) ? TRUE
                : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }
        
        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? 
            $_SESSION[_S]['ses_ip'] : 
            'ERROR';

        $this->usr_id = $_SESSION[_S]['usr_id'];


        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
                $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
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
            case 'GeneraPolizaCereco': 
                $this->GeneraPolizaCereco();
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
        $btnLimpiar->value = '&nbsp;&nbsp;Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-info";
        $btnLimpiar->icon = "fa fa-refresh";

        $clcFiltro = FALSE;
        $bdoFiltro = FALSE;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = TRUE;
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

        $inputCve = new StdClass();
        $inputCve->id = 'cveFin';
        $inputCve->name = 'cveFin';
        $inputCve->placeholder = 'Clave Financiamiento';
        $inputCve->enDiv = true;
        $inputCve->class = $vieneDesdeVinculacionClc;
        $inputCve->maxlength = '12';

        $botones = '
            <div class="row mb-4 class="col-md-12 col-xs-12">
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc") ?
               '<fieldset class="col-xs-2" style="padding-right: 1px !important;">' .
                    $this->ponInput($btnRegresa) .
                '</fieldset>' : '') . '
                '.($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 ?
                '<fieldset class="col-md-6 col-xs-6 " id="">' .
                    $this->ponInput($inputGur) .
                '</fieldset>' : '') . '
                <fieldset class="col-md-6 col-xs-6" id="">
                    ' . $this->ponInput($inputClc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6" id="">
                    ' . $this->ponInput($inputCve) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                        <input id="estatal" name="estatal" type="checkbox" value="">  
                        <strong>Recurso Estatal</strong>
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                        <input id="federal" name="federal" type="checkbox" value="">
                        <strong>Recurso Federal</strong>
                </fieldset>

            </div>';


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
                
                $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            $("nav").menusinpres("clickMenu");
                        });
                        '.( ( $_SESSION[_S]['nvj_id'] < 3 ) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                            : '').'
                    ');

                $this->mixScript('
                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            mes: $("#page-tabs li").parent(".active").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            cveFin: $("#cveFin").val(),
                            estatal:  $("#estatal").is(":checked"),
                            federal:  $("#federal").is(":checked")
                        });
                    ');
                break;
            case 'clc':
                $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            $("#btnRegresa").attr("origen", "")
                            $(".navbar").hide();
                            $("#vclcPantallaCompleta").show();
                            e.stopPropagation();
                        });

                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            cveFin: $("#cveFin").val(),
                            estatal:  $("#estatal").is(":checked"),
                            federal:  $("#federal").is(":checked")
                        });
                    ');
                break;
        }

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
	    $("#otraInfoFooter").text("UR ACTUAL : '.$_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'].'" );
                $("#opciones").click( function(e){
                    comunica("'.$this->pantalla.'", {
                        metodo: "Filtro",
                        origen: "'.$this->origen.'",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val()
                    });
                });

                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#cveFin").val("");

                    e.preventDefault();
                    var seleccionado = $(".meses");
                    $("#page-tabs li").each(function (index) { $(this).children("a").removeClass("active"); });
                    seleccionado.closest("li").addClass("active");

                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: seleccionado.attr( "mes" ),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        cveFin: $("#cveFin").val(),
                        estatal:  $("#estatal").is(":checked"),
                        federal:  $("#federal").is(":checked")
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#excelSinFlujoEfectivo").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/fondos/exp_SinFlujoEfectivo.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "16" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.( isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "checkEstatal").attr("value", ($("#estatal").is(":checked")? 1 : 0 ) ))
                        .append($("<input>").attr("type", "hidden").attr("name", "checkFederal").attr("value", ($("#federal").is(":checked")? 1 : 0 ) ))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimirSinFlujoEfectivo").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "16" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.( isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

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

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $html = '<div id="myGridSinFlujoEfectivo" >
                <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                    <table id="tabla-fondos-sinFlujoEfectivo" class="table table-striped table-bordered">
                        <div class="col-md-12 row" style="text-align:center">
                            <thead class="sticky-top" style="z-index:100;">
                                <tr class="warning" role="row">
                                    <th class="warning" role="columnheader" style= "text-align:center">UR</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CLC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AUTORIZACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CONCEPTO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">PERCEPCIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">RETENCIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">ESTADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">PÓLIZA DIARIO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">PÓLIZA EGRESOS</th>
                                </tr>
                            </thead>
                        </div>
                        <tbody></tbody>
                    </table> 
                </div> 
                </div><div id="SinFlujoEfectivoPaginador" style="margin:-.9% 0;"></div>';
        $this->mixHtml($html);

        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaSinFlujoEfectivo > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
                $("#page-tabs a").click( function(e){
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("a").addClass("active");


                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: seleccionado.attr( "mes" ),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        cveFin: $("#cveFin").val(),
                        estatal:  $("#estatal").is(":checked"),
                        federal:  $("#federal").is(":checked")
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
                    dataViewSinFlujoEfectivo.setFilterArgs({ searchString: searchString });
                    dataViewSinFlujoEfectivo.refresh();
                }

                // Cargamos la información a mostrar
                function loadData( json ){
                    $("#tabla-fondos-sinFlujoEfectivo tbody").html("");
                    const tbody = $("#tabla-fondos-sinFlujoEfectivo tbody");

                    dataSinFlujoEfectivo = [];

                    i = 0;
                    for( var key in json ){
                        //
                        const formatoMoneda = new Intl.NumberFormat("es-MX", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        var d = (dataSinFlujoEfectivo[i] = {});
                        id = json[key].id;
                        gur = json[key].gpo_id + json[key].ur_id;
                        clc_id = json[key].clc_id;
                        clc_fechaEnvio = json[key].clc_fechaEnvio;
                        clc_concepto = json[key].clc_concepto;
                        clc_percepcion = (json[key].clc_percepcion == 0) ? "" : formatoMoneda.format(json[key].clc_percepcion);
                        clc_retencion = (json[key].clc_retencion == 0) ? "" : formatoMoneda.format(json[key].clc_retencion);
                        polizaAutorizadog = (json[key].polizaAutorizadog == null) ? "" : json[key].polizaAutorizadog;
                        polizaAutorizadob = (json[key].polizaAutorizadob == null) ? "" : json[key].polizaAutorizadob;
                        agrupadorb = json[key].bdb_agrupador;
                        agrupadorg = json[key].bdg_agrupador;
                        claveFinanciamiento = json[key].ecf_clave;


                        /**********************************************/
                        tbody.append(
                            "<tr class=\'seleccionable\'><td style = \' text-align: center; white-space: nowrap\'>" 
                                + gur + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                + clc_id + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + clc_fechaEnvio +"</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                + clc_concepto + "</td><td style = \' text-align: right; white-space: nowrap\'>" 
                                + clc_percepcion + "</td><td style = \' text-align: right; white-space: nowrap\'>"
                                + clc_retencion + "</td><td style = \' text-align: left; white-space: nowwrap\'>" 
                                + "" + "</td><td style = \' text-align: leftt; white-space: nowrap\'>" 
                                + polizaAutorizadog + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                + polizaAutorizadob + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>"  
                                + agrupadorb + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>" 
                                + agrupadorg + "</td></tr>");


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

                        $("#tabla-fondos-sinFlujoEfectivo tbody tr").hover(
                            function() {
                                $(this).addClass("coloreaSinFlujoEfectivo");
                            },
                            function() {                      
                                $(this).removeClass("coloreaSinFlujoEfectivo"); 
                            }
                        ); 
                    });
                }

                $("#tabla-fondos-sinFlujoEfectivo").on("dblclick", "tbody>tr", function() {
                    // Obtener el índice de la fila (comenzando desde 0)
                    var rowIndex = $(this).index();

                    // Obtener el contenido de la fila
                    var rowData = [];
                    $(this).find("td").each(function() {
                        rowData.push($(this).text());
                    });

                    var gur = rowData[0];
                    var clc_id = rowData[1];
                    var fechaEnvio = rowData[2];
                    var concepto = rowData[3];
                    var percepcion = rowData[4];
                    var retencion = rowData[5];
                    var polizAutorizacionb = rowData[7];
                    var polizAutorizaciong = rowData[8];
                    var agrupadorb = rowData[9];
                    var agrupadorg = rowData[10];

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

                    if ($("#cveFin").val() == "" ) {
                        var_bdoFiltro = 2;
                    }

                    comunica("'.$this->pantalla.'", {
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        clc_id: clc_id,
                        clcFiltro: var_clcFiltro,
                        gur: gur,
                        afe_id: "",
                        bdo_id: "",
                        tdf_id: "",
                        rfc_id: "",
                        bdo_referencia: "",
                        bdoFiltro: var_bdoFiltro,
                        agrupadorb: agrupadorb,
                        agrupadorg: agrupadorg
                    });
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
                loadData('.$data.');
            ');

        return false;
    }

    private function MuestraDatosPagina()
    {
        $data = $this->DameInformacionPaginado();

        $this->mixScript('
            loadData( '.$data.' );
        ');

        return false;
    }

    private function DameInformacion()
    {
        $gur = ( isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id.$this->ur_id );
        $clc_id = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $periodo['mesDesde'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $periodo['mesHasta'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
               
        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = ( isset($this->prm->cveFin)&& $this->prm->cveFin != '' ? $this->prm->cveFin : '' );// busqueda por clave de financiamiento
        $ClaveFinanciamiento['ter_id'] = ( ( $ClaveFinanciamiento['ecf_id'] != '' ) ? '' :
                                            (   (   $this->prm->federal && $this->prm->estatal ) ? 'C' :
                                                    (   $this->prm->federal ? 'B':( $this->prm->estatal ? 'A':'' )
                                                    )
                                            )
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

        $detalle = FALSE;
        $paginado = TRUE;
        $registroInicial = 0;
        $PrimeraCon = TRUE;
        
        $datos = vinculacionSinFlujoEfectivo($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => "", "rfc_id" => "",
            "bdo_referencia" => ""), $paginado, $registroInicial,
            LINEAS_POR_PAGINA, $PrimeraCon);

        $nTotalPages = 0;
        if ($paginado == true) {
            $registro = LINEAS_POR_PAGINA;
            $registro = $datos[0]['gpo_id'];

            $nTotalPages = ceil($registro / LINEAS_POR_PAGINA);

            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado == true) {

            $this->mixScript('
                    $("#SinFlujoEfectivoPaginador").attr("last", 1 );
					var options = {
						numberOfPages: 5,
						currentPage: 1,
						totalPages: '.$nTotalPages.',
						containerClass: "pull-right",
						listContainerClass: "nav nav-tabs",
						itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
						onPageClicked: function(e,originalEvent,type,page){
							// No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
							if ( $("#SinFlujoEfectivoPaginador").attr("last") != page ) {
								 comunica("'.$this->pantalla.'",{
                                                                    metodo: "MuestraDatosPagina",
                                                                    origen: "'.$this->origen.'",
                                                                    seleccion: "'.( isset($this->prm->seleccion)
                        ? $this->prm->seleccion : "" ).'",
                                                                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                                                                    gur: $("#inputGur").val(),
                                                                    clc_id: $("#inputClc_id").val(),
                                                                    rfc_id: $("#inputRfc_id").val(),
                                                                    cveFin: $("#cveFin").val(),
                                                                    estatal:  $("#estatal").is(":checked"),
                                                                    federal:  $("#federal").is(":checked"),
                                                                    bdo_referencia: $("#inputBdo_referencia").val(),
                                                                    paginado: true,
                                                                    numpagina: page ,
                                                                    numreg: '.LINEAS_POR_PAGINA.'


                                                                });
								$("#SinFlujoEfectivoPaginador").attr("last", page );
							}
						}
					}
					$("#SinFlujoEfectivoPaginador").bootstrapPaginator(options);
				');
        } else {
            $this->mixScript('$("#SinFlujoEfectivoPaginador").html("")');
        }
       
        return json_encode($datos);
    }

    private function DameInformacionPaginado()
    {
       
        $gur = ( isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id.$this->ur_id );
        $clc_id = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $periodo['mesDesde'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $periodo['mesHasta'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $paginado = ( isset($this->prm->paginado) ? $this->prm->paginado : false );
        $numpagina = ( isset($this->prm->numpagina) ? $this->prm->numpagina : 1 );
        $numreg = ( isset($this->prm->numreg) ? $this->prm->numreg : 0 );
       

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = ( isset($this->prm->cveFin)&& $this->prm->cveFin != '' ? $this->prm->cveFin : '' );// busqueda por clave de financiamiento
        $ClaveFinanciamiento['ter_id'] = ( ( $ClaveFinanciamiento['ecf_id'] != '' ) ? '' :
                                         ( ( $this->prm->federal && $this->prm->estatal ) ? 'C' : ( $this->prm->federal ? 'B':( $this->prm->estatal ? 'A':'' ) ) ) );

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


        $nInicial = ( ( $numpagina - 1 ) * LINEAS_POR_PAGINA );   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionSinFlujoEfectivo($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => "", "rfc_id" => "",
            "bdo_referencia" => ""), $paginado, $nInicial,
            LINEAS_POR_PAGINA);
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
        $p2 = [$this->gpo_id, $this->ur_id];
        $conexion->consultar($p2);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }
       
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $bdo_id_documento = ( isset($this->prm->bdo_id) ? $this->prm->bdo_id : '' );

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
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-2";
        $btnRegresa->icon = "circle-left me-2";

        $botones = $this->ponBoton($btnRegresa); 
        $this->agregaTools($botones);

        $Clc_detalle = getinfoCLC($gpo_id_documento, $ur_id_documento,
            $clc_id_documento,
            array("ejercicio" => APP_EJERCICIO,
                "mmc_id" => "SIN"
                ));       
        $tipoSinFlujoEfectivo = 0;
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        if(!empty($Clc_detalle->detalleSinFlujoEfectivo)){       
            foreach ($Clc_detalle->detalleSinFlujoEfectivo as $key => $value) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->clc_id] = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }
    
                if ($this->prm->agrupadorb == $value->agrupador) {
                    $tipoSinFlujoEfectivo = $value->tipoMinistracion;
                    if ($value->tipoMinistracion != 2) {
                        $generaPolizaAutorizado = TRUE;
                        $fechaAutorizado = $value->fechaAutorizado;
                    }
                    $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado,
                            2, '0', STR_PAD_LEFT)]['ems_id'];
                    if ($value->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = TRUE;
                            $fechaCancelado = $value->fechaCancelado;
                        }
                        $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado,
                                2, '0', STR_PAD_LEFT)]['ems_id'];
                    }
                }
            }
        }else{            
            $this->mixScript('
                notify("La CLC '.$clc_id_documento.' aún no ha sido afectada contablemente y/o presupuestalmente.", "error");
                $("#btnRegresa").click(function(){
                    comunica("'.$this->pantalla.'", {
                        metodo: "PonBarraBotones",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.( isset($this->prm->tdf_id) ? $this->prm->tdf_id
                        : "" ).'",
                        rfc_id: "'.( isset($this->prm->rfc_id) ? $this->prm->rfc_id
                        : "" ).'",
                        bdo_referencia: "'.( isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia
                        : "" ).'",
                        bdoFiltro: '.$this->prm->bdoFiltro.'
                    });
                });
            ');
             return false;
        }
        if (!isset($estadoMesCancelado) || $estadoMesCancelado == "") {
            $existeFechaCan = FALSE;
        }
        if(!empty($Clc_detalle->detalleSinFlujoEfectivo)){ 
        $html='';
        /* INFORMACIÓN DE LA CLC  */
        
            $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>CLC 
                            <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            
                $clcDocumento = pintaClc($gpo_id_documento, $ur_id_documento,
                    $clc_id_documento,
                    array("ejercicio" => APP_EJERCICIO, "todo" => TRUE));
                $html .= $clcDocumento->html;
            
            $html .= '
                    </fieldset></br>
                ';
        }
        
        if(!empty($Clc_detalle->detalleSinFlujoEfectivo)){

            /* DATOS DE LA MINISTRACIÓN */
           $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Datos de la Ministración
                            <span id="SinFlujoEfectivo" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            $SinFlujoEfectivo = pintaSinFlujoEfectivo($gpo_id_documento, $ur_id_documento,
                $clc_id_documento,
                array("ejercicio" => APP_EJERCICIO, "clc_id" => $clc_id_documento,));
            
            $html .= $SinFlujoEfectivo->html;
            $html .= '
                    </fieldset></br>
                ';
            $tipoSinFlujoEfectivo=5;
            $banderaCuentas = FALSE;
            if ($tipoSinFlujoEfectivo == 5) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql("
                                    SELECT ncc_id
                                    FROM `scp_Clc` c
                                            INNER JOIN `scp_Contable` n USING(gpo_id, ur_id, clc_id)
                                            INNER JOIN (
                                                    SELECT gpo_id, ur_id, clc_id, afe_id, t.bdo_id
                                                    FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_".APP_EJERCICIO." t
                                                    WHERE gpo_id = :gpo AND ur_id = :ur
                                                            AND bdm_agrupador = :bdm
                                                    GROUP BY gpo_id, ur_id, clc_id, afe_id, t.bdo_id
                                            )bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                                    WHERE n.gpo_id = :gpo AND n.ur_id = :ur
                                            AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
                                            OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
                                            OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));");
                $p3 = [':gpo'=>$gpo_id_documento,':ur'=>$ur_id_documento,':bdm'=>$this->prm->agrupadorb];
                $conexion->consultar($p3);

                $dataRow = $conexion->extraer();

                if ($dataRow["ncc_id"]) {
                    $banderaCuentas = TRUE;
                }
                $conexion->desconectar();
            }
            
            if ($banderaCuentas == FALSE && ( $tipoSinFlujoEfectivo == 1 || $tipoSinFlujoEfectivo
                == 4 || $tipoSinFlujoEfectivo == 5 || $tipoSinFlujoEfectivo == 6 || $tipoSinFlujoEfectivo
                == 7 || $tipoSinFlujoEfectivo == 8 || $tipoSinFlujoEfectivo == 9)) {

                /*             * *********************************** Información de la Póliza Contable ************************************************************** */
                $polizas = getPolizaAgrupador($this->gpo_id, $this->ur_id,
                    $this->prm->agrupadorb);
               
                

                $polizaAut = new stdClass;
                $botonGeneraPa = '';

                //preguntamos los datos de la póliza contable de autorización
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                       
                        if ($value->tipoMomento == 'A') {
                            $polizaAutorizado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
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
                                    $btnGenera->text = 'Re-Genera Póliza Ministrado de Autorización';
                                    $btnGenera->value = 'Re-Genera Póliza Ministrado de Autorización';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPa = $this->ponBoton($btnGenera);

                                    $this->mixScript('
                                        $("#btnReGeneraPolizaA").click(function(){
                                            alertify.confirm("RE HACER LA POLIZA '.( isset($value->poliza) ? $value->poliza : '' ).'","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("'.$this->pantalla.'",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "'.$this->origen.'",
                                                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                        mes: '.$this->prm->mes.',
                                                        gur: '.$this->prm->gur.',
                                                        clc_id: "'.$this->prm->clc_id.'",
                                                        clcFiltro: '.$this->prm->clcFiltro.',
                                                        tdf_id: "'.$this->prm->tdf_id.'",
                                                        rfc_id: "'.$this->prm->rfc_id.'",
                                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                                        gpo_id: "'.$value->gpo_id.'",
                                                        ur_id: "'.$value->ur_id.'",
                                                        ue_id: "'.$value->ue_id.'",
                                                        fecha: "'.$fechaAutorizado.'",
                                                        agrupador: "'.$this->prm->agrupadorb.'",
                                                        agrupadorg: "'.$this->prm->agrupadorg.'",
                                                        agrupadorb: "'.$this->prm->agrupadorb.'",
                                                        tmc_id: "A",
                                                        tipoP: "A"
                                                    });
                                                }, 
                                                function(){ }
                                            ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                        });
                                    ');
                                } elseif ($existeFechaCan == TRUE) {
                                    $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización Ministrado esta cerrado</div>';
                                }
                            } elseif ($estadoMesAutorizado == 'A') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza Ministrado</div>';
                            } elseif ($estadoMesAutorizado == 'C') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización Ministrado esta cerrado</div>';
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
                            $polizaCancelado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
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
                                    $btnGenera->text = 'Re-Genera Póliza Ministrado de Cancelación';
                                    $btnGenera->value = 'Re-Genera Póliza Ministrado de Cancelación';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPc = $this->ponBoton($btnGenera);

                                    
                                    $this->mixScript('
                                        $("#btnReGeneraPolizaC").click(function(){
                                            alertify.confirm("RE HACER LA POLIZA '.( isset($value->poliza) ? $value->poliza : '' ).'","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("'.$this->pantalla.'",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "'.$this->origen.'",
                                                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                        mes: '.$this->prm->mes.',
                                                        gur: '.$this->prm->gur.',
                                                        clc_id: "'.$this->prm->clc_id.'",
                                                        clcFiltro: '.$this->prm->clcFiltro.',
                                                        tdf_id: "'.$this->prm->tdf_id.'",
                                                        rfc_id: "'.$this->prm->rfc_id.'",
                                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                                        gpo_id: "'.$value->gpo_id.'",
                                                        ur_id: "'.$value->ur_id.'",
                                                        ue_id: "'.$value->ue_id.'",
                                                        fecha: "'.$fechaCancelado.'",
                                                        agrupador: "'.$this->prm->agrupadorb.'",
                                                        agrupadorb: "'.$this->prm->agrupadorb.'",
                                                        agrupadorg: "'.$this->prm->agrupadorg.'",
                                                        tmc_id: "C",
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
                        $btnGeneraPoliza->text = 'Genera Póliza Ministrado de Autorización';
                        $btnGeneraPoliza->value = 'Genera Póliza Ministrado de Autorización';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);
                        //si el mes contable se encuentra abierto
                        if ($estadoMesAutorizado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function(){
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.( isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        fecha: "'.$fechaAutorizado.'",
                                        agrupador: "'.$this->prm->agrupadorb.'",
                                        agrupadorb: "'.$this->prm->agrupadorb.'",
                                        agrupadorg: "'.$this->prm->agrupadorg.'",
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
                            $btnGeneraPolizaCerrado->icon = 'ok';

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
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes de la SinFlujoEfectivo esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        '.$this->ponInput($inputFecha).'
                                        </center>
                                        <hr>
                                        '.$this->ponBoton($btnGeneraPolizaCerrado).'
                                        '.$this->ponBoton($btnCancelar).'
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
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.( isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupadorb.'",
                                        agrupadorg: "'.$this->prm->agrupadorg.'",
                                        agrupadorb: "'.$this->prm->agrupadorb.'",
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
                    }
                } elseif ($estadoMesAutorizado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza Ministrado</div>';
                } elseif ($estadoMesAutorizado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización Ministrado esta cerrado </div>';
                }

                if ($this->escritura) {
                    if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado
                        && !$polizaCan->valido) {

                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->text = 'Genera Póliza Ministrado de Cancelación';
                        $btnGeneraPoliza->value = 'Genera Póliza Ministrado de Cancelación';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);

                        if ($estadoMesCancelado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function(){
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.( isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupadorb.'",
                                        agrupadorg: "'.$this->prm->agrupadorg.'",
                                        agrupadorb: "'.$this->prm->agrupadorb.'",
                                        fecha: "'.$fechaCancelado.'",
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
                            $btnGeneraPolizaCerrado->icon = 'ok';

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
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes de cancelación de la SinFlujoEfectivo esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        '.$this->ponInput($inputFecha).'
                                        </center>
                                        <hr>
                                        '.$this->ponBoton($btnGeneraPolizaCerrado).'
                                        '.$this->ponBoton($btnCancelar).'
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
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.( isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupadorb.'",
                                        agrupadorb: "'.$this->prm->agrupadorb.'",
                                        agrupadorg: "'.$this->prm->agrupadorg.'",
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
            }//ministracion
        }
        /** INFORMACION CERECO */


        //preguntamos el estado de los meses contables en funcion de las fechas de la cere
        if(!empty($Clc_detalle->detalleCereCo)){
            foreach ($Clc_detalle->detalleCereCo as $key => $valueCereco) {
           
                if ($valueCereco->clc_id != 0) {
                    if ($clc_id_documento != '') {
                        if ($clc_id_documento == $valueCereco->clc_id) {
                            $clc_array[$valueCereco->clc_id] = $valueCereco->clc_id;
                        }
                    } else {
                        $clc_array[$valueCereco->clc_id] = $valueCereco->clc_id;
                    }
                }
    
                if ($this->prm->agrupadorg == $valueCereco->agrupador) {
                    if ($valueCereco->Presupuestal != 0) {
                        $generaPolizaAutorizado = TRUE;
                        $fechaAutorizado = $valueCereco->fechaAutorizado;
                    }
                    $estadoMesAutorizadoCereco = $mesContable[str_pad($valueCereco->mesAutorizado,
                            2, '0', STR_PAD_LEFT)]['ems_id'];
                    if ($valueCereco->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = TRUE;
                            $fechaCancelado = $valueCereco->fechaCancelado;
                        }
                        $estadoMesCanceladoCereco = $mesContable[str_pad($valueCereco->mesCancelado,
                                2, '0', STR_PAD_LEFT)]['ems_id'];
                    }
                }
            }
        }
        if (!isset($estadoMesCanceladoCereco) || $estadoMesCanceladoCereco == "") {
            $existeFechaCan = FALSE;
        }
      

        //         * *********************************** Información de la Póliza Contable ************************************************************** 
        $polizas = getPolizaAgrupador(1, 16, $this->prm->agrupadorg);
       

        $polizaAut = new stdClass;
        $botonGeneraCerecoPa = '';

        //preguntamos los datos de la póliza contable de autorización
        if ($polizas->valido) {
            foreach ($polizas->polizas as $key => $value) {
               
                if ($value->tipoMomento == 'A') {
                    $polizaAutorizadoCereco[$key] = getPoliza($value->gpo_id,
                        $value->ur_id, $value->ue_id, $value->msc_id,
                        $value->pot_id, $value->prf_id, $value->pma_id);
                    $polizaAut->valido = $polizaAutorizadoCereco[$key]->valido;
                }
                
            }
        }
        if (!isset($polizaAut->valido)) {
            $polizaAut->valido = FALSE;
        }
        
       
        //si existe una póliza
        if (isset($polizaAutorizadoCereco)) {
            foreach ($polizaAutorizadoCereco as $key => $value) {
                if ($value->valido) {
                    $polizaAutorizadoCerecoHtml = pintaPoliza($polizaAutorizadoCereco[$key]);
                    $html .= $polizaAutorizadoCerecoHtml->html;
                    //si el mes NO esta cerrado
                    if ($this->escritura) {
                        if ($estadoMesAutorizadoCereco == 'A') {
                            //podra regenerar la póliza contable
                            $btnGeneraCereco = new stdClass();
                            $btnGeneraCereco->id = 'btnReGeneraPolizaCerecoA';
                            $btnGeneraCereco->name = 'btnReGeneraPolizaCerecoA';
                            $btnGeneraCereco->text = 'Re-Genera Póliza CERE-CO de Autorización';
                            $btnGeneraCereco->value = 'Re-Genera Póliza CERE-CO de Autorización';
                            $btnGeneraCereco->color = 'verde';
                            $btnGeneraCereco->icon = 'tasks';
                            $botonGeneraCerecoPa = $this->ponBoton($btnGeneraCereco);

                            $this->mixScript('
                                    $("#btnReGeneraPolizaCerecoA").click(function(){
                                        alertify.confirm("RE HACER LA POLIZA '.( isset($value->poliza) ? $value->poliza : '' ).'","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("'.$this->pantalla.'",{
                                                    metodo: "GeneraPolizaCereco",
                                                    origen: "'.$this->origen.'",
                                                    seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                    mes: '.$this->prm->mes.',
                                                    gur: '.$this->prm->gur.',
                                                    clc_id: "'.$this->prm->clc_id.'",
                                                    clcFiltro: '.$this->prm->clcFiltro.',
                                                    tdf_id: "'.$this->prm->tdf_id.'",
                                                    rfc_id: "'.$this->prm->rfc_id.'",
                                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                                    gpo_id: "'.$value->gpo_id.'",
                                                    ur_id: "'.$value->ur_id.'",
                                                    ue_id: "'.$value->ue_id.'",
                                                    fecha: "'.$fechaAutorizado.'",
                                                    agrupador: "'.$this->prm->agrupadorg.'",
                                                    agrupadorg: "'.$this->prm->agrupadorg.'",
                                                    agrupadorb: "'.$this->prm->agrupadorb.'",
                                                    tmc_id: "A" ,
                                                    tipoP: "A"
                                                });
                                            }, 
                                            function(){ }
                                        ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                    });
                                ');
                        } elseif ($existeFechaCan == TRUE) {
                            $botonGeneraCerecoPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización Cereco esta cerrado</div>';
                        }
                    } elseif ($estadoMesAutorizadoCereco == 'A') {
                        $botonGeneraCerecoPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza Cereco</div>';
                    } elseif ($estadoMesAutorizadoCereco == 'C') {
                        $botonGeneraCerecoPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización Cereco esta cerrado</div>';
                    }
                }
            }
        }

        $polizaCan = new stdClass;
        $botonGeneraCerecoPc = '';

        //preguntamos los datos de la póliza contable de cancelación
        if ($polizas->valido) {
            foreach ($polizas->polizas as $key => $value) {
               
                if ($value->tipoMomento == 'C') {
                    $polizaCancelado[$key] = getPoliza($value->gpo_id,
                        $value->ur_id, $value->ue_id, $value->msc_id,
                        $value->pot_id, $value->prf_id, $value->pma_id);
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
                        if ($estadoMesCanceladoCereco == 'A') {
                            //podra regenerar la póliza contable
                            $btnGeneraCereco = new stdClass();
                            $btnGeneraCereco->id = 'btnReGeneraPolizaCerecoC';
                            $btnGeneraCereco->name = 'btnReGeneraPolizaCerecoC';
                            $btnGeneraCereco->text = 'Re-Genera Póliza CERE-CO de Cancelación';
                            $btnGeneraCereco->value = 'Re-Genera Póliza CERE-CO de Cancelación';
                            $btnGeneraCereco->color = 'verde';
                            $btnGeneraCereco->icon = 'tasks';
                            $botonGeneraCerecoPc = $this->ponBoton($btnGeneraCereco);

                            $this->mixScript('
                                    $("#btnReGeneraPolizaCerecoC").click(function(){
                                        alertify.confirm("RE HACER LA POLIZA '.( isset($value->poliza) ? $value->poliza : '' ).'","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("'.$this->pantalla.'",{
                                                    metodo: "GeneraPolizaCereco",
                                                    origen: "'.$this->origen.'",
                                                    seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                    mes: '.$this->prm->mes.',
                                                    gur: '.$this->prm->gur.',
                                                    clc_id: "'.$this->prm->clc_id.'",
                                                    clcFiltro: '.$this->prm->clcFiltro.',
                                                    tdf_id: "'.$this->prm->tdf_id.'",
                                                    rfc_id: "'.$this->prm->rfc_id.'",
                                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                                    gpo_id: "'.$value->gpo_id.'",
                                                    ur_id: "'.$value->ur_id.'",
                                                    ue_id: "'.$value->ue_id.'",
                                                    fecha: "'.$fechaCancelado.'",
                                                    agrupador: "'.$this->prm->agrupadorg.'",
                                                    agrupadorb: "'.$this->prm->agrupadorb.'",
                                                    agrupadorg: "'.$this->prm->agrupadorg.'",
                                                    tmc_id: "C" ,
                                                    tipoP: "C"
                                                });
                                            }, 
                                            function(){ }
                                        ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                    });
                                ');
                        } else {
                            $botonGeneraCerecoPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                        }
                    } elseif ($estadoMesCanceladoCereco == 'A') {
                        $botonGeneraCerecoPc = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                    } elseif ($estadoMesCanceladoCereco == 'C') {
                        $botonGeneraCerecoPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                    }
                }
            }
        }

        $botones = '';
       
        /**********************************************/

        if(isset($html)){
             $this->mixHtml($html);
        }

        $this->setContenedor('botonA');
        if(isset($botonGeneraPa)){
            $this->mixHtml($botonGeneraPa);
        }
        $this->mixHtml($botonGeneraCerecoPa);
        $this->setContenedor('botonC');
        if(isset($botonGeneraPc)){
            $this->mixHtml($botonGeneraPc);
        }
        $this->mixHtml($botonGeneraCerecoPc);

        $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("'.$this->pantalla.'", {
                        metodo: "PonBarraBotones",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.( isset($this->prm->tdf_id) ? $this->prm->tdf_id
                        : "" ).'",
                        rfc_id: "'.( isset($this->prm->rfc_id) ? $this->prm->rfc_id
                        : "" ).'",
                        bdo_referencia: "'.( isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia
                        : "" ).'",
                        bdoFiltro: '.$this->prm->bdoFiltro.'
                    });
                });
            ');
        
    }

    private function GeneraPoliza()
    {
        
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
       
        $tipoP = ( isset($this->prm->tipoP) ? $this->prm->tipoP : 'A' );
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error:'.$res->mensaje.'","error");');
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
                FROM '.BD_TESORERIA.'.min_BancoDocumentoMinistrado_'.APP_EJERCICIO.' AS bdm
		    INNER JOIN scp_Clc AS c using(gpo_id,ur_id,clc_id)
                WHERE gpo_id = ? AND ur_id = ? and clc_id= ?
                    GROUP BY clc_id LIMIT 1;
            ');
        $p4 = [$gpo_id_documento,$ur_id_documento,$this->prm->clc_id];    
        $this->consultar($p4);
        $this->desconectar();
        $rowCereCo = $this->extraer();

        if ($tipoP == "C") {
            $generaPolizaSinFlujoEfectivo = new sp_CereCoEgresoCancelado();
            $generaPolizaSinFlujoEfectivo->setGpo_id($gpo_id_documento);
            $generaPolizaSinFlujoEfectivo->setUr_id($ur_id_documento);
            $generaPolizaSinFlujoEfectivo->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaSinFlujoEfectivo->setPma_fecha($fecha);
            $generaPolizaSinFlujoEfectivo->setTmc_id($this->prm->tmc_id);
            $generaPolizaSinFlujoEfectivo->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
            $generaPolizaSinFlujoEfectivo->setEjercicio(APP_EJERCICIO);
            $generaPolizaSinFlujoEfectivo->setSis_id("SCG");
            $generaPolizaSinFlujoEfectivo->execute(false, false);
            $bandera = $generaPolizaSinFlujoEfectivo->getBandera();
            $mensajesp = $generaPolizaSinFlujoEfectivo->getMensaje();
        } else {
            if ($rowCereCo["clc_tipoCereCo"] == 1) {
                $generaPolizaSinFlujoEfectivo = new sp_CereEgresoAutorizado();
                $generaPolizaSinFlujoEfectivo->setGpo_id($gpo_id_documento);
                $generaPolizaSinFlujoEfectivo->setUr_id($ur_id_documento);
                $generaPolizaSinFlujoEfectivo->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaSinFlujoEfectivo->setPma_fecha($fecha);
                $generaPolizaSinFlujoEfectivo->setTmc_id($this->prm->tmc_id);
                $generaPolizaSinFlujoEfectivo->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaSinFlujoEfectivo->setEjercicio(APP_EJERCICIO);
                $generaPolizaSinFlujoEfectivo->setSis_id("SCG");
                $generaPolizaSinFlujoEfectivo->execute(false, false);
                $bandera = $generaPolizaSinFlujoEfectivo->getBandera();
                $mensajesp = $generaPolizaSinFlujoEfectivo->getMensaje();
            } else if ($rowCereCo["clc_tipoCereCo"] == 2) {
                $generaPolizaSinFlujoEfectivo = new sp_CereCoEgresoAutorizado();
                $generaPolizaSinFlujoEfectivo->setGpo_id($gpo_id_documento);
                $generaPolizaSinFlujoEfectivo->setUr_id($ur_id_documento);
                $generaPolizaSinFlujoEfectivo->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaSinFlujoEfectivo->setPma_fecha($fecha);
                $generaPolizaSinFlujoEfectivo->setTmc_id($this->prm->tmc_id);
                $generaPolizaSinFlujoEfectivo->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN");
                $generaPolizaSinFlujoEfectivo->setEjercicio(APP_EJERCICIO);
                $generaPolizaSinFlujoEfectivo->setSis_id("SCG");
                $generaPolizaSinFlujoEfectivo->execute(false, false);
                $bandera = $generaPolizaSinFlujoEfectivo->getBandera();
                $mensajesp = $generaPolizaSinFlujoEfectivo->getMensaje();
            }
        }

        $mensaje = "";
        $tipo = "";
        if ($bandera != 1) {
            $mensaje = "ERROR:".$mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }


        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        agrupadorb: "'.$this->prm->agrupadorb.'",
                        agrupadorg: "'.$this->prm->agrupadorg.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "C"
                    });
                ');
        } else {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        agrupadorb: "'.$this->prm->agrupadorb.'",
                        agrupadorg: "'.$this->prm->agrupadorg.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "A"
                    });
                ');
        }
    }
    private function GeneraPolizaCereco()
    {
        
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $tipoP = ( isset($this->prm->tipoP) ? $this->prm->tipoP : 'A' );
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error:'.$res->mensaje.'","error");');
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


            $generaPolizaCere = new sp_CereDiarioCancelado();
            $generaPolizaCere->setGpo_id($gpo_id_documento);
            $generaPolizaCere->setUr_id($ur_id_documento);
            $generaPolizaCere->setBdg_agrupador($this->prm->agrupador);
            $generaPolizaCere->setPma_fecha($fecha);
            $generaPolizaCere->setTmc_id($this->prm->tmc_id);
            $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
            $generaPolizaCere->setEjercicio(APP_EJERCICIO);
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
            $tipoministrado = $tmi->validaTMI($gpo_idc, $ur_idc, $clc_id, APP_EJERCICIO,
                1);

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
            SELECT clc_tipoCereCo
            FROM scp_BancoDocumentoCereCo AS bdg
                INNER JOIN scp_Clc AS c using(gpo_id,ur_id,clc_id)
            WHERE gpo_id= ? AND ur_id = ? AND bdg.bdg_agrupador = ?
            GROUP BY clc_id LIMIT 1;');
            $p5 = [$gpo_idc,$ur_idc,$this->prm->agrupador];
            $conexion->consultar($p5);
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
						WHERE gpo_id = :gpo AND ur_id = :ur
							AND bdg_agrupador = :bdg
						GROUP BY gpo_id, ur_id, clc_id, afe_id, Ce.bdo_id
					)bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
				WHERE n.gpo_id = :gpo AND n.ur_id = :ur
					AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
					OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
					OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));");
                $p6 = [':gpo'=>$gpo_idc,':ur'=>$ur_idc,':bdg'=>$this->prm->agrupador];
                $conexion->consultar($p6);

                $dataRow = $conexion->extraer();

                if ($dataRow["ncc_id"]) {
                    $banderaCuentas = TRUE;
                }
                $conexion->desconectar();
            }



            if ($banderaCuentas ) {


                $generaPolizaCere = new sp_CereCLCDiarioAutorizado();
                $generaPolizaCere->setGpo_id($gpo_id_documento);
                $generaPolizaCere->setUr_id($ur_id_documento);
                $generaPolizaCere->setBdg_agrupador($this->prm->agrupador);
                $generaPolizaCere->setPma_fecha($fecha);
                $generaPolizaCere->setTmc_id($this->prm->tmc_id);
                $generaPolizaCere->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA CERE");
                $generaPolizaCere->setEjercicio(APP_EJERCICIO);
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
                $generaPolizaCere->setEjercicio(APP_EJERCICIO);
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
            $mensaje = "ERROR:".$mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        agrupadorb: "'.$this->prm->agrupadorb.'",
                        agrupadorg: "'.$this->prm->agrupadorg.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "C"
                    });
                ');
        } else {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        agrupadorb: "'.$this->prm->agrupadorb.'",
                        agrupadorg: "'.$this->prm->agrupadorg.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "A"
                    });
                ');
        }
    }

}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['12']['16']['1'])) {
    new ControladorSinFlujoEfectivo();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
?>
