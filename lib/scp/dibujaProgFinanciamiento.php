<?php
function DibujarPantallaBuscarProgramaFinanciamientoHTML()
{
    return '
        <div id="cargaDatosProgFin" style="width:100%;display:none;">
            <div class="input-group input-group-sm" style="float: left; width:600px">
                <input id="filtrarTexto" tp="0" type="text" class="form-control" onKeyUp="filtrarProgFte()" placeholder="Texto en la descripción a buscar ..." >
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                &nbsp;&nbsp;&nbsp;
                <span style="color:red">
                ( doble click sobre la línea del encuadre para seleccionarlo )
                </span>
            </div>
            <br />
            <div style="position:relative">
                <div id="planPaginador" style="float:right;position:absolute;right:0;top:-10px;z-index:100;"></div>
            </div>
            <p>&nbsp;</p>
            <div id="myGridProgFte" style="width:100%;height:92%;">
            </div>
        </div>
	';

} //FIN DibujarPantallaBuscarProgramaFinanciamientoHTML

/* El filtro filtroObr recibe el numero de contrato */
function DibujarPantallaBuscarProgramaFinanciamientoJS($filtro = '', $ecf_id = '', $mod_ue_id = '', $toa_id = '', $filtroObr = '', $paso = '')
{
    $addFiltro = (($filtro != '') ? ',"filtro":"' . $filtro . '" ' : "");
    $addFiltro .= (($paso != '') ? ',"paso":' . $paso . ' ' : "");
    $addFiltro .= (($ecf_id != '') ? ',"ecf_id":"' . $ecf_id . '" ' : "");
    $addFiltro .= (($mod_ue_id != '') ? ',"mod_ue_id":' . $mod_ue_id . ' ' : "");
    $addFiltro .= (($toa_id != '') ? ',"toa_id":' . $toa_id . ' ' : "");
    $addFiltro .= (($filtroObr != '') ? ',"ipu_id":' . $filtroObr . ' ' : "");

    // Marcos: 10/ene/2018
    // aquí pondremos un IF para regresar otra variable y que no haya problema... en teoría al menos...
    return '
        /* Para mostrar el catalogo de cuentas y que busquen una */
        var dlgC=$("#cargaDatosProgFin").dialog({
            title: "BUSCAR Y SELECCIONAR ENCUADRE PROGRAMATICO + FUENTE DE FINANCIAMIENTO",
            resizable: true,
            autoOpen:false,
            modal: true,
            width:$(window).width()-10,
            height:$(window).height()-10,
            dialogClass: "cssDialogAyuda",
            open: function( event, ui ) { buscarProgFte(); },
            create: function(event, ui) {
                $(event.target).parent().css("position", "fixed");
                $(".ui-dialog-titlebar-close").addClass("btn btn-danger btn-small").append("X");
            }
        });

        /*function epgFormatter(row, cell, value, columnDef, dataContext) {
            var epg = dataProgFin[row].prog;

        // Si la parte de obra/actividad empieza con 1, es actividad.    Si no, es obra
        if ( epg.substr( 19, 1 ) == "1" ) {
            return epg.substr( 0, 19 ) + epg.substr( 20, 3 ) + ".000";
        } else {
            return epg.substr( 0, 19 ) + "000." + epg.substr( 20, 3 );
        }
        }*/

        var gridProgFin;
        var dataProgFin = [ { "epg_id": "", "ecf_id": "", "prog": "", "nombre" : "", "npy":"", "npro": "", "cveF": "", "ncve": "" } ];
        var dataFiltrado = [];

        var columnsProgFin = [
            {id:"prog", name:"Encuadre Programático", field:"prog", minWidth: 170, sortable: true, sorter:NumericSorter},
            {id:"npy", name:"Nombre Proyecto", field:"npy", minWidth: 240, sortable: true, sorter:NumericSorter},
            {id:"npro", name:"Nombre Obra/Actividad", field:"npro", minWidth: 240, sortable: true, sorter:NumericSorter},
            {id:"cveF", name:"Clave de Financiamiento", field:"cveF", minWidth: 150, sortable: true, sorter:NumericSorter},
            {id:"ncve", name:"Nombre de la Clave de Financiamiento", field:"ncve", minWidth: 200, sortable: true, sorter:NumericSorter},
        ];

        var options = {
            enableCellNavigation: true, /* Para permitir que se seleccione una celda */
            enableColumnReorder: false,
            autosizeColsMode: Slick.GridAutosizeColsMode.Legacy
        };

        /*Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
        function filtrarProgFte() {
            dataFiltrado = [];
            dataFiltrado = $.grep(dataProgFin,
                function(element){
                    if ( ( element.prog != null && element.prog != "" ) ) {
                        if ( element.prog.toUpperCase().indexOf($("#filtrarTexto").val().toUpperCase()) !== -1 ||
                            element.npy.toUpperCase().indexOf($("#filtrarTexto").val().toUpperCase()) !== -1 ||
                            element.npro.toUpperCase().indexOf($("#filtrarTexto").val().toUpperCase()) !== -1 ||
                            element.cveF.toUpperCase().indexOf($("#filtrarTexto").val().toUpperCase()) !== -1 ||
                            element.ncve.toUpperCase().indexOf($("#filtrarTexto").val().toUpperCase()) !== -1 ) {
                        return true;
                        }
                    }
                    return false;
                });
            gridProgFin.setData(dataFiltrado);
            gridProgFin.render();
        }

        gridProgFin = new Slick.Grid($("#myGridProgFte"), dataProgFin, columnsProgFin, options);

        // Función para ordenar una columna del grid. debe existir en su definición del array columns que es sortable y el sorter
        // --------------------------------------------------------------------------------------------------------------------------
        gridProgFin.onSort.subscribe(function(e, args) {
            sortdir = args.sortAsc ? 1 : -1;
            sortcol = args.sortCol.field;

            dataProgFin.sort(args.sortCol.sorter, sortdir);
            args.grid.invalidateAllRows();
            args.grid.render();
        });

        // Para que se seleccione toda la línea
        // -----------------------------------------------------------------------------------------------------

        gridProgFin.setSelectionModel(new Slick.RowSelectionModel());

        // Para obtener el valor de una celda al presionar doble click en ella
        // -----------------------------------------------------------------------------------------------------

            gridProgFin.onDblClick.subscribe(function(e) {

           var row = gridProgFin.getSelectedRows();

            /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
            if ( $("#filtrarTexto").val() == "" ) {
                $("#" + progID  ).attr( "enc", dataProgFin[row].epg_id );
                $("#" + progID  ).attr( "ori", dataProgFin[row].prog );
                $("#" + progID  ).val( dataProgFin[row].prog );
                $("#" + progNom ).val( dataProgFin[row].nombre.replace( /\./g, "" ) );
                $("#" + cveFID  ).attr( "enc", dataProgFin[row].ecf_id );
                $("#" + cveFID  ).attr( "ori", dataProgFin[row].cveF );
                $("#" + cveFID  ).val( dataProgFin[row].cveF );
                $("#" + cveFNom ).val( dataProgFin[row].ncve.replace( /\./g, "" ) );
                comunica( "srv/ClasificacionFinanciamiento.php", {
                    metodoCveFin: "selEncProgFin",
                    encProg: dataProgFin[row].epg_id.trim(),
                    prog: dataProgFin[row].prog.trim(),
                    encCveFin: dataProgFin[row].ecf_id.trim(),
                    cveFin: dataProgFin[row].cveF.trim(),
                    nomFin: dataProgFin[row].ncve.replace( /\./g, "" )
                });
            } else {
                $("#" + progID  ).attr( "enc", dataFiltrado[row].epg_id );
                $("#" + progID  ).attr( "ori", dataFiltrado[row].prog );
                $("#" + progID  ).val( dataFiltrado[row].prog );
                $("#" + progNom ).val( dataFiltrado[row].nombre.replace( /\./g, "" ) );
                $("#" + cveFID  ).attr( "enc", dataFiltrado[row].ecf_id );
                $("#" + cveFID  ).attr( "ori", dataFiltrado[row].cveF );
                $("#" + cveFID  ).val( dataFiltrado[row].cveF );
                $("#" + cveFNom ).val( dataFiltrado[row].ncve.replace( /\./g, "" ) );
                comunica( "srv/ClasificacionFinanciamiento.php", {
                    metodoCveFin: "selEncProgFin",
                    encProg: dataFiltrado[row].epg_id.trim(),
                    prog: dataFiltrado[row].prog.trim(),
                    encCveFin: dataFiltrado[row].ecf_id.trim(),
                    cveFin: dataFiltrado[row].cveF.trim(),
                    nomFin: dataFiltrado[row].ncve.replace( /\./g, "" )
                });
            }
            $( "#encProg" ).blur();
            //setTimeout( function() { $( "#" + nextField  ).focus() }, 100 );
            $("#cargaDatosProgFin").dialog("close");
            $("#encCveFin").blur();
        });

        // Buscamos una cuenta en la pantalla de selección de cuenta contable
        // ===========================================================================
        function buscarProgFte() {

            dataProgFin = [];
            dataFiltrado = [];
            $("#filtrarTexto").val("");

            var miURL = "lib/dameProgramaFinanciamiento.php";

            // Determinamos si ya hay paginación ...
            if ( $("#filtrarTexto").attr("tp") == "0" ) {
                var bolPrimeraBusqueda = true;
            } else {
                var bolPrimeraBusqueda = false;
            }

            $.ajax({
                data: { "p": $("#filtrarTexto").attr("ps")' . $addFiltro . ' },
                url:  miURL,
                type: "post",
                success: function ( response ) {
                    var json = $.parseJSON(response);
                    /* alert( JSON.stringify( $.parseJSON(response), null, 4 ) ); */
                    /* Empezamos a partir del 1 porque en la 0 traemos el total de las cuentas */
                    for (var i = 1; i < json.length; i++) {
                        dataProgFin[i-1] = {
                            id: i-1,
                            epg_id : json[i].epg_id,
                            ecf_id : json[i].ecf_id,
                            prog:  json[i].prog,
                            nombre:  json[i].nombre,
                            npy:  json[i].npy,
                            npro:  json[i].npro,
                            cveF:  json[i].cveF,
                            ncve:  json[i].ncve
                        }
                    }

                    if (bolPrimeraBusqueda) {
                        $("#filtrarTexto").attr("tp", json[0].prog );   // Guardamos el numero total de paginas de la cuenta actual
                    }

                    gridProgFin.resizeCanvas();
                    gridProgFin.setData( dataProgFin );
                    gridProgFin.render();
                    gridProgFin.init();
                    gridProgFin.autosizeColumns();

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
                            itemContainerClass: function ( type, page, current ) { return "nav-link me-1 " + ((page === current) ? "active" : "pointer-cursor"); },
                            onPageClicked: function(e,originalEvent,type,page){
                                // Grabamos la ultima página a la que le dieron click
                                $("#filtrarTexto").attr("upc",page);
                                // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                if ( $("#planPaginador").attr("last") != page ) {
                                    $("#filtrarTexto").attr("ps",page);
                                    buscarProgFte();
                                    $("#planPaginador").attr("last", page );
                                }
                            }
                        }
                        $("#planPaginador").bootstrapPaginator(options);
                    } else {
                        $("#planPaginador").html("");
                    }
                }
            });
        };
    ';

} //FIN DibujarPantallaBuscarProgramaFinanciamientoJS
?>