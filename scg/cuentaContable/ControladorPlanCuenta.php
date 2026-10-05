<?php
/* Controlar los catálogos de la CONAC, que arman la cuenta de mayor de una cuenta contable:
GENERO + GRUPO + RUBRO + CUENTA + SUBCUENTA

IMPORTANTE: 10/DIC/2015
Comenta Israel que no habrá desglose a nivel UE en la cuentable.   En éste escenario yo no podría dar de alta la cuenta
11131.001.0001 de la UE 001    y la cuenta
11131.001.0001 de la UE 004    ya que ambas pertenecerán a la UE Contable

Se mantendrá en el código, pero en los índices cg_PlanCuentas->idx_cuenta está marcado como UNIQUE y marca error cuando
se dá de alta la segunda UE del ejemplo anterior.   Se puso como NORMAL para las pruebas, pero se supone que ésto no
llegará a producción.
 */
declare (strict_types = 1);
DEFINE("LINEAS_POR_PAGINA", 60);

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/PlanCuentas.php';

class ControladorPlanCuentas extends WebResponse
{

    //variables
    public $lectura;
    public $escritura;
    //Constructor
    public function __construct()
    {
        // Como no es una pantalla que se llame de otra, sino sólo se llama desde el menú, mando el $_POST
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);

        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case "dibujaPantalla":
                $this->dibujaPantalla();
                break;
            case "llenaCombo":
                $this->llenaCombo();
                break;
            case "PonClaveEnCombos":
                $this->PonClaveEnCombos();
                break;
            case "muestraDatosPlanCuentas":
                $this->muestraDatosPlanCuentas();
                break;
            case "muestraDatosPlanCuentasPagina":
                $this->muestraDatosPlanCuentasPagina();
                break;
            case "checaNivelMayor":
                $this->checaNivelMayor();
                break;
            case "ponNombreCtaMayor":
                $this->ponNombreCtaMayor();
                break;
            case "eliminar":
                if ($this->escritura) {
                    $this->eliminar();
                }

                break;
            case "addPlanCuentas":
                if ($this->escritura) {
                    $this->addPlanCuentas();
                }

                break;
            case "modPlanCuentas":
                if ($this->escritura) {
                    $this->modPlanCuentas();
                }

                break;
            // No se permitirá modificar si el cuarto nivel es COG
            case "modificar":
                $this->modificar();
                break;
            // y tampoco permitimos eliminar si el cuarto nivel es CONAC
            case "eliminarCuenta":
                $this->eliminarCuenta();
                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'llenaHistorial':
                $this->llenaHistorial();
                break;
            default:
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

