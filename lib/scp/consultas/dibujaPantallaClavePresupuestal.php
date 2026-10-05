<?php

/**
 * Description of ControladorConsultaPartidasFolios
 *
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 14-Feb-2019
 * Comment: -----
 * Objective:
- Permite al usuario filtrar y seleccionar una clave Presupuestal
- Datos que se mostrara en la pantalla modal
 * Regla de Negocio: -----
 * */
function dibujaClavePresupuestalHtml()
{
    return '
        <div id="cargaDatosCvePre" style="width:100%;display:none;">
            <div class="input-group input-group-sm" style="float: left; width:600px">
                <input id="filtrarTexto" tp="0" type="text" class="form-control" onKeyUp="filtrarCvePre()" placeholder="Texto en la descripción a buscar ..." >
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                &nbsp;&nbsp;&nbsp;
                <span style="color:red">
                (doble click sobre la línea de la clave para seleccionarlo )
                </span>
            </div>
            <br />
            <div style="position:relative">
                <div id="planPaginador" style="float:right;position:absolute;right:0;top:-10px;z-index:100;"></div>
            </div>
            <p>&nbsp;</p>
            <div id="myGridCvePre" style="width:100%;height:92%;">
            </div>
        </div>
    ';
}

function dibujaClavePresupuestalJS()
{
    return '
        /* Para mostrar el catalogo de cuentas y que busquen una */
            var dlgCvePre=$("#cargaDatosCvePre").dialog({
            title: "BUSCAR Y SELECCIONAR CLAVE PRESUPUESTAL",
            resizable: true,
            autoOpen:false,
            modal: true,
            width:$(window).width()-10,
            height:$(window).height()-10,
            dialogClass: "cssDialogAyuda",
            open: function( event, ui ) { buscarCvePre(); },
            create: function(event, ui) {
                $(event.target).parent().css("position", "fixed");
                $(".ui-dialog-titlebar-close").addClass("btn btn-danger btn-small").append("X");
            }
        });

        var gridCvePre;
        var dataCvePre = [ { "epg_id": "", "ecf_id": "", "prog": "", "nombre" : "", "npy":"", "npro": "", "cveF": "", "ncve": "" } ];
        var dataFiltrado = [];

        /*var columnsCvePre = [
            {id:"clave", name:"CvePresupuestal", field:"clave", minWidth: 50, sortable: true, sorter:NumericSorter}
        ];*/

        var columnsCvePre = [
            {id:"prog", name:"Clave Presupuestal", field:"prog",	minWidth: 170, sortable: true, sorter:NumericSorter},
            {id:"npy", name:"Nombre Proyecto", field:"npy", minWidth: 240, sortable: true, sorter:NumericSorter},
            {id:"npro", name:"Nombre Obra/Actividad", field:"npro", minWidth: 240, sortable: true, sorter:NumericSorter},
            {id:"cveF", name:"Clave de Financiamiento", field:"cveF", minWidth: 150, sortable: true, sorter:NumericSorter},
            {id:"ncve", name:"Nombre de la Clave de Financiamiento", field:"ncve",	minWidth: 200, sortable: true, sorter:NumericSorter},
        ];

        var options = {
            enableCellNavigation: true, /* Para permitir que se seleccione una celda */
            enableColumnReorder: false,
            autosizeColsMode: Slick.GridAutosizeColsMode.Legacy,
        };

        /* Para filtrar el resultado del grid ... y buscar más precisamente EN EL RECORSET dibujado en pantalla */
        function filtrarCvePre() {
            dataFiltrado = [];
            dataFiltrado = $.grep(dataCvePre,
                function(element){
                    console.log($("#filtrarTexto").val());
                    if (( element.prog != null && element.prog != "" )) {
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
            gridCvePre.setData(dataFiltrado);
            gridCvePre.render();
        }

        gridCvePre = new Slick.Grid($("#myGridCvePre"), dataCvePre, columnsCvePre, options);

        // Función para ordenar una columna del grid. debe existir en su definición del array columns que es sortable y el sorter
        // ----------------------------------------------------------------------------------------------------------------
        gridCvePre.onSort.subscribe(function(e, args) {
            sortdir = args.sortAsc ? 1 : -1;
            sortcol = args.sortCol.field;

            dataCvePre.sort(args.sortCol.sorter, sortdir);
            args.grid.invalidateAllRows();
            args.grid.render();
        });

        // Para que se seleccione toda la línea
        // --------------------------------------------------------------------------------------------------------------
        gridCvePre.setSelectionModel(new Slick.RowSelectionModel());

        // Para obtener el valor de una celda al presionar doble click en ella
        // --------------------------------------------------------------------------------------------------------------
        gridCvePre.onDblClick.subscribe(function(e) {
            var row = gridCvePre.getSelectedRows();

            /* Si hay un filtro activo, debemos buscar en el array dataFiltrado */
            if ( $("#filtrarTexto").val() == "" ) {
                $("#" + cveFinID  ).val(dataCvePre[row].prog );
            } else {
                $("#" + cveFinID  ).val(dataFiltrado[row].prog );
            }
            $("#cargaDatosCvePre").dialog("close");
        });


        // Buscamos una cuenta en la pantalla de selección de cuenta contable
        // ===========================================================================
        function buscarCvePre() {
            dataCvePre = [];
            dataFiltrado = [];
            $("#filtrarTexto").val("");

            var miURL = "lib/dameClavePresupuestal.php";

            // Determinamos si ya hay paginación ...
            if ( $("#filtrarTexto").attr("tp") == "0" ) {
                var bolPrimeraBusqueda = true;
            } else {
                var bolPrimeraBusqueda = false;
            }

            $.ajax({
                data: { "v" : $("#filtrarTexto").val(),"p": $("#filtrarTexto").attr("ps") },
                url:  miURL,
                type: "post",
                success: function ( response ) {
                    // console.log(response);
                    var json = $.parseJSON(response);
                    /* alert( JSON.stringify( $.parseJSON(response), null, 4 ) ); */
                    /* Empezamos a partir del 1 porque en la 0 traemos el total de las cuentas */
                    for (var i = 1; i < json.length; i++) {
                        /*dataCvePre[i-1] = {
                            id: i-1,
                            gpo :   json[i].gpo,
                            ue:     json[i].ue,
                            epg:    json[i].epg,
                            pg:   	json[i].pg,
                            clave:  json[i].clave
                        }*/
                        dataCvePre[i-1] = {
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
                        $("#filtrarTexto").attr("tp", json[0].nombre );   // Guardamos el numero total de paginas
                    }

                    gridCvePre.resizeCanvas();
                    gridCvePre.setData( dataCvePre );
                    gridCvePre.render();
                    gridCvePre.init();
                    gridCvePre.autosizeColumns();

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
                                    buscarCvePre();
                                    $("#planPaginador").attr("last", page );
                                }
                            }
                        }
                        $("#planPaginador").bootstrapPaginator(options);

                    } else {
                        $("#planPaginador").html("");
                    }
                }
            });// fin ajax
        };
    ';
}