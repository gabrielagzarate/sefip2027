<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPagado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/2023/vinculacionPagado2023.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../lib/estadoMesContable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2023/sp_AutorizarPagoDocumento2023.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2023/sp_PagadoCancelado2023.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2023/sp_PagadoAutorizado2023.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2023/sp_CancelarPagoDocumento2023.php';

class ControladorPagado2023 extends WebResponse
{

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['s24-prm']['C']['17']['15']['1']) ? true : false;
        $this->ejercicioActual = isset($_SESSION['ejercicioActual']) ? $_SESSION['ejercicioActual'] : 2024;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        $sql = "SELECT
                gpo_id_2023 AS gpo_id, ur_id_2023 AS ur_id
                FROM sinpres.vin_UnidadEjecutora_2023_2024
                WHERE gpo_id_2024 = " . $this->gpo_id . " AND ur_id_2024 = " . $this->ur_id . ";
            ";
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar();
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2023 = $data['gpo_id'];
        $this->ur_id_2023 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : 'ERROR';
        $this->usr_id = $_SESSION[_S]['s24-id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion') {
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");
        }

        switch ($this->metodo) {
            case 'DibujaPantalla':$this->DibujaPantalla();
                break;
            case 'MuestraDatos':$this->MuestraDatos();
                break;
            case 'Documento':$this->Documento();
                break;
            case 'GeneraPoliza':$this->GeneraPoliza();
                break;
            case 'PonBarraBotones':$this->PonBarraBotones();
                break;
            case 'PagarDetalle':$this->PagarDetalle();
                break;
            case 'Pagar':$this->Pagar();
                break;
            case 'DocumentoDetalle':$this->DocumentoDetalle();
                break;
            case 'CallProcedure':$this->CallProcedure();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] < 3) {
            $this->mixScript('$("#toggleCA").hide();'); // ocultamos la unidad responsable si el nivel de usuario es menor a 3
        }
        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = '&nbsp;&nbsp;Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-test";

        $btnPagar = new stdClass();
        $btnPagar->id = 'btnPagar';
        $btnPagar->name = 'btnPagar';
        $btnPagar->text = 'Pagar';
        $btnPagar->value = 'Pagar';
        $btnPagar->type = 'button';
        $btnPagar->class = "me-2";
        $btnPagar->color = "gris";

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

        $botones = '
                <div class="row">
                    <fieldset class="col-md-1" style="padding-right: 1px !important;">
                    ' . $this->ponBoton($btnPagar) . '
                    </fieldset>
                    <fieldset class="col-md-1" style="padding-right: 1px !important;">
                        <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control" value="' . ($clcFiltro
            ? $this->prm->clc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->rfc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_referencia" placeHolder="Referencia" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_referencia : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <!-- <button id="excelPagado" class="btn btn-success ladda-button" data-style="slide-left"><i class="glyphicon glyphicon-download-alt"></i></button> -->
                    </fieldset>
                </div>
        ';

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
            WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . '
                AND msc_id NOT IN ( 0, 13 )
        ');
        $this->consultar();
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) {
                $inicio = 'class="active"';
                $active = 'active';
            } else {
                $inicio = '';
                $active = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a class="nav-link ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
            ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
            ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
            <div id="page-tabs">
                <ul class="nav nav-tabs">
                    ' . $pestana . '
                </ul>
            </div>
        ');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
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
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
            case 'clc':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
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
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                $("#opciones").click( function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
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
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#excelPagado").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/vinculacion2017/exp_Pagado2017.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "P" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", ' . $this->gpo_id_2023 . $this->ur_id_2023 . ' ))
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
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "P" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", ' . $this->gpo_id_2023 . $this->ur_id_2023 . ' ))
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
        $html = '<div id="myGridPagado" ></div>';
        $this->mixHtml($html);

        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
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

        $this->mixScript('
                //$("#tituloPantalla").hide();
                // Para que el GRID se ajuste al espacio disponible en pantalla
                $("#myGridPagado").height(
                    $(document).height() -
                    $("header").outerHeight() -
                    $("#sticky").outerHeight() -
                    $("footer").outerHeight() - 120
                );

                var sortcol = "CLAVE";
                var sortdir = 1;
                var searchString = "";

                function myFilter(item, args){
                    if( item["d"] === null || item["c"] === null ) { return false }
                    // Si empieza con numero ... buscamos en la cuenta
                    strNumeros = "0123456789";
                    if( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ){
                        return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                    // si no ... buscamos en el texto
                    }else{
                        if( args.searchString != "" && item["d"].toLowerCase().indexOf(args.searchString) == -1 ){
                            return false;
                        }
                    }
                    return true;
                }

                function comparer(a, b) {
                    var x = a[sortcol], y = b[sortcol];
                    return (x == y ? 0 : (x > y ? 1 : -1));
                }

                $("#buscar").keyup(function (e) {
                    // ESC limpia el campo
                    if (e.which == 27) { this.value = ""; }
                    searchString = this.value.toLowerCase();
                    updateFilter();
                });

                function FormatoConcepto(row, cell, value, columnDef, dataContext) {
                    // Cuando sea cancelada ... también tachamos el texto
                    return ( dataPagado[row].epl == "C" || dataPagado[row].epl == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
                }

                function FormatoMC(row, cell, value, columnDef, dataContext){
                    if( value.substring( 1 ) == "A" ){
                        return value.substring( 0, 1 );
                    }else{
                        return "<span title=\"La póliza corresponde a la cancelación de la ministración que fué autorizada en diferente mes\">" + value.substring( 0, 1 ) + "<span style=\"color:red\">" + value.substring( 1 ) + "</span></span>";
                    }
                }

                function updateFilter(){
                    dataViewPagado.setFilterArgs({ searchString: searchString });
                    dataViewPagado.refresh();
                }

                function FormatoMoneda(row, cell, value, columnDef, dataContext){
                    // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
                    strIni = ""
                    strFin = ""

                    if( value === null || value === "" || !(value != 0) ){
                        return "";
                    } else {
                        var n = value,

                        c = 2,
                        d = ".",
                        t = ","
                        s = n < 0 ? "-" : "",
                        i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                        j = (j = i.length) > 3 ? j % 3 : 0;

                        return strIni + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + strFin;

                        /*return "$" + Number(value).toFixed(2);*/
                    }
                }

                // Cargamos la información a mostrar
                function loadData( json ){
                    dataPagado = [];

                    i = 0;
                    for( var key in json ){
                        //console.log( key + "->" + json[key]["id"] );
                        var d = (dataPagado[i] = {});
                        d["id"] = json[key].id;
                        d["gur"] = json[key].gpo_id + json[key].ur_id;
                        d["afe_id"] = json[key].afe_id;
                        d["bdo_id"] = json[key].bdo_id;
                        d["tdf_id"] = json[key].tdf_id;
                        d["rfc_id"] = json[key].rfc_id;
                        d["bdo_referencia"] = json[key].bdo_referencia;
                        d["bdo_concepto"] = json[key].bdo_concepto;
                        d["tdf_id_pagado"] = json[key].tdf_id_pagado;
                        d["bdp_referencia"] = json[key].bdp_referencia;
                        d["importe"] = json[key].bdo_importe;
                        d["estado"] = json[key].estado;
                        d["fechaAutorizacion"] = json[key].fechaAutorizacion;
                        d["fechaCancelacion"] = json[key].fechaCancelacion;
                        d["polizaAutorizado"] = json[key].polizaAutorizado;
                        d["fechaAutorizado"] = json[key].fechaAutorizado;
                        d["polizaCancelado"] = json[key].polizaCancelado;
                        d["fechaCancelado"] = json[key].fechaCancelado;
                        d["agrupador"] = json[key].agrupador;
                        d["stringafec"] = json[key].stringafec;
                        i++;
                    }

                    //console.table( dataPagado );

                    dataViewPagado.setItems(dataPagado);

                }

                var dataViewPagado;
                var dataGridPagado;
                var columns = [
                    //{id: "gur", name: "UR", field: "gur", minWidth: 25, sortable: true },
                    //{id: "afe", name: "AFE", field: "afe_id", minWidth: 40, maxWidth: 60, sortable: true },
                    {id: "bdo", name: "DOC", field: "bdo_id", minWidth: 60, maxWidth: 60, sortable: true },
                    {id: "tdf", name: "TDF", field: "tdf_id", sortable: true },
                    {id: "rfc", name: "RFC", field: "rfc_id", minWidth: 100, maxWidth: 120, sortable: true },
                    {id: "referencia", name: "REFERENCIA", field: "bdo_referencia", minWidth: 80, sortable: true },
                    {id: "concepto", name: "CONCEPTO", field: "bdo_concepto", sortable: true },
                    {id: "tdf_pagado", name: "TDF", field: "tdf_id_pagado", minWidth: 40, sortable: true },
                    {id: "referenciaPagado", name: "REF.PAGADO", field: "bdp_referencia", minWidth: 80, maxWidth: 80, sortable: true },
                    {id: "importe", name: "IMPORTE", field: "importe", minWidth: 80, maxWidth: 100, sortable: true, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda },
                    {id: "estado", name: "ESTADO", field: "estado", minWidth: 40, maxWidth: 40, sortable: true },
                    {id: "fechaAutorizacion", name: "AUTORIZACIÓN", field: "fechaAutorizacion", minWidth: 70, maxWidth: 90, sortable: true },
                    {id: "fechaCancelacion", name: "CANCELACIÓN", field: "fechaCancelacion", minWidth: 70, maxWidth: 90, sortable: true },
                    {id: "polizaAutorizado", name: "POL AUT.", field: "polizaAutorizado", minWidth: 70, maxWidth: 70, sortable: true },
                    {id: "fechaAutorizado", name: "FECHA", field: "fechaAutorizado", minWidth: 70, maxWidth: 80, sortable: true },
                    {id: "polizaCancelado", name: "POL CAN.", field: "polizaCancelado", minWidth: 70, maxWidth: 70, sortable: true },
                    {id: "fechaCancelado", name: "FECHA", field: "fechaCancelado", minWidth: 70, maxWidth: 70, sortable: true }
                ];

                var checkboxSelector = new Slick.CheckboxSelectColumn({
                    cssClass: "slick-cell-checkboxsel, alineaCentro"
                });
                columnDefinition = checkboxSelector.getColumnDefinition();
                columnDefinition.headerCssClass = "alineaCentro checkboxPagado";
                columns.unshift(columnDefinition);

                var options = {
                    enableCellNavigation: true,
                    enableColumnReorder: false,
                    forceFitColumns: true
                    /*autoHeight:true
                    esto y la funcion $("#myGridPagado").height() arriba controlan si el grid se limita o no a la pantalla
                    */
                };

                /*dataViewPagado = new Slick.Data.DataView( { inlineFilters: false });
                dataGridPagado = new Slick.Grid("#myGridPagado", dataViewPagado, columns, options);
                dataGridPagado.setSelectionModel(new Slick.RowSelectionModel());*/

                dataViewPagado = new Slick.Data.DataView( { inlineFilters: false });
                dataGridPagado = new Slick.Grid("#myGridPagado", dataViewPagado, columns, options);
                dataGridPagado.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: true}));
                dataGridPagado.registerPlugin(checkboxSelector);

                dataViewPagado.onRowCountChanged.subscribe(function( e, args ){
                    dataGridPagado.updateRowCount();
                    dataGridPagado.render();
                });

                dataViewPagado.onRowsChanged.subscribe(function( e, args ){
                    //dataGridPagado.invalidateRows(args.rows);
                    dataGridPagado.render();
                });

                dataGridPagado.onSelectedRowsChanged.subscribe(function() {
                    if( $("#inputClc_id").val() || $("#inputBdo_id").val() || $("#inputRfc_id").val() || $("#inputBdo_referencia").val()  ){
                        $(".checkboxPagado .slick-column-name").html("<input type=\"checkbox\">");
                    } else{
                        $(".checkboxPagado .slick-column-name").html("");
                    }
                });

                dataGridPagado.onSort.subscribe(function( e, args ){
                    sortdir = args.sortAsc ? 1 : -1;
                    sortcol = args.sortCol.field;
                    dataViewPagado.sort(comparer, args.sortAsc);
                });

                // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
                $("#myModalPagado").on("hide.bs.modal", function (){
                    $("#declaraRangoPagado").remove();
                });

                dataGridPagado.onDblClick.subscribe(function(e){
                    // Obtenemos el array de los datos de la linea actual
                    row = dataGridPagado.getDataItem(dataGridPagado.getActiveCell().row);

                    // Si no hay nada que selecionar, no hacemos nada
                    if( row == "" ){
                        e.stopPropagation();
                        return false;
                    }

                    row = dataGridPagado.getDataItem(dataGridPagado.getActiveCell().row);
                    console.log( row.gur + "-" + row.afe_id + "-" + row.bdo_id + "-" + row.agrupador );
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }

                    if( row.estado != "A" ){
                        notify("Debe realizar el registro de pago, para ver el detalle", "warning" );
                    }else{
                        comunica("' . $this->pantalla . '", {
                            metodo: "Documento",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            clc_id: $("#inputClc_id").val(),
                            clcFiltro: var_clcFiltro,
                            gur: row.gur,
                            afe_id: row.afe_id,
                            bdo_id: row.bdo_id,
                            tdf_id: row.tdf_id,
                            rfc_id: row.rfc_id,
                            bdo_referencia: row.bdo_referencia,
                            bdoFiltro: var_bdoFiltro,
                            agrupador: row.agrupador
                        });
                    }

                    e.stopPropagation();
                });

                $("#btnPagar").click(function(e){
                    // Obtenemos el array de los datos de la linea actual
                    //row = dataGridPagado.getDataItem(dataGridPagado.getActiveCell().row);
                    var selected = dataGridPagado.getSelectedRows();
                    var rows =  new Array();
                    var pagadosnum=0;
                    var gur = "";
                    dataGridPagado.setSelectedRows([]);

                    $.each(selected, function (index, value) {
                        var fila = dataGridPagado.getDataItem(value);
                            actual = {};
                            actual.afe_id = fila.stringafec;
                            actual.bdo_id = fila.bdo_id;
                        rows.push(actual);
                        gur = fila.gur;
                        if(fila.estado == "A")
                            pagadosnum++;

                    })

                    //row=rows;
                    // Si no hay nada que selecionar, no hacemos nada

                    if( selected == "" ){
                        e.stopPropagation();
                        return false;
                    }

                    //row = dataGridPagado.getDataItem(dataGridPagado.getActiveCell().row);
                    //console.log( row.gur + "-" + row.afe_id + "-" + row.bdo_id );
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }
                    //console.log(rows);
                    if( pagadosnum > 0 ){
                        notify("Uno o mas documentos ya se encuentran pagados", "warning" );
                    }else{
                        //if(seleccionados = $("#tbl_Conceptos").tabla("getFilaSeleccion") ){
                            comunica("' . $this->pantalla . '",{
                                metodo: "PagarDetalle",
                                data: rows,
                                //np: $("#tbl_Conceptos a.active").text(),
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                                clc_id: $("#inputClc_id").val(),
                                clcFiltro: var_clcFiltro,
                                gur: gur,
                                //afe_id: row.afe_id,
                                bdo_id: $("#inputBdo_id").val(),
                                //tdf_id: row.tdf_id,
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                bdoFiltro: var_bdoFiltro,
                                //agrupador: row.agrupador,
                                tmc_id: "A"
                                },
                                true,
                                true
                            );
                        //}
                    }
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
        $this->mixStyle('
            #myGridPagado, #myGridPagado div {
                -webkit-box-sizing: content-box;
                -moz-box-sizing: content-box;
                box-sizing: content-box;
            }
        ');
    }

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();
        $this->mixScript('
                dataViewPagado.beginUpdate();
                dataViewPagado.getItems().length = 0
                dataGridPagado.invalidateAllRows();
                loadData( ' . $data . ' );
                dataViewPagado.setFilterArgs({ searchString: searchString });
                dataViewPagado.setFilter(myFilter);
                dataViewPagado.endUpdate();
                dataGridPagado.render();
        ');

        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id_2023 . $this->ur_id_2023);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = (isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia : '');
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        if ($clc_id || $bdo_id || $rfc_id || $bdo_referencia) {
            $this->mixScript('
                $(".checkboxPagado .slick-column-name").html("<input type=\"checkbox\">");
            ');
        } else {
            $this->mixScript('
                $(".checkboxPagado .slick-column-name").html("");
            ');
        }

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

        $datos = vinculacionPagado2023($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'P', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia));

        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(" SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = $this->gpo_id AND ur_id = $this->ur_id ");
        $conexion->consultar();

        $mesContableAbierto = false;
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

        $generaPolizaAutorizado = false;
        $generaPolizaCancelado = false;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = false;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresa->color = 'gris';

        $botones = $this->ponBoton($btnRegresa); //*/
        $this->agregaTools($botones);

        $documento = getDocumento($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento, $this->prm->rfc_id, $this->prm->bdo_referencia,
            array("ejercicio" => 2023, "tdf_id" => (isset($this->prm->tdf_id) ? $this->prm->tdf_id : ''), "mmc_id" => "P", "afe_id" => $afe_idstr));
        if (!isset($this->prm->tdf_id)) {
            $this->prm->tdf_id = $documento->tipoDocumentoFuente;
        }
        $html = '
            <legend>Análisis del documento 2023 a generar la póliza del pagado</legend>
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
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionPresupuestal, 2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionContable, 2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->retencionContable, 2) . '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>
        ';
        //preguntamos la(s) CLC(s) del documento
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        foreach ($documento->detallePagado2023 as $key => $value) {
            if ($clc_id_documento != '') {
                if ($clc_id_documento == $value->clc_id) {
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id'] = $value->gpo_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id'] = $value->ur_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id'] = $value->clc_id;
                }
            } else {
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id'] = $value->gpo_id;
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id'] = $value->ur_id;
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id'] = $value->clc_id;
            }

            if ($this->prm->agrupador == $value->agrupador) {
                if ($value->tipoPagado != 3) {
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
            $estadoPago = $value->estado;
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
                $clcDocumento = pintaClc($value['gpo_id'], $value['ur_id'],
                    $value['clc_id'],
                    array("ejercicio" => 2023, "bdo_id" => $bdo_id_documento, "todo" => true));
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
        $pagado = pintaPagado($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento,
            array("ejercicio" => 2023, "clc_id" => $clc_id_documento, "agrupador" => $this->prm->agrupador), $this->ejercicioActual);
        $html .= $pagado->html;
        $html .= '
                </fieldset></br>
        ';

        /*         * *********************************** Información de la Póliza Contable ************************************************************** */
        $polizas = getPolizaAgrupador($this->gpo_id, $this->ur_id, $this->prm->agrupador, '', 2023);
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
                            $btnGenera->color = 'verde';
                            $btnGenera->icon = 'tasks';
                            $botonGeneraPa = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaA").click(function(){
                                    bootbox.dialog({
                                        message:"Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                        title:"RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        buttons:{
                                            cancelar: {label:"Cancelar",className:"btn-outline-primary"},
                                            borrar: {label:"Proceder",className:"btn-outline-danger",
                                                callback:function(){
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
                                                }
                                            }
                                        }
                                    });
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
                    $polizaCancelado[$key] = getPoliza($value->gpo_id,
                        $value->ur_id, $value->ue_id, $value->msc_id,
                        $value->pot_id, $value->prf_id, $value->pma_id);
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
                            $btnGenera->icon = 'tasks';
                            $botonGeneraPc = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaC").click(function(){
                                    bootbox.dialog({
                                        message:"Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                        title:"RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        buttons:{
                                            cancelar: {label:"Cancelar",className:"btn-outline-primary"},
                                            borrar: {label:"Proceder",className:"btn-outline-danger",
                                                callback:function(){
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
                                                }
                                            }
                                        }
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
                $btnGeneraPoliza->color = 'verde';
                $btnGeneraPoliza->icon = 'ok';

                $botones = $this->ponBoton($btnGeneraPoliza);
                //si el mes contable se encuentra abierto
                if ($estadoMesAutorizado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaA").click(function(){
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
                    $btnGeneraPolizaCerrado->icon = 'ok';

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
                    $btnCancelar->icon = 'block';
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
            if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado && !$polizaCan->valido) {

                $btnGeneraPoliza = new stdClass();
                $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->color = 'verde';
                $btnGeneraPoliza->icon = 'ok';

                $botones = $this->ponBoton($btnGeneraPoliza);

                if ($estadoMesCancelado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaC").click(function(){
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
                    $btnGeneraPolizaCerrado->icon = 'ok';

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
                    $btnCancelar->icon = 'block';
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

        if ($this->escritura && $estadoPago == 'A') {
            $btnCancelar = new stdClass();
            $btnCancelar->id = 'btnCancelar';
            $btnCancelar->name = 'nuevo';
            $btnCancelar->color = 'rojo';
            $btnCancelar->text = 'Cancelar Pago';
            $btnCancelar->icon = 'remove me-2';
            $btnCancelar->value = 'Cancelar Pago';

            $botones .= $this->ponBoton($btnCancelar);

            $mensajecancela = '';
            if ($estadoMesAutorizado == 'C') {
                $mensajecancela = 'Mes de pago Cerrado, se generara póliza de CANCELACIÓN en el primer mes abierto<br/>';
            }
            $this->mixScript('
                $("#btnCancelar").click(function(){
                    bootbox.dialog({
                        message:"' . $mensajecancela . 'Esta acción Cancelara su registro de pago, desea proceder? ",
                        title:"CANCELAR PAGO ",
                        buttons:{
                            cancelar:   {label:"Cancelar",className:"btn-outline-primary"},
                            borrar:     {label:"Proceder",className:"btn-outline-danger",
                                callback:function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "CallProcedure",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        //  afes: ' . $afe_idstr . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: "' . $fechaAutorizado . '",
                                        mesContableAbierto: "' . $mesContableAbierto . '",
                                        tmc_id: "C"
                                    });
                                }
                            }
                        }
                    });
                });
            ');
        } else {

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

    private function GeneraPoliza()
    {
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = 2024;
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
        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaPagado = new sp_PagadoCancelado2023();
            $generaPolizaPagado->setGpo_id($this->gpo_id_2023);
            $generaPolizaPagado->setUr_id($this->ur_id_2023);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO 2023");
            $generaPolizaPagado->setEjercicio(2023);
            $generaPolizaPagado->setSis_id("SCG");
            $generaPolizaPagado->execute(false, false);
            $bandera = $generaPolizaPagado->getBandera();
            $mensajesp = $generaPolizaPagado->getMensaje();
        } else {

            $generaPolizaPagado = new sp_PagadoAutorizado2023();
            $generaPolizaPagado->setGpo_id($this->gpo_id_2023);
            $generaPolizaPagado->setUr_id($this->ur_id_2023);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO 2023");
            $generaPolizaPagado->setEjercicio(2023);
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

    //muestra la informacion de los datos de transferencia de los documentos a pagar
    private function PagarDetalle()
    {
        if (!isset($this->prm->gur)) {
            $this->prm->gur = $this->gpo_id_2023 . $this->ur_id_2023;
        }

        $datos = $this->prm->data;
        $botones = '';
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->class = "me-2";
        $btnRegresa->color = "gris";
        $btnRegresa->icon = "fa-solid fa-circle-left me-1";

        $botones = $this->ponBoton($btnRegresa); //*/

        $bdos = '';
        $afes = '';
        foreach ($datos as $bd) {
            if ($bdos == "") {
                $bdos .= $bd->bdo_id;
            } else {
                $bdos .= "," . $bd->bdo_id;
            }

            if ($afes == "") {

                $afes .= $bd->afe_id;
            } else {
                $pos = strpos($afes, $bd->afe_id);
                if ($pos === false) {
                    $afes .= "," . $bd->afe_id;
                }
            }

        }

        $html = '<span id="bds" bds="' . $bdos . '"  /><span id="afes" afes="' . $afes . '"/><div>';
        if (count($datos) > 1) {
            $html .= '<legend>Análisis de los documentos a pagar</legend>';
        } else {
            $html .= '<legend>Análisis del documento a pagar</legend>';
        }

        $om = $this->DocumentoPagar($datos);
        $html .= '
                <fieldset class="col-md-12 col-xs-12">
                    ' . $om->html . '
                </fieldset><br/>
        ';

        $this->mixScript($om->script);

        if ($om->valido) {
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

            $tdf_id = new stdClass();
            $tdf_id->id = 'tdf_id';
            $tdf_id->placeholder = "Tipo de Documeto";
            $tdf_id->required = true;
            $tdf_id->label = "Tipo de Documeto";
            $tdf_id->sql = "SELECT * FROM ctrlAnterior.cat_TipoDocumentoFuente";
            $tdf_id->comoAttr = "tdf_id";

            $pot_id = new stdClass();
            $pot_id->id = 'pot_id';
            $pot_id->placeholder = "Tipo de Poliza";
            $pot_id->required = true;
            $pot_id->label = "Tipo de Poliza";
            $pot_id->sql = 'SELECT pot_id,pot_nombre FROM ctrlAnterior.cat_TipoPoliza WHERE pot_id IN("D","E")';
            $pot_id->comoAttr = "pot_id";

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
            $bdp_fechaAutorizado->value = date("d/m/Y");

            $html .= '<div class="row"><fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponInput($bdp_concepto);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponInput($bdp_referencia);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4 mb-3">';
            $html .= $this->ponCombo($tdf_id);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-6 col-xs-6">';
            $html .= $this->ponCombo($pot_id);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-2 col-xs-2">';
            $html .= $this->ponInput($bdp_fechaAutorizado);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponCombo($bdp_poliza);
            $html .= '</fieldset></div>';

            if ($this->escritura) {
                $btnRegistraPago = new stdClass();
                $btnRegistraPago->id = 'btnRegistraPago';
                $btnRegistraPago->icon = 'fa fa-paper-plane me-1';
                $btnRegistraPago->name = 'nuevo';
                $btnRegistraPago->color = 'verde';
                $btnRegistraPago->text = 'Nuevo';
                $btnRegistraPago->value = 'Registrar Pago';
                $botones .= $this->ponBoton($btnRegistraPago);

                $anio = 2024;
                $mes = date('m');
                $dia = date('d');
                $year = date('Y');
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
                            if( (' . $anio . ') == ' . $year . ' ){
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
                            }else if ( (' . $anio . '+1) == ' . $year . ' ){
                                if( (new Date("2024-12-31") < new Date(fec[2]+"-"+fec[1]+"-"+fec[0])))
                                        {
                                            notify("La fecha maxima de pago es el 31 de DICIEMBRE 2024","error");
                                            $("#btnRegistraPago").removeAttr("disabled");
                                            return false;
                                }
                            }else{
                                notify("Favor de validar la fecha ","error");
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
                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
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
                if ($_SESSION[_S]['nvj_id'] > 2) {
                    $html .= '<div class="alert alert-danger">EL REGISTRO DE PAGO LO REALIZA LA UNIDAD RESPONSABLE</div>';
                } else {
                    $html .= '<div class="alert alert-danger">NO TIENE PERMISOS PARA GENERAR EL PAGO</div>';
                }

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
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
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

    private function DocumentoPagar($prm = null)
    {
        if (!$prm) {
            return false;
        }

        $bdos = "";
        $afes = "";
        $i = 0;

        foreach ($prm as $bd) {
            $i++;
            if ($bdos == "") {
                $bdos .= $bd->bdo_id;
            } else {
                $bdos .= "," . $bd->bdo_id;
            }

            if ($afes == "") {

                $afes .= $bd->afe_id;
            } else {
                $pos = strpos($afes, $bd->afe_id);
                if ($pos === false) {
                    $afes .= "," . $bd->afe_id;
                }
            }
        }

        $concepto = "";
        $html = '
                <legend>Registro de pago de los Documentos</legend>
                <table class="table table-bordered"cellpadding="10" style="padding:10px; width:100%; border:1px solid #EAEAEA; box-shadow:1px 2px 3px #999;">
                    <tr bgcolor="#EAEAEA">
                        <th colspan="7" style="text-align:center;" class="warning">Datos del Documento</th>
                        <th style="text-align:center;border: 1px solid rgba(45,171,102,0.5);" colspan="4" class="success">Importes Presupuestales/Contables </th><th>&nbsp;</th>
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
                $concepto = $data->clc_concepto;
            } else {
                $ag = $data->bdo_id;
                $concepto = $data->bdo_concepto;
            }

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
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->bdo_importe, 2) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->bdo_retencionContable, 2) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->bdo_percepcionContable, 2) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->Percepcion, 2) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->ncc_retencion, 2) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format($data->ncc_percepcion, 2) . '</td>
                        ';
            if ($imp == $min && $per == $perD && $retD == $ret) {
                $html .= '<td class="success">PAGO COMPLETO</td>';
            } else {
                $html .= '<td class="error">PAGO PARCIAL</td>';
            }

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
                        afe_id: afe
                    });
                });
            ';

        $r = new stdClass;
        $r->valido = $valido;
        $r->html = $html;
        $r->concepto = $concepto;
        $r->script = $script;

        return $r;
    }

    private function Pagar()
    {
        /* VALIDAR LOS DATOS DE LOS DOCUMENTOS QUE VIENEN  */
        $ta = ''; //TIPO AGRUPADOR  A:AGRUPADO: D: DOCUMENTO
        if (isset($this->prm->data)) {
            if (count((array) $this->prm->data) == 0) {
                $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return false;
            } else {
                $ta = count((array) $this->prm->data) > 1 ? "A" : "D";
            }

        } else {
            $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return false;
        }

        $fecha = new stdClass();
        $fecha->gpo_id = $this->gpo_id;
        $fecha->ur_id = $this->ur_id;
        $fecha->fecha = $this->prm->bdp_fechaAutorizado;
        $aniActual = date("Y");
        if ($this->prm->tdf_id_pagado == "SPEI") {
            $hoy = new DateTime("now");
            $fecha->fechaMaxima = $hoy->format('d/m/Y');
            unset($hoy);
            if ($aniActual != 2024) {
                $fecha->fechaMaxima = "31/12/2024";
            }
        } else {
            $fecha->fechaMaxima = "31/12/2024";
        }

        $fecha->fechaMinima = "01/01/2024";

        $res = validaFecha($fecha);

        if ($res->valido) {
            if (!$res->fechaMaxima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return false;
            }
            if (!$res->fechaMinima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return false;
            }
        } else {
            $this->mixScript('notify("Error 1:' . $res->mensaje . '","error"); '
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return false;
        }

        $this->prm->fechaMysql = $res->fechaMysql;
        $this->prm->mes = $res->mes;

        if ($res->edoMesContable == 'C') {
            $this->mixScript('notify("El mes esta cerrado","error"); '
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return false;
        }

        $this->CallProcedure();
    }

    private function DocumentoDetalle()
    {
        $this->setContenedor("det_fs" . $this->prm->bdo_id);
        $pagado = $this->DocumentoDatos($this->prm->bdo_id, $this->prm->afe_id, true);
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
                        <td align="right">' . number_format($res->ncc_percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion, 2) . '</td>
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
                        <td align="right">' . number_format($res->ncc_percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion, 2) . '</td>
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
                        <td align="right">' . number_format($pagado[0]->bdo_importe, 2) . ' </td>
                        <td align="right">' . number_format($pagado[0]->bdo_percepcionContable, 2) . ' </td>
                        <td align="right">' . number_format($pagado[0]->bdo_retencionContable, 2) . ' </td>
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
                    </tr>.' . ($trsp == "" ? '<tr><td colspan="10">Sin registros</td></tr>' : $trsp) . '</table>
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
                        <td align="right" class="success">' . number_format($top->pres, 2) . ' </td>
                        <td align="right" class="success">' . number_format($top->perc, 2) . ' </td>
                        <td align="right" class="success">' . number_format($top->retc, 2) . ' </td>
                        <td align="right" class="success"></td>
                        <td align="right" class="success"></td>
                    </tr>
                    </table>
            ';
        $this->mixHtml($html);
    }

    private function DocumentoDatos($bdos, $afes, $all = false)
    {
        $res = array();
        $conexion = new AccesoDatos();
        $sql = "
            SELECT
                gpo_id, ur_id,
                clc_id, afe_id,
                bdo_id, bdo_referencia, rfc_id, bdo_fechaDocumento, b.tdf_id, bdo_concepto,
                bdo_percepcionPresupuestal AS bdo_importe, bdo_retencionContable, bdo_percepcionContable,
                (Percepcion) AS Percepcion, (ncc_retencion) AS ncc_retencion, (ncc_percepcion) AS ncc_percepcion,
                clc_concepto,
                bdp_fechaAutorizado, bdm_fechaAutorizado
            FROM ctrl2023.cat_BancoDocumentos b
                INNER JOIN ctrlAnterior.min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, bdo_id )
                LEFT JOIN (
                    SELECT
                        pre.gpo_id, pre.ur_id, max(pre.clc_id) as clc_id, pre.afe_id, pre.bdo_id,
                        SUM(pre.ncp_percepcionNeto) AS Percepcion, 0 AS ncc_retencion, 0 AS ncc_percepcion,
                        max(c.clc_concepto) as clc_concepto
                    FROM ctrl2023.scp_Presupuestal pre
                        INNER JOIN ctrlAnterior.scp_Clc ac USING( gpo_id, ur_id, clc_id )
                        INNER JOIN ctrl2023.scp_Clc c  USING( gpo_id, ur_id, clc_id )
                        LEFT JOIN ctrlAnterior.cg_BancoDocumentoPagado p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id,ejr_id_clc )
                    WHERE pre.gpo_id = " . $this->gpo_id_2023 . " AND pre.ur_id = " . $this->ur_id_2023 . "
                        AND pre.bdo_id IN ( $bdos )
                        AND	pre.afe_id IN ( $afes )
                        AND c.clc_id > 0 AND c.ecl_id > 3
                        AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                        AND ejr_id_clc=2023
                    GROUP BY " . ($all ? 'pre.gpo_id, pre.ur_id, pre.clc_id, pre.afe_id '
            : 'pre.bdo_id,pre.afe_id') . "
                UNION ALL
                    SELECT
                        con.gpo_id, con.ur_id, max(con.clc_id) as clc_id, con.afe_id, con.bdo_id,
                        0 AS Percepcion, SUM(con.ncc_retencion) AS ncc_retencion, SUM(con.ncc_percepcion) AS ncc_percepcion,
                            max(c.clc_concepto) as clc_concepto
                    FROM ctrl2023.scp_Contable con
                        INNER JOIN ctrlAnterior.scp_Clc ca USING( gpo_id, ur_id, clc_id )
                        INNER JOIN ctrl2023.scp_Clc c USING( gpo_id, ur_id, clc_id)
                        LEFT JOIN ctrlAnterior.cg_BancoDocumentoPagado p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id,ejr_id_clc )
                    WHERE con.gpo_id = " . $this->gpo_id_2023 . " AND con.ur_id = " . $this->ur_id_2023 . "
                        AND con.bdo_id IN ( $bdos )
                        AND	con.afe_id IN ( $afes )
                        AND c.clc_id > 0 AND c.ecl_id > 3
                        AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                        AND ejr_id_clc=2023
                    GROUP BY " . ($all ? 'con.gpo_id, con.ur_id, con.clc_id, con.afe_id '
            : 'con.bdo_id,con.afe_id') . "
                )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN(
                    SELECT
                        gpo_id, ur_id, clc_id, afe_id,
                        bdp_estado, bdp_fechaAutorizado
                    FROM  ctrlAnterior.cg_BancoDocumentoPagado
                    WHERE bdo_id IN ( $bdos )
                            AND afe_id IN ( $afes )
                        AND bdp_estado = 'A'
                            AND ejr_id_clc=2023
                )p USING( gpo_id, ur_id, clc_id, afe_id )
            WHERE gpo_id = " . $this->gpo_id_2023 . " AND ur_id = " . $this->ur_id_2023 . "
                AND t.ejr_id_clc=2023
        AND t.ejr_id_autorizado =2024
                AND bdo_id IN ( $bdos )
                AND bdm_estado = 'A'
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
                $afes = '';
                $bdos = $this->prm->data;
                $bdoArray = explode(',', $bdos);
                $afes = $this->prm->afes;

                $agrupador = $gpo_id_documento . $ur_id_documento . $this->prm->mes . 'P';
                if (count(explode(",", $this->prm->data)) > 1) {
                    $agrupador .= "A" . "" . md5($this->prm->data); // AGRUPADOR POR VARIOS DOCUMENTOS
                } else {
                    $agrupador .= "A" . "" . $this->prm->data; // AGRUPADOR PARA UN SOLO DOCUMENTO
                }
                $this->prm->bdp_concepto = strtoupper(htmlspecialchars(str_replace('"', '\"', $this->prm->bdp_concepto)));

                // INICIAMOS LA TRANSACCIÓN
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "

                        INSERT INTO ctrlAnteriorTemporal.tem_BancoDocumentoPagado(
                            gpo_id, ur_id, msc_id, clc_id, afe_id, bdo_id,
                            bdp_fechaAutorizado,
                            bdp_percepcionPresupuestal, bdp_percepcionContable, bdp_retencionContable,
                            bdp_agrupador, tdf_id, bdp_referencia,
                            bdp_concepto,
                            pot_id, bdp_estado, bdp_tipoAgrupador,
                            bdp_poliza,ejr_id_autorizado,ejr_id_clc,
                            usr_id
                            )
                        SELECT
                            t.gpo_id, t.ur_id, " . $this->prm->mes . ", t.clc_id, t.afe_id, t.bdo_id,
                            '" . $this->prm->fechaMysql . "',
                            SUM(a.ncp_percepcionNeto), SUM(a.ncc_percepcion), SUM(a.ncc_retencion),
                            '$agrupador', '" . $this->prm->tdf_id_pagado . "', '" . $this->prm->bdp_referencia . "',
                            '" . $this->prm->bdp_concepto . "',
                            '" . $this->prm->pot_id . "', 'A','D',
                            " . $this->prm->bdp_poliza . ",YEAR('" . $this->prm->fechaMysql . "'),2023,
                            " . $this->usr_id . "
                        FROM ctrl2023.cat_BancoDocumentos b
                            INNER JOIN ctrlAnterior.min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, bdo_id )
                            LEFT JOIN (
                                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                                    n.ncp_percepcionNeto, 0 AS ncc_retencion, 0 AS ncc_percepcion
                                FROM ctrl2023.scp_Presupuestal n
                                    INNER JOIN ctrl2023.scp_Afectacion a USING( gpo_id, ur_id, clc_id, afe_id, epg_id, ecf_id )
                                    LEFT JOIN ctrlAnterior.cg_BancoDocumentoPagado p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                                WHERE n.gpo_id = $gpo_id_documento AND n.ur_id = $ur_id_documento
                                    AND n.bdo_id IN ( $bdos )
                                    AND n.afe_id IN ( $afes )
                                    AND a.eaf_id = 'A'
                                    AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                            UNION ALL
                                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                                    0 AS ncp_percepcionNeto, n.ncc_retencion, n.ncc_percepcion
                                FROM ctrl2023.scp_Contable n
                                    INNER JOIN ctrl2023.scp_Afectacion a USING( gpo_id, ur_id, clc_id, afe_id )
                                    LEFT JOIN ctrlAnterior.cg_BancoDocumentoPagado p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                                WHERE n.gpo_id = $gpo_id_documento AND n.ur_id = $ur_id_documento
                                    AND n.bdo_id IN ( $bdos )
                                    AND n.afe_id IN ( $afes )
                                    AND a.eaf_id = 'A'
                                    AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                            )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                        WHERE t.gpo_id = $gpo_id_documento AND t.ur_id = $ur_id_documento
                            AND t.bdo_id IN ( $bdos )
                            AND t.afe_id IN ( $afes )
                            AND t.bdm_estado = 'A'
                            AND a.afe_id IS NOT NULL
                        GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                        ON DUPLICATE KEY UPDATE
                            bdp_fechaAutorizado = '" . $this->prm->fechaMysql . "',
                            bdp_agrupador = '$agrupador',
                            tdf_id = '" . $this->prm->tdf_id_pagado . "',
                            bdp_referencia = '" . $this->prm->bdp_referencia . "',
                            bdp_concepto = '" . $this->prm->bdp_concepto . "',
                            pot_id = '" . $this->prm->pot_id . "',
                            bdp_estado = 'A',
                            bdp_poliza = " . $this->prm->bdp_poliza . ",
                            usr_id = " . $this->usr_id . "
                    ";
                $conexion->setSql($sql);
                $conexion->consultar();
                $conexion->desconectar();
                $bandera = 0;
                $mensajesp = "ERROR";
                $generaPagado = new sp_AutorizarPagoDocumento2023();
                $generaPagado->setGpo_id($gpo_id_documento);
                $generaPagado->setUr_id($ur_id_documento);
                $generaPagado->setBdp_agrupador($agrupador);

                $generaPagado->setTmc_id('A');
                $generaPagado->setBit_observacion("REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN 2023");
                $generaPagado->setEjercicio(2023);
                $generaPagado->setSis_id("SCP");
                $generaPagado->execute(false, false);
                $bandera = $generaPagado->getBandera();
                $mensajesp = $generaPagado->getMensaje();
                $mensaje = "";
                $tipo = "";

                if ($bandera != 1) {
                    $mensaje = $mensajesp;
                    $tipo = "error";
                    $conexion->conectar();
                    $conexion->setSql("
                            DELETE
                            FROM ctrlAnteriorTemporal.tem_BancoDocumentoPagado
                            WHERE gpo_id = $gpo_id_documento AND ur_id = $ur_id_documento
                                AND bdp_agrupador = '$agrupador' and ejr_id_clc=2023 and ejr_id_autorizado=2023
                        ");
                    $conexion->consultar();
                    $conexion->desconectar();

                    unset($conexion);
                    $this->mixScript('
                            notify("' . $mensaje . '","' . $tipo . '");
                            $("#btnRegistraPago").removeAttr("disabled");
                        ');

                    return false;
                } else {
                    $mensaje = $mensajesp;
                    $tipo = "success";
                    unset($conexion);
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
                                bdo_id: "' . $bdoArray[0] . '",
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
                $sql = "
                    SELECT msc_id, bdp_concepto, bdp_referencia, tdf_id, bdp_fechaAutorizado, pot_id, bdp_poliza, bdo_id
                    FROM ctrlAnterior.cg_BancoDocumentoPagado
                    WHERE gpo_id = " . $gpo_id_documento . " AND ur_id = " . $ur_id_documento . "
                        AND bdp_agrupador = '" . $this->prm->agrupador . "'
                ";
                $conexion->setSql($sql);
                $conexion->consultar();
                $data = $conexion->extraer();

                $res = null;
                $afes = '';
                $afes = $this->prm->afes;
                if (isset($this->prm->fecha) && $this->prm->fecha != '') {
                    $fecha = $this->prm->fecha;
                    $fechaValidar = new stdClass();
                    $fechaValidar->gpo_id = $gpo_id_documento;
                    $fechaValidar->ur_id = $ur_id_documento;
                    $fechaValidar->fecha = $fecha;
                    $fechaValidar->anio = 2024;

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
                    $dr = new stdClass();
                    $dr->gpo_id = $this->gpo_id;
                    $dr->ur_id = $this->ur_id;
                    $dr->mes = $data['msc_id'];
                    $ems_id = estadoMesContable($dr);
                    if ($ems_id == 'C') {
                        $this->mixScript('notify("el mes esta cerrado","error");');
                        return false;
                    }
                }
                $poliza = $data['bdp_poliza'];
                $conexion->desconectar();
                // INICIAMOS LA TRANSACCIÓN

                $bandera = 0;
                $mensajesp = "ERROR";
                $generaPagado = new sp_CancelarPagoDocumento2023();
                $generaPagado->setGpo_id($this->gpo_id);
                $generaPagado->setUr_id($this->ur_id);
                $generaPagado->setBdp_agrupador($this->prm->agrupador);
                $generaPagado->setTmc_id('C');
                $generaPagado->setBit_observacion("CANCELACIÓN DEL REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN 2023");
                $generaPagado->setEjercicio(2023);
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
                    return false;
                    unset($conexion);
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
if (isset($_SESSION[_S]['s24-prm']['C']['17']['15']['1'])) {
    new ControladorPagado2023();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
