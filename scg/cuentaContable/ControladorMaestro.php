<?php
/*
Controlar los niveles del catálogo Maestro.
El catálogo maestro es el que controla a todos los catálogos o planes de cuentas de las URs
Sólo las cuentas que existan aquí pueden usarse en el módulo de contabilidad
 */

//inicio de sesion y llamada a las librerias
declare (strict_types = 1);
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/CuentaQuinto.php';

class ControladorMaestro extends WebResponse
{

    //variables
    public $lectura;
    public $escritura;

    //Constructor
    public function __construct()
    {

        // Determinamos si el usuario actual tiene permisos de lectura / escritura para ésta opción
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);

        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case "dibujaPantalla":
                $this->dibujaPantalla();
                break;
            case "muestraDatosMaestro":
                $this->muestraDatosMaestro();
                break;
            case "muestraDatosMaestroPorCuenta":
                $this->muestraDatosMaestroPorCuenta();
                break;
            case "eliminar":
                if ($this->escritura) {
                    $this->eliminar();
                }

                break;
            case "addMaestro":
                if ($this->escritura) {
                    $this->addMaestro();
                }

                break;
            case "modMaestro":
                if ($this->escritura) {
                    $this->modMaestro();
                }

                break;
            case "ponNombreCtaMayor":
                $this->ponNombreCtaMayor();
                break;
            // No permitiremos altas, bajas o cambios de cuartos niveles que sean del COG
            case "validateCOG":
                $this->validateCOG();
                break;
            // En concordancia con el punto anterior, tampoco se permitirá modificar si el cuarto nivel es COG
            case "modificar":
                $this->modificar();
                break;
            // y tampoco permitimos eliminar si el cuarto nivel es CONAC
            case "eliminarCuenta":
                $this->eliminarCuenta();
                break;
            case "llenaTablaCtaMayor":
                $this->llenaTablaCtaMayor();
                break;
            case "PonBarraBotones":
                $this->PonBarraBotones();
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    public function main ()
    {
        $html = $this->getTablaMaestro();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $this->mixHtml($html);
    }

    public function PonBarraBotones()
    {

        $botones = '
        <div class="container">
            <div class="d-flex d-flex-row">
                ' . (($this->escritura) ? '
                <button id="adicionar" data-placement="bottom" title="Adicionar ayuda para la opción seleccionada" role="button" type="button" class="btn btn-outline-primary adicionar me-1" data-bs-toggle="modal" data-bs-target="#myModalAddMod"><i class="fa-solid fa-plus me-2"></i>Nueva</button>
                <button id="modificar" data-placement="bottom" title="Modificar ayuda para la opciòn seleccionada" role="button" type="button" class="btn btn-outline-secondary modificar me-1" data-bs-toggle="modal"><i class="fa-solid fa-pencil me-2"></i>Modificar</button>
                <button id="eliminar" data-placement="bottom" title="Eliminar concepto actual (sólo si aún no se ha usado)" class="btn btn-outline-danger eliminar me-1"><i class="fa-solid fa-trash me-2"></i>Eliminar</button>
                ' : '') . '
                <div class="btn-group">
                    <button class="btn dropdown-toggle btn-outline-danger me-1" data-bs-toggle="dropdown" cat="" aria-expanded="false"><i class="fa-solid fa-file-pdf me-2"></i>PDF<span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="#" id="impActual" class="dropdown-item impMaestro">Selección Actual</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="#" id="impTodo" class="dropdown-item impMaestro">Todo el Catálogo</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a id="impRango" data-bs-toggle="modal" class="dropdown-item">Rango de Cuentas</a></li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn  btn-test dropdown-toggle btn-outline-success me-1" data-bs-toggle="dropdown" cat="" aria-expanded="false"><i class="fa-solid fa-file-excel me-2"></i>Excel<span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="#" id="expActual" class="dropdown-item expMaestro">Selección Actual</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="#" id="expTodo" class=" dropdown-item expMaestro">Todo el Catálogo</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a id="expRango" class=" dropdown-item" data-bs-toggle="modal">Rango de Cuentas</a></li>
                    </ul>
                </div>
            </div>
            <span style="height:8px; display:block;"></span>
            <div class="row">
                <div class="d-flex justify-content-between">
                    <div class="col-md-2">
                        <div class="input-group-sm">
                            <div class="input-group input-group-sm">
                                <input id="ctaMayor" lastSearch="" name="ctaMayor" type="text" placeholder="Cuenta Mayor" maxlength=12 class="form-control F1Catalogo" tabindex="10">
                                <button id="mostrarMayor" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-rectangle-list"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group-sm">
                            <input id="nombreMayor" type="text" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button id="mostrar" data-placement="bottom" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-secondary btn-sm ladda-button" data-style="slide-left" cat="" tabindex="20"><i class="fa-solid fa-search me-2"></i>Mostrar</button>
                    </div>
                    <div class="input-group input-group-sm" style="float: right; width:200px">
                        <input id="buscar" type="text" class="form-control" placeholder="Texto a buscar ..." >
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    </div>
                </div>
            </div>
            <span style="height:6px; display:block;"></span>
        </div>
        ';

        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        $this->mixScript('
            var str = 0;
            function ponerCampos( str ){
                if( str == 3 ) {
                    $("#chQuinto").css("display","inline");
                } else {
                    $("#chQuinto").css("display","none");
                    $("#selNivelCuatro").css("display","none");
                    $(".nivel5").css("display","none");
                    $("#addModCuarto").css("display","block");
                }
            }

            $("nav").hide();
            $(".modulos").hide();
                ' . (($_SESSION[_S]["nvj_id"] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                : '') . '
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");

            if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#ctaMayor").focus( function(e) {
                mayorCuenta = "ctaMayor";
                mayorNombre = "nombreMayor";
            });

            $("#ctaMayor").blur( function(e) {
                // Mínimo 5 dígitos, máximo 6
                $("#ctaMayor").val( preparaCuentaContable( $("#ctaMayor").val(), 1 ) );
                if ( $("#ctaMayor").val() != "" && ( $("#ctaMayor").val().length >= 9 && $("#ctaMayor").val().length <= 12 ) ) {
                    // Si el campo de nombre de la cuenta está vacío ... o el nombre no corresponde a la cuenta de mayor actual ... le ponemos su nombre
                    if ( $("#nombreMayor").val() == "" || $("#nombreMayor").attr("deCta") != $("#ctaMayor").val() ) {
                        comunica("' . $this->pantalla . '",{"metodo":"ponNombreCtaMayor", cta: $("#ctaMayor").val(), campo: "nombreMayor" });
                    }
                }
            });

            function actualizarCombo(cta){
                comunica("' . $this->pantalla . '",{"metodo":"muestraDatosMaestroPorCuenta", cta: cta });
            }

