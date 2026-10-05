<?php

/*--------------------------------------------------------------------------------------------------------
* ControladorVinculacionPorClcFondos
*
* Elaboró: Marcos
*
* Diferencias con respecto a la pantalla de vincuación por CLC que ven todas las URs:
* -> Sólo verán las CLC en estado: 7, y del 10 al 17, mostrando la clave y nombre real, ya que son los estados de Tesorería propiamente
* -> Tendrá un filtro de UR
* -> Verá sólo los vinculos a los siguientes momentos contables:
*		Provisión
*		Ministrado
*		Pagos a Terceros
*		Rectificado (si tiene)

* Marcos: 11/mayo/2018
* De acuerdo al ticket #304875 (donde viene la definición de Israel de fecha 10/may/2018), 
* lo de los estados y momentos contables queda como sigue:
*
* 1. Se mostrarán todas las clcs que estén en estado >= 7
* 2. 	- Estado entre 7 y 9 : CERE O CERECO GENERADA
*		- Estado entre 10 y 14 :	EN PROCESO POR Tesorería		// Ajustado para reflejar el concepto
*		- Estado 15 : 				ENVIADA A BANCA ELECTRÓNICA
*		- Estado entre 16 y 18 : 	CON PAGO TRANSFERIDO
*		- Estado 19 : 				CERE/CERECO CON PAGO RECHAZADO 	// Ajustado para reflejar el concepto
*		- Estado 20 : 				CLC INUTILIZABLE
*		- Estado 21	: 				CLC CANCELADA
*
* 3. Los momentos contables que se mostrarán en la pantalla son:
*		- G para la póliza de la Provisión de la CERE
*		- B para la transferencia de la CERE/CERECO
*		- K para el Rectificado
--------------------------------------------------------------------------------------------------------*/
session_start();
require_once dirname(__FILE__).'/../../lib/WebResponse.php';

class ControladorVinculacionPorClcFondos extends WebResponse
{

    //Constructor
    public function __construct() {

        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case 'getTablaClcs':
                $this->getTablaClcs();
                break;
            default:
                $this->PonBarraBotones();
                break;
        }
        
