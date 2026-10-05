<?php
set_time_limit(0);
session_start();

//librerias
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/reporte/diario.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';

class ControladorReporteDiario extends WebResponse
{
    public $lectura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    //Constructor
    public function __construct()
    {
        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['10']['2']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case 'dibujaPantalla':
                $this->dibujaPantalla();
                break;
            case 'muestraDatos':
                $this->muestraDatos();
                break;
            case 'tipoRecurso':
                $this->tipoRecurso();
                break;
            case 'mesHasta':
                $this->mesHasta();
                break;
            case 'filtros':
                $this->filtros();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'], $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->ponBarraBotones();
                break;
        }
        $this->returnJson();

    }

    public function ponBarraBotones()
    {

        // Botones y opciones que se dibujarán en la barra de botones
        $botones = '
            <div class="container">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-inline">
                            <a id="opciones" opcion="#myModalOpciones" data-placement="bottom" title="Configurar las opciones del Reporte Diario" role="button" class="btn btn-outline-secondary me-2" data-toggle="modal"><i class="fa fa-bars me-2"></i>Opciones</a>

                            <input id="checkDiario" name="checkDiario" type="checkbox" value=1 checked>&nbsp;Diario</input>&nbsp;&nbsp;&nbsp;
                            <input id="checkIngresos" name="checkIngresos" type="checkbox" value=1 checked>&nbsp;Ingresos</input>&nbsp;&nbsp;&nbsp;
                            <input id="checkEgresos" name="checkEgresos" type="checkbox" value=1 checked>&nbsp;Egresos</input>&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input id="checkFederal" name="checkFederal" type="checkbox" value=1>&nbsp;Federal</input>&nbsp;&nbsp;&nbsp;
                            <input id="checkEstatal" name="checkEstatal" type="checkbox" value=1>&nbsp;Estatal</input>&nbsp;&nbsp;&nbsp;
                            
                            <!-- Un botón de varias opciones -->
                            <div class="input-group col-4" style="float: right; width:200px; display:none;">
                                <input id="buscar" type="text" class="form-control" placeholder="Texto a buscar ..." >
                                <button class="btn btn-outline-secondary" typre="button" >
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <span id="con_cvefin"></span>
                    </div>
                    <!-- Un espacio separador, solamente... -->
                    <span class id="contenedorReporteDiario"></span>
                    
                </div>
            </div>
        ';

        // Dibujamos la barra de botones
        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        $this->mixScript('
                ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                // Ocultamos la interfaz de SINPRES, para aprovechar más el espacio de pantalla
                $("nav").hide();
                $(".modulos").hide();
                $("#sticky-anchor").show();
                $("#sticky").show();
                $("#mainContainer").css("margin-top", "");

                comunica("' . $this->pantalla . '",{
                    metodo: "dibujaPantalla"
                });

                // Para que el contenedor use todo el ancho de pantalla
                if($(".container").css("max-width") == "1170px"){
                    $(".container").css("max-width", "100%");
                }

                // Botón regresar
                $(document).on("click", "#btnHomeMenu", function (e) {
                    e.preventDefault();
                    $("nav").show();
                    $(".modulos").show();
                    $("#tituloPantalla").show();
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();
                    $(".cabecera").show();
                    $("#mainContainer").css("margin-top","40px");

                    $("#ayuda").addClass("gris");
                    $("#tip").addClass("gris");

                    // $("#contenedor").html("");

                    $(document).off("click", "#btnHomeMenu");
                    $("nav").menusinpres("clickMenu");

                });

                $("#opciones").click(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "filtros",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkDiario").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin: "cargaComboCveFin",
                        gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . ',
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                        ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                        egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkIngresos").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin: "cargaComboCveFin",
                        gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . ',
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                        ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                        egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkEgresos").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin: "cargaComboCveFin",
                        gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . ',
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                        ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                        egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkFederal").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin: "cargaComboCveFin",
                        gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . ',
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                        ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                        egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkEstatal").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin: "cargaComboCveFin",
                        gpo_id: ' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id: ' . $_SESSION[_S]['clasAdm-ur'] . ',
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                        ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                        egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });

            ');

    }

    public function dibujaPantalla()
    {

        $html = '<div id="myGridReporteDiario"  class="tabla-contenedor col-md-12" style="overflow: scroll; style="padding-top:5px;">
               <table id="reporte-diario" class="table table-striped table-bordered">
                        <thead class="sticky-top" style="z-index:100;">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader" style="text-align:center;">PÓLIZA</th>
                                <th class="warning" role="columnheader" style= "text-align:center;">UE</th>
                                <th class="warning" role="columnheader" style= "text-align:center;">DIA</th>
                                <th class="warning" role="columnheader" style= "width:300px !important; text-align:center;">DESCRIPCIÓN</th>
                                <th class="warning" role="columnheader" style= "text-align:center;">DEBE</th>
                                <th class="warning" role="columnheader" style= "text-align:center;">HABER</th>
                                <th class="warning" role="columnheader" style= "text-align:center;">MC</th>
                                <th class="warning" role="columnheader" style= "width:2px !important; text-align:center;">ESTADO</th>
                            </tr>
                        </thead>
                    <tbody></tbody>
                </table>        
        </div>';
        $this->setContenedor("contenedor");
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                LEFT JOIN cg_MesContable USING(msc_id)
            WHERE gpo_id = ? AND ur_id = ?
        ');
        $this->consultar(array($this->gpo_id,$this->ur_id));
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') {
                $inicio = 'class="active"';
                $active = 'active';
            } else {
                $inicio = '';
                $active = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li role="presentation" opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a class="nav-link '. $active .' meses" role="tab" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
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
        // fin dibuja pantalla
        $this->mixHtml($html);

        $this->mixStyle('

            .tabla-contenedor table > tbody > tr.coloreaBalanza > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
            $("#tituloPantalla").hide();

            // Para que el GRID se ajuste al espacio disponible en pantalla
            $("#myGridReporteDiario").height(
                $(document).height() -
                $("header").outerHeight() -
                $("#sticky").outerHeight() -
                $("footer").outerHeight() - 58
            );

            // -------------------------------------------------------------------------------------
            $("#page-tabs a").click(function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");
                $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    mes: seleccionado.attr("mes"),
                    diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                    ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                    cveFin: $("#cveFin_id").val(),
                    egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                    federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                    estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                });

                $("#imprimir").attr("cat", seleccionado.attr("href"));
                $("#imprimir").attr("rep", seleccionado.text());
                e.stopPropagation();
                return false;
            });

            var sortcol = "CLAVE";
            var sortdir = 1;
            var searchString = "";

            function myFilter(item, args) {
                if (item["d"] === null || item["c"] === null) { return false }
                // Si empieza con numero ... buscamos en la cuenta
                strNumeros = "0123456789";
                if ((args.searchString || "") !== "" && strNumeros.indexOf((args.searchString || "").substring(0, 1)) !== -1) {
                    return (item["c"] && typeof item["c"] === "string" ? item["c"].substring(0, args.searchString.length) : "") === args.searchString;
                }else {
                   if (args.searchString && args.searchString !== "" && item["d"] && item["d"].toLowerCase().indexOf(args.searchString.toLowerCase()) === -1) {
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
                return (dataReporteDiario[row].epl == "C" || dataReporteDiario[row].epl == "H" ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
            }

            function FormatoMC(row, cell, value, columnDef, dataContext) {
                if (value.substring(1) == "A") {
                    return value.substring(0, 1);
                } else {
                    return "<span title=\"La póliza corresponde a la cancelación de un compromiso que fué autorizado en diferente mes\">" + value.substring(0, 1) + "<span style=\"color:red\">" + value.substring(1) + "</span></span>";
                }
            }

            function updateFilter() {
                dataViewReporteDiario.setFilterArgs({ searchString: searchString });
                dataViewReporteDiario.refresh();
            }

            function FormatoMoneda(row, cell, value, columnDef, dataContext) {

                // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
                strIni = ""
                strFin = ""

                if(value === null || value === "" || !(value != 0)){
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
            // ---------------------------------------------------------------------------------------------------
            function loadData(json){
                $("#reporte-diario tbody").html("");
                const tbody = $("#reporte-diario tbody");
                dataReporteDiario = [];
                i = 0;
                for(var key in json){
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    var d = (dataReporteDiario[i] = {});
                    d["id"] = json[key].key;
                    poliza = json[key].poliza;
                    ue_id= json[key].ue_id;
                    dia = json[key].dia;
                    concepto = json[key].pma_concepto.trim().toUpperCase();
                    pma_debe = (json[key].pma_debe == 0) ? "-" : 
                        formatoMoneda.format(json[key].pma_debe);
                    pma_haber = (json[key].pma_haber == 0) ? "0.00" : 
                        formatoMoneda.format(json[key].pma_haber);
                    mmc_id = json[key].mmc_id;
                    epl_id = json[key].epl_id;

                    switch(mmc_id.substring(1)){
                        case "A":
                            var titulo = ""
                            var colorLetra = "color:black";
                        break;
                        default:
                            var titulo = "La póliza corresponde a la cancelación de un compromiso que fué autorizado en diferente mes " + mmc_id.substring(0, 1);
                            var colorLetra = "color:red";
                        break;
                    }

                    if(epl_id == "C" || epl_id == "H"){
                       var etiqueta1 = "<del><i>";
                       var etiqueta2 = "</del></i>";
                    }else{
                        var etiqueta1 = "<span>";
                        var etiqueta2 = "</span>";
                    }

                     tbody.append(
                        "<tr class=\' seleccionable \'><td>" 
                        + poliza + "</td><td>" 
                        + ue_id + "</td><td style = \' white-space: nowrap; \'>" 
                        + dia + "</td><td style = \' white-space: pre-wrap; \'>" 
                        + etiqueta1 + concepto + etiqueta2 + "</td><td style = \' text-align: right; white-space: nowrap; \'>"
                        + pma_debe + "</td><td style = \' text-align: right; white-space: nowrap; \'>"
                        + pma_haber + "</td><td title=\'"+titulo+"\' style = \' text-align: center; "+colorLetra+"; white-space: nowrap; \'>" 
                        + mmc_id.substring(0, 1) + "</td><td style = \' text-align: center; white-space: nowrap; \'>" 
                        + epl_id + "</td></tr>");
                    i++;
                }

                $(document).ready(function() {
                    $("#reporte-diario tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaBalanza");
                        },
                        function() {                      
                            $(this).removeClass("coloreaBalanza"); 
                        }
                    ); 
                    
                    $(".seleccionable").click(function() {                                             
                        $(".seleccionable").css("font-weight","normal");
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                        $(this).css("font-weight","bold");
                    });
                });

                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();

            }

            $("#myModalOpciones").on("shown.bs.modal", function () {
                setTimeout(function() { $("#cuentaDesde").focus().select() }, 100);
            });

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalOpciones").on("hide.bs.modal", function () {
                $("#declaraRango").remove();
            });
        ');

    }

    private function muestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
            loadData(' . $data . ');
        ');

        return false;

    }

    private function DameInformacion()
    {
        $pot_id['diario'] = ((isset($_POST['diario']) && trim($_POST['diario']) != 0) ? 'D' : '');
        $pot_id['ingresos'] = ((isset($_POST['ingresos']) && trim($_POST['ingresos']) != 0) ? 'I' : '');
        $pot_id['egresos'] = ((isset($_POST['egresos']) && trim($_POST['egresos']) != 0) ? 'E' : '');

        $federal = ((isset($_POST['federal']) && trim($_POST['federal']) != 0) ? true : false);
        $estatal = ((isset($_POST['estatal']) && trim($_POST['estatal']) != 0) ? true : false);

        $periodo['mesDesde'] = $_POST['mes']; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $_POST['mes']; //Informacion de acuerdo al mes

        $claveFinanciamiento['ecf_id'] = (isset($_POST['cveFin']) && $_POST['cveFin'] != '' ? $_POST['cveFin'] : ''); // busqueda por clave de financiamiento
        $claveFinanciamiento['ter_id'] = (($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal && $estatal) ? 'C' : ($federal ? 'B' : ($estatal ? 'A' : ''))));

        $detalle = false;

        $datos = diario($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->ue_id, $pot_id, $periodo, $claveFinanciamiento, '', $detalle);

        return json_encode($datos);

    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {
            $mes = $this->prm->mes;

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id =? AND msc_id >= ? ";
            $comboMesHasta->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],$mes);
            $comboMesHasta->id = "comboMesHasta";
            $comboMesHasta->width = "150px";
            $comboMesHasta->label = "hasta";
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

    private function tipoRecurso()
    {
        $this->setContenedor('con_cvefin');

        $cveFin = new stdClass();
        $cveFin->gpo_id = $this->gpo_id;
        $cveFin->ur_id = $this->ur_id;
        $this->ue_id ? $cveFin->ue_id = $this->ue_id : '';
        $cveFin->filtro = 'OrigenRecurso';
        $cveFin->federal = ((isset($this->prm->federal) && $this->prm->federal == 1) ? true : false);
        $cveFin->estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1) ? true : false);
        //$cveFin->cargaTrabajo = 'OrigenRecurso';
        $tipoRecurso = (($cveFin->federal == 1 || $cveFin->estatal == 1) ? 1 : 2);
        $cveFin->tipoReporte = (isset($this->prm->tipoReporte) ? $this->prm->tipoReporte : 1);
        if ($tipoRecurso == 1) {
            $this->mixHtml(ClasificacionFinanciamiento::creaComboCveFin($cveFin));
        } else {
            $this->mixHtml('<div></div>');
        }

        $this->mixScript('
            $("#cveFin_id").change(function(e){
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                    diario: ($("#checkDiario").prop("checked") ? 1 : 0),
                    ingresos: ($("#checkIngresos").prop("checked") ? 1 : 0),
                    egresos: ($("#checkEgresos").prop("checked") ? 1 : 0),
                    cveFin: $("#cveFin_id").val(),
                    federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                    estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                });
            });
        ');
    }

    public function filtros()
    {

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? ;";
        $comboMesDesde->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']);
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';
        $comboMesDesde->change = '
                comunica("' . $this->pantalla . '", {
                    metodo: "mesHasta",
                    mes: $(this).val()
                });
            ';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? ;" ;
        $comboMesHasta->params = array($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']);
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->width = "150px";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->selecciona = date("m");

        $ue = new stdClass();
        $ue->label = "";

        $html = '
            <div id="myModalOpciones" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #9d2449">
                            <h4 class="modal-title" style="color:white;">Generar Reporte Diario</h4>
                            <button type="button" id="cerrarModal" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    <div class="modal-body">
                        <form action="" class="" id="form_reporte" method="post" target="_blank">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6 mb-3" style="padding-right: 1px !important;">
                                    <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                </div>
                            </div>
                            
                            <div class="row" id="periodo">
                                <legend>Periodo</legend>
                                <div class="col-6">
                                    ' . $this->ponCombo($comboMesDesde) . '
                                </div>
                                <div id="mhasta" class="col-6 mb-3">
                                    ' . $this->ponCombo($comboMesHasta) . '
                                </div>
                            </div>
                        
                            <div class="row">
                                <fieldset class="col-xs-3" style="padding-right: 1px !important;">
                                    <legend>Tipo de póliza</legend>
                                    <input id="checkDiario" name="checkDiario" type="checkbox" value=1 checked>&nbsp;Diario</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkIngreso" name="checkIngreso" type="checkbox" value=1 checked>&nbsp;Ingreso</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkEgreso" name="checkEgreso" type="checkbox" value=1 checked>&nbsp;Egreso</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>
                                <fieldset class="col-xs-3" style="padding-right: 1px !important;">
                                    <legend>Detalle</legend>
                                    <input id="checkDetalle" name="checkDetalle" type="checkbox" value=1>&nbsp;Detalle</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>
                            </div><br />
                        </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="imprimir" class="btn btn-outline-danger me-2" data-style="slide-left"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                        <button id="excel" class="btn btn-outline-success me-2" data-style="slide-left"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>

                    </div>
                </div>
            </div>
            ';

        $html .= '<div id="myGrid"></div>';
        $this->setContenedor("paraModales");
        $this->mixHtml($html);

        $this->mixStyle('
                #cogs table tr:hover{
                    background-color:#CCFF99 !important;
                }
            ');

        $this->mixScript('
                $("#myModalOpciones").modal().show();
                $("#cerrarModal").click(function(){ $("#myModalOpciones").remove(); });

                $("#checkFederal").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        tipoReporte: $("#tipoReporte").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin:"cargaComboCveFin",
                        gpo_id: ' . $this->gpo_id . ',
                        ur_id: ' . $this->ur_id . ',
                        tipoReporte: $("#tipoReporte").val(),
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                });

                $("#checkEstatal").change(function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "tipoRecurso",
                        tipoReporte: $("#tipoReporte").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });

                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin:"cargaComboCveFin",
                        gpo_id: ' . $this->gpo_id . ',
                        ur_id: ' . $this->ur_id . ',
                        tipoReporte: $("#tipoReporte").val(),
                        filtro: "OrigenRecurso",
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0)
                    });
                });

                v = $("#titulo_reporte");
                v.append($("#imprimir"))
                    .append($("#excel"))
                    .append($("#btntabla"))
                    .append($("<hr/>"));

                $("#excel,#imprimir").click(function(){
                    console.log($(this).attr("id"));
                    if($(this).attr("id")=="imprimir")
                        $("#form_reporte").attr("action","rep/doc/scg/reporte/reporte.php")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "10"))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2"))
                            .submit();
                    else{
                        $("#form_reporte").attr("action","rep/excel/scg/reporte/exp_Diario.php")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "10"))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2"))
                            .submit();
                    }
                });
            ');
    }

}

//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['10']['2']['1'])) {
    new ControladorReporteDiario();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
