<?php

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Para buscar un proveedor, intentaremos diseñar una pantalla genérica que pueda ser llamada desde cualquier parte
// del sinpres, mediante un dialogBox
// HTML : El código que dibuja la pantalla
// JS   : El código que controla su comportamiento
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarProveedorHTML()
{

    return '
        <div id="cargaDatosProveedor"  style="width:100%;display:none;">
            <div class="row">
                <div class="col-md-2">
                    <input id="rfc_buscar" name="rfc_buscar" type="text" placeholder="R.F.C." class="form-control" tabindex="300">
                </div>
                <div class="col-md-3">
                    <input id="proveedor_buscar" name="proveedor_buscar" type="text" placeholder="O su nombre empiece con" class="form-control" tabindex="305">
                </div>
                <div class="col-md-1">
                    <button tabindex="310" cat="" data-style="slide-left" class="btn btn-outline-secondary ladda-button" data-style="slide-left"
                    title="Buscar Proveedor" data-placement="bottom" id="mostrarProveedores"><i class="fa fa-search me-2"></i>Buscar</button>
                </div>
                <div class="input-group input-group-sm" style="float: right; width:250px">
                    <input id="filtrarTextoProveedor" tp="0" type="text" class="form-control" placeholder="Texto en la descripción a buscar ..." >
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                </div>
            </div>

            <div style="position:relative">
                <div id="planPaginadorP" style="float:right;position:absolute;right:0;top:-20px;z-index:100;">
                </div>
            </div>
            <p></p> 
             <div class="tabla-contenedor col-md-12" style="overflow: scroll;">           
                <table id="proveedores" class="table table-striped table-bordered">
                    <thead class="sticky-top" style="z-index: 100">
                        <tr role="row" class="warning">
                            <th role="columnheader" class="warning" style= "text-align:center; width:200px;">R.F.C.</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:570px;"> Nombre / Denominación</th>
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
function DibujaPantallaBuscarProveedorJS()
{

    return <<<JS

         /* Para mostrar el catalogo de proveedores */
        var dlgP=$("#cargaDatosProveedor").dialog({
            title: "BUSCAR Y SELECCIONAR PROVEEDOR",
            resizable: true,
            autoOpen:false,
            modal: true,
            width:$(window).width()-10,
            height:$(window).height()-10,
            dialogClass: "cssDialogAyuda",
            create: function(event, ui) {
                $(event.target).parent().css("position", "fixed");
                $(".ui-dialog-titlebar-close").addClass("btn btn-danger btn-test").html("X");
            },
        });

    // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
    // --------------------------------------------------------------------------------------------------------------
    function NumericSorter(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
    }

    var gridProveedores;
    var dataProveedores = [];
    var dataFiltrado = [];

    /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
    $("#filtrarTextoProveedor").on("keyup", function(e){                     
        var value = $(this).val().toLowerCase();
        $("#proveedores tbody>tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Para obtener el valor de una celda al presionar doble click en ella
    // --------------------------------------------------------------------------------------------------------------
    $(document).ready(function() { 
            $("#proveedores").on("dblclick", "tbody>tr", function() {
                // Obtener el índice de la fila (comenzando desde 0)
                var rowIndex = $(this).index();

                 // Obtener el contenido de la fila
                var rowData = [];
                $(this).find('td').each(function() {
                    rowData.push($(this).text());
                });

                var rfc = rowData[0];
                var nom = rowData[1];

                // Si no hay cuenta que seleccionar, no hacemos nada
                if ( rfc == "" ) {
                    e.stopPropagation();
                    return false;
                }

                /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
                if ( $("#filtrarTextoProveedor").val() == "" ) {
                    $("#" + rfcCampo ).val( rfc );
                    $("#" + rfcBeneficiario ).val( nom );
                    if(typeof tipoBeneficiario !== "undefined"){
                        if(rowData[2]==1){//Persona fisica
                            for (var i in tipoBeneficiario) {
                                $("#" + tipoBeneficiario[i] ).val( nom );
                            }
                        }
                    }
                    setTimeout( function() { $("#" + rfcBeneficiario ).focus() }, 100 )
                    $("#cargaDatosProveedor").dialog("close");
                } else {
                    $("#" + rfcCampo ).val( rfc );
                    $("#" + rfcBeneficiario ).val( nom );
                    setTimeout( function() { $("#" + rfcBeneficiario ).focus() }, 100 )
                    $("#cargaDatosProveedor").dialog("close");
                }
            });
        });

    $("#mostrarProveedores").click(function(e){

        e.preventDefault()

        // Si ponen algo en rfc ... buscamos por rfc
        if ( $("#rfc_buscar").val().trim() != "" ) {
            // Si están buscanlo lo mismo que la última vez, ni los pelamos
            if ( $("#rfc_buscar").val() != $("#rfc_buscar").attr("lastSearch") ) {
                $("#rfc_buscar").attr("tp", "0" );   // total paginas
                $("#rfc_buscar").attr("ps", "0" );   // pagina solicitada
                $("#rfc_buscar").attr("lastSearch", $("#rfc_buscar").val() );
                buscarRFCsolicitado("rfc_buscar");
            };
        // Si no ... buscamos por nombre si pusieron algo ahi
        } else if ( $("#proveedor_buscar").val().trim() != "" ) {
            // Si están buscanlo lo mismo que la última vez, ni los pelamos
            if ( $("#proveedor_buscar").val() != $("#proveedor_buscar").attr("lastSearch") ) {
                $("#proveedor_buscar").attr("tp", "0" );   // total paginas
                $("#proveedor_buscar").attr("ps", "0" );   // pagina solicitada
                $("#proveedor_buscar").attr("lastSearch", $("#proveedor_buscar").val() );
                buscarRFCsolicitado("proveedor_buscar");
            };
        }

        e.stopPropagation();
        return false;
    });

    // Buscamos un proveedor en la pantalla de selección
    // ===========================================================================
    function buscarRFCsolicitado( strCampoBusqueda ) {

        if ( $("#" + strCampoBusqueda).val().trim() == "" ) { return false; }

        dataProveedores = [];
        dataFiltrado = [];
        $("#filtrarTextoProveedor").val("");

        var miURL = "lib/dameCatalogoProveedores.php";
        if ( $("#" + strCampoBusqueda ).attr("tp") == "0" ) {
            var bolPrimeraBusqueda = true;
        } else {
            var bolPrimeraBusqueda = false;
        }
        $("#proveedores tbody").html("");

        $.ajax({
            data: { "v" : $("#" + strCampoBusqueda ).val(), "p": $("#" + strCampoBusqueda ).attr("ps"), "tB": strCampoBusqueda },
            url:  miURL,
            type: "post",
            success: function ( response ) {
                var json = $.parseJSON(response);
                /* alert( JSON.stringify( $.parseJSON(response), null, 4 ) ); */
                /* Empezamos a partir del 1 porque en la 0 traemos el total de los proveedores */
                for (var i = 1; i < json.length; i++) {
                    var nuevaFila = "<tr class='seleccionable'>";
                        nuevaFila += "<td style='text-align:center;'>" + json[i].rfc + "</td>";
                        nuevaFila += "<td>" + json[i].nom + "</td>";
                        nuevaFila += "<td style='display:none;'>" + json[i].tcy + "</td>";
                        nuevaFila += "</tr>";
                    $("#proveedores tbody").append(nuevaFila);
                }

                $(document).ready(function() {
                    $("#proveedores tbody tr").hover(
                        function() {
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        },
                        function() {                            
                            $(this).removeClass("seleccionado");
                            $(this).css("font-weight","normal");
                        }
                    );
            
                })

                if (bolPrimeraBusqueda) {
                    $("#" + strCampoBusqueda ).attr("tp", json[0].rfc);   // Guardamos el numero total de paginas de el rfc actual
                }

                if ( parseInt( $("#" + strCampoBusqueda).attr("tp") ) > 1 ) {
                    // Para que respete la paginación, definimos como página actual la última seleccionada
                    if (typeof $("#" + strCampoBusqueda).attr("upc") === "undefined") {
                        var cPage = 1
                    } else {
                        var cPage = parseInt( $("#" + strCampoBusqueda).attr("upc") );
                    }
                    var options = {
                        numberOfPages: 5,
                        currentPage: cPage,
                        totalPages: $("#" + strCampoBusqueda ).attr("tp"),
                        itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                        onPageClicked: function(e,originalEvent,type,page){
                            $("#proveedores tbody").html("");
                            e.stopImmediatePropagation();
                            // Grabamos la ultima página a la que le dieron click
                            $("#" + strCampoBusqueda).attr("upc",page);
                            // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                            if ( $("#planPaginadorP").attr("last") != page ) {
                                $("#" + strCampoBusqueda ).attr("ps",page);
                                buscarRFCsolicitado( strCampoBusqueda );
                                $("#" + strCampoBusqueda).attr("last", page );
                            }
                        }
                    }
                    $("#planPaginadorP").bootstrapPaginator(options);
                    $("#planPaginadorP").addClass("pagination");
                    $("#planPaginadorP li").addClass("page-item")
                    $("#planPaginadorP li a").addClass("page-link cursor-pointer").css("margin", "0px");
                } else {
                    $("#planPaginadorP").html("");
                }

                try {
                    window.espera.stop();
                } catch( err ) {
                    //nada
                }
            }
        });
    }
    JS;
}

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
                        <input id="filtrarTextoCuenta" tp="0" type="text" class="form-control" placeholder="Texto a buscar ..." >
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>

            <div style="position:relative">
                <div id="mayorPaginador" style="float:right;position:absolute;right:0;top:-20px;z-index:100;">
                </div>
            </div>
            <p></p> 
            <div class="tabla-contenedor col-md-12" style="overflow: scroll;">           
                <table id="datosMayor" class="table table-striped table-bordered">
                    <thead class="sticky-top" style="z-index: 100">
                        <tr role="row" class="warning">
                            <th role="columnheader" class="warning" style= "text-align:center; width:200px;">Cuenta Mayor</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:570px;">Nombre de la Cuenta</th>
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
function DibujaPantallaBuscarCuentaMayorJS()
{
    return <<<JS
      
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
    
    // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
    // --------------------------------------------------------------------------------------------------------------
    function NumericSorter(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
    }

    var puntos = "SI";
    var mayorCuenta = "";
    var mayorNombre = "";

    var gridMayor;
    var dataMayor = [];
    var dataFiltrado = [];

    
    /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
    $("#filtrarTextoCuenta").on("keyup", function(e){ 
        var strPrimerDigito = $(this).val().substring( 0, 1 );
        var strBuscar = $(this).val();
        var esNumero = ( strBuscar.match(/^[0-9]+$/) != null );
        if ( esNumero ) {
            strBuscar = preparaCuentaContable( strBuscar, 1 );
        }
        $("#datosMayor tbody>tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(strBuscar) > -1);
        });
    });

    $(document).ready(function() { 
        $("#datosMayor").on("dblclick", "tbody>tr", function() {
            // Obtener el índice de la fila (comenzando desde 0)
            var rowIndex = $(this).index();

            // Obtener el contenido de la fila
            var rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text());
            });

            var cta = rowData[0];
            var nom = rowData[1];
            var est = rowData[2];
            var nat = rowData[3];

            // Si no hay cuenta que seleccionar, no hacemos nada
            if ( cta == "" ) {
                e.stopPropagation();
                return false;
            }

            /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
            if ( $("#filtrarTextoCuenta").val() == "" ) {
                switch (puntos) {
                        case "SI":
                            $("#" + mayorCuenta ).val(cta); 
                            break;
                    
                        default:
                            $("#" + mayorCuenta ).val(cta.replace( /\./g, "" ));
                            break;
                    }  
                    if(mayorNombre != ""){                  
                        $("#" + mayorNombre ).val( nom );
                        // Para saber a qué cuenta corresponde el nombre actual, y solo buscar en BLUR cuando difiera (que es cuando lo cambien manualmente)
                        $("#" + mayorNombre ).attr( "deCta", cta.replace( /\./g, "" ) );
                        $("#" + mayorNombre ).attr( "deNat", nat );
                        $("#" + mayorNombre ).attr( "deEst", est );
                    }
                    //$("#" + mayorCuenta ).blur();
                    setTimeout( function() { $("#" + mayorCuenta ).focus() }, 100 )
                    $("#cargaDatosMayor").dialog("close");
            } else {
                switch (puntos) {
                        case "SI":
                            $("#" + mayorCuenta ).val(cta); 
                            break;
                    
                        default:
                            $("#" + mayorCuenta ).val(cta.replace( /\./g, "" ));
                            break;
                    }
                    if(mayorNombre != ""){
                        $("#" + mayorNombre ).val( nom );
                        // Para saber a qué cuenta corresponde el nombre actual, y solo buscar en BLUR cuando difiera (que es cuando lo cambien manualmente)
                        $("#" + mayorNombre ).attr( "deCta", cta.replace(/\./g, "" ) );
                        $("#" + mayorNombre ).attr( "deNat", nat );
                        $("#" + mayorNombre ).attr( "deEst", est );
                    }
                    //$("#" + mayorCuenta ).blur();
                    setTimeout( function() { $("#" + mayorCuenta ).focus() }, 100 )
                    $("#cargaDatosMayor").dialog("close");
            }
        });
    });

    function MuestraMayor() {

        $("#myGridMayor").html("");
        $("#filtrarTextoCuenta").val("");
        $("#datosMayor tbody").html("");

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
                    var nuevaFila = "<tr class='seleccionable'>";
                        nuevaFila += "<td style='text-align:center;'>" + json[i].c + "</td>";
                        nuevaFila += "<td>" + json[i].n + "</td>";
                        nuevaFila += "<td style='display:none;'>" + json[i].e + "</td>";
                        nuevaFila += "<td style='display:none;'>" + json[i].t + "</td>";
                        nuevaFila += "</tr>";
                    $("#datosMayor tbody").append(nuevaFila);
                }
                $(document).ready(function() {
                    $("#datosMayor tbody tr").hover(
                        function() {
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        },
                        function() {                            
                            $(this).removeClass("seleccionado");
                            $(this).css("font-weight","normal");
                        }
                    );
                });
          }
        });
    };
    JS;
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

        var miURL = "lib/dameCatalogoCuentas.php";

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
        $("#cuentaMayor tbody").html("");
        
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
                        nuevaFila += "</tr>";
                    $("#cuentaMayor tbody").append(nuevaFila);
                }

                $(document).ready(function() {
                    $("#cuentaMayor tbody tr").hover(
                        function() {
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        },
                        function() {                            
                            $(this).removeClass("seleccionado");
                            $(this).css("font-weight","normal");
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
                            $("#cuentaMayor tbody").html("");
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

// Código para que funcione el libro diario
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaLibroDiarioJS()
{
    $escritura = (isset($_SESSION[_S]['prm']['C']['4']['1']['2']) ? 0 : 1);
    return <<<JS
        function dameMesCorto( strMes ) {
            aMes = new Array( "ape", "ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic", "cie" );
            return aMes[ strMes ];
        }

        // La usaremos para que -si hay un error- parpadee el texto
        // --------------------------------------------------------------------------------------------------------------
        function Parpadea() {
            $(".parpadea").fadeOut(500);
            $(".parpadea").fadeIn(500);
        }
        setInterval(Parpadea, 100);

        // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
        // --------------------------------------------------------------------------------------------------------------
        function NumericSorter(a, b) {
            var x = a[sortcol], y = b[sortcol];
            return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
        }

        function FormatoMoneda(row, cell, value, columnDef, dataContext) {
            // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
            strIni = ""
            strFin = ""
            if( dataLibroDiario[row].i != dataLibroDiario[row].h ) {
                strIni = "<span class=\"parpadea\" title=\"Hay diferencia entre la suma del Debe y del Haber\"><font color=#FF0000><b>";
                strFin = "</b></font></span>";
            }

            if (value === null || value === "" || !(value != 0)) {
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

        function FormatoDia(row, cell, value, columnDef, dataContext) {
            if ( dataLibroDiario[row].mes != $("#mesActual").attr( "ma" ) ) {
                return value + "-<font color=\"red\">" + dameMesCorto( dataLibroDiario[row].mes ) + "</font>";
            } else {
                return value;
            }
        }

        function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
            return ( value == "C" || value == "H" ) ? "<img src=\'css/images/pin.png\'>" : "";
        }

        function FormatearTexto(row, cell, value, columnDef, dataContext) {
            // Cuando sea cancelada ... también tachamos el texto
            return ( dataLibroDiario[row].e == "C" || dataLibroDiario[row].e == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
        }

        function MCFormatter(row, cell, value, columnDef, dataContext) {
            if ( value.substring( 1 ) == "A" ) {
                return value.substring( 0, 1 );
            } else {
                return "<span title=\"La póliza corresponde a la cancelación de un compromiso que fué autorizado en diferente mes\">" + value.substring( 0, 1 ) + "<span style=\"color:red\">" + value.substring( 1 ) + "</span></span>";
            }
        }

        function PolFormatter(row, cell, value, columnDef, dataContext) {
            if ( dataLibroDiario[row].apg != "ORD" ) {
                return value;
            } else {
                return value + "<img src=\'css/images/punto.png\'>";
            }
        }
        var gridLibroDiario;
        var dataLibroDiario = [];
        var dataFiltrado = [];

        var columnsLibroDiario = [
            {id: "p",   name: "Póliza", field: "p",  minWidth: 50, sortable: true, sorter:NumericSorter, formatter: PolFormatter  },
            {id: "d",   name: "Día", field: "d",  maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter, formatter: FormatoDia },
            {id: "c",   name: "Descripción de la póliza", field: "c",  minWidth: 470, sortable: true, formatter: FormatearTexto, sorter:NumericSorter },
            {id: "i",   name: "Importe", field: "i",  minWidth: 150, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", sortable: true, sorter:NumericSorter, formatter: FormatoMoneda },
            {id: "mc",  name: "MC", field: "mc", maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter, formatter: MCFormatter },
            {id: "V",   name: "Vinculación", field: "v",  minWidth: 80, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter },
            {id: "e",   name: "Edo", field: "e",  maxWidth: 50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, formatter: CheckmarkFormatter, sorter:NumericSorter },
            {id: "ue",  name: "UE", field: "ue", maxWidth: 100, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter },
        ];

        var options = {
            enableCellNavigation: true, /* Para permitir que se seleccione una celda */
            enableColumnReorder: false,
            enableAutoSizeColumns: true,
            explicitInitialization: true,
            autoHeigth:true
        };

        /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
        function filtrarLibroDiario() {
            dataFiltrado = [];
            dataFiltrado = $.grep(dataLibroDiario,
                function(element){
                    if (element.c != null && element.c != "" && element.c.toUpperCase().indexOf($("#buscar").val().toUpperCase()) == -1) {
                        if (element.i != null && element.i != "" && element.i.indexOf($("#buscar").val().toUpperCase()) == -1) {
                            return false;
                        }
                    }
                    return true;
                });
            gridLibroDiario.setData(dataFiltrado);
            gridLibroDiario.render();
        }

        gridLibroDiario = new Slick.Grid($("#myGridLibroDiario"), dataLibroDiario, columnsLibroDiario, options);

        // Función para ordenar una columna del grid. debe existir en su definición del array columns que es sortable y el sorter
        // --------------------------------------------------------------------------------------------------------------------------
        gridLibroDiario.onSort.subscribe(function(e, args) {
            sortdir = args.sortAsc ? 1 : -1;
            sortcol = args.sortCol.field;
            
            dataLibroDiario.sort(args.sortCol.sorter, sortdir);
            args.grid.invalidateAllRows();
            args.grid.render();
        });

        // Para que se seleccione toda la línea
        // --------------------------------------------------------------------------------------------------------------
        gridLibroDiario.setSelectionModel(new Slick.RowSelectionModel());

        // Para obtener el valor de una celda al presionar doble click en ella
        // --------------------------------------------------------------------------------------------------------------
        gridLibroDiario.onDblClick.subscribe(function(e) {
            var cell = gridLibroDiario.getCellFromEvent(e),
            row = cell.row;

            // Si no hay nada que selecionar, no hacemos nada
            if ( dataLibroDiario[row].pot_id == "" ) { e.stopPropagation();   return false; }

            // Si sólo son afectaciones a cuentas de orden, los mandamos a la visualizacion ... y no a la edición
            if ( dataLibroDiario[row].apg == "ORD" || dataLibroDiario[row].e == "H" || dataLibroDiario[row].e == "C" || dataLibroDiario[row].em != "A" || $escritura) {
                comunica("scg/poliza/ControladorMuestraPoliza.php",{
                    "m" : $("#mesActual").attr( "ma" ),
                    "p" : dataLibroDiario[row].pot_id,
                    "r" : dataLibroDiario[row].prf_id,
                    "n" : dataLibroDiario[row].numero,
                    "ue": dataLibroDiario[row].ue,
                    "mp": $("#draCorazon").attr( "modoPoliza" )
                });
            } else {
                comunica("scg/poliza/ControladorPolizaManual.php",{
                    "m"  : $("#mesActual").attr( "ma" ),
                    "p"  : dataLibroDiario[row].pot_id,
                    "r"  : dataLibroDiario[row].prf_id,
                    "n"  : dataLibroDiario[row].numero,
                    "ue" : dataLibroDiario[row].ue,
                    "ori": "LibroDiario"
                });
            }
        });

        $("#mostrar1, #mostrar2, #mostrar3").on( "mousedown", function(e) {
            $("#avisosLD").remove();
            e.preventDefault()
            window.espera = Ladda.create(this);
            window.espera.start();   // Mostramos icono de espera ...
            $("#buscar").attr("tp","0");
            $("#buscar").attr("ps","");
            CargaLibroDiario( );
            chkMscId( $("#mesActual").attr("ma") );
            e.stopPropagation();
            window.espera.stop();
            return false;
        });

        // Buscamos un LibroDiario en la pantalla de selección
        // ===========================================================================
        function CargaLibroDiario( l ) {

            dataLibroDiario = [];
            dataFiltrado = [];
            $("#filtrarTextoLibroDiario").val("");

            var miURL = "lib/dameLibroDiario.php";

            if ( $("#buscar").attr("tp") == "0" ) {
                var bolPrimeraBusqueda = true;
            } else {
                var bolPrimeraBusqueda = false;
            }

            $.ajax({
                data : {
                    "m"     : $("#mesActual").attr( "ma" ),
                    "t"     : $("#tipoActual").attr( "ta" ),
                    "o"     : $("#ordenActual").attr( "oa" ),
                    "n"     : $("#numPoliza").val(),
                    "v"     : $("#numVinculacion").val(),
                    "nc"    : $("#numCLC").val(),
                    "ue"    : $("#ueFiltro option:selected").val(),
                    "tclc"  : $("#tclc_id option:selected").val(),
                    "u"     : $("#usr_id option:selected").val(),
                    "tb"    : $("#nombreBuscar").val(),
                    "p"     : $("#buscar").attr("ps"),
                    "po"    : ( $("#chkOrden").prop("checked" ) ? 1 : 0 ),
                    "rfc"   : $("#rfcFiltro").val(),
                    "apg"   : $("#apg_id option:selected").val(),
                    "clc_v" : $("#clc_vinculacion").val(),
                    "afe_v" : $("#afe_vinculacion").val(),
                    "cmp_v" : $("#cmp_vinculacion").val(),
                    "bdo_v" : $("#bdo_vinculacion").val(),
                    "ref_v" : $("#ref_vinculacion").val(),
                    "eje_v" : $("#ref_ejercicio").val(),
                    "clc_o" : $("#clc_origen").val(),
                    "afe_o" : $("#afe_origen").val(),
                    "cmp_o" : $("#cmp_origen").val(),
                    "bdo_o" : $("#bdo_origen").val(),
                    "vinClc": $("#desdeVinClc").val(),
                    "andOr" : $("input[name=andOr]:checked").val(),
                    "txt"   : $("#buscar").val(),
                    "ecf"   : $("#ClaveFinan").attr("ecf")
                },
                url:  miURL,
                type: "post",
                success: function ( response ) {
                    var json = $.parseJSON(response);
                    /* alert( JSON.stringify( $.parseJSON(response), null, 4 ) ); */
                    /* Empezamos a partir del 1 porque en la 0 traemos el total de los LibroDiario */
                    for (var i = 1; i < json.length; i++) {
                        dataLibroDiario[i-1] = {
                            pot_id: json[i].pot_id,
                            prf_id: json[i].prf_id,
                            numero: json[i].numero,
                            p:   json[i].pot_id + "-" + json[i].prf_id + json[i].numero,
                            d:   json[i].dia,
                            c:   json[i].c,
                            i:   json[i].d,
                            h:   json[i].h,
                            mc:  json[i].mc,
                            v:   json[i].v,
                            e:   json[i].e,
                            ue:  json[i].ue,
                            apg: json[i].apg,
                            em:  json[i].em,
                            mes: json[i].mes
                        }
                    }

                    if (bolPrimeraBusqueda) {
                        $("#buscar").attr("tp", json[0].pot_id);   // Guardamos el numero total de paginas en un campo de la pantalla
                    }

                    gridLibroDiario.resizeCanvas();
                    gridLibroDiario.setData( dataLibroDiario );
                    gridLibroDiario.render();
                    gridLibroDiario.init();
                    gridLibroDiario.autosizeColumns();

                    if ( parseInt( $("#buscar").attr("tp") ) > 1 ) {
                        // Para que respete la paginación, definimos como página actual la última seleccionada
                        if (typeof $("#buscar").attr("upc") === "undefined") {
                            var cPage = 1
                        } else {
                            var cPage = parseInt( $("#buscar").attr("upc") );
                        }
                        var options = {
                            numberOfPages: 5,
                            currentPage: cPage,
                            totalPages: $("#buscar").attr("tp"),
                            itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                            onPageClicked: function(e,originalEvent,type,page){
                                e.stopImmediatePropagation();
                                // Grabamos la ultima página a la que le dieron click
                                $("#buscar").attr("upc",page);

                                // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                const last = $("#buscar").attr("last") || 0;
                                if ( $("#LibroDiarioPaginador").attr("last") != page ) {
                                    $("#buscar").attr("ps",page);
                                    CargaLibroDiario();
                                    $("#LibroDiarioPaginador").attr("last", page );
                                }
                            }
                        }
                        $("#LibroDiarioPaginador").bootstrapPaginator(options);
                        $("#LibroDiarioPaginador").addClass("pagination");
                        $("#LibroDiarioPaginador li").addClass("page-item")
                        $("#LibroDiarioPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                    } else {
                        $("#LibroDiarioPaginador").html("");
                    }

                    window.espera.stop();
                }
            });
        };
    JS;
}

// Código para que funcione el Controlador de Documentos Fuente
// --------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------
function DibujaPantallaDocumentosFuenteJS()
{

    return '

    // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
    // --------------------------------------------------------------------------------------------------------------
    function NumericSorter(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
    }

    function FormatoMoneda(row, cell, value, columnDef, dataContext) {

        // Si hay un filtro activo tomamos de dataFiltrado, si no tomamos de dataDocumentosFuente ...
        if ( $("#buscar").val() == "" ) {
            debe = dataDocumentosFuente[row].d;
            haber = dataDocumentosFuente[row].h;
        } else {
            debe = dataFiltrado[row].d;
            haber = dataFiltrado[row].h;
        }

        // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
        strIni = ""
        strFin = ""
        if( debe < 0 || haber < 0 ) {
            strIni = "<span><font color=#FF0000><b>";
            strFin = "</b></font></span>";
        }


        if (value === null || value === "" || !(value != 0)) {
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

    function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
        return ( value == "C" || value == "H" ) ? "<img src=\'css/images/pin.png\'>" : "";
    }
    function rfcFormatter(row, cell, value, columnDef, dataContext) {
        // Si hay un filtro activo tomamos de dataFiltrado, si no tomamos de dataDocumentosFuente ...
        if ( $("#buscar").val() == "" ) {
            nombre = dataDocumentosFuente[row].n;
        } else {
            nombre = dataFiltrado[row].n;
        }

        rfc = value.trim();
        var retorno = "";
        if ( rfc.length == 12 ) {
            retorno = rfc.substr( 0, 3 ) + "-" + rfc.substr( 3, 6 ) + "-" + rfc.substr( 9, 3 );
        } else if ( rfc.length == 13 ) {
            retorno = rfc.substr( 0, 4 ) + "-" + rfc.substr( 4, 6 ) + "-" + rfc.substr( 10, 3 );
        } else if ( rfc.length == 10 ) {
            retorno = rfc.substr( 0, 4 ) + "-" + rfc.substr( 4, 6 );
        } else {
            retorno = rfc;
        }
        return "<span title=\"" + nombre + "\">" + retorno + "</span>"
    }
    function FormatearTexto(row, cell, value, columnDef, dataContext) {
        // Si hay un filtro activo tomamos de dataFiltrado, si no tomamos de dataDocumentosFuente ...
        if ( $("#buscar").val() == "" ) {
            status = dataDocumentosFuente[row].s;
        } else {
            status = dataFiltrado[row].s;
        }

        // Cuando sea cancelada ... también tachamos el texto
        return ( status == "C" || status == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
    }
    function FormatearTextoRFC(row, cell, value, columnDef, dataContext) {
        // Si hay un filtro activo tomamos de dataFiltrado, si no tomamos de dataDocumentosFuente ...
        if ( $("#buscar").val() == "" ) {
            nombre = dataDocumentosFuente[row].n;
        } else {
            nombre = dataFiltrado[row].n;
        }
        return "<span title=\"" + nombre + "\">" + value + "</span>";
    }

    var gridDocumentosFuente;
    var dataDocumentosFuente = [];
    var dataFiltrado = [];

    var columnsDocumentosFuente = [
        {id: "t",   name: "Tipo", 	  			  	field: "t",  minWidth:  40
        , sortable: true, sorter:NumericSorter },
        {id: "r",   name: "Referencia", 			field: "r",  minWidth: 100, sortable: true, formatter: FormatearTexto, sorter:NumericSorter },
        {id: "f",   name: "Fecha", 					field: "f",  minWidth:  60, sortable: true, formatter: FormatearTexto, sorter:NumericSorter },
        {id: "e",   name: "RFC", 					field: "e",  minWidth: 100, sortable: true, formatter: rfcFormatter, sorter:NumericSorter },
        {id: "c",   name: "Concepto del Documento", field: "c",  minWidth: 150, sortable: true, formatter: FormatearTexto, sorter:NumericSorter },
        {id: "d",   name: "Cargo",     			  	field: "d",  minWidth:  90, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", sortable: true, sorter:NumericSorter, formatter: FormatoMoneda },
        {id: "h",   name: "Abono",     			  	field: "h",  minWidth:  90, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", sortable: true, sorter:NumericSorter, formatter: FormatoMoneda },
        {id: "u",   name: "Cuenta",			 		field: "u",  minWidth:  60, sortable: true, headerCssClass: "alineaCentro", cssClass: "alineaCentro", formatter: FormatearTexto, sorter:NumericSorter },
        {id: "mc",  name: "MC",         			field: "mc", maxWidth:  50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter },
        {id: "v",   name: "Vincula",       			field: "v",  maxWidth:  70, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter },
        {id: "s",   name: "Edo",         			field: "s",  maxWidth:  50, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter, formatter: CheckmarkFormatter },
        {id: "p",   name: "Poliza", 			  	field: "p",  minWidth:  80, headerCssClass: "alineaCentro", cssClass: "alineaCentro", sortable: true, sorter:NumericSorter },
    ];

    var options = {
        enableCellNavigation: true, /* Para permitir que se seleccione una celda */
        enableColumnReorder: false,
        autosizeColsMode: Slick.GridAutosizeColsMode.Legacy,
        explicitInitialization: false
    };

    /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
    function filtrarDocumentosFuente() {
        dataFiltrado = [];
        dataFiltrado = $.grep(dataDocumentosFuente,
            function(element){
                if (element.t != null && element.t != "" && element.t.toUpperCase().indexOf($("#buscar").val().toUpperCase()) == -1) {
                    if (element.r != null && element.r != "" && element.r.indexOf($("#buscar").val().toUpperCase()) == -1) {
                        if (element.f != null && element.f != "" && element.f.indexOf($("#buscar").val().toUpperCase()) == -1) {
                            if (element.e != null && element.e != "" && element.e.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                if (element.c != null && element.c != "" && element.c.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                    if (element.d != null && element.d != "" && element.d.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                        if (element.h != null && element.h != "" && element.h.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                            if (element.u != null && element.u != "" && element.u.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                                if (element.p != null && element.p != "" && element.p.indexOf($("#buscar").val().toUpperCase()) == -1) {
                                                    return false;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return true;
            });
        gridDocumentosFuente.setData(dataFiltrado);
        gridDocumentosFuente.render();
    }

    gridDocumentosFuente = new Slick.Grid($("#myGridDocumentosFuente"), dataDocumentosFuente, columnsDocumentosFuente, options);

    // Función para ordenar una columna del grid. debe existir en su definición del array columns que es sortable y el sorter
    // --------------------------------------------------------------------------------------------------------------------------
    gridDocumentosFuente.onSort.subscribe(function(e, args) {
        sortdir = args.sortAsc ? 1 : -1;
        sortcol = args.sortCol.field;

        dataDocumentosFuente.sort(args.sortCol.sorter, sortdir);
        args.grid.invalidateAllRows();
        args.grid.render();
    });

    // Para que se seleccione toda la línea
    // --------------------------------------------------------------------------------------------------------------
    gridDocumentosFuente.setSelectionModel(new Slick.RowSelectionModel());

    // Para obtener el valor de una celda al presionar doble click en ella
    // --------------------------------------------------------------------------------------------------------------
    gridDocumentosFuente.onDblClick.subscribe(function(e) {
        var cell = gridDocumentosFuente.getCellFromEvent(e),
        row = cell.row;

        // Si no hay nada que selecionar, no hacemos nada
        if ( dataDocumentosFuente[row].pot_id == "" ) { e.stopPropagation();   return false; }
        comunica("scg/poliza/ControladorMuestraPoliza.php",{
            "m" : $("#mesActual").attr( "ma" ),
            "p" : dataDocumentosFuente[row].pot_id,
            "r" : dataDocumentosFuente[row].prf_id,
            "n" : dataDocumentosFuente[row].numero,
            "ue": dataDocumentosFuente[row].ue
        });
    });

    $("#mostrar").click(function(e){
        $("#avisosLD").remove();
        e.preventDefault()
        window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
        $("#buscar").attr("tp","0");
        $("#buscar").attr("ps","");
        CargaDocumentosFuente();
        chkMscId( $("#mesActual").attr("ma") );
        window.espera.stop();
        e.stopPropagation();
        return false;
    });

    // Mostramos los documentos fuente solicitados
    // ===========================================================================
    function CargaDocumentosFuente( l ) {

        dataDocumentosFuente = [];
        dataFiltrado = [];
        $("#filtrarTextoDocumentosFuente").val("");

        var miURL = "lib/dameDocumentosFuente.php";

        if ( $("#buscar").attr("tp") == "0" ) {
            var bolPrimeraBusqueda = true;
        } else {
            var bolPrimeraBusqueda = false;
        }

        $.ajax({
            data : {
                "m"   : $("#mesActual").attr( "ma" ),
                "t"   : $("#tipoActual").attr( "ta" ),
                "tdf" : $("#tdf").attr( "da" ),
                "rfc" : $("#rfc_id").val(),
                "cta" : $("#cuenta").val(),
                "p"   : $("#buscar").attr("ps")
            },
            url:  miURL,
            type: "post",
            success: function ( response ) {
                
                var json = $.parseJSON(response);
                /* alert( JSON.stringify( $.parseJSON(response), null, 4 ) ); */
                /* Empezamos a partir del 1 porque en la 0 traemos el total de los Documentos Fuente */
                for (var i = 1; i < json.length; i++) {
                    dataDocumentosFuente[i-1] = {
                        t:  json[i].t,
                        r:  json[i].r,
                        f:  json[i].f,
                        e:  json[i].e,
                        n:  json[i].n,
                        c:  json[i].c,
                        d:  json[i].d,
                        h:  json[i].h,
                        s:  json[i].s,
                        p:  json[i].p,
                        ue: json[i].ue,
                        mc: json[i].mc,
                        u:  json[i].u,
                        v:  json[i].v,
                        pot_id: json[i].pot_id,
                        prf_id: json[i].prf_id,
                        numero: json[i].numero
                    }
                }

                if (bolPrimeraBusqueda) {
                    $("#buscar").attr("tp", json[0].t);   // Guardamos el numero total de paginas en un campo de la pantalla
                }

                gridDocumentosFuente.resizeCanvas();
                gridDocumentosFuente.setData( dataDocumentosFuente );
                gridDocumentosFuente.render();
                gridDocumentosFuente.init();
                gridDocumentosFuente.autosizeColumns();

                if ( parseInt( $("#buscar").attr("tp") ) > 1 ) {
                    // Para que respete la paginación, definimos como página actual la última seleccionada
                    if (typeof $("#buscar").attr("upc") === "undefined") {
                        var cPage = 1
                    } else {
                        var cPage = parseInt( $("#buscar").attr("upc") );
                    }
                    var options = {
                        numberOfPages: 5,
                        currentPage: cPage,
                        totalPages: $("#buscar").attr("tp"),
                        itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                        onPageClicked: function(e,originalEvent,type,page){
                            // Grabamos la ultima página a la que le dieron click
                            $("#buscar").attr("upc",page);
                            // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                            if ( $("#DocumentosFuentePaginador").attr("last") != page ) {
                                $("#buscar").attr("ps",page);
                                CargaDocumentosFuente();
                                $("#DocumentosFuentePaginador").attr("last", page );
                            }
                        }
                    }
                    $("#DocumentosFuentePaginador").bootstrapPaginator(options);
                } else {
                    $("#DocumentosFuentePaginador").html("");
                }

                window.espera.stop();

            }
        });

    };
    ';
}

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Para buscar un documento, intentaremos diseñar una pantalla genérica que pueda ser llamada desde cualquier parte
// del sinpres, mediante un dialogBox
// HTML : El código que dibuja la pantalla
// JS   : El código que controla su comportamiento
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarDocumentoHTML()
{

    return '
        <div id="cargaDatosDocumentos" style="width:100%;display:none;">
            <div class="row">
                <div class="col-md-2">
                    <input id="ref_buscar" name="ref_buscar" type="text" placeholder="Referencia" class="form-control" tabindex="300">
                </div>
                <div class="col-md-1">
                    <input id="bdo_id_buscar" name="bdo_id_buscar" type="text" placeholder="#ID" class="form-control" tabindex="305">
                </div>
                <div class="col-md-2">
                    <input id="rfc_buscar_docto" name="rfc_buscar_docto" type="text" placeholder="RFC Emisor" class="form-control" tabindex="310">
                </div>
                <div class="col-md-2">
                    <input id="bdo_concepto_buscar" name="bdo_concepto_buscar" type="text" placeholder="Concepto" class="form-control" tabindex="315">
                </div>
                <div class="col-md-1">
                    <button tabindex="320" cat="" data-style="slide-left" class="btn btn-outline-secondary ladda-button" data-style="slide-left" title="Buscar en el Banco de Documentos" data-placement="bottom" id="mostrarDoctos">
                        <i class="fa fa-search me-2"></i>Buscar
                    </button>
                </div>
                <div class="input-group input-group-sm" style="float: right; width:250px;">
                    <input id="filtrarTextoDocumento" tp="0" type="text" class="form-control" placeholder="Texto en la descripción a buscar ..." >
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                </div>
            </div>
            <div style="position:relative">
                <div id="doctosPaginador" style="float:right;position:absolute;right:0;top:35px;z-index:100;">
                </div>
            </div>
            <p></p>
             <div class="tabla-contenedor col-md-12" style="overflow: scroll;">           
                <table id="doctos" class="table table-striped table-bordered">
                    <thead class="sticky-top" style="z-index: 100">
                        <tr role="row" class="warning">
                            <th role="columnheader" class="warning" style= "text-align:center; width:25px;">Tipo</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:200px;">Referencia</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:60px;">Fecha</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:80px;">RFC</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:60px;">Importe</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:405px;">Concepto</th>
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
function DibujaPantallaBuscarDocumentoJS()
{
    return <<<JS
    /* Para mostrar el banco de documentos y que busquen uno */
    var dlgD=$("#cargaDatosDocumentos").dialog({
        title: "BUSCAR Y SELECCIONAR DOCUMENTO",
        resizable: true,
        autoOpen:false,
        modal: true,
        width:$(window).width()-10,
        height:$(window).height()-10,
        dialogClass: "cssDialogAyuda",
        open: function( event, ui ) { buscarDocumentoSolicitado(); },
        create: function(event, ui) { 
            $(event.target).parent().css("position", "fixed"); 
            $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").html("X"); 
        }
    });

    function FormatoMoneda(row, cell, value, columnDef, dataContext) {
        if (value === null || value === "" || !(value != 0)) {
            return "";
        } else {
                var n = value,
            c = 2,
            d = ".",
            t = ","
            s = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");

            /*return "$" + Number(value).toFixed(2);*/
        }
    }

    // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
    // --------------------------------------------------------------------------------------------------------------
    function NumericSorter(a, b) {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
    }

    var gridDoctos;
    var dataDoctos = [ { "tdf": "", "ref": "", "fec": "", "rfc": "", "imp": "", "con": "" } ];
    var dataFiltrado = [];

    /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
    $("#filtrarTextoDocumento").on("keyup", function(e){
        var value = $(this).val().toLowerCase();
        $("#doctos tbody>tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });


    // Función para ordenar una columna del grid. debe existir en su definición del array columns que es sortable y el sorter
    // --------------------------------------------------------------------------------------------------------------------------
    $(document).ready(function() { 
        $("#doctos").on("dblclick", "tbody>tr", function() {
            // Obtener el índice de la fila (comenzando desde 0)
            var rowIndex = $(this).index();

            // Obtener el contenido de la fila
            var rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text());
            });

            var bdo = rowData[6];

            // Si no hay cuenta que seleccionar, no hacemos nada
            if ( bdo == "" ) {
                e.stopPropagation();
                return false;
            }

            var anchoReferencia;

            /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
            if ( $("#filtrarTextoDocumento").val() == "" ) {
                anchoReferencia = rowData[1].length;
                $("#" + docRef ).val( rowData[1] );
                $("#" + docRef ).attr( "tdf", rowData[0] );
                $("#" + docRef ).attr( "bdo", rowData[6] );
                $("#cargaDatosDocumentos").dialog("close");
            } else {
                anchoReferencia = rowData[1].length;
                $("#" + docRef ).val( rowData[1] );
                $("#" + docRef ).attr( "tdf", rowData[0] );
                $("#" + docRef ).attr( "bdo", rowData[6] );
                $("#cargaDatosDocumentos").dialog("close");
            }

            // Si hay un valor en el documento fuente, mostramos los demás campos del documento fuente... si no los ocultamos
            if ( anchoReferencia > 0 ) {
                $("#detalleDocumentoFuente").show();
            } else {
                $("#detalleDocumentoFuente").hide();
            }
        });
    });


    // Cuando le den click a buscar, ponemos a 0 la paginación y guardamos el ultimo documento buscado
    // ===========================================================================
    $("#mostrarDoctos").click(function(e){
        e.preventDefault();

        // Si están buscanlo lo mismo que la última vez, ni los pelamos
        window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
        $("#ref_buscar").attr("tp", "0" );   // total paginas
        $("#ref_buscar").attr("ps", "0" );   // pagina solicitada
        $("#ref_buscar").attr("lastSearch", $("#ref_buscar").val() );
        buscarDocumentoSolicitado();

        window.espera.stop();

        e.stopPropagation();
        return false;

    });

    // Buscamos un documento en la pantalla de selección del Banco de Documentos
    // ===========================================================================
    function buscarDocumentoSolicitado() {

        if ( $("#ref_buscar").val() == "" && $("#rfc_buscar_docto").val() == ""  && $("#bdo_id_buscar").val() == "" && $("#bdo_concepto_buscar").val() == "" ) {
            $("#ref_buscar").attr("tp", "0" );   // total paginas
            return false;
        }

        dataDoctos = [];
        dataFiltrado = [];
        $("#filtrarTextoDocumento").val("");

        var miURL = "lib/dameListaDocumentos.php";

        if ( $("#ref_buscar").attr("tp") == "0" ) {
            var bolPrimeraBusqueda = true;
        } else {
            var bolPrimeraBusqueda = false;
        }
        $("#doctos tbody").html("");

        $.ajax({
            data: { "r" : $("#ref_buscar").val(), "b" : $("#bdo_id_buscar").val(), "f" : $("#rfc_buscar_docto").val(), "c" : $("#bdo_concepto_buscar").val(), "p": $("#ref_buscar").attr("ps") },
            url:  miURL,
            type: "post",
            success: function ( response ) {
                var json = $.parseJSON(response);
                /* Empezamos a partir del 1 porque en la 0 traemos el total de los documentos */
                for (var i = 1; i < json.length; i++) {
                    if(isNaN(json[i].imp)){
                        var imp = formatNumber( parseFloat( json[i].imp ).toFixed(2,' , ', ' . '));
                    }else{
                        imp = "";
                    }
                    var nuevaFila = "<tr class='seleccionable'>";
                        nuevaFila += "<td style='text-align:center;'>" + json[i].tdf + "</td>";
                        nuevaFila += "<td>" + json[i].ref + "</td>";
                        nuevaFila += "<td style='text-align:center;'>" + json[i].fec + "</td>";
                        nuevaFila += "<td style='text-align:center;'>" + json[i].rfc + "</td>";
                        nuevaFila += "<td style='text-align:right;'>" + imp + "</td>";
                        nuevaFila += "<td>" + json[i].con + "</td>";
                        nuevaFila += "<td style='display:none;'>" + json[i].bdo + "</td>";
                        nuevaFila += "</tr>";
                    $("#doctos tbody").append(nuevaFila);
                }

                $(document).ready(function() {
                    $("#doctos tbody tr").hover(
                        function() {
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        },
                        function() {                            
                            $(this).removeClass("seleccionado");
                            $(this).css("font-weight","normal");
                        }
                    );
            
                });

                if (bolPrimeraBusqueda) {
                    $("#ref_buscar").attr("tp", json[0].tdf );   // Guardamos el numero total de paginas de la búsqueda actual
                }

                if ( parseInt( $("#ref_buscar").attr("tp") ) > 1 ) {
                    // Para que respete la paginación, definimos como página actual la última seleccionada
                    if (typeof $("#ref_buscar").attr("upc") === "undefined") {
                        var cPage = 1
                    } else {
                        var cPage = parseInt( $("#ref_buscar").attr("upc") );
                    }
                    var options = {
                        numberOfPages: 5,
                        currentPage: cPage,
                        totalPages: $("#ref_buscar").attr("tp"),
                        itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                        onPageClicked: function(e,originalEvent,type,page){
                            $("#doctos tbody").html("");
                            // Grabamos la ultima página a la que le dieron click
                            $("#ref_buscar").attr("upc",page);
                            // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                            if ( $("#planPaginador").attr("last") != page ) {
                                $("#ref_buscar").attr("ps",page);
                                buscarDocumentoSolicitado();
                                $("#planPaginador").attr("last", page );
                            }
                        }
                    }
                    $("#planPaginador").bootstrapPaginator(options);
                    $("#planPaginador ul").addClass( "pagination");
                    $("#planPaginador li").addClass("page-item")
                    $("#planPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                } else {
                    $("#planPaginador").html("");
                }

                window.espera.stop();

            }
        });

    };
    JS;
}

// ------------------------------------------------------------------------------------------------------------------------
// Marcos: 16/ene/2018
// F1 para buscar y seleccionar Claves de Financiamiento DESDE LA CONTABILIDAD... Será usado principalmente por:
// 1. Tesoreria... que le mostrará TODAS las claves, ya que ellos las controlan todas
// 2. Contabilidad... pudiendo filtrar por UR / UR + UE / Carga de Trabajo, dependiendo del nivel jerárquico del usuario
// DEBERÁN PASAR el parámetro TRUE a la función cuando sea para tesorería, para que NO APLIQUE NINGUN WHERE, y muestre todas
// ------------------------------------------------------------------------------------------------------------------------
function DibujaPantallaBuscarCgClaveFinaciamientoHTML($esTesoreria = false, $esContabilidad116 = false)
{

    if (isset($esTesoreria) && $esTesoreria == true) {
        $tesoreria = '1';
    } else {
        $tesoreria = '0';
    }
    if (isset($esContabilidad116) && $esContabilidad116 == true) {
        $esContabilidad116html = '<input name="chk116" id="chk116" data-placement="bottom" title="Mostrar sólo de UR 116" type="checkbox">UR 116</input>';
    } else {
        $esContabilidad116html = '';
    }

    return '
        <div id="cargaDatosCgCveFin" style="width:100%;display:none;">
            <div class="row">
                <div class="col-md-2 col-xs-2">
                    <input name="chkFed" id="chkFed" data-placement="bottom" title="Mostrar sólo Federales" type="checkbox">Federal</input>
                    <input name="chkEst" id="chkEst" data-placement="bottom" title="Mostrar sólo Estatales" type="checkbox">Estatal</input>
                    ' . $esContabilidad116html . '
                </div>
                <div class="col-md-2 col-xs-2">
                    <input id="anio_buscar" name="anio_buscar" type="text" placeholder="Año" maxlength=2 class="form-control" tabindex="305">
                </div>
                <div class="col-md-2 col-xs-2">
                    <input id="concepto_buscar" name="concepto_buscar" type="text" placeholder="Concepto" class="form-control" tabindex="310">
                </div>
                <div class="col-md-1 col-xs-1">
                    <button type="button" id="mostrarCvesFin" tabindex="315" cat="" tes="' . $tesoreria . '" data-style="slide-left" class="btn btn-outline-secondary" data-style="slide-left" title="Buscar Claves de Financiamiento" data-placement="bottom"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar</button>
                </div>

                <div class="input-group input-group-sm" style="float: right; width:250px">
                    <input tabindex="320" id="filtrarTexto" tp="0" type="text" class="form-control" placeholder="Texto a buscar ..." aria-describedby="btnGroupAddon">
                    <div class="input-group-text" id="btnGroupAddon"><i class="fa-solid fa-magnifying-glass"></i></div>
                </div>
            </div>
            <div id ="div-paginas" style="position:relative">
                <div id="planPaginador" style="float:right;position:absolute;right:0;top:-20px;z-index:100;">
                </div>
            </div>
            <p></p> 
            <div class="tabla-contenedor col-md-12" style="overflow: scroll;">           
                <table id="cgCveFin" class="table table-striped table-bordered">
                    <thead class="sticky-top" style="z-index: 100">
                        <tr role="row" class="warning">
                            <th role="columnheader" class="warning" style= "text-align:center; width:30px;">TipoRec</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:80px;">Año</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:120px;">Clave</th>
                            <th role="columnheader" class="warning" style= "text-align:center;  width:530px;">Descripción</th>
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
function DibujaPantallaBuscarCgClaveFinaciamientoJS()
{
    return <<<JS
        /* Para mostrar el catalogo de cuentas y que busquen una */
        var dlgCveFin = $("#cargaDatosCgCveFin");
        $(dlgCveFin).remove()
        $(dlgCveFin).dialog({
            title: "BUSCAR Y SELECCIONAR CLAVE DE FINANCIAMIENTO",
            resizable: true,
            autoOpen:false,
            modal: true,
            width:$(window).width()-10,
            height:$(window).height()-10,
            dialogClass: "cssDialogAyuda",
            open: function( event, ui ) { buscarCgCveFin(); },
            create: function(event, ui) {
                $(event.target).parent().css("position", "fixed");
                $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").html("X");
            }
        });

        var gridCveFin;
        var dataCveFin = [ { "ecf": "", "gpo": "", "ur": "", "ue": "", "ter": "", "anio": "", "cve": "", "nom" : "", "sts": "" } ];
        var dataCgCveFinFiltrado = [];

        /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
        $("#filtrarTexto").on("keyup", function(e){                     
            var value = $(this).val().toLowerCase();
            $("#cgCveFin tbody>tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Para obtener el valor de una celda al presionar doble click en ella
        // --------------------------------------------------------------------------------------------------------------
            $(document).on("dblclick", "#cgCveFin tbody>tr", function() {
                // Obtener el índice de la fila (comenzando desde 0)
                var rowIndex = $(this).index();

                 // Obtener el contenido de la fila
                var rowData = [];
                $(this).find('td').each(function() {
                    rowData.push($(this).text().replace( /\./g, "" ));
                });
                 
                if ( $("#filtrarTexto").val() == "" ) {
                    $("#" + cveFinID  ).attr( "ecf", rowData[4] );
                    $("#" + cveFinID  ).val( rowData[2] );
                    if(cveFinNom != ""){
                        $("#" + cveFinNom ).val( rowData[3] );
                    }
                } else {
                    $("#" + cveFinID  ).attr( "ecf", rowData[4] );
                    $("#" + cveFinID  ).val( rowData[0] );
                    if(cveFinNom != ""){
                        $("#" + cveFinNom ).val( rowData[3] );
                    }
                } 
                setTimeout( function() { $("#" + cveFinID ).focus() }, 100 );
                $(this).closest(".ui-dialog-content").dialog("close");
            });

            $("#mostrarCvesFin").on('click', function(e){
                e.preventDefault()
                buscarCgCveFin();
                e.stopPropagation();

            });


        // Buscamos una cuenta en la pantalla de selección de cuenta contable
        // ===========================================================================
        function buscarCgCveFin() {

            dataCveFin = [];
            dataCgCveFinFiltrado = [];
            $("#filtrarTexto").val("");

            var miURL = "lib/dameCgClaveFinanciamiento.php";

            // Determinamos si ya hay paginación ...
            if ( $("#filtrarTexto").attr("tp") == "0" ) {
                var bolPrimeraBusqueda = true;
            } else {
                var bolPrimeraBusqueda = false;
            }
            $("#cgCveFin tbody").html("");

            $.ajax({
                data: { "p": $("#filtrarTexto").attr("ps"), "t": $("#mostrarCvesFin").attr("tes"), "fed" : $("#chkFed").prop("checked"), "est" : $("#chkEst").prop("checked"), "a": $("#anio_buscar").val(), "c": $("#concepto_buscar").val(),"ur116" : $("#chk116").prop("checked") },
                url:  miURL,
                type: "post",
                success: function ( response ) {
                    var json = $.parseJSON(response);
                    
                    // Se seleccion el tbody de la tabla con id cgCveFin y se setea vacio
                     $("#cgCveFin tbody").html("");
                     // Se valida que el json venga con mas de 1 elemento, sino solo trae encabezado y paginación
                     if (json.length === 1 || (json.length > 1 && json[1].ecf === "0" && json[1].nom === "")) {
                    // Se le dice al usuario que no hay registros insertando una fila en tbody
                        $("#cgCveFin tbody").append(`
                            <tr>
                                <td colspan="4" style="text-align:center; font-weight:bold;">
                                    NO SE ENCONTRARON REGISTROS
                                </td>
                            </tr>
                        `);
                    // Sin registros no se muestra paginador
                        $("#planPaginador").html("");
                        return;
                    }
                    /* Empezamos a partir del 1 porque en la 0 traemos el total de las cuentas */
                    for (var i = 1; i < json.length; i++) {
                        var nuevaFila = "<tr class='seleccionable'>";
                        nuevaFila += "<td>" + json[i].ter + "</td>";
                        nuevaFila += "<td>" + json[i].anio + "</td>";
                        nuevaFila += "<td>" + json[i].cve + "</td>";
                        nuevaFila += "<td>" + json[i].nom + "</td>";
                        nuevaFila += "<td style='display:none;'>" + json[i].ecf + "</td>";
                        nuevaFila += "</tr>";
                        $("#cgCveFin tbody").append(nuevaFila);
                    }

                    $(document).ready(function() {
                        $("#cgCveFin tbody tr").hover(
                            function() {
                                $(this).addClass("seleccionado");
                                $(this).css("font-weight","bold");
                            },
                            function() {                            
                                $(this).removeClass("seleccionado");
                                $(this).css("font-weight","normal");
                            }
                        );
                    });

                    if (bolPrimeraBusqueda) {
                        $("#filtrarTexto").attr("tp", json[0].ecf );   // Guardamos el numero total de paginas de la cuenta actual
                    }

                    if ( parseInt( $("#filtrarTexto").attr("tp") ) > 1 ) {
                        // Para que respete la paginación, definimos como página actual la última seleccionada
                        if (typeof $("#filtrarTexto").attr("upc") === "undefined") {
                            var cPage = 1
                        } else {
                            var cPage = parseInt( $("#filtrarTexto").attr("upc") );
                        }
                        var options = {
                            numberOfPages: 5,
                            currentPage: cPage,
                            totalPages: $("#filtrarTexto").attr("tp"),
                            itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                            onPageClicked: function(e,originalEvent,type,page){
                                $("#cgCveFin tbody").html("");
                                // Grabamos la ultima página a la que le dieron click
                                $("#filtrarTexto").attr("upc",page);
                                // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                if ( $("#planPaginador").attr("last") != page ) {
                                    $("#filtrarTexto").attr("ps",page);
                                    buscarEncProg();
                                    $("#planPaginador").attr("last", page );
                                }
                            }
                        }
                        $("#planPaginador").bootstrapPaginator(options);
                        $("#planPaginador").addClass("pagination");
                        $("#planPaginador li").addClass("page-item")
                         $("#plaPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                    } else {
                        $("#planPaginador").html("");
                    }
                }
            });
        };
    JS;
}
// ========================================================================================================================
// FIN DEL CODIGO
// ========================================================================================================================