        $this->returnJson();
    }
        
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function PonBarraBotones() 
    {
        // Si llaman a la pantalla desde una opción del sinpres y pasan el RFC, que muestre esos valores por default
        $rfc = ((isset($this->prm->rfc) && $this->prm->rfc != "") ? $this->prm->rfc : "");
        $bandera = ((isset($this->prm->bandera) && $this->prm->bandera != "") ? $this->prm->bandera : "");

        // Determinamos el status de los meses
        $conexion =  new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql( 'SELECT msc_id, LOWER( msc_nombre ) AS nombre, ems_id FROM cat_MesContable LEFT OUTER JOIN 
            ( SELECT * FROM cg_MesContable WHERE gpo_id = :gpo AND ur_id = :ur) AS s 
            USING ( msc_id ) 
            WHERE msc_id BETWEEN 1 AND 12 
            ORDER BY msc_id' );
        $parametro = [':gpo'=>$_SESSION[_S]['clasAdm-gpo'],':ur'=>$_SESSION[_S]['clasAdm-ur']];
        $conexion->consultar($parametro);
        $conexion->desconectar();

        // Botones
        $strStatusMes = "";
        while ($row = $conexion->extraer()) {
            // En una variable meteremos el status para ponerlo en el DOM y usarlo desde ahí
            $strStatusMes .= (($row['ems_id'] == 'A') ? '.' : ' ');
            // Seguimos con el dibujado de la pagina normal
            $strStatusMes .= '<li><a href=' . intval($row['msc_id']) . ' class="dropdown-item mesContable">' .
                (($row['ems_id'] == 'A') ?
                    '<i class="fa fa-pencil me-2"></i>' : '<span class="ps-3"></span>') .
                ucfirst($row['nombre']) . '</a></li>';
        };

        // Obtenemos todos los tipos de CLC
        $conexion->conectar();
        $conexion->setSql('SELECT tcl_id, tcl_nombre FROM cat_TipoClc ORDER BY tcl_id;');
        $conexion->consultar();

        $menu2 = '<li><a href=0 class="dropdown-item tipoClc">TODOS LOS TIPOS DE CLC</a></li>
            <li class="dropdown-divider"></li>';
        while ($row = $conexion->extraer()) {
            // Seguimos con el dibujado de la pagina normal
            $menu2  .= '<li><a href=' . $row['tcl_id'] . ' class="dropdown-item tipoClc">' .
                $row['tcl_id'] . " - " . $row['tcl_nombre'] . '</a></li>';
        };

        // Agregamos los modos de pago de la CLC
        $conexion->setSql('SELECT mpc_id, mpc_nombre FROM cat_ModoPagoClc ORDER BY mpc_id;');
        $conexion->consultar();
        while ($row = $conexion->extraer()) {
            // Seguimos con el dibujado de la pagina normal
            $menu3 = '<li><a href=' . $row['mpc_id'] . ' class="dropdown-item tipoClc">' .
                $row['mpc_id'] . " - " . $row['mpc_nombre'] . '</a></li>';
        }
        $botones = '
            <div class="row">
                <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="dropdown btn-group">
                            <button id="mesActual" ma=0 class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" data-placement="right" title="Mes de la fecha de envío">
                            Mes
                            </button>
                            <ul class="dropdown-menu">
                            ' . $strStatusMes . '
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button id="tipoClcActual" data-placement="right" title="Filtrar por Tipo de CLC" ta=0 class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                Tipo
                            </button>
                            <ul class="dropdown-menu">
                                ' . $menu2 . '
                                <li class="dropdown-divider"></li>
                                <li><a href="OA" class="dropdown-item tipoClc">RECURSO ESTATAL</a></li>
                                <li><a href="OB" class="dropdown-item tipoClc">RECURSO FEDERAL</a></li>
                                <li class="dropdown-divider"></li>
                                ' . $menu3 . '
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button id="edoActual" ea=0 data-placement="right" title="Filtrar por Estado de la CLC" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            Estado
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href=0 class="dropdown-item estadoClc">TODOS LOS ESTADOS</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a href="EC_A" class="dropdown-item estadoClc">G1 - CERE O CERECO GENERADA</a></li>
                                <li><a href="EC_B" class="dropdown-item estadoClc">G2 - EN PROCESO POR TESORERÍA</a></li>
                                <li><a href="EC_C" class="dropdown-item estadoClc">16 - ENVIADA A BANCA ELECTRÓNICA</a></li>
                                <li><a href="EC_D" class="dropdown-item estadoClc">B  - CON PAGO TRANSFERIDO</a></li>
                                <li><a href="EC_E" class="dropdown-item estadoClc">19 - CERE/CERECO CON PAGO RECHAZADO</a></li>
                                <li><a href="EC_F" class="dropdown-item estadoClc">20 - CLC INUTILIZABLE</a></li>
                                <li><a href="EC_G" class="dropdown-item estadoClc">21 - CLC CANCELADA</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button id ="reportes" class="btn btn-outline-danger dropdown-toggle me-1" aria-disabled="true" data-bs-toggle="dropdown" cat="" disabled><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                            <ul class="dropdown-menu">
                                <li><a href="clc" class="dropdown-item imprimir" data-placement="right" title="Consultar la versión impresa de la CLC seleccionada">CLC Actual</a></li>
                                <li><a href="#" class="dropdown-item" id="reporte"  data-placement="right" title="Reporte de CLCs y las pólizas contables vinculadas" >Relación de CLCs y Pólizas</a></li>
                                <!--
                                <li class="dropdown-divider"></li>
                                <li><a href="clcComp" class="dropdown-item imprimir " data-placement="right" title="CLC seleccionada agrupada por Número de Compromiso">CLC - Comprometido</a></li>
                                -->
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button button id="exportarV" type="submit" title="a EXCEL" class="btn btn-outline-success me-1" cat="" tabindex="30" aria-disabled="true" disabled><i class="fa fa-file-excel me-2"></i>Excel</button>
                        </div>
                        <div class="input-group" style="width: 300px">
                            <input id="filtroUR" type="text" class="form-control" maxlength=3 placeholder="UR">
                            <button id="mostrarV" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-success muestraPlanCuentas ladda-button  me-1" data-style="expand-left" cat="" tabindex="31"><i class="fa-solid fa-magnifying-glass me-2"></i>Mostrar</button>
                        </div>
                        <div id="filtrarEnPantalla"  class="input-group" style="width: 250px">
                            <input id="buscar" data-p="0" type="text" class="form-control" placeholder="Texto o $ a buscar ..." />
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass buscar"></i></span>
                        </div>
                </div>
            </div>
        ';
        $html = '
            <div class="row">
                ' . $this->getTablaClcs() . '
            </div>
            <!--
            <div id="VinculacionPaginador" class="tabla-footer"></div>
            -->
        ';

        //$this->setContenedor("vclcPantallaCompleta");
        $this->agregaTools($botones);
        $this->mixHtml($html);

        if ($bandera == "btnR"){
            $this->mixScript('
            $("#mesActual").attr("ma", localStorage.getItem("msc"));
            $("#tipoClcActual").attr("ta", localStorage.getItem("tip"));
            $("#edoActual").attr("ea", localStorage.getItem("edo"));
            $("#buscar").attr("ps", localStorage.getItem("p"));
            $("#filtroUR").val(localStorage.getItem("ur"));

             CargaVinculacion();
            ');
        }

        $this->mixScript('

            // Marcos: 08/feb/2018
            // Debido al cambio de interfaz, cada que "salgamos" de la vinculación por CLC y lleguemos
            // a una pantalla de un momento contable, aparecerá el menú.   Por lo tanto lo esconderemos
            // (si no está minificado lo hacemos, para que no ocupe espacio de pantalla)

            // Mostramos los documentos que cumplan con los criterios seleccionados
            $("#mostrarV").click(function(e){
                e.preventDefault();
                
                if($("#mesActual").attr("ma") == 0) {
                    notify("Seleccione el mes requerido","warning");
                    return false;
                }


                // Marcos: 23/mar/2018

                localStorage.setItem("msc", $("#mesActual").attr("ma"));
                localStorage.setItem("tip", $("#tipoClcActual").attr("ta"));
                localStorage.setItem("edo", $("#edoActual").attr("ea"));
                localStorage.setItem("p",   $("#buscar").attr("ps") ?? "");
                localStorage.setItem("ur",  $("#filtroUR").val());
                localStorage.setItem("buscar", $("#buscar").val());
                
                window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                $("#buscar").attr("tp","0");
                $("#buscar").attr("ps","");
                CargaVinculacion( );
                window.espera.stop();
                setTimeout( function() { $( "#buscar" ).focus() }, 100 );
                e.stopPropagation();
                return false;
            });

            // Imprimir la clc actual
            $(".imprimir").click( function(e) {
                e.preventDefault();

                if ($("#tbl_FondosClc tbody tr.seleccionado").length === 0) {
                    notify("Seleccione una fila", "warning");
                    return;
                    }

                fila = $("#tbl_FondosClc tbody tr.seleccionado");
                
                const ur  = fila.find("td:eq(0)").text().trim();
                const clc = fila.find("td:eq(2)").text().trim();

                var form = $("<form>")
                            .attr("action", "rep/doc/rep_clc.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", ur))
                            .append($("<input>").attr("type", "hidden").attr("name", "clc_id").attr("value", clc))
                            .append($("<input>").attr("type", "hidden").attr("name", "scg_vc").attr("value", 1))
                            .append($("<input>").attr("type", "hidden").attr("name", "modo").attr("value", $(this).attr("href") ))
                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });


            // Reporte de CLCs y sus pólizas vinculadas
            $("#reporte").click( function(e) {
                e.preventDefault();
                
                $.ajax({
                    data : { 
                        "msc"   : $("#mesActual").attr( "ma" ),
                        "tip"   : $("#tipoClcActual").attr( "ta" ),
                        "edo"   : $("#edoActual").attr( "ea" ),
                        "p"     : $("#buscar").attr("ps"), 
                        "ur"    : $("#filtroUR").val(), 
                        "buscar": $("#buscar").val(),
                        "gpoClc": "1"
                    },
                    url:  "lib/scg/fondos/dameVinculacionPorClcFondos.php",
                    type: "post",
                    success: function (response) {
                        if (response.trim().length > 0) {
                            var form = $("<form>")
                                .attr("action", "rep/doc/scg/vinculacion/rep_VinculacionPorClc.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", $("#filtroUR").val().trim() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "clc").attr("value", response ))
                                .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", $("#mesActual").attr( "ma" ) ))
                            $("body").append(form);
                            form.submit();
                        }
                    }
                });

                e.stopPropagation();
            });

            $("#exportarV").click(function() {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_ExportaConciliacionVinculacionCLC.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", $("#mesActual").attr( "ma" )))
                    .append($("<input>").attr("type", "hidden").attr("name", "tip").attr("value", $("#tipoClcActual").attr( "ta" )))
                    .append($("<input>").attr("type", "hidden").attr("name", "edo").attr("value", $("#edoActual").attr( "ma" )))
                $("body").append(form);
                form.submit();
                form.remove();
            });
            
            $(".mesContable").click(function(e) {
                $("#mesActual").html( $(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>" );
                $("#mesActual").attr( "ma", $(this).attr("href") );
                $("#exportarV").prop("disabled", false);
                e.preventDefault();
            });

            $(".tipoClc").click(function(e) {
                $("#tipoClcActual").html( $(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>" );
                $("#tipoClcActual").attr( "ta", $(this).attr("href") );
                e.preventDefault();
            });

            $(".estadoClc").click(function(e) {
                $("#edoActual").html( $(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>" );
                $("#edoActual").attr( "ea", $(this).attr("href") );
                e.preventDefault();
            });
            
            // Buscamos y cargamos los documentos que cumplan con los criterios solicitados
            // ===========================================================================
            function CargaVinculacion(l) {
                $("#tbl_FondosClc tbody").html("");

                dataVinculacion = [];
                dataFiltrado = [];
                let formData = {};
                
                var miURL = "lib/scg/fondos/dameVinculacionPorClcFondos.php";
                
                if ($("#buscar").attr("tp") == "0") {
                    var bolPrimeraBusqueda = true;
                } else {
                    var bolPrimeraBusqueda = false;
                }
                if ("'. $bandera .'" == ""){
                     formData = {
                        "msc"   : $("#mesActual").attr("ma"),
                        "tip"   : $("#tipoClcActual").attr("ta"),
                        "edo"   : $("#edoActual").attr("ea"),
                        "p"     : $("#buscar").attr("ps"),
                        "ur"    : $("#filtroUR").val(),
                        "buscar": $("#buscar").val()
                        };
                    }else{
                        formData = {
                            "msc" : localStorage.getItem("msc") || "",
                            "tip" : localStorage.getItem("tip") || "",
                            "edo" : localStorage.getItem("edo") || "",
                            "p"   : localStorage.getItem("p") || "",
                            "ur"  : localStorage.getItem("ur") || "",
                            "bsucar" : localStorage.getItem("buscar") || ""
                        };
                    }
                
                $.ajax({
                    data : formData,
                    url:  miURL,
                    type: "post",
                    success: function ( response ) {
                        var json = JSON.parse(response);
                        /* Empezamos a partir del 1 porque en la 0 traemos el total de los Documentos */
                        if (json.length >= 2 && (typeof json[1].edo !== "undefined") ){
                        for (var i = 1; i < json.length; i++) { 
                            dataVinculacion[i-1] = {
                                id: i,
                                tcl:  json[i].tcl,
                                clc:  json[i].clc,
                                ori:  json[i].ori,
                                cf:   json[i].cf,
                                ci:   json[i].ci,
                                con:  json[i].con,
                                ecl:  json[i].edo,
                                mp:   json[i].mp,
                                imp:  json[i].imp,
                                ur:   json[i].ur
                            }

                            $("#tabla-tbl_FondosClc tbody").append(
                                "<tr class=\'seleccionable\'><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].ur + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].tcl + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].clc + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].ori + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].cf + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].ci + "</td><td style = \' text-align: left\'>"
                                + json[i].con + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].edo + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].mp + "</td><td style = \' text-align: center; white-space: nowrap\'>"
                                + json[i].imp + "</td>" + "<td style = \' text-align: center; white-space: nowrap\'>"
                                + "<a href=\'#\'><i class=\'fa fa-eye\'></i></a>" + "</td>"
                                + "<td style = \' text-align: center; white-space: nowrap\'>"
                                + "<a href=\'#\'><i class=\'fa fa-eye\'></i></a>" + "</td>"
                                + "<td style = \' text-align: center; white-space: nowrap\'>"
                                + "<a href=\'#\'><i class=\'fa fa-eye\'></i></a>" + "</td>"
                                + "</tr>"
                                );
                            }
                        }else{
                                notify ("NO HAY COINCIDENCIAS CON SU BÚSQUEDA","warning");
                                }

                        if (bolPrimeraBusqueda) {
                            $("#buscar").attr("tp", json[0].tcl);   // Guardamos el numero total de paginas en un campo de la pantalla
                        }
                        
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
                                containerClass: "nav nav-tabs",
                                itemContainerClass: function ( type, page, current ) { return "nav-link me-1 " + ((page === current) ? "active" : "pointer-cursor"); },
                                onPageClicked: function(e,originalEvent,type,page){ 
                                    // Grabamos la ultima página a la que le dieron click
                                    $("#buscar").attr("upc",page);
                                    // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                    if ( $("#VinculacionPaginador").attr("last") != page ) {
                                        $("#buscar").attr("ps",page);
                                        CargaVinculacion();
                                        $("#VinculacionPaginador").attr("last", page );
                                        setTimeout( function() { $( "#buscar" ).focus() }, 100 );
                                    }
                                }
                            }
                            $("#VinculacionPaginador").bootstrapPaginator(options);
                            $("#VinculacionPaginador").addClass("pagination");
                            $("#VinculacionPaginador li").addClass("d-flex flex-row")
                            $("#VinculacionPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                        } else {
                            $("#VinculacionPaginador").html("");
                        }
                        window.espera.stop();
                    }
                });
            };
        ');

        $this->mixScript('
        
            // Función para ordenar un array basado en un criterio numérico del orden (1 antes del 2, a antes de la b)
            // --------------------------------------------------------------------------------------------------------------
            function NumericSorter(a, b) {
                var x = a[sortcol], y = b[sortcol];
                  return sortdir * (x == y ? 0 : (x > y ? 1 : -1));
            }

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
                }
            }

            function FormatearTexto(row, cell, value, columnDef, dataContext) {
                // en las cadenas donde tenga 0 o 0, se lo quitamos
                strValor = value;
                // Si sólo trae el cero, quitarlo
                if ( value.trim() == "0" ) {
                    value = "";
                } else {
                    // Quitamos el 0, ( cero más coma ) 
                    strValor = strValor.replace(new RegExp(",", "g"), ", ");
                }
                return "<a href=\"\" onclick=\"return false\" class=\"areaActiva\" title=\"" + strValor + "\">" + strValor + "</a>";
            }

            function FormatearConcepto(row, cell, value, columnDef, dataContext) {
                // Cuando sea cancelada ... también tachamos el texto
                return ( dataVinculacion[row].ecl == "20" ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
            }

            function FormatearUR(row, cell, value, columnDef, dataContext) {
                if ( value != "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" ) {
                    return value;
                }
            }


            // DATOS PARA EL MAESTRO DE LA CLC
            // ---------------------------------------------------------------------------

            var dataVinculacion = [];
            var columnsVinculacion = [
                {id: "ur",    name: "UR",            field: "ur",  minWidth: 35, maxWidth: 40, sortable: true, sorter:NumericSorter },
                {id: "tcl",   name: "TIPO",          field: "tcl", minWidth: 35, maxWidth: 40, sortable: true, sorter:NumericSorter },
                {id: "clc",   name: "CLC",           field: "clc", minWidth: 45, maxWidth: 45, sortable: true, sorter:NumericSorter },
                {id: "ori",   name: "ORI",           field: "ori", minWidth: 20, sortable: true, sorter:NumericSorter },
                {id: "cf",    name: "CVE. FIN.",     field: "cf",  minWidth: 95, sortable: true, sorter:NumericSorter, formatter: FormatearConcepto  },
                {id: "ci",    name: "CLABE INTERB.", field: "ci",  minWidth: 50, sortable: true, sorter:NumericSorter, formatter: FormatearConcepto  },
                {id: "con",   name: "CONCEPTO",      field: "con", minWidth: 50, sortable: true, sorter:NumericSorter, formatter: FormatearConcepto  },
                {id: "edo",   name: "EDO",           field: "ecl", minWidth: 40,  sortable: true, sorter:NumericSorter, headerCssClass: "alineaCentro", cssClass: "alineaCentro"},
                {id: "mp",    name: "M PAGO",        field: "mp",  minWidth: 60,  sortable: true, sorter:NumericSorter, headerCssClass: "alineaCentro", cssClass: "alineaCentro"},
                {id: "imp",   name: "IMPORTE",       field: "imp", minWidth: 120, sortable: true, sorter:NumericSorter, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda }, 
                {id: "pv",    name: "G",             minWidth: 10, sortable: true, sorter:NumericSorter, headerCssClass: "alineaCentro", cssClass: "alineaCentro"},
                {id: "pg",    name: "B",             minWidth: 10, sortable: true, sorter:NumericSorter, headerCssClass: "alineaCentro", cssClass: "alineaCentro"},
                {id: "rc",    name: "K",             minWidth: 10, sortable: true, sorter:NumericSorter, headerCssClass: "alineaCentro", cssClass: "alineaCentro"}
            ];
            
            var options = {
                enableCellNavigation: true,
                enableColumnReorder: false,
                autoHeight: true,
                autosizeColsMode: Slick.GridAutosizeColsMode.Legacy
            };

            const observer = new MutationObserver(() => {
            document.querySelectorAll(".tabla-footer").forEach(e => e.remove());
            });
            observer.observe(document.body, { childList: true, subtree: true });

            $("#buscar").on("keyup", function(e){
                var value = $(this).val().toLowerCase();
                $("#tabla-tbl_FondosClc tbody>tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Seleccionar una sola fila
            $("#tabla-tbl_FondosClc").on("click", "tr.seleccionable", function () {
                // quitar selección a otras filas
                $("#tabla-tbl_FondosClc tr.seleccionado").removeClass("seleccionado");

                // seleccionar esta fila
                $(this).addClass("seleccionado");
                $("#reportes").prop("disabled", false);
            });
            

            // Para obtener el valor de una celda al presionar doble click en ella
            // --------------------------------------------------------------------------------------------------------------
            $("#tbody-tabla-tbl_FondosClc").on("dblclick", "td", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const colIndex = $(this).index();
                const columnActual = $("#tbl_FondosClc thead th").eq(colIndex).text();
                const fila = $(this).closest("tr");

                const urf  = fila.find("td:eq(0)").text().trim();
                const clc = fila.find("td:eq(2)").text().trim();

              var ur = "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '";
                var clc_solo_contables = "CDEHM";

                // Si no hay nada que selecionar, no hacemos nada
                //if (!fila ) { e.stopPropagation();   return false; }

                // Si no hay ningún documento seleccionado, no hacemos nada
                //if (!$(this).hasClass("seleccionado")) { e.stopPropagation();   return false; }

                // G - Provision de la CereCo
                // Aún no están
                if ( columnActual == "G" ) {
                    if ( clc.trim() != "" ) {
                        window.espera = Ladda.create(this);   window.espera.start();
                        comunica("scg/fondos/ControladorCereCo.php", { 
                            metodo: "PonBarraBotones", 
                            mes: $("#mesActual").attr( "ma" ), 
                            clc_id: clc, 
                            clcFiltro: 1, 
                            gur: urf, 
                            origen: "clc" 
                        });
                    }
                }

                // B Para la transferencia de la CERE/CERECO
                if ( columnActual == "B" ) {
                    if ( clc.trim() != "" ) {
                        comunica("scg/fondos/ControladorMinistrado.php", { 
                            metodo: "PonBarraBotones", 
                            mes: $("#mesActual").attr( "ma" ), 
                            clc_id: clc, 
                            clcFiltro: 1, 
                            gur: urf, 
                            origen: "clc"
                        });
                    }
                }

                // K Para el Rectificado
                if ( columnActual == "K" ) {
                    if ( clc.trim() != "" ) {
                        comunica("scg/fondos/ControladorRectificado.php", { 
                            metodo: "PonBarraBotones", 
                            mes: $("#mesActual").attr( "ma" ), 
                            clc_id: clc, 
                            clcFiltro: 1, 
                            gur: urf, 
                            origen: "clc" 
                        });
                    }
                }

                e.stopPropagation();
            });

            setTimeout( function() {
                    $("#filtrarEnPantalla").css({top: "2px"}) 
                    $("#VinculacionPaginador").css({top: "1px"}) 
            }, 100 );
        ');
    }

    private function getTablaClcs()
    {
        $botones = '';
        $botones .= <<<HTML
            <input type="search" width=50
                id="filtroTabla"
                style="display: inline; width:300px; height: 30px;"
                class="form-control m-r-10" 
                placeholder="Filtrar" />
            HTML;
        //$this->agregaTools($botones);

        $tabla = new stdClass();
        $tabla->id = 'tbl_FondosClc';
        $tabla->esCompacta = true;
        $tabla->titulo = 'Lista de Clcs';
        //$tabla->seleccionable = true;
        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ORI';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CVE. FIN.';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLAVE INTERB.';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = "center";
        $th->width = '25px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EDO';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'M PAGO';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'G';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'B';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'K';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);

        
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'LlenarTablaClcs';

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = array();

        return $this->wrPonTabla($tabla);
    }
}

if (isset($_SESSION[_S]['prm']['C']['12']['1']['1'])) { // permisos del usuario que puede ver este controlador
    new ControladorVinculacionPorClcFondos();
} else {
    WebResponse::sinPermisos();
}