    public function PonBarraBotones()
    {

        $botones = '
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <button id="expandir" data-placement="bottom" title="Expandir / Contraer" class="btn  btn-outline-inverse btn-sm me-1"><i id="cambiarIcono" class="fa-solid fa-down-left-and-up-right-to-center me-2"></i><span style="display:inline">Contraer</span></button>
                    ' . (($this->escritura) ? '
                        <button id="eliminar" data-placement="bottom" title="Eliminar concepto actual (sólo si aún no se ha usado)" class="btn btn-outline-danger btn-test btn-sm me-1"><i class="fa-solid fa-trash"></i></button>
                        <a id="modificar" data-placement="bottom" title="Modificar ayuda para la opciòn seleccionada" role="button" class="btn btn-outline-secondary btn-sm me-1" data-toggle="modal"><i class="fa-solid fa-pencil"></i></a>
                        <a id="adicionar" data-placement="bottom" title="Adicionar ayuda para la opción seleccionada" role="button" class="btn btn-outline-primary btn-sm me-1" data-toggle="modal"><i class="fa-solid fa-plus"></i></a>
                    ' : '') . '
                    <div class="btn-group">
                        <button class="btn btn-test dropdown-toggle btn-outline-danger btn-sm me-1" data-bs-toggle="dropdown" cat=""><i class="fa-solid fa-file-pdf me-2"></i>PDF<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a id="planActual" class="dropdown-item impCatalogo">Selección Actual</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a id="planTodo" class="dropdown-item impCatalogo">Todo el Catálogo</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a id="impRango" class="dropdown-item" data-bs-toggle="modal">Rango de Cuentas</a></li>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <button class="btn  btn-test dropdown-toggle btn-outline-success btn-sm me-1" data-bs-toggle="dropdown" cat=""><i class="fa-solid fa-file-excel me-2"></i>Excel<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a id="planActual" class="dropdown-item expCatalogo">Selección Actual</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a id="planTodo" class="dropdown-item expCatalogo">Todo el Catálogo</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a id="expRango" class="dropdown-item" data_bs-toggle="modal">Rango de Cuentas</a></li>
                        </ul>
                    </div>
                    <button id="verHistorial"  type="button" class="btn btn-outline-secondary btn-sm me-1"  title="Ver historial de la Cuenta"><i class="fa-solid fa-list-alt"></i></button>
                    <div class="btn-group">
                        <button class="btn dropdown-toggle btn-outline-warning btn-sm me-1" data-bs-toggle="dropdown" cat=""><i class="fa-solid fa-filter me-2"></i>Más Filtros<span class="caret"></span></button>
                        <ul class="dropdown-menu miDivFloat" style="padding-left: 10px; padding-right: 30px; width: 400px;">
                            <div class="row mb-4">
                                <fieldset class="col-xs-12" style="padding-right: 1px !important;">
                                    <input id="nombreBuscar" type="text" class="form-control"  placeholder="Que incluya el texto..." tabindex="20">
                                </fieldset>
                            </div>
                            <div class="row mb-4">
                                <fieldset class="col-xs-12" style="padding-right: 1px !important;">' .
        $this->ComboUnidadesEjecutoras() .
        '</fieldset>
                                <fieldset class="col-xs-12" style="padding-right: 1px !important;">' .
        $this->ComboAgrupacionCuentas() .
            '</fieldset>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <button id="mostrar1" style="float:right;" data-placement="bottom" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-secondary muestraPlanCuentas ladda-button btn-sm me-1" data-style="slide-left" cat="" tabindex="30"><i class="fa-solid fa-search me-1"></i>Mostrar</button>
                                </div>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
            <span style="height:8px; display:block"></span>
            <div class="row">
                <div class="d-flex justify-content-between">
                    <div class="col-md-2 me-4">
                        <div class="input-group-sm">
                            <div class="input-group input-group-sm">
                                <input id="ctaMayor" lastSearch="" name="ctaMayor" type="text" placeholder="Cuenta Mayor" maxlength=12 class="form-control F1Catalogo" tabindex="10">
                                <button id="mostrarMayor" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-rectangle-list"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 me-3">
                        <div class="input-group-sm">
                            <input id="nombreMayor" type="text" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button id="mostrar2" data-placement="bottom" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-secondary btn-sm muestraPlanCuentas ladda-button" data-style="slide-left" cat="" tabindex="20"><i class="fa-solid fa-search me-2"></i>Mostrar</button>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm" style="float: right; width:250px">
                            <input id="buscar" type="text" class="form-control" placeholder="Texto a buscar ...">
                            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
            <span style="height:32px; display:block"></span>
        ';

        $this->mixStyle('
            .slick-group-title[level="0"] { display:inline }
        ');

        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        $this->mixScript('
            $("nav").hide();
            $(".modulos").hide();
            ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
            : '') . '
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");

            if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            // Tabuladores Inferiores
            // -------------------------------------------------------------------------------------
            $("#page-tabsI a").click( function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#ctaMayor").attr("eje", seleccionado.attr("href") );
                $("#page-tabsI li").each(function (index) {
                    $(this).removeClass("selected");
                });
                seleccionado.closest("li").addClass("selected");
                $(".muestraPlanCuentas").click();
                e.stopPropagation();
                return false;
            });
            $(".miDivFloat").click(function(e) {
                e.stopPropagation();
            });

            /* expandir / Contraer niveles */
            $("#expandir").click(function (e) {
                e.preventDefault();

                const expandido = $("#cambiarIcono").hasClass("fa-down-left-and-up-right-to-center");

                // Cambiar ícono y texto del botón
                if (expandido) {
                    $("#expandir span").text("   Expandir");
                    $("#cambiarIcono")
                        .removeClass("fa-down-left-and-up-right-to-center")
                        .addClass("fa-up-right-and-down-left-from-center");
                } else {
                    $("#expandir span").text("   Contraer");
                    $("#cambiarIcono")
                        .removeClass("fa-up-right-and-down-left-from-center")
                        .addClass("fa-down-left-and-up-right-to-center");
                }

                // Mostrar u ocultar las filas de cada grupo manualmente
                $(".group-header").each(function () {
                    const groupId = $(this).data("group");
                    const rows = $(`.${groupId}`);

                    rows.each(function () {
                        $(this).css("display", expandido ? "none" : "table-row");
                    });

                    // Cambiar el icono del encabezado
                    const icon = $(this).find(".toggle-icon");
                    icon.text(expandido ? "▶" : "▼");
                });

                return false;
            });

            $("#ctaMayor").focus( function(e) {
                mayorCuenta = "ctaMayor";
                mayorNombre = "nombreMayor";
            });

            // F1 muestra el catálogo */
            $("#ctaMayor").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                }
            });

            $("#ctaMayor").blur( function(e) {
                // Mínimo 5 dígitos
                $("#ctaMayor").val( preparaCuentaContable( $("#ctaMayor").val(), 1 ) );
                if ( $("#ctaMayor").val() != "" && ( $("#ctaMayor").val().length >= 9 && $("#ctaMayor").val().length <=12 ) ) {
                    // Si el campo de nombre de la cuenta está vacío ... o el nombre no corresponde a la cuenta de mayor actual ... le ponemos su nombre
                    if ( $("#nombreMayor").val() == "" || $("#nombreMayor").attr("deCta") != $("#ctaMayor").val() ) {
                        comunica("' . $this->pantalla . '",{
                            "metodo":"ponNombreCtaMayor",
                            cta: $("#ctaMayor").val(),
                            campo: "nombreMayor"
                        });
                    }
                }
            });

            $("#mostrarMayor").click( function(e) {
                mayorCuenta = "ctaMayor";
                mayorNombre = "nombreMayor";
                e.preventDefault();
                dlg.dialog("open");
            });

            $("#impRango").click( function(e) {
                $("#imprimirRango").attr("t", "pdf" );
                $("#myModalRango").show();
            });
            $("#expRango").click( function(e) {
                $("#imprimirRango").attr("t", "xls" );
                $("#myModalRango").show();
            });

            $(".impCatalogo").click( function(e) {
                e.preventDefault();
                // Si están pidiendo la selección actual, no puede estar vacío el campo de cuenta Mayor y debe ser de al menos 5 dígitos
                if ( $(this).attr("id") == "planActual" ) {
                    if ( !( $("#ctaMayor").val().length >=9 && $("#ctaMayor").val().length <= 12 ) ) {
                        notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                        return false;
                    }
                }

                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $(this).attr("id") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mayor").attr("value", $("#ctaMayor").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#nombreBuscar").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "filtroUE").attr("value", $("#ueFiltro").val() ))
                $("body").append(form);
                    form.submit();
                    e.stopPropagation();
                });

                $(".expCatalogo").click( function(e) {
                    e.preventDefault();
                    // Si están pidiendo la selección actual, no puede estar vacío el campo de cuenta Mayor y debe ser de al menos 5 dígitos
                    if ( $(this).attr("id") == "planActual" ) {
                        if ( !( $("#ctaMayor").val().length >=9 && $("#ctaMayor").val().length <= 12 ) ) {
                            notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                            return false;
                        }
                    }

                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/cuentaContable/exp_PlanCuentas.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $(this).attr("id") ))
                        .append($("<input>").attr("type", "hidden").attr("name", "mayor").attr("value", $("#ctaMayor").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "filtroUE").attr("value", $("#ueFiltro").val() ))

                    $("body").append(form);
                        form.submit();
                    });

                    // Mostramos el historial de la cuenta
                    $("#verHistorial").click(function(e) {
                        // Obtener la fila seleccionada
                        const filaSeleccionada = document.querySelector("#tabla-tbl-plan tbody tr.seleccionable.seleccionado");
                        // Si no hay ningún documento seleccionado, no hacemos nada
                        if (!filaSeleccionada) {
                            // No hay fila seleccionada, no hacer nada
                            return false;
                        }

                         // Obtenemos el array de los datos de la linea actual
                        const celdas = filaSeleccionada.querySelectorAll("td");
                        console.log(celdas);

                        const row = {
                            c: celdas[1]?.textContent.trim(),
                            n: celdas[2]?.textContent.trim(),
                            ue: celdas[3]?.textContent.trim()
                        };

                        // Si no hay nada que selecionar, no hacemos nada
                        if ( celdas == "" || row.c == "" ) {
                            e.stopPropagation();
                            return false;
                        }

                        comunica("' . $this->pantalla . '",{
                            "metodo" : "verHistorial",
                            "c"   : row.c,
                            "ue"  : row.ue
                        });
                        e.stopPropagation();
                    });
        ');

        $this->dibujaPantalla();
    }

    public function dibujaPantalla()
    {
            $html = '<div id="myGrid" >
            <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                <table id="tabla-tbl-plan" class="table table-striped table-bordered">
                    <div class="col-md-12 row" style="text-align:center">
                        <thead class="sticky-top">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader" style="width:90px !important;"></th>
                                <th class="warning" role="columnheader">CUENTA</th>
                                <th class="warning" role="columnheader">NOMBRE</th>
                                <th class="warning" role="columnheader">UE</th>
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
                            <label id="rA-tbl-vinculacion"></label>
                            <label id="rB-tbl-vinculacion"></label> 
                            <label id="rTotal-tbl-vinculacion"></label>
                        </div>
                        <div id="planPaginador" class="flex-grow-2 justify-content-center p-1" ></div>
                    </div>
                </div>
            </div>
        </div>';

        $this->setContenedor("contenedor");

        // Dibujamos la ventana que permite especificar el rango a imprimir
        $this->dibujaVentanaRango();
        // Dibujamos la ventana que permite modificar o adicionar un registro
        $this->dibujaVentanaCaptura();
        // Dibujamos la pantalla que permite buscar una cuenta a nivel de mayor
        $this->mixHtml(DibujaPantallaBuscarCuentaMayorHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaMayorJS("SI"));

        $this->mixHtml($html);

        $this->mixScript('

            $("#tabla-tbl-plan thead").css("z-index", "100");
            $("#tituloPantalla").hide();
            $(".input-group-addon").css("padding","2px 12px");
            setTimeout( function() { $( "#ctaMayor" ).focus() }, 100 );
            // mousedown en lugar de click para que no interfiera con el blur que pone el nombre de la cuenta
            // porque el usuario "siente" que se pierde un click
            $(".muestraPlanCuentas").on( "mousedown click", function(e) {
                $("#borrame").remove();
                $("#jsPaginador").remove();
                // Si están buscanlo lo mismo que la última vez, ni los pelamos
                if ( $("#ctaMayor").val() != $("#ctaMayor").attr("lastSearch") || $("#nombreBuscar").val() != $("#nombreBuscar").attr("lastSearch") || $("#ueFiltro").val() != $("#ueFiltro").attr("lastSearch") || $("#AgCtaFiltro").val() != $("#AgCtaFiltro").attr("lastSearch")) {
                    if ( $("#ctaMayor").val() != "" && (   $("#ctaMayor").val().length >= 9 && $("#ctaMayor").val().length <=12 ) ) {
                        // Mostramos icono de espera ...
                        comunica("' . $this->pantalla . '",{
                            "metodo":"muestraDatosPlanCuentas",
                            cta: $("#ctaMayor").val(),
                            txt: $("#nombreBuscar").val(),
                            ue: $("#ueFiltro").val(),
                            AgCta: $("#AgCtaFiltro").val()
                        });
                    } else {
                        notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    }
                    /* Para grabar la última búsqueda */
                    $("#ctaMayor").attr( "lastSearch", $("#ctaMayor").val() );
                    $("#nombreBuscar").attr( "lastSearch", $("#nombreBuscar").val() );
                    $("#ueFiltro").attr( "lastSearch", $("#ueFiltro").val() );
                    $("#AgCtaFiltro").attr( "lastSearch", $("#AgCtaFiltro").val() );
                }
            });

            var sortcol = "CUENTA";
            var sortdir = 1;
            var searchString = "";

            function myFilter(item, args) {
                if ( item["c"] === null || item["n"] === null ) return false;
                // Si empieza con numero ... buscamos en la cuenta
                strNumeros = "0123456789";
                try {
                    if ( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ) {
                        return item["c"].startsWith(args.searchString);
                    // si no ... buscamos en el texto
                    } else {
                        if (args.searchString !== "" && !item["n"].toLowerCase().includes(args.searchString.toLowerCase())) {
                            return false;
                        }
                    }
                }  catch (err) {
                    // nada
                }
                return true;
            }

            function comparer(a, b) {
                var x = a[sortcol], y = b[sortcol];
                return (x == y ? 0 : (x > y ? 1 : -1));
            }

            /* Para filtrar el resultado en la tabla plan */
            $("#buscar").on("keyup", function(e){                     
                var value = $(this).val().toLowerCase();
                $("#tabla-tbl-plan tbody>tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });


            function updateFilter() {
                dataView.setFilterArgs({ searchString: searchString });
                dataView.refresh();
            }

            var dataView;
            var grid;
            var data = [];
            var cuartos = [];
            var columns = [
                {id: "espacio", name: "", field: "", maxWidth: 100, resizable: false, selectable: false, focusable: false },
                {id: "c", name: "CUENTA", field: "c", minWidth: 140, maxWidth: 600, sortable: true },
                {id: "n", name: "NOMBRE", field: "n", minWidth: 600, sortable: true },
                {id: "ue", name: "UE", field: "ue", minWidth: 100, sortable: true }
            ];

            var options = {
                enableCellNavigation: true,
                enableColumnReorder: false,
                enableAutoSizeColumns: true,
                autoHeight: true
            };

            // Llenamos el array de las cuentas a cuarto nivel
            // ---------------------------------------------------------------------------------------------------
            function loadArrayCuartos( json ) {
                cuartos = [];
                for (var i = 0; i < json.length; i++) {
                    var d = (cuartos[i] = {});
                    d["g"] = json[i].g;
                    d["n"] = json[i].n;
                }
            }

            // Cargamos la información a mostrar, del Plan de Cuentas
            // ---------------------------------------------------------------------------------------------------
            function loadData( json ) {
                $("#tabla-tbl-plan tbody").html("");
                const tbody = $("#tabla-tbl-plan tbody");

                data = [];
                for (var i = 0; i < json.length; i++) {
                    var d = (data[i] = {});
                    id  = json[i].ue + "." + json[i].id;

                    g   = json[i].g;
                    c   = json[i].c;
                    n   = json[i].n;
                    ue  = json[i].ue;
                    u   = json[i].u;
                    tbody.append(
                        "<tr class=\'seleccionable\'><td>" 
                        + g + "</td><td>" 
                        + c + "</td><td>" 
                        + n + "</td><td>" 
                        + ue + "</td></tr>");
                }

                $(document).ready(function() {
                    $(".seleccionable").click(function() {
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                    });
                });
            }

            function agrupaInformacion(datos, cuartos, searchString = "") {
                const tbody = document.querySelector("#tabla-tbl-plan tbody");
                tbody.innerHTML = "";

                const args = { searchString };
                const datosFiltrados = datos.filter(item => myFilter(item, args));
                
                // Agrupar por campo "g"
                const grupos = {};
                datosFiltrados.forEach(item => {
                    if (!grupos[item.g]) grupos[item.g] = [];
                        grupos[item.g].push(item);
                    });

                // Renderizar grupos
                for (let g in grupos) {
                    const nombreGrupo = (cuartos.find(c => c.g === g) || {}).n || `Grupo ${g}`;
                    const groupId = `group-${g}`;

                    // Encabezado del grupo
                    const headerRow = document.createElement("tr");
                    headerRow.classList.add("group-header", "table-active");
                    headerRow.setAttribute("data-group", groupId);
                    headerRow.innerHTML = `
                        <td colspan="4">
                            <strong><span class="toggle-icon">▼</span> ${nombreGrupo}</strong>
                        </td>
                    `;
                    tbody.appendChild(headerRow);

                    // Filas del grupo
                    grupos[g].forEach(item => {
                        const row = document.createElement("tr");
                        row.classList.add("group-row", groupId, "seleccionable");
                        row.style.display = "table-row";
                        row.setAttribute("data-id", item.id);
                        row.setAttribute("data-u", item.u);
                        row.setAttribute("data-ue", item.ue);

                        row.innerHTML = `
                            <td></td>
                            <td>${item.c}</td>
                            <td>${item.n}</td>
                            <td>${item.ue}</td>
                        `;

                            row.addEventListener("click", function () {
                                document.querySelectorAll(".seleccionable").forEach(r => r.classList.remove("seleccionado"));
                                this.classList.add("seleccionado");
                            });

                        tbody.appendChild(row);
                    });

                    // Expandir/contraer
                    headerRow.addEventListener("click", () => {
                        const rows = document.querySelectorAll(`.${groupId}`);
                        const visible = rows[0].style.display !== "none";
                        rows.forEach(r => r.style.display = visible ? "none" : "table-row");

                        const icon = headerRow.querySelector(".toggle-icon");
                        icon.textContent = visible ? "▶" : "▼";
                    });
                }
            }

        ');

        // SOLO USUARIOS CON PERMISOS DE ESCRITURA VERAN ÉSTE CODIGO
        if ($this->escritura) {
            $this->mixScript('
                $("#modificar").click(function(e){
                       // Obtener la fila seleccionada
                        const filaSeleccionada = document.querySelector("#tabla-tbl-plan tbody tr.seleccionable.seleccionado");
                        
                        // Si no hay una cuenta seleccionada, no hacemos nada
                        if (!filaSeleccionada) {
                            return false;
                        }

                        // Obtener datos de la fila
                        const celdas = filaSeleccionada.querySelectorAll("td");
                        console.log(celdas);

                        const row = {
                            c: celdas[1]?.textContent.trim(),
                            n: celdas[2]?.textContent.trim(),
                            ue: celdas[3]?.textContent.trim()
                        };

                        console.log("' . $this->pantalla . '");
                        comunica("' . $this->pantalla . '",{
                            "metodo":"modificar",
                            "c": row.c,
                            "nom": row.n,
                            "ue": row.ue,
                            "u": u
                        });
                    $("#myModalAddMod").show()
                });

                $("#adicionar").click(function(e){
                    $(".modal-title").text("Adicionar Cuenta Contable");
                    $("#grabar").attr("op","add");
                    $("#variasCuentas").css("display", "block");
                    // Default: estructura 1
                    nTipoCuenta = 1;
                    arrCta = "";
                    // Si hay una cuenta seleccionada, asumimos que quieren dar de alta una de ese grupo ... y precargamos cierta información
                    const filaSeleccionada = document.querySelector("#tabla-tbl-plan tbody tr.seleccionable.seleccionado");
                    if ( filaSeleccionada != null ) {
                        // Obtener datos de la fila
                        const celdas = filaSeleccionada.querySelectorAll("td");

                        const row = {
                            c: celdas[1]?.textContent.trim(),
                            n: celdas[2]?.textContent.trim(),
                            ue: celdas[3]?.textContent.trim()
                        };
                        // Obtenemos el array de los datos de la linea actual
                        cta = row.c;
                        // convertimos a array
                        arrCta = cta.split( "." );
                        // Checamos cuántos niveles tiene
                        nNiveles = arrCta.length;
                        // Determinamos la estructura de la cuenta
                        if ( nNiveles == 7 ) {   // Los niveles de mayor ya vienen separados por puntos, por lo tanto el sólo nivel de mayor abarca 5 "niveles"
                            // Si es de tipo 1 o 2, validamos por el ancho del nivel de registro (1 == 4, 2 == 9)
                            if ( arrCta[6].length == 4  ) {
                                nTipoCuenta = 1;
                            } else {
                                nTipoCuenta = 2;
                            }
                        } else {
                            nTipoCuenta = 3;
                        }
                        $("#nivelMayor").removeAttr("disabled");
                        $("#mostrarNivelMayor").removeAttr("disabled");
                        $("#nivMayor").val( arrCta[0]+"."+arrCta[1]+"."+arrCta[2]+"."+arrCta[3]+"."+arrCta[4] ).removeAttr("disabled");
                        $("#nivUE").val( row.ue ).hide();
                        $("#ue").val( row.ue );
                        $("#nivCuarto").val( arrCta[5] ).removeAttr("disabled");
                        // Sólo las cuentas de tipo 3 tienen quinto nivel
                        if ( nTipoCuenta == 3 ) {
                            $(".quinto").css("display", "block");
                            $("#nivQuinto").val( arrCta[6] ).removeAttr("disabled");
                            $("#quinto").val( arrCta[6] ).removeAttr("disabled");
                            $("#nivQuinto").trigger( "change");
                        } else {
                            $(".quinto").css("display", "none");
                        }
                        setTimeout( function() { $("#ue").focus(); }, 200 );
                    } else {
                        $("#ctaMayor").removeAttr("disabled");
                        //$( "#nivelMayor" ).focus().select();
                        setTimeout( function() {
                            $( "#nivelMayor" ).focus().select() }, 100 );
                        ;
                    }
                    // Por si no seleccionaron cuenta antes de llamar a la pantalla de alta
                    if (typeof cta == "undefined") {
                        cta = "";
                    }
                    // Ponemos el nivel de registro, de acuerto al tipo de cuenta
                    switch (nTipoCuenta) {
                        case 1:
                            $("#nivRegistro").attr( "maxlength", "4" );
                            if ( arrCta != "" ) {
                                $("#nivRegistro").val( arrCta[6] ).removeAttr("disabled");
                            }
                            break;
                        case 2:
                            $("#nivRegistro").attr( "maxlength", "9" );
                            if ( arrCta != "" ) {
                                $("#nivRegistro").val( arrCta[6] ).removeAttr("disabled");
                            }
                            break;
                        case 3:
                            $("#nivRegistro").attr( "maxlength", "4" );
                            if ( arrCta != "" ) {
                                $("#nivRegistro").val( arrCta[7] ).removeAttr("disabled");
                            }
                            break;
                    }
                    // Actualizamos los combox con el valor de los campos de texto
                    $("#nivMayor").trigger( "blur");
                    // Permitimos la edición en los combos
                    $("#ue").removeAttr("disabled");
                    $("#ue").change();
                    $("#cuarto").removeAttr("disabled");
                    $("#nivRegistro").val( "" );
                    $("#nombre").val( "" );
                    $("#nombre").removeAttr( "disabled");
                    $("#grabar").attr( "op", "add" );
                    $("#myModalAddMod").show()
                });

                // Borramos una cuenta del Plan de Cuentas ...
                $("#eliminar").click(function(e){
                     // Obtener la fila seleccionada
                    const filaSeleccionada = document.querySelector("#tabla-tbl-plan tbody tr.seleccionable.seleccionado");
    
                    if (!filaSeleccionada) {
                        // No hay fila seleccionada, no hacer nada
                        return false;
                    }

                    // Obtenemos el array de los datos de la linea actual
                    const celdas = filaSeleccionada.querySelectorAll("td");

                        const row = {
                            c: celdas[1]?.textContent.trim(),
                            n: celdas[2]?.textContent.trim(),
                            ue: celdas[3]?.textContent.trim()
                        };


                    // Marcos: Quitamos comillas dobles para que no marque error al confirmar
                    nomCta = row.n.replace(/\"/g, "");
                    // Si es un valor editable ...
                    if (typeof row.c != "undefined") {
                        comunica("' . $this->pantalla . '",{
                            "metodo":"eliminarCuenta",
                            "c": row.c,
                            "nom": nomCta,
                            "id": id,
                            "ue": row.ue,
                            "u": u });
                    } else {
                        e.preventDefault();
                        return false;
                    }
                });
            ');
        }
    }
    /* Regresamos en formato JSON los cuartos niveles de la cuenta solicitada
    ---------------------------------------------------------------------------------------------------- */

    private function DameCuartosNiveles($c)
    {

        $cuentaMayor = $c;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);

        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR", "error" );
                window.espera.stop();
            ');
            return false;
        }

        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
                window.espera.stop();
            ');
            return false;
        }

        // Misma consulta para todos los ejercicios
        $sql = 'SELECT cgc_id AS g, cgc_nombre AS n FROM '.BD_CTRL.'.cg_CuentaCuarto  WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?;';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();

        if ($nRegistros <= 0) {
            return json_encode(array('g' => '', 'n' => ''));
        } else {
            $data = array();
            while ($row = $conexion->extraer()) {
                array_push($data,
                    array(
                        'g' => $row['g'],
                        'n' => $row['n'],
                    ));
            }

            return json_encode($data);
        }
    }
    /* Regresamos en formato JSON el Plan de Cuentas de la cuenta seleccionada
    ---------------------------------------------------------------------------------------------------- */

    private function DamePlanCuentas($c, $t, $ue, $AgCta)
    {

        $cuentaMayor = $c;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript(' notify("Favor de verificar la cuenta de MAYOR", "error" );');
            return false;
        }
        // Si piden buscar tambien un texto en el nombre ...
        $buscarTexto = '';
        if (trim($t) != '') {
            $buscarTexto = ' AND cta_nombre LIKE "%' . trim($t) . '%" ';
        }

        // Si piden buscar tambien un UE en el nombre ...
        $filtrarUE = '';
        if (trim($ue) != '') {
            $filtrarUE = ' AND cta_ue_id = ' . trim($ue) . ' ';
        }
        // Si piden buscar tambien  pro agrupacion de la cuenat ...
        $filtrarAgCta = '';
        if (trim($AgCta) != '') {
            $filtrarAgCta = ' AND agc_id = "' . trim($AgCta) . '" ';
        }

        // Averiguamos el número de registros a regresar
        // -----------------------------------------------------------------------------------
        // En el ejercicio 2012 la estructura de tablas difiere
        if (APP_EJERCICIO == 2012) {
            $sql = '
                PENDIENTE
            ';
        } else {
            $sql = '
                SELECT COUNT(*) AS total FROM '.BD_CTRL.'.cg_PlanCuentas
                WHERE cta_gpo_id = ? AND cta_ur_id = ? AND
                ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? ' . $buscarTexto . $filtrarUE . $filtrarAgCta . ';
            ';
            $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $genero, $grupo, $rubro, $cuenta, $subcuenta];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $row = $conexion->extraer();

        $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
        $nInicial = 0; // Como aqui cargamos la pagina inicialmente, es la primera

        if ($nTotalPages > 1) {

            $this->mixScript('
                $("#planPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right nav nav-tabs",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return "nav-link me-1 " + ((page === current) ? "active" : "pointer-cursor"); },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#planPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                "metodo":"muestraDatosPlanCuentasPagina",
                                cta: $("#ctaMayor").val(),
                                txt: $("#buscar").val(),
                                ue: $("#ueFiltro").val(),
                                AgCta: $("#AgCtaFiltro").val(),
                                p: page
                            });
                            $("#planPaginador").attr("last", page );
                        }
                    }
                }
                $("#planPaginador").bootstrapPaginator(options);

            ');
        } else {
            $this->mixScript('$("#planPaginador").html("")');
        }

        // En el ejercicio 2012 la estructura de tablas difiere
        if (APP_EJERCICIO == 2012) {
            $sql = '
                PENDIENTE
            ';
        } else {
            $sql = '
                SELECT cgc_id AS g, cta_cuenta as c, cta_nombre as n, CONCAT( cta_gpo_id, cta_ur_id ) AS ur, cta_ue_id AS ue, IF( YEAR( c.usr_fecha ) = "' . APP_EJERCICIO . '", u.usr_usuario, "" ) AS usr
                FROM '.BD_CTRL.'.cg_PlanCuentas c
                LEFT JOIN cat_Usuario u USING( usr_id )
                WHERE cta_gpo_id = ? AND cta_ur_id = ? AND ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?' . $buscarTexto . $filtrarUE . $filtrarAgCta . '
                LIMIT ' . $nInicial . ', ' . LINEAS_POR_PAGINA . ';
            ';
            $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $genero, $grupo, $rubro, $cuenta, $subcuenta];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);

        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();

        if ($nRegistros <= 0) {
            return json_encode(array('id' => '0', 'g' => '', 'c' => '', 'n' => '',
                'ue' => '', 'u' => ''));
        } else {
            $data = array();
            while ($row = $conexion->extraer()) {
                array_push($data,
                    array(
                        'id' => $row['c'],
                        'g' => $row['g'],
                        'c' => $row['c'],
                        'n' => $row['n'],
                        'ue' => $row['ue'],
                        'u' => $row['usr'],
                    ));
            }
            return json_encode($data);
        }
    }
    /* Regresamos en formato JSON el Plan de Cuentas de la cuenta seleccionada, DE UNA PAGINA ESPECIFICA
    ---------------------------------------------------------------------------------------------------- */

    private function DamePlanCuentasPagina($c, $p, $t, $ue, $AgCta)
    {

        $this->mixScript('$("#borrame").remove()');

        $cuentaMayor = $c;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        // Si piden buscar tambien un texto en el nombre ...
        $buscarTexto = '';
        if (trim($t) != '') {
            $buscarTexto = ' AND cta_nombre LIKE "%' . trim($t) . '%" ';
        }

        // Si piden buscar tambien un texto en el nombre ...
        $filtrarUE = '';
        if (trim($ue) != '') {
            $filtrarUE = ' AND cta_ue_id = ' . trim($ue) . ' ';
        }
        // Si piden buscar tambien  pro agrupacion de la cuenat ...
        $filtrarAgCta = '';
        if (trim($AgCta) != '') {
            $filtrarAgCta = ' AND agc_id = "' . trim($AgCta) . '" ';
        }
        $nInicial = (($p - 1) * LINEAS_POR_PAGINA); // Como aqui cargamos la pagina inicialmente, es la primera
        // En el ejercicio 2012 la estructura de tablas difiere
        if (APP_EJERCICIO == 2012) {
            $sql = '
                PENDIENTE
            ';
        } else {
            $sql = '
                SELECT cgc_id AS g, cta_cuenta as c, cta_nombre as n, CONCAT( cta_gpo_id, cta_ur_id ) AS ur, cta_ue_id AS ue, IF( YEAR( c.usr_fecha ) = "' . substr($_SESSION[_S]["ejercicio"],
                0, 4) . '", u.usr_usuario, "" ) AS usr
                FROM '.BD_CTRL.'.cg_PlanCuentas c
                LEFT JOIN cat_Usuario u USING( usr_id )
                WHERE cta_gpo_id = ? AND cta_ur_id = ? AND
                ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?' . $buscarTexto . $filtrarUE . $filtrarAgCta . '
                LIMIT ' . $nInicial . ', ' . LINEAS_POR_PAGINA . ';
            ';
            $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $genero, $grupo, $rubro, $cuenta, $subcuenta];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();

        if ($nRegistros <= 0) {
            return json_encode(array('id' => '0', 'g' => '', 'c' => '', 'n' => ''));
        } else {
            $data = array();
            while ($row = $conexion->extraer()) {
                array_push($data,
                    array(
                        'id' => $row['c'],
                        'g' => $row['g'],
                        'c' => $row['c'],
                        'n' => $row['n'],
                        'ue' => $row['ue'],
                        'u' => $row['usr'],
                    ));
            }
            return json_encode($data);
        }
    }
    /* Mostramos la información a nivel de mayor
    ---------------------------------------------------------------------------------------------------- */

    private function muestraDatosPlanCuentas()
    {

        // Llenamos un array con los cuartos niveles
        // ( podemos obtenerlos desde el select con un INNER JOIN a cg_CuentaCuarto ... pero me parece un
        // desperdicio de banda el llamar una y otra vez el nombre del cuarto nivel, asi que mejor lo
        // llamo 1 sola vez ... lo meto en un array y que el grid lo busque y dibuje )
        $cuartos = $this->DameCuartosNiveles($this->prm->cta);
        $this->mixScript('loadArrayCuartos( ' . $cuartos . ' );');

        // Ahora seguimos normal, cargando el Plan de Cuentas solicitado...
        $data = $this->DamePlanCuentas($this->prm->cta, $this->prm->txt,
            $this->prm->ue, $this->prm->AgCta);

        $this->setIdScript('borrame');
        $this->mixScript('
            loadData( ' . $data . ' );
            agrupaInformacion(' . $data . ', ' . $cuartos . ');
            document.getElementById("buscar").addEventListener("input", function () {
            const texto = this.value.trim();
            agrupaInformacion(' . $data . ', ' . $cuartos . ', texto); // ← datosOriginales es el array completo original
        });

        ');

        return false;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Carga el catálogo de cuentas de una página específica (se llama desde los botones del paginador)
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function muestraDatosPlanCuentasPagina()
    {
        $cuartos = $this->DameCuartosNiveles($this->prm->cta);
        $this->mixScript('loadArrayCuartos( ' . $cuartos . ' );');

        // Ahora seguimos normal, cargando el Plan de Cuentas solicitado...
        $data = $this->DamePlanCuentasPagina($this->prm->cta, $this->prm->p,
            $this->prm->txt, $this->prm->ue, $this->prm->AgCta);

        $this->setIdScript('borrame');
        $this->mixScript('
            loadData( ' . $data . ' );
            agrupaInformacion(' . $data . ', ' . $cuartos . ');
            document.getElementById("buscar").addEventListener("input", function () {
                const texto = this.value.trim();
                agrupaInformacion(' . $data . ', ' . $cuartos . ', texto); // ← datosOriginales es el array completo original
            });
        ');

        return false;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla donde especificamos el rango de cuentas a imprimir
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dibujaVentanaRango()
    {

        $this->mixStyle('
            .form-group{margin-bottom:12px!important}
            legend{font-size:18px!important;margin-bottom:10px!important}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        ');

        $this->mixHtml('
            <div id="myModalRango" class="modal fade show" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="padding-top: 70px;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #9d2449; color: white;">
                            <h4 class="modal-title">Imprimir Rango de Cuentas</h4>
                            <button id="cerrarModal" type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="" id="impRangoCuentas">
                                <div class="row">
                                    <legend>Desde ...</legend>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <input id="rangoDesde" name="rangoDesde" type="text" placeholder="Cuenta" maxlength=12 class="form-control F1Catalogo" tabindex="100">
                                            <button id="mostrarDesde" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-rectangle-list"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group-sm">
                                            <input id="nombreDesde" type="text" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                                <br /><br />
                                <div class="row">
                                    <legend>Hasta ...</legend>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <input id="rangoHasta" name="rangoHasta" type="text" placeholder="Cuenta" maxlength=12 class="form-control F1Catalogo" tabindex="102">
                                            <button id="mostrarHasta" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-rectangle-list"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group-sm">
                                            <input id="nombreHasta" type="text" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="imprimirRango" class="btn btn-outline-secondary me-1" tabindex="103"><i class="fa-solid fa-print me-2"></i>Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->setIdScript('declaraRango');
        $this->mixScript('
            $("#myModalRango").on("shown.bs.modal", function () {
                $("#myModalRango").show();
                setTimeout( function() { $( "#rangoDesde" ).focus().select() }, 100 );
            });

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalRango").on("hide.bs.modal", function () {
                $("#declaraRango").remove();
            });
            $(".cerrar").click( function() {
                $("#myModalRango").hide();
            });

            $("#rangoDesde").focus( function(e) {
                mayorCuenta = "rangoDesde";
                mayorNombre = "nombreDesde";
            });
            $("#rangoHasta").focus( function(e) {
                mayorCuenta = "rangoHasta";
                mayorNombre = "nombreHasta";
            });
            // La cuenta final solo puede ser mayor o igual a la inicial
            $("#rangoDesde").blur( function(e) {
                if ( $("#rangoHasta").val() < $("#rangoDesde").val() ) {
                    $("#rangoHasta").val( $("#rangoDesde").val() );
                }
            });
            $("#rangoHasta").blur( function(e) {
                if ( $("#rangoHasta").val() < $("#rangoDesde").val() ) {
                    $("#rangoHasta").val( $("#rangoDesde").val() );
                }
            });

            // F1 muestra el catálogo */
            $("#rangoDesde").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                }
            });
            $("#mostrarDesde").click( function(e) {
                mayorCuenta = "rangoDesde";
                mayorNombre = "nombreDesde";
                e.preventDefault();
                dlg.dialog("open");
            });
            $("#rangoHasta").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                }
            });
            $("#mostrarHasta").click( function(e) {
                mayorCuenta = "rangoHasta";
                mayorNombre = "nombreHasta";
                e.preventDefault();
                dlg.dialog("open");
            });

            $("#imprimirRango").click( function(e) {
                e.preventDefault();
                if ( $("#rangoDesde").val() == "" ) {
                    notify("Debes especificar la cuenta inicial", "warning");
                    setTimeout( function() { $( "#rangoDesde" ).focus().select() }, 100 );
                    return false;
                }
                if ( $("#rangoHasta").val() == "" ) {
                    notify("Debes especificar la cuenta final", "warning");
                    setTimeout( function() { $( "#rangoHasta" ).focus().select() }, 100 );
                    return false;
                }
                if ( !( $("#rangoDesde").val().length >= 9 && $("#rangoDesde").val().length <= 12 ) ) {
                    notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    setTimeout( function() { $( "#rangoDesde" ).focus().select() }, 100 );
                    return false;
                }
                if ( !( $("#rangoHasta").val().length >= 9 && $("#rangoHasta").val().length <= 12 ) ) {
                    notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    setTimeout( function() { $( "#rangoHasta" ).focus().select() }, 100 );
                    return false;
                }
                if ( $("#imprimirRango").attr("t") == "xls" ) {
                    var form = $("<form>")
                    .attr("action", "rep/excel/scg/cuentaContable/exp_PlanCuentas.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", "planRango" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "desde").attr("value", $("#rangoDesde").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "hasta").attr("value", $("#rangoHasta").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "filtroUE").attr("value", $("#ueFiltro").val() ))
                    $("body").append(form);
                    form.submit();
                    e.stopPropagation();
                    return false;
                } else {
                    var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", "planRango" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "desde").attr("value", $("#rangoDesde").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "hasta").attr("value", $("#rangoHasta").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "filtroUE").attr("value", $("#ueFiltro").val() ))
                    $("body").append(form);
                    form.submit();
                    e.stopPropagation();
                    return false;
                }
            });
        ');
    }
    /* Eliminamos un registro del Plan de Cuentas
    La idea es dejarle a la base de datos checar si se ha usado o no ... a traves el error en caso de que
    falle alguna foreign key
    ---------------------------------------------------------------------------------------------------- */

    private function eliminar()
    {

        // tipo 1 = 1.1.1.3.1.108.001.001.0001     ---> 22 caracteres
        // tipo 2 = 8.2.1.0.0.116.001.331.ABBE0413 ---> 26 caracteres
        // tipo 3 = 4.1.4.1.0.116.002.001.01.0001  ---> 25 caracteres

        $ue = trim($this->prm->ue);

        // Convertimos a array la cuenta
        $cta = trim($this->prm->c);
        $arrCta = explode(".", $cta);

        $ctaMayor = $arrCta[0] . $arrCta[1] . $arrCta[2] . $arrCta[3] . $arrCta[4];
        $cuarto = $arrCta[5];

        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        // Determinamos la estructura de la cuenta contable
        $elementos = count($arrCta);

        // Si es de tipo 1 o 2 ...
        if ($elementos == 7) {
            // Determinamos si es de tipo 1 o 2 dependiendo del ancho del nivel de registro
            if (strlen($arrCta[5]) == 4) {
                $estructura = 1;
            } else {
                $estructura = 2;
            }
            // Por el momento sólo tenemos 3 estructuras, así que si no es de tipo 1 o 2, por eliminación es 3
        } else {
            $estructura = 3;
        }

        // Armamos el último nivel en la base de datos ...
        switch ($estructura) {
            case 1: // Tipo 1
                $registro = $arrCta[6];
                break;
            case 2: // Tipo 2
                $registro = $arrCta[6];
                break;
            case 3: // Tipo 3
                $registro = $arrCta[6] . $arrCta[7];
                break;
        }

        // No eliminaremos registros dados de alta automáticamente ( usr_id = 555 )

        $entidad = new stdClass();
        $entidad->cta_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $entidad->cta_ur_id = $_SESSION[_S]['clasAdm-ur'];
        $entidad->cta_ue_id = $ue;
        $entidad->ger_id = $genero;
        $entidad->cgp_id = $grupo;
        $entidad->rub_id = $rubro;
        $entidad->cgcu_id = $cuenta;
        $entidad->cgsc_id = $subcuenta;
        $entidad->cgc_id = $cuarto;
        $entidad->cta_id = $registro;
        $entidad->abc = 'b';
        $CuentaC = new PlanCuentas();
        $CuentaC->abc($entidad);

        $this->mixScript('
            const filaSeleccionada = document.querySelector("tr.seleccionado");
                if (filaSeleccionada) {
                    const id = filaSeleccionada.getAttribute("data-id");
                    const ue = filaSeleccionada.getAttribute("data-ue");
                    eliminarItemDeTabla(id, ue);
                }

            function eliminarItemDeTabla(id, ue) {
                const fila = document.querySelector(`tr[data-id="${id}"][data-ue="${ue}"]`);
                if (!fila) {
                    console.warn(`No se encontró una fila con data-id="${id}" y data-ue="${ue}"`);
                    return;
                }

            fila.remove();
            }
            notify("Se ha eliminado la cuenta ' . $this->prm->c . '", "success" );
        ');

        return false;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla BASICA donde modificaremos los datos del catálogo actual
// ( Lo hago así para que exista el DIV y el #id cuando se dibuje la pantalla )
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dibujaVentanaCaptura()
    {

        $pCombo = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
        
        $cmbUe = $this->dameCombo("ue", "Selecciona la Unidad Ejecutora", "",
            "SELECT ue_id, ue_nombre FROM cat_UnidadEjecutora
            WHERE gpo_id = ? AND ur_id = ? " .(isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != ""
                ? " AND ue_id =  " .$_SESSION[_S]['clasAdm-ue'] : "") .
            " ORDER BY ue_id;",$pCombo);

        $cmbCuarto = $this->dameComboVacio("cuarto",
            "Selecciona el cuarto nivel");
        $cmbQuinto = $this->dameComboVacio("quinto",
            "Selecciona el quinto nivel");

        $html = '
            <div id="myModalAddMod" class="modal fade show modal-al60porciento" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="padding-top: 70px;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #9d2449; color: white;">
                            <h4 class="modal-title">AddMod</h4>
                            <button id="cerrarModal" type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="" id="AddMod">
                                <div id="variasCuentas" class="row mb-4">
                                    <fieldset class="col-xs-12"><label><input id="altaVariasCuentas" type="checkbox">&nbsp;&nbsp;Adicionar varias cuentas</label></fieldset>
                                </div>
                                <div class="row mb-4">
                                    <fieldset class="col-md-6"><legend>Unidad Ejecutora</legend></fieldset>
                                </div>
                                <div class="row mb-4">
                                    <fieldset class="col-sm-3"><h5>Unidad Ejecutora</h5></fieldset>
                                    <fieldset class="col-sm-8">' . $cmbUe . '</fieldset>
                                </div>
                                <div class="row"></div>
                                    <div class="row">
                                        <fieldset class="col-sm-3"><legend>&nbsp;</legend></fieldset>
                                        <fieldset class="col-sm-7"><legend>Selecciona los niveles</legend></fieldset>
                                    </div>
                                <div class="row mb-4">
                                    <fieldset class="col-sm-3"><h5>Nivel de Mayor</h5></fieldset>
                                    <fieldset class="col-sm-4">
                                        <div class="input-group input-group-sm">
                                            <input id="nivelMayor" lastSearch="" name="nivelMayor" type="text" placeholder="Cuenta Mayor" maxlength=12 class="form-control F1Catalogo" >
                                            <span class="input-group-btn">
                                            <button id="mostrarNivelMayor" type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-rectangle-list"></i></button>
                                            </span>
                                        </div>
                                    </fieldset>
                                </div>
                                <span style="height:4px;"></span>
                                <div class="row mb-4">
                                    <fieldset class="col-sm-3"><h5></h5></fieldset>
                                    <fieldset class="col-sm-8"><input id="nombreNivelMayor" type="text" class="form-control" disabled></fieldset>
                                </div><span style="height:4px;"></span>
                                <div class="row mb-4">
                                    <fieldset class="col-sm-3"><h5>Cuarto Nivel</h5></fieldset>
                                    <fieldset class="col-sm-8">' . $cmbCuarto . '</fieldset>
                                </div><span style="height:4px;"></span>
                                <div class="row quinto mb-4">
                                    <fieldset class="col-sm-3"><h5>Quinto Nivel</h5></fieldset>
                                    <fieldset class="col-sm-8">' . $cmbQuinto . '</fieldset>
                                </div>
                                <br />
                                <div class="row">
                                    <fieldset class="col-sm-3"><legend>&nbsp;</legend></fieldset>
                                    <fieldset class="col-sm-9"><legend>O escribe directamente la cuenta</legend></fieldset>
                                </div>
                                <div class="row">
                                    <fieldset class="col-sm-3"><h5>Cuenta Contable</h5></fieldset>
                                    <fieldset class="col-sm-2" style="padding-right: 1px !important;">
                                        <input type="text" name="nivMayor" cve="" placeholder="Mayor" maxlength="6" value="" id="nivMayor" class="form-control">
                                    </fieldset>
                                    <fieldset class="col-sm-1" style="padding-left: 1px !important; padding-right: 1px !important;">
                                        <input type="text" name="nivCuarto" cve="" placeholder="4°" maxlength="3" value="" id="nivCuarto" class="form-control" style="padding: 6px 6px !important;">
                                    </fieldset>
                                    <fieldset class="col-sm-1 quinto" style="padding-left: 1px !important; padding-right: 1px !important;">
                                        <input type="text" name="nivQuinto" cve="" placeholder="5°" maxlength="5" value="" id="nivQuinto" class="form-control" style="padding: 6px 6px !important;">
                                    </fieldset>
                                    <fieldset class="col-sm-2" style="padding-left: 1px !important; padding-right: 1px !important;">
                                        <input type="text" name="nivRegistro" cve="" placeholder="Registro" maxlength="5" value="" id="nivRegistro" class="form-control" style="padding: 6px 6px !important;">
                                    </fieldset>
                                    <!-- pasamos aquí éstos niveles, ya que no se mostrarán -->
                                    <fieldset class="col-sm-1" style="padding-left: 1px !important; padding-right: 1px !important;">
                                        <input type="text" name="nivUR" cve="" placeholder="UR" maxlength="3" value="' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" id="nivUR" class="form-control" style="padding: 6px 6px !important; display:none">
                                    </fieldset>
                                    <fieldset class="col-sm-1" style="padding-left: 1px !important; padding-right: 1px !important;">
                                        <input type="text" name="nivUE" cve="" placeholder="UE" maxlength="3" value="" id="nivUE" class="form-control" style="padding: 6px 6px !important; display:none ">
                                    </fieldset>
                                </div><br />
                                <div class="row">
                                    <fieldset class="col-sm-3"><h5>Nombre Cta.</h5></fieldset>
                                    <fieldset class="col-sm-8">
                                        <input type="text" name="nombre" placeholder="Nombre de la clave" maxlength="200" value="" id="nombre" class="form-control">
                                    </fieldset>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="grabar" class="btn btn-outline-primary ladda-button" data-style="slide-left"><i class="fa-solid fa-plus me-2"></i>Grabar</button>
                        </div>
                    </div>
                </div>
            </div>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#myModalAddMod").on("hide.bs.modal", function () {
                $("#addModCONAC").remove();
            });
            $(".cerrar").click( function() {
                $("#myModalAddMod").hide();
            });

            $("#myModalAddMod").on("shown.bs.modal", function () {
                if ( $("#grabar").attr( "op" ) == "add" ) {
                    // Sólo si ya tiene cuenta de mayor ponemos por default el cursor en el nivel de registro
                    if ( $( "#nivelMayor" ).val() != "" ) {
                        setTimeout( function() { $( "#nivRegistro" ).focus().select() }, 100 );
                    } else {
                        setTimeout( function() { $( "#nivelMayor" ).focus().select() }, 100 );
                    }
                } else {
                    setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                }
            });

            $("#nivelMayor").focus( function(e) {
                mayorCuenta = "nivelMayor";
                mayorNombre = "nombreNivelMayor";
            });

            // F1 muestra el catálogo */
            $("#nivelMayor").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                }
            });

            $("#nivelMayor").blur( function(e) {
                // Mínimo 5 dígitos
                if ( $("#nivelMayor").val() != "" && ( $("#nivelMayor").val().length >=9 && $("#nivelMayor").val().length <= 12) ) {
                    // Si el campo de nombre de la cuenta está vacío ... o el nombre no corresponde a la cuenta de mayor actual ... le ponemos su nombre
                    //if ( $("#nombreNivelMayor").val() == "" || $("#nombreNivelMayor").attr("deCta") != $("#nivelMayor").val() ) {
                        // Verificamos la estructura de la cuenta y deshabilitamos/habilitamos el quinto nivel
                        comunica("' . $this->pantalla . '",{
                            "metodo":"checaNivelMayor",
                            "clave": $("#nivelMayor").val()
                        });
                        comunica("' . $this->pantalla . '",{
                            "metodo":"ponNombreCtaMayor",
                            cta: $("#nivelMayor").val(),
                            campo: "nombreNivelMayor"
                        });
                        $("#nivMayor").val( $(this).val() );
                        comunica("' . $this->pantalla . '",{
                            "metodo":"llenaCombo",
                            "cmb": "cuarto",
                            "mayor": $("#nivelMayor").val(),
                            "msg": "Selecciona el cuarto nivel"
                        });
                        cta = $("#ctaMayor").val();
                        setTimeout( function() { $("#cuarto").change(); }, 200 );
                        // Obtenemos el ancho y el tipo de cuenta
                        nTipoCuenta = $("#nombreNivelMayor").attr("deEst");
                        // Sólo las cuentas de tipo 3 tienen quinto nivel
                        if ( nTipoCuenta == 3 ) {
                            $(".quinto").css("display", "block");
                            $("#nivQuinto").val( "" ).removeAttr("disabled");
                            $("#quinto").val( "" ).removeAttr("disabled");
                            $("#nivQuinto").trigger( "change");
                        } else {
                            $(".quinto").css("display", "none");
                        }
                    //}
                }
            });

            $("#mostrarNivelMayor").click( function(e) {
                e.preventDefault();
                dlg.dialog("open");
            });

            $("#ue").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#nivUE").val("");
                } else {
                    $("#nivUE").val( $("#ue").val() );
                }
            });

            $("#cuarto").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#nivCuarto").val("");
                } else {
                    comunica("' . $this->pantalla . '",{
                        "metodo":"llenaCombo",
                        "cmb": "quinto",
                        "mayor": $("#nivelMayor").val(),
                        "cgc": $("#cuarto").val(),
                        "msg": "Selecciona el cuarto nivel" });
                    $("#nivCuarto").val( $("#cuarto").val() );
                }
            });

            $("#nivMayor").blur( function(e) {
                strClave = this.value;
                $("#nivelMayor").val( strClave );
                $("#nivelMayor"	).blur();
                // Verificamos la estructura de la cuenta y deshabilitamos/habilitamos el quinto nivel
                comunica("' . $this->pantalla . '",{
                    "metodo":"checaNivelMayor",
                    "clave": strClave });
                //setTimeout( function() { $("#cuarto").change(); }, 200 );
            });

            // Marcos: 21/03/2017
            // Validamos que a nivel registro, sólo escriban números y letras
            $("#nivRegistro").blur( function(e) {
                strClave = this.value;
                if ( strClave.trim() !== "+" ) {
                    var re = /^[0-9A-Za-z]+$/;
                        if ( !re.test(strClave) ) {
                            notify("En el nivel de registro sólo puedes escribir números y letras", "error");
                            $(this).focus();
                        }
                }
            });
            $("#nivUE").blur( function(e) {
                $("#ue").val( this.value );
            });
            $("#nivCuarto").blur( function(e) {
                $("#cuarto").val( this.value );
            });

            $("#grabar").click( function(e) {
                if ( $("#grabar").attr("op") == "mod" ) {
                    // Obtenemos el array de los datos de la linea actual (que es la que estamos editando)
                    const filaSeleccionada = document.querySelector("#tabla-tbl-plan tbody tr.seleccionable.seleccionado");
                        
                        // Si no hay una cuenta seleccionada, no hacemos nada
                        if (!filaSeleccionada) {
                            return false;
                        }

                        // Obtener datos de la fila
                        const celdas = filaSeleccionada.querySelectorAll("td");
                        console.log(celdas);

                        const row = {
                            c: celdas[1]?.textContent.trim(),
                            n: celdas[2]?.textContent.trim(),
                            ue: celdas[3]?.textContent.trim()
                        };
                    comunica("' . $this->pantalla . '",{"metodo":"modPlanCuentas", "c": row.c, "n": $("#nombre").val(), "id": id, "ue": $("#nivUE").val() });
                } else {
                    comunica("' . $this->pantalla . '",{"metodo":"addPlanCuentas", "mayor": $("#nivMayor").val(), "ue": $("#nivUE").val(), "cuarto": $("#nivCuarto").val(), "quinto": $("#nivQuinto").val(), "registro": $("#nivRegistro").val(), "n": $("#nombre").val() });
                }
            });
        ');
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla ESPECIFICA donde modificaremos los datos del catálogo actual
// Lo hago así porque dependiendo del catálogo es como se dibujará y se comportará la forma
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dameCombo($id, $mensaje, $seleccionado, $sql,$pCombo)
    {

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($pCombo);
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<select name="' . $id . '" id="' . $id . '" disabled class="form-control"><option value="X">ERROR. NO EXISTE INFORMACION</option></select>';
                break;

            default:
                $strCombo = '<select name="' . $id . '" id="' . $id . '" class="form-control">';
                $bolSoloUnaVez = true;
                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option ' . (($bolSoloUnaVez) ? 'selected' : '') . ' value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                    $bolSoloUnaVez = false;
                }
                $strCombo .= '</select>';
                break;
        }

        return $strCombo;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Llenamos un combo basandonos en la seleccion del actual
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function llenaCombo()
    {

        $combo = (isset($this->prm->cmb) ? $this->prm->cmb : "");
        $mayor = (isset($this->prm->mayor) ? $this->prm->mayor : "");
        $arrCta = explode(".", $mayor);
        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR");
            ');
            return false;
        }
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
            ');
            return false;
        }
        $cgc = (isset($this->prm->cgc) ? $this->prm->cgc : "");
        $mens = (isset($this->prm->msg) ? $this->prm->msg : "");

        switch ($combo) {
            case "cuarto":
                $sql = "SELECT cgc_" . ((APP_EJERCICIO >= 2014) ? "id" : "cuarto") . ", cgc_nombre FROM cg_CuentaCuarto WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? ORDER BY cgc_" . ((APP_EJERCICIO >= 2014) ? "id" : "cuarto") . ";";
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];
                break;
            case "quinto":
                $sql = "SELECT cgq_id, cgq_nombre FROM cg_CuentaQuinto WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? ORDER BY cgq_id;";
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cgc];
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<option value="">ERROR. NO EXISTE INFORMACION</option>';
                break;
            default:
                $strCombo = '';
                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                }
                break;
        }

        // Para que no marque error, le quitamos los tabuladores, y avances de línea
        $strCombo = str_replace('"', '\"', $strCombo);

        $strCombo = str_replace("\t", "", $strCombo);

        $strCombo = str_replace("\n", "", $strCombo);

        $strCombo = str_replace("\r", "", $strCombo);

        $this->mixScript('
            $("#' . $combo . '").empty().append("' . $strCombo . '");
        ');

        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Si escriben directamente la clave, llenamos los combos con esos valores ...
// MENOS el de GENERO, porque ese de por si ya está lleno con los valores ...
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function PonClaveEnCombos()
    {

        if ($this->prm->cbo == "conac") {

            $genero = trim(substr($this->prm->clave, 0, 1));
            $grupo = trim(substr($this->prm->clave, 1, 1));
            $rubro = trim(substr($this->prm->clave, 2, 1));
            $cuenta = trim(substr($this->prm->clave, 3, 1));
            $subcuenta = trim(substr($this->prm->clave, 4, 2));

            // Ponemos los combos cuyos valores existan ...
            if ($genero != "") {
                $this->llenaCombo("genero", $genero, "", "", "", "",
                    "Selecciona el Género");
            }
            if ($grupo != "") {
                $this->llenaCombo("grupo", $genero, $grupo, "", "", "",
                    "Selecciona el Grupo");
            }
            if ($rubro != "") {
                $this->llenaCombo("rubro", $genero, $grupo, $rubro, "", "",
                    "Selecciona el Rubro");
            }
            if ($cuenta != "") {
                $this->llenaCombo("cuenta", $genero, $grupo, $rubro, $cuenta,
                    "", "Selecciona el Cuenta");
            }
            // Además ... si viene la subcuenta hay que recargar el combo de cuartos niveles
            if ($subcuenta != "") {
                $this->llenaCombo("subcuenta", $genero, $grupo, $rubro, $cuenta,
                    $subcuenta, "Selecciona el Subcuenta");
                $this->llenaCombo("cuarto", $genero, $grupo, $rubro, $cuenta,
                    $subcuenta, "Selecciona el Cuarto Nivel");
            }

            // Limpiamos los que no existan
            if ($genero == "") {
                $this->mixScript('$("#genero").val("");');
            }
            if ($grupo == "") {
                $this->mixScript('$("#grupo").empty();');
            }
            if ($rubro == "") {
                $this->mixScript('$("#rubro").empty();');
            }
            if ($cuenta == "") {
                $this->mixScript('$("#cuenta").empty();');
            }
            if ($subcuenta == "") {
                $this->mixScript('$("#subcuenta").empty();   $("#cuarto").empty();');
            }

            // Si seleccionan uno ... llenamos el combo siguiente
            if ($genero != "" && $grupo == "") {
                $this->llenaCombo("grupo", $genero, "", "", "", "",
                    "Selecciona el Grupo");
            }
            if ($grupo != "" && $rubro == "") {
                $this->llenaCombo("rubro", $genero, $grupo, "", "", "",
                    "Selecciona el Rubro");
            }
            if ($rubro != "" && $cuenta == "") {
                $this->llenaCombo("cuenta", $genero, $grupo, $rubro, "", "",
                    "Selecciona el Cuenta");
            }
            if ($cuenta != "" && $subcuenta == "") {
                $this->llenaCombo("subcuenta", $genero, $grupo, $rubro, $cuenta,
                    "", "Selecciona el Subcuenta");
            }

            $this->mixScript('
                try {
                    $("#genero").val( ' . $genero . ' );
                    $("#grupo").val( ' . $grupo . ' );
                    $("#rubro").val( ' . $rubro . ' );
                    $("#cuenta").val( ' . $cuenta . ' );
                    $("#subcuenta").val( ' . $subcuenta . ' );
                } catch( error ) {
                    // nada
                }
            ');
        }
        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla ESPECIFICA donde modificaremos los datos del catálogo actual
// Lo hago así porque dependiendo del catálogo es como se dibujará y se comportará la forma
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dameComboVacio($id, $mensaje)
    {
        $strCombo = '<select name="' . $id . '" id="' . $id . '" class="form-control"></select>';
        return $strCombo;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Grabamos un cambio en el nombre de una cuenta, en el Plan de Cuentas
// parametros: cuenta, nombre, id
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function modPlanCuentas()
    {

        // tipo 1 = 1.1.1.3.1.108.001.001.0001     ---> 22 caracteres
        // tipo 2 = 8.2.1.0.0.116.001.331.ABBE0413 ---> 26 caracteres
        // tipo 3 = 4.1.4.1.0.116.002.001.01.0001  ---> 25 caracteres

        $ue = $this->prm->ue;

        // Convertimos a array la cuenta
        $cta = trim($this->prm->c);
        $arrCta = explode(".", $cta);

        $ctaMayor = $arrCta[0] . $arrCta[1] . $arrCta[2] . $arrCta[3] . $arrCta[4];
        $cuarto = $arrCta[5];

        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        // Determinamos la estructura de la cuenta contable
        $elementos = count($arrCta);

        // Si es de tipo 1 o 2 ...
        if ($elementos == 7) {
            // Determinamos si es de tipo 1 o 2 dependiendo del ancho del nivel de registro
            if (strlen($arrCta[6]) == 4) {
                $estructura = 1;
            } else {
                $estructura = 2;
            }
            // Por el momento sólo tenemos 3 estructuras, así que si no es de tipo 1 o 2, por eliminación es 3
        } else {
            $estructura = 3;
        }

        // Armamos el último nivel en la base de datos ...
        switch ($estructura) {
            case 1: // Tipo 1
                $registro = $arrCta[6];
                break;
            case 2: // Tipo 2
                $registro = $arrCta[6];
                break;
            case 3: // Tipo 3
                $registro = $arrCta[6] . $arrCta[7];
                break;
        }

        $tieneBloqueo = false;

        if ($tieneBloqueo) {
            $this->mixScript('
                notify( "La cuenta no está disponible para su uso", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        // Sólo la UR 116 puede usar la cuenta 11121
        if ($ctaMayor == "11121") {
            if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] != "116") {
                $this->mixScript('
                    notify( "Por favor, usa la cuenta 11131 para controlar la cuenta de Bancos", "error" );
                    setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                    window.espera.stop();
                ');
                return false;
            }
        }
        $nombrecta = "";
        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la cuenta NO puede quedar vacío", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        $nombrecta = str_replace('"', '\"', $this->prm->n);

        $entidad = new stdClass();
        $entidad->cta_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $entidad->cta_ur_id = $_SESSION[_S]['clasAdm-ur'];
        $entidad->cta_ue_id = $ue;
        $entidad->ger_id = $genero;
        $entidad->cgp_id = $grupo;
        $entidad->rub_id = $rubro;
        $entidad->cgcu_id = $cuenta;
        $entidad->cgsc_id = $subcuenta;
        $entidad->cgc_id = $cuarto;
        $entidad->cta_id = $registro;
        $entidad->cta_nombre = strtoupper($nombrecta);
        $entidad->abc = 'c';
        $CuentaC = new PlanCuentas();

        // Marcos: 25/mayo/2015
        // Se mandan estos dos parámetros, ya que no está actualizando en bitácora correctamente
        // los id de que usuario está modificando la información

        $CuentaC->abc($entidad);

        $this->mixScript('
        const filaSeleccionada = document.querySelector("tr.seleccionado");
            if (filaSeleccionada) {
                const id = filaSeleccionada.getAttribute("data-id");
                const ue = filaSeleccionada.getAttribute("data-ue");
                    actualizarItemEnTabla(id, ue,"' . $this->prm->n . '");
            }

            function actualizarItemEnTabla(id, ue, nuevoValorN) {
                const fila = document.querySelector(`tr[data-id="${id}"][data-ue="${ue}"]`);
                if (!fila) {
                    console.warn(`No se encontró una fila con data-id="${id}" y data-ue="${ue}"`);
                    return;
                }

                const celdas = fila.getElementsByTagName("td");
                if (celdas.length >= 3) {
                    celdas[2].textContent = nuevoValorN.toUpperCase();
                }
            }

            // Terminamos
            notify("Se ha actualizado la información", "success" );
            $("#myModalAddMod").hide();
            window.espera.stop();
        ');

        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Adicionamos una nueva contable al Plan de Cuentas
// parametros: mayor, ue, cuarto, quinto, registro, nombre
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function addPlanCuentas()
    {

        $cuentaMayor = $this->prm->mayor;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR", "error" );
                setTimeout( function() { $( "#nivMayor" ).focus().select(); window.espera.stop(); }, 100 );
            ');
            return false;
        }
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números", "error" );
                setTimeout( function() { $( "#nivMayor" ).focus().select(); window.espera.stop(); }, 100 );
            ');
            return false;
        }
        $cuentacompleta = $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta;
        $quinto = (isset($this->prm->quinto) ? $this->prm->quinto : "");

        if (strlen($this->prm->ue) != 3) {
            $this->mixScript('notify( "El ancho del tercer nivel (UE) es de 3 dígitos", "error" );   setTimeout( function() { $( "#nivUE" ).focus().select(); window.espera.stop(); }, 100 ); ');
            return false;
        }
        if (strlen($this->prm->cuarto) != 3) {
            $this->mixScript('notify( "El ancho del cuarto nivel es de 4 dígitos", "error" );   setTimeout( function() { $( "#nivCuarto" ).focus().select(); window.espera.stop(); }, 100 ); ');
            return false;
        }

        // Sólo la UR 116 puede usar la cuenta 11121
        if ($this->prm->mayor == "1.1.1.2.1") {
            if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] != "116") {
                $this->mixScript('
                    notify( "Por favor, usa la cuenta 11131 para controlar la cuenta de Bancos", "error" );
                    setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                    window.espera.stop();
                ');
                return false;
            }
        }

        // Si trae quinto nivel, verificamos que tenga 2 dígitos
        if ($quinto != "") {
            if (strlen($quinto) != 2) {
                $this->mixScript('notify( "El ancho del quinto nivel es de 2 dígitos", "error" );   setTimeout( function() { $( "#nivQuinto" ).focus().select(); window.espera.stop(); }, 100 ); ');
                return false;
            }
        }

        // Si en el nivel de registro escriben +, buscaremos el número secuencial que le toque a esta nueva cuenta
        if (trim($this->prm->registro) != "+" && strlen(trim($this->prm->registro))
            < 4) {
            $this->mixScript('notify( "Revisa el ancho del nivel de registro", "error" );   setTimeout( function() { $( "#nivRegistro" ).focus().select(); window.espera.stop(); }, 100 ); ');
            return false;
        }

        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la cuenta NO puede quedar vacío", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        /// Codigo para validar si existeun bloqueo en la cuenta contable
        $tieneBloqueo = false;
        $sql = 'SELECT cgc_bloqueo FROM cg_CuentaCuarto WHERE '
            . ' ger_id= ? AND cgp_id= ? AND  rub_id= ? AND  cgcu_id= ? AND  '
            . ' cgsc_id= ? AND cgc_id = ?;';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $this->prm->cuarto];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $row = $conexion->extraer();

        $var_cgc_bloqueo = $row['cgc_bloqueo'];
        if ($var_cgc_bloqueo == 1) {
            $sql = 'SELECT cgc_ordenBloqueo FROM vin_BloquearCuentaSefin WHERE '
                . ' gpo_id= ? AND ur_id= ? AND  '
                . ' ger_id= ? AND cgp_id= ? AND  rub_id= ? AND  cgcu_id=? AND  '
                . ' cgsc_id= ? AND cgc_id = ?;';
            $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $genero, $grupo, $rubro, $cuenta, $subcuenta, $this->prm->cuarto];
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            $conexion->desconectar();
            $row = $conexion->extraer();
            $var_cgc_ordenBloqueo = $row['cgc_ordenBloqueo'];

            if ($var_cgc_ordenBloqueo == 0) {
                $tieneBloqueo = true;
            }
        }

        if ($tieneBloqueo) {
            $this->mixScript('
                notify( "La cuenta no está disponible para su uso", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                window.espera.stop();
            ');
            return false;
        }
        if (trim($this->prm->registro) == "+") {
            // Si tiene quinto nivel tambien hay que incluirlo
            if ($quinto != "") {
                $sql = 'SELECT IFNULL( MAX( substring( cta_id, 3 ) ), 0 ) +1 AS nuevoNum FROM cg_PlanCuentas WHERE cta_gpo_id = ? AND cta_ur_id = ? AND cta_ue_id = ? AND ger_id = ? and cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? AND LEFT( cta_id, 2 ) = ?;';
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->ue, $genero, $grupo, $rubro, $cuenta, $subcuenta, $this->prm->cuarto, $quinto];
            } else {
                $sql = 'SELECT IFNULL( MAX( cta_id ), 0 ) +1 AS nuevoNum FROM cg_PlanCuentas WHERE cta_gpo_id = ? AND cta_ur_id = ? AND cta_ue_id = ? AND ger_id = ? and cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? AND LEFT( cta_id, 2 ) <> "99";'; // Para ignorar las genéricas que a nivel de registro son 9999 limitamos cta_id
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->ue, $genero, $grupo, $rubro, $cuenta, $subcuenta, $this->prm->cuarto];
            }

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            $conexion->desconectar();

            $row = $conexion->extraer();

            $registro = $quinto . str_pad($row['nuevoNum'], 4, "0", STR_PAD_LEFT);
        } else {

            // Siempre le anteponemos $quinto porque si no lo trae, vale "" y entonces no adiciona nada :D
            $registro = $quinto . mysql_escape_mimic(strtoupper($this->prm->registro));
        }
        $ctaCuenta = $this->prm->mayor . "." . $this->prm->cuarto . "." . $registro;
        $cuentacompleta .= "." . $this->prm->cuarto . "." . $registro;

        $nombrecta = str_replace("'", "\'", $this->prm->n);
        // Determinamos
        $entidad = new stdClass();
        $entidad->cta_gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $entidad->cta_ur_id = $_SESSION[_S]['clasAdm-ur'];
        $entidad->cta_ue_id = $this->prm->ue;
        $entidad->ger_id = $genero;
        $entidad->cgp_id = $grupo;
        $entidad->rub_id = $rubro;
        $entidad->cgcu_id = $cuenta;
        $entidad->cgsc_id = $subcuenta;
        $entidad->cgc_id = $this->prm->cuarto;
        $entidad->cta_id = $registro;
        $entidad->cta_cuenta = $ctaCuenta;
        $entidad->agc_id = "GRAL";
        $entidad->cta_nombre = mysql_escape_mimic(strtoupper($nombrecta));

        $entidad->abc = 'a';
        $CuentaC = new PlanCuentas();
        $CuentaC->conectar();
        $CuentaC->setSql("	SELECT cgsc_id  FROM ".BD_CTRL.".cg_PlanCuentas
            WHERE cta_gpo_id = ? AND cta_ur_id = ? AND
            " . (isset($entidad->cta_ue_id) && $entidad->cta_ue_id != "" ? " cta_ue_id= ? AND " : " ") . "
            ger_id = ?  AND cgp_id = ? AND rub_id = ? AND cgcu_id = ?
            AND cgsc_id = ? AND cgc_id= ? AND cta_id= ? ;
        ");
        $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $entidad->cta_ue_id, $genero, $grupo, $rubro, $cuenta, $subcuenta, $this->prm->cuarto, $registro];
        $CuentaC->consultar($parametros);
        $numreg = $CuentaC->getNumeroRegistros();
        $CuentaC->desconectar();
        if ($numreg == 0) {
            $CuentaC->abc($entidad);

            $this->mixScript('
                var strNombre = "' . str_replace('"', '\"', strtoupper($nombrecta)) . '";
                agregarFila("' . $ctaCuenta . '", strNombre, "' . $this->prm->ue . '", "' . $this->prm->cuarto . '");
                
                function agregarFila(id, n, ue, grupo) {
                    const tbody = document.querySelector("#tabla-tbl-plan tbody");
                    const nuevaFila = document.createElement("tr");
                    const groupId = `group-${grupo}`; // Esto coincide con las clases del grupo en la tabla

                    // Asignar clases y atributos
                    nuevaFila.classList.add("group-row", groupId, "seleccionable");
                    nuevaFila.setAttribute("data-id", id);
                    nuevaFila.setAttribute("data-ue", ue);

                    //Evento para que sea seleccionable la fila generada
                    nuevaFila.addEventListener("click", function () {
                        document.querySelectorAll(".seleccionable").forEach(r => r.classList.remove("seleccionado"));
                        this.classList.add("seleccionado");
                    });

                    // Crear celdas
                    const celdaVacia = document.createElement("td");
                    celdaVacia.textContent = "";
                    nuevaFila.appendChild(celdaVacia);

                    const celdaId = document.createElement("td");
                    celdaId.textContent = id;
                    nuevaFila.appendChild(celdaId);

                    const celdaN = document.createElement("td");
                    celdaN.textContent = n.toUpperCase();
                    nuevaFila.appendChild(celdaN);

                    const celdaUe = document.createElement("td");
                    celdaUe.textContent = ue;
                    nuevaFila.appendChild(celdaUe);

                    // Buscar la última fila del grupo para insertar debajo
                    const grupoFilas = tbody.querySelectorAll(`tr.${groupId}`);
                    if (grupoFilas.length > 0) {
                        const ultimaFilaGrupo = grupoFilas[grupoFilas.length - 1];
                        ultimaFilaGrupo.insertAdjacentElement("afterend", nuevaFila);
                    } else {
                        // Si el grupo no tiene filas aún, buscar el header del grupo
                        const headerRow = tbody.querySelector(`tr[data-group="${groupId}"]`);
                        if (headerRow) {
                            headerRow.insertAdjacentElement("afterend", nuevaFila);
                        } else {
                             // Como último recurso, agregar al final
                            tbody.appendChild(nuevaFila);
                        }
                    }
                }

                // Terminamos
                notify("Se ha insertado la información", "success" );
                // Si quieren adicionar varias cuentas ... seguimos en la ventana
                if ( $("#variasCuentas").css("display") ==  "block" && $("#altaVariasCuentas").is(":checked") ) {
                    setTimeout( function() { $( "#nivRegistro" ).focus().select() }, 100 );
                } else {
                    $("#myModalAddMod").hide();
                }
                window.espera.stop();
            ');
        } else {
            $this->mixScript('
                notify("El nivel de Registro ya Existe", "warning" );
                window.espera.stop();
            ');
        }
        return true;
    }
    /* Cuando terminen de definir el nivel de mayor, checamos la estructura de la cuenta para determinar
    si se muestra el campo de quinto nivel
    ---------------------------------------------------------------------------------------------------- */

    private function checaNivelMayor()
    {

        $clave = $this->prm->clave;
        $cuentaMayor = $this->prm->clave;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR");
            ');
            return false;
        }
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
            ');
            return false;
        }

        // Obtenemos la estructura de la cuenta
        $sql = 'SELECT ecu_id FROM cat_CgSubcuenta WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?;';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $row = $conexion->extraer();

        switch ($row['ecu_id']) {
            // Estructura 1 no lleva quinto nivel
            case "1":
                $this->mixScript('
                    $("#nivQuinto").val("");
                    $(".quinto").css("display", "none");
                ');
                break;
            // Las cuentas tipo 2 son presupuestales y NO DEBERÍAN DARSE DE ALTA MANUALMENTE
            case "2":
                // Verificado por Israel con Plinio el 25/05/2015
                // Podemos adicionar cuentas de la 56110 solamente
                // Marcos: 27/sep/2016
                // Israel solicita (ya que hay un ticket de Hilda) que también la cuenta 55490 tenga el
                // mismo comportamiento

                if ($clave != "56110" && $clave != "55490") {
                    $this->mixScript('
                        alertify.prompt("¿ Cuál es la palabra mágica ?", "", function(evt, value) {
                            // nada todavía
                            $("#nivMayor").val("");
                            $("#nivelMayor").val("");
                        });
                    ');
                }
                break;
            // Tipo 3 para cuentas de ingresos que llevan 6 niveles
            case "3":
                $this->mixScript('
                    $(".quinto").css("display", "block");
                ');
                break;
            default:
                $this->mixScript('
                    notify("La cuenta ' . $clave . ' no existe en el Catálogo Maestro" );
                ');
                break;
        }
    }
    /* Ponemos en un campo de texto ... el nombre de una cuenta de mayor dada
    ---------------------------------------------------------------------------------------------------- */

    private function ponNombreCtaMayor()
    {

        $cuentaMayor = $this->prm->cta;
        $arrCta = explode(".", $cuentaMayor);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        if ($elementos == 5) {
            $genero = $arrCta[0];
            $grupo = $arrCta[1];
            $rubro = $arrCta[2];
            $cuenta = $arrCta[3];
            $subcuenta = $arrCta[4];
        } else {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR");
            ');
            return false;
        }
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
            ');
            return false;
        }

        $sql = 'SELECT  cgsc_nombre AS nombre, nat_id, ecu_id,tcu_id, pcu_id FROM cat_CgSubcuenta  '
            . 'WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?;';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();

        if ($nRegistros <= 0) {
            $nombre = "";
        } else {
            $row = $conexion->extraer();
            $nombre = $row['nombre'];
        }

        $this->mixScript('
            var campo = "' . $this->prm->campo . '";
            $("#" + campo ).val( "' . $nombre . '" );
            $("#" + campo ).attr( "deCta", "' . $this->prm->cta . '" );
            
            if("'.$this->prm->campo.'" == "nombreMayor"){
                $("#ctaMayor").val("'.$this->prm->cta.'");
            } else if ("'.$this->prm->campo.'" == "nombreDesde"){
                $("#rangoDesde").val("'.$this->prm->cta.'");
            } else if ("'.$this->prm->campo.'" == "nombreHasta"){
                $("#rangoHasta").val("'.$this->prm->cta.'");
            } else if ("'.$this->prm->campo.'" == "nivelMayor"){
                $("#nivelMayor").val("'.$this->prm->cta.'");
            }
        ');
    }

    // Arma un combo con las Unidades Ejecutoras a mostrar para el filtro
    // Se hizo así porque si uso ponCombo() tal y como viene en otros .phps nomás no me queda
    // cuando la consulta regresa un solo registro, me pone el combo y me pone un textbox
    // ------------------------------------------------------------------------------------------------------------------------
    private function ComboUnidadesEjecutoras()
    {

        $sql = "SELECT ue_id, ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ?";
        
        // Si es un usuario de nivel UE, forzamos a que siempre vean sólo su UE
        $admiva = [];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $sql .= " AND ue_id = ?";
            $parametro = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $_SESSION[_S]['clasAdm-ue']];
        }else{
            $parametro = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametro);
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<select name="ueFiltro" id="ueFiltro" disabled class="form-control"><option value="XXX">ERROR</option></select>';
                break;
            case 1:
                $strCombo = '<select name="ueFiltro" id="ueFiltro" disabled class="form-select">';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['ue_id'] . '">' . $row['ue_id'] . " - " . $row['ue_nombre'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
            default:
                $strCombo = '<select name="ueFiltro" id="ueFiltro" class="form-select">';
                $strCombo .= '<option value="">MOSTRAR TODAS LAS EJECUTORAS</option>';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['ue_id'] . '">' . $row['ue_id'] . " - " . $row['ue_nombre'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
        }

        return $strCombo;
    }

    // Arma un combo con las agrupaciones de lac cuentas a mostrar para el filtro
    // ------------------------------------------------------------------------------------------------------------------------
    private function ComboAgrupacionCuentas()
    {

        $sql = "SELECT agc_id, agc_nombre FROM cat_AgrupacionCuentas; ";
        // Si es un usuario de nivel UE, forzamos a que siempre vean sólo su UE

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<select name="AgCtaFiltro" id="AgCtaFiltro" disabled class="form-control"><option value="XXX">ERROR</option></select>';
                break;
            case 1:
                $strCombo = '<select name="AgCtaFiltro" id="AgCtaFiltro" disabled class="form-control">';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['agc_id'] . '">' . $row['agc_nombre'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
            default:
                $strCombo = '<select name="AgCtaFiltro" id="AgCtaFiltro" class="form-select">';
                $strCombo .= '<option value="">MOSTRAR TODAS LAS AGRUPACIONES</option>';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['agc_id'] . '">' . $row['agc_nombre'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
        }

        return $strCombo;
    }

    // Validamos si el cuarto nivel que pretenden modificar es un COG... y si lo es no permitirmos la modificación
    // -------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------
    public function modificar()
    {
        $cuenta = $this->prm->c;
        $nombre = $this->prm->nom;

        $arrCta = explode(".", $cuenta);
        $cuarto = $arrCta[5]; //cgc_id -- pg_id
        $vger_id = $arrCta[0];
        $vcgp_id = $arrCta[1];
        $vrub_id = $arrCta[2];
        $vcgcu_id = $arrCta[3];
        $vcgsc_id = $arrCta[4];
        $vcta_id = strtoupper($arrCta[6]);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);

        // Determinamos si el cuarto nivel puede ser un COG
        if (is_numeric($cuarto)) {
            // Debe estar en el rango entre 100 y 999 para presumir que pudiera ser una clave COG
            $nCuartoNivel = intval($cuarto);

            $veces = 0;
            $mesaje = "";

            if (($vger_id == 8 && $vcgp_id == 2)) {
                $veces = 1;
                $mesaje = " Las Cuentas de Orden ";
            }
            if (empty($veces) || $veces == 0) {

                $sql = "SELECT ecu_id FROM  ".BD_CTRL.".cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    LIMIT 1";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);
                $estruc = 2;
                $row = $conexion->extraer();

                $estruc = $row['ecu_id'];
                $conexion->desconectar();
                if (empty($estruc) || $estruc == 2) {
                    $veces = 1;
                    $mesaje = "Las Cuentas contables de Claves Presupuestales ";
                }
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT cog_cuenta  FROM ".BD_CTRL.".vin_Cog
                    WHERE ger_id = ?
                    AND cgp_id = ? AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    AND pg_id = ? LIMIT 1;";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas del COG ";
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT cog_cuenta FROM ".BD_CTRL.".vin_CogContraCuenta
                        WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND pg_id  = ? LIMIT  1;";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas del COG ";
            }

            if (empty($veces) || $veces == 0) {
                $cuenta11239 = ($vger_id . $vcgp_id . $vrub_id . $vcgcu_id . $vcgsc_id . "." . $cuarto . "");
                $cuenta1123901 = ($vger_id . $vcgp_id . $vrub_id . $vcgcu_id . $vcgsc_id . "." . $cuarto . "." . $vcta_id . "");
                if (($cuenta11239 == "11239.010") && $cuenta1123901 != "11239.010.0001") {

                    $sql = "SELECT cta_id  FROM ".BD_CTRL.".vin_Retencion
                        WHERE  cta_id = ? LIMIT 1;";
                    $parametro = [$vcta_id];
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql($sql);
                    $conexion->consultar($parametro);

                    $row = $conexion->extraer();
                    $veces = $conexion->getNumeroRegistros();

                    $conexion->desconectar();
                    $mesaje = "Las Cuentas derivadas de Retenciones ";
                }
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT rfc_cta_id FROM ".BD_CTRL.".cat_Proveedor
                    WHERE rfc_cta_id = ? LIMIT  1;";
                $parametro = [$vcta_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametro);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas de Proveedores ";
            }

            // Si es del COG, le avisamos que no puede pasar ...
            if ($veces > 0) {
                $this->mixScript('
                    notify( "' . $mesaje . ' no pueden ser  Modificadas por el Usuario", "warning");
                ');
                return false;
            }
        }

        // Determinamos la estructura de la cuenta
        $estructura = 1;
        if ($elementos == 7) {
            // Determinamos si es de tipo 1 o 2 dependiendo del ancho del nivel de registro
            if (strlen($arrCta[6]) == 4) {
                $estructura = 1;
            } else {
                $estructura = 2;
            }
            // Por el momento sólo tenemos 3 estructuras, así que si no es de tipo 1 o 2, por eliminación es 3
        } else {
            $estructura = 3;
        }

        if ($estructura == 3) {
            $quinto = $arrCta[6];
        } else {
            $quinto = "";
        }

        // Si no es numérico el cuarto nivel, o si no está dentro del rango del COG... permitirmos su modificación
        $strScript = '
            $(".modal-title").text("Modificar Cuenta Contable");
            $("#nivelMayor").attr("disabled", "disabled");
            $("#mostrarNivelMayor").attr("disabled", "disabled");
            $("#nivMayor").val( "' . $arrCta[0] . "." . $arrCta[1] . "." . $arrCta[2] . "." . $arrCta[3] . "." . $arrCta[4] . '" ).attr("disabled", "disabled");
            $("#nivUE").val( "' . $this->prm->ue . '" ).hide();
            // Sólo las cuentas de tipo 3 tienen quinto nivel
            ' . (($estructura == 3) ?
            '$(".quinto").css("display", "block");
            $("#nivQuinto").val( "' . $quinto . '" ).attr("disabled", "disabled");
            $("#quinto").val( "' . $quinto . '" ).attr("disabled", "disabled");
            $("#nivQuinto").trigger( "change");
            ' :
            '$(".quinto").css("display", "none");');
        $strScriptquinto = '';
        // Ponemos el nivel de registro, de acuerto al tipo de cuenta
        switch ($estructura) {
            case 1:
                $strScript .= '
                    $("#nivRegistro").attr( "maxlength", "4" );
                    $("#nivRegistro").val( "' . $arrCta[6] . '" ).attr("disabled", "disabled");
                ';
                break;
            case 2:
                $strScript .= '
                    $("#nivRegistro").attr( "maxlength", "9" );
                    $("#nivRegistro").val( "' . $arrCta[6] . '" ).attr("disabled", "disabled");
                ';
                break;
            case 3:
                $strScript .= '
                    $("#nivRegistro").attr( "maxlength", "4" );
                    $("#nivRegistro").val( "' . $arrCta[7] . '" ).attr("disabled", "disabled");
                ';
                $strScriptquinto = '$("#quinto").val( "' . $quinto . '" );';
                break;
        }

        $strScript .= '
            // Actualizamos los combox con el valor de los campos de texto
            $("#nivMayor").trigger( "blur");
            $("#nivUE").trigger( "blur");
            setTimeout( function() {
                $("#cuarto").val( "' . $arrCta[5] . '" );
                    ' . $strScriptquinto . '
                $("#nivCuarto").val( "' . $arrCta[5] . '" ).attr("disabled", "disabled");
            }, 500 );

            // Anulamos la edición de combos
            $("#genero").attr( "disabled", "disabled");
            $("#grupo").attr( "disabled", "disabled");
            $("#rubro").attr( "disabled", "disabled");
            $("#cuenta").attr( "disabled", "disabled");
            $("#subcuenta").attr( "disabled", "disabled");
            $("#ue").attr( "disabled", "disabled");
            $("#cuarto").attr( "disabled", "disabled");

            $("#nombre").val( "'.$nombre.'" );
            //$("#nombre").attr( "disabled", "disabled");
            $("#grabar").attr( "op", "mod" );
            $("#myModalAddMod").show()
            setTimeout( function() { $( "#nombre" ).focus() }, 100 );
        ';

        $this->mixScript($strScript);
    }

    // Validamos si el cuarto nivel que pretenden eliminar es un COG... y si lo es no permitirmos la eliminación
    // -------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------
    public function eliminarCuenta()
    {

        $cuenta = $this->prm->c;
        $nombre = $this->prm->nom;
        $id = $this->prm->id;
        $ue = $this->prm->ue;

        $arrCta = explode(".", $cuenta);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);

        $cuarto = $arrCta[5]; //cgc_id -- pg_id
        $vger_id = $arrCta[0];
        $vcgp_id = $arrCta[1];
        $vrub_id = $arrCta[2];
        $vcgcu_id = $arrCta[3];
        $vcgsc_id = $arrCta[4];
        $vcta_id = $arrCta[6];
        $vcta_id = strtoupper($vcta_id);

        // Determinamos si el cuarto nivel puede ser un COG
        if (is_numeric($cuarto)) {
            // Debe estar en el rango entre 100 y 999 para presumir que pudiera ser una clave COG
            $nCuartoNivel = intval($cuarto);

            $veces = 0;
            $mesaje = "";
            if (($vger_id == 8 && $vcgp_id == 2)) {
                $veces = 1;
                $mesaje = "Las Cuentas de Orden ";
            }

            if (empty($veces) || $veces == 0) {

                $sql = "  SELECT ecu_id FROM  ".BD_CTRL.".cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    LIMIT 1";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);
                $estruc = 2;
                $row = $conexion->extraer();

                $estruc = $row['ecu_id'];
                $conexion->desconectar();
                if (empty($estruc) || $estruc == 2) {
                    $veces = 1;
                    $mesaje = "Las Cuentas contables de Claves Presupuestales ";
                }
            }
            if (empty($veces) || $veces == 0) {

                $sql = "SELECT cog_cuenta FROM ".BD_CTRL.".vin_Cog
                    WHERE ger_id = ?
                    AND cgp_id = ? AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    AND pg_id  = ? LIMIT 1;";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas del COG ";
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT cog_cuenta FROM ".BD_CTRL.".vin_CogContraCuenta
                        WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                                AND cgcu_id = ? AND cgsc_id = ?
                                AND pg_id  = ? LIMIT  1;";
                $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas del COG ";
            }
            if (empty($veces) || $veces == 0) {
                $cuenta11239 = ($vger_id . $vcgp_id . $vrub_id . $vcgcu_id . $vcgsc_id . "." . $cuarto);
                if ($cuenta11239 == "11239.010") {

                    $sql = "SELECT cta_id  FROM ".BD_CTRL.".vin_Retencion
                        WHERE  cta_id = ? LIMIT 1;";
                    $parametro = [$vcta_id];
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql($sql);
                    $conexion->consultar($parametro);

                    $row = $conexion->extraer();
                    $veces = $conexion->getNumeroRegistros();

                    $conexion->desconectar();
                    $mesaje = "Las Cuentas derivadas de Retenciones ";
                }
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT rfc_cta_id FROM ".BD_CTRL.".cat_Proveedor
                    WHERE rfc_cta_id = ? LIMIT  1;";
                $parametro = [$vcta_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametro);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas derivadas de Proveedores ";
            }
            if (empty($veces) || $veces == 0) {

                $sql = "SELECT * FROM ".BD_CTRL.".vin_BancosUrPlanCuentas
                    WHERE cta_gpo_id= ?
                    AND cta_ur_id = ?
                    AND cta_ue_id= ?
                    AND ger_id = ?
                    AND cgp_id = ?
                    AND rub_id = ?
                    AND cgcu_id = ?
                    AND cgsc_id = ?
                    AND cgc_id = ?
                    AND cta_id = ? LIMIT 1;";
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->ue, $vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto, $vcta_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = "Las Cuentas Contables vinculadas a Cuentas Bancarias ";
            }

            if ($veces == 0) {

                $sql = "SELECT cta_id FROM ".BD_CTRL.".scp_Contable
                    WHERE cta_gpo_id= ? AND cta_ur_id = ?
                    AND cta_ue_id= ? AND  ger_id = ?
                    AND cgp_id = ? AND rub_id = ? AND cgcu_id = ?
                    AND cgsc_id = ?
                    AND cgc_id = ?
                    AND cta_id = ? LIMIT 1; ";
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->ue, $vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto, $vcta_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = " Las Cuentas usadas en CLC ";
            }

            if (empty($veces) || $veces == 0) {

                $sql = "SELECT cta_id FROM ".BD_CTRL.".cg_PolizaDetalleAuxiliar
                    WHERE cta_gpo_id= ? AND cta_ur_id = ?
                    AND cta_ue_id= ? AND ger_id = ? AND cgp_id = ?
                    AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    AND cgc_id = ?
                    AND cta_id = ? LIMIT 1;";
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $this->prm->ue, $vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto, $vcta_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();

                $conexion->desconectar();
                $mesaje = " Las Cuentas contables usadas en Pólizas ";
            }

            /// Codigo para validar si existeun bloqueo en la cuenta contable
            $tieneBloqueo = false;
            $sql = 'SELECT cgc_bloqueo FROM '.BD_CTRL.'.cg_CuentaCuarto WHERE '
                . ' ger_id= ? AND cgp_id= ? AND  rub_id= ? AND  cgcu_id= ? AND  '
                . ' cgsc_id= ? AND cgc_id = ?;';
            $parametros = [$vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            $conexion->desconectar();

            $row = $conexion->extraer();
            $var_cgc_bloqueo = $row['cgc_bloqueo'];
            if ($var_cgc_bloqueo == 1) {
                $sql = 'SELECT cgc_ordenBloqueo FROM '.BD_CTRL.'.vin_BloquearCuentaSefin WHERE '
                    . ' gpo_id= ? AND ur_id= ? AND  '
                    . ' ger_id= ? AND cgp_id= ? AND  rub_id= ? AND  cgcu_id= ? AND  '
                    . ' cgsc_id= ? AND cgc_id = ? ;';
                $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $vger_id, $vcgp_id, $vrub_id, $vcgcu_id, $vcgsc_id, $cuarto];
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($parametros);
                $conexion->desconectar();
                $row = $conexion->extraer();
                $var_cgc_ordenBloqueo = $row['cgc_ordenBloqueo'];

                if ($var_cgc_ordenBloqueo == 0) {
                    $tieneBloqueo = true;
                }
            }

            if ($tieneBloqueo) {
                $this->mixScript('
                    notify( "La cuenta no está disponible para su uso", "error" );
                    setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
                    window.espera.stop();
                ');
                return false;
            }
            // Si es del COG, le avisamos que no puede pasar ...
            if ($veces > 0) {
                $this->mixScript('
                    notify( "' . $mesaje . ' No se pueden Eliminar ", "warning");
                ');
                return false;
            }
        }

        // Si no es numérico el cuarto nivel, o si no está dentro del rango del COG... permitirmos su modificación

        $this->mixScript('
            try {
                alertify.confirm("ELIMINAR REGISTRO","Se eliminará DEFINITIVAMENTE la cuenta <br /><br /><h4>" + "' . $this->prm->c . '" + " : " + "' . $this->prm->nom . '" + "</h4><br /><br />¿ Confirmas que deseas eliminarla ?",
                    function(){
                        comunica( "' . $this->pantalla . '", {
                            metodo:"eliminar",
                            c: "' . $this->prm->c . '",
                            id: "' . $id . '",
                            ue: "' . $ue . '"
                        });
                    },
                    function(){ }
                ).set("labels",{ok:"ELIMINAR",cancel:"Cancelar Operación"});
            } catch(e) {
                alert( e );
            }
        ');
    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos el historial de la cuenta actualmente seleccionada
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function verHistorial()
    {

        $tabla = new stdClass();
        $tabla->id = 'tblHistorial';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaHistorial';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Observaciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre de la Cuenta';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '180px';
        array_push($tr, $th);

        $trbody = array();
        //contenido clave
        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bit_observacion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cta_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_fecha';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->setContenedor('scpPantallaCompleta');

        $this->mixHtml('
            <div id="myModalMeta" class="modal fade modal-al95porciento show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Historial de la Cuenta</h4>
                            <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $("#homeMenu").click();
                $("#myModalMeta").remove();
                e.stopPropagation();
            });
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });
        ');

        $this->llenaHistorial();
    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Llenamos la tabla del historial
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function llenaHistorial()
    {

        $cta = ((isset($this->prm->c) && $this->prm->c != "") ? $this->prm->c
            : "");
        $ue = ((isset($this->prm->ue) && $this->prm->ue != "") ? $this->prm->ue : "");

        $sql = "SELECT usr_usuario, bit_observacion, cta_nombre, DATE_FORMAT(b.usr_fecha,'%d/%b/%y %h:%m:%s') AS usr_fecha
            FROM " . BD_BITACORA . ".bit_PlanCuentas b
            INNER JOIN " . BD_CTRL . ".cat_Usuario  USING (usr_id)
            WHERE b.cta_gpo_id= :gpo AND b.cta_ur_id= :ur
            AND b.cta_ue_id= :ue
            AND b.cta_cuenta = :cta
            ORDER BY b.usr_fecha;";
        $this->setSql($sql);
        $this->setParams(array(":gpo" => $_SESSION[_S]["clasAdm-gpo"], ":ur" => $_SESSION[_S]["clasAdm-ur"], ":ue" => $ue, ":cta" => $this->prm->c));
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblHistorial';
        $nav->nf = '999'; // No usaremos paginación en ésta pantalla
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );
    }
}
// Si no tiene permisos de lectura, no entra a la opción
if (WebResponse::tienePermisos(TAREA_LECTURA, $_POST)) {
    new ControladorPlanCuentas();
} else {
    WebResponse::sinPermisos();
}

// ----------------------------------------------------------------------------------------------------------------
// FIN DEL CÓDIGO
// ----------------------------------------------------------------------------------------------------------------