            // F1 muestra el catálogo */
            $("#ctaMayor").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                    return false;
                }
            });

            $("#mostrarNivelMayor").click( function(e) {
                e.preventDefault();
                mayorCuenta = "addModMayor";
                mayorNombre = "nombreMayor";
                dlg.dialog("open");
            });

            $("#addModMayor").on("blur", function(){
                $("#ctaMayor").val($(this).val());
                actualizarCombo($(this).val());
            });

            $("#mostrarMayor").click( function(e) {
                e.preventDefault();
                mayorCuenta = "ctaMayor";
                mayorNombre = "nombreMayor";
                dlg.dialog("open");
                return false;
            });

            $("#impRango").click( function(e) {
                $("#imprimirRango").attr("t", "pdf" );
                $("#myModalRango").show();
            });
            $("#expRango").click( function(e) {
                $("#imprimirRango").attr("t", "xls" );
                $("#myModalRango").show();
            });

            $(".impMaestro").click( function(e) {
                e.preventDefault();
                var bolContinua = true;
                // Si piden el rango actual, deben tener la cuenta de mayor especificada
                if ( $(this).attr("id") == "impActual" ) {
                    if ( $("#ctaMayor").val() == "" || !( $("#ctaMayor").val().length >= 9 && $("#ctaMayor").val().length <= 12 ) ) {
                        notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                        bolContinua = false;
                    }
                }
                if ( bolContinua ) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $(this).attr("id") ))
                        .append($("<input>").attr("type", "hidden").attr("name", "mayor").attr("value", $("#ctaMayor").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    $("body").append(form);
                    form.submit();
                    e.stopPropagation();
                    return false;
                }
            });

            $(".expMaestro").click( function(e) {
                e.preventDefault();
                var bolContinua = true;
                // Si piden el rango actual, deben tener la cuenta de mayor especificada
                if ( $(this).attr("id") == "expActual" ) {
                    if ( $("#ctaMayor").val() == "" || !( $("#ctaMayor").val().length >=9 && $("#ctaMayor").val().length <= 12 ) ) {
                        notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                        bolContinua = false;
                    }
                }

                if ( bolContinua ) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/cuentaContable/exp_Maestro.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $(this).attr("id") ))
                        .append($("<input>").attr("type", "hidden").attr("name", "mayor").attr("value", $("#ctaMayor").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    $("body").append(form);
                    form.submit();
                }
                e.stopPropagation();
            });
            /*
            comunica("' . $this->pantalla . '",{"metodo":"dibujaPantalla"});
            */
        ');
        $this->dibujaPantalla();
    }

    public function dibujaPantalla()
    {
        $html = $this->getTablaMaestro();
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
            $("#tituloPantalla").hide();
            $(".input-group-addon").css("padding","2px 12px");
            // Para que el GRID se ajuste al espacio disponible en pantalla

                $("#modificar").addClass("disabled");
                $("#eliminar").addClass("disabled");

            // Mousedown en lugar de click para que no interfiera con el blur ( sentían los usuarios que era un doble click al botón MOSTRAR )
            //$("#mostrar").mousedown( function(e) {
            $("#mostrar").on( "mousedown click", function(e) {
                // Si están buscanlo lo mismo que la última vez, ni los pelamos
                if ( $("#ctaMayor").val() != $("#ctaMayor").attr("lastSearch") ) {
                    if ( $("#ctaMayor").val() != "" && ( $("#ctaMayor").val().length >= 9 && $("#ctaMayor").val().length <=12 ) ) {
                        comunica("' . $this->pantalla . '",{"metodo":"muestraDatosMaestro", cta: $("#ctaMayor").val() });
                    } else {
                        notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    }
                    $("#ctaMayor").attr( "lastSearch", $("#ctaMayor").val() );   /* Para grabar la última búsqueda */
                }
            });

            var sortcol = "CUENTA";
            var sortdir = 1;
            var searchString = "";

            function myFilter(item, args) {
                if ( item["c"] === null || item["n"] === null ) { return false }
                // Si empieza con numero ... buscamos en la cuenta
                strNumeros = "0123456789";
                if ( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ) {
                    return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                // si no ... buscamos en el texto
                } else {
                    if (args.searchString != "" && item["n"].toLowerCase().indexOf(args.searchString) == -1 ){
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

            function updateFilter() {
                dataView.setFilterArgs({ searchString: searchString });
                dataView.refresh();
            }

            function expandeNaturaleza(row, cell, value, columnDef, dataContext) {
                return ( data[row].nat == "D" ) ?
                    "<span title=\"DEUDORA\">" + value + "</span>" :
                    "<span title=\"ACREEDORA\">" + value + "</span>";
            }
            function expandeEstructura(row, cell, value, columnDef, dataContext) {
                switch ( data[row].str == "D" ) {
                    case "3":
                        return "<span title=\"4 Niveles con la siguiente estructura:\n111111.111.11.1111\">" + value + "</span>";
                        break;
                    case "2":
                        return "<span title=\"3 Niveles con la siguiente estructura:\n111111.111.111111111\">" + value + "</span>";
                        break;
                    // Por default será la 1
                    default:
                        return "<span title=\"3 Niveles con la siguiente estructura:\n111111.111.1111\">" + value + "</span>";
                        break;
                }
            }
            function expandeEstado(row, cell, value, columnDef, dataContext) {
                return ( data[row].t == "B" ) ?
                    "<span title=\"APARECE EN EL \nESTADO DE SITUACIÓN FINANCIERA\">" + value + "</span>" :
                    "<span title=\"APARECE EN EL \nESTADO DE ACTIVIDADES\">" + value + "</span>";
            }
            function expandePosicion(row, cell, value, columnDef, dataContext) {
                switch ( data[row].p ) {
                    case "A":
                        return "<span title=\"APARECE EN LA SECCION DE \nACTIVO\">" + value + "</span>"
                        break;
                    case "P":
                        return "<span title=\"APARECE EN LA SECCION DE \nPASIVO\">" + value + "</span>"
                        break;
                    case "H":
                        return "<span title=\"APARECE EN LA SECCION DE \nHACIENTA PÚBLICA\">" + value + "</span>"
                        break;
                    case "I":
                        return "<span title=\"APARECE EN LA SECCION DE \nINGRESOS\">" + value + "</span>"
                        break;
                    case "E":
                        return "<span title=\"APARECE EN LA SECCION DE \nEGRESOS\">" + value + "</span>"
                        break;
                    case "O":
                        return "<span title=\"APARECE EN LA SECCION DE \nORDEN\">" + value + "</span>"
                        break;
                }
                return ( data[row].t == "B" ) ?
                    "<span title=\"APARECE EN LA SECCION DE \nESTADO DE SITUACIÓN FINANCIERA\">" + value + "</span>" :
                    "<span title=\"APARECE EN EL \nESTADO DE ACTIVIDADES\">" + value + "</span>";
            }

            var dataView;
            var grid;
            var data = [];
            var columns = [
                {id: "c",   name: "CUENTA", field: "c", minWidth: 140, maxWidth: 600, sortable: true },
                {id: "n",   name: "NOMBRE", field: "n", minWidth: 600, sortable: true },
            ];

            var options = {
                enableCellNavigation: true,
                enableColumnReorder: false,
                autoHeight:true
            };

            // Cargamos la información a mostrar, de la tabla cat_Ayuda
            // ---------------------------------------------------------------------------------------------------
            function loadData( json ) {
                data = [];
                for (var i = 0; i < json.length; i++) {
                    var d = (data[i] = {});
                    // Marcos: ejercicio 2016 la cuenta de mayor puede ser de 5 o 6 dígitos... así que ajustaremos a que
                    // sean 6 fijos
                    ctacon = json[i].id;
                    if ( ctacon.substr( 5, 1 ) == "." ) {
                        ctacon = ctacon.substr( 0, 5 ) + "." + ctacon.substr( 6, 30 );
                    }
                    d["id"]  = ctacon;
                    d["niv"] = json[i].niv;
                    d["c"]   = ctacon;
                    d["n"]   = json[i].n;
                }
                dataView.setItems(data);
            }
            dataView = new Slick.Data.DataView( { inlineFilters: false });
            /*
            grid = new Slick.Grid("#myGrid", dataView, columns, options);
            grid.setSelectionModel(new Slick.RowSelectionModel());
            */

            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });

            /*dataView.onRowsChanged.subscribe(function (e, args) {
                grid.invalidateRows(args.rows);
                grid.render();
                $("#myGrid").height($(myGrid).height() + 30);
            });*/

            /*grid.onSort.subscribe(function (e, args) {
                sortdir = args.sortAsc ? 1 : -1;
                sortcol = args.sortCol.field;
                dataView.sort(comparer, args.sortAsc);
            });
            grid.autosizeColumns(); */

            $("#tbody-tabla-tbl-maestro").on("click", "tr.seleccionable", function () {
                if ($(this).hasClass("seleccionado")){
                    $("#modificar").removeClass("disabled");
                    $("#eliminar").removeClass("disabled");
                }
            });

        ');

        // SOLO USUARIOS CON PERMISOS DE ESCRITURA VERAN ÉSTE CODIGO
        if ($this->escritura) {
            $this->mixScript('
                $("#modificar").click(function(e){
                    // Si no hay una cuenta seleccionada, no hacemos nada
                    const row = $("#tbl-maestro").tabla("getFilaSeleccion");

                     if (!row) { e.preventDefault(); return false;};

                    // Obtenemos el array de los datos de la linea actual
                    comunica("' . $this->pantalla . '",{
                        metodo:"modificar", 
                        niv: row.niv, 
                        c: row.c, 
                        nom: row.n, 
                        u: row.u 
                    });
                     $("#modificar").addClass("disabled");
                     $("#eliminar").addClass("disabled");
                });

                $("#adicionar").click(function(e){
                    const row = $("#tbl-maestro").tabla("getFilaSeleccion");
                    $("#selNivelCuatro").css("display","none");
                    $("#addModCuarto").css("display","block");
                    $("#chbxQuinto").prop("checked",false);
                    if (!row) {
                        if($("#ctaMayor").val()!="" && $("#ctaMayor").val() != null){
                            $("#chQuinto").css("display","inline");
                            $("#addModMayor").val( $("#ctaMayor").val() ).attr("disabled", "disabled");
                            $(".modal-title").text("Adicionar SEFIN(MAESTRO) ");
                            $("#addModCuarto").val( "" ).removeAttr("disabled");
                            $(".nivel5").css("display", "none");
                            setTimeout( function() { $( "#addModMayor" ).focus() }, 100 );
                        }
                        if( str == 3 ) {
                            $("#chQuinto").css("display","inline");
                            $("#addModMayor").val( $("#ctaMayor").val() ).attr("disabled", "disabled");
                            $(".modal-title").text("Adicionar SEFIN(MAESTRO) ");
                            $("#addModCuarto").val( "" ).removeAttr("disabled");
                            $(".nivel5").css("display", "none");
                            setTimeout( function() { $( "#addModMayor" ).focus() }, 100 );
                        }
                    } else {
                        // Obtenemos el array de los datos de la linea actual
                        var cta_punto=row.c;
                        arrCta = cta_punto.split( "." );
                        $("#addModMayor").val( arrCta[0]+"."+arrCta[1]+"."+arrCta[2]+"."+arrCta[3]+"."+arrCta[4] ).attr("disabled", "disabled");
                        if ( row.niv == "5" ) {
                            $("#nivelCuarto").css("display","none");
                            $("#addModCuarto").css("display","block");
                            $("#addModCuarto").val( arrCta[5] ).attr("disabled", "disabled");
                            $("#chQuinto").css("display","none");
                            $("#addModQuinto").val( "" ).removeAttr("disabled");
                            $(".nivel5").css("display", "block");
                            $(".modal-title").text("Adicionar QUINTO Nivel");
                            $("#textQuinto").html("Quinto Nivel");
                        } else {
                            if( str == 3 )
                                $("#chQuinto").css("display","inline");
                                $(".modal-title").text("Adicionar SEFIN(MAESTRO) ");
                                $("#addModCuarto").val( "" ).removeAttr("disabled");
                                $(".nivel5").css("display", "none");
                        }
                        setTimeout( function() { $( "#addModCuarto" ).focus() }, 100 );
                    }
                    $("#nombre").val( "" );
                    $("#grabar").attr( "op", "add" );
                    $("#myModalAddMod").modal("show");
                });

                // Borramos una cuenta del catalogo maestro ...
                $("#eliminar").click(function(e){
                    e.preventDefault();
                    // Obtenemos el array de los datos de la linea actual
                    const row = $("#tbl-maestro").tabla("getFilaSeleccion");
                    if (row){
                    comunica("' . $this->pantalla . '",{
                        "metodo":"eliminarCuenta",
                        "niv": row.niv,
                        "c": row.c,
                        "nom": row.n,
                        "id": row.c 
                    });
                    }
                    $("#eliminar").addClass("disabled");
                    $("#modificar").addClass("disabled");
                });
            ');
        }

        $this->mixScript('
            setTimeout( function() { $( "#ctaMayor" ).focus() }, 100 );
        ');
    }

    private function getTablaMaestro()
    {
            $tabla = new stdClass();
            $tabla->esCompacta = true;
            $tabla->inputFiltro = "buscar";
            $tabla->id = 'tbl-maestro';
            $tabla->seleccionable = true;
            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $this->pantalla;
            $tabla->comunica->metodo = 'muestraDatosMaestro';

            $tr = array();
            $th = new stdClass();
            $th->texto = 'CUENTA';
            $th->width = '20px';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'NOMBRE';
            $th->width = '250px';
            array_push($tr, $th);

            $trbody = array();

            $td = new stdClass();
            $td->campoTxt = new stdClass();
            $td->campoTxt->campos = ['c']; 
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = new stdClass();
            $td->campoTxt->campos = ['n']; 
            array_push($trbody, $td);

            $tabla->encabezado = array($tr);
            $tabla->cuerpo = $trbody;
            return $this->wrPonTabla($tabla);
    }

    /* Regresamos en formato JSON el catalogo Maestro de la cuenta seleccionada
    ---------------------------------------------------------------------------------------------------- */

    private function ObtenerCuentas($c)
    {
        $arrCta = explode(".", $c);
        $elementos = count($arrCta);

        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        $sql = '
            SELECT c.cgc_cuenta AS c, m.ecu_id AS str
            FROM cg_CuentaCuarto c
            INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            WHERE c.ger_id = ? AND c.cgp_id = ? AND c.rub_id = ? AND c.cgcu_id = ? AND c.cgsc_id = ?
            AND c.cgc_cuenta != ""
            GROUP BY c;
        ';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];      
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $data = array();
        while ($row = $conexion->extraer()) {
            array_push($data,
                array(
                    'id' => $row['c'],
                    'c' => $row['c'],
                    'str' => $row['str'],
                )
            );
        }
        return json_encode($data);
    }

    private function DameCatalogoMaestro($c)
    {

        $cuenta = $c;
        $arrCta = explode(".", $cuenta);
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
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
                window.espera.stop();
            ');
            return false;
        }

        $sql = '
            SELECT "4" AS niv, c.cgc_cuenta AS c, c.cgc_nombre AS n, m.ecu_id AS str, u.usr_usuario AS usr
            FROM cg_CuentaCuarto c INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            INNER JOIN cat_Usuario u ON ( c.usr_id = u.usr_id )
            WHERE c.ger_id = ? AND c.cgp_id = ? AND c.rub_id = ? AND c.cgcu_id = ? AND c.cgsc_id = ?
            GROUP BY c
            UNION
            SELECT "5" AS niv, q.cgq_cuenta AS c, q.cgq_nombre AS n, m.ecu_id AS str, u.usr_usuario AS usr
            FROM cg_CuentaQuinto q INNER JOIN cat_CgSubcuenta m USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            INNER JOIN cat_Usuario u ON ( q.usr_id = u.usr_id )
            WHERE q.ger_id = ? AND q.cgp_id = ? AND q.rub_id = ? AND q.cgcu_id = ? AND q.cgsc_id = ?
            GROUP BY c
            ORDER BY c;
        ';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $genero, $grupo, $rubro, $cuenta, $subcuenta];
        //$conexion = new AccesoDatos();
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 200;

        $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );
        $nav->idTabla = 'tbl-maestro';
        
        if (($this->wrLlenaTabla($nav) == 0) && ($this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
            $this->mixScript('$("#buscar").click();');
        }

        $nRegistros = $this->getNumeroRegistros();

        if ($nRegistros <= 0) {
            return json_encode(array('id' => '0', 'niv' => '', 'c' => '', 'n' => '',
                'str' => '', 't' => '', 'p' => '', 'u' => ''));
        } else {
            $data = array();
            while ($row = $this->extraer()) {
                array_push($data,
                    array(
                        'id' => $row['c'],
                        'niv' => $row['niv'],
                        'c' => $row['c'],
                        'n' => $row['n'],
                        'str' => $row['str'],
                    ));
            }
            return json_encode($data);
        }
    }
    /* Mostramos la información a nivel de mayor
    ---------------------------------------------------------------------------------------------------- */

    private function muestraDatosMaestro()
    {
        $data = $this->DameCatalogoMaestro($this->prm->cta);
        return false;
    }

    private function muestraDatosMaestroPorCuenta()
    {
        $data = $this->ObtenerCuentas($this->prm->cta);

        $this->mixScript('
            var datos = ' . $data . ';
            str = datos.length > 0  ? datos[0].str : 0;
            ponerCampos(str);
            if( str == 3 ){
                $("#selNivelCuatro").empty();
                var html = "";
                $.each(datos, function(i, val){
                    if(val != undefined){
                        var fila = val.c.split(".");
                        html += "<option value=" + fila[5] + ">" + fila[5] + "</option>"
                    }
                });
                $("#selNivelCuatro").html(html);
            }
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
            .modal-header{padding:8px!important; background: #9d2449; color:white;}
            .modal-footer{padding:10px 20px!important}
        ');

        $this->mixHtml('
            <div id="myModalRango" class="modal fade show" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="padding-top: 70px;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
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
                        <div class="modal-footer"><button id="imprimirRango" class="btn btn-outline-secondary" tabindex="103"><i class="fa-solid fa-print me-2"></i>Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->setIdScript('declaraRango');
        $this->mixScript('
            $("#myModalAddMod").on("hidden.bs.modal", function (e){
                $(".modal-backdrop").remove();
            });
            $("#myModalRango").on("shown.bs.modal", function () {
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
                if ( $("#rangoHasta").val() < $("#rangoDesde").val() ) { $("#rangoHasta").val( $("#rangoDesde").val() ); }
            });
            $("#rangoHasta").blur( function(e) {
                if ( $("#rangoHasta").val() < $("#rangoDesde").val() ) { $("#rangoHasta").val( $("#rangoDesde").val() ); }
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
                e.preventDefault();
                mayorCuenta = "rangoDesde";
                mayorNombre = "nombreDesde";
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
                e.preventDefault();
                mayorCuenta = "rangoHasta";
                mayorNombre = "nombreHasta";
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
                if ( !( $("#rangoDesde").val().length >=9 || $("#rangoDesde").val().length <=12 ) ) {
                    notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    setTimeout( function() { $( "#rangoDesde" ).focus().select() }, 100 );
                    return false;
                }
                if ( !( $("#rangoHasta").val().length  >=9  || $("#rangoHasta").val().length <=12 ) ) {
                    notify("Especifica la cuenta a nivel de mayor con puntos", "warning");
                    setTimeout( function() { $( "#rangoHasta" ).focus().select() }, 100 );
                    return false;
                }

                if ( $("#imprimirRango").attr("t") == "xls" ) {
                    var form = $("<form>")
                    .attr("action", "rep/excel/scg/cuentaContable/exp_Maestro.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", "expRango" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "desde").attr("value", $("#rangoDesde").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "hasta").attr("value", $("#rangoHasta").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
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
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", "impRango" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "desde").attr("value", $("#rangoDesde").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "hasta").attr("value", $("#rangoHasta").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    $("body").append(form);
                    form.submit();
                    e.stopPropagation();
                    return false;
                }
            });
        ');
    }
    /* Eliminamos un registro del catálogo Maestro
    La idea es dejarle a la base de datos checar si se ha usado o no ... a traves el error en caso de que
    falle alguna foreign key
    ---------------------------------------------------------------------------------------------------- */

    private function eliminar()
    {
        // Debemos recibir al menos dos niveles separador por un punto: 11131.001
        // entonces vamos a convertirlo a dos arrays para separar el mayor del cuarto nivel
        $cta = trim($this->prm->c);
        $arrCta = explode(".", $cta);

        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);

        $ctaMayor = $arrCta[0] . $arrCta[1] . $arrCta[2] . $arrCta[3] . $arrCta[4];
        $cuarto = $arrCta[5];

        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        // Checamos si quieren borrarla de la tabla de cuarto o de quintos niveles
        // y NO permitiremos borrar cuentas de usr_id = 55, ya que éstas se generan
        // automáticamente
        $entidad = '';
        $CuentaC = '';
        $sql = "";
        switch ($this->prm->niv) {
            case "4":
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->cgcu_id = $cuenta;
                $entidad->cgsc_id = $subcuenta;
                $entidad->cgc_id = $cuarto;
                $entidad->abc = 'b';
                $CuentaC = new CuentaCuarto();
                break;
            case "5":
                if ($elementos == 7) {
                    $quinto = $arrCta[6];
                    // Como no esta "amarrado" con el plan de cuentas con indices, verificamos manualmente
                    // que aún no se haya usado la cuenta que quieran eliminar
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql('SELECT COUNT(*) AS veces FROM cg_PlanCuentas WHERE ger_id = ? and cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? AND LEFT( cta_id, 2 ) = ?'); 
                    $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cuarto, $quinto];
                    $conexion->consultar($parametros);
                    $conexion->desconectar();
                    $row = $conexion->extraer();
                    if ($row['veces'] > 0) {
                        $this->mixScript('
                            notify("El quinto nivel ya ha sido usado. NO puede eliminarse", "error" );
                        ');
                        return false;
                    }

                    $entidad = new stdClass();
                    $entidad->ger_id = $genero;
                    $entidad->cgp_id = $grupo;
                    $entidad->rub_id = $rubro;
                    $entidad->cgcu_id = $cuenta;
                    $entidad->cgsc_id = $subcuenta;
                    $entidad->cgc_id = $cuarto;
                    $entidad->cgq_id = $quinto;
                    $entidad->abc = 'b';
                    $CuentaC = new CuentaQuinto();
                    break;
                }
        }

        $CuentaC->abc($entidad);

        $this->mixScript('
            notify("Se eliminó la cuenta ' . $this->prm->id . '", "success" );
            comunica("' . $this->pantalla . '",{
            "metodo":"muestraDatosMaestro", 
            cta: $("#ctaMayor").val()
            });
        ');
        return false;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla donde modificaremos los datos del catálogo Maestro
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dibujaVentanaCaptura()
    {
        $this->mixHtml('
            <div id="myModalAddMod" class="modal fade" data-bs-keyboard="false" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding-top: 70px;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Adicionar SEFIN(MAESTRO)</h4>
                            <button id="cerrarModal" type="button" class="btn-close cerrar" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body
                            <form action="" class="" id="AddMod">
                                <div class="row">
                                    <fieldset class="col-xs-3"><h5>Nivel de Mayor</h5></fieldset>
                                    <fieldset class="col-xs-3">
                                        <div class="input-group">
                                            <input id="addModMayor" lastSearch="" name="addModMayor" type="text" placeholder="Cuenta Mayor" maxlength=12 class="form-control F1Catalogo" disabled>
                                            <button id="mostrarNivelMayor" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-rectangle-list"></i></button>
                                        </div>
                                    </fieldset>
                                    <fieldset class="col-xs-3" style="display:none" id="chQuinto">
                                        <h5 style="display:inline-flex">Quinto nivel</h5>
                                        <input type="checkbox" id="chbxQuinto" value="1" style="margin-left:10px; margin-bottom:4px">
                                    </fieldset>
                                </div><br />
                                <div class="row">
                                    <fieldset class="col-xs-3"><h5>Nivel SEFIN</h5></fieldset>
                                    <fieldset class="col-xs-2"><div id="nivelCuarto"></div><input id="addModCuarto" type="text" maxLength="3" class="form-control"></fieldset>
                                </div><br />
                                <div class="row nivel5">
                                    <fieldset class="col-xs-3"><h5 id="textQuinto">Quinto Nivel</h5></fieldset>
                                    <fieldset class="col-xs-2"><input id="addModQuinto" pattern="[0-9]" type="text" maxLength="2" class="form-control"></fieldset>
                                </div><div class="nivel5"><br/></div>
                                <div class="row">
                                    <fieldset class="col-xs-3"><h5>Nombre</h5></fieldset>
                                    <fieldset class="col-xs-9"><input id="nombre" type="text" maxLength="60" class="form-control"></fieldset>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="grabar" class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i>Grabar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#chbxQuinto").change(function(){
                if($("#addModMayor").val() != ""){
                    if($(this).prop("checked")){
                        $(".modal-title").text("Adicionar QUINTO Nivel");
                        $(".nivel5").css("display", "block");
                        $("#nivelCuarto").css("display", "block");
                        $("#addModCuarto").css("display","none");
                        $("#selNivelCuatro").css("display","block");
                        actualizarCombo($("#addModMayor").val());
                        html = "<select class=\"form-select\" id=\"selNivelCuatro\">";
                        html += "</select>";
                        $("#nivelCuarto").html(html);
                        $("#textQuinto").html("Nivel Ingreso");
                    }else{
                        $(".modal-title").text("Adicionar SEFIN(MAESTRO) ");
                        $(".nivel5").css("display", "none");
                        $("#selNivelCuatro").css("display","none");
                        $("#addModCuarto").css("display","block");
                        $("#textQuinto").html("Quinto Nivel");
                    }
                }else
                    $(this).prop("checked", false);
            });

            $("#myModalAddMod").on("hide.bs.modal", function () {
                // Limpiamos los campos
                $("#addModMayor").val("");
                $("#addModSegundo").val("");
                $("#addModTercero").val("");
                $("#addModCuarto").val("");
                $("#addModQuinto").val("");
                $("#nombre").val("");
            });
            $(".cerrar").click( function() {
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModalAddMod"));
                modal.hide();
            });
            $("#myModalAddMod").on("shown.bs.modal", function () {
                if ( $("#grabar").attr("op") == "mod" ) {
                    setTimeout( function() { $( "#nombre" ).focus() }, 100 );
                } else {
                    if ( $(".nivel5").css("display") != "none" ) {
                        setTimeout( function() { $( "#addModQuinto" ).focus() }, 100 );
                    } else {
                        setTimeout( function() { $( "#addModCuarto" ).focus() }, 100 );
                    }
                }
            });

            // Validamos COG
            $("#addModCuarto").focus( function(e) {
                var cuenta = $("#addModMayor").val();
                if (cuenta != ""){
                    $("#ctaMayor").val(cuenta);
                }
            });
            // Validamos COG
            $("#addModCuarto").blur( function(e) {
                comunica("' . $this->pantalla . '",{
                metodo:"validateCOG",
                m: $("#addModMayor").val(),
                c: $("#addModCuarto").val()
                });
            });

            $("#grabar").click( function(e) {
                var cuenta = $("#addModMayor").val();
                if (cuenta != ""){
                    $("#ctaMayor").val(cuenta);
                }
                const row = $("#tbl-maestro").tabla("getFilaSeleccion");
                if ( $("#grabar").attr("op") == "mod" ) {
                    // Obtenemos el array de los datos de la linea actual (que es la que estamos editando)
                        comunica("' . $this->pantalla . '",{
                            metodo:"modMaestro",
                            c: row.c,
                            n: $("#nombre").val(),
                            id: row.c,
                            niv: row.niv
                        });
                } else {
                    var id = $("#chbxQuinto").prop("checked") ? $("#ctaMayor").val() + "." + $("#selNivelCuatro").val() + "." + $("#addModQuinto").val() : $("#ctaMayor").val() + "." + $("#addModCuarto").val();
                    var n4 = $("#chbxQuinto").prop("checked") ? $("#selNivelCuatro").val() : $("#addModCuarto").val();
                    var nivel = $("#chbxQuinto").prop("checked") ? "5" : "4";
                    if (!row) {
                        comunica("' . $this->pantalla . '",{
                            "metodo":"addMaestro",
                            "c":  $("#ctaMayor").val(),
                            "n": $("#nombre").val(),
                            "id": $("#ctaMayor").val(),
                            "n4": n4,
                            "n5": $("#addModQuinto").val(),
                            "niv": nivel
                        });
                    } else {
                        var  cta = row.c;
                        var arrCta = cta.split( "." );
                        var mayorCtaadd=arrCta[0]+"."+arrCta[1]+"."+arrCta[2]+"."+arrCta[3]+"."+arrCta[4];
                        if ( row.str == "3" ) {
                            comunica("' . $this->pantalla . '",{
                                "metodo":"addMaestro",
                                "c": mayorCtaadd,
                                "n": $("#nombre").val(),
                                "id": mayorCtaadd,
                                "n4": n4,
                                "n5": $("#addModQuinto").val(),
                                "niv": 5
                            });
                        } else {
                            nivel = $("#chbxQuinto").prop("checked") ? "5" : row.niv;
                            comunica("' . $this->pantalla . '",{
                                "metodo":"addMaestro",
                                "c": mayorCtaadd,
                                "n": $("#nombre").val(),
                                "id": mayorCtaadd,
                                "n4": n4,
                                "n5": $("#addModQuinto").val(),
                                "niv": nivel
                            });
                        }
                    }
                }
            });
        ');
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Grabamos un cambio en el catálogo Maestro
// parametros: cuenta, nombre, id, nivel
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function modMaestro()
    {
        // Debemos recibir al menos dos niveles separador por un punto: 11131.001
        // entonces vamos a convertirlo a dos arrays para separar el mayor del cuarto nivel
        $cta = trim($this->prm->c);
        $arrCta = explode(".", $cta);
        $elementos = count($arrCta);
        $ctaMayor = $arrCta[0] . $arrCta[1] . $arrCta[2] . $arrCta[3] . $arrCta[4];
        $cuarto = $arrCta[5];
        $genero = $arrCta[0];
        $grupo = $arrCta[1];
        $rubro = $arrCta[2];
        $cuenta = $arrCta[3];
        $subcuenta = $arrCta[4];

        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la cuenta NO puede estar vacío", "error" );
            ');
            return false;
        }

        switch ($this->prm->niv) {
            case "4":
                $sql = 'UPDATE cg_CuentaCuarto SET cgc_nombre = "' . strtoupper(mysql_escape_mimic($this->prm->n)) . '" WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? AND usr_id <> 55;';
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cuarto];
                break;
            case "5":
                if ($elementos == 7) {
                    $quinto = $arrCta[6];
                    $sql = 'UPDATE cg_CuentaQuinto SET cgq_nombre = "' . strtoupper(mysql_escape_mimic($this->prm->n)) . '" WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ? AND cgq_id = ? AND usr_id <> 55;';
                    $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cuarto, $quinto];
                    break;
                }
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();
        $this->mixScript('
            
           comunica("' . $this->pantalla . '",{
            "metodo":"muestraDatosMaestro", 
            cta: $("#ctaMayor").val()
            });
            notify("Se ha actualizado la información", "success" );
            const modal = bootstrap.Modal.getInstance(document.querySelector("#myModalAddMod"));
            modal.hide();
        ');
        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Adicionamos un nuevo cuarto o quinto nivel en el catálogo Maestro
// parametros: cuenta original, nombre, cuarto nivel, quinto nivel, estructura
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function addMaestro()
    {

        $prmcuenta = $this->prm->c;
        $arrCta = explode(".", $prmcuenta);

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

        switch ($this->prm->niv) {
            case "4":
                if (!is_numeric($this->prm->n4)) {
                    $this->mixScript('
                        notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
                    ');
                    return false;
                }
                break;
            case "5":
                if (!is_numeric($this->prm->n5)) {
                    $this->mixScript('
                        notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
                    ');
                    return false;
                }
                break;
        }

        // el cuarto nivel tampoco
        if ($this->prm->n4 == 0) {
            $this->mixScript('
                notify( "La clave NO puede ser cero", "error" );
                setTimeout( function() { $( "#addModCuarto" ).focus().select() }, 100 );
            ');
            return false;
        }

        $cuarto = str_pad($this->prm->n4, 3, "0", STR_PAD_LEFT);
        $quinto = str_pad($this->prm->n5, 2, "0", STR_PAD_LEFT);
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
            ');
            return false;
        }
        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la cuenta NO puede quedar vacía", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
            ');
            return false;
        }

        // el cuarto nivel tampoco
        if (strlen($this->prm->n4) == 0) {
            $this->mixScript('
                notify( "La clave NO puede quedar vacía", "error" );
                setTimeout( function() { $( "#addModCuarto" ).focus().select() }, 100 );
            ');
            return false;
        }
        $CuentaC = '';
        $entidad = '';
        $sql = "";
        switch ($this->prm->niv) {
            case "4":
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->cgcu_id = $cuenta;
                $entidad->cgsc_id = $subcuenta;
                $entidad->cgc_id = $cuarto;
                $entidad->nombre = strtoupper(mysql_escape_mimic($this->prm->n));
                $entidad->abc = 'a';
                $CuentaC = new CuentaCuarto();
                $sql = " select cgc_id from  cg_CuentaCuarto where
                            ger_id= ? and  cgp_id= ? and rub_id= ? and cgcu_id= ? and cgsc_id= ?
                            and cgc_id =? LIMIT 1;";
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cuarto];
                break;
            case "5":
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->cgcu_id = $cuenta;
                $entidad->cgsc_id = $subcuenta;
                $entidad->cgc_id = $cuarto;
                $entidad->cgq_id = $quinto;
                $entidad->nombre = strtoupper($this->prm->n);
                $entidad->abc = 'a';
                $CuentaC = new CuentaQuinto();
                $sql = " 
                    select cgq_id from  cg_CuentaQuinto 
                    where ger_id= ? and  cgp_id= ? and rub_id= ? and cgcu_id= ? and cgsc_id= ?
                    and cgc_id = ? and cgq_id = ?  LIMIT 1;";
                $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta, $cuarto, $quinto];
                break;
        }

        $CuentaC->conectar();

        $CuentaC->setSql($sql);
        $CuentaC->consultar($parametros);
        $numreg = $CuentaC->getNumeroRegistros();

        $CuentaC->desconectar();
        if ($numreg == 0) {
            $CuentaC->abc($entidad);

            $this->mixScript('
                // Actualizamos el grid
                var item = dataView.getItemById("' . $this->prm->id . '");
                //newRow.id = newId + 1;   // no recuerdo para qué usaba ésto
                if ( item == undefined ) {
                    var niv = $("#chbxQuinto").prop("checked") ? "5" : "4";
                    if (dataView.getItems().length > 0){
                        dataView.addItem({
                            "id":  "' . $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta . "." . $cuarto . (($this->prm->niv
                                == "4") ? "" : "." . $quinto) . '",
                            "c":   "' . $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta . "." . $cuarto . (($this->prm->niv
                                == "4") ? "" : "." . $quinto) . '",
                            "n":   "' . strtoupper(str_replace('"','',$this->prm->n)) . '",
                            "niv":   niv,
                            "nat": $("#nombreMayor").attr("deNat"),
                            "str": $("#nombreMayor").attr("deEst"),
                            "t":   $("#nombreMayor").attr("deTip"),
                            "p":   $("#nombreMayor").attr("dePos")
                        });
                    }else{
                        comunica("' . $this->pantalla . '",{"metodo":"muestraDatosMaestro", cta: $("#ctaMayor").val() });
                    }
                } else {
                    dataView.addItem({
                        "id":  "' . $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta . "." . $cuarto . (($this->prm->niv
                            == "4") ? "" : "." . $quinto) . '",
                        "c":   "' . $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta . "." . $cuarto . (($this->prm->niv
                            == "4") ? "" : "." . $quinto) . '",
                        "n":   "' . strtoupper(str_replace('"','',$this->prm->n)) . '",
                        "niv":   "' . $this->prm->niv . '",
                        "nat": item.nat,
                        "str": item.str,
                        "t":   item.t,
                        "p":   item.p
                    }); 
                }
                dataView.reSort();
                // Terminamos
                notify("Se ha insertado la información", "success" );
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModalAddMod"));
                modal.hide();
            ');
        } else {
            $this->mixScript('
                notify("El nivel Sefin ya Existe", "warning" );
            ');
        }

        return true;
    }
    /* Ponemos en un campo de texto ... el nombre de una cuenta de mayor dada
    ---------------------------------------------------------------------------------------------------- */

    private function ponNombreCtaMayor()
    {
        $cuenta = $this->prm->cta;
        $arrCta = explode(".", $cuenta);
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
        if (!is_numeric($elementos) || $elementos != 5) {
            $this->mixScript('
                notify("Favor de verificar la cuenta de MAYOR");
            ');
            return false;
        }
        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($genero) || !is_numeric($grupo) || !is_numeric($rubro) || !is_numeric($cuenta)
            || !is_numeric($subcuenta)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
            ');
            return false;
        }

        $sql = 'SELECT cgsc_nombre AS nombre, nat_id, ecu_id,tcu_id, pcu_id FROM cat_CgSubcuenta
                WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?;';
        $parametros = [$genero, $grupo, $rubro, $cuenta, $subcuenta];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();

        if ($nRegistros <= 0) {
            $nombre = "";
            $this->mixScript('
                var campo = "' . $this->prm->campo . '";
                $("#" + campo ).val( "' . $nombre . '" );
                $("#" + campo ).attr( "deCta", "' . $this->prm->cta . '" );
                $("#" + campo ).attr( "deNat", "" );
                $("#" + campo ).attr( "deEst", "" );
                $("#" + campo ).attr( "deTip", "" );
                $("#" + campo ).attr( "dePos", "" );
            ');
        } else {
            $row = $conexion->extraer();
            $nombre = $row['nombre'];
            $this->mixScript('
                var campo = "' . $this->prm->campo . '";
                $("#" + campo ).val( "' . $nombre . '" );
                $("#" + campo ).attr( "deCta", "' . $this->prm->cta . '" );
                $("#" + campo ).attr( "deNat", "' . $row['nat_id'] . '" );
                $("#" + campo ).attr( "deEst", "' . $row['ecu_id'] . '" );
                $("#" + campo ).attr( "deTip", "' . $row['tcu_id'] . '" );
                $("#" + campo ).attr( "dePos", "' . $row['pcu_id'] . '" );
            ');
        }
    }

    // Validamos que el cuarto nivel NO sea una clave COG, ya que no permitiremos Altas, Bajas o Cambios de
    // cuartos niveles que sean del COG
    public function validateCOG()
    {
        $primerNivel = $this->prm->m;
        $cuartoNivel = $this->prm->c;
        $arrCta = explode(".", $primerNivel);
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
            ');
            return false;
        }
        // Marcos: 14/ene/2016
        // Israel pide (después de que bajara el CP Andrés Pacheco) que quitemos ésta restricción
        // para todas las cuentas que no empiecen con 5 u 82
        if (!($genero == "5" || $genero . $grupo == "82")) {
            return false;
        }

        // Verificamos que le cuarto nivel sea numérico
        if (is_numeric($cuartoNivel)) {
            // Debe estar en el rango entre 100 y 999 para presumir que pudiera ser una clave COG
            $nCuartoNivel = intval($cuartoNivel);
            $sql = "SELECT COUNT(*) AS veces FROM cat_PartidaGenerica WHERE pg_id = ?;";
            $parametro = [$nCuartoNivel];
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($parametro);
            $row = $conexion->extraer();
            $veces = $row['veces'];

            // Si es del COG, le avisamos que no puede pasar ...
            if ($veces > 0) {
                $this->mixScript('
                    $("#addModCuarto" ).val("");
                    notify( "No puede registrar cuartos niveles COG", "warning");
                    setTimeout( function() { $( "#addModCuarto" ).focus() }, 100 );
                ');
            }
            $conexion->desconectar();
        }
    }

    // Validamos si el cuarto nivel que pretenden modificar es un COG... y si lo es no permitirmos la modificación
    public function modificar()
    {
        $nivel = $this->prm->niv;
        $cuenta = $this->prm->c;
        $nombre = $this->prm->nom;
        $arrCta = explode(".", $cuenta);
        // Para efectos de validación, obtenemos también cuántos elementos tra
        $elementos = count($arrCta);
        $ctaMayor = $arrCta[0] . "." . $arrCta[1] . "." . $arrCta[2] . "." . $arrCta[3] . "." . $arrCta[4];
        $cuarto = $arrCta[5];
        $vger_id = $arrCta[0];
        $vcgp_id = $arrCta[1];
        $vrub_id = $arrCta[2];
        $vcgcu_id = $arrCta[3];
        $vcgsc_id = $arrCta[4];

        if ($nivel == "5") {
            $quinto = $arrCta[6];
        } else {
            $quinto = "";
        }
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
                $sql = "
                    SELECT ecu_id FROM  cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                    AND cgcu_id = ? AND cgsc_id = ?
                    LIMIT 1";
                $p1 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($p1);
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
                $sql = "
                    SELECT cog_cuenta  FROM vin_Cog
                    WHERE ger_id = ?
                        AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND pg_id  = ? LIMIT 1;";
                $p2 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($p2);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las Cuentas derivadas del COG ";
                $conexion->desconectar();
            }

            if (empty($veces) || $veces == 0) {
                $sql = "
                    SELECT cog_cuenta FROM vin_CogContraCuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND pg_id  = ? LIMIT 1;";
                $p3 = [$vger_id, $vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($p3);

                $row = $conexion->extraer();
                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las Cuentas derivadas del COG ";
                $conexion->desconectar();
            }

            // Si es del COG, le avisamos que no puede pasar ...
            if ($veces > 0) {
                $this->mixScript('
                    $("#addModCuarto" ).val("");
                    notify( " ' . $mesaje . ' NO pueden ser modificadas", "warning");
                ');
                return false;
            }
        }
        // Si no es numérico el cuarto nivel, o si no está dentro del rango del COG... permitirmos su modificación
        $this->mixScript('
            $(".modal-title").text("Modificar Concepto");
            $("#addModMayor").val("' . $ctaMayor . '").attr("disabled", "disabled");
            $("#addModCuarto").val("' . $cuarto . '").attr("disabled", "disabled");
            if ( "'. $this->prm->niv .'" == "' . $nivel . '" ) {
                $(".nivel5").css("display", "block");
                $("#addModQuinto").val("' . $quinto . '").attr("disabled", "disabled");
            } else {
                $(".nivel5").css("display", "none");
            }
            $("#nombre").val("' . $nombre . '");
            $("#grabar").attr( "op", "mod" );
            $("#myModalAddMod").modal("show");
            setTimeout( function() { $( "#nombre" ).focus() }, 100 );
        ');
    }

    // Validamos si el cuarteliminarCuentao nivel que pretenden eliminar es un COG... y si lo es no permitirmos la eliminación
    public function eliminarCuenta()
    {
        $nivel = $this->prm->niv;
        $cuenta = $this->prm->c;
        $nombre = $this->prm->nom;
        $id = $this->prm->id;
        $arrCta = explode(".", $cuenta);
        $cuarto = $arrCta[5];
        $vger_id = $arrCta[0];
        $vcgp_id = $arrCta[1];
        $vrub_id = $arrCta[2];
        $vcgcu_id = $arrCta[3];
        $vcgsc_id = $arrCta[4];

        if ($nivel == "5") {
            $quinto = $arrCta[6];
        } else {
            $quinto = "";
        }

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
                $sql = "
                    SELECT ecu_id FROM  cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                    LIMIT 1";
                $pE1 = [$vger_id,$vcgp_id, $vrub_id,$vcgcu_id,$vcgsc_id];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($pE1);
                $estruc = 2;
                $row = $conexion->extraer();

                $estruc = $row['ecu_id'];
                $conexion->desconectar();
                if (empty($estruc) || $estruc == 2) {
                    $veces = 1;
                    $mesaje = "Las Cuentas contables de Claves Presupuestales ";
                }
            }

            if ((empty($veces) || $veces == 0) && $nivel != "5") {
                $sql = "
                    SELECT cta_cuenta FROM  cg_PlanCuentas
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND cgc_id= ?
                    LIMIT 1";
                $pE2 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($pE2);

                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las cuentas utilizadas ";
                $conexion->desconectar();
            }

            if ($veces == 0 && $nivel == "5") {
                $sql = "
                    SELECT * FROM cg_PlanCuentas
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?
                        AND cgc_id = ? AND  substring(cta_id,1,2) = ?
                        AND  length(cta_id)=6  LIMIT 1;";
                $pE3 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto,$quinto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($pE3);

                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las cuentas utilizadas ";
                $conexion->desconectar();
            }

            if (empty($veces) || $veces == 0) {
                $sql = "
                    SELECT cog_cuenta FROM vin_Cog
                    WHERE ger_id = ?
                        AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND pg_id  = ? LIMIT 1;";
                $pE4 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($pE4);

                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las Cuentas derivadas del COG ";
                $conexion->desconectar();
            }

            if (empty($veces) || $veces == 0) {
                $sql = "
                    SELECT cog_cuenta FROM vin_CogContraCuenta
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ?
                        AND cgcu_id = ? AND cgsc_id = ?
                        AND pg_id  = ? LIMIT 1;";
                $pE5 = [$vger_id,$vcgp_id,$vrub_id,$vcgcu_id,$vcgsc_id,$cuarto];
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($pE5);

                $veces = $conexion->getNumeroRegistros();
                $mesaje = "Las Cuentas derivadas del COG ";
                $conexion->desconectar();
            }

            // Si es del COG, le avisamos que no puede pasar ...
            if ($veces > 0) {
                $this->mixScript('
                    $("#addModCuarto" ).val("");
                    notify( "' . $mesaje . ' NO pueden ser eliminadas", "warning");
                ');
                return false;
            }
        }

        // Si no es numérico el cuarto nivel, o si no está dentro del rango del COG... permitirmos su modificación

        $this->mixScript('
            //Si es un valor editable ...
            const row = $("#tbl-maestro").tabla("getFilaSeleccion");
            if (row.c != "undefined") {
                try {
                    alertify.confirm("ELIMINAR REGISTRO","Se eliminará DEFINITIVAMENTE el registro <br/> <h5>" + "'.$cuenta.'" + " : " + "'.$nombre.'" + "</h5> ¿Confirmas que deseas eliminarlo?",
                        function(){
                           comunica( "'.$this->pantalla.'", { 
                                metodo:"eliminar",
                                niv: '.$nivel.', 
                                c: "'.$cuenta.'",
                                id: "'.$cuenta.'"
                            }); 
                        }, 
                        function(){ }
                    ).set("labels",{ok:"ELIMINAR",cancel:"Cancelar Operación"});
                } catch(e) {
                    alert( e );
                }
            } else {
                e.preventDefault();
            }
        ');
    }
}
// Si no tiene permisos de lectura, no entra a la opción
if (WebResponse::tienePermisos(TAREA_LECTURA, $_POST)) {
    new ControladorMaestro();
} else {
    WebResponse::sinPermisos();
}

// ----------------------------------------------------------------------------------------------------------------
// FIN DEL CÓDIGO
// ----------------------------------------------------------------------------------------------------------------