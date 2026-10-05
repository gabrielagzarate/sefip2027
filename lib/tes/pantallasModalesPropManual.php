<?php
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Para buscar un proveedor, intentaremos diseñar una pantalla genérica que pueda ser llamada desde cualquier parte
// del sinpres, mediante un dialogBox
// HTML : El código que dibuja la pantalla
// JS   : El código que controla su comportamiento
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Para buscar una cuenta a nivel de mayor, intentaremos diseñar una pantalla genérica que pueda ser llamada
// desde cualquier parte del sinpres, mediante un dialogBox
// HTML : El código que dibuja la pantalla
// JS   : El código que controla su comportamiento
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarCuentaMayorHTML()
{

    return '
        <div id="cargaDatosMayor" style="width:100%;display:none;">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="input-group input-group-sm" style="float: right; width:250px">
                        <input id="filtrarTextoCuenta" type="text" class="form-control" onKeyUp="filtrarBuscarCuenta()" placeholder="Texto a buscar ..." >
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                    <p></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div id="myGridMayor" style="width:100%;height: 92%;"></div>
                </div>
            </div>
        </div>
        ';
}

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarCuentaMayorJS($puntos = "no")
{

    return '
    // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
    // --------------------------------------------------------------------------------------------------------------
    $("#myGridMayor").height(
                    $(document).height() -
                    $("header").outerHeight() -
                    $("#sticky").outerHeight() -
                    $("#buscarMaestro").outerHeight() -
                    $("footer").outerHeight() - 80
                );
    function NumericSorter(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
    }

    var mayorCuenta = "";
    var mayorNombre = "";

    var gridMayor;
    var dataMayor = [];
    var dataFiltrado = [];
    // Cargamos una sola vez el catálogo
    $.ajax({
        url:  "lib/dameCatalogoMayor.php",
        type: "post",
        success: function ( response ) {
            var json = $.parseJSON(response);
            for (var i = 0; i < json.length; i++) {
                    dataMayor[i] = {
                        cta: json[i].c,
                        nom: json[i].n,
                        est: json[i].e,
                        nat: json[i].t
                    }
            }
        }
    });

    var options = {
        enableCellNavigation: true,
        enableColumnReorder: false,
        autosizeColsMode: Slick.GridAutosizeColsMode.Legacy
    };
    var columnsMayor = [
        {id:"cta", name:"CUENTA MAYOR", field:"cta", minWidth: 200, maxWidth: 200,  sortable: true, sorter:NumericSorter},
        {id:"nom", name:"NOMBRE DE LA CUENTA", field:"nom", minWidth: 900, maxWidth: 1500,  sortable: true, sorter:NumericSorter}
    ];

    //GRID
    function MuestraMayor() {

        $("#myGridMayor").html("");
        $("#filtrarTextoCuenta").val("");

        // Principal funcion que carga el grid y le da sus atributos
        // ===========================================================================
        $( function() {

            gridMayor = new Slick.Grid($("#myGridMayor"), dataMayor, columnsMayor, options);

            // Función para ordenar uina columna del grid. debe existir en su definición del array columns que es sortable y el sorter
            // --------------------------------------------------------------------------------------------------------------------------
            gridMayor.onSort.subscribe(function(e, args) {
                sortdir = args.sortAsc ? 1 : -1;
                sortcol = args.sortCol.field;

                dataMayor.sort(args.sortCol.sorter, sortdir);
                args.grid.invalidateAllRows();
                args.grid.render();
            });

            // Para que se seleccione toda la línea
            // --------------------------------------------------------------------------------------------------------------
            gridMayor.setSelectionModel(new Slick.RowSelectionModel());


            // Para obtener el valor de una celda al presionar doble click en ella
            // --------------------------------------------------------------------------------------------------------------
            gridMayor.onDblClick.subscribe(function(e) {
                var cell = gridMayor.getCellFromEvent(e),
                row = cell.row;
                col = cell.cell;
                /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
                if ( $("#filtrarTextoCuenta").val() == "" ) {
                    $("#" + mayorCuenta ).val( dataMayor[row].cta' . (($puntos == "SI") ? "" : '.replace( /\./g, "" )') . ' );
                    $("#" + mayorNombre ).val( dataMayor[row].nom );
                    // Para saber a qué cuenta corresponde el nombre actual, y solo buscar en BLUR cuando difiera (que es cuando lo cambien manualmente)
                    $("#" + mayorNombre ).attr( "deCta", dataMayor[row].cta.replace( /\./g, "" ) );
                    $("#" + mayorNombre ).attr( "deNat", dataMayor[row].nat );
                    $("#" + mayorNombre ).attr( "deEst", dataMayor[row].est );
                    $("#cargaDatosMayor").dialog("close");
                    $("#" + mayorCuenta ).blur();
                } else {
                    $("#" + mayorCuenta ).val( dataFiltrado[row].cta' . (($puntos == "SI") ? "" : '.replace( /\./g, "" )') . ' );
                    $("#" + mayorNombre ).val( dataFiltrado[row].nom );
                    // Para saber a qué cuenta corresponde el nombre actual, y solo buscar en BLUR cuando difiera (que es cuando lo cambien manualmente)
                    $("#" + mayorNombre ).attr( "deCta", dataFiltrado[row].cta.replace( /\./g, "" ) );
                    $("#" + mayorNombre ).attr( "deNat", dataFiltrado[row].nat );
                    $("#" + mayorNombre ).attr( "deEst", dataFiltrado[row].est );
                    $("#cargaDatosMayor").dialog("close");
                    $("#" + mayorCuenta ).blur();
                }
            });

        });

    };

    /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
    function filtrarBuscarCuenta() {
        strPrimerDigito = $("#filtrarTextoCuenta").val().substring( 0, 1 );
        strBuscar = $("#filtrarTextoCuenta").val();
        var esNumero = ( strBuscar.match(/^[0-9]+$/) != null );
        if ( esNumero ) {
            strBuscar = preparaCuentaContable( strBuscar, 1 );
        }

        if ( strBuscar.length != 0 ) {
            dataFiltrado = [];
            dataFiltrado = $.grep(dataMayor,
                function(element){
                    if ( strPrimerDigito != "" ) {
                        strNumeros = "0123456789";
                        // Si empieza con un numero, buscamos en la cuenta
                        if ( strNumeros.indexOf( strPrimerDigito ) !== -1 ) {
                            return ( element.cta.substring(0,strBuscar.length) == strBuscar );
                        // Si empieza con letra, buscamos en el nombre
                        } else {
                            if (element.nom != null && element.nom != "" && element.nom.toUpperCase().indexOf($("#filtrarTextoCuenta").val().toUpperCase()) == -1) {
                                return false;
                            }
                            return true;
                        }
                    }
                });
            gridMayor.setData(dataFiltrado);
            gridMayor.render();
        } else {
            gridMayor.setData(dataMayor);
            gridMayor.render();
        }

    }
    /* Para mostrar el catalogo de cuentas a nivel de mayor y que busquen una */
    var dlg=$("#cargaDatosMayor").dialog({
        title: "BUSCAR Y SELECCIONAR CUENTA DE MAYOR",
        resizable: false,
        autoOpen:false,
        modal: true,
        dialogClass: "cssDialogAyuda",
        width:$(window).width()-20,
        height:$(window).height()-40,
        open: function( event, ui ) { MuestraMayor(); },
        create: function(event, ui) {
            $(event.target).parent().css("position", "fixed");
            $(".ui-dialog-titlebar-close").addClass("btn btn-danger btn-test").html("X");
        },
        close: function () { $("#myGridMayor").empty();	 }
    });

    ';
}

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Para buscar una cuenta, intentaremos diseñar una pantalla genérica que pueda ser llamada desde cualquier parte
// del sinpres, mediante un dialogBox
// HTML : El código que dibuja la pantalla
// JS   : El código que controla su comportamiento
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarCuentaHTML()
{
    return '
        <div id="cargaDatosCuentas"  style="width:100%;display:none;">
            <div class="row">
                <div class="col-md-2">
                    <input id="cta_id_buscar" name="cta_id_buscar" type="text" placeholder="Cuenta de Mayor" maxlength=11 class="form-control" tabindex="300">
                </div>
                <div class="col-md-1">
                    <input id="cta_cuarto_id_buscar" name="cta_cuarto_id_buscar" type="text" placeholder="4o. Nivel" maxlength=3 class="form-control" tabindex="305">
                </div>
                <div class="col-md-1">
                    <button tabindex="310" cat="" data-style="slide-left" class="btn btn-outline-secondary ladda-button" data-style="slide-left"
                    title="Buscar en el catálogo de cuentas" data-placement="bottom" id="mostrarCuentas"><i class="fa fa-search me-2"></i>Buscar</button>
                </div>
                <div class="col-md-1">
                    <input id="cta_ejecutora_id_buscar" name="cta_ejecutora_id_buscar" type="text" placeholder="UE" maxlength=3 class="form-control" tabindex="311">
                </div>
                <div class="input-group input-group-sm" style="float: right; width:250px">
                    <input id="filtrarTextoCuenta" tp="0" type="text" class="form-control" placeholder="Texto en la descripción a buscar ..." >
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                </div>
            </div>

            <div style="position:relative">
                <div id="planCuentasPaginador" style="float:right;position:absolute;right:0;top:-20px;z-index:100;">
                </div>
            </div>
            <p></p> 
            <div class="tabla-contenedor col-md-12" style="overflow: scroll;">           
                <table id="cuentaMayor" class="table table-striped table-bordered">
                    <thead class="sticky-top" style="z-index: 100">
                        <tr role="row" class="warning">
                            <th role="columnheader" class="warning" style= "text-align:center; width:200px;">Cuenta Contable</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:570px;">Descripción de la Cuenta</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:150px;">Naturaleza</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:150px;">UE</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    ';
}

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarCuentaJS()
{

    return <<<JS
        /* Para mostrar el catalogo de cuentas y que busquen una */
        var dlgC=$("#cargaDatosCuentas").dialog({
            title: "BUSCAR Y SELECCIONAR CUENTA CONTABLE",
            resizable: true,
            autoOpen:false,
            modal: true,
            width:$(window).width()-10,
            height:$(window).height()-10,
            dialogClass: "cssDialogAyuda",
            open: function( event, ui ) { buscarCuentaSolicitada(); },
            create: function(event, ui) { 
                $(event.target).parent().css("position", "fixed"); 
                $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").html("X"); 
            }
        });
        // 10/ene/2018
        // Se pedirán puntos entre cada uno de los niveles que forman la cuenta de mayor ( 4 puntos deben estar )
        $("#cta_id_buscar").blur( function(e) {
            // Debe tener los 4 puntos que separarían los niveles
            $("#cta_id_buscar").val( preparaCuentaContable( $("#cta_id_buscar").val(), 1 ) );
            if ($("#cta_id_buscar").val().trim()!=""){
                if ( ( $("#cta_id_buscar").val().trim().split(".").length - 1 ) != 4 ) {
                    $("#cta_id_buscar").val("");
                    notify("La cuenta de mayor DEBE separar cada uno de sus niveles con un punto", "error");
    
                    e.preventDefault;
                    e.stopPropagation;
                    return false;
                }
            }
        });


        // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
        // --------------------------------------------------------------------------------------------------------------
        function NumericSorter(a, b) {
            var x = a[sortcol], y = b[sortcol];
            return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
        }

        var gridBuscarCuenta;
        var dataBuscarCuenta = [ { "cta": "", "nom": "", "nat": "", "ue": "" } ];
        var dataFiltrado = [];

        /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
        $("#filtrarTextoCuenta").on("keyup", function(e){                     
            var value = $(this).val().toLowerCase();
            $("#cuentaMayor tbody>tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Para obtener el valor de una celda al presionar doble click en ella
        // --------------------------------------------------------------------------------------------------------------        
        $(document).ready(function() { 
            $("#cuentaMayor").on("dblclick", "tbody>tr", function() {
                // Obtener el índice de la fila (comenzando desde 0)
                var rowIndex = $(this).index();

                 // Obtener el contenido de la fila
                var rowData = [];
                $(this).find('td').each(function() {
                    rowData.push($(this).text());
                });

                var cta = rowData[0];
                var nom = rowData[1];

                // Si no hay cuenta que seleccionar, no hacemos nada
                if ( cta == "" ) {
                    e.stopPropagation();
                    return false;
                }

                /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
                if ( $("#filtrarTextoCuenta").val() == "" ) {
                    $("#" + ctaCuenta ).val( rowData[0] );
                    $("#" + ctaNombre ).val( rowData[1] );
                    $("#" + ctaNombre ).attr( "deUE", rowData[3] );
                    $("#" + ctaCompleta ).val(rowData[4]);


                    if ( $( "#debe" ).length ) {
                            if ( $("#debe").is(":enabled") )
                                setTimeout( function() { $( "#debe" ).focus() }, 100 );
                            else
                                setTimeout( function() { $( "#haber" ).focus() }, 100 );
                    } else {
                        setTimeout( function() { $("#" + ctaCuenta ).focus() }, 100 );
                    }

                        $("#cargaDatosCuentas").dialog("close");
                } else {

                    $("#" + ctaCuenta ).val( rowData[0] );
                    $("#" + ctaNombre ).val( rowData[1] );
                    $("#" + ctaNombre ).attr( "deUE", rowData[3] );
                    $("#" + ctaCompleta ).val(rowData[4]);
                    if ( $("#debe").is(":enabled") ) setTimeout( function() { $( "#debe" ).focus() }, 100 ); else setTimeout( function() { $( "#haber" ).focus() }, 100 );
                    $("#cargaDatosCuentas").dialog("close");
                    setTimeout( function() { $("#" + ctaCuenta ).focus() }, 100 );
                }
            });
        });

        

    // Cuando le den click a buscar, ponemos a 0 la paginación y guardamos la última cuenta buscada
    // ===========================================================================
    $("#mostrarCuentas").click(function(e){        
        e.preventDefault()

        // 10/ene/2018
        // Se pedirán puntos entre cada uno de los niveles que forman la cuenta de mayor ( 4 puntos deben estar )
        // Debe tener los 4 puntos que separarían los niveles

        if ( ( $("#cta_id_buscar").val().trim().split(".").length - 1 ) != 4 ) {
            $("#cta_id_buscar").val("");
            notify("La cuenta de mayor DEBE separar cada uno de sus niveles con un punto", "error");

            return false;
        }


        // Si están buscanlo lo mismo que la última vez, ni los pelamos
        // Lo inhabilitamos temporalmente, ya que pueden estar dando de alta en otra pestaña cuentas
            window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
            $("#cta_id_buscar").attr("tp", "0" );   // total paginas
            $("#cta_id_buscar").attr("ps", "0" );   // pagina solicitada
            $("#cta_id_buscar").attr("lastSearch", $("#cta_id_buscar").val() );
            buscarCuentaSolicitada();

        e.stopPropagation();
        return false;

    });

    // Buscamos una cuenta en la pantalla de selección de cuenta contable
    // ===========================================================================
    function buscarCuentaSolicitada() {
        // Marcos: 09/ene/2018
        // Este ejercicio, a nivel de mayor, los niveles de RUBRO y SUBCUENTA pueden ser de dos dígitos
        // Ergo, si la cuenta tiene 5 dígitos seguiremos el procedimiento tradicional... pero si tiene más de 6
        // entonces pediremos que separen los niveles con puntos, para poder obtener correctamente la información
        // de cada uno de los niveles que componen el nivel de mayor
        var anchoCtaMayor = $("#cta_id_buscar").val().trim().length;
        if ( anchoCtaMayor > 5 ) {
            // Debe tener los 4 puntos que separarían los niveles
            if ( ( $("#cta_id_buscar").val().trim().split(".").length - 1 ) != 4 ) {
            $("#cta_id_buscar").val("");
                notify("La cuenta de mayor DEBE separar cada uno de sus niveles con un punto", "error");

                window.espera.stop();
                return false;
            }
        }

        if ( $("#cta_id_buscar").val() == "" ) {
            $("#cta_id_buscar").attr("tp", "0" );   // total paginas
            return false;
        }

        dataBuscarCuenta = [];
        dataFiltrado = [];
        $("#filtrarTextoCuenta").val("");

        var miURL = "lib/tes/dameCatalogoCuentasPropManual.php";

        if ( $("#cta_id_buscar").attr("tp") == "0" ) {
            var bolPrimeraBusqueda = true;
        } else {
            var bolPrimeraBusqueda = false;
        }

        if ( $("#cta_ejecutora_id_buscar").val().trim() != "" ) {
            uePaso = $("#cta_ejecutora_id_buscar").val().trim();
        } else {
            uePaso =  $("#EncabezadoPoliza").attr( "ue");
        }
        ueF = $("#cta_ejecutora_id_buscar").val().trim();;
        $("#cuentaMayor tbody").empty();
        
        $.ajax({
            data: { "v" : $("#cta_id_buscar").val(), "cto" : $("#cta_cuarto_id_buscar").val(), "e" : uePaso, "p": $("#cta_id_buscar").attr("ps"), "ueF": ueF },
            url:  miURL,
            type: "post",
            success: function ( response ) {
                var json = $.parseJSON(response);
                /* Empezamos a partir del 1 porque en la 0 traemos el total de las cuentas */
                for (var i = 1; i < json.length; i++) {
                    var nuevaFila = "<tr class='seleccionable'>";
                        nuevaFila += "<td>" + json[i].cta + "</td>";
                        nuevaFila += "<td>" + json[i].nom + "</td>";
                        nuevaFila += "<td>" + json[i].nat + "</td>";
                        nuevaFila += "<td>" + json[i].ue + "</td>";    
                        nuevaFila += "<td style='display:none;'>" + json[i].cta_gpo_id + "-"+ json[i].cta_ur_id  + "-" + json[i].cta_ue_id + "-" + json[i].ger_id + "-" + json[i].cgp_id + "-" + json[i].rub_id + "-" + json[i].cgcu_id + "-" + json[i].cgsc_id  + "-" + json[i].cgc_id + "-" + json[i].cta_id +"</td>";                     
                        nuevaFila += "</tr>";
                    $("#cuentaMayor tbody").append(nuevaFila);
                }

                $(document).ready(function() {
                    $("#cuentaMayor tbody tr").hover(
                        function() {
                            $(this).addClass("seleccionado");
                        },
                        function() {                            
                            $(this).removeClass("seleccionado");
                        }
                    );
            
                });

                if (bolPrimeraBusqueda) {
                    $("#cta_id_buscar").attr("tp", json[0].cta );   // Guardamos el numero total de paginas de la cuenta actual
                }

                if ( parseInt( $("#cta_id_buscar").attr("tp") ) > 1 ) {
                    // Para que respete la paginación, definimos como página actual la última seleccionada
                    if (typeof $("#cta_id_buscar").attr("upc") === "undefined") {
                        var cPage = 1
                    } else {
                        var cPage = parseInt( $("#cta_id_buscar").attr("upc") );
                    }
                    var options = {
                        numberOfPages: 5,
                        totalPages: 5,
                        currentPage: cPage,
                        totalPages: $("#cta_id_buscar").attr("tp"),
                        itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "cursor-pointer"; },
                        onPageClicked: function(e, originalEvent, type, page){
                            $("#cuentaMayor tbody").empty();
                            e.stopImmediatePropagation();
                            // Grabamos la ultima página a la que le dieron click
                            $("#cta_id_buscar").attr("upc", page);
                            // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                            const last = $("#cta_id_buscar").attr("last") || 0;
                            if ( parseInt(last) != page ) {
                                $("#cta_id_buscar").attr("ps", page);
                                buscarCuentaSolicitada();
                                $("#planCuentasPaginador").attr("last", page );
                            }
                        }
                    }
                    $("#planCuentasPaginador").bootstrapPaginator(options);
                    $("#planCuentasPaginador").addClass("pagination");
                    $("#planCuentasPaginador li").addClass("page-item")
                    $("#planCuentasPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                } else {
                    $("#planCuentasPaginador").html("");
                }

                window.espera.stop();

            }
        });
    };
    JS;
}

// ========================================================================================================================
// FIN DEL CODIGO
// ========================================================================================================================
